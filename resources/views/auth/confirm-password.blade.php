<x-guest-layout>
    <div class="mb-6">
        <p class="section-kicker">{{ __('Konfirmasi Password') }}</p>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            {{ __('Masukkan password Anda untuk melanjutkan ke area yang dilindungi.') }}
        </p>
    </div>

    <div class="info-banner mb-6">
        <p class="info-banner-title">Verifikasi Tambahan</p>
        <p class="mt-2 leading-6">Langkah ini melindungi perubahan sensitif pada akun dan pengaturan admin.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="mt-2 block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="auth-actions">
            <a href="{{ route('dashboard', absolute: false) }}" class="btn-ghost">
                {{ __('Kembali') }}
            </a>

            <x-primary-button>
                {{ __('Konfirmasi') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
