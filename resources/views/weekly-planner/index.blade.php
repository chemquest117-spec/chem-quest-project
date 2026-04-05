<x-app-layout>
    @section('title', __('planner.weekly_planner_title'))

    {{-- Inline styles for the time-grid calendar --}}
    <style>
        .time-grid {
            display: grid;
            grid-template-columns: 60px repeat(7, 1fr);
        }

        .time-label {
            font-size: 11px;
            color: #6b7280;
            text-align: right;
            padding-right: 8px;
            padding-top: 0;
            line-height: 1;
        }

        .day-column {
            position: relative;
            border-left: 1px solid rgba(255, 255, 255, 0.05);
            min-height: 120px;
        }

        .hour-line {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            height: 120px;
        }

        .event-card {
            position: absolute;
            left: 4px;
            right: 4px;
            z-index: 10;
            border-radius: 8px;
            padding: 6px 8px;
            border-left: 3px solid;
            cursor: pointer;
            transition: all 0.2s;
            overflow: hidden;
        }

        .event-card:hover {
            transform: scale(1.02);
            z-index: 20;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .time-grid {
                grid-template-columns: 45px 1fr;
            }

            .mobile-day-col {
                display: none;
            }

            .mobile-day-col.active {
                display: block;
            }
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <div class="py-6 sm:py-8" x-data="weeklyPlanner()"
        x-init="allEvents = JSON.parse($el.dataset.events)"
        data-events="{{ json_encode($plans->flatMap(fn($p) => $p->days->map(fn($d) => ['id' => $d->id, 'title' => $d->display_title, 'type' => $d->action_type, 'timeRange' => $d->time_range, 'notes' => $d->notes, 'color' => $d->color, 'completed' => (bool) $d->is_completed, 'weekLabel' => 'Week '.$p->week_number, 'stageId' => $p->stage_id]))->values()) }}"
        x-cloak>
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ═══════ Header ═══════ --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3">
                        <x-icon name="map" class="w-7 h-7 sm:w-8 sm:h-8 text-indigo-400" />
                        {{ __('planner.weekly_planner_title') }}
                    </h1>
                    <p class="text-slate-400 mt-1 text-sm">{{ __('planner.weekly_planner_desc') }}</p>
                </div>
                {{-- Add Task Button --}}
                <button @click="openAddModal()"
                    class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl font-semibold text-sm transition-all shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:scale-105 flex items-center gap-2 self-start sm:self-auto">
                    <x-icon name="plus" class="w-4 h-4" />
                    {{ __('planner.assign_day') }}
                </button>
            </div>

            {{-- ═══════ Week Navigation ═══════ --}}
            <div
                class="mb-5 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-3 sm:p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                {{-- Left: arrows + date range --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    @if($currentWeek > 1)
                    <a href="{{ route('weekly-planner.index', ['week' => $currentWeek - 1]) }}"
                        class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                        <x-icon name="arrow-right" class="w-4 h-4 rotate-180" />
                    </a>
                    @else
                    <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-slate-600">
                        <x-icon name="arrow-right" class="w-4 h-4 rotate-180" />
                    </div>
                    @endif

                    @if($currentWeek < $maxWeek)
                        <a href="{{ route('weekly-planner.index', ['week' => $currentWeek + 1]) }}"
                        class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                        <x-icon name="arrow-right" class="w-4 h-4" />
                        </a>
                        @else
                        <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-slate-600">
                            <x-icon name="arrow-right" class="w-4 h-4" />
                        </div>
                        @endif

                        <div class="ml-1">
                            <span
                                class="text-base sm:text-lg font-bold text-white">{{ __('planner.week', ['number' => $currentWeek]) }}</span>
                            <span class="text-slate-400 text-xs sm:text-sm ml-2">{{ $weekStart->format('d M') }} –
                                {{ $weekEnd->format('d M Y') }}</span>
                        </div>
                </div>

                {{-- Right: week tabs --}}
                <div class="flex items-center gap-1 overflow-x-auto no-scrollbar pb-0.5">
                    @foreach($plans as $plan)
                    <a href="{{ route('weekly-planner.index', ['week' => $plan->week_number]) }}" class="flex-shrink-0 px-2.5 py-1 rounded-lg text-xs font-medium transition-all
                                                {{ $plan->week_number === $currentWeek
                        ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/25'
                        : ($plan->status === 'completed'
                            ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/20'
                            : 'bg-white/5 text-slate-400 border border-white/10 hover:bg-white/10') }}">
                        W{{ $plan->week_number }}
                        @if($plan->status === 'completed')
                        <x-icon name="check" class="w-3 h-3 inline ml-0.5" />
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- ═══════ Active Learning Outcome Info ═══════ --}}
            @if($activePlan)
            <div
                class="mb-5 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 rounded-2xl border border-indigo-500/20 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-indigo-500/20 flex items-center justify-center flex-shrink-0">
                        <x-icon name="academic-cap" class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-400" />
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-white text-sm sm:text-lg truncate">
                            {{ $activePlan->stage->getTranslatedTitle() }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-400 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                            @if($activePlan->stage->estimated_study_minutes)
                            <span><x-icon name="clock" class="w-3 h-3 inline" />
                                {{ $activePlan->stage->estimated_study_minutes }}m</span>
                            @endif
                            <span
                                class="capitalize {{ $activePlan->status === 'completed' ? 'text-emerald-400' : 'text-amber-400' }}">
                                {{ __('planner.status_' . ($activePlan->status === 'completed' ? 'completed' : 'active')) }}
                            </span>
                            <span>{{ $completedEvents }}/{{ $totalEvents }} tasks</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('stages.show', $activePlan->stage) }}"
                        class="px-3 py-1.5 sm:px-4 sm:py-2 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 rounded-xl text-xs sm:text-sm font-medium transition flex items-center gap-1.5">
                        <x-icon name="target" class="w-3.5 h-3.5" /> {{ __('planner.start_quiz') }}
                    </a>
                </div>
            </div>
            @endif

            {{-- ═══════ Mobile Day Selector ═══════ --}}
            <div class="md:hidden mb-4 flex gap-1.5 overflow-x-auto no-scrollbar pb-1">
                @foreach($calendarDays as $day)
                @php $dateObj = $dayDates[$day];
                $isToday = $dateObj->isToday(); @endphp
                <button @click="mobileDay = '{{ $day }}'"
                    class="flex-shrink-0 px-3 py-2 rounded-xl text-center transition-all"
                    :class="mobileDay === '{{ $day }}'
                                ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/25'
                                : '{{ $isToday ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-white/5 text-slate-400 border border-white/10' }}'">
                    <div class="text-[10px] uppercase font-bold">{{ __('planner.days.' . $day) }}</div>
                    <div class="text-sm font-bold mt-0.5">{{ $dateObj->format('d') }}</div>
                </button>
                @endforeach
            </div>

            {{-- ═══════ Calendar Grid ═══════ --}}
            <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">

                {{-- Desktop Day Headers --}}
                <div class="hidden md:grid grid-cols-[60px_repeat(7,1fr)] border-b border-white/10">
                    <div class="p-3"></div>
                    @foreach($calendarDays as $day)
                    @php $dateObj = $dayDates[$day];
                    $isToday = $dateObj->isToday(); @endphp
                    <div class="p-3 text-center border-l border-white/5 {{ $isToday ? 'bg-indigo-500/10' : '' }}">
                        <div
                            class="text-xs uppercase tracking-wider font-bold {{ $isToday ? 'text-indigo-400' : 'text-slate-500' }}">
                            {{ __('planner.days.' . $day) }}
                        </div>
                        <div class="text-lg font-bold mt-0.5 {{ $isToday ? 'text-white' : 'text-slate-400' }}">
                            {{ $dateObj->format('d') }}
                        </div>
                        @if($isToday)
                        <div class="w-1.5 h-1.5 bg-indigo-500 rounded-full mx-auto mt-1 animate-pulse"></div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Time Grid --}}
                <div class="overflow-y-auto max-h-[70vh] sm:max-h-[65vh] no-scrollbar">

                    {{-- Desktop layout --}}
                    <div class="hidden md:block">
                        <div class="time-grid">
                            @foreach($timeSlots as $timeIndex => $time)
                            {{-- Time label --}}
                            <div class="time-label" style="height: 120px; padding-top: 2px;">
                                {{ \Carbon\Carbon::parse($time)->format('g A') }}
                            </div>

                            {{-- Day columns for this hour --}}
                            @foreach($calendarDays as $day)
                            @php $isToday = $dayDates[$day]->isToday(); @endphp
                            <div class="hour-line {{ $isToday ? 'bg-indigo-500/5' : '' }} relative"
                                @click="openAddModal('{{ $day }}', '{{ $time }}')">

                                @if($timeIndex === 0)
                                {{-- Render events only in the first hour row, positioned absolutely --}}
                                @foreach($eventsByDay[$day] as $event)
                                @php
                                $startMinutes = $event->start_time
                                ? (\Carbon\Carbon::parse($event->start_time)->hour * 60 + \Carbon\Carbon::parse($event->start_time)->minute)
                                : 540;
                                $endMinutes = $event->end_time
                                ? (\Carbon\Carbon::parse($event->end_time)->hour * 60 + \Carbon\Carbon::parse($event->end_time)->minute)
                                : ($startMinutes + 60);
                                $gridStartMinutes = 7 * 60; // 7 AM
                                $topPx = max(0, ($startMinutes - $gridStartMinutes) * 2);
                                $heightPx = max(60, ($endMinutes - $startMinutes) * 2);
                                $cc = $event->color_classes;
                                @endphp
                                <div class="event-card {{ $cc['bg'] }} {{ $cc['border'] }} {{ $event->is_completed ? 'opacity-60' : '' }}"
                                    style="top: {{ $topPx }}px; height: {{ $heightPx }}px;"
                                    @click.stop="openDetailModal({{ $event->id }})">

                                    <div class="text-[10px] font-medium {{ $cc['text'] }}">
                                        {{ $event->time_range }}
                                    </div>
                                    <div
                                        class="text-xs sm:text-sm font-semibold text-white leading-tight mt-0.5 {{ $event->is_completed ? 'line-through' : '' }}">
                                        {{ $event->display_title }}
                                    </div>
                                    @if($event->notes)
                                    <div class="text-[10px] text-slate-400 mt-0.5 line-clamp-3">{{ $event->notes }}</div>
                                    @endif
                                    <div class="flex items-center gap-1 mt-1">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $cc['dot'] }}"></span>
                                        <span class="text-[9px] text-slate-500">
                                            {{ $event->action_type === 'study' ? __('planner.study') : __('planner.test') }}
                                            · W{{ $event->plan->week_number ?? '' }}
                                        </span>
                                        @if($event->is_completed)
                                        <x-icon name="check-circle" class="w-3 h-3 text-emerald-400 ml-auto" />
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                                @endif
                            </div>
                            @endforeach
                            @endforeach
                        </div>
                    </div>

                    {{-- Mobile layout — single day view --}}
                    <div class="md:hidden">
                        @foreach($calendarDays as $day)
                        <div x-show="mobileDay === '{{ $day }}'" x-transition>
                            <div class="grid grid-cols-[45px_1fr]">
                                @foreach($timeSlots as $timeIndex => $time)
                                <div class="time-label" style="height: 120px; padding-top: 2px;">
                                    {{ \Carbon\Carbon::parse($time)->format('gA') }}
                                </div>
                                <div class="hour-line relative border-l border-white/5"
                                    @click="openAddModal('{{ $day }}', '{{ $time }}')">

                                    @if($timeIndex === 0)
                                    @foreach($eventsByDay[$day] as $event)
                                    @php
                                    $startMinutes = $event->start_time
                                    ? (\Carbon\Carbon::parse($event->start_time)->hour * 60 + \Carbon\Carbon::parse($event->start_time)->minute)
                                    : 540;
                                    $endMinutes = $event->end_time
                                    ? (\Carbon\Carbon::parse($event->end_time)->hour * 60 + \Carbon\Carbon::parse($event->end_time)->minute)
                                    : ($startMinutes + 60);
                                    $gridStartMinutes = 7 * 60;
                                    $topPx = max(0, ($startMinutes - $gridStartMinutes) * 2);
                                    $heightPx = max(60, ($endMinutes - $startMinutes) * 2);
                                    $cc = $event->color_classes;
                                    @endphp
                                    <div class="event-card {{ $cc['bg'] }} {{ $cc['border'] }} {{ $event->is_completed ? 'opacity-60' : '' }}"
                                        style="top: {{ $topPx }}px; height: {{ $heightPx }}px;"
                                        @click.stop="openDetailModal({{ $event->id }})">

                                        <div class="text-[10px] font-medium {{ $cc['text'] }}">{{ $event->time_range }}
                                        </div>
                                        <div
                                            class="text-sm font-semibold text-white leading-tight mt-0.5 {{ $event->is_completed ? 'line-through' : '' }}">
                                            {{ $event->display_title }}
                                        </div>
                                        @if($event->notes)
                                        <div class="text-[10px] text-slate-400 mt-0.5 line-clamp-3">{{ $event->notes }}
                                        </div>
                                        @endif
                                        <div class="flex items-center gap-1 mt-1">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $cc['dot'] }}"></span>
                                            <span class="text-[9px] text-slate-500">
                                                {{ $event->action_type === 'study' ? __('planner.study') : __('planner.test') }}
                                            </span>
                                            @if($event->is_completed)
                                            <x-icon name="check-circle" class="w-3 h-3 text-emerald-400 ml-auto" />
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>

            {{-- ═══════ Legend --}}
            <div class="mt-3 flex flex-wrap items-center gap-3 sm:gap-4 text-[10px] sm:text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-indigo-500/30"></span>
                    {{ __('planner.study_session') }}</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-purple-500/30"></span>
                    {{ __('planner.stage_quiz') }}</span>
                <span class="flex items-center gap-1.5"><x-icon name="check-circle" class="w-3 h-3 text-emerald-400" />
                    {{ __('planner.done') }}</span>
                <span class="text-slate-600">|</span>
                <span>Click any time slot to add a task</span>
            </div>

            {{-- ═══════ Weekly Overview Cards ═══════ --}}
            <div class="mt-8 space-y-3">
                <h2 class="text-lg sm:text-xl font-bold text-white flex items-center gap-2">
                    <x-icon name="document-text" class="w-5 h-5 text-indigo-400" />
                    All Weeks Overview
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
                    @foreach($plans as $plan)
                    @php
                    $studyDay = $plan->days->where('action_type', 'study')->first();
                    $testDay = $plan->days->where('action_type', 'test')->first();

                    $isPlanComplete = $plan->status === 'completed';
                    $planEvents = $plan->days->count();
                    $planDone = $plan->days->where('is_completed', true)->count();
                    $wColors = ['emerald', 'blue', 'purple', 'amber', 'rose', 'cyan', 'orange', 'indigo'];
                    $wc = $wColors[($plan->week_number - 1) % count($wColors)];
                    @endphp
                    <a href="{{ route('weekly-planner.index', ['week' => $plan->week_number]) }}"
                        class="bg-gradient-to-br from-{{ $wc }}-500/10 to-{{ $wc }}-500/5 border border-{{ $wc }}-500/30 rounded-xl p-3 sm:p-4 mb-1 hover:scale-[1.01] transition-all group
                                        {{ $plan->week_number === $currentWeek ? 'ring-2 ring-indigo-500/50 shadow-lg shadow-indigo-500/10' : '' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs sm:text-sm font-bold text-white">Week {{ $plan->week_number }}</span>

                            <div class="flex items-center gap-2">
                                <span
                                    class="px-1.5 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold
                                            {{ $isPlanComplete ? 'bg-emerald-500/20 text-emerald-300' : 'bg-white/10 text-slate-400' }}">
                                    {{ $planDone }}/{{ $planEvents ?: '0' }}
                                </span>

                                @if($isPlanComplete)
                                <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 text-[10px] font-bold rounded-full border border-emerald-500/20">
                                    <x-icon name="check" class="w-3 h-3 inline" /> {{ __('planner.done') }}
                                </span>
                                @else
                                <span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 text-[10px] font-bold rounded-full border border-amber-500/20">
                                    {{ __('planner.status_active') }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <p class="text-xs sm:text-sm text-white/80 mb-2 truncate">{{ $plan->stage->getTranslatedTitle() }}
                        </p>

                        <div class="flex items-center gap-3 text-xs text-slate-400">
                            <span class="flex items-center gap-1">
                                📘 {{ $studyDay ? __('planner.days.' . $studyDay->day_name) : __('planner.not_set') }}
                                @if($studyDay?->is_completed) <x-icon name="check-circle"
                                    class="w-3 h-3 text-emerald-400" /> @endif
                            </span>
                            <span class="flex items-center gap-1">
                                📝 {{ $testDay ? __('planner.days.' . $testDay->day_name) : __('planner.not_set') }}
                                @if($testDay?->is_completed) <x-icon name="check-circle"
                                    class="w-3 h-3 text-emerald-400" /> @endif
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- ═══════ ADD TASK MODAL ═══════ --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="addModalOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
            x-cloak>
            <div x-show="addModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm"
                @click="addModalOpen = false"></div>

            <div x-show="addModalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                class="relative bg-slate-800 border border-white/10 rounded-t-3xl sm:rounded-2xl shadow-2xl w-full sm:max-w-lg max-h-[90vh] overflow-y-auto"
                @click.stop>

                <form method="POST" action="{{ route('weekly-planner.store') }}">
                    @csrf

                    {{-- Header --}}
                    <div
                        class="sticky top-0 bg-slate-800/95 backdrop-blur-sm p-5 border-b border-white/10 flex justify-between items-center z-10">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <x-icon name="plus" class="w-5 h-5 text-indigo-400" />
                            {{ __('planner.assign_day') }}
                        </h3>
                        <button type="button" @click="addModalOpen = false"
                            class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-400 hover:text-white transition">
                            <x-icon name="x-mark" class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="p-5 space-y-5">

                        {{-- Stage / Plan Selector --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Stage
                                (Learning Outcome)</label>
                            <select name="plan_id"
                                class="w-full bg-white/5 border border-white/15 rounded-xl px-4 py-3 text-white text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 transition appearance-none cursor-pointer">
                                @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ $activePlan && $plan->id === $activePlan->id ? 'selected' : '' }}>
                                    W{{ $plan->week_number }}: {{ $plan->stage->getTranslatedTitle() }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Type --}}
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Type</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="relative">
                                    <input type="radio" name="action_type" value="study" x-model="newEvent.type"
                                        class="peer sr-only" checked>
                                    <div
                                        class="peer-checked:border-indigo-500 peer-checked:bg-indigo-500/15 border-2 border-white/10 rounded-xl p-3 text-center cursor-pointer transition-all hover:border-white/20">
                                        <span class="text-lg">📘</span>
                                        <p class="text-sm font-semibold text-white mt-1">{{ __('planner.study') }}</p>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="action_type" value="test" x-model="newEvent.type"
                                        class="peer sr-only">
                                    <div
                                        class="peer-checked:border-purple-500 peer-checked:bg-purple-500/15 border-2 border-white/10 rounded-xl p-3 text-center cursor-pointer transition-all hover:border-white/20">
                                        <span class="text-lg">📝</span>
                                        <p class="text-sm font-semibold text-white mt-1">{{ __('planner.test') }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Title --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Title
                                (optional)</label>
                            <input type="text" name="title" placeholder="e.g. Review Redox Reactions"
                                class="w-full bg-white/5 border border-white/15 rounded-xl px-4 py-3 text-white text-sm placeholder:text-slate-500 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 transition">
                        </div>

                        {{-- Day --}}
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Day</label>
                            <div class="grid grid-cols-7 gap-1.5">
                                @foreach($calendarDays as $day)
                                <label class="relative">
                                    <input type="radio" name="day_name" value="{{ $day }}" x-model="newEvent.day"
                                        class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                                    <div
                                        class="peer-checked:bg-indigo-500 peer-checked:text-white peer-checked:border-indigo-500 border border-white/10 rounded-lg py-2 text-center cursor-pointer text-xs font-bold text-slate-400 transition-all hover:border-white/30">
                                        {{ strtoupper(substr(__('planner.days.' . $day), 0, 2)) }}
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Time Range --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">From</label>
                                <input type="time" name="start_time" x-model="newEvent.start"
                                    class="w-full bg-white/5 border border-white/15 rounded-xl px-4 py-3 text-white text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 transition">
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">To</label>
                                <input type="time" name="end_time" x-model="newEvent.end"
                                    class="w-full bg-white/5 border border-white/15 rounded-xl px-4 py-3 text-white text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 transition">
                            </div>
                        </div>

                        {{-- Duration preview --}}
                        <div x-show="newEvent.start && newEvent.end"
                            class="bg-indigo-500/10 rounded-xl p-3 border border-indigo-500/20 text-sm text-indigo-300 flex items-center gap-2">
                            <x-icon name="clock" class="w-4 h-4" />
                            <span>Duration: <strong x-text="calcDuration(newEvent.start, newEvent.end)"></strong></span>
                        </div>

                        {{-- Color --}}
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Color</label>
                            <div class="flex gap-2 flex-wrap">
                                @foreach(['indigo', 'emerald', 'blue', 'purple', 'amber', 'rose', 'cyan', 'orange'] as $color)
                                <label class="relative">
                                    <input type="radio" name="color" value="{{ $color }}" x-model="newEvent.color"
                                        class="peer sr-only" {{ $color === 'indigo' ? 'checked' : '' }}>
                                    <div
                                        class="w-7 h-7 rounded-full bg-{{ $color }}-500 cursor-pointer transition-all ring-0 peer-checked:ring-2 peer-checked:ring-white peer-checked:ring-offset-2 peer-checked:ring-offset-slate-800 hover:scale-110">
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Notes
                                (optional)</label>
                            <textarea name="notes" rows="3"
                                placeholder="Add study notes, chapter references, or reminders..."
                                class="w-full bg-white/5 border border-white/15 rounded-xl px-4 py-3 text-white text-sm placeholder:text-slate-500 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 transition resize-none"></textarea>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div
                        class="sticky bottom-0 bg-slate-800/95 backdrop-blur-sm p-5 border-t border-white/10 flex gap-3">
                        <button type="button" @click="addModalOpen = false"
                            class="flex-1 px-4 py-3 bg-white/5 hover:bg-white/10 text-slate-300 rounded-xl text-sm font-medium transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl text-sm font-semibold transition shadow-lg shadow-indigo-500/25">
                            Add Task
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════ --}}
        {{-- ═══════ EVENT DETAIL MODAL ═══════ --}}
        {{-- ═══════════════════════════════════════════════ --}}
        <div x-show="detailModalOpen"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" x-cloak>
            <div x-show="detailModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm"
                @click="detailModalOpen = false"></div>

            <div x-show="detailModalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                class="relative bg-slate-800 border border-white/10 rounded-t-3xl sm:rounded-2xl shadow-2xl w-full sm:max-w-md"
                @click.stop>

                <template x-if="selectedEvent">
                    <div>
                        {{-- Color Bar --}}
                        <div class="h-1.5 rounded-t-3xl sm:rounded-t-2xl" :class="'bg-' + selectedEvent.color + '-500'">
                        </div>

                        {{-- Header --}}
                        <div class="p-5 border-b border-white/10">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] uppercase tracking-wider font-bold mb-1"
                                        :class="selectedEvent.type === 'study' ? 'text-indigo-400' : 'text-purple-400'"
                                        x-text="selectedEvent.type === 'study' ? '📘 {{ __('planner.study_session') }}' : '📝 {{ __('planner.stage_quiz') }}'">
                                    </p>
                                    <h3 class="text-lg font-bold text-white leading-tight" x-text="selectedEvent.title">
                                    </h3>
                                </div>
                                <button @click="detailModalOpen = false"
                                    class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-400 hover:text-white transition ml-3 flex-shrink-0">
                                    <x-icon name="x-mark" class="w-4 h-4" />
                                </button>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mt-3 text-xs text-slate-400">
                                <span class="flex items-center gap-1" x-show="selectedEvent.timeRange">
                                    <x-icon name="clock" class="w-3.5 h-3.5" /> <span
                                        x-text="selectedEvent.timeRange"></span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <x-icon name="map" class="w-3.5 h-3.5" /> <span
                                        x-text="selectedEvent.weekLabel"></span>
                                </span>
                                <span class="flex items-center gap-1" x-show="selectedEvent.completed">
                                    <x-icon name="check-circle" class="w-3.5 h-3.5 text-emerald-400" /> <span
                                        class="text-emerald-400">Completed</span>
                                </span>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="px-5 py-4" x-show="selectedEvent.notes">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Notes</p>
                            <p class="text-sm text-slate-300 leading-relaxed whitespace-pre-line"
                                x-text="selectedEvent.notes"></p>
                        </div>

                        {{-- Actions --}}
                        <div class="p-5 space-y-2.5 border-t border-white/10">
                            {{-- Toggle Completion --}}
                            <form method="POST" action="{{ route('weekly-planner.toggle') }}">
                                @csrf
                                <input type="hidden" name="event_id" :value="selectedEvent.id">
                                <button type="submit"
                                    class="w-full px-4 py-3 rounded-xl font-medium text-sm transition flex items-center justify-center gap-2"
                                    :class="selectedEvent.completed
                                        ? 'bg-amber-500/15 border border-amber-500/30 text-amber-300 hover:bg-amber-500/25'
                                        : 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/25'">
                                    <x-icon name="check-circle" class="w-5 h-5" />
                                    <span
                                        x-text="selectedEvent.completed ? 'Mark as Incomplete' : 'Mark as Complete'"></span>
                                </button>
                            </form>

                            {{-- Go to Stage --}}
                            <a :href="'/stages/' + selectedEvent.stageId"
                                class="block w-full px-4 py-3 bg-indigo-500/15 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/25 rounded-xl text-sm font-medium transition text-center">
                                <x-icon name="target" class="w-4 h-4 inline mr-1" /> {{ __('planner.start_quiz') }}
                            </a>

                            {{-- Delete --}}
                            <form method="POST" :action="'/weekly-planner/events/' + selectedEvent.id"
                                onsubmit="return confirm('Remove this task?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full px-4 py-3 bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 rounded-xl text-sm font-medium transition flex items-center justify-center gap-2">
                                    <x-icon name="trash" class="w-4 h-4" /> Remove Task
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    {{-- Alpine Planner Logic --}}
    <script>
        function weeklyPlanner() {
            return {
                addModalOpen: false,
                detailModalOpen: false,
                mobileDay: '{{ collect(["sun" => 0, "mon" => 1, "tue" => 2, "wed" => 3, "thu" => 4, "fri" => 5, "sat" => 6])->search(now()->dayOfWeek) ?: "sat" }}',
                selectedEvent: null,
                newEvent: {
                    type: 'study',
                    day: '{{ $calendarDays[0] }}',
                    start: '09:00',
                    end: '10:00',
                    color: 'indigo',
                },

                // Populated via x-init from data-events attribute
                allEvents: [],

                openAddModal(day, time) {
                    if (day) this.newEvent.day = day;
                    if (time) {
                        this.newEvent.start = time;
                        // Set end time 1 hour later
                        const [h, m] = time.split(':').map(Number);
                        const endH = Math.min(h + 1, 23);
                        this.newEvent.end = String(endH).padStart(2, '0') + ':' + String(m).padStart(2, '0');
                    }
                    this.addModalOpen = true;
                },

                openDetailModal(eventId) {
                    this.selectedEvent = this.allEvents.find(e => e.id === eventId);
                    if (this.selectedEvent) this.detailModalOpen = true;
                },

                calcDuration(start, end) {
                    if (!start || !end) return '';
                    const [sh, sm] = start.split(':').map(Number);
                    const [eh, em] = end.split(':').map(Number);
                    let mins = (eh * 60 + em) - (sh * 60 + sm);
                    if (mins <= 0) return 'Invalid';
                    const hours = Math.floor(mins / 60);
                    const remMins = mins % 60;
                    if (hours > 0 && remMins > 0) return hours + 'h ' + remMins + 'm';
                    if (hours > 0) return hours + 'h';
                    return remMins + 'm';
                }
            }
        }
    </script>
</x-app-layout>