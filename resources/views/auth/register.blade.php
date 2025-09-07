<x-guest-layout>
    <div class="min-h-[70vh] flex items-center justify-center py-8">
        <div class="w-full max-w-md">
            <!-- Brand / Header -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-semibold text-white">Create your account</h1>
                <p class="text-gray-400 text-sm">Join classes, manage bookings, and more</p>
            </div>

            <!-- Card -->
            <div class="bg-gray-800/80 backdrop-blur-sm border border-gray-700 rounded-xl shadow-lg p-6">
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <!-- Name -->
                    <div>
                        <x-input-label for="name" :value="__('Name')" class="text-gray-200" />
                        <x-text-input id="name" class="block mt-1 w-full bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-500 focus:border-primary focus:ring-primary h-[30px]" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" class="text-gray-200" />
                        <x-text-input id="email" class="block mt-1 w-full bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-500 focus:border-primary focus:ring-primary h-[30px]" type="email" name="email" :value="old('email')" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" class="text-gray-200" />
                        <x-text-input id="password" class="block mt-1 w-full bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-500 focus:border-primary focus:ring-primary h-[30px]"
                                        type="password"
                                        name="password"
                                        required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-200" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full bg-gray-900 border-gray-700 text-gray-100 placeholder-gray-500 focus:border-primary focus:ring-primary h-[30px]"
                                        type="password"
                                        name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <!-- Submit -->
                    <x-primary-button class="w-full justify-center py-3">
                        {{ __('Register') }}
                    </x-primary-button>

                    <div class="text-center text-xs text-gray-400">
                        <a class="hover:text-white" href="{{ route('login') }}">{{ __('Already registered? Log in') }}</a>
                    </div>
                </form>
            </div>

            <!-- Footer Links -->
            <div class="text-center mt-6 text-xs text-gray-500">
                <p>&copy; {{ date('Y') }} Made Running</p>
            </div>
        </div>
    </div>
</x-guest-layout>
