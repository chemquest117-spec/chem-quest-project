<nav x-data="{ open: false, notifOpen: false }"
    class="bg-black/30 backdrop-blur-md border-b border-white/10 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <x-chemtrack-logo size="sm" />
                    <span
                        class="text-xl font-bold bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">ChemTrack</span>
                </a>

                <!-- Desktop Nav Links -->
                <div class="hidden sm:flex sm:ml-10 sm:space-x-1">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <x-icon name="chart-bar" class="w-4 h-4" /> Dashboard
                    </a>
                    <a href="{{ route('stages.index') }}"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('stages.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <x-icon name="target" class="w-4 h-4" /> Stages
                    </a>
                    <a href="{{ route('leaderboard') }}"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                              {{ request()->routeIs('leaderboard') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <x-icon name="trophy" class="w-4 h-4" /> Leaderboard
                    </a>
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                                                      {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.stages.*') || request()->routeIs('admin.students.*') ? 'bg-amber-500/20 text-amber-300' : 'text-amber-400 hover:bg-amber-500/10' }}">
                            <x-icon name="cog" class="w-4 h-4" /> Admin
                        </a>
                        <a href="{{ route('admin.analytics') }}"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                                                      {{ request()->routeIs('admin.analytics') ? 'bg-amber-500/20 text-amber-300' : 'text-amber-400 hover:bg-amber-500/10' }}">
                            <x-icon name="chart-line" class="w-4 h-4" /> Analytics
                        </a>
                    @endif
                </div>
            </div>

            <!-- Right side -->
            <div class="hidden sm:flex sm:items-center sm:space-x-3">
                <!-- Points & Stars -->
                <div class="flex items-center space-x-2 sm:space-x-3 text-sm">
                    @if(auth()->user()->streak > 0)
                        <span class="flex items-center gap-1 bg-orange-500/20 text-orange-400 px-3 py-1 rounded-full"
                            title="Study Streak">
                            <x-icon name="fire"
                                class="w-3.5 h-3.5 {{ auth()->user()->streak > 0 ? 'animate-pulse' : '' }}" />
                            {{ auth()->user()->streak }}
                        </span>
                    @endif
                    <span class="flex items-center gap-1 bg-amber-500/20 text-amber-300 px-3 py-1 rounded-full">
                        <x-icon name="star" class="w-3.5 h-3.5" /> {{ auth()->user()->stars }}
                    </span>
                    <span class="flex items-center gap-1 bg-emerald-500/20 text-emerald-300 px-3 py-1 rounded-full">
                        <x-icon name="medal" class="w-3.5 h-3.5" /> {{ number_format(auth()->user()->total_points) }}
                        pts
                    </span>
                </div>

                <!-- Notification Bell -->
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen"
                        class="relative p-2 rounded-lg text-slate-300 hover:bg-white/10 transition">
                        <x-icon name="bell" class="w-5 h-5" />
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center animate-pulse">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </button>

                    <!-- Notification Dropdown -->
                    <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="absolute right-0 mt-2 w-80 bg-slate-800 rounded-xl shadow-2xl border border-white/10 overflow-hidden z-50">
                        <div class="p-3 border-b border-white/10 flex justify-between items-center">
                            <span class="text-sm font-semibold text-white">Notifications</span>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <form action="{{ route('notifications.readAll') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs text-cyan-400 hover:text-cyan-300">Mark all
                                        read</button>
                                </form>
                            @endif
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                <div class="px-4 py-3 border-b border-white/5 hover:bg-white/5">
                                    <p class="text-sm text-slate-200">{{ $notification->data['message'] ?? '' }}</p>
                                    <p class="text-xs text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            @empty
                                <div
                                    class="px-4 py-6 text-center text-slate-400 text-sm flex items-center justify-center gap-1.5">
                                    <x-icon name="check-circle" class="w-4 h-4 text-emerald-400" /> No new notifications
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-white/10 transition">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open"
                    class="p-2 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Nav -->
    <div :class="{'block': open, 'hidden': !open}"
        class="hidden sm:hidden bg-black/40 backdrop-blur-md border-t border-white/10">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10">
                <x-icon name="chart-bar" class="w-4 h-4" /> Dashboard</a>
            <a href="{{ route('stages.index') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10">
                <x-icon name="target" class="w-4 h-4" /> Stages</a>
            <a href="{{ route('leaderboard') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10">
                <x-icon name="trophy" class="w-4 h-4" /> Leaderboard</a>
            @if(auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-amber-400 hover:bg-amber-500/10">
                    <x-icon name="cog" class="w-4 h-4" /> Admin</a>
                <a href="{{ route('admin.analytics') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-amber-400 hover:bg-amber-500/10">
                    <x-icon name="chart-line" class="w-4 h-4" /> Analytics</a>
            @endif
        </div>
        <div class="pt-4 pb-3 border-t border-white/10 px-4">
            <div class="text-base font-medium text-white">{{ Auth::user()->name }}</div>
            <div class="text-sm text-slate-400">{{ Auth::user()->email }}</div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}"
                    class="block px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="block w-full text-left px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10">Log
                        Out</button>
                </form>
            </div>
        </div>
    </div>
</nav>