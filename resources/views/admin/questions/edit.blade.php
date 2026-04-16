<x-app-layout>
     @section('title', 'Edit Question')

     <div class="py-8">
          <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
               <a href="{{ route('admin.stages.questions.index', $stage) }}"
                    class="text-slate-400 hover:text-white text-sm">← Back to Questions</a>
               <h1 class="text-3xl font-bold text-white mt-2 mb-8">Edit Question</h1>

               <form action="{{ route('admin.stages.questions.update', [$stage, $question]) }}" method="POST" enctype="multipart/form-data"
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10 space-y-6">
                    @csrf @method('PUT')

                    <div>
                         <label class="block text-sm font-medium text-slate-300 mb-2">Question Image (Optional)</label>
                         @if($question->image)
                         <div class="mb-4 relative group">
                              <img src="{{ asset('storage/' . $question->image) }}" alt="Question Image" class="w-32 h-32 object-cover rounded-lg border border-white/10">
                              <div class="mt-1 text-xs text-slate-500">Current Image</div>
                         </div>
                         @endif
                         <input type="file" name="image" accept="image/*"
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-cyan-500">
                         @error('image') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-slate-300 mb-2">Question Type</label>
                         <select name="type" id="question_type" required
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-cyan-500">
                              <option value="mcq" {{ old('type', $question->type ?? 'mcq') === 'mcq' ? 'selected' : '' }}>Multiple Choice</option>
                              <option value="complete" {{ old('type', $question->type) === 'complete' ? 'selected' : '' }}>{{ __('quiz.complete_question') }}</option>
                         </select>
                         @error('type') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-slate-300 mb-2">Question Text</label>
                         <textarea name="question_text" id="question_text" rows="3" required
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-cyan-500"
                              placeholder="{{ __('quiz.complete_question_placeholder') }}">{{ old('question_text', $question->question_text) }}</textarea>
                         @error('question_text') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-slate-300 mb-2">Explanation (Optional)</label>
                         <textarea name="explanation" rows="4"
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-cyan-500">{{ old('explanation', $question->explanation) }}</textarea>
                         @error('explanation') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- MCQ Fields --}}
                    <div id="mcq-fields" class="{{ old('type', $question->type ?? 'mcq') === 'complete' ? 'hidden' : '' }} space-y-6">
                         @foreach(['a', 'b', 'c', 'd'] as $opt)
                         <div>
                              <label class="block text-sm font-medium text-slate-300 mb-2">Option
                                   {{ strtoupper($opt) }}</label>
                              <input type="text" name="option_{{ $opt }}" id="option_{{ $opt }}"
                                   value="{{ old('option_' . $opt, $question->{'option_' . $opt}) }}"
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-cyan-500">
                         </div>
                         @endforeach

                         <div>
                              <label class="block text-sm font-medium text-slate-300 mb-2">Correct Answer</label>
                              <select name="correct_answer" id="correct_answer"
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-cyan-500">
                                   @foreach(['a', 'b', 'c', 'd'] as $opt)
                                   <option value="{{ $opt }}" {{ old('correct_answer', $question->correct_answer) === $opt ? 'selected' : '' }}>{{ strtoupper($opt) }}</option>
                                   @endforeach
                              </select>
                         </div>
                    </div>

                    {{-- Complete Question Fields --}}
                    <div id="complete-fields" class="{{ old('type', $question->type ?? 'mcq') !== 'complete' ? 'hidden' : '' }} space-y-4">
                         <div class="p-4 rounded-xl bg-violet-500/10 border border-violet-500/20">
                              <p class="text-sm text-violet-300 mb-1">{{ __('quiz.blank_instructions') }}</p>
                              <p class="text-xs text-slate-400">{{ __('quiz.blank_instructions_detail') }}</p>
                              <p class="mt-2 text-sm text-violet-400 font-bold" id="blank-count-display">{{ __('quiz.blanks_detected', ['count' => 0]) }}</p>
                         </div>
                         <div id="blanks-container" class="space-y-3">
                              {{-- Dynamic blank answer fields inserted by JS --}}
                         </div>
                         @error('expected_answers') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                         @error('expected_answers.*.value') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                         <div>
                              <label class="block text-sm font-medium text-slate-300 mb-2">Difficulty</label>
                              <select name="difficulty" required
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-cyan-500">
                                   @foreach(['easy', 'medium', 'hard'] as $level)
                                   <option value="{{ $level }}" {{ old('difficulty', $question->difficulty) === $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
                                   @endforeach
                              </select>
                         </div>
                    </div>

                    <button type="submit"
                         class="w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white py-3 rounded-xl font-bold transition shadow-lg">
                         Update Question
                    </button>
               </form>
          </div>
     </div>

     <script>
          document.addEventListener('DOMContentLoaded', function() {
               const typeSelect = document.getElementById('question_type');
               const questionTextArea = document.getElementById('question_text');
               const mcqFields = document.getElementById('mcq-fields');
               const completeFields = document.getElementById('complete-fields');
               const blanksContainer = document.getElementById('blanks-container');
               const blankCountDisplay = document.getElementById('blank-count-display');
               const existingAnswers = @json(old('expected_answers', $question->expected_answers ?? []));

               function updateFields() {
                    if (typeSelect.value === 'complete') {
                         mcqFields.classList.add('hidden');
                         completeFields.classList.remove('hidden');
                         detectBlanks();
                    } else {
                         completeFields.classList.add('hidden');
                         mcqFields.classList.remove('hidden');
                    }
               }

               function detectBlanks() {
                    const text = questionTextArea.value;
                    const count = (text.match(/_{3,}/g) || []).length;
                    blankCountDisplay.textContent = `${count} {{ __('quiz.blanks_word') }}`;

                    const existing = blanksContainer.querySelectorAll('.blank-answer-group');

                    for (let i = existing.length; i < count; i++) {
                         addBlankField(i);
                    }

                    while (blanksContainer.querySelectorAll('.blank-answer-group').length > count) {
                         blanksContainer.removeChild(blanksContainer.lastElementChild);
                    }
               }

               function addBlankField(index) {
                    const oldValue = existingAnswers[index]?.value ?? '';
                    const oldTolerance = existingAnswers[index]?.tolerance ?? '';

                    const div = document.createElement('div');
                    div.className = 'blank-answer-group flex items-center gap-3 p-4 rounded-xl bg-white/5 border border-white/10';
                    div.innerHTML = `
                         <span class="text-violet-400 font-bold whitespace-nowrap text-sm">#${index + 1}</span>
                         <div class="flex-1">
                              <label class="block text-xs text-slate-400 mb-1">{{ __('quiz.expected_numeric_answer') }} *</label>
                              <input type="number" name="expected_answers[${index}][value]" step="any" required
                                   value="${oldValue}"
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-cyan-500"
                                   placeholder="{{ __('quiz.enter_numeric_value') }}">
                         </div>
                         <div class="flex-1">
                              <label class="block text-xs text-slate-400 mb-1">{{ __('quiz.tolerance') }} (±)</label>
                              <input type="number" name="expected_answers[${index}][tolerance]" step="any" min="0"
                                   value="${oldTolerance}"
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-cyan-500"
                                   placeholder="{{ __('quiz.tolerance_placeholder') }}">
                         </div>
                    `;
                    blanksContainer.appendChild(div);
               }

               questionTextArea.addEventListener('input', () => {
                    if (typeSelect.value === 'complete') {
                         detectBlanks();
                    }
               });

               typeSelect.addEventListener('change', updateFields);
               updateFields();
          });
     </script>
</x-app-layout>