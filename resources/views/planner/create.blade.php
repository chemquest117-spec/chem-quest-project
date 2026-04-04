<x-app-layout>
    @section('title', __('planner.create_plan'))

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="mb-8">
                <a href="{{ route('planner.index') }}"
                    class="flex items-center gap-1 text-slate-400 hover:text-white text-sm mb-2 transition">
                    <x-icon name="arrow-right" class="w-4 h-4 rotate-180" /> {{ __('planner.title') }}
                </a>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <x-icon name="sparkles" class="w-8 h-8 text-indigo-400" />
                    {{ __('planner.setup_title') }}
                </h1>
                <p class="text-slate-400 mt-1">{{ __('planner.setup_subtitle') }}</p>
            </div>

            {{-- Multi-Step Wizard --}}
            <div x-data="{
                step: 1,
                totalSteps: 4,
                formData: {
                    start_date: '{{ old('start_date', now()->format('Y-m-d')) }}',
                    exam_date: '{{ old('exam_date', now()->addWeeks(4)->format('Y-m-d')) }}',
                    preferred_days: {{ json_encode(old('preferred_days', ['sun', 'mon', 'tue', 'wed', 'thu'])) }},
                    hours_per_day: {{ old('hours_per_day', 2) }},
                    pace: '{{ old('pace', 'medium') }}'
                },
                errorMessage: '',
                nextStep() {
                    this.errorMessage = '';
                    
                    if (this.step === 1) {
                        const start = new Date(this.formData.start_date);
                        const exam = new Date(this.formData.exam_date);
                        
                        // Set hours to 0 to compare dates purely
                        start.setHours(0, 0, 0, 0);
                        exam.setHours(0, 0, 0, 0);
                        
                        const diffTime = exam.getTime() - start.getTime();
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        
                        if (exam <= start) {
                            this.errorMessage = '{{ __("planner.err_exam_after_start") }}';
                            return;
                        }
                        if (diffDays < 3) {
                            this.errorMessage = '{{ __("planner.err_min_days") }}';
                            return;
                        }
                    } else if (this.step === 2) {
                        if (this.formData.preferred_days.length === 0) {
                            this.errorMessage = '{{ __("planner.err_select_day") }}';
                            return;
                        }
                    }
                    
                    if (this.step < this.totalSteps) this.step++;
                },
                toggleDay(day) {
                    const idx = this.formData.preferred_days.indexOf(day);
                    if (idx > -1) {
                        this.formData.preferred_days.splice(idx, 1);
                    } else {
                        this.formData.preferred_days.push(day);
                    }
                    if (this.formData.preferred_days.length > 0) this.errorMessage = '';
                },
                isDaySelected(day) {
                    return this.formData.preferred_days.includes(day);
                }
            }">
                {{-- Progress Bar --}}
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-2">
                        <template x-for="s in totalSteps" :key="s">
                            <button @click="step = s"
                                class="flex items-center gap-1.5 text-xs font-medium transition-all duration-300"
                                :class="s <= step ? 'text-indigo-400' : 'text-slate-500'">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300"
                                    :class="s < step ? 'bg-indigo-500 text-white' : (s === step ? 'bg-indigo-500/20 text-indigo-400 ring-2 ring-indigo-500/50' : 'bg-white/10 text-slate-500')">
                                    <template x-if="s < step"><x-icon name="check" class="w-3.5 h-3.5" /></template>
                                    <template x-if="s >= step"><span x-text="s"></span></template>
                                </span>
                            </button>
                        </template>
                    </div>
                    <div class="w-full bg-white/10 rounded-full h-1.5 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500"
                            :style="`width: ${(step / totalSteps) * 100}%`"></div>
                    </div>
                </div>

                <form method="POST" action="{{ route('planner.store') }}" id="plannerForm">
                    @csrf

                    {{-- Hidden fields to submit array data --}}
                    <template x-for="day in formData.preferred_days" :key="day">
                        <input type="hidden" name="preferred_days[]" :value="day">
                    </template>

                    {{-- Step 1: Dates --}}
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10">
                            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                <x-icon name="clock" class="w-5 h-5 text-indigo-400" />
                                {{ __('planner.step_dates') }}
                            </h2>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">{{ __('planner.start_date') }}</label>
                                    <input type="date" x-model="formData.start_date" name="start_date"
                                        class="w-full bg-white/5 border border-white/20 rounded-xl px-4 py-3 text-white focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 transition"
                                        min="{{ now()->format('Y-m-d') }}" required>
                                    @error('start_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-2">{{ __('planner.exam_date') }}</label>
                                    <input type="date" x-model="formData.exam_date" name="exam_date"
                                        class="w-full bg-white/5 border border-white/20 rounded-xl px-4 py-3 text-white focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 transition"
                                        required>
                                    @error('exam_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Stage summary --}}
                            <div class="mt-6 p-4 bg-indigo-500/10 rounded-xl border border-indigo-500/20">
                                <p class="text-sm text-indigo-300">
                                    <x-icon name="information-circle" class="w-4 h-4 inline" />
                                    {{ __('planner.stages_to_cover', ['count' => $stages->count()]) }} · {{ __('planner.total_est_time', ['time' => $stages->sum('estimated_study_minutes')]) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Preferred Days --}}
                    <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10">
                            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                <x-icon name="map" class="w-5 h-5 text-indigo-400" />
                                {{ __('planner.step_days') }}
                            </h2>

                            <div class="grid grid-cols-7 gap-2 sm:gap-3">
                                @foreach(['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                                    <button type="button" @click="toggleDay('{{ $day }}')"
                                        class="aspect-square rounded-2xl flex flex-col items-center justify-center text-sm font-medium transition-all duration-300 border-2"
                                        :class="isDaySelected('{{ $day }}')
                                            ? 'bg-indigo-500/20 border-indigo-500 text-indigo-300 shadow-lg shadow-indigo-500/10'
                                            : 'bg-white/5 border-white/10 text-slate-400 hover:border-white/30'">
                                        <span class="text-xs sm:text-sm font-bold">{{ __('planner.' . $day) }}</span>
                                    </button>
                                @endforeach
                            </div>
                            @error('preferred_days') <p class="text-red-400 text-xs mt-2">{{ $message }}</p> @enderror

                            <p class="text-sm text-slate-400 mt-4 text-center">
                                <span x-text="formData.preferred_days.length"></span> {{ __('planner.days_selected', ['count' => '']) }}
                            </p>
                        </div>
                    </div>

                    {{-- Step 3: Hours + Pace --}}
                    <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10 mb-6">
                            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                <x-icon name="clock" class="w-5 h-5 text-indigo-400" />
                                {{ __('planner.step_hours') }}
                            </h2>

                            <div class="max-w-xs mx-auto text-center">
                                <div class="text-5xl font-bold text-indigo-400 mb-4">
                                    <span x-text="formData.hours_per_day"></span>
                                    <span class="text-lg text-slate-400 font-normal">{{ __('planner.hrs') }}</span>
                                </div>
                                <input type="range" x-model="formData.hours_per_day" name="hours_per_day"
                                    min="0.5" max="8" step="0.5"
                                    class="w-full h-2 bg-white/10 rounded-full appearance-none cursor-pointer accent-indigo-500">
                                <div class="flex justify-between text-xs text-slate-500 mt-2">
                                    <span>0.5h</span><span>4h</span><span>8h</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10">
                            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                <x-icon name="trending-up" class="w-5 h-5 text-indigo-400" />
                                {{ __('planner.step_pace') }}
                            </h2>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                @foreach(['light', 'medium', 'intensive'] as $pace)
                                    <button type="button" @click="formData.pace = '{{ $pace }}'"
                                        class="p-5 rounded-2xl border-2 text-center transition-all duration-300"
                                        :class="formData.pace === '{{ $pace }}'
                                            ? 'border-indigo-500 bg-indigo-500/10 shadow-lg shadow-indigo-500/10'
                                            : 'border-white/10 bg-white/5 hover:border-white/30'">
                                        <div class="text-2xl mb-2">
                                            @if($pace === 'light') 🌿 @elseif($pace === 'medium') ⚡ @else 🔥 @endif
                                        </div>
                                        <p class="font-semibold text-white text-sm">{{ __('planner.pace_' . $pace) }}</p>
                                        <p class="text-xs text-slate-400 mt-1">{{ __('planner.pace_' . $pace . '_desc') }}</p>
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="pace" :value="formData.pace">
                        </div>
                    </div>

                    {{-- Step 4: Review --}}
                    <div x-show="step === 4" x-cloak x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10">
                            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                <x-icon name="check-circle" class="w-5 h-5 text-emerald-400" />
                                {{ __('planner.step_confirm') }}
                            </h2>

                            <div class="space-y-4">
                                <div class="flex justify-between py-3 border-b border-white/10">
                                    <span class="text-slate-400">{{ __('planner.start_date') }}</span>
                                    <span class="text-white font-medium" x-text="formData.start_date"></span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-white/10">
                                    <span class="text-slate-400">{{ __('planner.exam_date') }}</span>
                                    <span class="text-white font-medium" x-text="formData.exam_date"></span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-white/10">
                                    <span class="text-slate-400">{{ __('planner.preferred_days') }}</span>
                                    <span class="text-white font-medium" x-text="formData.preferred_days.join(', ').toUpperCase()"></span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-white/10">
                                    <span class="text-slate-400">{{ __('planner.hours_per_day') }}</span>
                                    <span class="text-white font-medium"><span x-text="formData.hours_per_day"></span> {{ __('planner.hrs') }}</span>
                                </div>
                                <div class="flex justify-between py-3">
                                    <span class="text-slate-400">{{ __('planner.pace') }}</span>
                                    <span class="text-white font-medium capitalize" x-text="formData.pace"></span>
                                </div>
                            </div>

                            <div class="mt-6 p-4 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                                <p class="text-sm text-emerald-300 flex items-center gap-2">
                                    <x-icon name="information-circle" class="w-4 h-4" />
                                    {{ __('planner.intelligent_schedule_msg', ['count' => $stages->count()]) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Navigation Buttons --}}
                    <div x-show="errorMessage" x-cloak class="mb-4 mt-4 text-red-400 text-sm font-medium text-center bg-red-400/10 border border-red-400/20 rounded-xl py-3 px-4">
                        <x-icon name="exclamation" class="w-4 h-4 inline mr-1" />
                        <span x-text="errorMessage"></span>
                    </div>

                    <div class="flex items-center justify-between mt-8">
                        <button type="button" x-show="step > 1" @click="step--; errorMessage = ''"
                            class="px-6 py-3 bg-white/5 hover:bg-white/10 text-slate-300 rounded-xl font-medium transition flex items-center gap-2">
                            <x-icon name="arrow-right" class="w-4 h-4 rotate-180" />
                            {{ __('planner.previous') }}
                        </button>
                        <div x-show="step <= 1"></div>

                        <button type="button" x-show="step < totalSteps" @click="nextStep()"
                            class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl font-medium transition shadow-lg shadow-indigo-500/25 flex items-center gap-2">
                            {{ __('planner.next') }}
                            <x-icon name="arrow-right" class="w-4 h-4" />
                        </button>

                        <button type="submit" x-show="step === totalSteps" x-cloak
                            class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-cyan-600 hover:from-emerald-600 hover:to-cyan-700 text-white rounded-xl font-semibold transition shadow-lg shadow-emerald-500/25 hover:scale-105 flex items-center gap-2">
                            <x-icon name="sparkles" class="w-5 h-5" />
                            {{ __('planner.generate_plan') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
