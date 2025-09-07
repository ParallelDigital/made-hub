<x-guest-layout>
    <!-- Container -->
    <div class="min-h-[70vh] flex items-center justify-center py-8">
        <div class="w-full max-w-md">
            <!-- Brand / Header -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-semibold text-white">Welcome back</h1>
                <p class="text-gray-400 text-sm">Log in to access your dashboard</p>
            </div>

            <!-- Card -->
            <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-xl shadow-lg p-6">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" class="text-gray-200" />
                        <x-text-input id="email" class="block mt-1 w-full bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-500 focus:border-primary focus:ring-primary h-[30px]" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between">
                            <x-input-label for="password" :value="__('Password')" class="text-gray-200" />
                            @if (Route::has('password.request'))
                                <a class="text-xs text-primary hover:text-white" href="{{ route('password.request') }}">
                                    {{ __('Forgot password?') }}
                                </a>
                            @endif
                        </div>
                        <x-text-input id="password" class="block mt-1 w-full bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-500 focus:border-primary focus:ring-primary h-[30px]"
                                        type="password"
                                        name="password"
                                        required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-600 bg-gray-900 text-primary shadow-sm focus:ring-primary" name="remember">
                            <span class="ms-2 text-sm text-gray-300">{{ __('Remember me') }}</span>
                        </label>
                        @if (Route::has('register'))
                            <a class="text-xs text-gray-300 hover:text-white" href="{{ route('register') }}">
                                {{ __('Don\'t have an account? Sign up') }}
                            </a>
                        @endif
                    </div>

                    <!-- Submit -->
                    <x-primary-button class="w-full justify-center py-3">
                        {{ __('Log in') }}
                    </x-primary-button>
                </form>
            </div>

            <!-- Footer Links -->
            <div class="text-center mt-6 text-xs text-gray-500">
                <p>&copy; {{ date('Y') }} Made Running</p>
            </div>
        </div>
    </div>
</x-guest-layout>
