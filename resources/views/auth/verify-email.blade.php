<x-guest-layout>
    <div class="mb-6">
        <p class="section-kicker">{{ __('Verifikasi Email') }}</p>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            {{ __('Sebelum mulai, verifikasi dulu alamat email Anda melalui tautan yang sudah kami kirim.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="glass-banner mb-4 border-emerald-200/80 bg-emerald-50/85 text-sm font-medium text-emerald-700 shadow-none dark:border-emerald-900/70 dark:bg-emerald-950/35 dark:text-emerald-200">
            {{ __('Link verifikasi baru telah dikirim ke alamat email Anda.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Kirim Ulang Verifikasi') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn-ghost">
                {{ __('Keluar') }}
            </button>
        </form>
    </div>
</x-guest-layout>
