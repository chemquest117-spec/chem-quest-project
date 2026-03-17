<x-app-layout>
     @section('title', 'Manage Stages')

     <div class="py-8">
          <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
               <div class="flex items-center justify-between mb-8">
                    <div>
                         <a href="{{ route('admin.dashboard') }}"
                              class="flex items-center gap-1 text-slate-400 hover:text-white text-sm">
                              <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> Admin Dashboard</a>
                         <h1 class="text-3xl font-bold text-white mt-1 flex items-center gap-2"><x-icon
                                   name="academic-cap" class="w-7 h-7 text-purple-400" /> Manage Stages</h1>
                    </div>
                    <a href="{{ route('admin.stages.create') }}"
                         class="flex items-center gap-1.5 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-5 py-2 rounded-xl font-medium transition shadow-lg">
                         <x-icon name="plus" class="w-4 h-4" /> New Stage
                    </a>
               </div>

               <div class="space-y-4">
                    @foreach($stages as $stage)
                         <div
                              class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-white/20 transition">
                              <div class="flex items-center justify-between">
                                   <div class="flex items-center space-x-4">
                                        <div
                                             class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-400 font-bold text-xl">
                                             {{ $stage->order }}
                                        </div>
                                        <div>
                                             <h3 class="text-lg font-bold text-white">{{ $stage->title }}</h3>
                                             <div class="flex items-center space-x-3 text-sm text-slate-400">
                                                  <span class="flex items-center gap-1"><x-icon name="clock"
                                                            class="w-3 h-3" /> {{ $stage->time_limit_minutes }} min</span>
                                                  <span class="flex items-center gap-1"><x-icon name="document-text"
                                                            class="w-3 h-3" /> {{ $stage->questions_count }} questions</span>
                                                  <span class="flex items-center gap-1"><x-icon name="target"
                                                            class="w-3 h-3" /> {{ $stage->passing_percentage }}%</span>
                                                  <span class="flex items-center gap-1"><x-icon name="medal"
                                                            class="w-3 h-3" /> {{ $stage->points_reward }} pts</span>
                                             </div>
                                        </div>
                                   </div>
                                   <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.stages.questions.index', $stage) }}"
                                             class="flex items-center gap-1 bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-400 px-4 py-2 rounded-xl text-sm transition">
                                             <x-icon name="document-text" class="w-3.5 h-3.5" /> Questions
                                        </a>
                                        <a href="{{ route('admin.stages.edit', $stage) }}"
                                             class="flex items-center gap-1 bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 px-4 py-2 rounded-xl text-sm transition">
                                             <x-icon name="pencil" class="w-3.5 h-3.5" /> Edit
                                        </a>
                                        <form action="{{ route('admin.stages.destroy', $stage) }}" method="POST"
                                             onsubmit="return confirm('Delete this stage and all its questions?')">
                                             @csrf @method('DELETE')
                                             <button
                                                  class="flex items-center gap-1 bg-red-500/20 hover:bg-red-500/30 text-red-400 px-4 py-2 rounded-xl text-sm transition">
                                                  <x-icon name="trash" class="w-3.5 h-3.5" /> Delete
                                             </button>
                                        </form>
                                   </div>
                              </div>
                         </div>
                    @endforeach
               </div>
          </div>
     </div>
</x-app-layout>