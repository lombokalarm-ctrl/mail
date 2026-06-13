<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="section-kicker">User Manager</p>
                <h2 class="section-title">Kelola User Admin Group</h2>
                <p class="section-copy">Buat akun admin pelanggan, atur group yang diakses, reset password awal, dan kelola status akun berlangganan.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.groups.index', [], false) }}" class="btn-secondary px-4 py-2.5">Kelola Group</a>
                <a href="{{ route('dashboard', [], false) }}" class="btn-primary px-4 py-2.5">Kembali Ke Dashboard</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($errors->any())
            <div class="glass-banner border-rose-200/80 bg-rose-50/85 text-sm text-rose-800 shadow-none dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-200">
                <p class="font-semibold">Ada input user yang perlu diperbaiki.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <section class="panel-card">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Buat Admin Group Baru</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Akun baru langsung aktif dan dapat dipaksa ganti password saat login pertama.</p>
                    </div>
                    <span class="status-badge-blue">{{ $users->total() }} user</span>
                </div>

                <form method="POST" action="{{ route('admin.users.store', [], false) }}" class="mt-6 grid gap-4">
                    @csrf
                    <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_GROUP_ADMIN }}" />

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="user_name" class="detail-pair-label">Nama User</label>
                            <input id="user_name" type="text" name="name" value="{{ old('name') }}" class="field-input mt-2" placeholder="Contoh: Admin Acme" required />
                        </div>
                        <div>
                            <label for="user_email" class="detail-pair-label">Email Login</label>
                            <input id="user_email" type="email" name="email" value="{{ old('email') }}" class="field-input mt-2" placeholder="admin@acme.test" required />
                        </div>
                    </div>

                    <div>
                        <label for="user_group_id" class="detail-pair-label">Group Pelanggan</label>
                        <select id="user_group_id" name="group_id" class="field-input mt-2" required>
                            <option value="">Pilih group...</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" @selected((string) old('group_id') === (string) $group->id)>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="user_password" class="detail-pair-label">Password Awal</label>
                            <input id="user_password" type="password" name="password" class="field-input mt-2" required />
                        </div>
                        <div>
                            <label for="user_password_confirmation" class="detail-pair-label">Konfirmasi Password</label>
                            <input id="user_password_confirmation" type="password" name="password_confirmation" class="field-input mt-2" required />
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-3 rounded-[1.4rem] border border-slate-200/80 bg-slate-50/70 px-4 py-3 text-sm text-slate-700 dark:border-slate-800/80 dark:bg-slate-900/60 dark:text-slate-200">
                        <input type="checkbox" name="must_change_password" value="1" class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked(old('must_change_password', '1') === '1') />
                        <span>Wajib ganti password saat login pertama</span>
                    </label>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary px-4 py-3">Simpan User</button>
                    </div>
                </form>
            </section>

            <section class="panel-card">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Filter User</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Cari user berdasarkan nama, email login, group, role, atau status akun.</p>
                    </div>
                    <span class="status-badge-slate">Admin SaaS</span>
                </div>

                <form method="GET" class="mt-6 grid gap-4">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label for="q" class="detail-pair-label">Cari</label>
                            <input id="q" type="search" name="q" value="{{ $search }}" class="field-input mt-2" placeholder="Nama atau email user..." />
                        </div>
                        <div>
                            <label for="group_filter" class="detail-pair-label">Group</label>
                            <select id="group_filter" name="group_id" class="field-input mt-2">
                                <option value="">Semua group</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}" @selected((string) $groupId === (string) $group->id)>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label for="role_filter" class="detail-pair-label">Role</label>
                            <select id="role_filter" name="role" class="field-input mt-2">
                                <option value="">Semua role</option>
                                @foreach ($roleOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="status_filter" class="detail-pair-label">Status</label>
                            <select id="status_filter" name="status" class="field-input mt-2">
                                <option value="">Semua status</option>
                                <option value="active" @selected($status === 'active')>Active</option>
                                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary px-4 py-3">Terapkan Filter</button>
                    </div>
                </form>
            </section>
        </div>

        <section class="panel-card overflow-hidden">
            <div class="admin-toolbar">
                <div class="admin-toolbar-meta">
                    <span class="status-badge-blue">{{ $users->total() }} user</span>
                    @if ($search)
                        <span class="status-badge-slate">Cari: {{ $search }}</span>
                    @endif
                    @if ($status)
                        <span class="status-badge-slate">Status: {{ strtoupper($status) }}</span>
                    @endif
                </div>
            </div>

            <div class="mt-6 space-y-6">
                @forelse ($users as $managedUser)
                    <article class="rounded-[2rem] border border-slate-200/80 bg-white/80 p-5 shadow-sm dark:border-slate-800/80 dark:bg-slate-950/50 sm:p-6">
                        <div class="grid gap-6 xl:grid-cols-[1fr_0.95fr]">
                            <div class="space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="status-badge-blue">{{ $roleOptions[$managedUser->role] ?? $managedUser->role }}</span>
                                    <span class="status-badge-slate">{{ $managedUser->is_active ? 'ACTIVE' : 'INACTIVE' }}</span>
                                    @if ($managedUser->must_change_password)
                                        <span class="status-badge-amber">WAJIB GANTI PASSWORD</span>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('admin.users.update', $managedUser, false) }}" class="grid gap-4">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_GROUP_ADMIN }}" />

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label for="managed-user-name-{{ $managedUser->id }}" class="detail-pair-label">Nama User</label>
                                            <input id="managed-user-name-{{ $managedUser->id }}" type="text" name="name" value="{{ $managedUser->name }}" class="field-input mt-2" required />
                                        </div>
                                        <div>
                                            <label for="managed-user-email-{{ $managedUser->id }}" class="detail-pair-label">Email Login</label>
                                            <input id="managed-user-email-{{ $managedUser->id }}" type="email" name="email" value="{{ $managedUser->email }}" class="field-input mt-2" required />
                                        </div>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label for="managed-user-group-{{ $managedUser->id }}" class="detail-pair-label">Group</label>
                                            <select id="managed-user-group-{{ $managedUser->id }}" name="group_id" class="field-input mt-2" required>
                                                @foreach ($groups as $group)
                                                    <option value="{{ $group->id }}" @selected($managedUser->group_id === $group->id)>{{ $group->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="managed-user-status-{{ $managedUser->id }}" class="detail-pair-label">Status</label>
                                            <select id="managed-user-status-{{ $managedUser->id }}" name="is_active" class="field-input mt-2" required>
                                                <option value="1" @selected($managedUser->is_active)>Active</option>
                                                <option value="0" @selected(! $managedUser->is_active)>Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <button type="submit" class="btn-primary px-4 py-2.5 text-sm">Simpan User</button>
                                    </div>
                                </form>
                            </div>

                            <div class="space-y-4">
                                <div class="rounded-[1.6rem] border border-slate-200/80 bg-slate-50/70 p-4 dark:border-slate-800/80 dark:bg-slate-900/70">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Reset Password</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Atur password baru dan paksa user menggantinya lagi saat login.</p>

                                    <form method="POST" action="{{ route('admin.users.reset-password', $managedUser, false) }}" class="mt-4 grid gap-3">
                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <label for="reset-password-{{ $managedUser->id }}" class="detail-pair-label">Password Baru</label>
                                            <input id="reset-password-{{ $managedUser->id }}" type="password" name="password" class="field-input mt-2" required />
                                        </div>

                                        <div>
                                            <label for="reset-password-confirmation-{{ $managedUser->id }}" class="detail-pair-label">Konfirmasi Password</label>
                                            <input id="reset-password-confirmation-{{ $managedUser->id }}" type="password" name="password_confirmation" class="field-input mt-2" required />
                                        </div>

                                        <div>
                                            <button type="submit" class="btn-secondary px-4 py-2.5 text-sm">Reset Password</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="rounded-[1.6rem] border border-slate-200/80 bg-slate-50/70 p-4 text-sm text-slate-600 dark:border-slate-800/80 dark:bg-slate-900/70 dark:text-slate-300">
                                    <p><span class="font-semibold text-slate-900 dark:text-white">Group:</span> {{ $managedUser->group?->name ?: '-' }}</p>
                                    <p class="mt-2"><span class="font-semibold text-slate-900 dark:text-white">Dibuat:</span> {{ $managedUser->created_at?->format('d M Y H:i') }}</p>
                                    <p class="mt-2"><span class="font-semibold text-slate-900 dark:text-white">Update terakhir:</span> {{ $managedUser->updated_at?->format('d M Y H:i') }}</p>
                                </div>

                                <form method="POST" action="{{ route('admin.users.destroy', $managedUser, false) }}" onsubmit="return confirm('Hapus user admin group ini secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger px-4 py-2.5 text-sm">Hapus User</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        Belum ada user admin group yang cocok dengan filter saat ini.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
