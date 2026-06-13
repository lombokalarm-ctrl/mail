<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Services\EmailMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));

        $emails = Email::query()
            ->with(['inbox', 'attachments'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('subject', 'like', "%{$search}%")
                        ->orWhere('sender_email', 'like', "%{$search}%")
                        ->orWhere('sender_name', 'like', "%{$search}%")
                        ->orWhere('recipient_email', 'like', "%{$search}%");
                });
            })
            ->latest('received_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.emails.index', [
            'emails' => $emails,
            'search' => $search,
        ]);
    }

    public function destroy(Email $email, EmailMaintenanceService $maintenance): RedirectResponse
    {
        $maintenance->deleteEmail($email);

        return back()->with('status', 'Email berhasil dihapus.');
    }
}
