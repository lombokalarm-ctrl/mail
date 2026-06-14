<?php

namespace App\Services;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdminUserService
{
    public function createManagedGroupAdmin(array $data): User
    {
        $groupPayload = $this->normalizeGroupPayload($data);
        $userPayload = $this->normalizeUserPayload([
            ...$data,
            'role' => User::ROLE_GROUP_ADMIN,
        ], true);

        return DB::transaction(function () use ($groupPayload, $userPayload): User {
            $group = Group::query()->create($groupPayload);

            return User::query()->create([
                ...$userPayload,
                'group_id' => $group->id,
            ]);
        });
    }

    public function updateManagedUser(User $user, array $data): User
    {
        $payload = $this->normalizeUserPayload([
            ...$data,
            'must_change_password' => $data['must_change_password'] ?? $user->must_change_password,
        ], false);

        $user->update($payload);

        return $user->refresh();
    }

    public function resetPassword(User $user, string $password): User
    {
        $user->update([
            'password' => $password,
            'must_change_password' => true,
        ]);

        return $user->refresh();
    }

    public function deleteManagedUser(User $user): void
    {
        $user->delete();
    }

    protected function normalizeGroupPayload(array $data): array
    {
        return [
            'name' => trim((string) $data['group_name']),
            'viewer_token' => strtolower(trim((string) $data['viewer_token'])),
            'status' => trim((string) ($data['group_status'] ?? 'active')),
        ];
    }

    protected function normalizeUserPayload(array $data, bool $isCreate): array
    {
        $role = $data['role'] ?? User::ROLE_GROUP_ADMIN;
        $groupId = $data['group_id'] ?? null;

        $willCreateGroup = $isCreate && array_key_exists('group_name', $data) && array_key_exists('viewer_token', $data);

        if ($role === User::ROLE_GROUP_ADMIN && ! $groupId && ! $willCreateGroup) {
            throw new InvalidArgumentException('Admin group wajib terkait ke satu group.');
        }

        if ($role === User::ROLE_SAAS_ADMIN) {
            $groupId = null;
        }

        $payload = [
            'name' => trim((string) $data['name']),
            'email' => strtolower(trim((string) $data['email'])),
            'role' => $role,
            'group_id' => $groupId ? (int) $groupId : null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'must_change_password' => (bool) ($data['must_change_password'] ?? false),
        ];

        if ($isCreate) {
            $payload['password'] = (string) $data['password'];
            $payload['email_verified_at'] = Carbon::now();
        }

        return $payload;
    }
}
