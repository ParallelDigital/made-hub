@extends('layouts.admin')

@section('title', 'Class Passes Management')

@section('content')
<div class="bg-gray-900 rounded-lg border border-gray-700 overflow-hidden">
    <!-- Header -->
    <div class="p-4 sm:p-6 border-b border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white">Class Passes Management</h1>
                <p class="text-gray-400 text-sm mt-1">Manage unlimited passes and credit packages</p>
            </div>
            <a href="{{ route('admin.class-passes.create') }}" class="bg-primary hover:bg-purple-400 text-black px-4 py-2 rounded-md font-medium transition-colors">
                Add Class Pass
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-4 sm:p-6 border-b border-gray-700 bg-gray-800/50">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-300 mb-1">Search Users</label>
                <input type="text" id="search" value="{{ $search }}" placeholder="Name or email..." 
                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            <!-- Filter -->
            <div>
                <label for="filter" class="block text-sm font-medium text-gray-300 mb-1">Filter</label>
                <select id="filter" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Passes</option>
                    <option value="active_unlimited" {{ $filter === 'active_unlimited' ? 'selected' : '' }}>Active Unlimited</option>
                    <option value="expired_unlimited" {{ $filter === 'expired_unlimited' ? 'selected' : '' }}>Expired Unlimited</option>
                    <option value="active_credits" {{ $filter === 'active_credits' ? 'selected' : '' }}>Active Credits</option>
                    <option value="expired_credits" {{ $filter === 'expired_credits' ? 'selected' : '' }}>Expired Credits</option>
                </select>
            </div>

            <!-- Sort By -->
            <div>
                <label for="sort_by" class="block text-sm font-medium text-gray-300 mb-1">Sort By</label>
                <select id="sort_by" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="unlimited_pass_expires_at" {{ $sortBy === 'unlimited_pass_expires_at' ? 'selected' : '' }}>Unlimited Pass Expiry</option>
                    <option value="credits_expires_at" {{ $sortBy === 'credits_expires_at' ? 'selected' : '' }}>Credits Expiry</option>
                    <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Name</option>
                    <option value="email" {{ $sortBy === 'email' ? 'selected' : '' }}>Email</option>
                    <option value="credits" {{ $sortBy === 'credits' ? 'selected' : '' }}>Credits Amount</option>
                </select>
            </div>

            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-300 mb-1">Order</label>
                <select id="sort_order" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="desc" {{ $sortOrder === 'desc' ? 'selected' : '' }}>Newest First</option>
                    <option value="asc" {{ $sortOrder === 'asc' ? 'selected' : '' }}>Oldest First</option>
                </select>
            </div>
        </div>

        <!-- Filter Actions -->
        <div class="flex gap-2 items-end mt-4">
            <button type="button" onclick="applyFilters()" class="bg-primary hover:bg-purple-400 text-black px-4 py-2 rounded-md font-medium transition-colors">
                Apply Filters
            </button>
            <button type="button" onclick="clearFilters()" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                Clear
            </button>
            <button type="button" onclick="refreshPasses()" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors" onclick="sortTable('name')">
                        User
                        @if($sortBy === 'name')
                            <span class="ml-1">{{ $sortOrder === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors" onclick="sortTable('unlimited_pass_expires_at')">
                        Unlimited Pass
                        @if($sortBy === 'unlimited_pass_expires_at')
                            <span class="ml-1">{{ $sortOrder === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors" onclick="sortTable('credits')">
                        Credits
                        @if($sortBy === 'credits')
                            <span class="ml-1">{{ $sortOrder === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors" onclick="sortTable('credits_expires_at')">
                        Credits Expiry
                        @if($sortBy === 'credits_expires_at')
                            <span class="ml-1">{{ $sortOrder === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700" id="passes-table">
                @forelse($users as $user)
                    @php
                        $hasActiveUnlimited = $user->hasActiveUnlimitedPass();
                        $hasActiveCredits = $user->getNonMemberAvailableCredits() > 0;
                        $unlimitedExpiry = $user->unlimited_pass_expires_at;
                        $creditsExpiry = $user->credits_expires_at;
                    @endphp
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <div class="text-sm font-medium text-white">{{ $user->name }}</div>
                                <div class="text-sm text-gray-400">{{ $user->email }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($unlimitedExpiry)
                                <div class="flex flex-col">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $hasActiveUnlimited ? 'bg-green-800 text-green-100' : 'bg-red-800 text-red-100' }}">
                                        {{ $hasActiveUnlimited ? 'Active' : 'Expired' }}
                                    </span>
                                    <span class="text-xs text-gray-400 mt-1">
                                        Until {{ $unlimitedExpiry->format('M j, Y') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-500">No unlimited pass</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($user->credits > 0)
                                <div class="flex flex-col">
                                    <span class="text-white font-medium">{{ $user->credits }} credits</span>
                                    <span class="text-xs {{ $hasActiveCredits ? 'text-green-400' : 'text-red-400' }}">
                                        {{ $hasActiveCredits ? 'Available' : 'Expired' }}
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-500">No credits</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            @if($creditsExpiry)
                                {{ $creditsExpiry->format('M j, Y') }}
                            @else
                                <span class="text-gray-500">No expiry</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($hasActiveUnlimited)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-800 text-purple-100">
                                    Unlimited Active
                                </span>
                            @elseif($hasActiveCredits)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-800 text-blue-100">
                                    Credits Active
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-700 text-gray-300">
                                    No Active Pass
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.class-passes.show', $user) }}" class="text-primary hover:text-purple-400">
                                    View
                                </a>
                                <a href="{{ route('admin.class-passes.edit', $user) }}" class="text-blue-400 hover:text-blue-300">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">No class passes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="p-4 border-t border-gray-700">
        {{ $users->links() }}
    </div>
</div>

<script>
function refreshPasses() {
    // Show loading state
    const refreshBtn = document.querySelector('button[onclick="refreshPasses()"]');
    const originalText = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<svg class="w-4 h-4 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Refreshing...';
    refreshBtn.disabled = true;

    // Reload the page to get latest passes
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

function applyFilters() {
    const search = document.getElementById('search').value;
    const filter = document.getElementById('filter').value;
    const sortBy = document.getElementById('sort_by').value;
    const sortOrder = document.getElementById('sort_order').value;

    const params = new URLSearchParams();

    if (search) params.set('search', search);
    if (filter) params.set('filter', filter);
    if (sortBy) params.set('sort_by', sortBy);
    if (sortOrder) params.set('sort_order', sortOrder);

    window.location.href = '{{ route("admin.class-passes.index") }}?' + params.toString();
}

function clearFilters() {
    document.getElementById('search').value = '';
    document.getElementById('filter').value = 'all';
    document.getElementById('sort_by').value = 'unlimited_pass_expires_at';
    document.getElementById('sort_order').value = 'desc';
    
    window.location.href = '{{ route("admin.class-passes.index") }}';
}

function sortTable(column) {
    const currentSortBy = '{{ $sortBy }}';
    const currentSortOrder = '{{ $sortOrder }}';

    let newSortOrder = 'asc';

    // If clicking the same column, toggle the order
    if (currentSortBy === column) {
        newSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
    }

    const params = new URLSearchParams(window.location.search);
    params.set('sort_by', column);
    params.set('sort_order', newSortOrder);

    // Preserve other filters
    const search = document.getElementById('search').value;
    const filter = document.getElementById('filter').value;
    if (search) params.set('search', search);
    if (filter) params.set('filter', filter);

    window.location.href = '{{ route("admin.class-passes.index") }}?' + params.toString();
}

// Auto-apply filters on Enter key
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});
</script>
@endsection
