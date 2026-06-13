<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Services\AdminUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $role = trim((string) $request->string('role'));
        $groupId = $request->integer('group_id');
        $status = trim((string) $request->string('status'));

        $users = User::query()
            ->with('group')
            ->where('role', User::ROLE_GROUP_ADMIN)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($groupId > 0, fn ($query) => $query->where('group_id', $groupId))
            ->when($status !== '', function ($query) use ($status): void {
                if ($status === 'active') {
                    $query->where('is_active', true);
                }

                if ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'groups' => Group::query()->orderBy('name')->get(['id', 'name']),
            'search' => $search,
            'role' => User::ROLE_GROUP_ADMIN,
            'groupId' => $groupId > 0 ? $groupId : null,
            'status' => $status,
            'roleOptions' => [
                User::ROLE_GROUP_ADMIN => 'Admin Group',
            ],
        ]);
    }

    public function store(Request $request, AdminUserService $userService): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in([User::ROLE_GROUP_ADMIN])],
            'group_id' => ['required', 'exists:groups,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'must_change_password' => ['nullable', 'boolean'],
        ]);

        $userService->createManagedUser([
            ...$data,
            'must_change_password' => (bool) ($data['must_change_password'] ?? true),
            'is_active' => true,
        ]);

        return back()->with('status', 'User admin group berhasil dibuat.');
    }

    public function update(Request $request, User $user, AdminUserService $userService): RedirectResponse
    {
        $this->ensureManagedUser($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in([User::ROLE_GROUP_ADMIN])],
            'group_id' => ['required', 'exists:groups,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $userService->updateManagedUser($user, $data);

        return back()->with('status', 'User admin group berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user, AdminUserService $userService): RedirectResponse
    {
        $this->ensureManagedUser($user);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $userService->resetPassword($user, $data['password']);

        return back()->with('status', 'Password user berhasil direset dan wajib diganti saat login.');
    }

    public function destroy(User $user, AdminUserService $userService): RedirectResponse
    {
        $this->ensureManagedUser($user);
        $userService->deleteManagedUser($user);

        return back()->with('status', 'User admin group berhasil dihapus.');
    }

    protected function ensureManagedUser(User $user): void
    {
        abort_unless($user->isGroupAdmin(), 404);
    }
}
