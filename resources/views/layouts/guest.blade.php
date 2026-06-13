<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'APLI Mail') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <script>
        if (localStorage.getItem('apli-theme') === 'dark' || (! localStorage.getItem('apli-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.18),_transparent_32%),linear-gradient(180deg,_rgba(248,250,252,0.98),_rgba(241,245,249,0.92)_38%,_rgba(255,255,255,0.98))] dark:bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.18),_transparent_22%),linear-gradient(180deg,_rgba(2,6,23,1),_rgba(15,23,42,0.98)_38%,_rgba(2,6,23,1))]">
        <div class="absolute inset-x-0 top-0 h-96 bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.24),_transparent_55%)]"></div>
        <div class="relative mx-auto flex min-h-screen max-w-6xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid w-full gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="page-hero hidden p-10 lg:block">
                    <div class="mb-8 flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 text-base font-bold text-white shadow-lg shadow-blue-600/20">AM</span>
                        <div>
                            <p class="text-sm font-semibold">APLI Mail</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Viewer dan dashboard catch-all email untuk `email.apli.my.id`.</p>
                        </div>
                    </div>
                    <div class="space-y-5">
                        <h1 class="max-w-md text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">Pantau semua inbox wildcard dalam satu tampilan modern.</h1>
                        <p class="max-w-xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                            APLI Mail menerima email ke alamat apa pun di subdomain catch-all, menyimpannya ke PostgreSQL, lalu menampilkannya dengan viewer bertoken, dashboard admin, dan pencarian cepat.
                        </p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="glass-banner border-slate-200/80 bg-white/80 shadow-none dark:border-slate-800/80 dark:bg-slate-950/50">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Viewer Publik</p>
                                <p class="mt-3 text-sm text-slate-700 dark:text-slate-300">URL `/view/{inbox}-{token}`, pencarian email, pagination, dark mode.</p>
                            </div>
                            <div class="glass-banner border-slate-200/80 bg-white/80 shadow-none dark:border-slate-800/80 dark:bg-slate-950/50">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Admin Console</p>
                                <p class="mt-3 text-sm text-slate-700 dark:text-slate-300">Statistik email harian, manajemen inbox, hapus email dan lampiran.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="w-full rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-[0_28px_80px_-34px_rgba(59,130,246,0.28)] backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-900/80 dark:shadow-[0_28px_80px_-38px_rgba(15,23,42,0.85)] sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
