<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Services\ViewerAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function __invoke(Request $request, Attachment $attachment, ViewerAccessService $access): StreamedResponse
    {
        $attachment->loadMissing('email.inbox');

        if (! $request->user() && ! $access->canDownloadAttachment($request->query('viewer'), $attachment)) {
            abort(403);
        }

        return Storage::disk(config('apli_mail.attachments_disk'))->download(
            $attachment->filepath,
            $attachment->filename,
            ['Content-Type' => $attachment->mime_type]
        );
    }
}
