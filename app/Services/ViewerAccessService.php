<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Email;
use App\Models\Inbox;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ViewerAccessService
{
    public function resolveInbox(string $viewerKey): Inbox
    {
        preg_match('/^(?<slug>.+)-(?<token>[A-Za-z0-9]+)$/', $viewerKey, $matches);

        $slug = $matches['slug'] ?? null;
        $token = $matches['token'] ?? null;

        if (! $slug || ! $token) {
            throw (new ModelNotFoundException())->setModel(Inbox::class);
        }

        return Inbox::query()
            ->with('group')
            ->where('slug', $slug)
            ->whereHas('group', fn ($query) => $query->where('viewer_token', $token))
            ->firstOrFail();
    }

    public function ensureInboxEmailMatch(Inbox $inbox, Email $email): void
    {
        if ($email->inbox_id !== $inbox->id) {
            throw (new ModelNotFoundException())->setModel(Email::class);
        }
    }

    public function canDownloadAttachment(?string $viewerKey, Attachment $attachment): bool
    {
        if (! $viewerKey) {
            return false;
        }

        $expected = $attachment->email->inbox->viewer_key;

        return hash_equals($expected, $viewerKey);
    }
}
