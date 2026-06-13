<section>
    <header>
        <h2 class="text-lg font-medium text-slate-900 dark:text-white">
            {{ __('Ubah Password') }}
        </h2>

        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
            {{ __('Gunakan password yang kuat agar dashboard admin tetap aman.') }}
        </p>
    </header>

    <div class="info-banner mt-6">
        <p class="info-banner-title">Rekomendasi</p>
        <p class="mt-2 leading-6">Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol agar akun admin lebih aman.</p>
    </div>

    <form method="post" action="{{ route('password.update', [], false) }}" class="auth-form mt-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Password Saat Ini')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-2 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('Password Baru')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-2 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="auth-actions">
            <p class="helper-text">Password baru akan dipakai pada sesi login berikutnya.</p>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Simpan') }}</x-primary-button>

                @if (session('status') === 'password-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-slate-500 dark:text-slate-400"
                    >{{ __('Tersimpan.') }}</p>
                @endif
            </div>
        </div>
    </form>
</section>
