<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Email;
use App\Models\Inbox;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use ZBateson\MailMimeParser\Header\HeaderConsts;
use ZBateson\MailMimeParser\Message;
use ZBateson\MailMimeParser\Message\IMessagePart;

class InboundEmailService
{
    public function ingest(string $rawEmail): Email
    {
        $message = Message::from($rawEmail, false);
        $recipientEmail = $this->resolveRecipientEmail($message, $rawEmail);
        $inboxName = $this->extractInboxName($recipientEmail);
        $sender = $this->resolveSender($message);
        $subject = trim((string) $message->getSubject()) ?: '(Tanpa Subjek)';
        $bodyHtml = $message->getHtmlContent();
        $bodyText = $message->getTextContent();

        if (! $bodyText && $bodyHtml) {
            $bodyText = trim(strip_tags($bodyHtml));
        }

        $sanitizedHtml = $bodyHtml ? $this->sanitizeHtml($bodyHtml) : null;
        $receivedAt = $this->resolveReceivedAt($message);

        return DB::transaction(function () use (
            $message,
            $inboxName,
            $recipientEmail,
            $sender,
            $subject,
            $sanitizedHtml,
            $bodyText,
            $receivedAt
        ): Email {
            $inbox = $this->findOrCreateInbox($inboxName);

            $email = Email::query()->create([
                'inbox_id' => $inbox->id,
                'sender_email' => $sender['email'],
                'sender_name' => $sender['name'],
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'body_html' => $sanitizedHtml,
                'body_text' => $bodyText,
                'received_at' => $receivedAt,
            ]);

            $this->storeAttachments($message, $email);

            return $email->load(['inbox', 'attachments']);
        });
    }

    protected function resolveRecipientEmail(Message $message, string $rawEmail): string
    {
        $candidates = [
            $message->getHeaderValue('Delivered-To'),
            $message->getHeaderValue('X-Original-To'),
            $message->getHeaderValue('Envelope-To'),
            $message->getHeaderValue(HeaderConsts::TO),
        ];

        foreach ($candidates as $candidate) {
            $parsed = $this->parseEmailAddress($candidate);

            if ($parsed) {
                return $parsed;
            }
        }

        preg_match('/^(Delivered-To|X-Original-To|Envelope-To|To):\s*(.+)$/mi', $rawEmail, $matches);
        $parsed = $this->parseEmailAddress($matches[2] ?? null);

        if (! $parsed) {
            throw new RuntimeException('Alamat penerima tidak ditemukan pada raw email.');
        }

        return $parsed;
    }

    protected function resolveSender(Message $message): array
    {
        $header = $message->getHeader(HeaderConsts::FROM);

        if ($header && method_exists($header, 'getAddresses')) {
            $addresses = $header->getAddresses();

            if (count($addresses) > 0) {
                return [
                    'email' => strtolower($addresses[0]->getEmail()),
                    'name' => $addresses[0]->getName() ?: null,
                ];
            }
        }

        $senderEmail = $this->parseEmailAddress($message->getHeaderValue(HeaderConsts::FROM)) ?: 'unknown@example.com';

        return [
            'email' => $senderEmail,
            'name' => null,
        ];
    }

    protected function resolveReceivedAt(Message $message): CarbonImmutable
    {
        try {
            $headerValue = $message->getHeaderValue(HeaderConsts::DATE);

            if ($headerValue) {
                return CarbonImmutable::parse($headerValue);
            }
        } catch (\Throwable) {
            // Fallback to current time when Date header is malformed.
        }

        return CarbonImmutable::now();
    }

    protected function extractInboxName(string $recipientEmail): string
    {
        $recipientEmail = strtolower(trim($recipientEmail));
        $expectedDomain = '@'.strtolower(config('apli_mail.domain'));

        if (! str_ends_with($recipientEmail, $expectedDomain)) {
            throw new RuntimeException('Email bukan bagian dari domain catch-all yang dikonfigurasi.');
        }

        return Str::before($recipientEmail, '@');
    }

    protected function findOrCreateInbox(string $inboxName): Inbox
    {
        $normalized = strtolower(trim($inboxName));
        $slug = $this->slugifyInboxName($normalized);

        return Inbox::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'inbox_name' => $normalized,
                'access_token' => $this->generateAccessToken(),
            ]
        );
    }

    protected function storeAttachments(Message $message, Email $email): void
    {
        foreach ($message->getAllAttachmentParts() as $index => $attachmentPart) {
            $this->storeAttachmentPart($attachmentPart, $email, $index + 1);
        }
    }

    protected function storeAttachmentPart(IMessagePart $attachmentPart, Email $email, int $index): void
    {
        $stream = $attachmentPart->getContentStream();
        $contents = $stream?->getContents();

        if (! is_string($contents) || $contents === '') {
            return;
        }

        $size = strlen($contents);
        $limit = (int) config('apli_mail.attachment_limit');

        if ($size > $limit) {
            throw new RuntimeException("Lampiran melebihi batas {$limit} byte.");
        }

        $originalName = $attachmentPart->getFilename() ?: "attachment-{$index}.bin";
        $safeName = preg_replace('/[^A-Za-z0-9.\-_]/', '-', $originalName) ?: "attachment-{$index}.bin";
        $path = 'attachments/'.$email->id.'/'.Str::random(12).'-'.$safeName;

        Storage::disk(config('apli_mail.attachments_disk'))->put($path, $contents);

        Attachment::query()->create([
            'email_id' => $email->id,
            'filename' => $originalName,
            'filepath' => $path,
            'filesize' => $size,
            'mime_type' => $attachmentPart->getContentType('application/octet-stream') ?: 'application/octet-stream',
        ]);
    }

    protected function sanitizeHtml(string $html): string
    {
        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height'])
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            ->allowMediaSchemes(['http', 'https', 'data'])
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->withMaxInputLength(500_000);

        return (new HtmlSanitizer($config))->sanitize($html);
    }

    protected function generateAccessToken(): string
    {
        do {
            $token = strtolower(Str::random(6));
        } while (Inbox::query()->where('access_token', $token)->exists());

        return $token;
    }

    protected function slugifyInboxName(string $name): string
    {
        $slug = Str::slug($name, '-');

        if ($slug !== '') {
            return $slug;
        }

        return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-') ?: strtolower(Str::random(8));
    }

    protected function parseEmailAddress(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $value, $matches);

        return isset($matches[0]) ? strtolower($matches[0]) : null;
    }
}
