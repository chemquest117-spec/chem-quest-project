<x-app-layout>
     @section('title', 'Admin Dashboard')

     <div class="py-8">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
               <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-2"><x-icon name="cog"
                         class="w-7 h-7 text-amber-400" /> Admin Dashboard</h1>
               <p class="text-slate-400 mb-8">Manage stages, questions, and monitor student progress</p>

               {{-- Stats --}}
               <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="users" class="w-5 h-5 text-cyan-400" />
                         </div>
                         <div class="text-3xl font-bold text-cyan-400">{{ $totalStudents }}</div>
                         <div class="text-sm text-slate-400 mt-1">Total Students</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="academic-cap" class="w-5 h-5 text-purple-400" />
                         </div>
                         <div class="text-3xl font-bold text-purple-400">{{ $totalStages }}</div>
                         <div class="text-sm text-slate-400 mt-1">Total Stages</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="document-text" class="w-5 h-5 text-amber-400" />
                         </div>
                         <div class="text-3xl font-bold text-amber-400">{{ $totalAttempts }}</div>
                         <div class="text-sm text-slate-400 mt-1">Quiz Attempts</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="percent" class="w-5 h-5 text-emerald-400" />
                         </div>
                         <div class="text-3xl font-bold text-emerald-400">{{ $passRate }}%</div>
                         <div class="text-sm text-slate-400 mt-1">Pass Rate</div>
                    </div>
               </div>

               {{-- Quick Links --}}
               <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <a href="{{ route('admin.stages.index') }}"
                         class="bg-purple-500/10 border border-purple-500/20 rounded-2xl p-6 hover:bg-purple-500/20 transition text-center group">
                         <x-icon name="academic-cap"
                              class="w-8 h-8 mx-auto mb-2 text-purple-400 group-hover:scale-110 transition-transform" />
                         <div class="text-white font-bold">Manage Stages</div>
                         <div class="text-sm text-slate-400">Create & edit stages</div>
                    </a>
                    <a href="{{ route('admin.students.index') }}"
                         class="bg-cyan-500/10 border border-cyan-500/20 rounded-2xl p-6 hover:bg-cyan-500/20 transition text-center group">
                         <x-icon name="users"
                              class="w-8 h-8 mx-auto mb-2 text-cyan-400 group-hover:scale-110 transition-transform" />
                         <div class="text-white font-bold">View Students</div>
                         <div class="text-sm text-slate-400">Monitor progress</div>
                    </a>
                    <a href="{{ route('leaderboard') }}"
                         class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-6 hover:bg-amber-500/20 transition text-center group">
                         <x-icon name="trophy"
                              class="w-8 h-8 mx-auto mb-2 text-amber-400 group-hover:scale-110 transition-transform" />
                         <div class="text-white font-bold">Leaderboard</div>
                         <div class="text-sm text-slate-400">Top performers</div>
                    </a>
               </div>

               {{-- Recent Attempts --}}
               <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2"><x-icon name="document-text"
                              class="w-5 h-5 text-blue-400" /> Recent Attempts</h2>
                    <div class="overflow-x-auto">
                         <table class="w-full">
                              <thead>
                                   <tr class="text-left border-b border-white/10">
                                        <th class="px-4 py-3 text-sm text-slate-400">Student</th>
                                        <th class="px-4 py-3 text-sm text-slate-400">Stage</th>
                                        <th class="px-4 py-3 text-sm text-slate-400">Score</th>
                                        <th class="px-4 py-3 text-sm text-slate-400">Result</th>
                                        <th class="px-4 py-3 text-sm text-slate-400">Date</th>
                                   </tr>
                              </thead>
                              <tbody>
                                   @foreach($recentAttempts as $attempt)
                                        <tr class="border-b border-white/5 hover:bg-white/5">
                                             <td class="px-4 py-3 text-white">{{ $attempt->user->name }}</td>
                                             <td class="px-4 py-3 text-slate-300">{{ $attempt->stage->title }}</td>
                                             <td class="px-4 py-3 text-white">
                                                  {{ $attempt->score }}/{{ $attempt->total_questions }}
                                             </td>
                                             <td class="px-4 py-3">
                                                  <span
                                                       class="flex items-center gap-1 w-fit px-2 py-1 rounded-full text-xs {{ $attempt->passed ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' }}">
                                                       @if($attempt->passed) <x-icon name="check" class="w-3 h-3" /> @else
                                                       <x-icon name="x-circle" class="w-3 h-3" /> @endif
                                                       {{ $attempt->passed ? 'Passed' : 'Failed' }}
                                                  </span>
                                             </td>
                                             <td class="px-4 py-3 text-slate-400 text-sm">
                                                  {{ $attempt->created_at->diffForHumans() }}
                                             </td>
                                        </tr>
                                   @endforeach
                              </tbody>
                         </table>
                    </div>
               </div>
          </div>
     </div>
</x-app-layout>