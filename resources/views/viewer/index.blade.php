<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $inbox->inbox_name }} - APLI Mail Viewer</title>
    <script>
        if (localStorage.getItem('apli-theme') === 'dark' || (! localStorage.getItem('apli-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="mx-auto min-h-screen max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="page-hero mb-6 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Inbox Viewer</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-4xl">{{ $inbox->inbox_name }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $inbox->inbox_name . '@' . config('apli_mail.domain') }}</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <span class="status-badge-blue">{{ $emails->total() }} email</span>
                    <span class="status-badge-slate">Group: {{ $inbox->group?->name ?: '-' }}</span>
                    <span class="status-badge-slate">Token: {{ $inbox->access_token }}</span>
                </div>
            </div>

            <div class="toolbar-group">
                <button type="button" data-theme-toggle class="btn-secondary px-4 py-2.5">Mode Gelap</button>
                <button
                    type="button"
                    data-copy-text="{{ $inbox->inbox_name . '@' . config('apli_mail.domain') }}"
                    data-copy-success="Alamat inbox berhasil disalin."
                    class="btn-primary px-4 py-2.5"
                >
                    Salin Alamat
                </button>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[0.95fr_1.45fr]">
            <aside class="panel-card xl:sticky xl:top-6 xl:h-fit">
                <form method="GET" class="space-y-4">
                    <div>
                        <label for="q" class="field-label">Cari email</label>
                        <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Subject, sender, isi email..." class="field-input mt-2" />
                    </div>
                    <button type="submit" class="btn-primary w-full">Cari</button>
                </form>

                <div class="mt-8 space-y-4">
                    <div class="glass-banner border-slate-200/80 bg-white/80 shadow-none dark:border-slate-800/80 dark:bg-slate-950/50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Viewer URL</p>
                                <p class="mt-2 break-all text-sm text-slate-700 dark:text-slate-300">{{ route('viewer.index', ['viewerKey' => $viewerKey], false) }}</p>
                            </div>
                            <button
                                type="button"
                                data-copy-text="{{ route('viewer.index', ['viewerKey' => $viewerKey], false) }}"
                                data-copy-success="Viewer URL berhasil disalin."
                                class="btn-secondary shrink-0 px-3 py-2 text-xs"
                            >
                                Salin
                            </button>
                        </div>
                    </div>
                    <div class="glass-banner border-slate-200/80 bg-white/80 shadow-none dark:border-slate-800/80 dark:bg-slate-950/50">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Urutan</p>
                        <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">Email terbaru selalu tampil paling atas.</p>
                    </div>
                </div>
            </aside>

            <section class="panel-card overflow-hidden">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Daftar Email</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tampilan inbox ringkas seperti mailbox modern, dengan pengirim, subject, preview, dan waktu di satu baris.</p>
                    </div>
                </div>

                <div class="hidden overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/80 dark:border-slate-800/80 dark:bg-slate-950/50 md:block">
                    <div class="gmail-toolbar">
                        <div class="gmail-toolbar-left">
                            <span class="gmail-toolbar-dot" aria-hidden="true"></span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $emails->count() }} email di halaman ini</span>
                            @if ($search)
                                <span class="status-badge-blue">Filter aktif: {{ $search }}</span>
                            @else
                                <span class="status-badge-slate">Mode inbox ringkas</span>
                            @endif
                        </div>

                        <div class="gmail-toolbar-right">
                            <span class="status-badge-slate">Urut terbaru</span>
                            <a href="{{ route('viewer.index', ['viewerKey' => $viewerKey, 'q' => $search], false) }}" class="btn-ghost px-3 py-2 text-xs">Muat Ulang</a>
                        </div>
                    </div>

                    <div class="gmail-table-header">
                        <div>Status</div>
                        <div>Pengirim</div>
                        <div>Email & Ringkasan</div>
                        <div class="text-right">Waktu</div>
                    </div>

                    <div class="divide-y divide-slate-200/80 dark:divide-slate-800/80">
                        @forelse ($emails as $email)
                            <a href="{{ route('viewer.show', ['viewerKey' => $viewerKey, 'email' => $email], false) }}" class="gmail-row">
                                <div class="gmail-row-leading">
                                    <span class="gmail-select-shell" aria-hidden="true"></span>
                                    <span class="gmail-star-shell {{ $loop->first ? 'gmail-star-shell-active' : '' }}" aria-hidden="true"></span>
                                </div>

                                <div class="gmail-row-sender">
                                    <p class="truncate font-semibold text-slate-900 dark:text-slate-100">{{ $email->sender_name ?: $email->sender_email }}</p>
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $email->sender_email }}</p>
                                </div>

                                <div class="gmail-row-content">
                                    <div class="min-w-0">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <p class="gmail-row-subject">{{ $email->subject ?: '(Tanpa Subjek)' }}</p>
                                            @if ($email->attachments->isNotEmpty())
                                                <span class="status-badge-amber shrink-0 text-[10px]">{{ $email->attachments->count() }} lampiran</span>
                                            @endif
                                        </div>
                                        <p class="gmail-row-preview">
                                            {{ \Illuminate\Support\Str::limit($email->body_text ?: strip_tags($email->body_html), 180) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="gmail-row-time {{ $loop->first ? 'gmail-row-time-active' : '' }}">
                                    {{ $email->received_at?->format('d M Y H:i') }}
                                </div>
                            </a>
                        @empty
                            <div class="empty-state m-4">
                                Belum ada email pada inbox ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-3 md:hidden">
                    @forelse ($emails as $email)
                        <a href="{{ route('viewer.show', ['viewerKey' => $viewerKey, 'email' => $email], false) }}" class="block rounded-[1.8rem] border border-white/70 bg-white/90 p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-[0_18px_40px_-26px_rgba(59,130,246,0.42)] dark:border-slate-800 dark:bg-slate-950/60 dark:hover:border-blue-800 dark:hover:bg-slate-900">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="truncate text-base font-semibold text-slate-950 dark:text-white">{{ $email->subject ?: '(Tanpa Subjek)' }}</p>
                                        @if ($email->attachments->isNotEmpty())
                                            <span class="status-badge-amber text-[11px]">Lampiran</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-300">{{ $email->sender_name ?: $email->sender_email }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $email->sender_email }}</p>
                                    <p class="mt-3 line-clamp-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                                        {{ \Illuminate\Support\Str::limit($email->body_text ?: strip_tags($email->body_html), 180) }}
                                    </p>
                                </div>
                                <div class="shrink-0 text-sm text-slate-500 dark:text-slate-400">
                                    {{ $email->received_at?->format('d M Y H:i') }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state p-10">
                            Belum ada email pada inbox ini.
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $emails->links() }}
                </div>
            </section>
        </div>
    </div>
</body>
</html>
