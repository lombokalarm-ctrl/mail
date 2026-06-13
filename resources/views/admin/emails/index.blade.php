<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Email Manager</p>
            <h2 class="section-title">Daftar Email Global</h2>
            <p class="section-copy">Cari email berdasarkan subject, sender, atau penerima, lalu hapus bila diperlukan.</p>
        </div>
    </x-slot>

    <section class="panel-card">
        <form method="GET" class="flex flex-col gap-4 md:flex-row">
            <div class="flex-1">
                <label for="q" class="sr-only">Cari email</label>
                <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Cari subject, sender, atau recipient..." class="field-input" />
            </div>
            <button type="submit" class="btn-primary">Cari Email</button>
        </form>

        <div class="mt-6 space-y-4">
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

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('viewer.show', ['viewerKey' => $email->inbox->viewer_key, 'email' => $email]) }}" target="_blank" class="btn-secondary px-4 py-2 text-xs">Lihat Detail</a>
                            <form method="POST" action="{{ route('admin.emails.destroy', $email) }}" onsubmit="return confirm('Hapus email dan seluruh lampirannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger px-4 py-2 text-xs">Hapus</button>
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
