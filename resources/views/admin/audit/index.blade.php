<x-app-layout>
     @section('title', 'Audit Logs')

     <div class="py-8">
          <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
               <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-1 text-slate-400 hover:text-white text-sm">
                    <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> {{ __('admin.dashboard') }}</a>
               <h1 class="text-3xl font-bold text-white mt-1 mb-8 flex items-center gap-2"><x-icon name="file-text"
                         class="w-7 h-7 text-cyan-400" /> Audit Logs</h1>

               <!-- Filters -->
               <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-6 mb-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                         <div>
                              <label for="action" class="block text-sm font-medium text-white mb-2">Action</label>
                              <select name="action" id="action" class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                   <option value="">All Actions</option>
                                   @foreach($actions as $action)
                                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                                   @endforeach
                              </select>
                         </div>

                         <div>
                              <label for="user_id" class="block text-sm font-medium text-white mb-2">User</label>
                              <select name="user_id" id="user_id" class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                   <option value="">All Users</option>
                                   @foreach(\App\Models\User::whereIn('role', ['admin', 'super_admin'])->get() as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                   @endforeach
                              </select>
                         </div>

                         <div>
                              <label for="from_date" class="block text-sm font-medium text-white mb-2">From Date</label>
                              <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}"
                                   class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                         </div>

                         <div>
                              <label for="to_date" class="block text-sm font-medium text-white mb-2">To Date</label>
                              <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}"
                                   class="w-full bg-white/10 border border-white/20 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                         </div>

                         <div class="md:col-span-4 flex gap-4">
                              <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition">
                                   Filter
                              </button>
                              <a href="{{ route('admin.audit.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition">
                                   Clear Filters
                              </a>
                         </div>
                    </form>
               </div>

               <!-- Logs Table -->
               <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
                    <div class="overflow-x-auto">
                         <table class="w-full">
                              <thead>
                                   <tr class="text-left border-b border-white/10">
                                        <th class="px-6 py-4 text-sm text-slate-400">User</th>
                                        <th class="px-6 py-4 text-sm text-slate-400">Action</th>
                                        <th class="px-6 py-4 text-sm text-slate-400">Description</th>
                                        <th class="px-6 py-4 text-sm text-slate-400">IP Address</th>
                                        <th class="px-6 py-4 text-sm text-slate-400">Date</th>
                                   </tr>
                              </thead>
                              <tbody>
                                   @forelse($logs as $log)
                                        <tr class="border-b border-white/5 hover:bg-white/5">
                                             <td class="px-6 py-4">
                                                  <div class="text-white font-medium">{{ $log->user->name ?? 'Unknown' }}</div>
                                                  <div class="text-xs text-slate-500">{{ $log->user->email ?? '' }}</div>
                                             </td>
                                             <td class="px-6 py-4">
                                                  <span class="px-2 py-1 text-xs rounded-full bg-blue-500/20 text-blue-400">
                                                       {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                                  </span>
                                             </td>
                                             <td class="px-6 py-4 text-slate-300">{{ $log->description }}</td>
                                             <td class="px-6 py-4 text-slate-300 font-mono text-sm">{{ $log->ip_address }}</td>
                                             <td class="px-6 py-4 text-slate-300">{{ $log->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                   @empty
                                        <tr>
                                             <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                                  No audit logs found
                                             </td>
                                        </tr>
                                   @endforelse
                              </tbody>
                         </table>
                    </div>
               </div>

               <div class="mt-4">
                    {{ $logs->appends(request()->query())->links() }}
               </div>
          </div>
     </div>
</x-app-layout>
