<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'APLI Mail') }}</title>
    <script>
        if (localStorage.getItem('apli-theme') === 'dark' || (! localStorage.getItem('apli-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-[38rem] bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.26),_transparent_55%)]"></div>

        <header class="relative mx-auto flex max-w-7xl items-center justify-between px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 text-sm font-bold text-white shadow-lg shadow-blue-600/25">AM</span>
                <div>
                    <p class="text-sm font-semibold">APLI Mail</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Catch-All Email Viewer</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" data-theme-toggle class="btn-secondary px-4 py-2.5">
                    Mode Gelap
                </button>
                @auth
                    <a href="{{ route('dashboard', absolute: false) }}" class="btn-primary px-4 py-2.5">Dashboard</a>
                @else
                    <a href="{{ route('login', absolute: false) }}" class="btn-primary px-4 py-2.5">Login Admin</a>
                @endauth
            </div>
        </header>

        <main class="relative mx-auto max-w-7xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
            <section class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
                <div>
                    <p class="section-kicker">email.apli.my.id</p>
                    <h1 class="mt-6 max-w-4xl text-5xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-6xl">
                        Viewer modern untuk semua email wildcard di domain Anda.
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300">
                        Setiap email ke `*@email.apli.my.id` otomatis diterima, dibuatkan inbox, disimpan ke PostgreSQL, dan siap dibuka melalui URL viewer bertoken.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('login', absolute: false) }}" class="btn-primary">Masuk ke Dashboard</a>
                        <a href="#fitur" class="btn-secondary">Lihat Fitur</a>
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div class="glass-banner">
                            <p class="text-3xl font-semibold text-slate-950 dark:text-white">Catch-All</p>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Inbox tercipta otomatis tanpa pembuatan akun email.</p>
                        </div>
                        <div class="glass-banner">
                            <p class="text-3xl font-semibold text-slate-950 dark:text-white">Viewer URL</p>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Akses daftar email lewat format `/view/{inbox}-{token}`.</p>
                        </div>
                        <div class="glass-banner">
                            <p class="text-3xl font-semibold text-slate-950 dark:text-white">Admin Ops</p>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Statistik harian, pencarian, delete inbox, dan lampiran.</p>
                        </div>
                    </div>
                </div>

                <div class="page-hero p-6">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-950 dark:text-white">Contoh Inbox Viewer</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Tampilan modern ala Gmail</p>
                        </div>
                        <span class="status-badge-blue">Live-ready</span>
                    </div>

                    <div class="space-y-3">
                        <div class="glass-banner border-slate-200/80 bg-white/80 shadow-none dark:border-slate-800/80 dark:bg-slate-950/50">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">ahmad-alhijrah@email.apli.my.id</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Viewer URL: /view/ahmad-alhijrah-f7k29a</p>
                        </div>
                        <div class="space-y-3 rounded-[1.8rem] border border-white/70 bg-white/80 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                            <div class="mail-row">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">E-ticket Umrah Berhasil Terbit</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">travel@maskapai.example</p>
                                </div>
                                <span class="status-badge-slate">2 file</span>
                            </div>
                            <div class="mail-row">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Visa confirmation for alhijrah group</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">visa@embassy.example</p>
                                </div>
                                <span class="status-badge-slate">PDF</span>
                            </div>
                            <div class="mail-row">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Promo marketing bundle Qurban 2026</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">marketing@partner.example</p>
                                </div>
                                <span class="status-badge-emerald">baru</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="fitur" class="mt-20 grid gap-6 lg:grid-cols-3">
                <div class="panel-card">
                    <p class="text-sm font-semibold text-blue-600">Ingestion Pipeline</p>
                    <h3 class="mt-3 text-xl font-semibold text-slate-950 dark:text-white">Postfix + Laravel + Redis</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">Email masuk diproses lewat queue, body HTML disanitasi, lalu lampiran disimpan ke local atau S3-compatible storage.</p>
                </div>
                <div class="panel-card">
                    <p class="text-sm font-semibold text-blue-600">Viewer Inbox</p>
                    <h3 class="mt-3 text-xl font-semibold text-slate-950 dark:text-white">Search, pagination, dark mode</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">Daftar email tampil urut terbaru dengan filter sender, subject, preview isi, dan indikator lampiran.</p>
                </div>
                <div class="panel-card">
                    <p class="text-sm font-semibold text-blue-600">Admin Dashboard</p>
                    <h3 class="mt-3 text-xl font-semibold text-slate-950 dark:text-white">Statistik operasional harian</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">Lihat total inbox, email, attachment, aktivitas per hari, lalu hapus data bila diperlukan.</p>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
