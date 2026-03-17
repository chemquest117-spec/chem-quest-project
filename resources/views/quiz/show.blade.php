<x-app-layout>
     @section('title', 'Quiz - ' . $stage->title)

     <div class="py-8" x-data="quizTimer({{ $remainingSeconds }})">
          <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

               {{-- Timer Bar --}}
               <div
                    class="sticky top-16 z-40 bg-slate-900/95 backdrop-blur-md rounded-2xl p-4 mb-6 border border-white/10 shadow-2xl">
                    <div class="flex items-center justify-between">
                         <div>
                              <h2 class="text-lg font-bold text-white">{{ $stage->title }}</h2>
                              <p class="text-xs text-slate-400 flex items-center gap-1"><x-icon name="document-text"
                                        class="w-3 h-3" /> {{ $answers->count() }} questions</p>
                         </div>
                         <div class="flex items-center space-x-4">
                              <div class="text-right">
                                   <div class="text-xs text-slate-400 flex items-center gap-1 justify-end"><x-icon
                                             name="clock" class="w-3 h-3" /> Time Remaining</div>
                                   <div class="text-2xl font-mono font-bold"
                                        :class="remaining <= 60 ? 'text-red-400 animate-pulse' : (remaining <= 180 ? 'text-amber-400' : 'text-emerald-400')"
                                        x-text="display"></div>
                              </div>
                              <button type="submit" form="quiz-form"
                                   class="flex items-center gap-1.5 bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white px-6 py-2 rounded-xl font-bold transition-all duration-200 shadow-lg">
                                   Submit <x-icon name="check" class="w-4 h-4" />
                              </button>
                         </div>
                    </div>
                    {{-- Progress bar for time --}}
                    <div class="mt-3 w-full bg-white/10 rounded-full h-1.5">
                         <div class="h-1.5 rounded-full transition-all duration-1000"
                              :class="remaining <= 60 ? 'bg-red-500' : (remaining <= 180 ? 'bg-amber-500' : 'bg-emerald-500')"
                              :style="'width: ' + timePercent + '%'"></div>
                    </div>
               </div>

               {{-- Quiz Form --}}
               <form id="quiz-form" action="{{ route('quiz.submit', $attempt) }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                         @foreach($answers as $index => $answer)
                                             <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-white/20 transition-all duration-200"
                                                  x-data="{ selected: null }">

                                                  {{-- Question Header --}}
                                                  <div class="flex items-start space-x-4 mb-4">
                                                       <span
                                                            class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-500/20 text-purple-400 flex items-center justify-center text-sm font-bold">
                                                            {{ $index + 1 }}
                                                       </span>
                                                       <div>
                                                            <p class="text-white font-medium">{{ $answer->question->question_text }}</p>
                                                            <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded text-xs
                                                                   {{ $answer->question->difficulty === 'easy' ? 'bg-green-500/20 text-green-400' :
                              ($answer->question->difficulty === 'medium' ? 'bg-amber-500/20 text-amber-400' :
                                   'bg-red-500/20 text-red-400') }}">
                                                                 @if($answer->question->difficulty === 'easy')
                                                                      <x-icon name="shield-check" class="w-3 h-3" />
                                                                 @elseif($answer->question->difficulty === 'medium')
                                                                      <x-icon name="lightning-bolt" class="w-3 h-3" />
                                                                 @else
                                                                      <x-icon name="lightning-bolt" class="w-3 h-3" />
                                                                 @endif
                                                                 {{ ucfirst($answer->question->difficulty) }}
                                                            </span>
                                                       </div>
                                                  </div>

                                                  {{-- Options --}}
                                                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 ml-12">
                                                       @foreach(['a', 'b', 'c', 'd'] as $option)
                                                            <label class="relative cursor-pointer group" @click="selected = '{{ $option }}'">
                                                                 <input type="radio" name="answers[{{ $answer->question_id }}]"
                                                                      value="{{ $option }}" class="sr-only peer">
                                                                 <div class="p-3 rounded-xl border-2 transition-all duration-200
                                                                                 peer-checked:border-purple-500 peer-checked:bg-purple-500/10
                                                                                 border-white/10 hover:border-white/30 hover:bg-white/5">
                                                                      <div class="flex items-center space-x-3">
                                                                           <span class="w-7 h-7 rounded-full border-2 flex items-center justify-center text-xs font-bold transition-all duration-200
                                                                                          peer-checked:border-purple-500 peer-checked:bg-purple-500 peer-checked:text-white
                                                                                          border-white/20 text-slate-400 group-hover:border-white/40"
                                                                                :class="selected === '{{ $option }}' ? 'border-purple-500 bg-purple-500 text-white' : ''">
                                                                                {{ strtoupper($option) }}
                                                                           </span>
                                                                           <span
                                                                                class="text-sm text-slate-300">{{ $answer->question->{'option_' . $option} }}</span>
                                                                      </div>
                                                                 </div>
                                                            </label>
                                                       @endforeach
                                                  </div>
                                             </div>
                         @endforeach
                    </div>

                    {{-- Bottom Submit Button --}}
                    <div class="mt-8 text-center">
                         <button type="submit"
                              class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white px-12 py-3 rounded-xl font-bold text-lg transition-all duration-200 shadow-lg hover:shadow-emerald-500/30">
                              Submit Quiz <x-icon name="check-circle" class="w-5 h-5" />
                         </button>
                    </div>
               </form>
          </div>
     </div>

     <script>
          function quizTimer(totalSeconds) {
               return {
                    remaining: totalSeconds,
                    total: totalSeconds,
                    interval: null,
                    init() {
                         this.interval = setInterval(() => {
                              this.remaining--;
                              if (this.remaining <= 0) {
                                   clearInterval(this.interval);
                                   document.getElementById('quiz-form').submit();
                              }
                         }, 1000);
                    },
                    get display() {
                         const m = Math.floor(this.remaining / 60);
                         const s = this.remaining % 60;
                         return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                    },
                    get timePercent() {
                         return Math.max(0, (this.remaining / this.total) * 100);
                    }
               };
          }
     </script>
</x-app-layout>