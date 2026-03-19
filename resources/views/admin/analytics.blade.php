<x-app-layout>
     @section('title', 'Analytics Dashboard')

     @push('head')
          <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
     @endpush

     <div class="py-8">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
               {{-- Header --}}
               <div class="mb-8">
                    <a href="{{ route('admin.dashboard') }}"
                         class="flex items-center gap-1 text-slate-400 hover:text-white text-sm">
                         <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> {{ __('admin.dashboard') }}</a>
                    <h1 class="text-3xl font-bold text-white mt-1 flex items-center gap-2">
                         <x-icon name="chart-bar" class="w-7 h-7 text-blue-400" /> {{ __('admin.analytics_title') }}
                    </h1>
                    <p class="text-slate-400 text-sm">{{ __('admin.analytics_desc') }}</p>
               </div>

               {{-- Summary Cards --}}
               <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="users" class="w-5 h-5 text-cyan-400" />
                         </div>
                         <div class="text-3xl font-bold text-cyan-400">{{ $totalStudents }}</div>
                         <div class="text-sm text-slate-400 mt-1">{{ __('admin.active_students') }}</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="document-text" class="w-5 h-5 text-purple-400" />
                         </div>
                         <div class="text-3xl font-bold text-purple-400">{{ $totalAttempts }}</div>
                         <div class="text-sm text-slate-400 mt-1">{{ __('admin.total_attempts') }}</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="target" class="w-5 h-5 text-emerald-400" />
                         </div>
                         <div class="text-3xl font-bold text-emerald-400">{{ $overallPassRate }}%</div>
                         <div class="text-sm text-slate-400 mt-1">{{ __('admin.overall_pass_rate') }}</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="clock" class="w-5 h-5 text-amber-400" />
                         </div>
                         <div class="text-3xl font-bold text-amber-400">
                              @if($avgStudyTime > 60) {{ round($avgStudyTime / 60) }}m @else {{ $avgStudyTime }}s @endif
                         </div>
                         <div class="text-sm text-slate-400 mt-1">{{ __('admin.avg_study_time') }}</div>
                    </div>
               </div>

               {{-- Charts Row --}}
               <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {{-- Stage Performance Chart --}}
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                              <x-icon name="chart-bar" class="w-5 h-5 text-blue-400" /> {{ __('admin.stage_performance') }}
                         </h2>
                         <div class="h-64">
                              <canvas id="stageScoreChart"></canvas>
                         </div>
                    </div>

                    {{-- Daily Activity Chart --}}
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                              <x-icon name="trending-up" class="w-5 h-5 text-emerald-400" /> {{ __('admin.daily_activity') }}
                         </h2>
                         <div class="h-64">
                              <canvas id="dailyActivityChart"></canvas>
                         </div>
                    </div>
               </div>

               <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Pass Rate by Stage --}}
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                              <x-icon name="target" class="w-5 h-5 text-pink-400" /> {{ __('admin.pass_rate_stage') }}
                         </h2>
                         <div class="h-56">
                              <canvas id="passRateChart"></canvas>
                         </div>
                    </div>

                    {{-- Stage Details Table --}}
                    <div class="lg:col-span-2 bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                              <x-icon name="academic-cap" class="w-5 h-5 text-purple-400" /> {{ __('admin.stage_breakdown') }}
                         </h2>
                         <div class="overflow-x-auto">
                              <table class="w-full">
                                   <thead>
                                        <tr class="text-left border-b border-white/10">
                                             <th class="px-4 py-3 text-sm text-slate-400">{{ __('admin.stage') }}</th>
                                             <th class="px-4 py-3 text-sm text-slate-400 text-center">{{ __('admin.attempts') }}</th>
                                             <th class="px-4 py-3 text-sm text-slate-400 text-center">{{ __('admin.avg_score') }}</th>
                                             <th class="px-4 py-3 text-sm text-slate-400 text-center">{{ __('admin.pass_rate') }}</th>
                                             <th class="px-4 py-3 text-sm text-slate-400 text-center">{{ __('admin.avg_time') }}</th>
                                        </tr>
                                   </thead>
                                   <tbody>
                                        @foreach($stageStats as $stat)
                                             <tr class="border-b border-white/5 hover:bg-white/5">
                                                  <td class="px-4 py-3 text-white font-medium">
                                                       <span class="inline-flex items-center gap-2">
                                                            <span
                                                                 class="w-6 h-6 rounded-full bg-purple-500/20 text-purple-400 flex items-center justify-center text-xs font-bold">{{ $stat['order'] }}</span>
                                                            {{ $stat['name'] }}
                                                       </span>
                                                  </td>
                                                  <td class="px-4 py-3 text-center text-slate-300">{{ $stat['attempts'] }}
                                                  </td>
                                                  <td class="px-4 py-3 text-center">
                                                       <span
                                                            class="font-bold {{ $stat['avg_score'] >= 75 ? 'text-emerald-400' : ($stat['avg_score'] >= 50 ? 'text-amber-400' : 'text-red-400') }}">
                                                            {{ $stat['avg_score'] }}%
                                                       </span>
                                                  </td>
                                                  <td class="px-4 py-3 text-center">
                                                       <span
                                                            class="px-2 py-1 rounded-full text-xs {{ $stat['pass_rate'] >= 75 ? 'bg-emerald-500/20 text-emerald-400' : ($stat['pass_rate'] >= 50 ? 'bg-amber-500/20 text-amber-400' : 'bg-red-500/20 text-red-400') }}">
                                                            {{ $stat['pass_rate'] }}%
                                                       </span>
                                                  </td>
                                                  <td class="px-4 py-3 text-center text-slate-300">
                                                       @if($stat['avg_time'] > 60) {{ round($stat['avg_time'] / 60) }}m @else
                                                       {{ $stat['avg_time'] }}s @endif
                                                  </td>
                                             </tr>
                                        @endforeach
                                   </tbody>
                              </table>
                         </div>
                    </div>
               </div>

               {{-- Top Performers and Problematic Questions Row --}}
               <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                    @if($topPerformers->count() > 0)
                         <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                              <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                   <x-icon name="trophy" class="w-5 h-5 text-amber-400" /> {{ __('admin.top_performers') }}
                              </h2>
                              <div class="space-y-3">
                                   @foreach($topPerformers as $i => $performer)
                                        <div class="flex items-center p-3 rounded-lg border {{ $i === 0 ? 'bg-amber-500/10 border-amber-500/30 ring-1 ring-amber-500/50' : 'bg-white/5 border-white/10' }}">
                                             <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $i === 0 ? 'from-amber-400 to-amber-600' : ($i === 1 ? 'from-slate-300 to-slate-500' : 'from-orange-400 to-orange-600') }} flex items-center justify-center text-white font-bold text-sm shadow-md me-4 shrink-0">
                                                  {{ strtoupper(substr($performer->name, 0, 1)) }}
                                             </div>
                                             <div class="flex-1 min-w-0">
                                                  <h3 class="text-white font-medium text-sm truncate">{{ $performer->name }}</h3>
                                                  <div class="text-slate-400 text-xs flex items-center gap-2 mt-0.5">
                                                       <span class="flex items-center gap-0.5"><x-icon name="star" class="w-3 h-3 text-amber-400" />{{ $performer->stars }}</span>
                                                       <span class="text-emerald-400 font-bold whitespace-nowrap">{{ number_format($performer->total_points) }} {{ __('admin.pts') }}</span>
                                                  </div>
                                             </div>
                                        </div>
                                   @endforeach
                              </div>
                         </div>
                    @endif

                    @if(count($problematicQuestions) > 0)
                         <div class="bg-red-500/5 backdrop-blur-sm rounded-2xl p-6 border border-red-500/20">
                              <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                   <x-icon name="exclamation" class="w-5 h-5 text-red-400" /> {{ __('admin.problematic_questions') }}
                              </h2>
                              <div class="space-y-3">
                                   @foreach($problematicQuestions as $pq)
                                        <div class="p-3 rounded-lg bg-white/5 border border-white/10">
                                             <div class="flex justify-between items-start mb-2">
                                                  <span class="text-xs text-purple-400 font-medium bg-purple-500/10 px-2 py-0.5 rounded">{{ $pq['question']->stage->getTranslatedTitle() }}</span>
                                                  <span class="text-red-400 text-xs font-bold">{{ __('admin.fail_rate', ['rate' => $pq['failure_rate']]) }}</span>
                                             </div>
                                             <p class="text-sm text-slate-300 line-clamp-2" title="{{ $pq['question']->getTranslatedQuestionText() }}">{{ $pq['question']->getTranslatedQuestionText() }}</p>
                                             <div class="text-xs text-slate-500 mt-2 flex items-center justify-between">
                                                  <span>{{ __('admin.attempts_count', ['count' => $pq['total_attempts']]) }}</span>
                                                  <a href="{{ route('admin.questions.edit', [$pq['question']->stage_id, $pq['question']->id]) }}" class="text-blue-400 hover:text-blue-300 flex items-center gap-1">{{ __('admin.edit') }} <x-icon name="pencil" class="w-3 h-3" /></a>
                                             </div>
                                        </div>
                                   @endforeach
                              </div>
                         </div>
                    @endif
               </div>
          </div>
     </div>

     <script>
          document.addEventListener('DOMContentLoaded', function () {
               // Shared chart defaults
               Chart.defaults.color = '#94a3b8';
               Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';
               Chart.defaults.font.family = 'Inter, sans-serif';

               // Stage Score Chart
               new Chart(document.getElementById('stageScoreChart'), {
                    type: 'bar',
                    data: {
                         labels: @json(collect($stageStats)->pluck('name')),
                         datasets: [{
                              label: 'Average Score %',
                              data: @json(collect($stageStats)->pluck('avg_score')),
                              backgroundColor: [
                                   'rgba(16, 185, 129, 0.6)',
                                   'rgba(6, 182, 212, 0.6)',
                                   'rgba(139, 92, 246, 0.6)',
                                   'rgba(245, 158, 11, 0.6)',
                                   'rgba(236, 72, 153, 0.6)',
                              ],
                              borderRadius: 8,
                              barThickness: 40,
                         }]
                    },
                    options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         plugins: { legend: { display: false } },
                         scales: {
                              y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                              x: { grid: { display: false } }
                         }
                    }
               });

               // Daily Activity Chart
               new Chart(document.getElementById('dailyActivityChart'), {
                    type: 'line',
                    data: {
                         labels: @json(collect($dailyActivity)->pluck('date')),
                         datasets: [{
                              label: 'Total Attempts',
                              data: @json(collect($dailyActivity)->pluck('attempts')),
                              borderColor: '#06b6d4',
                              backgroundColor: 'rgba(6, 182, 212, 0.1)',
                              fill: true,
                              tension: 0.4,
                              pointRadius: 0,
                              pointHoverRadius: 5,
                         }, {
                              label: 'Passed',
                              data: @json(collect($dailyActivity)->pluck('passed')),
                              borderColor: '#10b981',
                              backgroundColor: 'rgba(16, 185, 129, 0.1)',
                              fill: true,
                              tension: 0.4,
                              pointRadius: 0,
                              pointHoverRadius: 5,
                         }]
                    },
                    options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         plugins: { legend: { position: 'bottom' } },
                         scales: {
                              y: { beginAtZero: true, ticks: { stepSize: 1 } },
                              x: {
                                   grid: { display: false },
                                   ticks: { maxTicksLimit: 10 }
                              }
                         }
                    }
               });

               // Pass Rate Doughnut
               new Chart(document.getElementById('passRateChart'), {
                    type: 'doughnut',
                    data: {
                         labels: @json(collect($stageStats)->pluck('name')),
                         datasets: [{
                              data: @json(collect($stageStats)->pluck('pass_rate')),
                              backgroundColor: [
                                   'rgba(16, 185, 129, 0.8)',
                                   'rgba(6, 182, 212, 0.8)',
                                   'rgba(139, 92, 246, 0.8)',
                                   'rgba(245, 158, 11, 0.8)',
                                   'rgba(236, 72, 153, 0.8)',
                              ],
                              borderWidth: 0,
                         }]
                    },
                    options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         plugins: {
                              legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } }
                         },
                         cutout: '65%',
                    }
               });
          });
     </script>
</x-app-layout>