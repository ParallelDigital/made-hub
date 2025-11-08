@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Modal-style card -->
        <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700 p-8">
            <!-- Icon -->
            <div class="flex justify-center mb-4">
                <div class="bg-primary/10 rounded-full p-3">
                    <svg class="w-12 h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
            </div>

            <!-- Title and description -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-white mb-2">Phone Number Required</h2>
                <p class="text-gray-400 text-sm">
                    Please provide your phone number to continue using the platform. This helps us keep you informed about your bookings and classes.
                </p>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('complete-phone.store') }}" class="space-y-6">
                @csrf

                <!-- Phone input -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="tel" 
                        name="phone" 
                        id="phone" 
                        required
                        pattern="[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}"
                        placeholder="e.g., +1 (555) 123-4567"
                        value="{{ old('phone') }}"
                        class="w-full px-4 py-3 bg-gray-700 border @error('phone') border-red-500 @else border-gray-600 @enderror rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                    >
                    <p class="mt-2 text-xs text-gray-500">
                        Accepted formats: +1234567890, (123) 456-7890, 123-456-7890
                    </p>
                    
                    @error('phone')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit button -->
                <div>
                    <button 
                        type="submit" 
                        class="w-full bg-primary hover:bg-purple-400 text-black font-bold py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-gray-800"
                    >
                        Continue
                    </button>
                </div>

                <!-- Info text -->
                <div class="text-center">
                    <p class="text-xs text-gray-500">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                        Your phone number will be kept secure and private
                    </p>
                </div>
            </form>

            <!-- Note: Cannot proceed without phone -->
            <div class="mt-6 p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm text-yellow-200">
                        You must provide a phone number to access your account and book classes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Client-side validation enhancement -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    
    phoneInput.addEventListener('input', function(e) {
        // Remove validation message as user types
        const errorEl = this.parentElement.querySelector('.text-red-500');
        if (errorEl) {
            this.classList.remove('border-red-500');
            this.classList.add('border-gray-600');
        }
    });
    
    phoneInput.addEventListener('blur', function(e) {
        const value = this.value.trim();
        const pattern = /^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/;
        
        if (value && !pattern.test(value)) {
            this.classList.add('border-red-500');
            this.classList.remove('border-gray-600');
        }
    });
});
</script>
@endsection
