<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4 flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-slate-700 bg-slate-900/50 text-cyan-500 shadow-sm focus:ring-cyan-500 focus:ring-offset-slate-900"
                    name="remember">
                <span class="ms-2 text-sm text-slate-400">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-emerald-400 hover:text-emerald-300 hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-cyan-500 rounded-md"
                    href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="mt-8">
            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
            <div class="mt-4 text-center text-sm text-slate-400">
                Don't have an account?
                <a href="{{ route('register') }}"
                    class="text-cyan-400 hover:text-cyan-300 hover:underline transition-colors">Sign up</a>
            </div>
        </div>
    </form>
</x-guest-layout>