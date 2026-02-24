<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Icon -->
        <link rel="icon" href="{{ asset('images/favicon/favicon.ico') }}" type="image/x-icon"/>
        <link rel="shortcut icon" href="{{ asset('images/favicon/favicon.ico') }}" type="image/x-icon"/>
        <link rel="icon" type="image/png" href="{{ asset('images/favicon/favicon-96x96.png') }}" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon/favicon.svg') }}" />
        <link rel="shortcut icon" href="{{ asset('images/favicon/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicon/apple-touch-icon.png') }}" />
        <meta name="apple-mobile-web-app-title" content="TSU Homebase" />
        <link rel="manifest" href="{{ asset('images/favicon/site.webmanifest') }}" />

        <!-- Scripts -->
        <x-head-theme-script />
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
