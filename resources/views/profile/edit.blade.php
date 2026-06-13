<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">{{ __('Account Settings') }}</p>
            <h2 class="section-title">{{ __('Profil Admin') }}</h2>
            <p class="section-copy">{{ __('Perbarui identitas akun, kata sandi, dan pengaturan keamanan akses admin.') }}</p>
        </div>
    </x-slot>

    <div class="profile-grid">
        <div class="profile-main">
            @if (auth()->user()?->must_change_password)
                <div class="glass-banner border-amber-200/80 bg-amber-50/90 text-sm text-amber-900 shadow-none dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-100">
                    <p class="font-semibold">Ganti password wajib dilakukan.</p>
                    <p class="mt-2">Password awal dari admin SaaS harus segera diganti sebelum Anda melanjutkan penggunaan dashboard.</p>
                </div>
            @endif

            <div class="settings-section">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="settings-section">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <aside class="profile-sidebar">
            <div class="panel-card">
                <p class="section-kicker">Ringkasan Akses</p>
                <h3 class="mt-3 text-xl font-semibold text-slate-950 dark:text-white">Kelola identitas admin dengan aman</h3>
                <div class="mt-5 helper-list">
                    <div class="helper-item">
                        <span class="helper-item-dot"></span>
                        <p>Pastikan email login aktif agar notifikasi reset password dan verifikasi tetap dapat diterima.</p>
                    </div>
                    <div class="helper-item">
                        <span class="helper-item-dot"></span>
                        <p>Gunakan password unik dan panjang, lalu perbarui secara berkala untuk mengurangi risiko akses tidak sah.</p>
                    </div>
                    <div class="helper-item">
                        <span class="helper-item-dot"></span>
                        <p>Hapus akun hanya jika Anda yakin tidak lagi membutuhkan akses admin ke dashboard APLI Mail.</p>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </aside>
    </div>
</x-app-layout>
