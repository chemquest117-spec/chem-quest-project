<x-app-layout>
    @section('title', 'Dashboard')

    <div class="py-8" x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 100)" x-cloak>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Welcome Header --}}
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 group" x-show="shown" 
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div>
                    <h1 class="text-3xl font-bold text-white flex items-center gap-2">
                        {{ __('dashboard.welcome', ['name' => $user->name]) }}
                        <x-icon name="hand-wave" class="w-8 h-8 text-amber-400 group-hover:rotate-12 transition-transform duration-300" />
                    </h1>
                    <p class="text-slate-400 mt-1">{{ __('dashboard.continue_learning') }}</p>
                </div>

                {{-- Quick Navigation List --}}
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('stages.index') }}" 
                        class="flex items-center gap-2 px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-sm font-medium text-slate-300 hover:text-white transition-all shadow-sm">
                        <x-icon name="target" class="w-4 h-4 text-emerald-400" />
                        {{ __('stages.title') }}
                    </a>
                    <a href="{{ route('planner.index') }}" 
                        class="flex items-center gap-2 px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-sm font-medium text-slate-300 hover:text-white transition-all shadow-sm">
                        <x-icon name="academic-cap" class="w-4 h-4 text-indigo-400" />
                        {{ __('navigation.study_planner') }}
                    </a>
                    <a href="{{ route('weekly-planner.index') }}" 
                        class="flex items-center gap-2 px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-sm font-medium text-slate-300 hover:text-white transition-all shadow-sm">
                        <x-icon name="calendar" class="w-4 h-4 text-purple-400" />
                        {{ __('navigation.weekly_planner') }}
                    </a>
                </div>
            </div>

            {{-- Stats Cards Row 1 --}}
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
                {{-- Streak --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    style="transition-delay: 50ms"
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-white/10 hover:border-orange-500/30 hover:shadow-lg hover:shadow-orange-500/5 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-2 sm:mb-3">
                        <span class="text-slate-400 text-[10px] sm:text-sm uppercase tracking-wider font-semibold sm:font-normal">{{ __('dashboard.streak') }}</span>
                        <x-icon name="fire"
                            class="w-4 h-4 sm:w-5 sm:h-5 text-orange-400 group-hover:scale-125 transition-transform duration-300 {{ $user->streak > 0 ? 'animate-pulse' : '' }}" />
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold text-orange-400">{{ $user->streak }}</div>
                    <p class="text-slate-500 text-[10px] sm:text-sm mt-1 truncate">{{ __('dashboard.keep_burning') }}</p>
                </div>

                {{-- Progress --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    style="transition-delay: 100ms"
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-white/10 hover:border-cyan-500/30 hover:shadow-lg hover:shadow-cyan-500/5 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-2 sm:mb-3">
                        <span class="text-slate-400 text-[10px] sm:text-sm uppercase tracking-wider font-semibold sm:font-normal">{{ __('dashboard.your_progress') }}</span>
                        <x-icon name="trending-up"
                            class="w-4 h-4 sm:w-5 sm:h-5 text-cyan-400 group-hover:scale-125 transition-transform duration-300" />
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold text-white">{{ $progressPercentage }}%</div>
                    <div class="mt-2 sm:mt-3 w-full bg-white/10 rounded-full h-1.5 sm:h-2.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-cyan-500 to-emerald-500 h-full rounded-full transition-all duration-[2000ms] ease-out"
                            x-bind:style="shown ? 'width: {{ $progressPercentage }}%' : 'width: 0%'"></div>
                    </div>
                </div>

                {{-- Points --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    style="transition-delay: 200ms"
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-white/10 hover:border-emerald-500/30 hover:shadow-lg hover:shadow-emerald-500/5 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-2 sm:mb-3">
                        <span class="text-slate-400 text-[10px] sm:text-sm uppercase tracking-wider font-semibold sm:font-normal">{{ __('dashboard.points') }}</span>
                        <x-icon name="medal"
                            class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-400 group-hover:scale-125 transition-transform duration-300" />
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold text-emerald-400">{{ number_format($user->total_points) }}</div>
                    <p class="text-slate-500 text-[10px] sm:text-sm mt-1 truncate">{{ __('dashboard.keep_earning') }}</p>
                </div>

                {{-- Stars --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    style="transition-delay: 300ms"
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-white/10 hover:border-amber-500/30 hover:shadow-lg hover:shadow-amber-500/5 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-2 sm:mb-3">
                        <span class="text-slate-400 text-[10px] sm:text-sm uppercase tracking-wider font-semibold sm:font-normal">{{ __('dashboard.stars') }}</span>
                        <x-icon name="star"
                            class="w-4 h-4 sm:w-5 sm:h-5 text-amber-400 group-hover:scale-125 group-hover:rotate-12 transition-transform duration-300" />
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold text-amber-400">{{ $user->stars }}</div>
                    <p class="text-slate-500 text-[10px] sm:text-sm mt-1 truncate">{{ $stages->count() - count($completedIds) }} {{ __('dashboard.more_to_go') }}</p>
                </div>

                {{-- Current Stage --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    style="transition-delay: 400ms"
                    class="col-span-2 lg:col-span-1 bg-white/5 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-white/10 hover:border-purple-500/30 hover:shadow-lg hover:shadow-purple-500/5 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-2 sm:mb-3">
                        <span class="text-slate-400 text-[10px] sm:text-sm uppercase tracking-wider font-semibold sm:font-normal">{{ __('dashboard.current_stage') }}</span>
                        <x-icon name="target"
                            class="w-4 h-4 sm:w-5 sm:h-5 text-purple-400 group-hover:scale-125 transition-transform duration-300" />
                    </div>
                    <div class="text-base sm:text-lg font-bold text-purple-400 truncate">
                        {{ $currentStage ? $currentStage->getTranslatedTitle() : __('dashboard.all_complete') }}
                    </div>
                    @if($currentStage)
                    <a href="{{ route('stages.show', $currentStage) }}"
                        class="text-[10px] sm:text-sm text-purple-300 hover:text-purple-200 mt-1 inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform">{{ __('dashboard.start_now') }}
                        <x-icon name="arrow-right" class="w-3 h-3 sm:w-3.5 sm:h-3.5" /></a>
                    @endif
                </div>
            </div>

            {{-- Analytics Cards Row 2 --}}
            <div x-show="shown" x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                style="transition-delay: 500ms" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">

                <div
                    class="bg-gradient-to-br from-green-500/10 to-emerald-500/5 rounded-2xl p-4 sm:p-5 border border-green-500/20 text-center hover:border-green-400/40 transition-all duration-300">
                    <div class="text-2xl sm:text-3xl font-bold text-green-400">{{ $completedCount }}</div>
                    <div class="text-[10px] sm:text-xs text-slate-400 mt-1">{{ __('dashboard.stages_completed') }}</div>
                </div>

                <div
                    class="bg-gradient-to-br from-blue-500/10 to-cyan-500/5 rounded-2xl p-4 sm:p-5 border border-blue-500/20 text-center hover:border-blue-400/40 transition-all duration-300">
                    <div class="text-2xl sm:text-3xl font-bold text-blue-400">{{ $totalAttempts }}</div>
                    <div class="text-[10px] sm:text-xs text-slate-400 mt-1">{{ __('dashboard.total_attempts') }}</div>
                </div>

                <div
                    class="bg-gradient-to-br from-amber-500/10 to-yellow-500/5 rounded-2xl p-4 sm:p-5 border border-amber-500/20 text-center hover:border-amber-400/40 transition-all duration-300">
                    <div class="text-2xl sm:text-3xl font-bold text-amber-400">{{ $successRate }}%</div>
                    <div class="text-[10px] sm:text-xs text-slate-400 mt-1">{{ __('dashboard.success_rate') }}</div>
                </div>

                <div
                    class="bg-gradient-to-br from-purple-500/10 to-pink-500/5 rounded-2xl p-4 sm:p-5 border border-purple-500/20 text-center hover:border-purple-400/40 transition-all duration-300">
                    <div class="text-2xl sm:text-3xl font-bold text-purple-400">
                        @if($totalTimeSpent > 3600)
                        {{ round($totalTimeSpent / 3600, 1) }}h
                        @elseif($totalTimeSpent > 60)
                        {{ round($totalTimeSpent / 60) }}m
                        @else
                        {{ $totalTimeSpent }}s
                        @endif
                    </div>
                    <div class="text-[10px] sm:text-xs text-slate-400 mt-1">{{ __('dashboard.time_studying') }}</div>
                </div>
            </div>

            {{-- Study Planner Widget --}}
            <div class="mb-6" x-show="shown" x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                style="transition-delay: 500ms">
                @php $activePlan = $user->activeStudyPlan(); @endphp
                @if($activePlan)
                    @php
                        $planTodayItems = $activePlan->todayItems();
                        $planTodayItems->load('stage');
                    @endphp
                    <div class="bg-gradient-to-r from-indigo-500/10 to-purple-500/10 backdrop-blur-sm rounded-2xl p-5 border border-indigo-500/20 hover:border-indigo-500/30 transition-all duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <x-icon name="academic-cap" class="w-5 h-5 text-indigo-400" />
                                {{ __('planner.widget_title') }}
                            </h3>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="text-amber-400 font-medium flex items-center gap-1">
                                    <x-icon name="clock" class="w-4 h-4" />
                                    {{ __('planner.widget_exam_in', ['days' => $activePlan->daysRemaining()]) }}
                                </span>
                                <a href="{{ route('planner.index') }}" class="text-indigo-400 hover:text-indigo-300 transition">
                                    {{ __('planner.view_plan') }} →
                                </a>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 mb-3">
                            <div class="flex-1 bg-white/10 rounded-full h-2 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-emerald-500 rounded-full transition-all duration-1000"
                                    x-bind:style="shown ? 'width: {{ $activePlan->total_progress }}%' : 'width: 0%'"></div>
                            </div>
                            <span class="text-sm font-bold text-indigo-300">{{ $activePlan->total_progress }}%</span>
                        </div>
                        @if($planTodayItems->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($planTodayItems as $pItem)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium
                                        {{ $pItem->is_completed ? 'bg-emerald-500/15 text-emerald-300' : 'bg-white/10 text-slate-300' }}">
                                        @if($pItem->is_completed)
                                            <x-icon name="check" class="w-3 h-3" />
                                        @else
                                            <x-icon name="target" class="w-3 h-3 text-indigo-400" />
                                        @endif
                                        {{ $pItem->stage->getTranslatedTitle() }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-slate-400">{{ __('planner.nothing_today') }}</p>
                        @endif
                    </div>
                @else
                    <a href="{{ route('planner.create') }}"
                        class="block bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-dashed border-indigo-500/30 hover:border-indigo-500/50 hover:bg-indigo-500/5 transition-all duration-300 group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <x-icon name="academic-cap" class="w-6 h-6 text-indigo-400" />
                            </div>
                            <div>
                                <p class="text-white font-semibold">{{ __('planner.widget_no_plan') }}</p>
                                <p class="text-sm text-slate-400">{{ __('planner.no_plan_desc') }}</p>
                            </div>
                            <x-icon name="arrow-right" class="w-5 h-5 text-indigo-400 ms-auto group-hover:translate-x-1 transition-transform" />
                        </div>
                    </a>
                @endif
            </div>

            {{-- Weekly Flexible Planner Widget --}}
            <div class="mb-8" x-show="shown" x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                style="transition-delay: 600ms">
                <div class="bg-gradient-to-br from-emerald-500/10 to-cyan-500/10 backdrop-blur-md rounded-3xl p-6 border border-emerald-500/20 shadow-2xl relative overflow-hidden group">
                    {{-- Decorative Glow --}}
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/20 rounded-full blur-3xl group-hover:bg-emerald-500/30 transition-colors duration-500"></div>
                    
                    <div class="relative z-10">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                    <x-icon name="calendar" class="w-6 h-6 text-emerald-400" />
                                    {{ __('navigation.weekly_planner') }}
                                </h3>
                                <p class="text-slate-400 text-sm mt-1">{{ __('planner.week_label') }} {{ $currentWeek }}: {{ $weeklyPlan?->stage?->getTranslatedTitle() ?? __('planner.not_set') }}</p>
                            </div>
                            <a href="{{ route('weekly-planner.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 rounded-xl text-sm font-bold border border-emerald-500/30 transition-all group/btn">
                                📅 {{ __('planner.view_plan') }}
                                <x-icon name="arrow-right" class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" />
                            </a>
                        </div>

                        @if($weeklyPlan)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Study Day Card --}}
                                <div class="bg-white/5 rounded-2xl p-4 border border-white/10 hover:bg-white/10 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('planner.study_session') }}</span>
                                        <x-icon name="academic-cap" class="w-4 h-4 text-indigo-400" />
                                    </div>
                                    @php $studyDay = $weeklyPlan->days->where('action_type', 'study')->first(); @endphp
                                    @if($studyDay)
                                        <div class="flex items-center justify-between">
                                            <span class="text-lg font-bold text-white capitalize">{{ trans('planner.days.'.$studyDay->day_name) }}</span>
                                            @if($studyDay->is_completed)
                                                <span class="flex items-center gap-1 text-xs font-bold text-emerald-400 bg-emerald-400/10 px-2 py-1 rounded-lg border border-emerald-400/20">
                                                    <x-icon name="check-circle" class="w-3 h-3" /> {{ __('planner.done') }}
                                                </span>
                                            @else
                                                <span class="text-xs font-medium text-indigo-300 bg-indigo-500/10 px-2 py-1 rounded-lg border border-indigo-500/20">{{ __('planner.planned') }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-500 italic text-sm">{{ __('planner.not_scheduled') }}</span>
                                    @endif
                                </div>

                                {{-- Test Day Card --}}
                                <div class="bg-white/5 rounded-2xl p-4 border border-white/10 hover:bg-white/10 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('planner.stage_quiz') }}</span>
                                        <x-icon name="beaker" class="w-4 h-4 text-purple-400" />
                                    </div>
                                    @php $testDay = $weeklyPlan->days->where('action_type', 'test')->first(); @endphp
                                    @if($testDay)
                                        <div class="flex items-center justify-between">
                                            <span class="text-lg font-bold text-white capitalize">{{ trans('planner.days.'.$testDay->day_name) }}</span>
                                            @if($testDay->is_completed)
                                                <span class="flex items-center gap-1 text-xs font-bold text-emerald-400 bg-emerald-400/10 px-2 py-1 rounded-lg border border-emerald-400/20">
                                                    <x-icon name="check-circle" class="w-3 h-3" /> {{ __('planner.passed') }}
                                                </span>
                                            @else
                                                <span class="text-xs font-medium text-purple-300 bg-purple-500/10 px-2 py-1 rounded-lg border border-purple-500/20">{{ __('planner.planned') }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-500 italic text-sm">{{ __('planner.not_scheduled') }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($weeklyPlan->status === 'completed')
                                <div class="mt-6 flex items-center gap-3 bg-emerald-500/20 border border-emerald-500/30 rounded-2xl p-4">
                                    <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                        <x-icon name="star" class="w-6 h-6 text-white" />
                                    </div>
                                    <div>
                                        <p class="text-white font-bold">{{ __('planner.stage_completed') }}!</p>
                                        <p class="text-emerald-300/80 text-xs">{{ __('planner.mastered_msg') }}</p>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="flex flex-col items-center justify-center py-4">
                                <p class="text-slate-400 text-sm mb-3 italic">{{ __('planner.no_plan_initialized') }}</p>
                                <a href="{{ route('weekly-planner.index') }}" class="text-emerald-400 text-sm font-bold hover:underline">{{ __('planner.go_to_planner') }} →</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Stage Roadmap --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 -translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0" style="transition-delay: 600ms"
                    class="lg:col-span-2 bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2"><x-icon name="map"
                            class="w-5 h-5 text-blue-400" /> {{ __('dashboard.your_roadmap') }}</h2>
                    <div class="space-y-3">
                        @foreach($stages as $stage)
                        @php
                        $isCompleted = in_array($stage->id, $completedIds);
                        $isUnlocked = $stage->isUnlockedFor($user);
                        $isCurrent = $currentStage && $currentStage->id === $stage->id;
                        @endphp
                        <div
                            class="flex items-center space-x-4 p-3 rounded-xl transition-all duration-300 hover:translate-x-1
                                                                                                    {{ $isCompleted ? 'bg-emerald-500/10 border border-emerald-500/20' :
                            ($isCurrent ? 'bg-blue-500/10 border border-blue-500/30' :
                                ($isUnlocked ? 'bg-white/5 border border-white/10' : 'bg-orange-500/10 border border-orange-500/20 opacity-80')) }}">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300
                                                                                                        {{ $isCompleted ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' :
                            ($isCurrent ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20 animate-pulse' :
                                ($isUnlocked ? 'bg-white/10 text-slate-400' : 'bg-orange-500/20 text-orange-400')) }}">
                                @if($isCompleted)
                                <x-icon name="check" class="w-5 h-5" />
                                @elseif(!$isUnlocked)
                                <x-icon name="lock-closed" class="w-4 h-4" />
                                @else
                                <span class="text-sm font-bold">{{ $stage->order }}</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p
                                    class="font-medium {{ $isCompleted ? 'text-emerald-300' : ($isCurrent ? 'text-blue-300' : ($isUnlocked ? 'text-slate-300' : 'text-orange-300')) }}">
                                    {{ $stage->getTranslatedTitle() }}
                                </p>
                                <p class="text-xs text-slate-500 flex items-center gap-2">
                                    <span class="flex items-center gap-0.5"><x-icon name="clock" class="w-3 h-3" />
                                        {{ $stage->time_limit_minutes }} {{ __('dashboard.minute') }}</span>
                                    <span>&middot;</span>
                                    <span>{{ $stage->passing_percentage }}% {{ __('dashboard.to_pass') }}</span>
                                </p>
                            </div>
                            @if($isUnlocked && !$isCompleted)
                            <a href="{{ route('stages.show', $stage) }}"
                                class="px-4 py-1.5 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium transition hover:scale-105">
                                {{ __('dashboard.start_now') }}
                            </a>
                            @elseif($isCompleted)
                            <span class="flex items-center gap-1 text-emerald-400 text-sm font-medium"><x-icon
                                    name="check-circle" class="w-4 h-4" /> {{ __('dashboard.passed') }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right sidebar: Recent + Notifications --}}
                <div class="space-y-6" x-show="shown" x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0" style="transition-delay: 700ms">

                    {{-- Recent Attempts --}}
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2"><x-icon
                                name="document-text" class="w-5 h-5 text-blue-400" /> {{ __('dashboard.recent_attempts') }}</h2>
                        @forelse($recentAttempts as $attempt)
                        <div
                            class="flex items-center justify-between py-2 border-b border-white/5 last:border-0 hover:bg-white/5 -mx-2 px-2 rounded-lg transition">
                            <div>
                                <p class="text-sm text-slate-300">{{ $attempt->stage->getTranslatedTitle() }}</p>
                                <p class="text-xs text-slate-500">{{ $attempt->created_at->diffForHumans() }}</p>
                            </div>
                            <span
                                class="flex items-center gap-1 text-sm font-medium {{ $attempt->completed_at ? ($attempt->passed ? 'text-emerald-400' : 'text-red-400') : 'text-cyan-400' }}">
                                @if($attempt->completed_at)
                                    {{ $attempt->score }}/{{ $attempt->total_questions }}
                                    @if($attempt->passed)
                                    <x-icon name="check" class="w-3.5 h-3.5" />
                                    @else
                                    <x-icon name="x-circle" class="w-3.5 h-3.5" />
                                    @endif
                                @else
                                    <span class="flex items-center gap-1">
                                        <x-icon name="clock" class="w-3.5 h-3.5 animate-spin-slow" />
                                        {{ __('dashboard.in_progress') }}
                                    </span>
                                @endif
                            </span>
                        </div>
                        @empty
                        <p class="text-slate-500 text-sm">{{ __('dashboard.no_attempts_yet') }}</p>
                        @endforelse
                    </div>

                    {{-- Notifications --}}
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2"><x-icon name="bell"
                                class="w-5 h-5 text-amber-400" /> {{ __('dashboard.notifications') }}</h2>
                        @forelse($notifications as $notif)
                        <div class="py-2 border-b border-white/5 last:border-0">
                            <p class="text-sm text-slate-300">
                                @if(app()->getLocale() === 'ar' && isset($notif->data['message_ar']))
                                {{ $notif->data['message_ar'] }}
                                @elseif(isset($notif->data['message_en']))
                                {{ $notif->data['message_en'] }}
                                @else
                                {{ $notif->data['message'] ?? '' }}
                                @endif
                            </p>
                            <p class="text-xs text-slate-500 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                        </div>
                        @empty
                        <p class="text-slate-500 text-sm flex items-center gap-1.5"><x-icon name="sparkles"
                                class="w-4 h-4 text-emerald-400" /> {{ __('dashboard.all_caught_up') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>