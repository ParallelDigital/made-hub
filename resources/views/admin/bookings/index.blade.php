@extends('admin.layout')

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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Booked At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700" id="bookings-table">
                @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ optional($booking->booked_at)->format('M j, Y g:i A') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $booking->user->name ?? 'Unknown' }} <span class="text-gray-400">({{ $booking->user->email ?? 'no-email' }})</span></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a class="text-primary hover:text-purple-400" href="{{ route('admin.classes.show', $booking->fitnessClass) }}">{{ $booking->fitnessClass->name ?? 'Class #' . $booking->fitness_class_id }}</a>
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
