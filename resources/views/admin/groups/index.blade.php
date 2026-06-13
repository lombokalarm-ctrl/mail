<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="section-kicker">Group Manager</p>
                <h2 class="section-title">Kelola Group SaaS Dan Inbox</h2>
                <p class="section-copy">Buat token manual per group, tambah inbox pelanggan, lalu kelola viewer URL tanpa perlu API manual.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.inboxes.index', [], false) }}" class="btn-secondary px-4 py-2.5">Lihat Semua Inbox</a>
                <a href="{{ route('admin.emails.index', [], false) }}" class="btn-primary px-4 py-2.5">Buka Email</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($errors->any())
            <div class="glass-banner border-rose-200/80 bg-rose-50/85 text-sm text-rose-800 shadow-none dark:border-rose-900/70 dark:bg-rose-950/40 dark:text-rose-200">
                <p class="font-semibold">Ada input yang perlu diperbaiki.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('import_report'))
            <div class="glass-banner border-sky-200/80 bg-sky-50/85 text-sm text-sky-900 shadow-none dark:border-sky-900/70 dark:bg-sky-950/40 dark:text-sky-100">
                <p class="font-semibold">Ringkasan import inbox.</p>
                <p class="mt-2">
                    {{ session('import_report.created') }} inbox berhasil ditambahkan.
                    @if (count(session('import_report.skipped', [])) > 0)
                        {{ count(session('import_report.skipped', [])) }} baris dilewati.
                    @endif
                </p>

                @if (count(session('import_report.skipped', [])) > 0)
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach (array_slice(session('import_report.skipped', []), 0, 8) as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>

                    @if (count(session('import_report.skipped', [])) > 8)
                        <p class="mt-2 text-xs text-sky-700 dark:text-sky-200">Masih ada {{ count(session('import_report.skipped', [])) - 8 }} catatan lain yang tidak ditampilkan.</p>
                    @endif
                @endif
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
            <section class="panel-card">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Buat Group Baru</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Setiap customer memiliki satu token viewer yang dipakai bersama oleh semua inbox di group tersebut.</p>
                    </div>
                    <span class="status-badge-blue">{{ $groups->total() }} group</span>
                </div>

                <form method="POST" action="{{ route('admin.groups.store', [], false) }}" class="mt-6 grid gap-4">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="group_name" class="detail-pair-label">Nama Group</label>
                            <input id="group_name" type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Acme Travel" class="field-input mt-2" required />
                        </div>
                        <div>
                            <label for="viewer_token" class="detail-pair-label">Viewer Token</label>
                            <input id="viewer_token" type="text" name="viewer_token" value="{{ old('viewer_token') }}" placeholder="Contoh: acme2026" class="field-input mt-2" required />
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Gunakan huruf dan angka tanpa spasi agar URL viewer tetap bersih.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
                        <div>
                            <label for="group_status" class="detail-pair-label">Status</label>
                            <select id="group_status" name="status" class="field-input mt-2" required>
                                @foreach (['active' => 'Active', 'trial' => 'Trial', 'paused' => 'Paused'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'active') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-primary px-4 py-3">Simpan Group</button>
                    </div>
                </form>
            </section>

            <section class="panel-card">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Tambah Inbox Ke Group</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inbox baru harus didaftarkan lebih dulu agar email masuk bisa diterima oleh sistem.</p>
                    </div>
                    <span class="status-badge-slate">Quick Create</span>
                </div>

                <form method="POST" action="{{ route('admin.inboxes.store', [], false) }}" class="mt-6 grid gap-4">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="quick_group_id" class="detail-pair-label">Pilih Group</label>
                            <select id="quick_group_id" name="group_id" class="field-input mt-2" required>
                                <option value="">Pilih group...</option>
                                @foreach ($groupOptions as $groupOption)
                                    <option value="{{ $groupOption->id }}" @selected((string) old('group_id') === (string) $groupOption->id)>{{ $groupOption->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="quick_inbox_name" class="detail-pair-label">Inbox Name</label>
                            <input id="quick_inbox_name" type="text" name="inbox_name" value="{{ old('inbox_name') }}" placeholder="Contoh: support-acme" class="field-input mt-2" required />
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Alamat email akan menjadi `nama-inbox@{{ config('apli_mail.domain') }}`.</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary px-4 py-3">Tambah Inbox</button>
                    </div>
                </form>
            </section>
        </div>

        <section class="panel-card">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Import Inbox Massal</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Upload file CSV atau XLSX untuk menambahkan banyak inbox sekaligus ke satu group.</p>
                </div>
                <span class="status-badge-slate">CSV / XLSX</span>
            </div>

            <form method="POST" action="{{ route('admin.groups.import-inboxes', [], false) }}" enctype="multipart/form-data" class="mt-6 grid gap-4">
                @csrf

                <div class="grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
                    <div>
                        <label for="import_group_id" class="detail-pair-label">Pilih Group Tujuan</label>
                        <select id="import_group_id" name="group_id" class="field-input mt-2" required>
                            <option value="">Pilih group...</option>
                            @foreach ($groupOptions as $groupOption)
                                <option value="{{ $groupOption->id }}" @selected((string) old('group_id') === (string) $groupOption->id)>{{ $groupOption->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="import_file" class="detail-pair-label">File Import</label>
                        <input id="import_file" type="file" name="import_file" accept=".csv,.txt,.xlsx" class="field-input mt-2 !py-2.5" required />
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Maksimal 10 MB. Bisa berisi satu kolom `inbox_name`, `email`, atau cukup kolom pertama.</p>
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-slate-200/80 bg-slate-50/70 p-4 text-sm text-slate-600 dark:border-slate-800/80 dark:bg-slate-900/60 dark:text-slate-300">
                    <p class="font-semibold text-slate-900 dark:text-white">Format yang didukung</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li>Satu inbox per baris, misalnya `support-acme` atau `support-acme@{{ config('apli_mail.domain') }}`.</li>
                        <li>Jika baris pertama adalah header seperti `inbox_name` atau `email`, sistem akan membacanya otomatis.</li>
                        <li>Inbox yang sudah ada atau duplikat di file akan dilewati, bukan menimpa data lama.</li>
                    </ul>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary px-4 py-3">Import Inbox</button>
                </div>
            </form>
        </section>

        <section class="panel-card overflow-hidden">
            <div class="admin-toolbar">
                <form method="GET" class="flex flex-1 flex-col gap-4 md:flex-row">
                    <div class="flex-1">
                        <label for="q" class="sr-only">Cari group atau inbox</label>
                        <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Cari group, token, status, atau inbox..." class="field-input" />
                    </div>
                    <button type="submit" class="btn-primary">Cari</button>
                </form>

                <div class="admin-toolbar-meta">
                    <span class="status-badge-blue">{{ $groups->total() }} group</span>
                    @if ($search)
                        <span class="status-badge-slate">Filter: {{ $search }}</span>
                    @endif
                </div>
            </div>

            <div class="mt-6 space-y-6">
                @forelse ($groups as $group)
                    <article class="rounded-[2rem] border border-slate-200/80 bg-white/80 p-5 shadow-sm dark:border-slate-800/80 dark:bg-slate-950/50 sm:p-6">
                        <div class="grid gap-6 xl:grid-cols-[1fr_1.1fr]">
                            <div class="space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="status-badge-blue">{{ $group->inboxes_count }} inbox</span>
                                    <span class="status-badge-slate">{{ strtoupper($group->status) }}</span>
                                    <span class="status-badge-slate">Token: {{ $group->viewer_token }}</span>
                                </div>

                                <form method="POST" action="{{ route('admin.groups.update', $group, false) }}" class="grid gap-4">
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <label for="group-name-{{ $group->id }}" class="detail-pair-label">Nama Group</label>
                                        <input id="group-name-{{ $group->id }}" type="text" name="name" value="{{ $group->name }}" class="field-input mt-2" required />
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label for="group-token-{{ $group->id }}" class="detail-pair-label">Viewer Token</label>
                                            <input id="group-token-{{ $group->id }}" type="text" name="viewer_token" value="{{ $group->viewer_token }}" class="field-input mt-2" required />
                                        </div>
                                        <div>
                                            <label for="group-status-{{ $group->id }}" class="detail-pair-label">Status</label>
                                            <select id="group-status-{{ $group->id }}" name="status" class="field-input mt-2" required>
                                                @foreach (['active' => 'Active', 'trial' => 'Trial', 'paused' => 'Paused'] as $value => $label)
                                                    <option value="{{ $value }}" @selected($group->status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <button type="submit" class="btn-primary px-4 py-2.5 text-sm">Simpan Group</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('admin.groups.destroy', $group, false) }}" onsubmit="return confirm('Hapus group beserta seluruh inbox, email, dan lampirannya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger px-4 py-2.5 text-sm">Hapus Group</button>
                                </form>
                            </div>

                            <div class="space-y-4">
                                <div class="rounded-[1.6rem] border border-slate-200/80 bg-slate-50/70 p-4 dark:border-slate-800/80 dark:bg-slate-900/70">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900 dark:text-white">Tambah Inbox Ke {{ $group->name }}</p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Semua inbox di bawah ini memakai token viewer group yang sama.</p>
                                        </div>
                                        <span class="status-badge-blue">{{ $group->viewer_token }}</span>
                                    </div>

                                    <form method="POST" action="{{ route('admin.inboxes.store', [], false) }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto]">
                                        @csrf
                                        <input type="hidden" name="group_id" value="{{ $group->id }}" />
                                        <input type="text" name="inbox_name" placeholder="Contoh: sales-acme" class="field-input" required />
                                        <button type="submit" class="btn-secondary px-4 py-3 text-sm">Tambah</button>
                                    </form>
                                </div>

                                <div class="space-y-3">
                                    @forelse ($group->inboxes as $inbox)
                                        <div class="rounded-[1.5rem] border border-slate-200/80 bg-white/70 p-4 dark:border-slate-800/80 dark:bg-slate-950/40">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="status-badge-blue">{{ $inbox->emails_count }} email</span>
                                                <span class="status-badge-slate">{{ $inbox->inbox_name }}@{{ config('apli_mail.domain') }}</span>
                                            </div>

                                            <form method="POST" action="{{ route('admin.inboxes.update', $inbox, false) }}" class="mt-4 grid gap-4">
                                                @csrf
                                                @method('PATCH')

                                                <div class="grid gap-4 lg:grid-cols-[0.95fr_0.95fr_1.2fr]">
                                                    <div>
                                                        <label for="inbox-name-{{ $inbox->id }}" class="detail-pair-label">Inbox Name</label>
                                                        <input id="inbox-name-{{ $inbox->id }}" type="text" name="inbox_name" value="{{ $inbox->inbox_name }}" class="field-input mt-2" required />
                                                    </div>
                                                    <div>
                                                        <label for="inbox-group-{{ $inbox->id }}" class="detail-pair-label">Group</label>
                                                        <select id="inbox-group-{{ $inbox->id }}" name="group_id" class="field-input mt-2" required>
                                                            @foreach ($groupOptions as $groupOption)
                                                                <option value="{{ $groupOption->id }}" @selected($inbox->group_id === $groupOption->id)>{{ $groupOption->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="detail-pair-label">Viewer URL</label>
                                                        <p class="field-input mt-2 break-all !py-3 text-sm">{{ $inbox->viewer_url }}</p>
                                                    </div>
                                                </div>

                                                <div>
                                                    <button type="submit" class="btn-primary px-4 py-2.5 text-sm">Simpan Inbox</button>
                                                </div>
                                            </form>

                                            <div class="grid gap-3 sm:flex sm:flex-wrap">
                                                <button
                                                    type="button"
                                                    data-copy-text="{{ $inbox->viewer_url }}"
                                                    data-copy-success="Viewer URL inbox berhasil disalin."
                                                    class="btn-secondary px-4 py-2.5 text-sm"
                                                >
                                                    Salin Viewer URL
                                                </button>
                                                <a href="{{ $inbox->viewer_url }}" target="_blank" class="btn-ghost px-4 py-2.5 text-sm">Buka Viewer</a>
                                                <form method="POST" action="{{ route('admin.inboxes.destroy', $inbox, false) }}" onsubmit="return confirm('Hapus inbox beserta seluruh email dan lampirannya?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-danger px-4 py-2.5 text-sm">Hapus Inbox</button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="empty-state">
                                            Group ini belum memiliki inbox. Tambahkan inbox pertama untuk mulai menerima email.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        Belum ada group yang cocok dengan pencarian.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $groups->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
