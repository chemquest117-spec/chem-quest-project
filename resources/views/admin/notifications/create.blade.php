<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight flex items-center gap-2">
            <x-icon name="bell" class="w-6 h-6 text-cyan-400" />
            {{ $header }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-slate-800 border border-white/10 overflow-hidden shadow-2xl rounded-2xl">
                <div class="p-6">
                    <p class="text-sm text-slate-400 mb-6">
                        Send automated push notifications and in-app alerts directly to your students. 
                        Users with the mobile app or browser notifications enabled will receive a native push alert.
                    </p>

                    <form action="{{ route('admin.notifications.store') }}" method="POST" x-data="{ target: 'all' }">
                        @csrf

                        <!-- Target Audience -->
                        <div class="mb-6">
                            <label class="block font-medium text-sm text-slate-300 mb-2">Target Audience</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="target" value="all" x-model="target" class="hidden peer">
                                    <div class="px-4 py-3 rounded-lg border border-white/10 bg-slate-900/50 peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 transition-colors">
                                        <div class="flex items-center gap-2 text-white font-medium mb-1">
                                            <x-icon name="user-group" class="w-4 h-4 text-cyan-400" /> All Students
                                        </div>
                                        <p class="text-xs text-slate-400">{{ __('navigation.broadcast') }} to everyone</p>
                                    </div>
                                </label>
                                
                                <label class="cursor-pointer">
                                    <input type="radio" name="target" value="inactive" x-model="target" class="hidden peer">
                                    <div class="px-4 py-3 rounded-lg border border-white/10 bg-slate-900/50 peer-checked:border-amber-500 peer-checked:bg-amber-500/10 transition-colors">
                                        <div class="flex items-center gap-2 text-white font-medium mb-1">
                                            <x-icon name="clock" class="w-4 h-4 text-amber-400" /> Inactive Students
                                        </div>
                                        <p class="text-xs text-slate-400">No login in the past 7 days</p>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="target" value="specific" x-model="target" class="hidden peer">
                                    <div class="px-4 py-3 rounded-lg border border-white/10 bg-slate-900/50 peer-checked:border-purple-500 peer-checked:bg-purple-500/10 transition-colors">
                                        <div class="flex items-center gap-2 text-white font-medium mb-1">
                                            <x-icon name="user" class="w-4 h-4 text-purple-400" /> Specific Students
                                        </div>
                                        <p class="text-xs text-slate-400">Select specific users manually</p>
                                    </div>
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('target')" class="mt-2" />
                        </div>

                        <!-- Specific User Selection -->
                        <div class="mb-6 bg-purple-500/5 border border-purple-500/20 rounded-xl p-4" x-show="target === 'specific'" x-cloak x-transition>
                            <label for="user_ids" class="block font-medium text-sm text-slate-300 mb-2">Select Students</label>
                            <select name="user_ids[]" id="user_ids" multiple class="w-full bg-slate-900 border-white/10 rounded-lg text-white shadow-sm focus:border-cyan-500 focus:ring-cyan-500 h-32">
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-400 mt-2">Hold Ctrl/Cmd to select multiple students.</p>
                            <x-input-error :messages="$errors->get('user_ids')" class="mt-2" />
                        </div>

                        <!-- Notification Type -->
                        <div class="mb-6">
                            <label for="type" class="block font-medium text-sm text-slate-300 mb-2">Notification Type / Style</label>
                            <select name="type" id="type" required class="w-full bg-slate-900/50 border-white/10 rounded-lg text-white shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                <option value="info">Info (Blue)</option>
                                <option value="success">Success (Green)</option>
                                <option value="warning">Warning / Action Required (Amber)</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- English Content -->
                            <div class="bg-slate-900/40 p-4 rounded-xl border border-white/5">
                                <div class="flex items-center gap-2 mb-4 text-cyan-400 font-bold border-b border-white/5 pb-2">
                                    <img src="https://flagcdn.com/w20/us.png" alt="EN" width="20"> English
                                </div>
                                <div class="mb-4">
                                    <label for="title_en" class="block font-medium text-sm text-slate-300 mb-1">Title</label>
                                    <input type="text" name="title_en" id="title_en" value="{{ old('title_en') }}" required class="w-full bg-slate-900/50 border-white/10 rounded-lg text-white shadow-sm focus:border-cyan-500 focus:ring-cyan-500">
                                    <x-input-error :messages="$errors->get('title_en')" class="mt-2" />
                                </div>
                                <div>
                                    <label for="message_en" class="block font-medium text-sm text-slate-300 mb-1">Message</label>
                                    <textarea name="message_en" id="message_en" rows="3" required class="w-full bg-slate-900/50 border-white/10 rounded-lg text-white shadow-sm focus:border-cyan-500 focus:ring-cyan-500">{{ old('message_en') }}</textarea>
                                    <x-input-error :messages="$errors->get('message_en')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Arabic Content -->
                            <div class="bg-slate-900/40 p-4 rounded-xl border border-white/5" dir="rtl">
                                <div class="flex items-center gap-2 mb-4 text-cyan-400 font-bold border-b border-white/5 pb-2">
                                    <img src="https://flagcdn.com/w20/eg.png" alt="AR" width="20"> العربية
                                </div>
                                <div class="mb-4">
                                    <label for="title_ar" class="block font-medium text-sm text-slate-300 mb-1">العنوان</label>
                                    <input type="text" name="title_ar" id="title_ar" value="{{ old('title_ar') }}" required class="w-full bg-slate-900/50 border-white/10 rounded-lg text-white shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-right">
                                    <x-input-error :messages="$errors->get('title_ar')" class="mt-2" />
                                </div>
                                <div>
                                    <label for="message_ar" class="block font-medium text-sm text-slate-300 mb-1">الرسالة</label>
                                    <textarea name="message_ar" id="message_ar" rows="3" required class="w-full bg-slate-900/50 border-white/10 rounded-lg text-white shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-right">{{ old('message_ar') }}</textarea>
                                    <x-input-error :messages="$errors->get('message_ar')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex items-center justify-end mt-6 pt-6 border-t border-white/5">
                            <button type="submit" class="bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-bold py-2 px-6 rounded-lg transition-colors flex items-center gap-2">
                                <x-icon name="paper-airplane" class="w-5 h-5" />
                                Send {{ __('navigation.broadcast') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
