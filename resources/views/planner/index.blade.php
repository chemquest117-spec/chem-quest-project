<x-app-layout>
    @section('title', __('planner.title'))

    <div class="py-8" x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 100)" x-cloak>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="mb-8" x-show="shown" x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <x-icon name="academic-cap" class="w-8 h-8 text-indigo-400" />
                    {{ __('planner.title') }}
                </h1>
                <p class="text-slate-400 mt-1">{{ __('planner.setup_subtitle') }}</p>
            </div>

            @if($activePlan)
            {{-- ── Active Plan Dashboard ── --}}

            {{-- Top Stats Row --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8" x-show="shown"
                x-transition:enter="transition ease-out duration-500 delay-100"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                {{-- Progress Ring --}}
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10 hover:border-indigo-500/30 transition-all duration-300 text-center">
                    <div class="relative w-20 h-20 mx-auto mb-3">
                        <svg class="w-20 h-20 -rotate-90" viewBox="0 0 36 36">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="3" />
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none" stroke="url(#progressGrad)" stroke-width="3" stroke-linecap="round"
                                stroke-dasharray="{{ $activePlan->total_progress }}, 100" />
                            <defs>
                                <linearGradient id="progressGrad">
                                    <stop offset="0%" stop-color="#818cf8" />
                                    <stop offset="100%" stop-color="#34d399" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-lg font-bold text-white">{{ $activePlan->total_progress }}%</span>
                    </div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider">{{ __('planner.overall_progress') }}</p>
                </div>

                {{-- Days Remaining --}}
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10 hover:border-amber-500/30 transition-all duration-300 text-center">
                    <x-icon name="clock" class="w-6 h-6 text-amber-400 mx-auto mb-2" />
                    <div class="text-3xl font-bold text-amber-400">{{ $activePlan->daysRemaining() }}</div>
                    <p class="text-xs text-slate-400 mt-1">{{ __('planner.days_remaining', ['days' => $activePlan->daysRemaining()]) }}</p>
                </div>

                {{-- Plan Status --}}
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10 hover:border-emerald-500/30 transition-all duration-300 text-center">
                    <x-icon name="check-circle" class="w-6 h-6 text-emerald-400 mx-auto mb-2" />
                    <div class="text-3xl font-bold text-emerald-400">
                        {{ $activePlan->items->where('is_completed', true)->count() }}/{{ $activePlan->items->count() }}
                    </div>
                    <p class="text-xs text-slate-400 mt-1">{{ __('planner.plan_status') }}</p>
                </div>

                {{-- Pace --}}
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10 hover:border-purple-500/30 transition-all duration-300 text-center">
                    <x-icon name="trending-up" class="w-6 h-6 text-purple-400 mx-auto mb-2" />
                    <div class="text-lg font-bold text-purple-400 capitalize">{{ __('planner.pace_' . $activePlan->pace) }}</div>
                    <p class="text-xs text-slate-400 mt-1">{{ __('planner.pace') }}</p>
                </div>
            </div>

            {{-- Missed Tasks Warning --}}
            @if($missedItems->count() > 0)
            <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl p-5 flex items-center justify-between"
                x-show="shown" x-transition>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center">
                        <x-icon name="exclamation" class="w-5 h-5 text-red-400" />
                    </div>
                    <div>
                        <p class="text-red-300 font-medium">{{ __('planner.missed_tasks') }}</p>
                        <p class="text-red-400/70 text-sm">{{ __('planner.missed_warning', ['count' => $missedItems->count()]) }}</p>
                    </div>
                </div>
                <form action="{{ route('planner.reschedule', $activePlan) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                        <x-icon name="arrow-right" class="w-4 h-4" />
                        {{ __('planner.reschedule_missed') }}
                    </button>
                </form>
            </div>
            @endif

            {{-- Today's Tasks --}}
            <div class="mb-8" x-show="shown" x-transition:enter="transition ease-out duration-500 delay-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <x-icon name="star" class="w-5 h-5 text-amber-400" />
                    {{ __('planner.today_tasks') }}
                </h2>

                @if($todayItems->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($todayItems as $item)
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border transition-all duration-300
                                    {{ $item->is_completed ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-white/10 hover:border-indigo-500/30' }}">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold {{ $item->is_completed ? 'text-emerald-300 line-through' : 'text-white' }} truncate">
                                    {{ $item->stage->getTranslatedTitle() }}
                                </h3>
                                <div class="flex items-center gap-3 mt-1 text-xs text-slate-400">
                                    <span class="flex items-center gap-1">
                                        <x-icon name="clock" class="w-3 h-3" /> {{ __('planner.est_time', ['minutes' => $item->estimated_minutes]) }}
                                    </span>
                                    @if($item->marks_weight > 0)
                                    <span class="flex items-center gap-1">
                                        <x-icon name="medal" class="w-3 h-3" /> {{ __('planner.marks', ['marks' => $item->marks_weight]) }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <form action="{{ route('planner.items.toggle', $item) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300
                                                {{ $item->is_completed
                                                    ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                                                    : 'bg-white/10 text-slate-400 hover:bg-indigo-500/20 hover:text-indigo-400' }}">
                                    <x-icon name="check" class="w-4 h-4" />
                                </button>
                            </form>
                        </div>

                        @if($item->is_completed && $item->completed_at)
                        <p class="text-xs text-emerald-400/70 flex items-center gap-1">
                            <x-icon name="check-circle" class="w-3 h-3" />
                            {{ __('planner.completed_at', ['time' => $item->completed_at->diffForHumans()]) }}
                        </p>
                        @endif

                        @if($item->auto_rescheduled)
                        <p class="text-xs text-amber-400/70 flex items-center gap-1 mt-1">
                            <x-icon name="arrow-right" class="w-3 h-3" />
                            {{ __('planner.auto_rescheduled') }}
                        </p>
                        @endif

                        @if(!$item->is_completed)
                        <a href="{{ route('stages.show', $item->stage) }}"
                            class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 rounded-lg text-xs font-medium transition">
                            <x-icon name="target" class="w-3 h-3" /> {{ __('planner.start_quiz') }}
                        </a>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10 text-center">
                    <x-icon name="sparkles" class="w-8 h-8 text-emerald-400 mx-auto mb-2" />
                    <p class="text-slate-400">{{ __('planner.nothing_today') }}</p>
                </div>
                @endif
            </div>

            {{-- Weekly View --}}
            <div x-show="shown" x-transition:enter="transition ease-out duration-500 delay-300"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <x-icon name="map" class="w-5 h-5 text-indigo-400" />
                        {{ __('planner.weekly_view') }}
                    </h2>
                    <a href="{{ route('planner.show', $activePlan) }}"
                        class="text-sm text-indigo-400 hover:text-indigo-300 flex items-center gap-1 transition">
                        {{ __('planner.view_plan') }}
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                </div>

                <div class="space-y-4">
                    @php $weekNum = 1; @endphp
                    @foreach($itemsByWeek as $weekStart => $items)
                    @php
                    $weekCompleted = $items->where('is_completed', true)->count();
                    $weekTotal = $items->count();
                    $weekProgress = $weekTotal > 0 ? round(($weekCompleted / $weekTotal) * 100) : 0;
                    @endphp
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10 hover:border-white/20 transition-all duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-white flex items-center gap-2">
                                {{ __('planner.week', ['number' => $weekNum]) }}
                                <span class="text-xs text-slate-500">
                                    {{ \Carbon\Carbon::parse($weekStart)->format('M d') }} –
                                    {{ \Carbon\Carbon::parse($weekStart)->addDays(6)->format('M d') }}
                                </span>
                            </h3>
                            <span class="text-sm font-medium {{ $weekProgress >= 100 ? 'text-emerald-400' : 'text-slate-400' }}">
                                {{ $weekCompleted }}/{{ $weekTotal }}
                                @if($weekProgress >= 100)
                                <x-icon name="check-circle" class="w-4 h-4 inline" />
                                @endif
                            </span>
                        </div>

                        {{-- Progress bar --}}
                        <div class="w-full bg-white/10 rounded-full h-1.5 mb-3 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700 ease-out
                                        {{ $weekProgress >= 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-indigo-500 to-purple-500' }}"
                                style="width: {{ (int) $weekProgress }}%;"></div>
                        </div>

                        {{-- Items --}}
                        <div class="flex flex-wrap gap-2">
                            @foreach($items->sortBy('scheduled_date') as $item)
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all
                                            {{ $item->is_completed
                                                ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/20'
                                                : ($item->isOverdue()
                                                    ? 'bg-red-500/15 text-red-300 border border-red-500/20'
                                                    : 'bg-white/5 text-slate-300 border border-white/10') }}">
                                @if($item->is_completed)
                                <x-icon name="check" class="w-3 h-3" />
                                @elseif($item->isOverdue())
                                <x-icon name="exclamation" class="w-3 h-3" />
                                @else
                                <x-icon name="clock" class="w-3 h-3" />
                                @endif
                                {{ $item->stage->getTranslatedTitle() }}
                                <span class="text-slate-500">{{ $item->scheduled_date->format('D') }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @php $weekNum++; @endphp
                    @endforeach
                </div>
            </div>

            {{-- Plan Actions --}}
            <div class="mt-8 flex flex-wrap gap-3" x-show="shown" x-transition>
                <a href="{{ route('planner.show', $activePlan) }}"
                    class="px-5 py-2 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                    <x-icon name="map" class="w-4 h-4" /> {{ __('planner.view_plan') }}
                </a>
                <form action="{{ route('planner.destroy', $activePlan) }}" method="POST"
                    onsubmit="return confirm('{{ addslashes(__('planner.delete_confirm')) }}')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="px-5 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                        <x-icon name="trash" class="w-4 h-4" /> {{ __('planner.delete_plan') }}
                    </button>
                </form>
            </div>

            @else
            {{-- ── No Active Plan — Setup CTA ── --}}
            <div class="flex flex-col items-center justify-center py-16" x-show="shown"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                <div class="relative mb-8">
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center border border-indigo-500/20 animate-pulse">
                        <x-icon name="academic-cap" class="w-16 h-16 text-indigo-400" />
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-cyan-500 flex items-center justify-center shadow-lg">
                        <x-icon name="plus" class="w-5 h-5 text-white" />
                    </div>
                </div>

                <h2 class="text-2xl font-bold text-white mb-2">{{ __('planner.no_active_plan') }}</h2>
                <p class="text-slate-400 text-center max-w-md mb-8">{{ __('planner.no_plan_desc') }}</p>

                <a href="{{ route('planner.create') }}"
                    class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-2xl font-semibold text-lg transition-all duration-300 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:shadow-indigo-500/30 hover:scale-105 flex items-center gap-2">
                    <x-icon name="sparkles" class="w-5 h-5" />
                    {{ __('planner.get_started') }}
                </a>
            </div>

            {{-- Past Plans --}}
            @if(isset($pastPlans) && $pastPlans->count() > 0)
            <div class="mt-12" x-show="shown" x-transition>
                <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <x-icon name="document-text" class="w-5 h-5 text-slate-400" />
                    {{ __('planner.past_plans') }}
                </h2>
                <div class="space-y-3">
                    @foreach($pastPlans as $plan)
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10 flex items-center justify-between">
                        <div>
                            <p class="text-white font-medium">
                                {{ $plan->start_date->format('M d') }} – {{ $plan->exam_date->format('M d, Y') }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5 capitalize flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full {{ $plan->status === 'completed' ? 'bg-emerald-400' : ($plan->status === 'expired' ? 'bg-red-400' : 'bg-amber-400') }}"></span>
                                {{ __('planner.status_' . $plan->status) }} · {{ $plan->total_progress }}%
                            </p>
                        </div>
                        <a href="{{ route('planner.show', $plan) }}"
                            class="text-sm text-indigo-400 hover:text-indigo-300 transition">
                            {{ __('planner.view_plan') }}
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif

        </div>
    </div>
</x-app-layout>