<form method="post" action="{{ route('password.update') }}" class="space-y-6">
    @csrf
    @method('put')

    <div class="space-y-4">
        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="text-gray-300 text-sm font-medium mb-1" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <x-text-input 
                    id="update_password_current_password" 
                    name="current_password" 
                    type="password" 
                    class="pl-10 w-full h-10 bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg" 
                    autocomplete="current-password" 
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" class="text-gray-300 text-sm font-medium mb-1" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <x-text-input 
                    id="update_password_password" 
                    name="password" 
                    type="password" 
                    class="pl-10 w-full h-10 bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg" 
                    autocomplete="new-password" 
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm New Password')" class="text-gray-300 text-sm font-medium mb-1" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <x-text-input 
                    id="update_password_password_confirmation" 
                    name="password_confirmation" 
                    type="password" 
                    class="pl-10 w-full h-10 bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg" 
                    autocomplete="new-password" 
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
        </div>

        <!-- Password requirements -->
        <div class="mt-2 text-xs text-gray-400">
            <p class="font-medium mb-1">Password requirements:</p>
            <ul class="list-disc list-inside space-y-0.5">
                <li :class="{ 'text-green-400': $el.querySelector('input').value.length >= 8 }">At least 8 characters</li>
                <li :class="{ 'text-green-400': /[A-Z]/.test($el.querySelector('input').value) }">At least one uppercase letter</li>
                <li :class="{ 'text-green-400': /[a-z]/.test($el.querySelector('input').value) }">At least one lowercase letter</li>
                <li :class="{ 'text-green-400': /[0-9]/.test($el.querySelector('input').value) }">At least one number</li>
            </ul>
        </div>
    </div>

    <div class="flex items-center justify-end pt-4 border-t border-gray-700">
        @if (session('status') === 'password-updated')
            <div class="mr-4 flex items-center">
                <svg class="h-5 w-5 text-green-500 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-sm text-green-400">{{ __('Password updated') }}</span>
            </div>
        @endif
        
        <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            {{ __('Update Password') }}
        </x-primary-button>
    </div>
</form>
