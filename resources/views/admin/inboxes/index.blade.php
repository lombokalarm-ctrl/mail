<x-app-layout>
    @php
        $currentUser = auth()->user();
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="section-kicker">Inbox Manager</p>
                <h2 class="section-title">{{ $currentUser->isSaasAdmin() ? 'Daftar Inbox Catch-All' : 'Inbox Group Anda' }}</h2>
                <p class="section-copy">{{ $currentUser->isSaasAdmin() ? 'Kelola inbox yang terdaftar ke group SaaS dan buka viewer bertoken per group.' : 'Kelola inbox yang terdaftar ke group Anda dan buka viewer bertoken pelanggan.' }}</p>
            </div>

            <div class="flex flex-wrap gap-3">
                @if ($currentUser->isSaasAdmin())
                    <a href="{{ route('admin.groups.index', [], false) }}" class="btn-secondary px-4 py-2.5">Kelola Group</a>
                @endif
                <a href="{{ route('admin.emails.index', [], false) }}" class="btn-primary px-4 py-2.5">Lihat Email</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="panel-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Tambah Inbox</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $currentUser->isSaasAdmin() ? 'Tambahkan inbox baru dan hubungkan ke group pelanggan yang tepat.' : 'Tambahkan inbox baru untuk group Anda agar email catch-all bisa diterima.' }}</p>
                </div>
                <span class="status-badge-blue">Quick Create</span>
            </div>

            <form method="POST" action="{{ route('admin.inboxes.store', [], false) }}" class="mt-6 grid gap-4">
                @csrf

                <div class="grid gap-4 md:grid-cols-2">
                    @if ($currentUser->isSaasAdmin())
                        <div>
                            <label for="quick_group_id" class="detail-pair-label">Group</label>
                            <select id="quick_group_id" name="group_id" class="field-input mt-2" required>
                                <option value="">Pilih group...</option>
                                @foreach ($groupOptions as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="group_id" value="{{ $currentUser->group_id }}" />
                        <div>
                            <label class="detail-pair-label">Group</label>
                            <p class="field-input mt-2 !py-3">{{ $currentUser->group?->name }}</p>
                        </div>
                    @endif

                    <div>
                        <label for="quick_inbox_name" class="detail-pair-label">Inbox Name</label>
                        <input id="quick_inbox_name" type="text" name="inbox_name" class="field-input mt-2" placeholder="Contoh: support-acme" required />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary px-4 py-3">Tambah Inbox</button>
                </div>
            </form>
        </section>

        <section class="panel-card overflow-hidden">
        <div class="admin-toolbar">
            <form method="GET" class="flex flex-1 flex-col gap-4 md:flex-row">
                <div class="flex-1">
                    <label for="q" class="sr-only">Cari inbox</label>
                    <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Cari inbox atau slug..." class="field-input" />
                </div>
                <button type="submit" class="btn-primary">Cari Inbox</button>
            </form>

            <div class="admin-toolbar-meta">
                <span class="status-badge-blue">{{ $inboxes->total() }} inbox</span>
                @if ($search)
                    <span class="status-badge-slate">Filter: {{ $search }}</span>
                @endif
            </div>
        </div>

        <div class="mt-6 hidden overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/80 dark:border-slate-800/80 dark:bg-slate-950/50 md:block">
            <div class="gmail-toolbar">
                <div class="gmail-toolbar-left">
                    <span class="gmail-toolbar-dot" aria-hidden="true"></span>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $inboxes->count() }} inbox di halaman ini</span>
                    @if ($search)
                        <span class="status-badge-blue">Filter aktif: {{ $search }}</span>
                    @else
                        <span class="status-badge-slate">{{ $currentUser->isSaasAdmin() ? 'Catch-all mailbox' : 'Mailbox group' }}</span>
                    @endif
                </div>

                <div class="gmail-toolbar-right">
                    <span class="status-badge-slate">{{ $currentUser->isSaasAdmin() ? 'Inbox admin' : 'Inbox pelanggan' }}</span>
                </div>
            </div>

            <div class="gmail-table-header">
                <div>Status</div>
                <div>Inbox</div>
                <div>Email & Viewer</div>
                <div class="text-right">Dibuat</div>
            </div>

            <div class="divide-y divide-slate-200/80 dark:divide-slate-800/80">
                @forelse ($inboxes as $inbox)
                    <div class="gmail-row">
                        <div class="gmail-row-leading">
                            <span class="gmail-select-shell" aria-hidden="true"></span>
                            <span class="gmail-star-shell {{ $loop->first ? 'gmail-star-shell-active' : '' }}" aria-hidden="true"></span>
                        </div>

                        <div class="gmail-row-sender">
                            <p class="truncate font-semibold text-slate-900 dark:text-slate-100">{{ $inbox->inbox_name }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $inbox->inbox_name . '@' . config('apli_mail.domain') }}</p>
                        </div>

                        <div class="gmail-row-content">
                            <div class="min-w-0">
                                <div class="flex min-w-0 items-center gap-2">
                                    <p class="gmail-row-subject">{{ $inbox->group?->name ?: 'Group belum diatur' }}</p>
                                    <span class="status-badge-blue shrink-0 text-[10px]">{{ $inbox->emails_count }} email</span>
                                </div>
                                <p class="gmail-row-preview">{{ $inbox->viewer_url }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <div class="gmail-row-time {{ $loop->first ? 'gmail-row-time-active' : '' }}">
                                {{ $inbox->created_at?->format('d M Y H:i') }}
                            </div>
                            <button
                                type="button"
                                data-copy-text="{{ $inbox->viewer_url }}"
                                data-copy-success="Viewer URL inbox berhasil disalin."
                                class="btn-ghost px-3 py-2 text-xs"
                            >
                                Salin
                            </button>
                            <a href="{{ $inbox->viewer_url }}" target="_blank" class="btn-ghost px-3 py-2 text-xs">Buka</a>
                            <form method="POST" action="{{ route('admin.inboxes.destroy', $inbox, false) }}" onsubmit="return confirm('Hapus inbox beserta seluruh email dan lampirannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger px-3 py-2 text-xs">Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty-state m-4">
                        Belum ada inbox yang cocok dengan pencarian.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mobile-card-grid mt-6">
            @forelse ($inboxes as $inbox)
                <article class="mobile-card">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $inbox->inbox_name }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $inbox->inbox_name . '@' . config('apli_mail.domain') }}</p>
                        </div>
                        <span class="status-badge-blue">{{ $inbox->emails_count }} email</span>
                    </div>

                    <div class="mt-4 grid gap-3">
                        <div class="detail-pair">
                            <p class="detail-pair-label">Group</p>
                            <p class="detail-pair-value">{{ $inbox->group?->name ?: '-' }}</p>
                        </div>
                        <div class="detail-pair">
                            <p class="detail-pair-label">Viewer URL</p>
                            <p class="detail-pair-value break-all">{{ $inbox->viewer_url }}</p>
                        </div>
                        <div class="detail-pair">
                            <p class="detail-pair-label">Dibuat</p>
                            <p class="detail-pair-value">{{ $inbox->created_at?->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            data-copy-text="{{ $inbox->viewer_url }}"
                            data-copy-success="Viewer URL inbox berhasil disalin."
                            class="btn-secondary w-full px-4 py-2 text-xs"
                        >
                            Salin URL
                        </button>
                        <a href="{{ $inbox->viewer_url }}" target="_blank" class="btn-primary w-full px-4 py-2 text-xs">Buka</a>
                    </div>

                    <form method="POST" action="{{ route('admin.inboxes.destroy', $inbox, false) }}" onsubmit="return confirm('Hapus inbox beserta seluruh email dan lampirannya?')" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger w-full px-4 py-2 text-xs">Hapus Inbox</button>
                    </form>
                </article>
            @empty
                <div class="empty-state">
                    Belum ada inbox yang cocok dengan pencarian.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $inboxes->links() }}
        </div>
        </section>
    </div>
</x-app-layout>
