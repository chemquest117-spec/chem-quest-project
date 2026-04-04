<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-social-meta 
        :title="$metaTitle ?? null" 
        :description="$metaDescription ?? null" 
        :image="$metaImage ?? null" 
        :url="$metaUrl ?? null" 
    />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 text-white">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="mb-4">
            <a href="/" class="flex flex-col items-center group">
                <x-chemtrack-logo size="xl" class="mx-auto transition-transform duration-300 group-hover:scale-110" />
                <span
                    class="text-3xl font-extrabold mt-4 bg-gradient-to-r from-emerald-400 via-cyan-400 to-purple-400 bg-clip-text text-transparent">ChemTrack</span>
            </a>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-4 sm:px-8 py-8 bg-white/5 backdrop-blur-md shadow-2xl border border-white/10 overflow-hidden rounded-3xl">
            {{ $slot }}
        </div>

        <p class="mt-8 text-slate-500 text-sm">
            ChemTrack &copy; {{ date('Y') }} — Making Chemistry Fun!
        </p>
    </div>
</body>

</html>