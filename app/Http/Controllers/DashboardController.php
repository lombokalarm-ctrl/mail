<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Email;
use App\Models\Group;
use App\Models\Inbox;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $dailyStats = Email::query()
            ->selectRaw('DATE(received_at) as day, COUNT(*) as total')
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
            'totalInboxes' => Inbox::query()->count(),
            'totalGroups' => Group::query()->count(),
            'totalEmails' => Email::query()->count(),
            'totalAttachments' => Attachment::query()->count(),
            'emailsToday' => Email::query()->whereDate('received_at', now()->toDateString())->count(),
            'chartData' => $chartData,
            'recentInboxes' => Inbox::query()->with(['group'])->withCount('emails')->latest()->limit(6)->get(),
            'recentEmails' => Email::query()
                ->with(['inbox.group', 'attachments'])
                ->latest('received_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
