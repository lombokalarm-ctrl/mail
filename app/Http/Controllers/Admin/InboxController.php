<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inbox;
use App\Services\EmailMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InboxController extends Controller
{
    public function index(Request $request): View
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
            ->paginate(15)
            ->withQueryString();

        return view('admin.inboxes.index', [
            'inboxes' => $inboxes,
            'search' => $search,
        ]);
    }

    public function destroy(Inbox $inbox, EmailMaintenanceService $maintenance): RedirectResponse
    {
        $maintenance->deleteInbox($inbox);

        return back()->with('status', 'Inbox berhasil dihapus.');
    }
}
