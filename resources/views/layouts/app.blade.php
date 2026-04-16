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
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
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

        {{-- Global Toast Component --}}
        <div x-data="{ 
                toasts: [],
                add(toast) {
                    this.toasts.push({
                        id: Date.now(),
                        type: toast.type || 'info',
                        message: toast.message,
                        show: true
                    });
                },
                remove(id) {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }
            }"
            @toast.window="add($event.detail)"
            class="fixed top-20 right-4 z-[9999] flex flex-col items-end space-y-2 pointer-events-none">
            <template x-for="toast in toasts" :key="toast.id">
                <div x-show="toast.show"
                    x-init="setTimeout(() => { toast.show = false; setTimeout(() => remove(toast.id), 500) }, 10000)"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-12 scale-90"
                    x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-x-12 scale-90"
                    class="pointer-events-auto min-w-[280px] max-w-[400px] p-4 rounded-2xl shadow-2xl backdrop-blur-md border flex items-center justify-between gap-3"
                    :class="{
                        'bg-red-500/20 border-red-500/50 text-red-100': toast.type === 'error',
                        'bg-emerald-500/20 border-emerald-500/50 text-emerald-100': toast.type === 'success',
                        'bg-blue-500/20 border-blue-500/50 text-blue-100': toast.type === 'info',
                        'bg-amber-500/20 border-amber-500/50 text-amber-100': toast.type === 'warning'
                    }">
                    <div class="flex items-center gap-3">
                        <template x-if="toast.type === 'error'">
                            <x-icon name="x-circle" class="w-5 h-5 flex-shrink-0" />
                        </template>
                        <template x-if="toast.type === 'success'">
                            <x-icon name="check-circle" class="w-5 h-5 flex-shrink-0" />
                        </template>
                        <template x-if="toast.type === 'warning'">
                            <x-icon name="exclamation" class="w-5 h-5 flex-shrink-0" />
                        </template>
                        <template x-if="toast.type === 'info'">
                            <x-icon name="information-circle" class="w-5 h-5 flex-shrink-0" />
                        </template>
                        <span class="text-sm font-medium" x-text="toast.message"></span>
                    </div>
                </div>
            </template>
        </div>

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
            <p class="flex items-center justify-center gap-1.5"><x-chemtrack-logo size="xs" />
                {!! __('welcome.footer_text', ['year' => date('Y')]) !!}
            </p>
        </footer>
    </div>
</body>

</html>