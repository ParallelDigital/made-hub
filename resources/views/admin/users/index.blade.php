@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold text-white">Users</h1>
    <div class="flex space-x-4">
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-semibold shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create New User
        </a>
        <a href="{{ route('admin.users.export', request()->query()) }}"
           class="inline-flex items-center px-4 py-2 bg-primary hover:bg-purple-400 text-white rounded-md text-sm font-semibold shadow-sm">
            Export CSV
        </a>
        <form action="{{ route('admin.create-member-accounts') }}" method="POST" id="usersSyncStripeForm">
            @csrf
            <button type="button" onclick="confirmUsersSyncStripe()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-semibold shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Sync Stripe Members
            </button>
        </form>
    </div>
</div>

<div class="bg-gray-900/50 border border-gray-700/50 rounded-lg p-6 mb-8">
    <form action="{{ route('admin.users.index') }}" method="GET" id="filterForm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Search -->
            <div>
                <label for="search" class="text-sm font-medium text-gray-400">Search Users</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3" 
                       placeholder="Name, email, or username...">
            </div>

            <!-- Role Filter -->
            <div>
                <label for="role" class="text-sm font-medium text-gray-400">Role</label>
                <div class="mt-2">
                    <x-custom-select 
                        name="role" 
                        id="role"
                        :options="collect($roles)->mapWithKeys(fn($role) => [$role => ucfirst($role)])->toArray()"
                        :selected="request('role')"
                        placeholder="All Roles" />
                </div>
            </div>

            <!-- Start Date -->
            <div>
                <label for="start_date" class="text-sm font-medium text-gray-400">Registration From</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                       class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
            </div>

            <!-- End Date -->
            <div>
                <label for="end_date" class="text-sm font-medium text-gray-400">Registration To</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" 
                       class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
            </div>

        </div>
        <div class="mt-6 pt-4 border-t border-gray-700/50 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-gray-400 hover:text-white">Clear Filters</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 focus:ring-offset-gray-900">
                Apply Filters
            </button>
        </div>
    </form>

    <script>
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            const formData = new FormData(this);
            const params = new URLSearchParams();
            
            for (let [key, value] of formData.entries()) {
                if (value && value.trim() !== '') {
                    params.append(key, value);
                }
            }
            
            window.location.href = this.action + '?' + params.toString();
            e.preventDefault();
        });
    </script>
    <script>
        function confirmUsersSyncStripe() {
            if (confirm('Set role to "subscriber" and membership to "member" for all users with active Stripe subscriptions?')) {
                const form = document.getElementById('usersSyncStripeForm');
                const button = form.querySelector('button');
                button.disabled = true;
                button.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Syncing...';
                form.submit();
            }
        }
    </script>
</div>

<div class="bg-gray-800 shadow rounded-lg border border-gray-700 overflow-hidden">
    <div class="p-4 border-b border-gray-700">
        <p class="text-sm text-gray-400">Showing {{ $users->total() }} users</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Membership</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Registered</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Created</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-700/50 cursor-pointer" onclick="window.location.href='{{ route('admin.users.edit', $user) }}'">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-purple-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">
                                            {{ strtoupper(substr($user->first_name ?: $user->name ?: $user->email, 0, 1)) }}{{ strtoupper(substr($user->last_name ?: '', 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">
                                        @if($user->first_name || $user->last_name)
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        @else
                                            {{ $user->name ?: $user->display_name }}
                                        @endif
                                    </div>
                                    @if($user->user_login)
                                        <div class="text-sm text-gray-400">@{{ $user->user_login }}</div>
                                    @endif
                                    @if($user->nickname && $user->nickname !== $user->user_login)
                                        <div class="text-xs text-gray-500">{{ $user->nickname }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-white">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($user->role === 'administrator') bg-red-100 text-red-800
                                @elseif($user->role === 'editor') bg-yellow-100 text-yellow-800
                                @elseif($user->role === 'wpamelia-customer') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('-', ' ', $user->role ?: 'subscriber')) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->hasActiveMembership())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Member
                                </span>
                                @if($user->membership)
                                    <div class="text-xs text-gray-500 mt-1">{{ $user->membership->name }}</div>
                                @endif
                            @elseif($user->stripe_subscription_id)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Subscriber
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Non-Member
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            @if($user->phone)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    {{ $user->phone }}
                                </div>
                            @else
                                <span class="text-gray-500 text-xs">Not provided</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            @if($user->user_registered)
                                {{ $user->user_registered->format('M j, Y') }}
                                <div class="text-xs text-gray-500">{{ $user->user_registered->format('g:i A') }}</div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            @if($user->created_at)
                                {{ $user->created_at->format('M j, Y') }}
                                <div class="text-xs text-gray-500">{{ $user->created_at->format('g:i A') }}</div>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-lg font-medium">No users found</div>
                            <div class="text-sm">Try adjusting your search criteria</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
