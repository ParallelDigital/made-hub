<x-guest-layout>
    <div class="flex items-center justify-center bg-gradient-to-br from-gray-900 to-gray-800 p-4">
        <div class="w-full max-w-md">
            <!-- Logo/Brand -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Reset Password</h1>
                <p class="text-gray-400">Enter your email to receive a password reset link</p>
            </div>

            <!-- Card -->
            <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-xl shadow-xl overflow-hidden">
                <div class="p-8">
                    <!-- Session Status -->
                    <x-auth-session-status class="mb-6" :status="session('status')" />

                    <p class="text-sm text-gray-400 mb-6">
                        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.') }}
                    </p>

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                        @csrf

                        <!-- Email -->
                        <div>
                            <x-input-label for="email" :value="__('Email Address')" class="text-gray-300 text-sm font-medium mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                    </svg>
                                </div>
                                <x-text-input 
                                    id="email" 
                                    name="email" 
                                    type="email" 
                                    class="pl-10 w-full h-10 bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg" 
                                    :value="old('email')" 
                                    required 
                                    autofocus 
                                    autocomplete="email" 
                                />
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors h-10">
                                {{ __('Email Password Reset Link') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Back to Login Link -->
                <div class="px-8 py-4 bg-gray-800/50 border-t border-gray-700 text-center">
                    <p class="text-sm text-gray-400">
                        {{ __('Remember your password?') }}
                        <a href="{{ route('login') }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
                            {{ __('Back to login') }}
                        </a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-xs text-gray-500">
                <p>&copy; {{ date('Y') }} Made Running. All rights reserved.</p>
            </div>
        </div>
    </div>
</x-guest-layout>
