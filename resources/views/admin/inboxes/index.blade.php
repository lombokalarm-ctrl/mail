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
        <form method="GET" class="flex flex-col gap-4 md:flex-row">
            <div class="flex-1">
                <label for="q" class="sr-only">Cari inbox</label>
                <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Cari inbox atau slug..." class="field-input" />
            </div>
            <button type="submit" class="btn-primary">Cari Inbox</button>
        </form>

        <div class="mt-6 overflow-x-auto">
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
                                <a href="{{ $inbox->viewer_url }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-500">{{ $inbox->viewer_url }}</a>
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
