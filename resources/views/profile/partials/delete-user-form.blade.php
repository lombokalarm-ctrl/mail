<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-slate-900 dark:text-white">
            {{ __('Hapus Akun') }}
        </h2>

        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
            {{ __('Setelah akun dihapus, seluruh akses admin akan hilang permanen. Pastikan Anda benar-benar yakin sebelum melanjutkan.') }}
        </p>
    </header>

    <div class="info-banner">
        <p class="info-banner-title">Peringatan</p>
        <p class="mt-2 leading-6">Penghapusan akun bersifat permanen dan tidak dapat dibatalkan. Pastikan ada admin pengganti bila sistem masih aktif digunakan.</p>
    </div>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Hapus Akun Admin') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy', [], false) }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-slate-900 dark:text-white">
                {{ __('Anda yakin ingin menghapus akun ini?') }}
            </h2>

            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                {{ __('Tindakan ini bersifat permanen. Masukkan password untuk mengonfirmasi penghapusan akun admin.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full sm:w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button class="sm:ms-3">
                    {{ __('Hapus Akun') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
