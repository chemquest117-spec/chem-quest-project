<x-app-layout>
     @section('title', 'Add Question')

     <div class="py-8">
          <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
               <a href="{{ route('admin.stages.questions.index', $stage) }}"
                    class="text-slate-400 hover:text-white text-sm">← Back to Questions</a>
               <h1 class="text-3xl font-bold text-white mt-2 mb-8">Add Question to: {{ $stage->title }}</h1>

               <form action="{{ route('admin.stages.questions.store', $stage) }}" method="POST" enctype="multipart/form-data"
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10 space-y-6">
                    @csrf

                    <div>
                         <label class="block text-sm font-medium text-slate-300 mb-2">Question image (Optional)</label>
                         <input type="file" name="image" accept="image/*"
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-cyan-500">
                         @error('image') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-slate-300 mb-2">Question Text</label>
                         <textarea name="question_text" rows="3" required
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-cyan-500">{{ old('question_text') }}</textarea>
                         @error('question_text') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    @foreach(['a', 'b', 'c', 'd'] as $opt)
                         <div>
                              <label class="block text-sm font-medium text-slate-300 mb-2">Option
                                   {{ strtoupper($opt) }}</label>
                              <input type="text" name="option_{{ $opt }}" value="{{ old('option_' . $opt) }}" required
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-cyan-500">
                              @error('option_' . $opt) <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                         </div>
                    @endforeach

                    <div class="grid grid-cols-2 gap-4">
                         <div>
                              <label class="block text-sm font-medium text-slate-300 mb-2">Correct Answer</label>
                              <select name="correct_answer" required
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-cyan-500">
                                   <option value="a" {{ old('correct_answer') === 'a' ? 'selected' : '' }}>A</option>
                                   <option value="b" {{ old('correct_answer') === 'b' ? 'selected' : '' }}>B</option>
                                   <option value="c" {{ old('correct_answer') === 'c' ? 'selected' : '' }}>C</option>
                                   <option value="d" {{ old('correct_answer') === 'd' ? 'selected' : '' }}>D</option>
                              </select>
                         </div>
                         <div>
                              <label class="block text-sm font-medium text-slate-300 mb-2">Difficulty</label>
                              <select name="difficulty" required
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-cyan-500 focus:ring-cyan-500">
                                   <option value="easy" {{ old('difficulty') === 'easy' ? 'selected' : '' }}>Easy</option>
                                   <option value="medium" {{ old('difficulty', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                   <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                              </select>
                         </div>
                    </div>

                    <button type="submit"
                         class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white py-3 rounded-xl font-bold transition shadow-lg">
                         Add Question
                    </button>
               </form>
          </div>
     </div>
</x-app-layout>