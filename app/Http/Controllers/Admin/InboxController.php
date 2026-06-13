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
        $user = $request->user();
        $search = trim((string) $request->string('q'));

        $inboxes = Inbox::query()
            ->with('group')
            ->withCount('emails')
            ->when($user->isGroupAdmin(), fn ($query) => $query->where('group_id', $user->group_id))
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
            'groupOptions' => $user->isSaasAdmin()
                ? Group::query()->orderBy('name')->get(['id', 'name'])
                : Group::query()->whereKey($user->group_id)->get(['id', 'name']),
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
        $user = $request->user();
        $groupId = $user->isGroupAdmin() ? $user->group_id : (int) $data['group_id'];

        Inbox::query()->create([
            'group_id' => $groupId,
            'inbox_name' => $normalized,
            'slug' => $this->generateUniqueSlug($normalized),
        ]);

        return back()->with('status', 'Inbox berhasil dibuat.');
    }

    public function update(Request $request, Inbox $inbox): RedirectResponse
    {
        $this->ensureInboxAccess($request, $inbox);

        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'inbox_name' => ['required', 'string', 'max:255', Rule::unique('inboxes', 'inbox_name')->ignore($inbox->id)],
        ]);

        $user = $request->user();
        $normalized = strtolower(trim($data['inbox_name']));
        $slug = $normalized === $inbox->inbox_name
            ? $inbox->slug
            : $this->generateUniqueSlug($normalized, $inbox->id);
        $groupId = $user->isGroupAdmin() ? $user->group_id : (int) $data['group_id'];

        $inbox->forceFill([
            'group_id' => $groupId,
            'inbox_name' => $normalized,
            'slug' => $slug,
            'access_token' => Group::query()->whereKey($groupId)->value('viewer_token'),
        ])->save();

        return back()->with('status', 'Inbox berhasil diperbarui.');
    }

    public function destroy(Request $request, Inbox $inbox, EmailMaintenanceService $maintenance): RedirectResponse
    {
        $this->ensureInboxAccess($request, $inbox);
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

    protected function ensureInboxAccess(Request $request, Inbox $inbox): void
    {
        $user = $request->user();

        if ($user->isGroupAdmin()) {
            abort_unless($inbox->group_id === $user->group_id, 404);
        }
    }
}
