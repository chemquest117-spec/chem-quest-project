<x-guest-layout :metaTitle="__('errors.server_error')">
     <div class="text-center max-w-lg mx-auto py-12">
          <div class="mb-10 relative">
               <div class="text-9xl font-black bg-gradient-to-br from-red-500 via-rose-500 to-orange-500 bg-clip-text text-transparent drop-shadow-2xl animate-pulse">
                    500
               </div>
               <div class="absolute -bottom-2 inset-x-0 h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>
          </div>

          <h1 class="text-4xl font-extrabold text-white mb-4 tracking-tight">
               {{ __('errors.something_isnt_right') }}
          </h1>

          <p class="text-slate-400 mb-4 leading-relaxed text-lg italic">
               &ldquo;{{ $friendlyDefault ?? __('errors.server_error_desc') }}&rdquo;
          </p>

          <p class="text-slate-500 text-sm mb-12">
               {{ __('errors.tech_details_logged') }}
               @if(app()->bound('sentry') && app('sentry')->getLastEventId())
               <br>
               <span class="font-mono text-xs text-rose-400 mt-2 inline-block px-3 py-1 bg-rose-500/10 rounded-full border border-rose-500/20">
                    Ref: {{ app('sentry')->getLastEventId() }}
               </span>
               @endif
          </p>

          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
               <button onclick="window.location.reload()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-xl hover:shadow-emerald-500/20 hover:scale-105 active:scale-95 group">
                    <x-icon name="arrow-path" class="w-5 h-5 group-hover:rotate-180 transition-transform duration-500" />
                    {{ __('errors.refresh_page') }}
               </button>

               <a href="{{ url('/') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 px-8 py-4 rounded-2xl font-bold transition-all duration-300 shadow-xl hover:scale-105 active:scale-95 border border-slate-700">
                    <x-icon name="home" class="w-5 h-5" />
                    {{ __('errors.go_home') }}
               </a>
          </div>

          <div class="mt-16 pt-8 border-t border-slate-800">
               <p class="text-slate-500 text-xs uppercase tracking-widest mb-4 font-semibold">
                    {{ __('errors.tried_everything') }}
               </p>
               <div class="flex justify-center gap-6">
                    <form action="{{ route('logout') }}" method="POST">
                         @csrf
                         <button type="submit" class="text-slate-400 hover:text-white text-sm transition-colors underline decoration-slate-600 underline-offset-4">
                              {{ __('errors.logout_relogin') }}
                         </button>
                    </form>
                    <a href="mailto:chemquest117@gmail.com" class="text-slate-400 hover:text-white text-sm transition-colors underline decoration-slate-600 underline-offset-4">
                         {{ __('errors.contact_support') }}
                    </a>
               </div>
          </div>
     </div>
</x-guest-layout>