<x-guest-layout :metaTitle="__('errors.too_many_requests')">
    <div class="text-center">
        <div class="mb-6">
            <div class="text-8xl font-black bg-gradient-to-r from-yellow-400 via-amber-500 to-orange-500 bg-clip-text text-transparent drop-shadow-2xl">
                429
            </div>
        </div>
        <h1 class="text-3xl font-extrabold text-white mb-3 tracking-tight">{{ __('errors.too_many_requests') }}</h1>
        <p class="text-slate-400 mb-10 leading-relaxed text-lg">{{ __('errors.too_many_requests_desc') }}</p>
        <a href="{{ url('/') }}"
            class="inline-flex items-center gap-2.5 bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 text-white px-10 py-4 rounded-2xl font-bold transition-all duration-300 shadow-xl hover:shadow-amber-500/30 hover:scale-105 active:scale-95 group">
            <x-icon name="home" class="w-5 h-5 transition-transform group-hover:-translate-y-0.5" /> {{ __('errors.go_home') }}
        </a>
    </div>
</x-guest-layout>
