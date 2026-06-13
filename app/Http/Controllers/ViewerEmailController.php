<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Services\ViewerAccessService;
use Illuminate\View\View;

class ViewerEmailController extends Controller
{
    public function __invoke(string $viewerKey, Email $email, ViewerAccessService $access): View
    {
        $inbox = $access->resolveInbox($viewerKey);
        $email->load(['attachments', 'inbox']);
        $access->ensureInboxEmailMatch($inbox, $email);

        return view('viewer.show', [
            'inbox' => $inbox,
            'email' => $email,
            'viewerKey' => $viewerKey,
        ]);
    }
}
