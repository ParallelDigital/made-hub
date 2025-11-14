@extends('layouts.admin')

@section('title', 'Class Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('admin.classes.index') }}" class="text-gray-400 hover:text-white mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $class->name }}</h1>
        </div>
        <div class="flex space-x-3">
            <button type="button" onclick="showSendRosterModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Send Roster Email
            </button>
            <a href="{{ route('admin.classes.edit', $class) }}" 
               class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                Edit Class
            </a>
            <button type="button" onclick="showCancelModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                Cancel Class
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Class Information -->
        <div class="lg:col-span-2">
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Class Information</h2>
                
                @if($class->recurring && isset($filterDate))
                    <div class="mb-4 p-3 bg-blue-900/30 border border-blue-700 rounded-lg">
                        <p class="text-sm text-blue-200">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Viewing bookings for: <strong>{{ \Carbon\Carbon::parse($filterDate)->format('l, F j, Y') }}</strong>
                        </p>
                        <a href="{{ route('admin.classes.show', $class) }}" class="text-xs text-blue-300 hover:text-blue-100 underline mt-1 inline-block">
                            View all dates →
                        </a>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Class Name</label>
                        <p class="mt-1 text-white">{{ $class->name }}</p>
                    </div>
                    
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Date</label>
                        <p class="mt-1 text-white">
                            @if(isset($filterDate))
                                {{ \Carbon\Carbon::parse($filterDate)->format('l, F j, Y') }}
                            @elseif($class->display_date ?? false)
                                {{ $class->display_date->format('l, F j, Y') }}
                            @else
                                {{ $class->class_date->format('l, F j, Y') }}
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Instructor</label>
                        <p class="mt-1 text-white">{{ $class->instructor->name ?? 'No Instructor Assigned' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Duration</label>
                        <p class="mt-1 text-white">{{ $class->duration }} minutes</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Time</label>
                        <p class="mt-1 text-white">{{ $class->start_time }} - {{ $class->end_time }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Price</label>
                        <p class="mt-1 text-white">£{{ number_format($class->price, 2) }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Max Spots</label>
                        <p class="mt-1 text-white">{{ $class->max_spots }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Status</label>
                        <p class="mt-1">
                            @if($class->active)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-800 text-green-100">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-800 text-red-100">
                                    Inactive
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
                
                @if($class->description)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-400">Description</label>
                        <p class="mt-1 text-white">{{ $class->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Stats & Bookings -->
        <div class="space-y-6">
            <!-- Stats Card -->
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Statistics</h3>
                @if($class->recurring && !isset($filterDate))
                    <div class="mb-3 text-xs text-gray-400 bg-gray-700 rounded px-2 py-1">
                        <span class="font-medium">Recurring Class:</span> Stats show all bookings across all dates
                    </div>
                @elseif($class->recurring && isset($filterDate))
                    <div class="mb-3 text-xs text-blue-300 bg-blue-900/30 border border-blue-700 rounded px-2 py-1">
                        <span class="font-medium">Filtered by date:</span> Stats for {{ \Carbon\Carbon::parse($filterDate)->format('M j, Y') }} only
                    </div>
                @endif
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Total Bookings</span>
                        <button type="button" onclick="openBookingsModal()" class="text-white font-medium hover:text-primary underline decoration-dotted cursor-pointer">
                            {{ $class->bookings->count() }}
                        </button>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Available Spots</span>
                        <span class="text-white font-medium">
                            @if($class->recurring && !isset($filterDate))
                                <span class="text-xs text-gray-400">Varies by date</span>
                            @else
                                {{ $class->max_spots - $class->bookings->where('status', 'confirmed')->count() }}
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Confirmed</span>
                        <span class="text-green-400 font-medium">{{ $class->bookings->where('status', 'confirmed')->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Cancelled</span>
                        <span class="text-red-400 font-medium">{{ $class->bookings->where('status', 'cancelled')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Waitlist</span>
                        <span class="text-yellow-400 font-medium">{{ $class->bookings->where('status', 'waitlist')->count() }}</span>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

<!-- Modal: Bookings List -->
<div id="bookingsModal" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
    <div class="absolute inset-0 bg-black bg-opacity-60" onclick="closeBookingsModal()"></div>
    <div class="relative bg-gray-800 border border-gray-700 rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">All Bookings</h3>
            <button type="button" class="text-gray-400 hover:text-white" onclick="closeBookingsModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-[60vh] overflow-auto p-5 space-y-3">
            <!-- All bookings -->
            @if($class->recurring && !isset($filterDate))
                @php
                    // Group bookings by date for recurring classes (only when not filtered)
                    $groupedBookings = $class->bookings->groupBy(function($booking) {
                        return $booking->booking_date ? $booking->booking_date->format('Y-m-d') : 'unknown';
                    })->sortKeysDesc();
                @endphp
                @forelse($groupedBookings as $date => $dateBookings)
                    <div class="mb-4">
                        <div class="text-sm font-semibold mb-2 border-b border-gray-600 pb-1 flex justify-between items-center">
                            <div>
                                <span class="text-primary">{{ $date !== 'unknown' ? \Carbon\Carbon::parse($date)->format('l, M j, Y') : 'Unknown Date' }}</span>
                                <span class="text-gray-400 font-normal">({{ $dateBookings->count() }} booking{{ $dateBookings->count() !== 1 ? 's' : '' }})</span>
                            </div>
                            @if($date !== 'unknown')
                                <a href="{{ route('admin.classes.show', ['class' => $class->id, 'date' => $date]) }}" 
                                   class="text-xs text-blue-400 hover:text-blue-300 underline"
                                   onclick="closeBookingsModal()">
                                    View this date →
                                </a>
                            @endif
                        </div>
                        @foreach($dateBookings->sortByDesc('booked_at') as $booking)
                            <div class="flex items-center justify-between py-2 border-b border-gray-700 last:border-b-0 ml-2">
                                <div class="flex-1">
                                    <p class="text-white text-sm font-medium">{{ $booking->user->name ?? 'Unknown' }}</p>
                                    <p class="text-gray-400 text-xs">{{ $booking->user->email ?? '' }}</p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        @if($booking->stripe_session_id)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-900 text-green-300 text-xs">
                                                💳 Paid
                                            </span>
                                        @elseif($booking->booking_type === 'pay_on_arrival')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-orange-900 text-orange-300 text-xs">
                                                🏃 Pay on Arrival
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-900 text-blue-300 text-xs">
                                                🎫 Credits
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-gray-400 text-xs">Booked: {{ optional($booking->booked_at)->format('M j, g:i A') ?? '-' }}</p>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $booking->status === 'confirmed' ? 'bg-green-800 text-green-100' : ($booking->status === 'cancelled' ? 'bg-red-800 text-red-100' : 'bg-yellow-800 text-yellow-100') }}">{{ ucfirst($booking->status) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <p class="text-gray-400 text-sm">No bookings.</p>
                @endforelse
            @else
                {{-- Show simple list when viewing specific date or non-recurring class --}}
                @forelse($class->bookings->sortByDesc('booked_at') as $booking)
                    <div class="flex items-center justify-between py-2 border-b border-gray-700 last:border-b-0">
                        <div class="flex-1">
                            <p class="text-white text-sm font-medium">{{ $booking->user->name ?? 'Unknown' }}</p>
                            <p class="text-gray-400 text-xs">{{ $booking->user->email ?? '' }}</p>
                            <div class="flex items-center space-x-2 mt-1">
                                @if($booking->stripe_session_id)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-900 text-green-300 text-xs">
                                        💳 Paid
                                    </span>
                                @elseif($booking->booking_type === 'pay_on_arrival')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-orange-900 text-orange-300 text-xs">
                                        🏃 Pay on Arrival
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-900 text-blue-300 text-xs">
                                        🎫 Credits
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-400 text-xs">{{ optional($booking->booked_at)->format('M j, Y g:i A') ?? '-' }}</p>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $booking->status === 'confirmed' ? 'bg-green-800 text-green-100' : ($booking->status === 'cancelled' ? 'bg-red-800 text-red-100' : 'bg-yellow-800 text-yellow-100') }}">{{ ucfirst($booking->status) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm">No bookings.</p>
                @endforelse
            @endif
        </div>
        <div class="px-5 py-3 border-t border-gray-700 text-right">
            <button class="bg-primary text-black hover:opacity-90 px-4 py-2 rounded-md text-sm font-medium" onclick="closeBookingsModal()">Close</button>
        </div>
    </div>
</div>

<!-- Cancel Class Modal -->
<div id="cancelModal" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
    <div class="absolute inset-0 bg-black bg-opacity-60" onclick="closeCancelModal()"></div>
    <div class="relative bg-gray-800 border border-gray-700 rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Cancel Class</h3>
            <button type="button" class="text-gray-400 hover:text-white" onclick="closeCancelModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-5">
            <div class="mb-4">
                <h4 class="text-white font-medium mb-2">{{ $class->name }}</h4>
                <p class="text-gray-400 text-sm mb-3">
                    {{ $class->class_date->format('l, F j, Y') }} at {{ $class->start_time }}
                </p>
            </div>

            <div class="mb-4">
                <div class="text-sm text-gray-300 mb-2">This action will:</div>
                <ul class="text-sm text-gray-400 space-y-1">
                    <li>• Deactivate this class</li>
                    @if($class->isRecurring() && !$class->isChildClass())
                        <li>• Cancel all future recurring instances</li>
                    @endif
                    <li>• Cancel all confirmed bookings for this class</li>
                    <li>• Notify affected members</li>
                </ul>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Cancellation Reason (Optional)</label>
                <textarea id="cancel_reason" class="w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" rows="3" placeholder="Reason for cancellation..."></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeCancelModal()" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Keep Class
                </button>
                <form id="cancel-class-form" action="{{ route('admin.classes.cancel', $class) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="reason" id="cancel_reason_hidden">
                    <button type="submit" onclick="submitCancel()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                        Cancel Class
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Send Roster Email Modal -->
<div id="sendRosterModal" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
    <div class="absolute inset-0 bg-black bg-opacity-60" onclick="closeSendRosterModal()"></div>
    <div class="relative bg-gray-800 border border-gray-700 rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Send Roster Email</h3>
            <button type="button" class="text-gray-400 hover:text-white" onclick="closeSendRosterModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-5">
            <div class="mb-4">
                <h4 class="text-white font-medium mb-2">{{ $class->name }}</h4>
                <p class="text-gray-400 text-sm mb-3">
                    @if(isset($filterDate))
                        {{ \Carbon\Carbon::parse($filterDate)->format('l, F j, Y') }} at {{ $class->start_time }}
                    @elseif($class->display_date ?? false)
                        {{ $class->display_date->format('l, F j, Y') }} at {{ $class->start_time }}
                    @else
                        {{ $class->class_date->format('l, F j, Y') }} at {{ $class->start_time }}
                    @endif
                </p>
                @if($class->instructor)
                    <p class="text-gray-400 text-sm">
                        Instructor: <span class="text-white">{{ $class->instructor->name }}</span> ({{ $class->instructor->email }})
                    </p>
                @endif
            </div>

            <div class="mb-4">
                <label for="roster_email" class="block text-sm font-medium text-gray-300 mb-2">
                    Send to Email Address
                </label>
                <input 
                    type="email" 
                    id="roster_email" 
                    name="email"
                    class="w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Enter email address"
                    @if($class->instructor)
                        value="{{ $class->instructor->email }}"
                    @endif
                >
                <p class="mt-1 text-xs text-gray-400">
                    @if($class->instructor)
                        Defaults to instructor's email. You can change it to send to a different address.
                    @else
                        Enter the email address to send the roster to.
                    @endif
                </p>
            </div>

            <div class="mb-4 p-3 bg-blue-900/30 border border-blue-700 rounded-lg">
                <p class="text-sm text-blue-200">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    This will send the current class roster to the specified email address.
                </p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeSendRosterModal()" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Cancel
                </button>
                <form id="send-roster-form" action="{{ route('admin.classes.send-roster', $class) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="date" value="@if(isset($filterDate)){{ $filterDate }}@elseif($class->display_date ?? false){{ $class->display_date->format('Y-m-d') }}@else{{ $class->class_date->format('Y-m-d') }}@endif">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Send Email
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openBookingsModal() {
    document.getElementById('bookingsModal').style.display = 'flex';
}

function closeBookingsModal() {
    document.getElementById('bookingsModal').style.display = 'none';
}

function showCancelModal() {
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
    document.getElementById('cancel_reason').value = '';
}

function submitCancel() {
    const reason = document.getElementById('cancel_reason').value;
    document.getElementById('cancel_reason_hidden').value = reason;
    document.getElementById('cancel-class-form').submit();
}

// Close modal when clicking outside
document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});

// Send Roster Email Modal Functions
function showSendRosterModal() {
    document.getElementById('sendRosterModal').style.display = 'flex';
}

function closeSendRosterModal() {
    document.getElementById('sendRosterModal').style.display = 'none';
}

function submitSendRoster() {
    const email = document.getElementById('roster_email').value;
    
    // Create hidden input for email if it doesn't exist
    let emailInput = document.querySelector('#send-roster-form input[name="email"]');
    if (!emailInput) {
        emailInput = document.createElement('input');
        emailInput.type = 'hidden';
        emailInput.name = 'email';
        document.getElementById('send-roster-form').appendChild(emailInput);
    }
    emailInput.value = email;
    
    document.getElementById('send-roster-form').submit();
}

document.getElementById('sendRosterModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSendRosterModal();
    }
});
</script>
@endsection
