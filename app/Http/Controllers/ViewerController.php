<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Services\ViewerAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ViewerController extends Controller
{
    public function __invoke(Request $request, string $viewerKey, ViewerAccessService $access): View
    {
        $inbox = $access->resolveInbox($viewerKey);
        $search = trim((string) $request->string('q'));

        $emails = Email::query()
            ->with('attachments')
            ->where('inbox_id', $inbox->id)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('subject', 'like', "%{$search}%")
                        ->orWhere('sender_email', 'like', "%{$search}%")
                        ->orWhere('sender_name', 'like', "%{$search}%")
                        ->orWhere('body_text', 'like', "%{$search}%");
                });
            })
            ->latest('received_at')
            ->paginate(15)
            ->withQueryString();

        return view('viewer.index', [
            'inbox' => $inbox,
            'emails' => $emails,
            'viewerKey' => $viewerKey,
            'search' => $search,
        ]);
    }
}
