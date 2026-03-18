<x-app-layout>
    @section('title', 'Dashboard')

    <div class="py-8" x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 100)" x-cloak>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Welcome Header --}}
            <div class="mb-8" x-show="shown" x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <h1 class="text-3xl font-bold text-white flex items-center gap-2">Welcome back, {{ $user->name }}!
                    <x-icon name="hand-wave" class="w-8 h-8 text-amber-400" />
                </h1>
                <p class="text-slate-400 mt-1">Continue your chemistry journey</p>
            </div>

            {{-- Stats Cards Row 1 --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                {{-- Streak --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    style="transition-delay: 50ms"
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-orange-500/30 hover:shadow-lg hover:shadow-orange-500/5 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-slate-400 text-sm">Study Streak</span>
                        <x-icon name="fire"
                            class="w-5 h-5 text-orange-400 group-hover:scale-125 transition-transform duration-300 {{ $user->streak > 0 ? 'animate-pulse' : '' }}" />
                    </div>
                    <div class="text-3xl font-bold text-orange-400">{{ $user->streak }} <span
                            class="text-sm font-normal text-slate-500">Days</span></div>
                    <p class="text-slate-500 text-sm mt-1">Keep it burning!</p>
                </div>

                {{-- Progress --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    style="transition-delay: 100ms"
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-cyan-500/30 hover:shadow-lg hover:shadow-cyan-500/5 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-slate-400 text-sm">Progress</span>
                        <x-icon name="trending-up"
                            class="w-5 h-5 text-cyan-400 group-hover:scale-125 transition-transform duration-300" />
                    </div>
                    <div class="text-3xl font-bold text-white">{{ $user->progressPercentage() }}%</div>
                    <div class="mt-3 w-full bg-white/10 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-cyan-500 to-emerald-500 h-2.5 rounded-full transition-all duration-[2000ms] ease-out"
                            x-bind:style="shown ? 'width: {{ $user->progressPercentage() }}%' : 'width: 0%'"></div>
                    </div>
                </div>

                {{-- Points --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    style="transition-delay: 200ms"
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-emerald-500/30 hover:shadow-lg hover:shadow-emerald-500/5 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-slate-400 text-sm">Total Points</span>
                        <x-icon name="medal"
                            class="w-5 h-5 text-emerald-400 group-hover:scale-125 transition-transform duration-300" />
                    </div>
                    <div class="text-3xl font-bold text-emerald-400">{{ number_format($user->total_points) }}</div>
                    <p class="text-slate-500 text-sm mt-1">Keep earning!</p>
                </div>

                {{-- Stars --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    style="transition-delay: 300ms"
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-amber-500/30 hover:shadow-lg hover:shadow-amber-500/5 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-slate-400 text-sm">Stars Earned</span>
                        <x-icon name="star"
                            class="w-5 h-5 text-amber-400 group-hover:scale-125 group-hover:rotate-12 transition-transform duration-300" />
                    </div>
                    <div class="text-3xl font-bold text-amber-400">{{ $user->stars }}</div>
                    <p class="text-slate-500 text-sm mt-1">{{ $stages->count() - count($completedIds) }} more to go</p>
                </div>

                {{-- Current Stage --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    style="transition-delay: 400ms"
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-purple-500/30 hover:shadow-lg hover:shadow-purple-500/5 transition-all duration-300 group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-slate-400 text-sm">Current Stage</span>
                        <x-icon name="target"
                            class="w-5 h-5 text-purple-400 group-hover:scale-125 transition-transform duration-300" />
                    </div>
                    <div class="text-lg font-bold text-purple-400">
                        {{ $currentStage ? $currentStage->title : 'All Complete!' }}
                    </div>
                    @if($currentStage)
                        <a href="{{ route('stages.show', $currentStage) }}"
                            class="text-sm text-purple-300 hover:text-purple-200 mt-1 inline-flex items-center gap-1 group-hover:translate-x-1 transition-transform">Start
                            now <x-icon name="arrow-right" class="w-3.5 h-3.5" /></a>
                    @endif
                </div>
            </div>

            {{-- Analytics Cards Row 2 --}}
            <div x-show="shown" x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                style="transition-delay: 500ms" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">

                <div
                    class="bg-gradient-to-br from-green-500/10 to-emerald-500/5 rounded-2xl p-5 border border-green-500/20 text-center hover:border-green-400/40 transition-all duration-300">
                    <div class="text-3xl font-bold text-green-400">{{ $completedCount }}</div>
                    <div class="text-xs text-slate-400 mt-1">Stages Completed</div>
                </div>

                <div
                    class="bg-gradient-to-br from-blue-500/10 to-cyan-500/5 rounded-2xl p-5 border border-blue-500/20 text-center hover:border-blue-400/40 transition-all duration-300">
                    <div class="text-3xl font-bold text-blue-400">{{ $totalAttempts }}</div>
                    <div class="text-xs text-slate-400 mt-1">Total Attempts</div>
                </div>

                <div
                    class="bg-gradient-to-br from-amber-500/10 to-yellow-500/5 rounded-2xl p-5 border border-amber-500/20 text-center hover:border-amber-400/40 transition-all duration-300">
                    <div class="text-3xl font-bold text-amber-400">{{ $successRate }}%</div>
                    <div class="text-xs text-slate-400 mt-1">Success Rate</div>
                </div>

                <div
                    class="bg-gradient-to-br from-purple-500/10 to-pink-500/5 rounded-2xl p-5 border border-purple-500/20 text-center hover:border-purple-400/40 transition-all duration-300">
                    <div class="text-3xl font-bold text-purple-400">
                        @if($totalTimeSpent > 3600)
                            {{ round($totalTimeSpent / 3600, 1) }}h
                        @elseif($totalTimeSpent > 60)
                            {{ round($totalTimeSpent / 60) }}m
                        @else
                            {{ $totalTimeSpent }}s
                        @endif
                    </div>
                    <div class="text-xs text-slate-400 mt-1">Time Studying</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Stage Roadmap --}}
                <div x-show="shown" x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 -translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0" style="transition-delay: 600ms"
                    class="lg:col-span-2 bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2"><x-icon name="map"
                            class="w-5 h-5 text-blue-400" /> Your Roadmap</h2>
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
                                ($isUnlocked ? 'bg-white/5 border border-white/10' : 'bg-white/[0.02] border border-white/5 opacity-50')) }}">
                                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300
                                                                                    {{ $isCompleted ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' :
                            ($isCurrent ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/20 animate-pulse' :
                                ($isUnlocked ? 'bg-white/10 text-slate-400' : 'bg-white/5 text-slate-600')) }}">
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
                                                        class="font-medium {{ $isCompleted ? 'text-emerald-300' : ($isCurrent ? 'text-blue-300' : 'text-slate-300') }}">
                                                        {{ $stage->title }}
                                                    </p>
                                                    <p class="text-xs text-slate-500 flex items-center gap-2">
                                                        <span class="flex items-center gap-0.5"><x-icon name="clock" class="w-3 h-3" />
                                                            {{ $stage->time_limit_minutes }} min</span>
                                                        <span>&middot;</span>
                                                        <span>{{ $stage->passing_percentage }}% to pass</span>
                                                    </p>
                                                </div>
                                                @if($isUnlocked && !$isCompleted)
                                                    <a href="{{ route('stages.show', $stage) }}"
                                                        class="px-4 py-1.5 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium transition hover:scale-105">
                                                        Start
                                                    </a>
                                                @elseif($isCompleted)
                                                    <span class="flex items-center gap-1 text-emerald-400 text-sm font-medium"><x-icon
                                                            name="check-circle" class="w-4 h-4" /> Passed</span>
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
                                name="document-text" class="w-5 h-5 text-blue-400" /> Recent Attempts</h2>
                        @forelse($recentAttempts as $attempt)
                            <div
                                class="flex items-center justify-between py-2 border-b border-white/5 last:border-0 hover:bg-white/5 -mx-2 px-2 rounded-lg transition">
                                <div>
                                    <p class="text-sm text-slate-300">{{ $attempt->stage->title }}</p>
                                    <p class="text-xs text-slate-500">{{ $attempt->created_at->diffForHumans() }}</p>
                                </div>
                                <span
                                    class="flex items-center gap-1 text-sm font-medium {{ $attempt->passed ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $attempt->score }}/{{ $attempt->total_questions }}
                                    @if($attempt->passed) <x-icon name="check" class="w-3.5 h-3.5" /> @else <x-icon
                                    name="x-circle" class="w-3.5 h-3.5" /> @endif
                                </span>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm">No attempts yet. Start your first quiz!</p>
                        @endforelse
                    </div>

                    {{-- Notifications --}}
                    <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2"><x-icon name="bell"
                                class="w-5 h-5 text-amber-400" /> Notifications</h2>
                        @forelse($notifications as $notif)
                            <div class="py-2 border-b border-white/5 last:border-0">
                                <p class="text-sm text-slate-300">{{ $notif->data['message'] ?? '' }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="text-slate-500 text-sm flex items-center gap-1.5"><x-icon name="sparkles"
                                    class="w-4 h-4 text-emerald-400" /> All caught up!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>