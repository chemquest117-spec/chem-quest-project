<x-app-layout>
     @section('title', __('admin.student_details') . ' - ' . $user->name)

     <div class="py-8">
          <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
               {{-- Header --}}
               <div class="flex items-center justify-between mb-8">
                    <div>
                         <a href="{{ route('admin.students.index') }}"
                              class="flex items-center gap-1 text-slate-400 hover:text-white text-sm">
                              <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> {{ __('admin.back_to_students') }}
                         </a>
                         <h1 class="text-3xl font-bold text-white mt-1 flex items-center gap-3">
                              <x-icon name="user-circle" class="w-8 h-8 text-blue-400" />
                              {{ $user->name }}
                              @if($user->is_banned)
                                   <span class="text-sm bg-red-500/20 text-red-400 px-2 py-0.5 rounded-lg">{{ __('admin.banned') }}</span>
                              @endif
                         </h1>
                         <p class="text-slate-400 text-sm mt-1">{{ $user->email }} · {{ __('admin.joined') }} {{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                         <form action="{{ route('admin.students.resetPassword', $user) }}" method="POST"
                              onsubmit="return confirm('Reset password for this student?')">
                              @csrf
                              <button class="flex items-center gap-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 px-4 py-2 rounded-xl text-sm font-medium transition">
                                   <x-icon name="key" class="w-4 h-4" /> {{ __('admin.reset_password') }}
                              </button>
                         </form>
                         <form action="{{ route('admin.students.toggleBan', $user) }}" method="POST">
                              @csrf
                              <button class="flex items-center gap-1.5 {{ $user->is_banned ? 'bg-green-500/20 hover:bg-green-500/30 text-green-400' : 'bg-red-500/20 hover:bg-red-500/30 text-red-400' }} px-4 py-2 rounded-xl text-sm font-medium transition">
                                   <x-icon name="{{ $user->is_banned ? 'check-circle' : 'x-circle' }}" class="w-4 h-4" />
                                   {{ $user->is_banned ? __('admin.unban') : __('admin.ban') }}
                              </button>
                         </form>
                         <form action="{{ route('admin.students.destroy', $user) }}" method="POST"
                              onsubmit="return confirm('Permanently delete this student? This cannot be undone.')">
                              @csrf @method('DELETE')
                              <button class="flex items-center gap-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 px-4 py-2 rounded-xl text-sm font-medium transition">
                                   <x-icon name="trash" class="w-4 h-4" /> {{ __('admin.delete') }}
                              </button>
                         </form>
                    </div>
               </div>

               {{-- Overview Stats --}}
               <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10 text-center">
                         <div class="text-2xl font-bold text-emerald-400">{{ $user->total_points }}</div>
                         <div class="text-xs text-slate-400 mt-1">{{ __('admin.points') }}</div>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10 text-center">
                         <div class="text-2xl font-bold text-amber-400">{{ $user->stars }}</div>
                         <div class="text-xs text-slate-400 mt-1">{{ __('admin.stars') }}</div>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10 text-center">
                         <div class="text-2xl font-bold text-orange-400">{{ $user->streak }}</div>
                         <div class="text-xs text-slate-400 mt-1">{{ __('admin.streak') }}</div>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10 text-center">
                         <div class="text-2xl font-bold text-blue-400">{{ $successRate }}%</div>
                         <div class="text-xs text-slate-400 mt-1">{{ __('admin.success_rate') }}</div>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10 text-center">
                         <div class="text-2xl font-bold text-cyan-400">{{ count($completedIds) }}/{{ $stages->count() }}</div>
                         <div class="text-xs text-slate-400 mt-1">{{ __('admin.stages_passed') }}</div>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10 text-center">
                         <div class="text-2xl font-bold text-purple-400">
                              @if($totalTimeSpent > 3600)
                                   {{ round($totalTimeSpent / 3600, 1) }}h
                              @elseif($totalTimeSpent > 60)
                                   {{ round($totalTimeSpent / 60) }}m
                              @else
                                   {{ $totalTimeSpent }}s
                              @endif
                         </div>
                         <div class="text-xs text-slate-400 mt-1">{{ __('admin.study_time') }}</div>
                    </div>
               </div>

               {{-- Stage Performance Table --}}
               <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 mb-8">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                         <x-icon name="trending-up" class="w-5 h-5 text-blue-400" /> {{ __('admin.stage_performance') }}
                    </h2>
                    <div class="overflow-x-auto">
                         <table class="w-full text-sm">
                              <thead>
                                   <tr class="text-slate-400 border-b border-white/10">
                                        <th class="text-start py-3 px-2">{{ __('admin.stage') }}</th>
                                        <th class="text-center py-3 px-2">{{ __('admin.attempts') }}</th>
                                        <th class="text-center py-3 px-2">{{ __('admin.avg_score') }}</th>
                                        <th class="text-center py-3 px-2">{{ __('admin.best_score') }}</th>
                                        <th class="text-center py-3 px-2">{{ __('admin.avg_time') }}</th>
                                        <th class="text-center py-3 px-2">{{ __('admin.status') }}</th>
                                   </tr>
                              </thead>
                              <tbody>
                                   @foreach($stagePerformance as $perf)
                                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                                             <td class="py-3 px-2 text-white font-medium">{{ $perf['stage']->getTranslatedTitle() }}</td>
                                             <td class="py-3 px-2 text-center text-slate-300">{{ $perf['attempts'] }}</td>
                                             <td class="py-3 px-2 text-center">
                                                  <span class="{{ $perf['avg_score'] >= 75 ? 'text-emerald-400' : ($perf['avg_score'] >= 50 ? 'text-amber-400' : 'text-red-400') }}">
                                                       {{ $perf['avg_score'] }}%
                                                  </span>
                                             </td>
                                             <td class="py-3 px-2 text-center text-cyan-400">{{ $perf['best_score'] }}%</td>
                                             <td class="py-3 px-2 text-center text-slate-300">
                                                  {{ $perf['avg_time'] > 60 ? round($perf['avg_time'] / 60, 1) . 'm' : $perf['avg_time'] . 's' }}
                                             </td>
                                             <td class="py-3 px-2 text-center">
                                                  @if($perf['passed'])
                                                       <span class="flex items-center justify-center gap-1 text-emerald-400">
                                                            <x-icon name="check-circle" class="w-4 h-4" /> {{ __('admin.passed') }}
                                                       </span>
                                                  @elseif($perf['attempts'] > 0)
                                                       <span class="text-amber-400">{{ __('admin.in_progress') }}</span>
                                                  @else
                                                       <span class="text-slate-500">{{ __('admin.not_started') }}</span>
                                                  @endif
                                             </td>
                                        </tr>
                                   @endforeach
                              </tbody>
                         </table>
                    </div>
               </div>

               {{-- Recent Attempts Timeline --}}
               <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                         <x-icon name="clock" class="w-5 h-5 text-purple-400" /> {{ __('admin.recent_activity') }}
                    </h2>
                    <div class="space-y-3">
                         @forelse($recentAttempts as $attempt)
                              <div class="flex items-center justify-between py-3 px-4 rounded-xl {{ $attempt->passed ? 'bg-emerald-500/5 border border-emerald-500/10' : 'bg-red-500/5 border border-red-500/10' }}">
                                   <div>
                                        <p class="text-white font-medium">{{ $attempt->stage->getTranslatedTitle() }}</p>
                                        <p class="text-slate-400 text-xs">{{ $attempt->completed_at?->diffForHumans() ?? 'In progress' }}</p>
                                   </div>
                                   <div class="flex items-center gap-4 text-sm">
                                        <span class="text-slate-300">{{ $attempt->score }}/{{ $attempt->total_questions }}</span>
                                        <span class="{{ $attempt->passed ? 'text-emerald-400' : 'text-red-400' }} font-medium">
                                             {{ $attempt->score_percentage }}%
                                        </span>
                                        <span class="text-slate-500">
                                             {{ $attempt->time_spent_seconds ? round($attempt->time_spent_seconds / 60, 1) . 'm' : '—' }}
                                        </span>
                                   </div>
                              </div>
                         @empty
                              <p class="text-slate-500 text-center py-6">{{ __('admin.no_activity') }}</p>
                         @endforelse
                    </div>
               </div>
          </div>
     </div>
</x-app-layout>
