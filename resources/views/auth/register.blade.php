<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 to-gray-800 p-4">
        <div class="w-full max-w-md">
            <!-- Logo/Brand -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">Create Account</h1>
                <p class="text-gray-400">Join us to get started with your fitness journey</p>
            </div>

            <!-- Card -->
            <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-xl shadow-xl overflow-hidden">
                <div class="p-8">
                    <form method="POST" action="{{ route('register') }}" class="space-y-6">
                        @csrf

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Full Name')" class="text-gray-300 text-sm font-medium mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 2a5 5 0 00-5 5v2a2 5 0 00-2 5v4a2 2 0 002 2h10a2 2 0 002-2v-4a5 5 0 00-2-5V7a5 5 0 00-5-5z" />
                                    </svg>
                                </div>
                                <x-text-input 
                                    id="name" 
                                    name="name" 
                                    type="text" 
                                    class="pl-10 w-full h-10 bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg" 
                                    :value="old('name')" 
                                    required 
                                    autofocus 
                                    autocomplete="name" 
                                />
                            </div>
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>

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
                                    autocomplete="email" 
                                />
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>

                        <!-- Password -->
                        <div>
                            <x-input-label for="password" :value="__('Password')" class="text-gray-300 text-sm font-medium mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <x-text-input 
                                    id="password" 
                                    name="password" 
                                    type="password" 
                                    class="pl-10 w-full h-10 bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg" 
                                    required 
                                    autocomplete="new-password" 
                                />
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-300 text-sm font-medium mb-1" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <x-text-input 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    type="password" 
                                    class="pl-10 w-full h-10 bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg" 
                                    required 
                                    autocomplete="new-password" 
                                />
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                        </div>

                        <!-- Password Requirements -->
                        <div class="text-xs text-gray-400 mt-2">
                            <p class="font-medium mb-1">Password must contain:</p>
                            <ul class="list-disc list-inside space-y-0.5">
                                <li :class="{ 'text-green-400': $el.querySelector('input').value.length >= 8 }">At least 8 characters</li>
                                <li :class="{ 'text-green-400': /[A-Z]/.test($el.querySelector('input').value) }">At least one uppercase letter</li>
                                <li :class="{ 'text-green-400': /[a-z]/.test($el.querySelector('input').value) }">At least one lowercase letter</li>
                                <li :class="{ 'text-green-400': /[0-9]/.test($el.querySelector('input').value) }">At least one number</li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors h-10">
                                {{ __('Create Account') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Login Link -->
                <div class="px-8 py-4 bg-gray-800/50 border-t border-gray-700 text-center">
                    <p class="text-sm text-gray-400">
                        {{ __('Already have an account?') }}
                        <a href="{{ route('login') }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
                            {{ __('Sign in') }}
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
