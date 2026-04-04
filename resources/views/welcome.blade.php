<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-social-meta 
        :title="__('welcome.title')" 
        :description="__('welcome.hero_desc')" 
    />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-sans antialiased bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 text-white min-h-screen flex flex-col overflow-x-hidden"
    x-data="{ mounted: false }" x-init="setTimeout(() => mounted = true, 100)">

    <div class="flex-1 flex items-center justify-center px-4 py-8">
        <!-- Language Switcher -->
        <div class="absolute top-4 end-4 z-50">
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('language.switch', 'en') }}"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-white/10 transition bg-black/20 backdrop-blur-md border border-white/10">
                    <img src="https://flagcdn.com/w20/us.png" srcset="https://flagcdn.com/w40/us.png 2x" width="20" alt="English"> EN
                </a>
            @else
                <a href="{{ route('language.switch', 'ar') }}"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-white/10 transition bg-black/20 backdrop-blur-md border border-white/10"
                    dir="rtl">
                    <img src="https://flagcdn.com/w20/eg.png" srcset="https://flagcdn.com/w40/eg.png 2x" width="20" alt="Arabic"> AR
                </a>
            @endif
        </div>
        <div class="text-center max-w-3xl transition-all duration-1000 transform"
             :class="mounted ? 'translate-y-0 opacity-100' : 'translate-y-12 opacity-0'">
            {{-- Creative Animated Logo --}}
            <div class="mb-6 hover:scale-110 transition-transform duration-500 cursor-pointer drop-shadow-[0_0_15px_rgba(168,85,247,0.5)]">
                <x-chemtrack-logo size="2xl" class="mx-auto" />
            </div>

            <h1 class="text-5xl sm:text-6xl font-extrabold mb-4">
                <span
                    class="bg-gradient-to-r from-emerald-400 via-cyan-400 to-purple-400 bg-clip-text text-transparent">
                    {{ __('welcome.hero_title') }}
                </span>
            </h1>

            <p class="text-xl text-slate-300 mb-2">{{ __('welcome.hero_subtitle') }}</p>
            <p class="text-slate-500 mb-6 max-w-lg mx-auto">{{ __('welcome.hero_desc') }}</p>

            {{-- Feature Pills --}}
            <div class="flex flex-wrap justify-center gap-3 mb-6 transition-all duration-1000 delay-300 transform"
                 :class="mounted ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">
                <span
                    class="flex items-center gap-1.5 bg-white/5 border border-white/10 px-4 py-2 rounded-full text-sm text-slate-300 hover:bg-white/10 hover:border-emerald-400/50 hover:text-emerald-300 transition-all duration-300 cursor-default shadow-[0_0_10px_rgba(0,0,0,0)] hover:shadow-[0_0_15px_rgba(52,211,153,0.3)]">
                    <x-icon name="clock" class="w-4 h-4" /> {{ __('welcome.feat_timed') }}</span>
                <span
                    class="flex items-center gap-1.5 bg-white/5 border border-white/10 px-4 py-2 rounded-full text-sm text-slate-300 hover:bg-white/10 hover:border-cyan-400/50 hover:text-cyan-300 transition-all duration-300 cursor-default shadow-[0_0_10px_rgba(0,0,0,0)] hover:shadow-[0_0_15px_rgba(34,211,238,0.3)]">
                    <x-icon name="target" class="w-4 h-4" /> {{ __('welcome.feat_stages') }}</span>
                <span
                    class="flex items-center gap-1.5 bg-white/5 border border-white/10 px-4 py-2 rounded-full text-sm text-slate-300 hover:bg-white/10 hover:border-yellow-400/50 hover:text-yellow-300 transition-all duration-300 cursor-default shadow-[0_0_10px_rgba(0,0,0,0)] hover:shadow-[0_0_15px_rgba(250,204,21,0.3)]">
                    <x-icon name="star" class="w-4 h-4" /> {{ __('welcome.feat_stars') }}</span>
                <span
                    class="flex items-center gap-1.5 bg-white/5 border border-white/10 px-4 py-2 rounded-full text-sm text-slate-300 hover:bg-white/10 hover:border-purple-400/50 hover:text-purple-300 transition-all duration-300 cursor-default shadow-[0_0_10px_rgba(0,0,0,0)] hover:shadow-[0_0_15px_rgba(192,132,252,0.3)]">
                    <x-icon name="trophy" class="w-4 h-4" /> {{ __('welcome.feat_leaderboard') }}</span>
                <span
                    class="flex items-center gap-1.5 bg-white/5 border border-white/10 px-4 py-2 rounded-full text-sm text-slate-300 hover:bg-white/10 hover:border-pink-400/50 hover:text-pink-300 transition-all duration-300 cursor-default shadow-[0_0_10px_rgba(0,0,0,0)] hover:shadow-[0_0_15px_rgba(244,114,182,0.3)]">
                    <x-icon name="medal" class="w-4 h-4" /> {{ __('welcome.feat_points') }}</span>
            </div>

            {{-- CTA Buttons --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center transition-all duration-1000 delay-500 transform"
                 :class="mounted ? 'scale-100 opacity-100' : 'scale-95 opacity-0'">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center justify-center gap-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-8 py-3 rounded-xl font-bold text-lg transition-all duration-200 shadow-lg hover:shadow-purple-500/30">
                        {{ __('welcome.btn_dashboard') }} <x-icon name="arrow-right" class="w-5 h-5" />
                    </a>
                @else
                    <a href="{{ route('register') }}"
                        class="flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white px-8 py-3 rounded-xl font-bold text-lg transition-all duration-200 shadow-lg hover:shadow-emerald-500/30">
                        {{ __('welcome.btn_register') }} <x-icon name="rocket" class="w-5 h-5" />
                    </a>
                    <a href="{{ route('login') }}"
                        class="bg-white/10 hover:bg-white/20 text-white px-8 py-3 rounded-xl font-medium text-lg transition-all duration-200 border border-white/10">
                        {{ __('welcome.btn_login') }}
                    </a>
                @endauth
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-6 mt-10 max-w-md mx-auto">
                <div>
                    <div class="text-3xl font-bold text-emerald-400">{{ \App\Models\Stage::count() }}</div>
                    <div class="text-sm text-slate-500">{{ __('welcome.stat_stages') }}</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-cyan-400">{{ \App\Models\Question::count() }}+</div>
                    <div class="text-sm text-slate-500">{{ __('welcome.stat_questions') }}</div>
                </div>
                <div>
                    <div class="flex justify-center">
                        <x-icon name="infinity" class="w-8 h-8 text-purple-400" />
                    </div>
                    <div class="text-sm text-slate-500">{{ __('welcome.stat_retries') }}</div>
                </div>
            </div>
        </div>
    </div>

    <footer class="my-footer text-center py-6 text-sm text-slate-600">
        <p class="flex items-center justify-center gap-1.5">
            <x-chemtrack-logo size="xs" /> {!! __('welcome.footer_text', ['year' => date('Y')]) !!}
        </p>
    </footer>
</body>

</html>