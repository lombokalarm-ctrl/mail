<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Email;
use App\Models\Inbox;
use App\Services\InboundEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InboundEmailServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_inbox_email_and_attachment_from_raw_message(): void
    {
        Storage::fake('local');
        Config::set('apli_mail.attachments_disk', 'local');

        $rawEmail = <<<MAIL
Delivered-To: ahmad-alhijrah@email.apli.my.id
From: "Maskapai Nusantara" <travel@maskapai.example>
To: <ahmad-alhijrah@email.apli.my.id>
Subject: E-ticket Umrah Berhasil Terbit
Date: Fri, 12 Jun 2026 10:00:00 +0700
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary="boundary42"

--boundary42
Content-Type: text/plain; charset="utf-8"

Terlampir e-ticket untuk keberangkatan jamaah.

--boundary42
Content-Type: text/html; charset="utf-8"

<p>Terlampir <strong>e-ticket</strong> untuk keberangkatan jamaah.</p>

--boundary42
Content-Type: text/plain; name="e-ticket.txt"
Content-Disposition: attachment; filename="e-ticket.txt"
Content-Transfer-Encoding: base64

U2FtcGxlIGF0dGFjaG1lbnQ=
--boundary42--
MAIL;

        $email = app(InboundEmailService::class)->ingest($rawEmail);

        $this->assertInstanceOf(Email::class, $email);
        $this->assertDatabaseCount('inboxes', 1);
        $this->assertDatabaseCount('emails', 1);
        $this->assertDatabaseCount('attachments', 1);
        $this->assertSame('ahmad-alhijrah', Inbox::query()->first()->inbox_name);
        $this->assertSame('travel@maskapai.example', $email->sender_email);

        $attachment = Attachment::query()->first();
        Storage::disk('local')->assertExists($attachment->filepath);
    }
}
