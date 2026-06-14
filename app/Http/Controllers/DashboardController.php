<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Email;
use App\Models\Group;
use App\Models\Inbox;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $groupId = $user->isGroupAdmin() ? $user->group_id : null;

        $dailyStats = Email::query()
            ->selectRaw('DATE(received_at) as day, COUNT(*) as total')
            ->when($groupId, fn ($query) => $query->whereHas('inbox', fn ($inboxQuery) => $inboxQuery->where('group_id', $groupId)))
            ->where('received_at', '>=', now()->subDays(13))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $chartData = collect(range(13, 0))
            ->reverse()
            ->map(function (int $daysAgo) use ($dailyStats): array {
                $day = now()->subDays($daysAgo)->toDateString();

                return [
                    'day' => $day,
                    'label' => now()->subDays($daysAgo)->translatedFormat('d M'),
                    'total' => (int) optional($dailyStats->get($day))->total,
                ];
            });

        return view('dashboard', [
            'isSaasAdmin' => $user->isSaasAdmin(),
            'groupContext' => $groupId ? Group::query()->find($groupId) : null,
            'totalInboxes' => Inbox::query()
                ->when($groupId, fn ($query) => $query->where('group_id', $groupId))
                ->count(),
            'totalGroups' => $groupId ? 1 : Group::query()->count(),
            'totalGroupAdmins' => $groupId ? 1 : User::query()->where('role', User::ROLE_GROUP_ADMIN)->count(),
            'totalEmails' => Email::query()
                ->when($groupId, fn ($query) => $query->whereHas('inbox', fn ($inboxQuery) => $inboxQuery->where('group_id', $groupId)))
                ->count(),
            'totalAttachments' => Attachment::query()
                ->when($groupId, fn ($query) => $query->whereHas('email.inbox', fn ($inboxQuery) => $inboxQuery->where('group_id', $groupId)))
                ->count(),
            'emailsToday' => Email::query()
                ->when($groupId, fn ($query) => $query->whereHas('inbox', fn ($inboxQuery) => $inboxQuery->where('group_id', $groupId)))
                ->whereDate('received_at', now()->toDateString())
                ->count(),
            'chartData' => $chartData,
            'recentInboxes' => Inbox::query()
                ->with(['group'])
                ->withCount('emails')
                ->when($groupId, fn ($query) => $query->where('group_id', $groupId))
                ->latest()
                ->limit(6)
                ->get(),
            'recentGroups' => $groupId
                ? collect()
                : Group::query()
                    ->withCount([
                        'users as admin_users_count' => fn ($query) => $query->where('role', User::ROLE_GROUP_ADMIN),
                        'inboxes',
                    ])
                    ->latest()
                    ->limit(6)
                    ->get(),
            'recentEmails' => Email::query()
                ->with(['inbox.group', 'attachments'])
                ->when($groupId, fn ($query) => $query->whereHas('inbox', fn ($inboxQuery) => $inboxQuery->where('group_id', $groupId)))
                ->latest('received_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
