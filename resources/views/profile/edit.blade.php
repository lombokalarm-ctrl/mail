<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">{{ __('Account Settings') }}</p>
            <h2 class="section-title">{{ __('Profil Admin') }}</h2>
            <p class="section-copy">{{ __('Perbarui identitas akun, kata sandi, dan pengaturan keamanan akses admin.') }}</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-0 lg:px-0">
            <div class="settings-section">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="settings-section">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="settings-section">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
