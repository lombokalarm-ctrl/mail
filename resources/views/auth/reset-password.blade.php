<x-guest-layout>
    <div class="mb-6">
        <p class="section-kicker">{{ __('Reset Password') }}</p>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            {{ __('Tetapkan password baru untuk akun admin Anda.') }}
        </p>
    </div>

    <div class="info-banner mb-6">
        <p class="info-banner-title">Tips Keamanan</p>
        <p class="mt-2 leading-6">Gunakan password baru yang belum pernah dipakai pada layanan lain untuk menjaga keamanan panel admin.</p>
    </div>

    <form method="POST" action="{{ route('password.store', [], false) }}" class="auth-form">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />

            <x-text-input id="password_confirmation" class="mt-2 block w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="auth-actions">
            <a href="{{ route('login', absolute: false) }}" class="btn-ghost">
                {{ __('Kembali ke Login') }}
            </a>

            <x-primary-button>
                {{ __('Simpan Password Baru') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
