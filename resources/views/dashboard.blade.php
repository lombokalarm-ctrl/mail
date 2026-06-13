<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="section-kicker">{{ $isSaasAdmin ? 'Admin Console' : 'Group Console' }}</p>
                <h2 class="section-title">{{ $isSaasAdmin ? 'Dashboard APLI Mail' : 'Dashboard Group '.($groupContext?->name ?? '') }}</h2>
                <p class="section-copy">
                    {{ $isSaasAdmin ? 'Ringkasan inbox, email masuk, lampiran, dan aktivitas 14 hari terakhir.' : 'Ringkasan inbox, email, lampiran, dan aktivitas group Anda selama 14 hari terakhir.' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @if ($isSaasAdmin)
                    <a href="{{ route('admin.groups.index', [], false) }}" class="btn-secondary px-4 py-2.5">Kelola Group</a>
                    <a href="{{ route('admin.users.index', [], false) }}" class="btn-secondary px-4 py-2.5">Kelola User</a>
                @endif
                <a href="{{ route('admin.inboxes.index', [], false) }}" class="btn-secondary px-4 py-2.5">Kelola Inbox</a>
                <a href="{{ route('admin.emails.index', [], false) }}" class="btn-primary px-4 py-2.5">Kelola Email</a>
            </div>
        </div>
    </x-slot>

    @php
        $maxChart = max($chartData->max('total'), 1);
    @endphp

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @if ($isSaasAdmin)
                <div class="metric-card">
                    <p class="metric-label">Total Group</p>
                    <p class="metric-value">{{ number_format($totalGroups) }}</p>
                    <p class="metric-hint">Customer SaaS aktif</p>
                </div>
            @endif
            <div class="metric-card">
                <p class="metric-label">Total Inbox</p>
                <p class="metric-value">{{ number_format($totalInboxes) }}</p>
                <p class="metric-hint">{{ $isSaasAdmin ? 'Inbox terdaftar ke group' : 'Inbox aktif di group Anda' }}</p>
            </div>
            <div class="metric-card">
                <p class="metric-label">Total Email</p>
                <p class="metric-value">{{ number_format($totalEmails) }}</p>
                <p class="metric-hint">{{ $isSaasAdmin ? 'Pesan tersimpan di database' : 'Pesan tersimpan untuk group Anda' }}</p>
            </div>
            <div class="metric-card">
                <p class="metric-label">Total Lampiran</p>
                <p class="metric-value">{{ number_format($totalAttachments) }}</p>
                <p class="metric-hint">{{ $isSaasAdmin ? 'File tersimpan di storage' : 'Lampiran pada email group Anda' }}</p>
            </div>
            <div class="metric-card">
                <p class="metric-label">Email Hari Ini</p>
                <p class="metric-value">{{ number_format($emailsToday) }}</p>
                <p class="metric-hint">Lalu lintas harian terbaru</p>
            </div>
        </div>

        <div class="quick-actions">
            @if ($isSaasAdmin)
                <a href="{{ route('admin.groups.index', [], false) }}" class="quick-action-card">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Group Manager</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Atur token viewer per customer dan kelola inbox SaaS tanpa API manual.</p>
                    </div>
                    <span class="status-badge-blue">{{ number_format($totalGroups) }}</span>
                </a>
                <a href="{{ route('admin.users.index', [], false) }}" class="quick-action-card">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">User Manager</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Buat akun admin group pelanggan dan reset password awal bila dibutuhkan.</p>
                    </div>
                    <span class="status-badge-blue">Akses</span>
                </a>
            @endif
            <a href="{{ route('admin.inboxes.index', [], false) }}" class="quick-action-card">
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Inbox Manager</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $isSaasAdmin ? 'Lihat inbox catch-all dan salin viewer URL lebih cepat.' : 'Kelola inbox yang terdaftar untuk group Anda.' }}</p>
                </div>
                <span class="status-badge-blue">{{ number_format($totalInboxes) }}</span>
            </a>
            <a href="{{ route('admin.emails.index', [], false) }}" class="quick-action-card">
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Email Manager</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $isSaasAdmin ? 'Tinjau email global, lampiran, dan aksi hapus dari satu halaman.' : 'Tinjau email yang masuk ke inbox group Anda.' }}</p>
                </div>
                <span class="status-badge-slate">{{ number_format($totalEmails) }}</span>
            </a>
            @if ($isSaasAdmin)
                <a href="{{ route('viewer.index', ['viewerKey' => 'ahmad-alhijrah-f7k29a'], false) }}" class="quick-action-card">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Sample Viewer</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Buka inbox demo publik untuk cek tampilan pengguna akhir.</p>
                    </div>
                    <span class="status-badge-emerald">Live</span>
                </a>
            @endif
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

                <div class="chart-scroll">
                    <div class="chart-grid">
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
                </div>
            </section>

            <section class="panel-card">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Inbox Terbaru</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $isSaasAdmin ? 'Inbox terbaru yang sudah dipetakan ke group pelanggan.' : 'Inbox terbaru yang terdaftar pada group Anda.' }}</p>
                    </div>
                    <a href="{{ route('admin.inboxes.index', [], false) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">Lihat semua</a>
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
                            Belum ada inbox. Tambahkan inbox ke group lebih dulu lewat API backend.
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
                <a href="{{ route('admin.emails.index', [], false) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">Buka daftar email</a>
            </div>

            <div class="mobile-card-grid">
                @forelse ($recentEmails as $email)
                    <article class="mobile-card">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $email->subject ?: '(Tanpa Subjek)' }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $email->inbox->inbox_name }} • {{ $email->received_at?->format('d M Y H:i') }}</p>
                            </div>
                            <span class="status-badge-slate">{{ $email->attachments->count() }}</span>
                        </div>

                        <div class="mt-4 grid gap-3">
                            <div class="detail-pair">
                                <p class="detail-pair-label">Sender</p>
                                <p class="detail-pair-value">{{ $email->sender_name ?: $email->sender_email }}</p>
                            </div>
                            <div class="detail-pair">
                                <p class="detail-pair-label">Preview</p>
                                <p class="detail-pair-value line-clamp-2">{{ \Illuminate\Support\Str::limit($email->body_text ?: strip_tags($email->body_html), 110) }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        Belum ada email yang tersimpan.
                    </div>
                @endforelse
            </div>

            <div class="mt-6 hidden overflow-x-auto md:block">
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
