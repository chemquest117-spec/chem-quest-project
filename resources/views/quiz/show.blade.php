<x-app-layout>
     @section('title', __('quiz.quiz') . ' - ' . $stage->title)

     <div class="py-8 overscroll-y-contain" x-data="quizTimer({{ $remainingSeconds }}, {{ $totalSeconds }})">
          <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

               {{-- Timer Bar --}}
               <div
                    class="sticky top-16 z-40 bg-slate-900/95 backdrop-blur-md rounded-2xl p-4 mb-6 border border-white/10 shadow-[0_0_30px_rgba(0,0,0,0.5)] transition-shadow duration-300"
                    :class="remaining <= 60 ? 'shadow-[0_0_30px_rgba(239,68,68,0.2)]' : ''">
                    <div class="flex items-center justify-between">
                         <div>
                              <h2 class="text-lg font-bold text-white">{{ $stage->title }}</h2>
                              <p class="text-xs text-slate-400 flex items-center gap-1"><x-icon name="document-text"
                                        class="w-3 h-3" /> {{ __('stages.questions_count', ['count' => $answers->count()]) }}</p>
                         </div>
                         <div class="flex items-center space-x-4">
                              <div class="text-right">
                                   <div class="text-xs text-slate-400 flex items-center gap-1 justify-end"><x-icon
                                             name="clock" class="w-3 h-3" /> {{ __('quiz.time_remaining') }}</div>
                                   <div class="text-2xl font-mono font-bold flex items-baseline justify-end gap-1">
                                        <span :class="remaining <= 60 ? 'text-red-400 animate-pulse' : (remaining <= 180 ? 'text-amber-400' : 'text-emerald-400')"
                                              x-text="display"></span>
                                        <span class="text-sm text-slate-500 font-medium tracking-normal whitespace-pre">/ <span x-text="totalDisplay"></span></span>
                                   </div>
                              </div>
                              <button type="submit" form="quiz-form"
                                   class="flex items-center gap-1.5 bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white px-6 py-2 rounded-xl font-bold transition-all duration-200 shadow-lg">
                                   {{ __('quiz.submit') }} <x-icon name="check" class="w-4 h-4" />
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
                                             <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 transition-all duration-300 transform outline-none focus-within:ring-2 focus-within:ring-purple-400/50 select-none touch-manipulation"
                                                  style="-webkit-touch-callout: none; -webkit-user-select: none; user-select: none;"
                                                  :class="selected ? 'border-purple-500/30 bg-white/10 shadow-[0_0_20px_rgba(168,85,247,0.1)]' : 'hover:border-white/30 hover:shadow-lg'"
                                                  x-data="{ selected: null, mounted: false, ...{ delay: {{ $index * 100 }} } }"
                                                  x-init="setTimeout(() => mounted = true, delay)"
                                                  :style="mounted ? 'opacity: 1; transform: translateY(0);' : 'opacity: 0; transform: translateY(20px); transition: all 0.5s ease-out;'">

                                                  {{-- Question Header --}}
                                                  <div class="flex items-start space-x-4 mb-4">
                                                       <span
                                                            class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-500/20 text-purple-400 flex items-center justify-center text-sm font-bold">
                                                            {{ $index + 1 }}
                                                       </span>
                                                       <div class="flex-1 min-w-0">
                                                             @if($answer->question->isComplete())
                                                                  {{-- Question text with inline blank inputs --}}
                                                                  <div class="text-white font-medium text-sm sm:text-base leading-[2.5]">
                                                                       @php
                                                                            $questionText = e($answer->question->getTranslatedQuestionText());
                                                                            $parts = preg_split('/_{3,}/', $questionText);
                                                                            $blankCount = count($parts) - 1;
                                                                       @endphp
                                                                       @foreach($parts as $pi => $part)
                                                                            <span>{!! $part !!}</span>@if($pi < $blankCount)<input type="number"
                                                                                 class="inline-blank-input w-16 sm:w-20 bg-white/5 border-2 border-violet-400/40 hover:border-violet-400/60 focus:border-violet-500 rounded-lg px-2 py-0.5 mx-1 text-center text-violet-300 focus:outline-none transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                                                                 data-question-id="{{ $answer->question_id }}"
                                                                                 data-blank-index="{{ $pi }}"
                                                                                 step="any" inputmode="decimal" placeholder="?"
                                                                                 @input="selected = true">@endif
                                                                       @endforeach
                                                                  </div>
                                                             @else
                                                                  <p class="text-white font-medium text-sm sm:text-base">{{ $answer->question->getTranslatedQuestionText() }}</p>
                                                             @endif
                                                              @if($answer->question->image)
                                                                   <div class="mt-3 mb-2">
                                                                        <img src="{{ asset('storage/' . $answer->question->image) }}" alt="{{ __('quiz.question_image') }}" class="max-w-full sm:max-w-md rounded-xl border border-white/10 shadow-lg object-contain">
                                                                   </div>
                                                              @endif
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
                                                                 {{ ucfirst($answer->question->getTranslatedDifficulty()) }}
                                                            </span>
                                                       </div>
                                                  </div>

                                                  {{-- Options --}}
                                                  @if($answer->question->isComplete())
                                                       {{-- Hidden field collects all inline blank values as JSON --}}
                                                       <input type="hidden" name="answers[{{ $answer->question_id }}]"
                                                            class="inline-hidden-answer" data-question-id="{{ $answer->question_id }}">
                                                  @else
                                                       <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 ms-12">
                                                            @foreach(['a', 'b', 'c', 'd'] as $option)
                                                                 <label class="relative cursor-pointer group" @click="selected = '{{ $option }}'">
                                                                      <input type="radio" name="answers[{{ $answer->question_id }}]"
                                                                           value="{{ $option }}" class="sr-only peer">
                                                                      <div class="p-3 rounded-xl border-2 transition-all duration-300 transform
                                                                                      border-white/10 hover:border-emerald-400/40 hover:bg-white/5"
                                                                           :class="selected === '{{ $option }}' ? 'scale-[1.02] border-emerald-500 bg-emerald-500/10 shadow-[0_0_15px_rgba(16,185,129,0.3)]' : ''">
                                                                           <div class="flex items-center space-x-3">
                                                                                <span class="w-7 h-7 rounded-full border-2 flex items-center justify-center text-xs font-bold transition-all duration-300
                                                                                               border-white/20 text-slate-400 group-hover:border-emerald-400/60"
                                                                                     :class="selected === '{{ $option }}' ? 'border-emerald-500 bg-emerald-500 text-white shadow-[0_0_10px_rgba(16,185,129,0.5)] animate-pulse' : ''">
                                                                                     {{ strtoupper($option) }}
                                                                                </span>
                                                                                <span
                                                                                     class="text-sm text-slate-300">{{ $answer->question->getTranslatedOption($option) }}</span>
                                                                           </div>
                                                                      </div>
                                                                 </label>
                                                            @endforeach
                                                       </div>
                                                  @endif
                                             </div>
                         @endforeach
                    </div>

                    {{-- Bottom Submit Button --}}
                    <div class="mt-8 text-center">
                         <button type="submit"
                              class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white px-12 py-3 rounded-xl font-bold text-lg transition-all duration-200 shadow-lg hover:shadow-emerald-500/30">
                              {{ __('quiz.submit') }} <x-icon name="check-circle" class="w-5 h-5" />
                         </button>
                    </div>
               </form>
          </div>
     </div>

     <style>
          /* Prevent Printing */
          @media print {
               body { display: none !important; }
               .no-print { display: none !important; }
          }
          
          /* Extra Blur on Focus Loss */
          .quiz-blur {
               filter: blur(20px);
               transition: filter 0.3s ease;
          }
     </style>

     <script>
          function quizTimer(remainingSeconds, totalSeconds) {
               return {
                    remaining: remainingSeconds,
                    total: totalSeconds,
                    interval: null,
                    tabSwitches: 0,
                    isSubmitting: false,
                    init() {
                         // Get CSRF token from meta tag (auto-refreshes with session)
                         const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

                         this.interval = setInterval(() => {
                              this.remaining--;
                              if (this.remaining <= 0) {
                                   clearInterval(this.interval);
                                   this.isSubmitting = true;
                                   document.getElementById('quiz-form').submit();
                              }
                         }, 1000);

                         // Warn before leaving (except on submit)
                         document.getElementById('quiz-form').addEventListener('submit', () => { 
                              this.isSubmitting = true; 
                         });

                         window.addEventListener('beforeunload', (e) => {
                              if (!this.isSubmitting) {
                                   e.preventDefault();
                                   e.returnValue = '';
                              }
                         });

                          // Anti-cheat: prevent copy/paste/contextmenu
                          const antiCheatWarning = () => {
                               window.dispatchEvent(new CustomEvent('toast', {
                                    detail: {
                                         type: 'error',
                                         message: '{{ __("quiz.anti_cheat") }}'
                                    }
                               }));
                          };

                          document.addEventListener('contextmenu', e => { e.preventDefault(); antiCheatWarning(); });
                          document.addEventListener('copy', e => { e.preventDefault(); try { navigator.clipboard.writeText(''); }catch(e){} antiCheatWarning(); });
                          document.addEventListener('paste', e => { e.preventDefault(); antiCheatWarning(); });
                          document.addEventListener('cut', e => { e.preventDefault(); try { navigator.clipboard.writeText(''); }catch(e){} antiCheatWarning(); });
                          
                          // Explicit text selection blocking outside of textareas/inputs
                          document.addEventListener('selectstart', e => {
                               if (e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'INPUT') {
                                   e.preventDefault();
                               }
                          });

                          // Block screenshot/devtool shortcuts (Windows + Shift + S, Cmd + Shift + 3/4/5, PrintScreen)
                          const preventScreenshots = (e) => {
                               // PrintScreen key
                               if (e.key === 'PrintScreen' || e.keyCode === 44) {
                                    e.preventDefault();
                                    antiCheatWarning();
                                    try { navigator.clipboard.writeText(''); } catch(err){} 
                                    return false;
                               }
                               // Snipping tools
                               if (e.metaKey && e.shiftKey && (e.key === 's' || e.key === 'S' || e.key === '3' || e.key === '4' || e.key === '5')) {
                                    e.preventDefault();
                                    antiCheatWarning();
                                    return false;
                               }
                               // DevTools / View Source
                               if (
                                    (e.ctrlKey && e.shiftKey && (e.key === 'i' || e.key === 'I' || e.key === 'j' || e.key === 'J' || e.key === 'c' || e.key === 'C')) || 
                                    (e.key === 'F12') || 
                                    (e.ctrlKey && (e.key === 'u' || e.key === 'U' || e.key === 'p' || e.key === 'P' || e.key === 's' || e.key === 'S'))
                               ) {
                                    e.preventDefault();
                                    antiCheatWarning();
                                    return false;
                               }
                          };

                          document.addEventListener('keydown', preventScreenshots);
                          document.addEventListener('keyup', preventScreenshots);

                          // Window focus loss tracking (Blocks split-screen cheating)
                          this.isFocused = document.hasFocus();

                          const handleFocusLoss = () => {
                               if (!this.isFocused) return; // Prevent double trigger
                               this.isFocused = false;
                               
                               const container = document.querySelector('.py-8');
                               if(container) container.classList.add('quiz-blur');
                               try { navigator.clipboard.writeText(''); } catch(err){} 
                               
                               if (!this.isSubmitting) {
                                   this.tabSwitches++;
                                   
                                   if (this.tabSwitches >= 3) {
                                        window.dispatchEvent(new CustomEvent('toast', {
                                             detail: { type: 'error', message: '{{ __("quiz.tab_switch_limit") }}' }
                                        }));
                                        this.isSubmitting = true;
                                        setTimeout(() => document.getElementById('quiz-form').submit(), 1500);
                                   } else {
                                        window.dispatchEvent(new CustomEvent('toast', {
                                             detail: { 
                                                  type: 'warning', 
                                                  message: '{{ __("quiz.tab_switch_warning") }}'.replace(':count', this.tabSwitches) 
                                             }
                                        }));
                                   }
                               }
                          };

                          const handleFocusGain = () => {
                               if (this.isFocused) return;
                               this.isFocused = true;
                               if (!this.isSubmitting) {
                                   const container = document.querySelector('.py-8');
                                   if(container) container.classList.remove('quiz-blur');
                               }
                          };

                          window.addEventListener('blur', handleFocusLoss);
                          window.addEventListener('focus', handleFocusGain);
                          document.addEventListener('visibilitychange', () => {
                               if (document.hidden) handleFocusLoss();
                               else handleFocusGain();
                          });

                          // Auto-save helper with retry on 419 (CSRF token mismatch)
                          const autoSave = (url, data, retries = 1) => {
                               fetch(url, {
                                    method: 'POST',
                                    headers: {
                                         'Content-Type': 'application/json',
                                         'X-CSRF-TOKEN': csrfToken(),
                                         'Accept': 'application/json'
                                    },
                                    body: JSON.stringify(data)
                               }).then(resp => {
                                    if (resp.status === 419 && retries > 0) {
                                         // Token expired — retry once after short delay
                                         setTimeout(() => autoSave(url, data, retries - 1), 500);
                                    } else if (!resp.ok) {
                                         console.warn('Auto-save returned:', resp.status);
                                    }
                               }).catch(err => console.log('Auto-save failed:', err));
                          };

                         // Auto-save answers on selection
                         document.querySelectorAll('input[type="radio"]').forEach(radio => {
                              radio.addEventListener('change', (e) => {
                                   const name = e.target.name;
                                   const match = name.match(/answers\[(\d+)\]/);
                                   if (match) {
                                        autoSave('{{ route("quiz.saveAnswer", $attempt) }}', {
                                             question_id: parseInt(match[1]),
                                             answer: e.target.value
                                        });
                                   }
                              });
                         });

                         // Auto-save answers for complete question inline blanks
                         let numericTimeouts = {};

                         function updateHiddenAnswer(questionId) {
                              const inputs = document.querySelectorAll(`.inline-blank-input[data-question-id="${questionId}"]`);
                              const values = Array.from(inputs).map(inp => inp.value);
                              const hidden = document.querySelector(`.inline-hidden-answer[data-question-id="${questionId}"]`);
                              if (hidden) {
                                   hidden.value = JSON.stringify(values);
                              }
                              return values;
                         }

                         document.querySelectorAll('.inline-blank-input').forEach(input => {
                              input.addEventListener('input', function(e) {
                                   const questionId = e.target.dataset.questionId;
                                   const values = updateHiddenAnswer(questionId);

                                   if (numericTimeouts[questionId]) {
                                        clearTimeout(numericTimeouts[questionId]);
                                   }

                                   numericTimeouts[questionId] = setTimeout(() => {
                                        autoSave('{{ route("quiz.saveAnswer", $attempt) }}', {
                                             question_id: parseInt(questionId),
                                             answer: JSON.stringify(values)
                                        });
                                   }, 1000);
                              });
                         });

                         // Ensure hidden fields are populated before form submit
                         document.querySelector('form')?.addEventListener('submit', function() {
                              const qids = new Set();
                              document.querySelectorAll('.inline-blank-input').forEach(inp => qids.add(inp.dataset.questionId));
                              qids.forEach(qid => updateHiddenAnswer(qid));
                         });
                    },
                    get display() {
                         const m = Math.floor(this.remaining / 60);
                         const s = this.remaining % 60;
                         return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                    },
                    get totalDisplay() {
                         const m = Math.floor(this.total / 60);
                         const s = this.total % 60;
                         return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                    },
                    get timePercent() {
                         return Math.max(0, (this.remaining / this.total) * 100);
                    }
               };
          }
     </script>
</x-app-layout>