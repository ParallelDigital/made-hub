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
                    <option value="active" {{ $filter === 'active' ? 'selected' : '' }}>Active Passes</option>
                    <option value="expired" {{ $filter === 'expired' ? 'selected' : '' }}>Expired Passes</option>
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
            @if($filter === 'active')
                <button type="button" onclick="changeFilter('expired')" class="bg-orange-600 hover:bg-orange-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Show Expired Passes
                </button>
            @elseif($filter === 'expired')
                <button type="button" onclick="changeFilter('active')" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Show Active Passes
                </button>
            @endif
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Active Pass / Credits</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Expires</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Source</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700" id="passes-table">
                @forelse($users as $user)
                    @php
                        $hasActiveUnlimited = $user->hasActiveUnlimitedPass();
                        $totalCredits = $user->getNonMemberAvailableCredits();
                        
                        // Check new system first
                        $activeUnlimitedPass = null;
                        $firstCreditPass = null;
                        $passSource = 'Unknown';
                        $passExpiry = null;
                        
                        try {
                            $activeUnlimitedPass = $user->passes()->where('pass_type', 'unlimited')->where('expires_at', '>=', now()->toDateString())->orderBy('expires_at', 'desc')->first();
                            $firstCreditPass = $user->passes()->where('pass_type', 'credits')->where('expires_at', '>=', now()->toDateString())->orderBy('expires_at', 'asc')->first();
                        } catch (\Exception $e) {
                            // Fallback to old system
                        }
                        
                        // Determine source and expiry
                        if ($activeUnlimitedPass) {
                            $passSource = $activeUnlimitedPass->source;
                            $passExpiry = $activeUnlimitedPass->expires_at;
                        } elseif ($firstCreditPass) {
                            $passSource = $firstCreditPass->source;
                            $passExpiry = $firstCreditPass->expires_at;
                        } else {
                            // Check old system for fallback display
                            if ($hasActiveUnlimited && $user->unlimited_pass_expires_at) {
                                $passSource = 'legacy_system';
                                $passExpiry = $user->unlimited_pass_expires_at;
                            } elseif ($totalCredits > 0 && $user->credits_expires_at) {
                                $passSource = 'legacy_system';
                                $passExpiry = $user->credits_expires_at;
                            }
                        }
                    @endphp
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-sm font-medium text-white hover:text-primary hover:underline">{{ $user->name }}</a>
                                <div class="text-sm text-gray-400">{{ $user->email }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($hasActiveUnlimited && $activeUnlimitedPass)
                                <span class="font-semibold text-purple-300">Unlimited Pass</span>
                            @elseif($totalCredits > 0)
                                <span class="font-semibold text-blue-300">{{ $totalCredits }} Credits</span>
                            @else
                                <span class="text-gray-500">No Active Pass</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            @if($passExpiry)
                                {{ $passExpiry->format('M j, Y') }}
                            @else
                                <span class="text-gray-500">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                            {{ Str::title(str_replace('_', ' ', $passSource)) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                             @if($hasActiveUnlimited)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-800 text-green-100">Active</span>
                            @elseif($totalCredits > 0)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-800 text-green-100">Active</span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-800 text-red-100">Expired</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.class-passes.show', $user) }}" class="text-primary hover:text-purple-400">View</a>
                                <a href="{{ route('admin.class-passes.edit', $user) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">No users with class passes found.</td>
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
    document.getElementById('filter').value = 'active'; // Changed from 'all' to 'active'
    document.getElementById('sort_by').value = 'unlimited_pass_expires_at';
    document.getElementById('sort_order').value = 'desc';
    
    window.location.href = '{{ route("admin.class-passes.index") }}';
}

function changeFilter(newFilter) {
    const params = new URLSearchParams(window.location.search);
    params.set('filter', newFilter);
    
    // Preserve search if present
    const search = document.getElementById('search').value;
    if (search) params.set('search', search);
    
    window.location.href = '{{ route("admin.class-passes.index") }}?' + params.toString();
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
