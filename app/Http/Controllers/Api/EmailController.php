<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Services\EmailMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q'));

        $emails = Email::query()
            ->with(['inbox', 'attachments'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('subject', 'like', "%{$search}%")
                        ->orWhere('sender_email', 'like', "%{$search}%")
                        ->orWhere('sender_name', 'like', "%{$search}%");
                });
            })
            ->latest('received_at')
            ->paginate(20);

        $emails->getCollection()->transform(fn (Email $email) => $this->transformEmail($email, false));

        return response()->json($emails);
    }

    public function show(Email $email): JsonResponse
    {
        $email->load(['inbox', 'attachments']);

        return response()->json([
            'data' => $this->transformEmail($email, true),
        ]);
    }

    public function destroy(Email $email, EmailMaintenanceService $maintenance): JsonResponse
    {
        $maintenance->deleteEmail($email);

        return response()->json([
            'message' => 'Email berhasil dihapus.',
        ]);
    }

    protected function transformEmail(Email $email, bool $includeBodies): array
    {
        return [
            'id' => $email->id,
            'inbox' => [
                'id' => $email->inbox->id,
                'inbox_name' => $email->inbox->inbox_name,
                'viewer_url' => $email->inbox->viewer_url,
            ],
            'sender_email' => $email->sender_email,
            'sender_name' => $email->sender_name,
            'recipient_email' => $email->recipient_email,
            'subject' => $email->subject,
            'body_html' => $includeBodies ? $email->body_html : null,
            'body_text' => $includeBodies ? $email->body_text : null,
            'received_at' => optional($email->received_at)->toAtomString(),
            'created_at' => optional($email->created_at)->toAtomString(),
            'attachments' => $email->attachments->map(fn ($attachment) => [
                'id' => $attachment->id,
                'filename' => $attachment->filename,
                'filepath' => $attachment->filepath,
                'filesize' => $attachment->filesize,
                'mime_type' => $attachment->mime_type,
            ]),
        ];
    }
}
