<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inbox;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InboxController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q'));

        $inboxes = Inbox::query()
            ->withCount('emails')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('inbox_name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);

        $inboxes->getCollection()->transform(function (Inbox $inbox): array {
            return [
                'id' => $inbox->id,
                'inbox_name' => $inbox->inbox_name,
                'slug' => $inbox->slug,
                'access_token' => $inbox->access_token,
                'viewer_url' => $inbox->viewer_url,
                'emails_count' => $inbox->emails_count,
                'created_at' => optional($inbox->created_at)->toAtomString(),
                'updated_at' => optional($inbox->updated_at)->toAtomString(),
            ];
        });

        return response()->json($inboxes);
    }

    public function show(Inbox $inbox): JsonResponse
    {
        $inbox->load(['emails' => fn ($query) => $query->latest('received_at')->limit(20)->withCount('attachments')]);
        $inbox->loadCount('emails');

        return response()->json([
            'data' => [
                'id' => $inbox->id,
                'inbox_name' => $inbox->inbox_name,
                'slug' => $inbox->slug,
                'access_token' => $inbox->access_token,
                'viewer_url' => $inbox->viewer_url,
                'emails_count' => $inbox->emails_count,
                'emails' => $inbox->emails->map(fn ($email) => [
                    'id' => $email->id,
                    'subject' => $email->subject,
                    'sender_email' => $email->sender_email,
                    'sender_name' => $email->sender_name,
                    'recipient_email' => $email->recipient_email,
                    'received_at' => optional($email->received_at)->toAtomString(),
                    'attachments_count' => $email->attachments_count,
                ]),
            ],
        ]);
    }
}
