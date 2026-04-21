<x-guest-layout :metaTitle="__('errors.method_not_allowed')">
    <div class="text-center">
        <div class="mb-6">
            <div class="text-8xl font-black bg-gradient-to-r from-sky-400 via-blue-500 to-indigo-500 bg-clip-text text-transparent drop-shadow-2xl">
                405
            </div>
        </div>
        <h1 class="text-3xl font-extrabold text-white mb-3 tracking-tight">{{ __('errors.method_not_allowed') }}</h1>
        <p class="text-slate-400 mb-10 leading-relaxed text-lg">{{ __('errors.method_not_allowed_desc') }}</p>
        <a href="{{ url('/') }}"
            class="inline-flex items-center gap-2.5 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-700 hover:to-indigo-700 text-white px-10 py-4 rounded-2xl font-bold transition-all duration-300 shadow-xl hover:shadow-indigo-500/30 hover:scale-105 active:scale-95 group">
            <x-icon name="home" class="w-5 h-5 transition-transform group-hover:-translate-y-0.5" /> {{ __('errors.go_home') }}
        </a>
    </div>
</x-guest-layout>
