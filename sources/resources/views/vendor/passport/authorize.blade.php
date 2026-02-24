{{-- resources/views/auth/oauth/authorize.blade.php --}}
<x-guest-layout>

    <div class="absolute top-0 right-0 p-6 z-10">
        <x-theme-switcher />
    </div>

    <x-authentication-card>
        <x-slot name="logo">
            <a href="{{ route('dashboard') }}" class="flex justify-center">
                <img src="{{ asset('images/icon-logo-tsu.png') }}" alt="Ikon Tiga Serangkai University" width="60px" />
            </a>
        </x-slot>

        <div class="mb-4 text-center">
            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                Otorisasi Aplikasi
            </h1>

            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                Aplikasi <strong class="text-gray-800 dark:text-gray-200">{{ $client->name }}</strong> meminta izin untuk mengakses akun Anda.
            </p>
        </div>

        {{-- Form Otorisasi --}}
        <form method="post" action="{{ route('passport.authorizations.approve') }}" class="space-y-6">
            @csrf

            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <input type="hidden" name="state" value="{{ request('state') }}">
            <input type="hidden" name="client_id" value="{{ $client->id }}">

            {{-- Daftar Izin (Scopes) --}}
            @if (count($scopes) > 0)
                <div class="space-y-2">
                    {{-- 4. Perbaiki Warna Label & List --}}
                    <label class="font-medium text-sm text-gray-700 dark:text-gray-300">Aplikasi ini meminta izin untuk:</label>
                    <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400">
                        @foreach ($scopes as $scope)
                            <li>{{ $scope->description }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex items-center justify-end mt-6 gap-4">
                {{-- Tombol Deny (Tolak) --}}
                <button type="submit" name="authorization" value="deny"
                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                    Tolak
                </button>

                {{-- Tombol Approve (Izinkan) --}}
                <x-button type="submit" name="authorization" value="approve" class="ms-3">
                    Izinkan
                </x-button>
            </div>
        </form>

    </x-authentication-card>
</x-guest-layout>
