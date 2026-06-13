<?php

namespace App\Services;

use App\Models\Email;
use App\Models\Inbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmailMaintenanceService
{
    public function deleteEmail(Email $email): void
    {
        DB::transaction(function () use ($email): void {
            $email->loadMissing('attachments');

            foreach ($email->attachments as $attachment) {
                Storage::disk(config('apli_mail.attachments_disk'))->delete($attachment->filepath);
            }

            $email->delete();
        });
    }

    public function deleteInbox(Inbox $inbox): void
    {
        DB::transaction(function () use ($inbox): void {
            $inbox->loadMissing('emails.attachments');

            foreach ($inbox->emails as $email) {
                foreach ($email->attachments as $attachment) {
                    Storage::disk(config('apli_mail.attachments_disk'))->delete($attachment->filepath);
                }
            }

            $inbox->delete();
        });
    }
}
