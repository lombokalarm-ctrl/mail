<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Email Manager</p>
            <h2 class="section-title">Daftar Email Global</h2>
            <p class="section-copy">Cari email berdasarkan subject, sender, atau penerima, lalu hapus bila diperlukan.</p>
        </div>
    </x-slot>

    <section class="panel-card overflow-hidden">
        <div class="admin-toolbar">
            <form method="GET" class="flex flex-1 flex-col gap-4 md:flex-row">
                <div class="flex-1">
                    <label for="q" class="sr-only">Cari email</label>
                    <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Cari subject, sender, atau recipient..." class="field-input" />
                </div>
                <button type="submit" class="btn-primary">Cari Email</button>
            </form>

            <div class="admin-toolbar-meta">
                <span class="status-badge-blue">{{ $emails->total() }} email</span>
                @if ($search)
                    <span class="status-badge-slate">Filter: {{ $search }}</span>
                @endif
            </div>
        </div>

        <div class="mt-6 hidden overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white/80 dark:border-slate-800/80 dark:bg-slate-950/50 md:block">
            <div class="gmail-toolbar">
                <div class="gmail-toolbar-left">
                    <span class="gmail-toolbar-dot" aria-hidden="true"></span>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $emails->count() }} email di halaman ini</span>
                    @if ($search)
                        <span class="status-badge-blue">Filter aktif: {{ $search }}</span>
                    @else
                        <span class="status-badge-slate">Global mailbox</span>
                    @endif
                </div>

                <div class="gmail-toolbar-right">
                    <span class="status-badge-slate">Admin view</span>
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
                    <div class="gmail-row">
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
                                    <span class="status-badge-blue shrink-0 text-[10px]">{{ $email->inbox->inbox_name }}</span>
                                    @if ($email->attachments->isNotEmpty())
                                        <span class="status-badge-amber shrink-0 text-[10px]">{{ $email->attachments->count() }} lampiran</span>
                                    @endif
                                </div>
                                <p class="gmail-row-preview">
                                    {{ $email->recipient_email }} • {{ \Illuminate\Support\Str::limit($email->body_text ?: strip_tags($email->body_html), 170) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <div class="gmail-row-time {{ $loop->first ? 'gmail-row-time-active' : '' }}">
                                {{ $email->received_at?->format('d M Y H:i') }}
                            </div>
                            <a href="{{ route('viewer.show', ['viewerKey' => $email->inbox->viewer_key, 'email' => $email], false) }}" target="_blank" class="btn-ghost px-3 py-2 text-xs">Buka</a>
                            <form method="POST" action="{{ route('admin.emails.destroy', $email, false) }}" onsubmit="return confirm('Hapus email dan seluruh lampirannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger px-3 py-2 text-xs">Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty-state m-4">
                        Tidak ada email yang cocok dengan pencarian.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-6 space-y-4 md:hidden">
            @forelse ($emails as $email)
                <article class="glass-banner border-slate-200/80 bg-white/80 p-5 dark:border-slate-800/80 dark:bg-slate-950/50">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="status-badge-blue">{{ $email->inbox->inbox_name }}</span>
                                <span class="status-badge-slate">{{ $email->received_at?->format('d M Y H:i') }}</span>
                                @if ($email->attachments->isNotEmpty())
                                    <span class="status-badge-amber">{{ $email->attachments->count() }} lampiran</span>
                                @endif
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $email->subject ?: '(Tanpa Subjek)' }}</h3>
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                    Dari <span class="font-medium">{{ $email->sender_name ?: $email->sender_email }}</span>
                                    ke <span class="font-medium">{{ $email->recipient_email }}</span>
                                </p>
                                <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500 dark:text-slate-400">
                                    {{ \Illuminate\Support\Str::limit($email->body_text ?: strip_tags($email->body_html), 200) }}
                                </p>
                            </div>
                        </div>

                        <div class="grid w-full gap-2 sm:flex sm:w-auto sm:flex-wrap">
                            <a href="{{ route('viewer.show', ['viewerKey' => $email->inbox->viewer_key, 'email' => $email], false) }}" target="_blank" class="btn-secondary w-full px-4 py-2 text-xs sm:w-auto">Lihat Detail</a>
                            <form method="POST" action="{{ route('admin.emails.destroy', $email, false) }}" onsubmit="return confirm('Hapus email dan seluruh lampirannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger w-full px-4 py-2 text-xs sm:w-auto">Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    Tidak ada email yang cocok dengan pencarian.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $emails->links() }}
        </div>
    </section>
</x-app-layout>
