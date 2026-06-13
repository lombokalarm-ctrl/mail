<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-white/60 bg-white/60 backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-950/50">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 text-sm font-bold text-white shadow-lg shadow-blue-600/25">AM</span>
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">APLI Mail</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Catch-All Inbox Console</p>
                </div>
            </a>
        </div>

        <div class="hidden items-center gap-2 rounded-full border border-white/70 bg-slate-100/65 px-2 py-2 dark:border-slate-800 dark:bg-slate-900/65 md:flex">
            <a href="{{ route('dashboard') }}" class="nav-pill {{ request()->routeIs('dashboard') ? 'nav-pill-active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.inboxes.index') }}" class="nav-pill {{ request()->routeIs('admin.inboxes.*') ? 'nav-pill-active' : '' }}">Inbox</a>
            <a href="{{ route('admin.emails.index') }}" class="nav-pill {{ request()->routeIs('admin.emails.*') ? 'nav-pill-active' : '' }}">Email</a>
            <a href="{{ route('profile.edit') }}" class="nav-pill {{ request()->routeIs('profile.*') ? 'nav-pill-active' : '' }}">Profil</a>
        </div>

        <div class="hidden items-center gap-3 md:flex">
            <button type="button" data-theme-toggle class="btn-secondary px-4 py-2.5 text-sm">
                Mode Gelap
            </button>

            <div class="rounded-[1.4rem] border border-white/70 bg-white/80 px-4 py-2.5 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ Auth::user()->email }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-primary px-4 py-2.5 text-sm">
                    Keluar
                </button>
            </form>
        </div>

        <button @click="open = !open" type="button" class="inline-flex items-center justify-center rounded-2xl border border-white/70 bg-white/85 p-2.5 text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900/85 dark:text-slate-200 md:hidden">
            <span class="sr-only">Buka menu</span>
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <div x-cloak x-show="open" x-transition class="border-t border-white/60 bg-white/90 px-4 py-4 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/90 md:hidden">
        <div class="space-y-2">
            <div class="mb-3 rounded-[1.4rem] border border-white/70 bg-white/80 px-4 py-3 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ Auth::user()->email }}</p>
            </div>
            <a href="{{ route('dashboard') }}" class="mobile-nav-pill">Dashboard</a>
            <a href="{{ route('admin.inboxes.index') }}" class="mobile-nav-pill">Inbox</a>
            <a href="{{ route('admin.emails.index') }}" class="mobile-nav-pill">Email</a>
            <a href="{{ route('profile.edit') }}" class="mobile-nav-pill">Profil</a>
            <button type="button" data-theme-toggle class="mobile-nav-pill w-full text-left">Mode Gelap</button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mobile-nav-pill w-full text-left text-rose-600 dark:text-rose-300">Keluar</button>
            </form>
        </div>
    </div>
</nav>
