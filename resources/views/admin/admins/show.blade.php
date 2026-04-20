<x-app-layout>
     @section('title', 'Admin Details')

     <div class="py-8">
          <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
               <a href="{{ route('admin.admins.index') }}"
                    class="flex items-center gap-1 text-slate-400 hover:text-white text-sm">
                    <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> Back to Admins</a>
               <h1 class="text-3xl font-bold text-white mt-1 mb-8">Admin Details</h1>

               <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Profile Info -->
                    <div class="lg:col-span-1">
                         <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
                              <div class="text-center">
                                   <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">
                                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                                   </div>
                                   <h2 class="text-xl font-bold text-white">{{ $admin->name }}</h2>
                                   <p class="text-slate-400">{{ $admin->email }}</p>
                                   <div class="mt-4">
                                        <span class="px-3 py-1 text-sm rounded-full {{ $admin->role === 'super_admin' ? 'bg-red-500/20 text-red-400' : 'bg-blue-500/20 text-blue-400' }}">
                                             {{ ucfirst(str_replace('_', ' ', $admin->role)) }}
                                        </span>
                                   </div>
                              </div>

                              <div class="mt-6 space-y-3">
                                   @if($admin->role !== 'super_admin' && $admin->id !== auth()->id())
                                        <a href="{{ route('admin.admins.edit', ['admin' => $admin]) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition text-center block">
                                             Edit Admin
                                        </a>
                                        @if($admin->role !== 'super_admin' || \App\Models\User::where('role', 'super_admin')->count() > 1)
                                             <form action="{{ route('admin.admins.destroy', ['admin' => $admin]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this admin?')">
                                                  @csrf
                                                  @method('DELETE')
                                                  <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition">
                                                       Delete Admin
                                                  </button>
                                             </form>
                                        @endif
                                   @endif
                              </div>
                         </div>
                    </div>

                    <!-- Activity -->
                    <div class="lg:col-span-2">
                         <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
                              <h3 class="text-lg font-semibold text-white mb-4">Recent Activity</h3>

                              @if($admin->auditLogs->count() > 0)
                                   <div class="space-y-4">
                                        @foreach($admin->auditLogs->take(10) as $log)
                                             <div class="flex items-start gap-3 p-3 bg-white/5 rounded-lg">
                                                  <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center flex-shrink-0">
                                                       <x-icon name="activity" class="w-4 h-4 text-gray-300" />
                                                  </div>
                                                  <div class="flex-1">
                                                       <p class="text-white text-sm">{{ $log->description }}</p>
                                                       <p class="text-slate-500 text-xs">{{ $log->created_at->diffForHumans() }}</p>
                                                  </div>
                                             </div>
                                        @endforeach
                                   </div>
                              @else
                                   <p class="text-slate-400 text-center py-8">No recent activity</p>
                              @endif
                         </div>
                    </div>
               </div>
          </div>
     </div>
</x-app-layout>
