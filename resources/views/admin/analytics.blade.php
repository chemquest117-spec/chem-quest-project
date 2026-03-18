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
                         <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> Admin Dashboard</a>
                    <h1 class="text-3xl font-bold text-white mt-1 flex items-center gap-2">
                         <x-icon name="chart-bar" class="w-7 h-7 text-blue-400" /> Class Analytics
                    </h1>
                    <p class="text-slate-400 text-sm">Monitor student performance and identify learning trends</p>
               </div>

               {{-- Summary Cards --}}
               <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="users" class="w-5 h-5 text-cyan-400" />
                         </div>
                         <div class="text-3xl font-bold text-cyan-400">{{ $totalStudents }}</div>
                         <div class="text-sm text-slate-400 mt-1">Active Students</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="document-text" class="w-5 h-5 text-purple-400" />
                         </div>
                         <div class="text-3xl font-bold text-purple-400">{{ $totalAttempts }}</div>
                         <div class="text-sm text-slate-400 mt-1">Total Attempts</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="target" class="w-5 h-5 text-emerald-400" />
                         </div>
                         <div class="text-3xl font-bold text-emerald-400">{{ $overallPassRate }}%</div>
                         <div class="text-sm text-slate-400 mt-1">Overall Pass Rate</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <div class="flex items-center justify-between mb-2">
                              <x-icon name="clock" class="w-5 h-5 text-amber-400" />
                         </div>
                         <div class="text-3xl font-bold text-amber-400">
                              @if($avgStudyTime > 60) {{ round($avgStudyTime / 60) }}m @else {{ $avgStudyTime }}s @endif
                         </div>
                         <div class="text-sm text-slate-400 mt-1">Avg. Study Time</div>
                    </div>
               </div>

               {{-- Charts Row --}}
               <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {{-- Stage Performance Chart --}}
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                              <x-icon name="chart-bar" class="w-5 h-5 text-blue-400" /> Average Score by Stage
                         </h2>
                         <div class="h-64">
                              <canvas id="stageScoreChart"></canvas>
                         </div>
                    </div>

                    {{-- Daily Activity Chart --}}
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                              <x-icon name="trending-up" class="w-5 h-5 text-emerald-400" /> Daily Activity (30 Days)
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
                              <x-icon name="target" class="w-5 h-5 text-pink-400" /> Pass Rate by Stage
                         </h2>
                         <div class="h-56">
                              <canvas id="passRateChart"></canvas>
                         </div>
                    </div>

                    {{-- Stage Details Table --}}
                    <div class="lg:col-span-2 bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                              <x-icon name="academic-cap" class="w-5 h-5 text-purple-400" /> Stage Breakdown
                         </h2>
                         <div class="overflow-x-auto">
                              <table class="w-full">
                                   <thead>
                                        <tr class="text-left border-b border-white/10">
                                             <th class="px-4 py-3 text-sm text-slate-400">Stage</th>
                                             <th class="px-4 py-3 text-sm text-slate-400 text-center">Attempts</th>
                                             <th class="px-4 py-3 text-sm text-slate-400 text-center">Avg Score</th>
                                             <th class="px-4 py-3 text-sm text-slate-400 text-center">Pass Rate</th>
                                             <th class="px-4 py-3 text-sm text-slate-400 text-center">Avg Time</th>
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

               {{-- Top Performers --}}
               @if($topPerformers->count() > 0)
                    <div class="mt-8 bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                              <x-icon name="trophy" class="w-5 h-5 text-amber-400" /> Top Performers
                         </h2>
                         <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                              @foreach($topPerformers as $i => $performer)
                                   <div
                                        class="bg-white/5 rounded-xl p-4 text-center border {{ $i === 0 ? 'border-amber-500/30' : 'border-white/10' }}">
                                        <div
                                             class="w-12 h-12 mx-auto rounded-full bg-gradient-to-br {{ $i === 0 ? 'from-amber-400 to-amber-600' : ($i === 1 ? 'from-slate-300 to-slate-500' : 'from-orange-400 to-orange-600') }} flex items-center justify-center text-white font-bold text-lg mb-2">
                                             {{ strtoupper(substr($performer->name, 0, 1)) }}
                                        </div>
                                        <p class="text-white font-medium text-sm">{{ $performer->name }}</p>
                                        <p class="text-emerald-400 text-sm font-bold">
                                             {{ number_format($performer->total_points) }} pts</p>
                                        <p class="text-slate-500 text-xs flex items-center justify-center gap-0.5">
                                             <x-icon name="star" class="w-3 h-3 text-amber-400" /> {{ $performer->stars }}
                                        </p>
                                   </div>
                              @endforeach
                         </div>
                    </div>
               @endif
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