<x-app-layout>
     @section('title', 'Questions - ' . $stage->title)

     <div class="py-8">
          <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
               <div class="flex items-center justify-between mb-8">
                    <div>
                         <a href="{{ route('admin.stages.index') }}"
                              class="flex items-center gap-1 text-slate-400 hover:text-white text-sm">
                              <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> Back to Stages</a>
                         <h1 class="text-3xl font-bold text-white mt-1 flex items-center gap-2"><x-icon
                                   name="document-text" class="w-7 h-7 text-blue-400" /> {{ $stage->title }} — Questions
                         </h1>
                         <p class="text-slate-400 text-sm">{{ $questions->count() }} questions</p>
                    </div>
                    <div class="flex items-center gap-2">
                         <form action="{{ route('admin.stages.questions.generate', $stage) }}" method="POST"
                              x-data="{ loading: false }" @submit="loading = true">
                              @csrf
                              <button type="submit" :disabled="loading"
                                   class="flex items-center gap-1.5 bg-gradient-to-r from-purple-500 to-violet-500 hover:from-purple-600 hover:to-violet-600 disabled:opacity-50 text-white px-5 py-2 rounded-xl font-medium transition shadow-lg">
                                   <template x-if="!loading">
                                        <span class="flex items-center gap-1.5"><x-icon name="lightning-bolt"
                                                  class="w-4 h-4" /> AI Generate</span>
                                   </template>
                                   <template x-if="loading">
                                        <span class="flex items-center gap-1.5">
                                             <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                                  fill="none" viewBox="0 0 24 24">
                                                  <circle class="opacity-25" cx="12" cy="12" r="10"
                                                       stroke="currentColor" stroke-width="4"></circle>
                                                  <path class="opacity-75" fill="currentColor"
                                                       d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                             </svg>
                                             Generating...
                                        </span>
                                   </template>
                              </button>
                         </form>
                         <a href="{{ route('admin.stages.questions.create', $stage) }}"
                              class="flex items-center gap-1.5 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white px-5 py-2 rounded-xl font-medium transition shadow-lg">
                              <x-icon name="plus" class="w-4 h-4" /> Add Question
                         </a>
                    </div>
               </div>

               <div class="space-y-4">
                    @foreach($questions as $index => $question)
                                   <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                                        <div class="flex items-start justify-between">
                                             <div class="flex-1">
                                                  <div class="flex items-center space-x-2 mb-2">
                                                       <span class="text-sm font-bold text-slate-400">#{{ $index + 1 }}</span>
                                                       <span class="flex items-center gap-1 px-2 py-0.5 rounded text-xs
                                                                          {{ $question->difficulty === 'easy' ? 'bg-green-500/20 text-green-400' :
                         ($question->difficulty === 'medium' ? 'bg-amber-500/20 text-amber-400' :
                              'bg-red-500/20 text-red-400') }}">
                                                            @if($question->difficulty === 'easy')
                                                                 <x-icon name="shield-check" class="w-3 h-3" />
                                                            @else
                                                                 <x-icon name="lightning-bolt" class="w-3 h-3" />
                                                            @endif
                                                            {{ ucfirst($question->difficulty) }}
                                                       </span>
                                                  </div>
                                                  <p class="text-white font-medium mb-3">{{ $question->question_text }}</p>
                                                  <div class="grid grid-cols-2 gap-2 text-sm">
                                                       @foreach(['a', 'b', 'c', 'd'] as $opt)
                                                            <div
                                                                 class="p-2 rounded-lg flex items-center gap-2 {{ $opt === $question->correct_answer ? 'bg-emerald-500/20 text-emerald-300 ring-1 ring-emerald-500/30' : 'bg-white/5 text-slate-400' }}">
                                                                 <span class="font-bold">{{ strtoupper($opt) }}.</span>
                                                                 {{ $question->{'option_' . $opt} }}
                                                                 @if($opt === $question->correct_answer)
                                                                      <x-icon name="check" class="w-3.5 h-3.5 text-emerald-400 ml-auto" />
                                                                 @endif
                                                            </div>
                                                       @endforeach
                                                  </div>
                                             </div>
                                             <div class="flex items-center space-x-2 ml-4">
                                                  <a href="{{ route('admin.stages.questions.edit', [$stage, $question]) }}"
                                                       class="flex items-center gap-1 bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 px-3 py-1.5 rounded-lg text-sm transition">
                                                       <x-icon name="pencil" class="w-3.5 h-3.5" /> Edit</a>
                                                  <form action="{{ route('admin.stages.questions.destroy', [$stage, $question]) }}"
                                                       method="POST" onsubmit="return confirm('Delete this question?')">
                                                       @csrf @method('DELETE')
                                                       <button
                                                            class="flex items-center gap-1 bg-red-500/20 hover:bg-red-500/30 text-red-400 px-3 py-1.5 rounded-lg text-sm transition">
                                                            <x-icon name="trash" class="w-3.5 h-3.5" /> Delete</button>
                                                  </form>
                                             </div>
                                        </div>
                                   </div>
                    @endforeach

                    @if($questions->isEmpty())
                         <div class="text-center py-12 text-slate-500">
                              <x-icon name="document-text" class="w-12 h-12 mx-auto mb-2 text-slate-600" />
                              <p>No questions yet. Add your first question!</p>
                         </div>
                    @endif
               </div>
          </div>
     </div>
</x-app-layout>