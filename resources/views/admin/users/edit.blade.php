@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-white mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Edit User</h1>
        </div>
    </div>

    <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-400">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-400">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-400">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                           pattern="[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}"
                           placeholder="e.g., +1 (555) 123-4567"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
                    <p class="mt-1 text-xs text-gray-500">Accepts: +1234567890, (123) 456-7890, 123-456-7890</p>
                    @error('phone')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- First Name -->
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-400">First Name</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-400">Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div>
                    <label for="user_login" class="block text-sm font-medium text-gray-400">Username</label>
                    <input type="text" name="user_login" id="user_login" value="{{ old('user_login', $user->user_login) }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
                    @error('user_login')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-400">Role</label>
                    <div class="mt-1">
                        <x-custom-select 
                            name="role" 
                            id="role"
                            :options="collect($roles)->mapWithKeys(fn($role) => [$role => ucfirst($role)])->toArray()"
                            :selected="old('role', $user->role)"
                            placeholder="Select Role"
                            required />
                    </div>
                </div>
                @error('role')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror

                <!-- Nickname -->
                <div class="md:col-span-2">
                    <label for="nickname" class="block text-sm font-medium text-gray-400">Nickname</label>
                    <input type="text" name="nickname" id="nickname" value="{{ old('nickname', $user->nickname) }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
                    @error('nickname')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 focus:ring-offset-gray-900">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 focus:ring-offset-gray-900">
                    Update User
                </button>
            </div>
        </form>
    </div>

    <!-- User Information Card -->
    <div class="mt-6 bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">User Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-400">User ID:</span>
                <span class="text-white ml-2">{{ $user->id }}</span>
            </div>
            <div>
                <span class="text-gray-400">QR Code:</span>
                <div class="mt-2">
                    <img src="{{ route('user.qr-code', $user) }}"
                         alt="User QR Code"
                         class="w-24 h-24 border border-gray-600 rounded">
                    <div class="mt-1">
                        <span class="text-white font-mono text-sm">{{ $user->qr_code }}</span>
                    </div>
                </div>
            </div>
            <div>
                <span class="text-gray-400">Registered:</span>
                <span class="text-white ml-2">
                    @if($user->user_registered)
                        {{ $user->user_registered->format('M j, Y g:i A') }}
                    @else
                        {{ $user->created_at->format('M j, Y g:i A') }}
                    @endif
                </span>
            </div>
            <div>
                <span class="text-gray-400">Last Login:</span>
                <span class="text-white ml-2">{{ $user->last_login ? $user->last_login->format('M j, Y g:i A') : 'Never' }}</span>
            </div>
            <div>
                <span class="text-gray-400">Legacy Credits:</span>
                <span class="text-white ml-2">{{ $user->credits ?? 0 }}</span>
            </div>
            <div>
                <span class="text-gray-400">Monthly Credits:</span>
                <span class="text-white ml-2">{{ $user->monthly_credits ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Credits Management -->
    <div class="mt-6 bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Credits Management</h3>

        @if(session('success'))
            <div class="mb-4 p-3 rounded border border-green-700 bg-green-900/30 text-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 rounded border border-red-700 bg-red-900/30 text-red-300 text-sm">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.credits.add', $user) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            @csrf

            <div>
                <label for="credit_type" class="block text-sm font-medium text-gray-400">Credit Type</label>
                <select id="credit_type" name="credit_type" class="mt-1 block w-full bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
                    <option value="legacy">Legacy Credits (one-off)</option>
                    <option value="monthly">Monthly Credits (membership top-up)</option>
                </select>
            </div>

            <div>
                <label for="amount" class="block text-sm font-medium text-gray-400">Amount</label>
                <input type="number" id="amount" name="amount" min="1" max="1000" value="1" class="mt-1 block w-full bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
            </div>

            <div class="md:col-span-3">
                <label for="note" class="block text-sm font-medium text-gray-400">Note (optional)</label>
                <input type="text" id="note" name="note" maxlength="500" placeholder="Reason or internal note (optional)" class="mt-1 block w-full bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
            </div>

            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-purple-400 text-white rounded-md text-sm font-semibold shadow-sm">
                    Add Credits
                </button>
            </div>
        </form>
        <p class="mt-3 text-xs text-gray-400">Tip: Use Legacy Credits for ad-hoc bookings without a membership. Use Monthly Credits to top-up members for the current month.</p>
    </div>
</div>
@endsection
