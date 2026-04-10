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
                              <option value="essay" {{ old('type', $question->type) === 'essay' ? 'selected' : '' }}>Essay</option>
                         </select>
                         @error('type') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-slate-300 mb-2">Question Text</label>
                         <textarea name="question_text" rows="3" required
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-cyan-500">{{ old('question_text', $question->question_text) }}</textarea>
                         @error('question_text') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-slate-300 mb-2">Explanation (Optional)</label>
                         <textarea name="explanation" rows="4"
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-cyan-500">{{ old('explanation', $question->explanation) }}</textarea>
                         @error('explanation') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="mcq-fields" class="{{ old('type', $question->type ?? 'mcq') === 'essay' ? 'hidden' : '' }} space-y-6">
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

                    <div id="essay-fields" class="{{ old('type', $question->type ?? 'mcq') === 'mcq' ? 'hidden' : '' }} space-y-6">
                         <div>
                              <label class="block text-sm font-medium text-slate-300 mb-2">Expected Answer <span class="text-xs text-slate-400">(keywords student must mention)</span></label>
                              <textarea name="expected_answer" id="expected_answer" rows="3"
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-cyan-500">{{ old('expected_answer', $question->expected_answer) }}</textarea>
                              @error('expected_answer') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                         </div>
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
               const mcqFields = document.getElementById('mcq-fields');
               const essayFields = document.getElementById('essay-fields');
               
               const mcqInputs = [
                    ...['a', 'b', 'c', 'd'].map(opt => document.getElementById('option_' + opt)),
                    document.getElementById('correct_answer')
               ];
               const expectedAnswerInput = document.getElementById('expected_answer');

               function updateFields() {
                    if (typeSelect.value === 'essay') {
                         mcqFields.classList.add('hidden');
                         essayFields.classList.remove('hidden');
                         mcqInputs.forEach(input => input.removeAttribute('required'));
                         expectedAnswerInput.setAttribute('required', 'required');
                    } else {
                         essayFields.classList.add('hidden');
                         mcqFields.classList.remove('hidden');
                         expectedAnswerInput.removeAttribute('required');
                         mcqInputs.forEach(input => input.setAttribute('required', 'required'));
                    }
               }

               typeSelect.addEventListener('change', updateFields);
               updateFields();
          });
     </script>
</x-app-layout>