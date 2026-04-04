<x-app-layout>
    @section('title', __('planner.plan_details'))

    <div class="py-8" x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 100)" x-cloak>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="mb-8" x-show="shown" x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <a href="{{ route('planner.index') }}"
                    class="flex items-center gap-1 text-slate-400 hover:text-white text-sm mb-2 transition">
                    <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> {{ __('planner.title') }}
                </a>
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                            <x-icon name="map" class="w-8 h-8 text-indigo-400" />
                            {{ __('planner.plan_details') }}
                        </h1>
                        <p class="text-slate-400 mt-1">
                            {{ $studyPlan->start_date->format('M d, Y') }} – {{ $studyPlan->exam_date->format('M d, Y') }}
                            · <span class="capitalize">{{ __('planner.pace_' . $studyPlan->pace) }}</span>
                            · {{ $studyPlan->total_progress }}% complete
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            {{ $studyPlan->status === 'active' ? 'bg-emerald-500/20 text-emerald-300' :
                               ($studyPlan->status === 'completed' ? 'bg-indigo-500/20 text-indigo-300' :
                               ($studyPlan->status === 'expired' ? 'bg-red-500/20 text-red-300' : 'bg-amber-500/20 text-amber-300')) }}">
                            {{ __('planner.status_' . $studyPlan->status) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Plan Info Bar --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8" x-show="shown" x-transition>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10 text-center">
                    <div class="text-2xl font-bold text-indigo-400">{{ $studyPlan->total_progress }}%</div>
                    <div class="text-xs text-slate-400 mt-1">{{ __('planner.overall_progress') }}</div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10 text-center">
                    <div class="text-2xl font-bold text-amber-400">{{ $studyPlan->daysRemaining() }}</div>
                    <div class="text-xs text-slate-400 mt-1">{{ __('planner.days_remaining', ['days' => '']) }}</div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10 text-center">
                    <div class="text-2xl font-bold text-emerald-400">{{ $studyPlan->items->where('is_completed', true)->count() }}</div>
                    <div class="text-xs text-slate-400 mt-1">Completed</div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10 text-center">
                    <div class="text-2xl font-bold text-red-400">{{ $studyPlan->items->filter(fn($i) => $i->isOverdue())->count() }}</div>
                    <div class="text-xs text-slate-400 mt-1">{{ __('planner.overdue') }}</div>
                </div>
            </div>

            {{-- Overall Progress Bar --}}
            <div class="mb-8 bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10" x-show="shown" x-transition>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-white">{{ __('planner.overall_progress') }}</span>
                    <span class="text-sm font-bold text-indigo-400">{{ $studyPlan->total_progress }}%</span>
                </div>
                <div class="w-full bg-white/10 rounded-full h-3 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 transition-all duration-1000 ease-out"
                        x-bind:style="shown ? 'width: {{ $studyPlan->total_progress }}%' : 'width: 0%'"></div>
                </div>
            </div>

            {{-- Day-by-Day Calendar View --}}
            <div class="space-y-6" x-show="shown" x-transition:enter="transition ease-out duration-500 delay-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">

                @php $weekNum = 1; @endphp
                @foreach($itemsByWeek as $weekStart => $weekItems)
                    @php
                        $weekCompleted = $weekItems->where('is_completed', true)->count();
                        $weekTotal = $weekItems->count();
                        $weekProgress = $weekTotal > 0 ? round(($weekCompleted / $weekTotal) * 100) : 0;
                        $isCurrentWeek = \Carbon\Carbon::parse($weekStart)->isCurrentWeek();
                    @endphp

                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl border transition-all duration-300
                        {{ $isCurrentWeek ? 'border-indigo-500/30 shadow-lg shadow-indigo-500/5' : 'border-white/10' }}">

                        {{-- Week Header --}}
                        <div class="p-5 border-b border-white/10 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if($isCurrentWeek)
                                    <span class="px-2 py-0.5 bg-indigo-500/20 text-indigo-300 text-xs font-bold rounded-full">NOW</span>
                                @endif
                                <h3 class="text-lg font-bold text-white">
                                    {{ __('planner.week', ['number' => $weekNum]) }}
                                </h3>
                                <span class="text-sm text-slate-400">
                                    {{ \Carbon\Carbon::parse($weekStart)->format('M d') }} –
                                    {{ \Carbon\Carbon::parse($weekStart)->addDays(6)->format('M d') }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-24 bg-white/10 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-700
                                        {{ $weekProgress >= 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-indigo-500 to-purple-500' }}"
                                        style="width: {{ $weekProgress }}%"></div>
                                </div>
                                <span class="text-sm font-medium {{ $weekProgress >= 100 ? 'text-emerald-400' : 'text-slate-400' }}">
                                    {{ $weekCompleted }}/{{ $weekTotal }}
                                </span>
                            </div>
                        </div>

                        {{-- Day Items --}}
                        <div class="p-5">
                            @php
                                $dayGroups = $weekItems->sortBy(['scheduled_date', 'sort_order'])->groupBy(fn($i) => $i->scheduled_date->toDateString());
                            @endphp

                            <div class="space-y-4">
                                @foreach($dayGroups as $dateStr => $dayItems)
                                    @php
                                        $dateObj = \Carbon\Carbon::parse($dateStr);
                                        $isToday = $dateObj->isToday();
                                        $isPast = $dateObj->isPast() && !$isToday;
                                    @endphp

                                    <div class="flex gap-4">
                                        {{-- Date Badge --}}
                                        <div class="flex-shrink-0 w-14 text-center pt-1">
                                            <div class="text-xs uppercase tracking-wider {{ $isToday ? 'text-indigo-400 font-bold' : 'text-slate-500' }}">
                                                {{ $dateObj->format('D') }}
                                            </div>
                                            <div class="text-lg font-bold {{ $isToday ? 'text-white' : ($isPast ? 'text-slate-500' : 'text-slate-300') }}">
                                                {{ $dateObj->format('d') }}
                                            </div>
                                            @if($isToday)
                                                <div class="w-1.5 h-1.5 bg-indigo-500 rounded-full mx-auto mt-1 animate-pulse"></div>
                                            @endif
                                        </div>

                                        {{-- Items for this day --}}
                                        <div class="flex-1 space-y-2">
                                            @foreach($dayItems as $item)
                                                <div class="flex items-center gap-3 p-3 rounded-xl transition-all duration-300
                                                    {{ $item->is_completed
                                                        ? 'bg-emerald-500/10 border border-emerald-500/20'
                                                        : ($item->isOverdue()
                                                            ? 'bg-red-500/10 border border-red-500/20'
                                                            : 'bg-white/5 border border-white/10 hover:border-white/20') }}">

                                                    {{-- Toggle Button --}}
                                                    @if($studyPlan->status === 'active')
                                                        <form action="{{ route('planner.items.toggle', $item) }}" method="POST" class="flex-shrink-0">
                                                            @csrf
                                                            <button type="submit"
                                                                class="w-7 h-7 rounded-full flex items-center justify-center transition-all duration-300
                                                                {{ $item->is_completed
                                                                    ? 'bg-emerald-500 text-white'
                                                                    : 'bg-white/10 text-slate-400 hover:bg-indigo-500/20 hover:text-indigo-400' }}">
                                                                <x-icon name="check" class="w-3.5 h-3.5" />
                                                            </button>
                                                        </form>
                                                    @else
                                                        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0
                                                            {{ $item->is_completed ? 'bg-emerald-500/50 text-white' : 'bg-white/10 text-slate-500' }}">
                                                            <x-icon name="check" class="w-3.5 h-3.5" />
                                                        </div>
                                                    @endif

                                                    {{-- Content --}}
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium {{ $item->is_completed ? 'text-emerald-300 line-through' : ($item->isOverdue() ? 'text-red-300' : 'text-white') }} truncate">
                                                            {{ $item->stage->getTranslatedTitle() }}
                                                        </p>
                                                        <div class="flex items-center gap-3 mt-0.5 text-xs text-slate-500">
                                                            <span class="flex items-center gap-0.5">
                                                                <x-icon name="clock" class="w-3 h-3" /> {{ $item->estimated_minutes }}m
                                                            </span>
                                                            @if($item->marks_weight > 0)
                                                                <span class="flex items-center gap-0.5">
                                                                    <x-icon name="medal" class="w-3 h-3" /> {{ $item->marks_weight }}
                                                                </span>
                                                            @endif
                                                            @if($item->auto_rescheduled)
                                                                <span class="text-amber-400 flex items-center gap-0.5">
                                                                    <x-icon name="arrow-right" class="w-3 h-3" /> rescheduled
                                                                </span>
                                                            @endif
                                                            @if($item->is_completed && $item->completed_at)
                                                                <span class="text-emerald-400">✓ {{ $item->completed_at->diffForHumans() }}</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Quiz Link --}}
                                                    @if(!$item->is_completed && $studyPlan->status === 'active')
                                                        <a href="{{ route('stages.show', $item->stage) }}"
                                                            class="flex-shrink-0 px-3 py-1 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 rounded-lg text-xs font-medium transition">
                                                            {{ __('planner.start_quiz') }}
                                                        </a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @php $weekNum++; @endphp
                @endforeach
            </div>

            {{-- Bottom Actions --}}
            @if($studyPlan->status === 'active')
                <div class="mt-8 flex flex-wrap gap-3" x-show="shown" x-transition>
                    <form action="{{ route('planner.reschedule', $studyPlan) }}" method="POST">
                        @csrf
                        <button class="px-5 py-2 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                            <x-icon name="arrow-right" class="w-4 h-4" /> {{ __('planner.reschedule_missed') }}
                        </button>
                    </form>
                    <form action="{{ route('planner.destroy', $studyPlan) }}" method="POST"
                        onsubmit="return confirm('{{ __('planner.delete_confirm') }}')">
                        @csrf @method('DELETE')
                        <button class="px-5 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                            <x-icon name="trash" class="w-4 h-4" /> {{ __('planner.delete_plan') }}
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
