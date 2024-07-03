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

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body>
    <svg class="absolute right-1/2 top-8 -mr-3 lg:left-0 lg:m-0" width="404" height="392" fill="none"
        viewBox="0 0 404 392">
        <defs>
            <pattern id="837c3e70-6c3a-44e6-8854-cc48c737b659" x="0" y="0" width="20" height="20"
                patternUnits="userSpaceOnUse">
                <rect x="0" y="0" width="4" height="4" class="text-gray-200" fill="currentColor" />
            </pattern>
        </defs>
        <rect width="404" height="392" fill="url(#837c3e70-6c3a-44e6-8854-cc48c737b659)" />
    </svg>
    <div class="font-sans text-gray-900 antialiased">
        {{ $slot }}
    </div>

    @include('components.tawk-chat')

    @livewireScripts
    @include('cookie-consent::index')
</body>

</html>
