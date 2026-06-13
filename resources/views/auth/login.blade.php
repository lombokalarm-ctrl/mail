<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-8">
        <p class="section-kicker">Admin Login</p>
        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">Masuk ke dashboard APLI Mail</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            Gunakan akun admin Laravel untuk memantau inbox, email, lampiran, dan statistik catch-all.
        </p>
    </div>

    <form method="POST" action="{{ route('login', absolute: false) }}">
        @csrf

        <div>
            <x-input-label for="email" :value="'Email Admin'" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="'Password'" />

            <x-text-input id="password" class="mt-2 block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-slate-600 dark:text-slate-300">{{ __('Ingat saya') }}</span>
            </label>
        </div>

        <div class="mt-6 flex items-center justify-between gap-4">
            @if (Route::has('password.request'))
                <a class="text-sm text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400" href="{{ route('password.request', absolute: false) }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3 px-5 py-3 text-sm font-medium tracking-[0.18em]">
                {{ __('Masuk') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
