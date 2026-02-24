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
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                {{ $value }}
            </div>
        @endsession

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="block">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Email Password Reset Link') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
