@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-white">Create New User</h1>
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-sm font-semibold shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Users
        </a>
    </div>

    <div class="bg-gray-900/50 border border-gray-700/50 rounded-lg p-6">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="space-y-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium text-white mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-400">Full Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                   class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3"
                                   placeholder="John Doe" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-400">Email Address *</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3"
                                   placeholder="john@example.com" required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Names -->
                <div>
                    <h3 class="text-lg font-medium text-white mb-4">Additional Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-400">First Name</label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                                   class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3"
                                   placeholder="John">
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-400">Last Name</label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                                   class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3"
                                   placeholder="Doe">
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Account Details -->
                <div>
                    <h3 class="text-lg font-medium text-white mb-4">Account Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-400">Password *</label>
                            <input type="password" name="password" id="password"
                                   class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3"
                                   placeholder="••••••••" required>
                            @error('password')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-400">Role *</label>
                            <div class="mt-2">
                                <select name="role" id="role"
                                        class="block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3" required>
                                    <option value="">Select a role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('-', ' ', $role)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('role')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Optional Fields -->
                <div>
                    <h3 class="text-lg font-medium text-white mb-4">Optional Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="user_login" class="block text-sm font-medium text-gray-400">Username</label>
                            <input type="text" name="user_login" id="user_login" value="{{ old('user_login') }}"
                                   class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3"
                                   placeholder="johndoe">
                            @error('user_login')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nickname" class="block text-sm font-medium text-gray-400">Nickname</label>
                            <input type="text" name="nickname" id="nickname" value="{{ old('nickname') }}"
                                   class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3"
                                   placeholder="Johnny">
                            @error('nickname')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="pt-6 border-t border-gray-700/50 flex items-center justify-end space-x-4">
                    <a href="{{ route('admin.users.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-sm font-semibold shadow-sm">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 focus:ring-offset-gray-900">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Create User
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
/* Custom select styling to match dark theme */
select {
    color: white !important;
}

select option {
    background-color: #1f2937;
    color: white;
}

/* Ensure form elements have proper styling */
input:focus, select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}
</style>
@endsection
