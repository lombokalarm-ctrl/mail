<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Inbox;
use App\Services\EmailMaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GroupInboxController extends Controller
{
    public function store(Request $request, Group $group): JsonResponse
    {
        $data = $request->validate([
            'inbox_name' => ['required', 'string', 'max:255', Rule::unique('inboxes', 'inbox_name')],
        ]);

        $normalized = strtolower(trim($data['inbox_name']));
        $slug = $this->generateUniqueSlug($normalized);

        $inbox = Inbox::query()->create([
            'group_id' => $group->id,
            'inbox_name' => $normalized,
            'slug' => $slug,
        ]);

        $inbox->load('group');

        return response()->json([
            'message' => 'Inbox berhasil ditambahkan ke group.',
            'data' => [
                'id' => $inbox->id,
                'group_id' => $group->id,
                'inbox_name' => $inbox->inbox_name,
                'slug' => $inbox->slug,
                'viewer_url' => $inbox->viewer_url,
            ],
        ], 201);
    }

    public function destroy(Group $group, Inbox $inbox, EmailMaintenanceService $maintenance): JsonResponse
    {
        abort_unless($inbox->group_id === $group->id, 404);

        $maintenance->deleteInbox($inbox);

        return response()->json([
            'message' => 'Inbox group berhasil dihapus.',
        ]);
    }

    protected function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name, '-');

        if ($slug === '') {
            $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-') ?: strtolower(Str::random(8));
        }

        $candidate = $slug;
        $suffix = 1;

        while (Inbox::query()->where('slug', $candidate)->exists()) {
            $candidate = "{$slug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
