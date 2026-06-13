<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="section-kicker">Inbox Manager</p>
                <h2 class="section-title">Daftar Inbox Catch-All</h2>
                <p class="section-copy">Cari inbox yang dibuat otomatis dari email masuk ke `email.apli.my.id`.</p>
            </div>
        </div>
    </x-slot>

    <section class="panel-card">
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

                    <form method="POST" action="{{ route('admin.inboxes.destroy', $inbox) }}" onsubmit="return confirm('Hapus inbox beserta seluruh email dan lampirannya?')" class="mt-2">
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

        <div class="mt-6 hidden overflow-x-auto md:block">
            <table class="data-table">
                <thead>
                    <tr class="table-head">
                        <th class="px-4 pb-2">Inbox</th>
                        <th class="px-4 pb-2">Viewer URL</th>
                        <th class="px-4 pb-2">Jumlah Email</th>
                        <th class="px-4 pb-2">Dibuat</th>
                        <th class="px-4 pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inboxes as $inbox)
                        <tr class="table-row">
                            <td class="table-cell">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $inbox->inbox_name }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $inbox->inbox_name . '@' . config('apli_mail.domain') }}</p>
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    <a href="{{ $inbox->viewer_url }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-500">{{ $inbox->viewer_url }}</a>
                                    <button
                                        type="button"
                                        data-copy-text="{{ $inbox->viewer_url }}"
                                        data-copy-success="Viewer URL inbox berhasil disalin."
                                        class="btn-secondary shrink-0 px-3 py-2 text-xs"
                                    >
                                        Salin
                                    </button>
                                </div>
                            </td>
                            <td class="table-cell">
                                <span class="status-badge-blue">
                                    {{ $inbox->emails_count }} email
                                </span>
                            </td>
                            <td class="table-cell text-slate-600 dark:text-slate-300">
                                {{ $inbox->created_at?->format('d M Y H:i') }}
                            </td>
                            <td class="table-cell text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ $inbox->viewer_url }}" target="_blank" class="btn-secondary px-4 py-2 text-xs">Buka</a>
                                    <form method="POST" action="{{ route('admin.inboxes.destroy', $inbox) }}" onsubmit="return confirm('Hapus inbox beserta seluruh email dan lampirannya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger px-4 py-2 text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                Belum ada inbox yang cocok dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $inboxes->links() }}
        </div>
    </section>
</x-app-layout>
