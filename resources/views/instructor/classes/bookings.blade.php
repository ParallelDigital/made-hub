@extends('layouts.admin')

@section('title', 'Class Bookings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $class->name }}</h1>
                <p class="text-gray-400 mt-1">{{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}</p>
            </div>
            <a href="{{ route('instructor.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                &larr; Back to Dashboard
            </a>
        </div>
        
        @if($class->recurring)
            <div class="mt-4 text-sm text-gray-400 bg-gray-700 rounded px-3 py-2 inline-block">
                <span class="font-medium">Recurring Class:</span> Showing all bookings across all dates
            </div>
        @endif
    </div>

    <!-- Bookings Card -->
    <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-white">All Bookings</h2>
                <span class="text-sm text-gray-400">Total: {{ $bookings->count() }}</span>
            </div>

            <!-- Use EXACT same structure as admin modal -->
            @if($class->recurring)
                @php
                    // Group bookings by date for recurring classes
                    $groupedBookings = $bookings->groupBy(function($booking) {
                        return $booking->booking_date ? $booking->booking_date->format('Y-m-d') : 'unknown';
                    })->sortKeysDesc();
                @endphp
                @forelse($groupedBookings as $date => $dateBookings)
                    <div class="mb-4">
                        <div class="text-sm font-semibold text-purple-400 mb-2 border-b border-gray-600 pb-1">
                            {{ $date !== 'unknown' ? \Carbon\Carbon::parse($date)->format('l, M j, Y') : 'Unknown Date' }}
                            <span class="text-gray-400 font-normal">({{ $dateBookings->count() }} booking{{ $dateBookings->count() !== 1 ? 's' : '' }})</span>
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
                @forelse($bookings->sortByDesc('booked_at') as $booking)
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
    </div>
</div>
@endsection
