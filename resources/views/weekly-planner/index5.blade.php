<x-app-layout>
    @section('title', __('planner.weekly_planner_title'))

    <div class="py-8" x-data="{
        assignModalOpen: false,
        detailModalOpen: false,
        activePlanId: null,
        activeDay: null,
        activeItem: null,
        plans: @js($plans->map(fn($p) => ['id' => $p->id, 'week_number' => $p->week_number, 'stage_title' => $p->stage->getTranslatedTitle(), 'stage_id' => $p->stage_id])),
        openAssign(day) {
            this.activeDay = day;
            this.assignModalOpen = true;
        },
        openDetail(item) {
            this.activeItem = item;
            this.detailModalOpen = true;
        }
    }" x-cloak>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                        <x-icon name="map" class="w-8 h-8 text-indigo-400" />
                        {{ __('planner.weekly_planner_title') }}
                    </h1>
                    <p class="text-slate-400 mt-1">{{ __('planner.weekly_planner_desc') }}</p>
                </div>

                {{-- Quick Stats --}}
                <div class="flex items-center gap-3">
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl px-4 py-2 border border-white/10 text-center">
                        <div class="text-lg font-bold text-indigo-400"></div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">{{ __('planner.plan_status') }}</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl px-4 py-2 border border-white/10 text-center">
                        <div class="text-lg font-bold text-emerald-400">{{ $plans->where('status', 'completed')->count() }}/{{ $maxWeek }}</div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">{{ __('planner.done') }}</div>
                    </div>
                </div>
            </div>

            {{-- Week Navigation Bar --}}
            <div class="mb-6 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    {{-- Prev/Next Arrows --}}
                    @if($currentWeek > 1)
                    <a href="{{ route('weekly-planner.index', ['week' => $currentWeek - 1]) }}"
                        class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                        <x-icon name="arrow-right" class="w-4 h-4 rotate-180" />
                    </a>
                    @else
                    <div class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-slate-600">
                        <x-icon name="arrow-right" class="w-4 h-4 rotate-180" />
                    </div>
                    @endif

                    @if($currentWeek < $maxWeek)
                        <a href="{{ route('weekly-planner.index', ['week' => $currentWeek + 1]) }}"
                        class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                        <x-icon name="arrow-right" class="w-4 h-4" />
                        </a>
                        @else
                        <div class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-slate-600">
                            <x-icon name="arrow-right" class="w-4 h-4" />
                        </div>
                        @endif

                        <div class="ml-1">
                            <span class="text-lg font-bold text-white">
                                {{ __('planner.week', ['number' => $currentWeek]) }}
                            </span>
                            <span class="text-slate-400 text-sm ml-2">
                                {{ $weekStart->format('d M') }} – {{ $weekEnd->format('d M Y') }}
                            </span>
                        </div>
                </div>

                {{-- Week tabs --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                    @foreach($plans as $plan)
                    <a href="{{ route('weekly-planner.index', ['week' => $plan->week_number]) }}"
                        class="flex-shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium transition-all
                                {{ $plan->week_number === $currentWeek
                                    ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/25'
                                    : ($plan->status === 'completed'
                                        ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/20 hover:bg-emerald-500/25'
                                        : 'bg-white/5 text-slate-400 border border-white/10 hover:bg-white/10') }}">
                        W{{ $plan->week_number }}
                        @if($plan->status === 'completed')
                        <x-icon name="check" class="w-3 h-3 inline ml-0.5" />
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Active Plan Info Banner --}}
            @if($activePlan)
            <div class="mb-6 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 rounded-2xl border border-indigo-500/20 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/20 flex items-center justify-center flex-shrink-0">
                        <x-icon name="academic-cap" class="w-6 h-6 text-indigo-400" />
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-lg">LO{{ $activePlan->week_number }}: {{ $activePlan->stage->getTranslatedTitle() }}</h3>
                        <p class="text-sm text-slate-400">
                            @if($activePlan->stage->estimated_study_minutes)
                            <x-icon name="clock" class="w-3 h-3 inline" /> {{ __('planner.est_time', ['minutes' => $activePlan->stage->estimated_study_minutes]) }}
                            ·
                            @endif
                            @if($activePlan->stage->marks_weight)
                            <x-icon name="medal" class="w-3 h-3 inline" /> {{ __('planner.marks', ['marks' => $activePlan->stage->marks_weight]) }}
                            ·
                            @endif
                            <span class="capitalize
                                {{ $activePlan->status === 'completed' ? 'text-emerald-400' : 'text-amber-400' }}">
                                {{ __('planner.status_' . ($activePlan->status === 'completed' ? 'completed' : 'active')) }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('stages.show', $activePlan->stage) }}"
                        class="px-4 py-2 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                        <x-icon name="target" class="w-4 h-4" /> {{ __('planner.start_quiz') }}
                    </a>
                    <form method="POST" action="{{ route('weekly-planner.reset') }}">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $activePlan->id }}">
                        <button type="submit"
                            class="px-4 py-2 bg-white/5 hover:bg-white/10 text-slate-400 rounded-xl text-sm font-medium transition flex items-center gap-1.5"
                            onclick="return confirm('Reset this week\'s plan?')">
                            <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> Reset
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Calendar Grid --}}
            <div class="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">

                {{-- Day Header Row --}}
                <div class="grid grid-cols-7 border-b border-white/10">
                    @foreach($calendarDays as $day)
                    @php
                    $dateObj = $dayDates[$day];
                    $isToday = $dateObj->isToday();
                    @endphp
                    <div class="p-3 sm:p-4 text-center border-r border-white/5 last:border-r-0
                            {{ $isToday ? 'bg-indigo-500/10' : '' }}">
                        <div class="text-xs uppercase tracking-wider font-bold
                                {{ $isToday ? 'text-indigo-400' : 'text-slate-400' }}">
                            {{ __('planner.days.' . $day) }}
                        </div>
                        <div class="text-lg sm:text-xl font-bold mt-0.5
                                {{ $isToday ? 'text-white' : 'text-slate-500' }}">
                            {{ $dateObj->format('d') }}
                        </div>
                        @if($isToday)
                        <div class="w-1.5 h-1.5 bg-indigo-500 rounded-full mx-auto mt-1 animate-pulse"></div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Calendar Body --}}
                <div class="grid grid-cols-7 min-h-[420px]">
                    @foreach($calendarDays as $day)
                    @php
                    $dateObj = $dayDates[$day];
                    $isToday = $dateObj->isToday();
                    $dayItems = $calendarGrid[$day] ?? [];
                    // Sort: study first, then test
                    usort($dayItems, function($a, $b) {
                    return $a['action_type'] === 'study' ? -1 : 1;
                    });
                    @endphp
                    <div class="border-r border-white/5 last:border-r-0 p-2 sm:p-3 flex flex-col gap-2
                            {{ $isToday ? 'bg-indigo-500/5' : '' }}
                            cursor-pointer group relative"
                        @click="openAssign('{{ $day }}')">

                        {{-- Items --}}
                        @forelse($dayItems as $item)
                        @php
                        $isStudy = $item['action_type'] === 'study';
                        $isCompleted = $item['is_completed'];
                        $stage = $item['stage'];
                        $weekNum = $item['week_number'];
                        $est = $item['estimated_minutes'];

                        // Color coding per week
                        $colors = [
                        1 => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/30', 'text' => 'text-emerald-300', 'time' => 'text-emerald-400/70'],
                        2 => ['bg' => 'bg-blue-500/15', 'border' => 'border-blue-500/30', 'text' => 'text-blue-300', 'time' => 'text-blue-400/70'],
                        3 => ['bg' => 'bg-purple-500/15', 'border' => 'border-purple-500/30', 'text' => 'text-purple-300', 'time' => 'text-purple-400/70'],
                        4 => ['bg' => 'bg-amber-500/15', 'border' => 'border-amber-500/30', 'text' => 'text-amber-300', 'time' => 'text-amber-400/70'],
                        5 => ['bg' => 'bg-rose-500/15', 'border' => 'border-rose-500/30', 'text' => 'text-rose-300', 'time' => 'text-rose-400/70'],
                        ];
                        $c = $colors[$weekNum] ?? $colors[1];

                        if ($isCompleted) {
                        $c = ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/20', 'text' => 'text-emerald-400', 'time' => 'text-emerald-400/50'];
                        }
                        @endphp

                        <div class="rounded-xl p-2.5 sm:p-3 {{ $c['bg'] }} border {{ $c['border'] }} transition-all duration-200 hover:scale-[1.02] hover:shadow-lg cursor-pointer"
                            @click.stop="openDetail({
                                        plan_id: {{ $item['plan_id'] }},
                                        week: {{ $weekNum }},
                                        type: '{{ $item['action_type'] }}',
                                        title: '{{ addslashes($stage->getTranslatedTitle()) }}',
                                        completed: {{ $isCompleted ? 'true' : 'false' }},
                                        est: {{ $est }},
                                        stage_id: {{ $stage->id }}
                                     })">

                            {{-- Time estimate --}}
                            <div class="text-[10px] font-medium {{ $c['time'] }} mb-1">
                                @if($isStudy)
                                {{ $est }}m · {{ __('planner.study') }}
                                @else
                                30m · {{ __('planner.test') }}
                                @endif
                            </div>

                            {{-- Title --}}
                            <div class="text-xs sm:text-sm font-semibold {{ $c['text'] }} leading-tight {{ $isCompleted ? 'line-through opacity-70' : '' }}">
                                {{ $stage->getTranslatedTitle() }}
                            </div>

                            {{-- Status indicator --}}
                            @if($isCompleted)
                            <div class="flex items-center gap-1 mt-1.5">
                                <x-icon name="check-circle" class="w-3.5 h-3.5 text-emerald-400" />
                                <span class="text-[10px] text-emerald-400/70">{{ __('planner.done') }}</span>
                            </div>
                            @else
                            <div class="flex items-center gap-1 mt-1.5">
                                <span class="w-2 h-2 rounded-full {{ $isStudy ? 'bg-indigo-400' : 'bg-purple-400' }} animate-pulse"></span>
                                <span class="text-[10px] {{ $c['time'] }}">W{{ $weekNum }}</span>
                            </div>
                            @endif
                        </div>
                        @empty
                        {{-- Empty state --}}
                        <div class="flex-1 flex items-center justify-center min-h-[60px] rounded-xl border-2 border-dashed border-white/5 group-hover:border-indigo-500/30 transition-all duration-200">
                            <div class="text-center opacity-0 group-hover:opacity-100 transition-all duration-200">
                                <x-icon name="plus" class="w-5 h-5 text-indigo-400 mx-auto" />
                                <span class="text-[10px] text-indigo-400/70 block mt-0.5">Add</span>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Legend --}}
            <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-slate-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-indigo-500/30"></span> {{ __('planner.study_session') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-purple-500/30"></span> {{ __('planner.stage_quiz') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <x-icon name="check-circle" class="w-3 h-3 text-emerald-400" /> {{ __('planner.stage_completed') }}
                </span>
            </div>

            {{-- Weekly Overview List --}}
            <div class="mt-8 space-y-3">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <x-icon name="document-text" class="w-5 h-5 text-indigo-400" />
                    {{ __('planner.weekly_planner_title') }} — All Weeks
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($plans as $plan)
                    @php
                    $studyDay = $plan->days->where('action_type', 'study')->first();
                    $testDay = $plan->days->where('action_type', 'test')->first();
                    $isPlanComplete = $plan->status === 'completed';

                    $cardColors = [
                    1 => 'from-emerald-500/10 to-emerald-500/5 border-emerald-500/20',
                    2 => 'from-blue-500/10 to-blue-500/5 border-blue-500/20',
                    3 => 'from-purple-500/10 to-purple-500/5 border-purple-500/20',
                    4 => 'from-amber-500/10 to-amber-500/5 border-amber-500/20',
                    5 => 'from-rose-500/10 to-rose-500/5 border-rose-500/20',
                    ];
                    $cardColor = $cardColors[$plan->week_number] ?? $cardColors[1];
                    @endphp
                    <a href="{{ route('weekly-planner.index', ['week' => $plan->week_number]) }}"
                        class="bg-gradient-to-br {{ $cardColor }} rounded-2xl border p-4 hover:scale-[1.01] transition-all duration-200 group
                                {{ $plan->week_number === $currentWeek ? 'ring-2 ring-indigo-500/50 shadow-lg shadow-indigo-500/10' : '' }}">

                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-bold text-white">
                                {{ __('planner.week_label') }} {{ $plan->week_number }}
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

                        <h4 class="text-sm font-medium text-white/90 mb-2 truncate">LO{{ $plan->week_number }}: {{ $plan->stage->getTranslatedTitle() }}</h4>

                        <div class="flex items-center gap-3 text-xs text-slate-400">
                            <span class="flex items-center gap-1">
                                📘 {{ $studyDay ? __('planner.days.' . $studyDay->day_name) : __('planner.not_set') }}
                                @if($studyDay?->is_completed) <x-icon name="check-circle" class="w-3 h-3 text-emerald-400" /> @endif
                            </span>
                            <span class="flex items-center gap-1">
                                📝 {{ $testDay ? __('planner.days.' . $testDay->day_name) : __('planner.not_set') }}
                                @if($testDay?->is_completed) <x-icon name="check-circle" class="w-3 h-3 text-emerald-400" /> @endif
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ═══════════ Assign Day Modal ═══════════ --}}
        <div x-show="assignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
            <div x-show="assignModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="assignModalOpen = false"></div>

            <div x-show="assignModalOpen" x-transition.scale.origin.bottom
                class="relative bg-slate-800 border border-white/10 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" @click.stop>

                {{-- Modal Header --}}
                <div class="p-5 border-b border-white/10 flex justify-between items-center bg-gradient-to-r from-indigo-500/10 to-purple-500/10">
                    <div>
                        <h3 class="text-lg font-bold text-white">{{ __('planner.assign_day') }}</h3>
                        <p class="text-sm text-slate-400 mt-0.5">{{ __('planner.assign_day_subtitle') }}</p>
                    </div>
                    <button @click="assignModalOpen = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-400 hover:text-white transition">
                        <x-icon name="x-mark" class="w-4 h-4" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">

                    {{-- Pick which week/stage to assign --}}
                    <p class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-2">Select stage to assign:</p>

                    <template x-for="plan in plans" :key="plan.id">
                        <div class="border border-white/10 rounded-xl p-3 mb-3 hover:border-indigo-500/30 transition">
                            <p class="text-sm font-semibold text-white mb-2" x-text="'W' + plan.week_number + ': ' + plan.stage_title"></p>
                            <div class="flex gap-2">
                                {{-- Assign Study --}}
                                <form method="POST" action="{{ route('weekly-planner.assign') }}" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="plan_id" x-bind:value="plan.id">
                                    <input type="hidden" name="day_name" x-bind:value="activeDay">
                                    <input type="hidden" name="action_type" value="study">
                                    <button type="submit"
                                        class="w-full px-3 py-2 rounded-lg border border-indigo-500/30 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 text-xs font-medium transition flex items-center justify-center gap-1.5">
                                        📘 {{ __('planner.study') }}
                                    </button>
                                </form>
                                {{-- Assign Test --}}
                                <form method="POST" action="{{ route('weekly-planner.assign') }}" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="plan_id" x-bind:value="plan.id">
                                    <input type="hidden" name="day_name" x-bind:value="activeDay">
                                    <input type="hidden" name="action_type" value="test">
                                    <button type="submit"
                                        class="w-full px-3 py-2 rounded-lg border border-purple-500/30 bg-purple-500/10 hover:bg-purple-500/20 text-purple-300 text-xs font-medium transition flex items-center justify-center gap-1.5">
                                        📝 {{ __('planner.test') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </div>

        {{-- ═══════════ Detail / Toggle Modal ═══════════ --}}
        <div x-show="detailModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
            <div x-show="detailModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="detailModalOpen = false"></div>

            <div x-show="detailModalOpen" x-transition.scale.origin.bottom
                class="relative bg-slate-800 border border-white/10 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden" @click.stop>

                <template x-if="activeItem">
                    <div>
                        {{-- Header --}}
                        <div class="p-5 border-b border-white/10 bg-gradient-to-r from-indigo-500/10 to-purple-500/10">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wider font-bold mb-1"
                                        x-text="activeItem.type === 'study' ? '📘 {{ __('planner.study_session') }}' : '📝 {{ __('planner.stage_quiz') }}'"></p>
                                    <h3 class="text-lg font-bold text-white" x-text="activeItem.title"></h3>
                                </div>
                                <button @click="detailModalOpen = false" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-400 hover:text-white transition">
                                    <x-icon name="x-mark" class="w-4 h-4" />
                                </button>
                            </div>
                            <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                                <span class="flex items-center gap-1">
                                    <x-icon name="clock" class="w-3 h-3" /> <span x-text="activeItem.est + 'm'"></span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <x-icon name="map" class="w-3 h-3" /> <span x-text="'{{ __('planner.week_label') }} ' + activeItem.week"></span>
                                </span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="p-5 space-y-3">
                            {{-- Toggle Completion --}}
                            <form method="POST" action="{{ route('weekly-planner.toggle') }}">
                                @csrf
                                <input type="hidden" name="plan_id" x-bind:value="activeItem.plan_id">
                                <input type="hidden" name="action_type" x-bind:value="activeItem.type">
                                <button type="submit"
                                    class="w-full px-4 py-3 rounded-xl font-medium text-sm transition flex items-center justify-center gap-2"
                                    :class="activeItem.completed
                                        ? 'bg-amber-500/15 border border-amber-500/30 text-amber-300 hover:bg-amber-500/25'
                                        : 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/25'">
                                    <x-icon name="check-circle" class="w-5 h-5" />
                                    <span x-text="activeItem.completed ? '{{ __('planner.item_unmarked') }}' : '{{ __('planner.item_completed') }}'"></span>
                                </button>
                            </form>

                            {{-- Go to Stage --}}
                            <a :href="'/stages/' + activeItem.stage_id"
                                class="block w-full px-4 py-3 bg-indigo-500/15 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/25 rounded-xl text-sm font-medium transition text-center flex items-center justify-center gap-2">
                                <x-icon name="target" class="w-5 h-5" />
                                {{ __('planner.start_quiz') }}
                            </a>

                            {{-- Clear --}}
                            <form method="POST" action="{{ route('weekly-planner.clear') }}">
                                @csrf
                                <input type="hidden" name="plan_id" x-bind:value="activeItem.plan_id">
                                <input type="hidden" name="action_type" x-bind:value="activeItem.type">
                                <button type="submit"
                                    class="w-full px-4 py-3 bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 rounded-xl text-sm font-medium transition flex items-center justify-center gap-2">
                                    <x-icon name="trash" class="w-4 h-4" />
                                    Remove from calendar
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>