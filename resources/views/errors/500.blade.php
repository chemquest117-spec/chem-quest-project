<x-guest-layout>
     <div class="min-h-screen flex items-center justify-center p-4">
          <div class="text-center max-w-md mx-auto">
               <div class="mb-6">
                    <div class="text-8xl font-bold bg-gradient-to-r from-red-400 to-orange-400 bg-clip-text text-transparent">
                         500
                    </div>
               </div>
               <h1 class="text-2xl font-bold text-white mb-2">{{ __('errors.server_error') }}</h1>
               <p class="text-slate-400 mb-8">{{ __('errors.server_error_desc') }}</p>
               <a href="{{ url('/') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 text-white px-8 py-3 rounded-xl font-bold transition-all duration-200 shadow-lg">
                    <x-icon name="home" class="w-5 h-5" /> {{ __('errors.go_home') }}
               </a>
          </div>
     </div>
</x-guest-layout>
