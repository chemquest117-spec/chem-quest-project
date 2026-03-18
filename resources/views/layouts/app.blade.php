<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ChemTrack') }} — @yield('title', 'Learn Chemistry')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('head')
</head>

<body
    class="font-sans antialiased bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 text-white min-h-screen">
    <div class="min-h-screen flex flex-col">

        {{-- Navigation --}}
        @include('layouts.navigation')

        {{-- Flash Messages --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                class="max-w-7xl mx-auto mt-4 px-4">
                <div
                    class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 rounded-xl backdrop-blur-sm">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                class="max-w-7xl mx-auto mt-4 px-4">
                <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-xl backdrop-blur-sm">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white/5 backdrop-blur-sm border-b border-white/10">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="my-footer bg-black/20 border-t border-white/10 py-6 text-center text-sm text-slate-400">
            <p class="flex items-center justify-center gap-1.5"><x-chemtrack-logo size="xs" /> ChemTrack &copy;
                {{ date('Y') }} — Making Chemistry Fun!
            </p>
        </footer>
    </div>
</body>

</html>