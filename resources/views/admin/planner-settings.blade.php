<x-app-layout>
    @section('title', __('planner.planner_settings'))

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-1 text-slate-400 hover:text-white text-sm">
                        <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> Admin Dashboard
                    </a>
                    <h1 class="text-3xl font-bold text-white mt-1 flex items-center gap-2">
                        <x-icon name="academic-cap" class="w-7 h-7 text-indigo-400" />
                        {{ __('planner.planner_settings') }}
                    </h1>
                    <p class="text-slate-400 mt-1">Configure study weights and durations for the planner algorithm.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.planner-settings.update') }}">
                @csrf

                <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
                    {{-- Table Header --}}
                    <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-white/10 bg-white/5 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <div class="col-span-1">#</div>
                        <div class="col-span-3">{{ __('planner.stage_name') }}</div>
                        <div class="col-span-2 text-center">{{ __('planner.marks_weight') }}</div>
                        <div class="col-span-2 text-center">{{ __('planner.estimated_minutes') }}</div>
                        <div class="col-span-2 text-center">{{ __('planner.importance_score') }}</div>
                        <div class="col-span-2 text-center">{{ __('planner.recommended_week') }}</div>
                    </div>

                    {{-- Stage Rows --}}
                    @foreach($stages as $index => $stage)
                        <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-white/5 items-center hover:bg-white/5 transition">
                            <input type="hidden" name="stages[{{ $index }}][id]" value="{{ $stage->id }}">

                            <div class="col-span-1">
                                <span class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold text-sm">
                                    {{ $stage->order }}
                                </span>
                            </div>

                            <div class="col-span-3">
                                <p class="text-white font-medium text-sm truncate">{{ $stage->getTranslatedTitle() }}</p>
                                <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $stage->getTranslatedDescription() }}</p>
                            </div>

                            <div class="col-span-2">
                                <input type="number" name="stages[{{ $index }}][marks_weight]"
                                    value="{{ old("stages.{$index}.marks_weight", $stage->marks_weight) }}"
                                    min="0" max="100"
                                    class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white text-sm text-center focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 transition">
                                @error("stages.{$index}.marks_weight") <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="col-span-2">
                                <input type="number" name="stages[{{ $index }}][estimated_study_minutes]"
                                    value="{{ old("stages.{$index}.estimated_study_minutes", $stage->estimated_study_minutes) }}"
                                    min="10" max="600"
                                    class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white text-sm text-center focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 transition">
                                @error("stages.{$index}.estimated_study_minutes") <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="col-span-2">
                                <input type="number" name="stages[{{ $index }}][importance_score]"
                                    value="{{ old("stages.{$index}.importance_score", $stage->importance_score) }}"
                                    min="1" max="10"
                                    class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white text-sm text-center focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 transition">
                                @error("stages.{$index}.importance_score") <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="col-span-2">
                                <input type="number" name="stages[{{ $index }}][recommended_week]"
                                    value="{{ old("stages.{$index}.recommended_week", $stage->recommended_week) }}"
                                    min="1" max="52" placeholder="—"
                                    class="w-full bg-white/5 border border-white/20 rounded-lg px-3 py-2 text-white text-sm text-center focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 transition">
                                @error("stages.{$index}.recommended_week") <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Save Button --}}
                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl font-semibold transition shadow-lg shadow-indigo-500/25 flex items-center gap-2">
                        <x-icon name="check" class="w-5 h-5" />
                        {{ __('planner.save_settings') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
