<x-app-layout>
     @section('title', 'Admins')

     <div class="py-8">
          <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
               <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-1 text-slate-400 hover:text-white text-sm">
                    <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> {{ __('admin.dashboard') }}</a>
               <h1 class="text-3xl font-bold text-white mt-1 mb-8 flex items-center gap-2"><x-icon name="shield"
                         class="w-7 h-7 text-cyan-400" /> Admin Management</h1>

               <div class="mb-6">
                    <a href="{{ route('admin.admins.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                         Create New Admin
                    </a>
               </div>

               <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
                    <div class="overflow-x-auto">
                         <table class="w-full">
                              <thead>
                                   <tr class="text-left border-b border-white/10">
                                        <th class="px-6 py-4 text-sm text-slate-400">Admin</th>
                                        <th class="px-6 py-4 text-sm text-slate-400">Role</th>
                                        <th class="px-6 py-4 text-sm text-slate-400">Created</th>
                                        <th class="px-6 py-4 text-sm text-slate-400">Actions</th>
                                   </tr>
                              </thead>
                              <tbody>
                                   @foreach($admins as $admin)
                                        <tr class="border-b border-white/5 hover:bg-white/5">
                                             <td class="px-6 py-4">
                                                  <div class="flex items-center space-x-3">
                                                       <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white text-xs font-bold">
                                                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                                                       </div>
                                                       <div>
                                                            <div class="text-white font-medium">{{ $admin->name }}</div>
                                                            <div class="text-xs text-slate-500">{{ $admin->email }}</div>
                                                       </div>
                                                  </div>
                                             </td>
                                             <td class="px-6 py-4">
                                                  <span class="px-2 py-1 text-xs rounded-full {{ $admin->role === 'super_admin' ? 'bg-red-500/20 text-red-400' : 'bg-blue-500/20 text-blue-400' }}">
                                                       {{ ucfirst(str_replace('_', ' ', $admin->role)) }}
                                                  </span>
                                             </td>
                                             <td class="px-6 py-4 text-slate-300">{{ $admin->created_at->format('M d, Y') }}</td>
                                             <td class="px-6 py-4">
                                                  <div class="flex items-center gap-1">
                                                       <a href="{{ route('admin.admins.show', ['admin' => $admin]) }}"
                                                          class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-blue-400 hover:text-blue-300 hover:bg-blue-500/10 rounded-md transition-colors">
                                                            <x-icon name="eye" class="w-3 h-3" />
                                                            View
                                                       </a>
                                                       @if($admin->role !== 'super_admin' && $admin->id !== auth()->id())
                                                            <a href="{{ route('admin.admins.edit', $admin) }}"
                                                               class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-green-400 hover:text-green-300 hover:bg-green-500/10 rounded-md transition-colors">
                                                                 <x-icon name="pencil" class="w-3 h-3" />
                                                                 Edit
                                                            </a>
                                                            @if($admin->role !== 'super_admin' || $admins->where('role', 'super_admin')->count() > 1)
                                                                 <form action="{{ route('admin.admins.destroy', ['admin' => $admin]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                                                      @csrf
                                                                      @method('DELETE')
                                                                      <button type="submit"
                                                                              class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-md transition-colors">
                                                                           <x-icon name="trash" class="w-3 h-3" />
                                                                           Delete
                                                                      </button>
                                                                 </form>
                                                            @endif
                                                       @endif
                                                  </div>
                                             </td>
                                        </tr>
                                   @endforeach
                              </tbody>
                         </table>
                    </div>
               </div>

               <div class="mt-4">
                    {{ $admins->links() }}
               </div>
          </div>
     </div>
</x-app-layout>
