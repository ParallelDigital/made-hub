@extends('layouts.admin')

@section('title', 'Bookings')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Bookings</h1>
    <button onclick="refreshBookings()" class="bg-primary hover:bg-purple-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Refresh
    </button>
</div>

<!-- Filters Section -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-6">
    <div class="px-4 py-4">
        <div class="flex flex-wrap gap-4 items-center">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-300 mb-1">Search</label>
                <input type="text"
                       name="search"
                       id="search"
                       value="{{ request('search') }}"
                       placeholder="Search by member, email, or class..."
                       class="w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            <!-- Sort By -->
            <div class="flex-1 min-w-[150px]">
                <label for="sort_by" class="block text-sm font-medium text-gray-300 mb-1">Sort By</label>
                <select name="sort_by" id="sort_by" class="w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="booked_at" {{ request('sort_by', 'booked_at') === 'booked_at' ? 'selected' : '' }}>Booked Date</option>
                    <option value="user_name" {{ request('sort_by') === 'user_name' ? 'selected' : '' }}>Member Name</option>
                    <option value="class_name" {{ request('sort_by') === 'class_name' ? 'selected' : '' }}>Class Name</option>
                    <option value="status" {{ request('sort_by') === 'status' ? 'selected' : '' }}>Status</option>
                </select>
            </div>

            <!-- Sort Order -->
            <div class="flex-1 min-w-[100px]">
                <label for="sort_order" class="block text-sm font-medium text-gray-300 mb-1">Order</label>
                <select name="sort_order" id="sort_order" class="w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>Descending</option>
                    <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Ascending</option>
                </select>
            </div>

            <!-- Filter Actions -->
            <div class="flex gap-2 items-end">
                <button type="button" onclick="applyFilters()" class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Apply
                </button>
                <a href="{{ route('admin.bookings.index') }}" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Reset
                </a>
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-800 shadow rounded-lg border border-gray-700 overflow-hidden">
    <div class="p-4 border-b border-gray-700 flex justify-between items-center">
        <p class="text-sm text-gray-400">Showing {{ $bookings->total() }} bookings</p>
        <div class="text-xs text-gray-500">
            Last updated: <span id="last-updated">{{ now()->format('g:i A') }}</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors" onclick="sortTable('booked_at')">
                        Booked At
                        @if(request('sort_by', 'booked_at') === 'booked_at')
                            <span class="ml-1">
                                @if(request('sort_order', 'desc') === 'asc')
                                    ↑
                                @else
                                    ↓
                                @endif
                            </span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors" onclick="sortTable('user_name')">
                        Member
                        @if(request('sort_by') === 'user_name')
                            <span class="ml-1">
                                @if(request('sort_order') === 'asc')
                                    ↑
                                @else
                                    ↓
                                @endif
                            </span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors" onclick="sortTable('class_name')">
                        Class
                        @if(request('sort_by') === 'class_name')
                            <span class="ml-1">
                                @if(request('sort_order') === 'asc')
                                    ↑
                                @else
                                    ↓
                                @endif
                            </span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors" onclick="sortTable('status')">
                        Payment
                        @if(request('sort_by') === 'status')
                            <span class="ml-1">
                                @if(request('sort_order') === 'asc')
                                    ↑
                                @else
                                    ↓
                                @endif
                            </span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors" onclick="sortTable('status')">
                        Status
                        @if(request('sort_by') === 'status')
                            <span class="ml-1">
                                @if(request('sort_order') === 'asc')
                                    ↑
                                @else
                                    ↓
                                @endif
                            </span>
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700" id="bookings-table">
                @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-700/50 cursor-pointer" onclick="window.location.href='{{ route('admin.bookings.show', $booking) }}'">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ optional($booking->booked_at)->format('M j, Y g:i A') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $booking->user->name ?? 'Unknown' }} <span class="text-gray-400">({{ $booking->user->email ?? 'no-email' }})</span></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a class="text-primary hover:text-purple-400" href="{{ route('admin.classes.show', $booking->fitnessClass) }}" onclick="event.stopPropagation()">{{ $booking->fitnessClass->name ?? 'Class #' . $booking->fitness_class_id }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($booking->stripe_session_id)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-800 text-green-100">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    Stripe
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-800 text-blue-100">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    Credits
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $booking->status === 'confirmed' ? 'bg-green-800 text-green-100' : 
                                   ($booking->status === 'cancelled' ? 'bg-red-800 text-red-100' : 'bg-yellow-800 text-yellow-100') }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400">No bookings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-700">
        {{ $bookings->links() }}
    </div>
</div>

<script>
function refreshBookings() {
    // Show loading state
    const refreshBtn = document.querySelector('button[onclick="refreshBookings()"]');
    const originalText = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<svg class="w-4 h-4 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Refreshing...';
    refreshBtn.disabled = true;

    // Reload the page to get latest bookings
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

function applyFilters() {
    const search = document.getElementById('search').value;
    const sortBy = document.getElementById('sort_by').value;
    const sortOrder = document.getElementById('sort_order').value;

    const params = new URLSearchParams(window.location.search);

    if (search) {
        params.set('search', search);
    } else {
        params.delete('search');
    }

    if (sortBy) {
        params.set('sort_by', sortBy);
    } else {
        params.delete('sort_by');
    }

    if (sortOrder) {
        params.set('sort_order', sortOrder);
    } else {
        params.delete('sort_order');
    }

    window.location.href = '{{ route("admin.bookings.index") }}?' + params.toString();
}

function sortTable(column) {
    const currentSortBy = '{{ request("sort_by", "booked_at") }}';
    const currentSortOrder = '{{ request("sort_order", "desc") }}';

    let newSortOrder = 'asc';

    // If clicking the same column, toggle the order
    if (currentSortBy === column) {
        newSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
    }

    const params = new URLSearchParams(window.location.search);

    params.set('sort_by', column);
    params.set('sort_order', newSortOrder);

    // Preserve search if exists
    const search = document.getElementById('search').value;
    if (search) {
        params.set('search', search);
    }

    window.location.href = '{{ route("admin.bookings.index") }}?' + params.toString();
}

// Auto-refresh every 30 seconds
setInterval(() => {
    // Update timestamp
    document.getElementById('last-updated').textContent = new Date().toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });

    // Optionally auto-refresh (uncomment if desired)
    // window.location.reload();
}, 30000);
</script>
@endsection
