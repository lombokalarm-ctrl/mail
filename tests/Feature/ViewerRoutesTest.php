<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Email;
use App\Models\Inbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ViewerRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_pages_show_email_list_and_detail(): void
    {
        $inbox = Inbox::factory()->create([
            'inbox_name' => 'visa-alhijrah',
            'slug' => 'visa-alhijrah',
            'access_token' => 'abc123',
        ]);

        $email = Email::factory()->create([
            'inbox_id' => $inbox->id,
            'subject' => 'Visa confirmation for alhijrah group',
            'sender_email' => 'visa@embassy.example',
            'recipient_email' => 'visa-alhijrah@email.apli.my.id',
            'body_text' => 'Pengajuan visa telah diterima.',
        ]);

        $viewerKey = $inbox->viewer_key;

        $this->get(route('viewer.index', ['viewerKey' => $viewerKey]))
            ->assertOk()
            ->assertSee('Visa confirmation for alhijrah group')
            ->assertSee('visa@embassy.example');

        $this->get(route('viewer.show', ['viewerKey' => $viewerKey, 'email' => $email]))
            ->assertOk()
            ->assertSee('Pengajuan visa telah diterima.');
    }

    public function test_attachment_download_requires_valid_viewer_key_or_authentication(): void
    {
        Storage::fake('local');
        Config::set('apli_mail.attachments_disk', 'local');

        $inbox = Inbox::factory()->create([
            'slug' => 'tiket-alhijrah',
            'access_token' => 'tok321',
        ]);

        $email = Email::factory()->create([
            'inbox_id' => $inbox->id,
        ]);

        Storage::disk('local')->put('attachments/test/sample.txt', 'sample');

        $attachment = Attachment::factory()->create([
            'email_id' => $email->id,
            'filename' => 'sample.txt',
            'filepath' => 'attachments/test/sample.txt',
            'mime_type' => 'text/plain',
            'filesize' => 6,
        ]);

        $this->get(route('attachments.download', ['attachment' => $attachment]))
            ->assertForbidden();

        $this->get(route('attachments.download', ['attachment' => $attachment, 'viewer' => $inbox->viewer_key]))
            ->assertOk();
    }
}
