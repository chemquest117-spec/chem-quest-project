<x-app-layout>
     @section('title', 'Students')

     <div class="py-8">
          <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
               <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-1 text-slate-400 hover:text-white text-sm">
                    <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> Admin Dashboard</a>
               <h1 class="text-3xl font-bold text-white mt-1 mb-8 flex items-center gap-2"><x-icon name="users"
                         class="w-7 h-7 text-cyan-400" /> Student Progress</h1>

               <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
                    <div class="overflow-x-auto">
                         <table class="w-full">
                              <thead>
                                   <tr class="text-left border-b border-white/10">
                                        <th class="px-6 py-4 text-sm text-slate-400">Student</th>
                                        <th class="px-6 py-4 text-sm text-slate-400 text-center">Points</th>
                                        <th class="px-6 py-4 text-sm text-slate-400 text-center">Stars & Streak</th>
                                        <th class="px-6 py-4 text-sm text-slate-400 text-center">Attempts</th>
                                        @foreach($stages as $stage)
                                             <th class="px-4 py-4 text-sm text-slate-400 text-center"
                                                  title="{{ $stage->title }}">
                                                  S{{ $stage->order }}
                                             </th>
                                        @endforeach
                                   </tr>
                              </thead>
                              <tbody>
                                   @foreach($students as $student)
                                        @php $completedIds = $student->completedStageIds(); @endphp
                                        <tr class="border-b border-white/5 hover:bg-white/5">
                                             <td class="px-6 py-4">
                                                  <div class="flex items-center space-x-3">
                                                       <div
                                                            class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xs font-bold">
                                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                                       </div>
                                                       <div>
                                                            <div class="text-white font-medium">{{ $student->name }}</div>
                                                            <div class="text-xs text-slate-500">{{ $student->email }}</div>
                                                       </div>
                                                  </div>
                                             </td>
                                             <td class="px-6 py-4 text-center text-emerald-400 font-bold">
                                                  {{ number_format($student->total_points) }}
                                             </td>
                                             <td class="px-6 py-4 text-center">
                                                  <div class="flex items-center justify-center gap-3">
                                                       <span class="flex items-center justify-center gap-0.5 text-amber-400">
                                                            <x-icon name="star" class="w-3.5 h-3.5" /> {{ $student->stars }}
                                                       </span>
                                                       @if($student->streak > 0)
                                                            <span class="flex items-center justify-center gap-0.5 text-orange-400 bg-orange-500/10 px-2 py-0.5 rounded-full text-xs font-bold">
                                                                 <x-icon name="fire" class="w-3 h-3" /> {{ $student->streak }}
                                                            </span>
                                                       @endif
                                                  </div>
                                             </td>
                                             <td class="px-6 py-4 text-center text-slate-300">{{ $student->attempts_count }}
                                             </td>
                                             @foreach($stages as $stage)
                                                  <td class="px-4 py-4 text-center">
                                                       @if(in_array($stage->id, $completedIds))
                                                            <x-icon name="check" class="w-4 h-4 text-emerald-400 mx-auto" />
                                                       @else
                                                            <span class="text-slate-600">—</span>
                                                       @endif
                                                  </td>
                                             @endforeach
                                        </tr>
                                   @endforeach
                              </tbody>
                         </table>
                    </div>
               </div>

               <div class="mt-4">
                    {{ $students->links() }}
               </div>
          </div>
     </div>
</x-app-layout>