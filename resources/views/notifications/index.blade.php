<x-app-layout>
     @section('title', __('navigation.notifications'))

     <div class="py-12">
          <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
               <div class="flex items-center justify-between mb-8">
                    <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                         <x-icon name="bell" class="w-8 h-8 text-cyan-400" />
                         {{ __('navigation.notifications') }}
                    </h1>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                         <form action="{{ route('notifications.readAll') }}" method="POST">
                              @csrf
                              <button type="submit" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                                   <x-icon name="check-circle" class="w-4 h-4 text-emerald-400" />
                                   {{ __('navigation.mark_all_read') }}
                              </button>
                         </form>
                    @endif
               </div>

               <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
                    <div class="divide-y divide-white/10">
                         @forelse($notifications as $notification)
                              <div class="p-6 transition-colors duration-200 {{ is_null($notification->read_at) ? 'bg-cyan-500/10 hover:bg-cyan-500/20' : 'bg-transparent hover:bg-white/5' }}">
                                   <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                             <div class="flex items-center gap-2 mb-1">
                                                  @if(is_null($notification->read_at))
                                                       <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                                                  @endif
                                                  <span class="text-sm text-slate-400">
                                                       {{ $notification->created_at->diffForHumans() }}
                                                  </span>
                                             </div>
                                             <p class="text-base {{ is_null($notification->read_at) ? 'text-white font-medium' : 'text-slate-300' }}">
                                                  @if(app()->getLocale() === 'ar' && isset($notification->data['message_ar']))
                                                       {{ $notification->data['message_ar'] }}
                                                  @elseif(isset($notification->data['message_en']))
                                                       {{ $notification->data['message_en'] }}
                                                  @else
                                                       {{ $notification->data['message'] ?? '' }}
                                                  @endif
                                             </p>
                                        </div>
                                        @if(is_null($notification->read_at))
                                             <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                                  @csrf
                                                  <button type="submit" class="text-xs text-cyan-400 hover:text-cyan-300 bg-cyan-500/10 hover:bg-cyan-500/20 px-3 py-1.5 rounded-lg transition" title="Mark as read">
                                                       <x-icon name="check" class="w-4 h-4" />
                                                  </button>
                                             </form>
                                        @endif
                                   </div>
                              </div>
                         @empty
                              <div class="p-12 text-center flex flex-col items-center justify-center">
                                   <x-icon name="bell" class="w-12 h-12 text-slate-500 mb-4 opacity-50" />
                                   <p class="text-slate-400">{{ __('dashboard.no_attempts_yet') /* Fallback generic text since we don't have exact */ }}</p>
                              </div>
                         @endforelse
                    </div>
               </div>

               <div class="mt-6">
                    {{ $notifications->links() }}
               </div>
          </div>
     </div>
</x-app-layout>
