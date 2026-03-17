<x-app-layout>
     @section('title', 'Leaderboard')

     <div class="py-8">
          <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="leaderboard({{ auth()->id() }})" x-init="init()">

               {{-- Header --}}
               <div class="transition-all duration-700 ease-out"
                    :class="headerReady ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-6'">
                    <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-2"><x-icon name="trophy"
                              class="w-8 h-8 text-amber-400" /> Leaderboard</h1>
                    <p class="text-slate-400 mb-2">Top students ranked by total points</p>
                    <p class="text-xs mb-8 flex items-center gap-2"
                         :class="polling ? 'text-emerald-500' : 'text-slate-600'">
                         <span class="relative flex h-2 w-2">
                              <span
                                   class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                         </span>
                         <span
                              x-text="polling ? 'Live — refreshes data every 10s (no page reload)' : 'Connecting...'"></span>
                         <span x-show="lastUpdate" class="text-slate-600" x-text="'· updated ' + lastUpdate"></span>
                    </p>
               </div>

               {{-- Top 3 Podium --}}
               <template x-if="students.length >= 3">
                    <div class="grid grid-cols-3 gap-4 mb-10 items-end transition-all duration-700 ease-out"
                         :class="podiumReady ? 'opacity-100 scale-100' : 'opacity-0 scale-75'">

                         {{-- 2nd Place --}}
                         <div
                              class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 text-center hover:scale-105 hover:bg-white/10 transition-all duration-300 cursor-default">
                              <x-icon name="medal-silver" class="w-10 h-10 mx-auto mb-2" />
                              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-slate-400 to-slate-500 flex items-center justify-center text-white font-bold text-lg mx-auto mb-2 shadow-lg"
                                   x-text="students[1]?.name?.charAt(0).toUpperCase()"></div>
                              <div class="text-white font-bold text-sm truncate" x-text="students[1]?.name"></div>
                              <div class="text-emerald-400 font-bold text-lg mt-1"
                                   x-text="Number(students[1]?.total_points).toLocaleString()"></div>
                              <div class="flex items-center justify-center gap-0.5 text-amber-400 text-xs"><x-icon
                                        name="star" class="w-3 h-3" /> <span x-text="students[1]?.stars"></span></div>
                         </div>

                         {{-- 1st Place --}}
                         <div
                              class="bg-gradient-to-b from-amber-500/20 to-amber-500/5 backdrop-blur-sm rounded-2xl p-6 border border-amber-500/30 text-center hover:scale-105 transition-all duration-300 -mt-4 shadow-lg shadow-amber-500/10 cursor-default">
                              <x-icon name="medal-gold" class="w-12 h-12 mx-auto mb-2" />
                              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 flex items-center justify-center text-white font-bold text-xl mx-auto mb-2 ring-4 ring-amber-500/30 shadow-lg"
                                   x-text="students[0]?.name?.charAt(0).toUpperCase()"></div>
                              <div class="text-white font-bold truncate" x-text="students[0]?.name"></div>
                              <div class="text-emerald-400 font-bold text-xl mt-1"
                                   x-text="Number(students[0]?.total_points).toLocaleString()"></div>
                              <div class="flex items-center justify-center gap-0.5 text-amber-400 text-sm"><x-icon
                                        name="star" class="w-3.5 h-3.5" /> <span x-text="students[0]?.stars"></span>
                              </div>
                         </div>

                         {{-- 3rd Place --}}
                         <div
                              class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 text-center hover:scale-105 hover:bg-white/10 transition-all duration-300 cursor-default">
                              <x-icon name="medal-bronze" class="w-10 h-10 mx-auto mb-2" />
                              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-amber-700 to-amber-800 flex items-center justify-center text-white font-bold text-lg mx-auto mb-2 shadow-lg"
                                   x-text="students[2]?.name?.charAt(0).toUpperCase()"></div>
                              <div class="text-white font-bold text-sm truncate" x-text="students[2]?.name"></div>
                              <div class="text-emerald-400 font-bold text-lg mt-1"
                                   x-text="Number(students[2]?.total_points).toLocaleString()"></div>
                              <div class="flex items-center justify-center gap-0.5 text-amber-400 text-xs"><x-icon
                                        name="star" class="w-3 h-3" /> <span x-text="students[2]?.stars"></span></div>
                         </div>
                    </div>
               </template>

               {{-- Full Ranking Table --}}
               <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden transition-all duration-700 ease-out"
                    :class="tableReady ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
                    <table class="w-full">
                         <thead>
                              <tr class="border-b border-white/10 text-left">
                                   <th class="px-6 py-4 text-sm font-medium text-slate-400">Rank</th>
                                   <th class="px-6 py-4 text-sm font-medium text-slate-400">Student</th>
                                   <th class="px-6 py-4 text-sm font-medium text-slate-400 text-center">Stars</th>
                                   <th class="px-6 py-4 text-sm font-medium text-slate-400 text-right">Points</th>
                              </tr>
                         </thead>
                         <tbody>
                              <template x-for="(student, index) in students" :key="student.id">
                                   <tr class="border-b border-white/5 transition-all duration-500 ease-out" :class="{
                                    'opacity-100 translate-x-0': visibleRows.includes(index),
                                    'opacity-0 translate-x-12': !visibleRows.includes(index),
                                    'bg-blue-500/10': student.id === currentUserId
                                }">
                                        <td class="px-6 py-4">
                                             <template x-if="index === 0"><x-icon name="medal-gold"
                                                       class="w-7 h-7" /></template>
                                             <template x-if="index === 1"><x-icon name="medal-silver"
                                                       class="w-7 h-7" /></template>
                                             <template x-if="index === 2"><x-icon name="medal-bronze"
                                                       class="w-7 h-7" /></template>
                                             <span x-show="index > 2" class="text-slate-400 font-bold text-lg ml-1"
                                                  x-text="index + 1"></span>
                                        </td>
                                        <td class="px-6 py-4">
                                             <div class="flex items-center space-x-3">
                                                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-sm shadow-lg"
                                                       x-text="student.name.charAt(0).toUpperCase()"></div>
                                                  <div>
                                                       <span class="text-white font-medium"
                                                            x-text="student.name"></span>
                                                       <span x-show="student.id === currentUserId"
                                                            class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded-full ml-1">You</span>
                                                  </div>
                                             </div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-amber-400 font-medium">
                                             <div class="flex items-center justify-center gap-0.5">
                                                  <x-icon name="star" class="w-4 h-4" />
                                                  <span x-text="student.stars"></span>
                                             </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                             <span class="text-emerald-400 font-bold text-lg transition-all duration-300"
                                                  x-text="Number(student.total_points).toLocaleString()"></span>
                                             <span class="text-slate-500 text-sm ml-1">pts</span>
                                        </td>
                                   </tr>
                              </template>

                              <template x-if="students.length === 0 && tableReady">
                                   <tr>
                                        <td colspan="4"
                                             class="px-6 py-12 text-center text-slate-500 flex items-center justify-center gap-2">
                                             <x-icon name="rocket" class="w-5 h-5" /> No students yet. Be the first to
                                             earn points!
                                        </td>
                                   </tr>
                              </template>
                         </tbody>
                    </table>
               </div>

               {{-- Flash on data update --}}
               <div x-show="flashUpdate" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed bottom-6 right-6 bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 px-4 py-2 rounded-xl backdrop-blur-md text-sm shadow-lg flex items-center gap-2">
                    <x-icon name="check-circle" class="w-4 h-4" /> Leaderboard updated
               </div>
          </div>
     </div>

     <script>
          function leaderboard(userId) {
               return {
                    students: @json($studentsJson),
                    currentUserId: userId,
                    headerReady: false,
                    podiumReady: false,
                    tableReady: false,
                    visibleRows: [],
                    polling: false,
                    lastUpdate: '',
                    flashUpdate: false,

                    init() {
                         setTimeout(() => this.headerReady = true, 200);
                         setTimeout(() => this.podiumReady = true, 500);
                         setTimeout(() => this.tableReady = true, 800);

                         this.students.forEach((_, i) => {
                              setTimeout(() => this.visibleRows.push(i), 900 + i * 120);
                         });

                         setTimeout(() => {
                              this.polling = true;
                              this.startPolling();
                         }, 2000);
                    },

                    startPolling() {
                         setInterval(() => this.fetchData(), 10000);
                    },

                    async fetchData() {
                         try {
                              const res = await fetch('{{ route("leaderboard.data") }}');
                              const data = await res.json();

                              const oldJson = JSON.stringify(this.students);
                              const newStudents = data.map(s => ({
                                   id: s.id, name: s.name,
                                   total_points: s.total_points, stars: s.stars
                              }));

                              if (oldJson !== JSON.stringify(newStudents)) {
                                   this.students = newStudents;
                                   this.visibleRows = newStudents.map((_, i) => i);
                                   this.flashUpdate = true;
                                   setTimeout(() => this.flashUpdate = false, 2000);
                              }

                              const now = new Date();
                              this.lastUpdate = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                         } catch (e) {
                              console.error('Leaderboard fetch error:', e);
                         }
                    }
               };
          }
     </script>
</x-app-layout>