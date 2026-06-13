<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\EmailMaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->string('q'));

        $groups = Group::query()
            ->withCount('inboxes')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('viewer_token', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);

        $groups->getCollection()->transform(fn (Group $group) => $this->transformGroup($group, false));

        return response()->json($groups);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'viewer_token' => ['required', 'string', 'max:64', 'alpha_num', Rule::unique('groups', 'viewer_token')],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $group = Group::query()->create([
            'name' => $data['name'],
            'viewer_token' => strtolower($data['viewer_token']),
            'status' => $data['status'] ?? 'active',
        ]);
        $group->loadCount('inboxes');

        return response()->json([
            'message' => 'Group berhasil dibuat.',
            'data' => $this->transformGroup($group, true),
        ], 201);
    }

    public function show(Group $group): JsonResponse
    {
        $group->loadCount('inboxes');
        $group->load(['inboxes' => fn ($query) => $query->with('group')->withCount('emails')->latest()]);

        return response()->json([
            'data' => $this->transformGroup($group, true),
        ]);
    }

    public function update(Request $request, Group $group): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'viewer_token' => ['sometimes', 'required', 'string', 'max:64', 'alpha_num', Rule::unique('groups', 'viewer_token')->ignore($group->id)],
            'status' => ['sometimes', 'required', 'string', 'max:50'],
        ]);

        $group->fill([
            'name' => $data['name'] ?? $group->name,
            'viewer_token' => isset($data['viewer_token']) ? strtolower($data['viewer_token']) : $group->viewer_token,
            'status' => $data['status'] ?? $group->status,
        ])->save();

        $group->loadCount('inboxes');

        return response()->json([
            'message' => 'Group berhasil diperbarui.',
            'data' => $this->transformGroup($group, true),
        ]);
    }

    public function destroy(Group $group, EmailMaintenanceService $maintenance): JsonResponse
    {
        $maintenance->deleteGroup($group);

        return response()->json([
            'message' => 'Group berhasil dihapus.',
        ]);
    }

    protected function transformGroup(Group $group, bool $includeInboxes): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'viewer_token' => $group->viewer_token,
            'status' => $group->status,
            'inboxes_count' => $group->inboxes_count,
            'created_at' => optional($group->created_at)->toAtomString(),
            'updated_at' => optional($group->updated_at)->toAtomString(),
            'inboxes' => $includeInboxes
                ? $group->inboxes->map(fn ($inbox) => [
                    'id' => $inbox->id,
                    'inbox_name' => $inbox->inbox_name,
                    'slug' => $inbox->slug,
                    'viewer_url' => $inbox->viewer_url,
                    'emails_count' => $inbox->emails_count ?? $inbox->emails()->count(),
                ])
                : [],
        ];
    }
}
