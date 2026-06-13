<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="section-kicker">Admin Console</p>
                <h2 class="section-title">Dashboard APLI Mail</h2>
                <p class="section-copy">Ringkasan inbox, email masuk, lampiran, dan aktivitas 14 hari terakhir.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.inboxes.index') }}" class="btn-secondary px-4 py-2.5">Kelola Inbox</a>
                <a href="{{ route('admin.emails.index') }}" class="btn-primary px-4 py-2.5">Kelola Email</a>
            </div>
        </div>
    </x-slot>

    @php
        $maxChart = max($chartData->max('total'), 1);
    @endphp

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="metric-card">
                <p class="metric-label">Total Inbox</p>
                <p class="metric-value">{{ number_format($totalInboxes) }}</p>
                <p class="metric-hint">Inbox catch-all aktif</p>
            </div>
            <div class="metric-card">
                <p class="metric-label">Total Email</p>
                <p class="metric-value">{{ number_format($totalEmails) }}</p>
                <p class="metric-hint">Pesan tersimpan di database</p>
            </div>
            <div class="metric-card">
                <p class="metric-label">Total Lampiran</p>
                <p class="metric-value">{{ number_format($totalAttachments) }}</p>
                <p class="metric-hint">File tersimpan di storage</p>
            </div>
            <div class="metric-card">
                <p class="metric-label">Email Hari Ini</p>
                <p class="metric-value">{{ number_format($emailsToday) }}</p>
                <p class="metric-hint">Lalu lintas harian terbaru</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
            <section class="panel-card">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Statistik Email per Hari</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Grafik 14 hari terakhir untuk memantau beban inbox.</p>
                    </div>
                    <span class="status-badge-blue">14 hari</span>
                </div>

                <div class="mt-8 grid grid-cols-7 gap-3 sm:grid-cols-14">
                    @foreach ($chartData as $point)
                        @php
                            $height = max(16, (int) (($point['total'] / $maxChart) * 180));
                        @endphp
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex h-52 w-full items-end rounded-3xl bg-slate-100 px-2 py-3 dark:bg-slate-900/80">
                                <div class="w-full rounded-2xl bg-gradient-to-t from-blue-600 to-sky-400" style="height: {{ $height }}px"></div>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $point['total'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $point['label'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="panel-card">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Inbox Terbaru</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inbox catch-all yang baru terbentuk otomatis.</p>
                    </div>
                    <a href="{{ route('admin.inboxes.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">Lihat semua</a>
                </div>

                <div class="mt-6 space-y-3">
                    @forelse ($recentInboxes as $inbox)
                        <div class="glass-banner border-slate-200/80 bg-white/80 p-4 shadow-none dark:border-slate-800/80 dark:bg-slate-950/50">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $inbox->inbox_name }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $inbox->viewer_url }}</p>
                                </div>
                                <span class="status-badge-blue">
                                    {{ $inbox->emails_count }} email
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="empty-state">
                            Belum ada inbox. Inbox akan dibuat otomatis saat email pertama masuk.
                        </p>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="panel-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Email Terbaru</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pantau pesan terbaru, pengirim, subjek, dan lampirannya.</p>
                </div>
                <a href="{{ route('admin.emails.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">Buka daftar email</a>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr class="table-head">
                            <th class="px-4 pb-2">Inbox</th>
                            <th class="px-4 pb-2">Subject</th>
                            <th class="px-4 pb-2">Sender</th>
                            <th class="px-4 pb-2">Waktu</th>
                            <th class="px-4 pb-2 text-right">Lampiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentEmails as $email)
                            <tr class="table-row">
                                <td class="table-cell">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $email->inbox->inbox_name }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $email->recipient_email }}</p>
                                </td>
                                <td class="table-cell">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $email->subject ?: '(Tanpa Subjek)' }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($email->body_text ?: strip_tags($email->body_html), 90) }}</p>
                                </td>
                                <td class="table-cell">
                                    <p class="font-medium text-slate-900 dark:text-white">{{ $email->sender_name ?: $email->sender_email }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $email->sender_email }}</p>
                                </td>
                                <td class="table-cell text-slate-600 dark:text-slate-300">
                                    {{ $email->received_at?->format('d M Y H:i') }}
                                </td>
                                <td class="table-cell text-right">
                                    <span class="status-badge-slate">
                                        {{ $email->attachments->count() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">
                                    Belum ada email yang tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
