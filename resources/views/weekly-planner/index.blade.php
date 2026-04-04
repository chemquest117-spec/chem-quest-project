<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-200 leading-tight">
            {{ __('planner.weekly_planner_title') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        assignModalOpen: false,
        activePlanId: null,
        activeDay: null,
        openModal(planId, day) {
            this.activePlanId = planId;
            this.activeDay = day;
            this.assignModalOpen = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white/5 border border-white/10 overflow-hidden rounded-2xl shadow-xl backdrop-blur-xl">
                <div class="p-6 text-slate-300 border-b border-white/10">
                    <p>{{ __('planner.weekly_planner_desc') }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/5 border-b border-white/10 text-slate-400 text-sm">
                                <th class="p-4 font-semibold whitespace-nowrap">{{ __('planner.stage_week') }}</th>
                                @foreach(['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'] as $day)
                                    <th class="p-4 font-semibold capitalize text-center whitespace-nowrap">{{ trans('planner.days.'.$day) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($plans as $plan)
                                <tr class="hover:bg-white/5 transition">
                                    <td class="p-4 border-r border-white/5 whitespace-nowrap">
                                        <div class="font-bold text-white text-lg">{{ __('planner.week_label') }} {{ $plan->week_number }}</div>
                                        <div class="text-sm text-slate-400 truncate max-w-[150px]">{{ $plan->stage->getTranslatedTitle() }}</div>
                                        @if($plan->status === 'completed')
                                            <div class="mt-2 text-xs font-bold text-emerald-400 bg-emerald-400/10 inline-block px-2 py-1 rounded-full border border-emerald-400/20">
                                                <x-icon name="star" class="w-3 h-3 inline pb-0.5" /> {{ __('planner.stage_completed') }}
                                            </div>
                                        @endif
                                    </td>
                                    
                                    @foreach(['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'] as $day)
                                        @php
                                            $dayMarkers = $plan->days->where('day_name', $day);
                                            $studyMarker = $dayMarkers->where('action_type', 'study')->first();
                                            $testMarker = $dayMarkers->where('action_type', 'test')->first();
                                        @endphp
                                        <td class="p-2 border-r border-white/5 align-top min-w-[120px]">
                                            <div class="min-h-[80px] rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 transition flex flex-col gap-2 p-2 cursor-pointer group relative"
                                                 @click="openModal({{ $plan->id }}, '{{ $day }}')">
                                                
                                                {{-- Empty State Hint --}}
                                                @if(!$studyMarker && !$testMarker)
                                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                                        <x-icon name="plus" class="w-5 h-5 text-slate-400" />
                                                    </div>
                                                @endif
                                                
                                                @if($studyMarker)
                                                    <div class="bg-indigo-500/20 border border-indigo-500/30 rounded-lg p-2 text-xs text-indigo-300 flex items-center justify-between">
                                                        <span>📘 {{ __('planner.study') }}</span>
                                                        @if($studyMarker->is_completed)
                                                            <x-icon name="check-circle" class="w-4 h-4 text-emerald-400" />
                                                        @endif
                                                    </div>
                                                @endif

                                                @if($testMarker)
                                                    <div class="bg-purple-500/20 border border-purple-500/30 rounded-lg p-2 text-xs text-purple-300 flex items-center justify-between">
                                                        <span>📝 {{ __('planner.test') }}</span>
                                                        @if($testMarker->is_completed)
                                                            <x-icon name="check-circle" class="w-4 h-4 text-emerald-400" />
                                                        @endif
                                                    </div>
                                                @endif

                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Alpine Modal for Assigning Action --}}
        <div x-show="assignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0" x-cloak>
            <div x-show="assignModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="assignModalOpen = false"></div>
            
            <div x-show="assignModalOpen" x-transition.scale.origin.bottom class="relative bg-slate-800 border border-white/10 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden" @click.stop>
                <div class="p-6 border-b border-white/10 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">{{ __('planner.assign_day') }}</h3>
                    <button @click="assignModalOpen = false" class="text-slate-400 hover:text-white transition">
                        <x-icon name="x-mark" class="w-6 h-6" />
                    </button>
                </div>
                
                <div class="p-6 flex flex-col gap-4">
                    <p class="text-slate-400 text-sm mb-2">{{ __('planner.assign_day_subtitle') }}</p>
                    
                    {{-- Assign Study Form --}}
                    <form method="POST" action="{{ route('weekly-planner.assign') }}">
                        @csrf
                        <input type="hidden" name="plan_id" x-bind:value="activePlanId">
                        <input type="hidden" name="day_name" x-bind:value="activeDay">
                        <input type="hidden" name="action_type" value="study">
                        <button type="submit" class="w-full p-4 rounded-xl border border-indigo-500/30 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 font-medium transition flex items-center justify-center gap-2">
                            📘 {{ __('planner.assign_study_slot') }}
                        </button>
                    </form>

                    {{-- Assign Test Form --}}
                    <form method="POST" action="{{ route('weekly-planner.assign') }}">
                        @csrf
                        <input type="hidden" name="plan_id" x-bind:value="activePlanId">
                        <input type="hidden" name="day_name" x-bind:value="activeDay">
                        <input type="hidden" name="action_type" value="test">
                        <button type="submit" class="w-full p-4 rounded-xl border border-purple-500/30 bg-purple-500/10 hover:bg-purple-500/20 text-purple-300 font-medium transition flex items-center justify-center gap-2">
                            📝 {{ __('planner.assign_test_slot') }}
                        </button>
                    </form>
                    
                    <hr class="border-white/10 my-2">

                    {{-- Clear Action Forms --}}
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('weekly-planner.clear') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="plan_id" x-bind:value="activePlanId">
                            <input type="hidden" name="action_type" value="study">
                            <button type="submit" class="w-full p-3 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500/10 text-sm font-medium transition">
                                {{ __('planner.clear_study') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('weekly-planner.clear') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="plan_id" x-bind:value="activePlanId">
                            <input type="hidden" name="action_type" value="test">
                            <button type="submit" class="w-full p-3 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500/10 text-sm font-medium transition">
                                {{ __('planner.clear_test') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
