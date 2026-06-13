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

        <div class="grid gap-6 xl:grid-cols-[0.9fr_1.5fr]">
            <aside class="space-y-6">
                <section class="panel-card">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Metadata</h2>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">From</dt>
                            <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $email->sender_name ?: $email->sender_email }}</dd>
                            <dd class="text-slate-500 dark:text-slate-400">{{ $email->sender_email }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">To</dt>
                            <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $email->recipient_email }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Subject</dt>
                            <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $email->subject ?: '(Tanpa Subjek)' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Received Time</dt>
                            <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $email->received_at?->format('d M Y H:i:s') }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="panel-card">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Lampiran</h2>
                        <span class="status-badge-blue">{{ $email->attachments->count() }} file</span>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($email->attachments as $attachment)
                            <div class="glass-banner border-slate-200/80 bg-white/80 p-4 shadow-none dark:border-slate-800/80 dark:bg-slate-950/50">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $attachment->filename }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($attachment->filesize / 1024, 1) }} KB • {{ $attachment->mime_type }}</p>
                                <a href="{{ route('attachments.download', ['attachment' => $attachment, 'viewer' => $viewerKey], false) }}" class="btn-primary mt-3 px-4 py-2 text-xs">
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
            </aside>

            <section class="space-y-6">
                <div class="panel-card">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">HTML Content</h2>
                    <div class="content-prose mt-5 rounded-[1.8rem] border border-slate-200/80 bg-white p-6 text-sm leading-7 text-slate-700 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-300">
                        @if ($email->body_html)
                            {!! $email->body_html !!}
                        @else
                            <p>Tidak ada konten HTML.</p>
                        @endif
                    </div>
                </div>

                <div class="panel-card">
                    <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Text Content</h2>
                    <pre class="mt-5 whitespace-pre-wrap rounded-[1.8rem] border border-slate-200/80 bg-slate-50/85 p-6 text-sm leading-7 text-slate-700 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-300">{{ $email->body_text ?: 'Tidak ada konten teks.' }}</pre>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
