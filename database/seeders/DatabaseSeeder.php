<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Email;
use App\Models\Group;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => config('apli_mail.default_admin_email'),
        ], [
            'name' => 'APLI Admin',
            'email_verified_at' => now(),
            'password' => Hash::make(config('apli_mail.default_admin_password')),
        ]);

        Storage::disk(config('apli_mail.attachments_disk'))->deleteDirectory('attachments');
        Attachment::query()->delete();
        Email::query()->delete();
        Inbox::query()->delete();
        Group::query()->delete();

        $samples = [
            [
                'group' => [
                    'name' => 'Alhijrah Travel',
                    'viewer_token' => 'f7k29a',
                    'status' => 'active',
                ],
                'inboxes' => [
                    [
                        'inbox_name' => 'ahmad-alhijrah',
                        'emails' => [
                            [
                                'sender_email' => 'travel@maskapai.example',
                                'sender_name' => 'Maskapai Nusantara',
                                'subject' => 'E-ticket Umrah Berhasil Terbit',
                                'body_html' => '<h2>E-ticket Umrah</h2><p>Terlampir e-ticket untuk keberangkatan jamaah tanggal 15 Juli 2026.</p>',
                                'body_text' => 'Terlampir e-ticket untuk keberangkatan jamaah tanggal 15 Juli 2026.',
                                'attachments' => [
                                    ['filename' => 'e-ticket.txt', 'mime_type' => 'text/plain', 'content' => 'Sample e-ticket content'],
                                ],
                            ],
                            [
                                'sender_email' => 'marketing@partner.example',
                                'sender_name' => 'Partner Marketing',
                                'subject' => 'Promo marketing bundle Qurban 2026',
                                'body_html' => '<p>Berikut penawaran bundle marketing untuk campaign Qurban 2026.</p>',
                                'body_text' => 'Berikut penawaran bundle marketing untuk campaign Qurban 2026.',
                                'attachments' => [],
                            ],
                        ],
                    ],
                    [
                        'inbox_name' => 'visa-alhijrah',
                        'emails' => [
                            [
                                'sender_email' => 'visa@embassy.example',
                                'sender_name' => 'Embassy Desk',
                                'subject' => 'Visa confirmation for alhijrah group',
                                'body_html' => '<p>Pengajuan visa telah diterima dan sedang diproses.</p>',
                                'body_text' => 'Pengajuan visa telah diterima dan sedang diproses.',
                                'attachments' => [
                                    ['filename' => 'visa-checklist.txt', 'mime_type' => 'text/plain', 'content' => 'Checklist dokumen visa'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'inbox_name' => 'tiket-alhijrah',
                        'emails' => [
                            [
                                'sender_email' => 'ops@travel.example',
                                'sender_name' => 'Travel Operations',
                                'subject' => 'Manifest tiket group terbaru',
                                'body_html' => '<p>Manifest tiket group telah diperbarui.</p>',
                                'body_text' => 'Manifest tiket group telah diperbarui.',
                                'attachments' => [
                                    ['filename' => 'manifest.csv', 'mime_type' => 'text/csv', 'content' => "nama,tiket\nAhmad,GA-221\nFatimah,GA-222"],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'group' => [
                    'name' => 'Demo Umum',
                    'viewer_token' => 'demo2026',
                    'status' => 'trial',
                ],
                'inboxes' => [
                    [
                        'inbox_name' => 'marketing-demo',
                        'emails' => [
                            [
                                'sender_email' => 'campaign@partner.example',
                                'sender_name' => 'Partner Campaign',
                                'subject' => 'Materi promosi landing page terbaru',
                                'body_html' => '<p>Materi promosi terbaru sudah siap ditinjau.</p>',
                                'body_text' => 'Materi promosi terbaru sudah siap ditinjau.',
                                'attachments' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $group = Group::query()->updateOrCreate([
                'viewer_token' => $sample['group']['viewer_token'],
            ], [
                'name' => $sample['group']['name'],
                'status' => $sample['group']['status'],
            ]);

            foreach ($sample['inboxes'] as $inboxData) {
                $inbox = Inbox::query()->updateOrCreate([
                    'slug' => $inboxData['inbox_name'],
                ], [
                    'group_id' => $group->id,
                    'inbox_name' => $inboxData['inbox_name'],
                ]);

                foreach ($inboxData['emails'] as $emailData) {
                    $email = Email::query()->create([
                        'inbox_id' => $inbox->id,
                        'sender_email' => $emailData['sender_email'],
                        'sender_name' => $emailData['sender_name'],
                        'recipient_email' => $inboxData['inbox_name'].'@'.config('apli_mail.domain'),
                        'subject' => $emailData['subject'],
                        'body_html' => $emailData['body_html'],
                        'body_text' => $emailData['body_text'],
                        'received_at' => now()->subMinutes(rand(10, 500)),
                    ]);

                    foreach ($emailData['attachments'] as $attachmentData) {
                        $path = 'attachments/'.$email->id.'/'.$attachmentData['filename'];
                        Storage::disk(config('apli_mail.attachments_disk'))->put($path, $attachmentData['content']);

                        Attachment::query()->create([
                            'email_id' => $email->id,
                            'filename' => $attachmentData['filename'],
                            'filepath' => $path,
                            'filesize' => strlen($attachmentData['content']),
                            'mime_type' => $attachmentData['mime_type'],
                        ]);
                    }
                }
            }
        }
    }
}
