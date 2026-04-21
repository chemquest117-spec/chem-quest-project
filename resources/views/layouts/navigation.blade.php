<nav x-data="{ open: false, notifOpen: false }" class="bg-black/30 backdrop-blur-md border-b border-white/10 sticky top-0 z-50">
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
                <ul class="hidden sm:flex sm:ms-10 sm:space-x-1 items-center">
                    {{-- Dashboard Dropdown --}}
                    <li class="flex items-center relative" x-data="{ dashOpen: false }">
                        <button @click="dashOpen = !dashOpen" @click.away="dashOpen = false"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                                   {{ request()->routeIs('dashboard') || request()->routeIs('stages.*') || request()->routeIs('planner.*') || request()->routeIs('weekly-planner.*') ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                            <x-icon name="chart-bar" class="w-4 h-4" /> {{ __('dashboard.title') }}
                            <svg class="w-4 h-4 ms-1 transition-transform" :class="dashOpen ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="dashOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            class="absolute top-full start-0 mt-2 w-56 bg-slate-800 rounded-xl shadow-2xl border border-white/10 overflow-hidden z-50">

                            <a href="{{ route('dashboard') }}"
                                class="flex items-center gap-2 px-4 py-3 text-sm transition-colors
                                       {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white font-bold' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                                <x-icon name="chart-bar" class="w-4 h-4 text-cyan-400" /> {{ __('dashboard.title') }}
                            </a>

                            <div class="border-t border-white/5"></div>

                            <a href="{{ route('stages.index') }}"
                                class="flex items-center gap-2 px-4 py-3 text-sm transition-colors
                                       {{ request()->routeIs('stages.*') ? 'bg-white/10 text-white font-bold' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                                <x-icon name="target" class="w-4 h-4 text-emerald-400" /> {{ __('stages.title') }}
                            </a>

                            <a href="{{ route('planner.index') }}"
                                class="flex items-center gap-2 px-4 py-3 text-sm transition-colors
                                       {{ request()->routeIs('planner.*') ? 'bg-white/10 text-white font-bold' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                                <x-icon name="academic-cap" class="w-4 h-4 text-indigo-400" />
                                {{ __('navigation.study_planner') }}
                            </a>

                            <a href="{{ route('weekly-planner.index') }}"
                                class="flex items-center gap-2 px-4 py-3 text-sm transition-colors
                                       {{ request()->routeIs('weekly-planner.*') ? 'bg-white/10 text-white font-bold' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                                <x-icon name="calendar" class="w-4 h-4 text-purple-400" />
                                {{ __('navigation.weekly_planner') }}
                            </a>
                        </div>
                    </li>

                    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin'))
                    <li class="flex items-center relative" x-data="{ adminOpen: false }">
                        <button @click="adminOpen = !adminOpen" @click.away="adminOpen = false"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                                       {{ request()->routeIs('admin.*') ? 'bg-blue-500/20 text-blue-300' : 'text-blue-400 hover:bg-blue-500/10' }}">
                            <x-icon name="cog" class="w-4 h-4" /> {{ __('navigation.admin') }}
                            <svg class="w-4 h-4 ms-1 transition-transform" :class="adminOpen ? 'rotate-180' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="adminOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            class="absolute top-full start-0 mt-2 w-48 bg-slate-800 rounded-xl shadow-2xl border border-white/10 overflow-hidden z-50">
                            <a href="{{ route('admin.dashboard') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:bg-white/10 transition-colors">
                                <x-icon name="cog" class="w-4 h-4" /> {{ __('navigation.admin') }}
                            </a>
                            <a href="{{ route('admin.students.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:bg-white/10 transition-colors">
                                <x-icon name="users" class="w-4 h-4" /> {{ __('navigation.students') }}
                            </a>
                            @if (auth()->user()->hasRole('super_admin'))
                            <a href="{{ route('admin.admins.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:bg-white/10 transition-colors">
                                <x-icon name="user-group" class="w-4 h-4" /> {{ __('navigation.admins') }}
                            </a>
                            <a href="{{ route('admin.license.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:bg-white/10 transition-colors">
                                <x-icon name="key" class="w-4 h-4" /> {{ __('navigation.license') }}
                            </a>
                            <a href="{{ route('admin.audit.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:bg-white/10 transition-colors">
                                <x-icon name="clipboard-document-list" class="w-4 h-4" /> {{ __('navigation.audit_logs') }}
                            </a>
                            @endif
                            <div class="border-t border-white/5"></div>
                            <a href="{{ route('admin.analytics') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:bg-white/10 transition-colors">
                                <x-icon name="chart-line" class="w-4 h-4" /> {{ __('navigation.analytics') }}
                            </a>
                            <a href="{{ route('admin.planner-settings') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:bg-white/10 transition-colors">
                                <x-icon name="academic-cap" class="w-4 h-4" />
                                {{ __('navigation.planner_settings') }}
                            </a>
                            <a href="{{ route('admin.notifications.create') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm {{ request()->routeIs('admin.notifications.*') ? 'text-amber-400 font-bold bg-white/5' : 'text-slate-300 hover:bg-white/10' }} transition-colors">
                                <x-icon name="bell" class="w-4 h-4" /> {{ __('navigation.broadcast') }}
                            </a>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>

            <!-- Right side -->
            <div class="hidden sm:flex sm:items-center sm:space-x-3">
                <!-- Points & Stars -->
                <div class="flex items-center space-x-2 sm:space-x-3 text-sm">
                    @if (auth()->user()->streak > 0)
                    <span class="flex items-center gap-1 bg-orange-500/20 text-orange-400 px-3 py-1 rounded-full"
                        title="{{ __('navigation.study_streak') }}">
                        <x-icon name="fire"
                            class="w-3.5 h-3.5 {{ auth()->user()->streak > 0 ? 'animate-pulse' : '' }}" />
                        {{ auth()->user()->streak }}
                    </span>
                    @endif
                    <span class="flex items-center gap-1 bg-amber-500/20 text-amber-300 px-3 py-1 rounded-full">
                        <x-icon name="star" class="w-3.5 h-3.5" /> {{ auth()->user()->stars }}
                    </span>
                    <span class="flex items-center gap-1 bg-emerald-500/20 text-emerald-300 px-3 py-1 rounded-full">
                        <x-icon name="medal" class="w-3.5 h-3.5" />
                        {{ number_format(auth()->user()->total_points) }}
                        {{ __('dashboard.points') }}
                    </span>
                </div>

                <!-- Notification Bell -->
                <div class="relative" x-data="{
                    notifOpen: false,
                    unreadCount: {{ auth()->user()->unreadNotifications->count() }},
                    markingRead: false
                }"
                    @fcm-message.window="
                        unreadCount++;
                        let list = document.getElementById('notification-list');
                        let empty = document.getElementById('notification-empty');
                        if (empty) empty.remove();
                        if (list) {
                            let html = `
                                <div class='px-4 py-3 border-b border-white/5 bg-cyan-500/10 is-unread transition-colors'>
                                    <div class='flex gap-2'>
                                        <span class='w-2 h-2 mt-1.5 rounded-full bg-cyan-400 flex-shrink-0 unread-dot animate-pulse'></span>
                                        <div class='flex-1'>
                                            <p class='text-sm text-white font-medium'>${$event.detail.title}</p>
                                            <p class='text-xs text-slate-400 mt-1'>Just now</p>
                                        </div>
                                    </div>
                                </div>
                            `;
                            list.insertAdjacentHTML('afterbegin', html);
                        }
                    ">
                    <button @click="notifOpen = !notifOpen" @click.away="notifOpen = false"
                        class="relative p-2 rounded-lg text-slate-300 hover:bg-white/10 transition">
                        <x-icon name="bell" class="w-5 h-5" />
                        <span x-show="unreadCount > 0" x-text="unreadCount" x-cloak
                            class="absolute -top-1 -end-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center animate-pulse">
                        </span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div x-show="notifOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="absolute end-0 mt-2 w-80 bg-slate-800 rounded-xl shadow-2xl border border-white/10 overflow-hidden z-50">
                        <div class="p-3 border-b border-white/10 flex justify-between items-center">
                            <span class="text-sm font-semibold text-white">{{ __('navigation.notifications') }}</span>
                            <button x-show="unreadCount > 0"
                                @click="
                                        markingRead = true;
                                        fetch('{{ route('notifications.readAll') }}', {
                                            method: 'POST',
                                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                        }).then(() => {
                                            unreadCount = 0;
                                            document.querySelectorAll('.is-unread').forEach(el => el.classList.remove('bg-cyan-500/10', 'is-unread'));
                                            document.querySelectorAll('.unread-dot').forEach(el => el.remove());
                                        }).finally(() => { markingRead = false });
                                    "
                                class="text-xs text-cyan-400 hover:text-cyan-300 disabled:opacity-50 transition"
                                :disabled="markingRead">
                                {{ __('navigation.mark_all_read') }}
                            </button>
                        </div>
                        <div class="max-h-64 overflow-y-auto" id="notification-list">
                            @forelse(auth()->user()->notifications->take(5) as $notification)
                            <div
                                class="px-4 py-3 border-b border-white/5 hover:bg-white/5 transition-colors {{ is_null($notification->read_at) ? 'bg-cyan-500/10 is-unread' : '' }}">
                                <div class="flex gap-2">
                                    @if (is_null($notification->read_at))
                                    <span
                                        class="w-2 h-2 mt-1.5 rounded-full bg-cyan-400 flex-shrink-0 unread-dot animate-pulse"></span>
                                    @endif
                                    <div class="flex-1">
                                        <p
                                            class="text-sm {{ is_null($notification->read_at) ? 'text-white font-medium' : 'text-slate-300' }}">
                                            @if (app()->getLocale() === 'ar' && isset($notification->data['message_ar']))
                                            {{ $notification->data['message_ar'] }}
                                            @elseif(isset($notification->data['message_en']))
                                            {{ $notification->data['message_en'] }}
                                            @else
                                            {{ $notification->data['message'] ?? '' }}
                                            @endif
                                        </p>
                                        <p class="text-xs text-slate-400 mt-1">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div id="notification-empty"
                                class="px-4 py-6 text-center text-slate-400 text-sm flex items-center justify-center gap-1.5">
                                <x-icon name="check-circle" class="w-4 h-4 text-emerald-400" />
                                {{ __('navigation.no_new_notifications') }}
                            </div>
                            @endforelse
                        </div>
                        <div class="border-t border-white/10 bg-slate-800/50">
                            <a href="{{ route('notifications.index') }}"
                                class="block px-4 py-2 text-center text-sm text-cyan-400 hover:text-cyan-300 hover:bg-white/5 transition">
                                {{ __('navigation.view_all_notifications') }}
                            </a>
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
                        <!-- Leaderboard -->
                        <x-dropdown-link :href="route('leaderboard')">
                            <div class="flex items-center gap-2 text-amber-400">
                                <x-icon name="trophy" class="w-4 h-4" /> {{ __('navigation.leaderboard') }}
                            </div>
                        </x-dropdown-link>

                        <!-- Language Switcher -->
                        <x-dropdown-link :href="app()->getLocale() === 'ar' ? route('language.switch', 'en') : route('language.switch', 'ar')">
                            <div class="flex items-center gap-2 text-slate-300">
                                @if (app()->getLocale() === 'ar')
                                <img src="https://flagcdn.com/w20/us.png"
                                    srcset="https://flagcdn.com/w40/us.png 2x" width="20" alt="English">
                                🇺🇸 English
                                @else
                                <img src="https://flagcdn.com/w20/eg.png"
                                    srcset="https://flagcdn.com/w40/eg.png 2x" width="20" alt="Arabic">
                                🇪🇬 العربية
                                @endif
                            </div>
                        </x-dropdown-link>

                        <div class="border-t border-white/10 my-1"></div>

                        <x-dropdown-link :href="route('profile.edit')">
                            <div class="flex items-center gap-2">
                                <x-icon name="user" class="w-4 h-4" /> {{ __('navigation.profile') }}
                            </div>
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <div class="flex items-center gap-2 text-red-400">
                                    <x-icon name="logout" class="w-4 h-4" /> {{ __('navigation.log_out') }}
                                </div>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile controls (Language + Hamburger) -->
            <div class="-me-2 flex items-center sm:hidden gap-2">
                <!-- Mobile Language Switcher -->
                <div class="relative">
                    @if (app()->getLocale() === 'ar')
                    <a href="{{ route('language.switch', 'en') }}"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-white/10 transition">
                        <img src="https://flagcdn.com/w20/us.png" srcset="https://flagcdn.com/w40/us.png 2x"
                            width="20" alt="English"> 🇺🇸 EN
                    </a>
                    @else
                    <a href="{{ route('language.switch', 'ar') }}"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-white/10 transition"
                        dir="rtl">
                        <img src="https://flagcdn.com/w20/eg.png" srcset="https://flagcdn.com/w40/eg.png 2x"
                            width="20" alt="Arabic"> 🇪🇬 AR
                    </a>
                    @endif
                </div>

                <button @click="open = !open"
                    class="p-2 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition">
                    <div class="relative w-6 h-6 transform transition-transform duration-300"
                        :class="open ? 'rotate-90' : 'rotate-0'">
                        <svg x-transition.opacity.duration.200ms x-show="!open" class="absolute inset-0 h-6 w-6"
                            stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-transition.opacity.duration.200ms x-show="open" x-cloak
                            class="absolute inset-0 h-6 w-6" stroke="currentColor" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Nav -->
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-300 origin-top"
        x-transition:enter-start="opacity-0 -translate-y-4 scale-y-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
        x-transition:leave="transition ease-in duration-200 origin-top"
        x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
        x-transition:leave-end="opacity-0 -translate-y-4 scale-y-95"
        class="sm:hidden bg-black/40 backdrop-blur-md border-t border-white/10 transform">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10
                       {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white font-bold' : '' }}">
                <x-icon name="chart-bar" class="w-4 h-4 text-cyan-400" /> {{ __('dashboard.title') }}</a>
            <a href="{{ route('stages.index') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10
                       {{ request()->routeIs('stages.*') ? 'bg-white/10 text-white font-bold' : '' }}">
                <x-icon name="target" class="w-4 h-4 text-emerald-400" /> {{ __('stages.title') }}</a>
            <a href="{{ route('planner.index') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10
                       {{ request()->routeIs('planner.*') ? 'bg-white/10 text-white font-bold' : '' }}">
                <x-icon name="academic-cap" class="w-4 h-4 text-indigo-400" />
                {{ __('navigation.study_planner') }}</a>
            <a href="{{ route('weekly-planner.index') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10
                       {{ request()->routeIs('weekly-planner.*') ? 'bg-white/10 text-white font-bold' : '' }}">
                <x-icon name="calendar" class="w-4 h-4 text-purple-400" /> {{ __('navigation.weekly_planner') }}</a>
            <a href="{{ route('leaderboard') }}"
                class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10
                       {{ request()->routeIs('leaderboard') ? 'bg-white/10 text-white font-bold' : '' }}">
                <x-icon name="trophy" class="w-4 h-4 text-amber-400" /> {{ __('navigation.leaderboard') }}</a>

            {{-- Mobile Stats --}}
            <div class="grid grid-cols-3 gap-2 px-3 py-3 border-t border-white/5 mt-2">
                <div class="flex flex-col items-center justify-center p-2 rounded-xl bg-orange-500/10 text-orange-400">
                    <x-icon name="fire"
                        class="w-4 h-4 mb-1 {{ auth()->user()->streak > 0 ? 'animate-pulse' : '' }}" />
                    <span class="text-xs font-bold">{{ auth()->user()->streak }}</span>
                </div>
                <div class="flex flex-col items-center justify-center p-2 rounded-xl bg-amber-500/10 text-amber-300">
                    <x-icon name="star" class="w-4 h-4 mb-1" />
                    <span class="text-xs font-bold">{{ auth()->user()->stars }}</span>
                </div>
                <div
                    class="flex flex-col items-center justify-center p-2 rounded-xl bg-emerald-500/10 text-emerald-300">
                    <x-icon name="medal" class="w-4 h-4 mb-1" />
                    <span class="text-xs font-bold">{{ number_format(auth()->user()->total_points) }}</span>
                </div>
            </div>

            @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin'))
            <div class="border-t border-white/5 pt-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-blue-400 hover:bg-blue-500/10">
                    <x-icon name="cog" class="w-4 h-4" /> {{ __('navigation.admin') }}</a>
                <a href="{{ route('admin.students.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10">
                    <x-icon name="users" class="w-4 h-4" /> {{ __('navigation.students') }}</a>
                @if (auth()->user()->hasRole('super_admin'))
                <a href="{{ route('admin.admins.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10">
                    <x-icon name="user-group" class="w-4 h-4" /> {{ __('navigation.admins') }}</a>
                <a href="{{ route('admin.license.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10">
                    <x-icon name="key" class="w-4 h-4" /> {{ __('navigation.license') }}</a>
                <a href="{{ route('admin.audit.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10">
                    <x-icon name="document-text" class="w-4 h-4" /> {{ __('navigation.audit_logs') }}</a>
                @endif
                <a href="{{ route('admin.analytics') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-amber-400 hover:bg-amber-500/10">
                    <x-icon name="chart-line" class="w-4 h-4" /> {{ __('navigation.analytics') }}</a>
                <a href="{{ route('admin.notifications.create') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('admin.notifications.*') ? 'bg-amber-500/10 text-amber-400 font-bold' : 'text-slate-300 hover:bg-white/10' }}">
                    <x-icon name="bell" class="w-4 h-4" /> {{ __('navigation.broadcast') }}</a>
            </div>
            @endif
        </div>
        <div class="pt-4 pb-3 border-t border-white/10 px-4">
            <div class="text-base font-medium text-white">{{ Auth::user()->name }}</div>
            <div class="text-sm text-slate-400">{{ Auth::user()->email }}</div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:bg-white/10">
                    <x-icon name="user" class="w-4 h-4" /> {{ __('navigation.profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 w-full text-start px-3 py-2 rounded-lg text-red-400 hover:bg-white/10">
                        <x-icon name="logout" class="w-4 h-4" /> {{ __('navigation.log_out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>