<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
{{--            <x-authentication-card-logo />--}}
            <a href="{{ route('dashboard') }}" class="flex justify-center">
                <img src="{{ asset('images/icon-logo-tsu.png') }}" alt="Ikon Tiga Serangkai University" width="40px" />
            </a>

            <h1 class="mt-4 text-2xl font-bold text-center text-gray-800 dark:text-gray-200">
                TSU Homebase
            </h1>

            <p class="mt-1 text-sm text-center text-gray-600 dark:text-gray-400">
                Sistem Informasi Terpusat Tiga Serangkai University
            </p>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" autofocus />
            </div>

            <div class="flex justify-end mt-4">
                <x-button class="ms-4">
                    {{ __('Confirm') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
