<x-guest-layout>
     <div class="min-h-screen flex items-center justify-center p-4">
          <div class="text-center max-w-md mx-auto">
               <div class="mb-6">
                    <div class="text-8xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                         404
                    </div>
               </div>
               <h1 class="text-2xl font-bold text-white mb-2">{{ __('errors.page_not_found') }}</h1>
               <p class="text-slate-400 mb-8">{{ __('errors.page_not_found_desc') }}</p>
               <a href="{{ url('/') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-8 py-3 rounded-xl font-bold transition-all duration-200 shadow-lg">
                    <x-icon name="home" class="w-5 h-5" /> {{ __('errors.go_home') }}
               </a>
          </div>
     </div>
</x-guest-layout>
