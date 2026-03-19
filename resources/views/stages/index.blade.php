<x-app-layout>
     @section('title', 'Stages')

     <div class="py-8">
          <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
               <div x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 100)" x-cloak>
                    {{-- Header --}}
                    <div x-show="shown" x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 -translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0">
                         <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-2"><x-icon name="target"
                                   class="w-7 h-7 text-pink-400" /> {{ __('stages.title') }}</h1>
                         <p class="text-slate-400 mb-4">Complete each stage to unlock the next. You need
                              {{ $stages->first()->passing_percentage ?? 75 }}% to pass.
                         </p>

                         {{-- Horizontal progress indicator --}}
                         <div class="flex items-center justify-center gap-2 mb-10 px-4">
                              @foreach($stages as $idx => $s)
                                                       @php
                                                            $done = in_array($s->id, $completedIds);
                                                            $unlocked = $s->isUnlockedFor($user);
                                                          @endphp
                                                       <div class="flex items-center">
                                                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-500
                                                                                              {{ $done ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 scale-110' :
                                   ($unlocked ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30 animate-pulse' :
                                        'bg-slate-700 text-slate-500') }}">
                                                                 @if($done)
                                                                      <x-icon name="check" class="w-5 h-5" />
                                                                 @elseif($unlocked)
                                                                      {{ $s->order }}
                                                                 @else
                                                                      <x-icon name="lock-closed" class="w-4 h-4" />
                                                                 @endif
                                                            </div>
                                                            @if(!$loop->last)
                                                                 <div
                                                                      class="w-8 sm:w-14 h-1 rounded-full mx-1 transition-all duration-700
                                                                                                            {{ $done ? 'bg-gradient-to-r from-emerald-500 to-emerald-400' : 'bg-slate-700' }}">
                                                                 </div>
                                                            @endif
                                                       </div>
                              @endforeach
                         </div>
                    </div>

                    {{-- Vertical Roadmap --}}
                    <div class="relative">
                         <div
                              class="absolute start-8 top-0 bottom-0 w-0.5 bg-gradient-to-b from-emerald-500 via-purple-500 to-slate-700">
                         </div>

                         <div class="space-y-6">
                              @foreach($stages as $index => $stage)
                                                       @php
                                                            $isCompleted = in_array($stage->id, $completedIds);
                                                            $isUnlocked = $stage->isUnlockedFor($user);
                                                            $delay = ($index + 1) * 150;
                                                          @endphp

                                                       <div x-show="shown" x-transition:enter="transition ease-out duration-600"
                                                            x-transition:enter-start="opacity-0 translate-x-8"
                                                            x-transition:enter-end="opacity-100 translate-x-0"
                                                            style="transition-delay: {{ $delay }}ms"
                                                            class="relative flex items-start space-x-6 group">

                                                            {{-- Node dot --}}
                                                            <div class="relative z-10 flex-shrink-0 w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg transition-all duration-500 group-hover:scale-110 group-hover:rotate-3
                                                                                              {{ $isCompleted ? 'bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-emerald-500/30' :
                                   ($isUnlocked ? 'bg-gradient-to-br from-blue-500 to-purple-600 text-white shadow-blue-500/30 animate-pulse' :
                                        'bg-slate-700/80 text-slate-500') }}">
                                                                 @if($isCompleted)
                                                                      <x-icon name="check" class="w-7 h-7" />
                                                                 @elseif(!$isUnlocked)
                                                                      <x-icon name="lock-closed" class="w-6 h-6" />
                                                                 @else
                                                                      <x-icon name="play" class="w-6 h-6" />
                                                                 @endif
                                                            </div>

                                                            {{-- Card --}}
                                                            <div class="flex-1 backdrop-blur-sm rounded-2xl p-6 border transition-all duration-500 group-hover:translate-x-1
                                                                                              {{ $isCompleted ? 'bg-emerald-500/10 border-emerald-500/30 hover:border-emerald-400/50 hover:shadow-lg hover:shadow-emerald-500/10' :
                                   ($isUnlocked ? 'bg-blue-500/10 border-blue-500/30 hover:border-blue-400/50 hover:bg-blue-500/15 hover:shadow-lg hover:shadow-blue-500/10' :
                                        'bg-white/[0.03] border-white/5 opacity-50') }}">
                                                                 <div class="flex items-start justify-between">
                                                                      <div>
                                                                           <div class="flex items-center gap-2 mb-1">
                                                                                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                                                                                               {{ $isCompleted ? 'bg-emerald-500/20 text-emerald-400' :
                                   ($isUnlocked ? 'bg-blue-500/20 text-blue-400' : 'bg-slate-700 text-slate-500') }}">
                                                                                     Stage {{ $stage->order }}
                                                                                </span>
                                                                                @if($isCompleted)
                                                                                     <span
                                                                                          class="flex items-center gap-1 text-xs text-emerald-400 font-medium"><x-icon
                                                                                               name="check-circle" class="w-3.5 h-3.5" />
                                                                                          Completed</span>
                                                                                @elseif($isUnlocked)
                                                                                     <span class="relative flex h-2 w-2">
                                                                                          <span
                                                                                               class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                                                                          <span
                                                                                               class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                                                                                     </span>
                                                                                @endif
                                                                           </div>

                                                                           <h3
                                                                                class="text-lg font-bold {{ $isCompleted ? 'text-emerald-300' : ($isUnlocked ? 'text-white' : 'text-slate-500') }}">
                                                                                {{ $stage->getTranslatedTitle() }}
                                                                           </h3>
                                                                           <p class="text-sm text-slate-400 mt-1">{{ $stage->getTranslatedDescription() }}</p>

                                                                           <div
                                                                                class="flex flex-wrap items-center gap-3 mt-3 text-xs text-slate-500">
                                                                                <span class="flex items-center gap-1"><x-icon name="clock"
                                                                                          class="w-3 h-3" /> {{ $stage->time_limit_minutes }}
                                                                                     min</span>
                                                                                <span class="flex items-center gap-1"><x-icon
                                                                                          name="document-text" class="w-3 h-3" />
                                                                                     {{ $stage->questions_count }} questions</span>
                                                                                <span class="flex items-center gap-1"><x-icon name="target"
                                                                                          class="w-3 h-3" /> {{ $stage->passing_percentage }}% to
                                                                                     pass</span>
                                                                                <span
                                                                                     class="flex items-center gap-1 {{ $isCompleted ? 'text-emerald-400' : '' }}"><x-icon
                                                                                          name="medal" class="w-3 h-3" />
                                                                                     +{{ $stage->points_reward }} pts</span>
                                                                           </div>
                                                                      </div>

                                                                      <div class="mt-1 ms-4">
                                                                           @if($isCompleted)
                                                                                <a href="{{ route('stages.show', $stage) }}"
                                                                                     class="inline-flex items-center gap-1 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 hover:scale-105">
                                                                                     <x-icon name="refresh" class="w-4 h-4" /> Retry
                                                                                </a>
                                                                           @elseif($isUnlocked)
                                                                                <a href="{{ route('stages.show', $stage) }}"
                                                                                     class="inline-flex items-center gap-1 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 shadow-lg hover:shadow-blue-500/25 hover:scale-105">
                                                                                     Start Quiz <x-icon name="arrow-right" class="w-4 h-4" />
                                                                                </a>
                                                                           @else
                                                                                <span
                                                                                     class="flex items-center gap-1 bg-slate-700/50 text-slate-500 px-4 py-2 rounded-xl text-sm cursor-not-allowed">
                                                                                     Locked <x-icon name="lock-closed" class="w-3.5 h-3.5" />
                                                                                </span>
                                                                           @endif
                                                                      </div>
                                                                 </div>
                                                            </div>
                                                       </div>
                              @endforeach
                         </div>
                    </div>
               </div>
          </div>
     </div>
</x-app-layout>