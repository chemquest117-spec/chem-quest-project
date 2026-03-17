<x-app-layout>
     @section('title', 'Quiz Results')

     <div class="py-8">
          <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

               {{-- Result Header --}}
               <div class="text-center mb-8">
                    @if($attempt->passed)
                         <div class="mb-4 animate-bounce">
                              <x-icon name="sparkles" class="w-16 h-16 text-emerald-400 mx-auto" />
                         </div>
                         <h1 class="text-3xl font-bold text-emerald-400">Congratulations!</h1>
                         <p class="text-slate-400 mt-1">You passed {{ $attempt->stage->title }}!</p>
                    @else
                         <div class="mb-4">
                              <x-icon name="lightning-bolt" class="w-16 h-16 text-amber-400 mx-auto" />
                         </div>
                         <h1 class="text-3xl font-bold text-amber-400">Keep Going!</h1>
                         <p class="text-slate-400 mt-1">You need {{ $attempt->stage->passing_percentage }}% to pass. Don't
                              give up!</p>
                    @endif
               </div>

               {{-- Score Card --}}
               <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border mb-8
                     {{ $attempt->passed ? 'border-emerald-500/30' : 'border-amber-500/30' }}">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
                         <div>
                              <div
                                   class="text-4xl font-bold {{ $attempt->passed ? 'text-emerald-400' : 'text-amber-400' }}">
                                   {{ $attempt->score_percentage }}%
                              </div>
                              <div class="text-sm text-slate-400 mt-1">Score</div>
                         </div>
                         <div>
                              <div class="text-4xl font-bold text-white">
                                   {{ $attempt->score }}/{{ $attempt->total_questions }}
                              </div>
                              <div class="text-sm text-slate-400 mt-1">Correct</div>
                         </div>
                         <div>
                              <div class="text-4xl font-bold text-cyan-400">
                                   {{ $attempt->time_spent_seconds ? gmdate('i:s', $attempt->time_spent_seconds) : '-' }}
                              </div>
                              <div class="text-sm text-slate-400 mt-1">Time Taken</div>
                         </div>
                         <div>
                              <div class="flex justify-center">
                                   @if($attempt->passed)
                                        <x-icon name="check-circle" class="w-10 h-10 text-emerald-400" />
                                   @else
                                        <x-icon name="x-circle" class="w-10 h-10 text-red-400" />
                                   @endif
                              </div>
                              <div class="text-sm text-slate-400 mt-1">{{ $attempt->passed ? 'Passed' : 'Failed' }}
                              </div>
                         </div>
                    </div>
               </div>

               {{-- Question Review --}}
               <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 mb-8">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2"><x-icon name="document-text"
                              class="w-5 h-5 text-blue-400" /> Question Review</h2>
                    <div class="space-y-4">
                         @foreach($attempt->answers as $index => $answer)
                              <div
                                   class="p-4 rounded-xl border
                                       {{ $answer->is_correct ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-red-500/5 border-red-500/20' }}">
                                   <div class="flex items-start space-x-3">
                                        <span
                                             class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                             {{ $answer->is_correct ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }}">
                                             @if($answer->is_correct)
                                                  <x-icon name="check" class="w-4 h-4" />
                                             @else
                                                  <x-icon name="x-circle" class="w-4 h-4" />
                                             @endif
                                        </span>
                                        <div class="flex-1">
                                             <p class="text-white font-medium mb-2">{{ $answer->question->question_text }}
                                             </p>

                                             <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                  @foreach(['a', 'b', 'c', 'd'] as $opt)
                                                                                     <div class="p-2 rounded-lg text-sm flex items-center space-x-2
                                                                                                   {{ $opt === $answer->question->correct_answer ? 'bg-emerald-500/20 text-emerald-300 ring-1 ring-emerald-500/30' :
                                                       ($opt === $answer->selected_answer && !$answer->is_correct ? 'bg-red-500/20 text-red-300 ring-1 ring-red-500/30' :
                                                            'bg-white/5 text-slate-400') }}">
                                                                                          <span class="w-5 h-5 rounded-full border flex items-center justify-center text-xs
                                                                                                       {{ $opt === $answer->question->correct_answer ? 'border-emerald-500 bg-emerald-500 text-white' :
                                                       ($opt === $answer->selected_answer && !$answer->is_correct ? 'border-red-500 bg-red-500 text-white' :
                                                            'border-white/20') }}">
                                                                                               {{ strtoupper($opt) }}
                                                                                          </span>
                                                                                          <span>{{ $answer->question->{'option_' . $opt} }}</span>
                                                                                          @if($opt === $answer->question->correct_answer)
                                                                                               <span class="flex items-center gap-0.5 text-emerald-400 ml-auto"><x-icon
                                                                                                         name="check" class="w-3 h-3" /> Correct</span>
                                                                                          @elseif($opt === $answer->selected_answer && !$answer->is_correct)
                                                                                               <span class="text-red-400 ml-auto">Your answer</span>
                                                                                          @endif
                                                                                     </div>
                                                  @endforeach
                                             </div>

                                             @if(!$answer->selected_answer)
                                                  <p class="flex items-center gap-1 text-xs text-slate-500 mt-1 italic">
                                                       <x-icon name="x-circle" class="w-3 h-3" /> Not answered
                                                  </p>
                                             @endif
                                        </div>
                                   </div>
                              </div>
                         @endforeach
                    </div>
               </div>

               {{-- Action Buttons --}}
               <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @if(!$attempt->passed)
                         <form action="{{ route('quiz.start', $attempt->stage) }}" method="POST">
                              @csrf
                              <button type="submit"
                                   class="w-full sm:w-auto flex items-center justify-center gap-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-8 py-3 rounded-xl font-bold transition-all duration-200 shadow-lg">
                                   <x-icon name="refresh" class="w-5 h-5" /> Retry Quiz
                              </button>
                         </form>
                    @endif
                    <a href="{{ route('stages.index') }}"
                         class="flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white px-8 py-3 rounded-xl font-medium transition-all duration-200">
                         <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> Back to Stages
                    </a>
                    <a href="{{ route('dashboard') }}"
                         class="flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white px-8 py-3 rounded-xl font-medium transition-all duration-200">
                         <x-icon name="chart-bar" class="w-4 h-4" /> Dashboard
                    </a>
               </div>
          </div>
     </div>
</x-app-layout>