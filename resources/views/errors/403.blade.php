<x-guest-layout :metaTitle="__('errors.forbidden')">
    <div class="text-center">
        <div class="mb-6">
            <div class="text-8xl font-black bg-gradient-to-r from-violet-400 via-fuchsia-500 to-pink-500 bg-clip-text text-transparent drop-shadow-2xl">
                403
            </div>
        </div>
        <h1 class="text-3xl font-extrabold text-white mb-3 tracking-tight">{{ __('errors.forbidden') }}</h1>
        <p class="text-slate-400 mb-10 leading-relaxed text-lg">{{ __('errors.forbidden_desc') }}</p>
        <a href="{{ url('/') }}"
            class="inline-flex items-center gap-2.5 bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 text-white px-10 py-4 rounded-2xl font-bold transition-all duration-300 shadow-xl hover:shadow-fuchsia-500/30 hover:scale-105 active:scale-95 group">
            <x-icon name="home" class="w-5 h-5 transition-transform group-hover:-translate-y-0.5" /> {{ __('errors.go_home') }}
        </a>
    </div>
</x-guest-layout>
