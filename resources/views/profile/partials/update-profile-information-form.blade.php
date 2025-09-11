<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            :value="old('name', $user->name)" 
                            required 
                            autofocus 
                            autocomplete="name" 
                        />
                    </div>
                    <x-input-error class="mt-1" :messages="$errors->get('name')" />
                </div>

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
                            :value="old('email', $user->email)" 
                            required 
                            autocomplete="username" 
                        />
                    </div>
                    <x-input-error class="mt-1" :messages="$errors->get('email')" />

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-2 p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                            <p class="text-sm text-yellow-400">
                                {{ __('Your email address is unverified.') }}
                                <button 
                                    form="send-verification" 
                                    class="ml-1 underline text-yellow-300 hover:text-white transition-colors"
                                >
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 text-sm text-green-400">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-700">
            @if (session('status') === 'profile-updated')
                <div class="mr-4 flex items-center">
                    <svg class="h-5 w-5 text-green-500 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-sm text-green-400">{{ __('Saved') }}</span>
                </div>
            @endif
            
            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('Save Changes') }}
            </x-primary-button>
        </div>
    </form>
</section>
