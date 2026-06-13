<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Inbox;
use App\Services\EmailMaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InboxController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));

        $inboxes = Inbox::query()
            ->with('group')
            ->withCount('emails')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('inbox_name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('group', function ($groupQuery) use ($search): void {
                            $groupQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('viewer_token', 'like', "%{$search}%");
                        });
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'inbox_name' => ['required', 'string', 'max:255', Rule::unique('inboxes', 'inbox_name')],
        ]);

        $normalized = strtolower(trim($data['inbox_name']));

        Inbox::query()->create([
            'group_id' => (int) $data['group_id'],
            'inbox_name' => $normalized,
            'slug' => $this->generateUniqueSlug($normalized),
        ]);

        return back()->with('status', 'Inbox berhasil dibuat.');
    }

    public function update(Request $request, Inbox $inbox): RedirectResponse
    {
        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'inbox_name' => ['required', 'string', 'max:255', Rule::unique('inboxes', 'inbox_name')->ignore($inbox->id)],
        ]);

        $normalized = strtolower(trim($data['inbox_name']));
        $slug = $normalized === $inbox->inbox_name
            ? $inbox->slug
            : $this->generateUniqueSlug($normalized, $inbox->id);

        $inbox->forceFill([
            'group_id' => (int) $data['group_id'],
            'inbox_name' => $normalized,
            'slug' => $slug,
            'access_token' => Group::query()->whereKey($data['group_id'])->value('viewer_token'),
        ])->save();

        return back()->with('status', 'Inbox berhasil diperbarui.');
    }

    public function destroy(Inbox $inbox, EmailMaintenanceService $maintenance): RedirectResponse
    {
        $maintenance->deleteInbox($inbox);

        return back()->with('status', 'Inbox berhasil dihapus.');
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreInboxId = null): string
    {
        $slug = Str::slug($name, '-');

        if ($slug === '') {
            $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-') ?: strtolower(Str::random(8));
        }

        $candidate = $slug;
        $suffix = 1;

        while (
            Inbox::query()
                ->when($ignoreInboxId, fn ($query) => $query->whereKeyNot($ignoreInboxId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = "{$slug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
