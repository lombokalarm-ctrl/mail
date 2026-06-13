<x-guest-layout>
    <div class="mb-6">
        <p class="section-kicker">{{ __('Registrasi') }}</p>
        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ __('Buat akun baru') }}</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            {{ __('Halaman ini tersedia untuk kebutuhan default Laravel, tetapi pendaftaran publik umumnya dinonaktifkan pada APLI Mail.') }}
        </p>
    </div>

    <div class="info-banner mb-6">
        <p class="info-banner-title">Catatan</p>
        <p class="mt-2 leading-6">Gunakan halaman ini hanya jika alur registrasi memang diaktifkan kembali oleh administrator sistem.</p>
    </div>

    <form method="POST" action="{{ route('register', [], false) }}" class="auth-form">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input id="name" class="mt-2 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="mt-2 block w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />

            <x-text-input id="password_confirmation" class="mt-2 block w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="auth-actions mt-4">
            <a class="btn-ghost" href="{{ route('login', absolute: false) }}">
                {{ __('Sudah punya akun?') }}
            </a>

            <x-primary-button class="sm:ms-4">
                {{ __('Daftar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
