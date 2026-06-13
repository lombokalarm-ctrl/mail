<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $email->subject ?: '(Tanpa Subjek)' }} - APLI Mail</title>
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
                <p class="section-kicker">Email Detail</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-4xl">{{ $email->subject ?: '(Tanpa Subjek)' }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Inbox {{ $inbox->inbox_name }} • {{ $email->received_at?->format('d M Y H:i') }}</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('viewer.index', ['viewerKey' => $viewerKey], false) }}" class="btn-secondary px-4 py-2.5">Kembali ke Inbox</a>
                <button type="button" data-theme-toggle class="btn-primary px-4 py-2.5">Mode Gelap</button>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.55fr_0.85fr]">
            <section class="space-y-6">
                <div class="panel-card overflow-hidden p-0">
                    <div class="gmail-reader-toolbar">
                        <div class="gmail-reader-toolbar-left">
                            <span class="gmail-toolbar-dot" aria-hidden="true"></span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Reading pane</span>
                            @if ($email->attachments->isNotEmpty())
                                <span class="status-badge-amber">{{ $email->attachments->count() }} lampiran</span>
                            @endif
                        </div>

                        <div class="gmail-reader-toolbar-right">
                            <span class="status-badge-slate">{{ $email->received_at?->format('d M Y H:i') }}</span>
                            <button
                                type="button"
                                data-copy-text="{{ $email->sender_email }}"
                                data-copy-success="Alamat pengirim berhasil disalin."
                                class="btn-ghost px-3 py-2 text-xs"
                            >
                                Salin Pengirim
                            </button>
                        </div>
                    </div>

                    <div class="p-6 sm:p-7">
                        <div class="gmail-message-header">
                            <div class="gmail-message-avatar">
                                {{ strtoupper(\Illuminate\Support\Str::substr($email->sender_name ?: $email->sender_email, 0, 1)) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="truncate text-lg font-semibold text-slate-950 dark:text-white">{{ $email->sender_name ?: $email->sender_email }}</p>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $email->sender_email }}</p>
                                    </div>

                                    <div class="gmail-message-date">
                                        {{ $email->received_at?->format('d M Y H:i:s') }}
                                    </div>
                                </div>

                                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                    <div class="detail-pair">
                                        <p class="detail-pair-label">To</p>
                                        <p class="detail-pair-value">{{ $email->recipient_email }}</p>
                                    </div>
                                    <div class="detail-pair">
                                        <p class="detail-pair-label">Subject</p>
                                        <p class="detail-pair-value">{{ $email->subject ?: '(Tanpa Subjek)' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="gmail-message-body mt-8">
                            <h2 class="gmail-section-title">HTML Content</h2>
                            <div class="content-prose mt-5 rounded-[1.8rem] border border-slate-200/80 bg-white p-6 text-sm leading-7 text-slate-700 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-300">
                                @if ($email->body_html)
                                    {!! $email->body_html !!}
                                @else
                                    <p>Tidak ada konten HTML.</p>
                                @endif
                            </div>
                        </div>

                        <div class="gmail-message-body mt-8">
                            <h2 class="gmail-section-title">Text Content</h2>
                            <pre class="mt-5 whitespace-pre-wrap rounded-[1.8rem] border border-slate-200/80 bg-slate-50/85 p-6 text-sm leading-7 text-slate-700 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-300">{{ $email->body_text ?: 'Tidak ada konten teks.' }}</pre>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="panel-card">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Lampiran</h2>
                        <span class="status-badge-blue">{{ $email->attachments->count() }} file</span>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($email->attachments as $attachment)
                            <div class="gmail-attachment-card">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $attachment->filename }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($attachment->filesize / 1024, 1) }} KB • {{ $attachment->mime_type }}</p>
                                </div>
                                <a href="{{ route('attachments.download', ['attachment' => $attachment, 'viewer' => $viewerKey], false) }}" class="btn-primary px-4 py-2 text-xs">
                                    Download
                                </a>
                            </div>
                        @empty
                            <div class="empty-state p-6 text-left">
                                Email ini tidak memiliki lampiran.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="panel-card">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Metadata Cepat</h2>
                    <div class="mt-5 space-y-3">
                        <div class="detail-pair">
                            <p class="detail-pair-label">Inbox</p>
                            <p class="detail-pair-value">{{ $inbox->inbox_name }}</p>
                        </div>
                        <div class="detail-pair">
                            <p class="detail-pair-label">Viewer Path</p>
                            <p class="detail-pair-value break-all">{{ route('viewer.show', ['viewerKey' => $viewerKey, 'email' => $email], false) }}</p>
                        </div>
                        <div class="detail-pair">
                            <p class="detail-pair-label">Received</p>
                            <p class="detail-pair-value">{{ $email->received_at?->format('d M Y H:i:s') }}</p>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</body>
</html>
