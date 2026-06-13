<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdminUserService
{
    public function createManagedUser(array $data): User
    {
        $payload = $this->normalizePayload($data, true);

        return DB::transaction(fn () => User::query()->create($payload));
    }

    public function updateManagedUser(User $user, array $data): User
    {
        $payload = $this->normalizePayload([
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

    protected function normalizePayload(array $data, bool $isCreate): array
    {
        $role = $data['role'] ?? User::ROLE_GROUP_ADMIN;
        $groupId = $data['group_id'] ?? null;

        if ($role === User::ROLE_GROUP_ADMIN && ! $groupId) {
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
