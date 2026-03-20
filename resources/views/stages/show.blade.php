<x-app-layout>
     @section('title', $stage->getTranslatedTitle())

     <div class="py-8">
          <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
               {{-- Breadcrumb --}}
               <div class="mb-6">
                    <a href="{{ route('stages.index') }}"
                         class="flex items-center gap-1 text-slate-400 hover:text-white text-sm transition">
                         <x-icon name="arrow-right" class="w-4 h-4 rtl:rotate-180" /> {{ __('stages.back_to_stages') }}
                    </a>
               </div>

               {{-- Stage Header --}}
               <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10 mb-6">
                    <div class="flex items-start justify-between">
                         <div>
                              <span
                                   class="inline-block px-3 py-1 rounded-full text-xs font-medium mb-3
                              {{ $isCompleted ? 'bg-emerald-500/20 text-emerald-400' : 'bg-purple-500/20 text-purple-400' }}">
                                   {{ __('stages.stage', ['number' => $stage->order]) }}
                              </span>
                              <h1 class="text-3xl font-bold text-white mb-2">{{ $stage->getTranslatedTitle() }}</h1>
                              <p class="text-slate-400">{{ $stage->getTranslatedDescription() }}</p>
                         </div>
                         @if($isCompleted)
                              <x-icon name="trophy" class="w-12 h-12 text-amber-400" />
                         @else
                              <x-chemtrack-logo size="lg" />
                         @endif
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                         <div class="bg-white/5 rounded-xl p-3 text-center">
                              <x-icon name="clock" class="w-6 h-6 mx-auto mb-1 text-cyan-400" />
                              <div class="text-sm text-slate-400">{{ __('stages.time_limit') }}</div>
                              <div class="text-white font-bold">{{ __('stages.minutes', ['minutes' => $stage->time_limit_minutes]) }}</div>
                         </div>
                         <div class="bg-white/5 rounded-xl p-3 text-center">
                              <x-icon name="document-text" class="w-6 h-6 mx-auto mb-1 text-blue-400" />
                              <div class="text-sm text-slate-400">{{ __('stages.questions') }}</div>
                              <div class="text-white font-bold">{{ $stage->questions_count }}</div>
                         </div>
                         <div class="bg-white/5 rounded-xl p-3 text-center">
                              <x-icon name="target" class="w-6 h-6 mx-auto mb-1 text-pink-400" />
                              <div class="text-sm text-slate-400">{{ __('stages.pass_rate') }}</div>
                              <div class="text-white font-bold">{{ $stage->passing_percentage }}%</div>
                         </div>
                         <div class="bg-white/5 rounded-xl p-3 text-center">
                              <x-icon name="medal" class="w-6 h-6 mx-auto mb-1 text-emerald-400" />
                              <div class="text-sm text-slate-400">{{ __('stages.reward') }}</div>
                              <div class="text-emerald-400 font-bold">+{{ __('stages.points', ['points' => $stage->points_reward]) }}</div>
                         </div>
                    </div>

                    <div class="mt-6">
                         <form action="{{ route('quiz.start', $stage) }}" method="POST">
                              @csrf
                              <button type="submit"
                                   class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white py-3 px-6 rounded-xl font-bold text-lg transition-all duration-200 shadow-lg hover:shadow-purple-500/30">
                                   @if($isCompleted)
                                        <x-icon name="refresh" class="w-5 h-5" /> {{ __('stages.retry_quiz') }}
                                   @else
                                        <x-icon name="rocket" class="w-5 h-5" /> {{ __('stages.start_quiz') }}
                                   @endif
                              </button>
                         </form>
                    </div>
               </div>

               {{-- Best Attempt --}}
               @if($bestAttempt)
                    <div class="bg-emerald-500/10 rounded-2xl p-6 border border-emerald-500/20 mb-6">
                         <h3 class="text-lg font-bold text-emerald-400 mb-2 flex items-center gap-2"><x-icon name="trophy"
                                   class="w-5 h-5" /> {{ __('stages.best_score') }}</h3>
                         <div class="flex items-center space-x-4">
                              <span class="text-4xl font-bold text-white">{{ $bestAttempt->score_percentage }}%</span>
                              <span class="text-slate-400">({{ $bestAttempt->score }}/{{ $bestAttempt->total_questions }}
                                   {{ __('stages.correct') }})</span>
                         </div>
                    </div>
               @endif

               {{-- Attempt History --}}
               @if($attemptHistory->count() > 0)
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                         <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2"><x-icon name="chart-bar"
                                   class="w-5 h-5 text-blue-400" /> {{ __('stages.attempt_history') }}</h3>
                         <div class="space-y-2">
                              @foreach($attemptHistory as $attempt)
                                   <div class="flex items-center justify-between p-3 rounded-xl bg-white/5">
                                        <div>
                                             <span class="text-sm text-slate-300">{{ $attempt->created_at->format('M d, Y H:i') }}</span>
                                             @if($attempt->time_spent_seconds !== null && $attempt->time_spent_seconds >= 0)
                                                  <span class="text-xs text-slate-500 ms-2">({{ gmdate('i:s', (int) $attempt->time_spent_seconds) }})</span>
                                             @endif
                                        </div>
                                        <div class="flex items-center space-x-3">
                                             <span
                                                  class="text-sm font-medium {{ $attempt->passed ? 'text-emerald-400' : 'text-red-400' }}">
                                                  {{ $attempt->score }}/{{ $attempt->total_questions }}
                                             </span>
                                             <span
                                                  class="flex items-center gap-1 text-xs px-2 py-1 rounded-full {{ $attempt->passed ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' }}">
                                                  @if($attempt->passed) <x-icon name="check" class="w-3 h-3" /> @else <x-icon
                                                  name="x-circle" class="w-3 h-3" /> @endif
                                                  {{ $attempt->passed ? __('stages.passed') : __('stages.failed') }}
                                             </span>
                                             <a href="{{ route('quiz.result', $attempt) }}"
                                                  class="flex items-center gap-1 text-cyan-400 hover:text-cyan-300 text-sm">{{ __('stages.view') }}
                                                  <x-icon name="arrow-right" class="w-3.5 h-3.5 rtl:rotate-180" /></a>
                                        </div>
                                   </div>
                              @endforeach
                         </div>
                    </div>
               @endif
          </div>
     </div>
</x-app-layout>