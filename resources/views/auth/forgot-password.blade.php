<x-guest-layout>
    <div class="mb-6">
        <p class="section-kicker">{{ __('Reset Password') }}</p>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            {{ __('Masukkan email admin dan kami akan mengirim tautan reset password ke inbox Anda.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="info-banner mb-6">
        <p class="info-banner-title">Petunjuk</p>
        <p class="mt-2 leading-6">Gunakan email admin yang terdaftar. Link reset akan dikirim jika akun ditemukan di sistem.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="auth-actions">
            <a href="{{ route('login', absolute: false) }}" class="btn-ghost">
                {{ __('Kembali ke Login') }}
            </a>

            <x-primary-button>
                {{ __('Kirim Link Reset') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
