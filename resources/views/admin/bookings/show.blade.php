@extends('layouts.admin')

@section('title', 'Booking Details')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Booking Details</h1>
        <a href="{{ route('admin.bookings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
            Back to Bookings
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Booking Information -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Booking Information</h2>
            <div class="space-y-3">
                <div>
                    <span class="font-medium text-gray-700">Booking ID:</span>
                    <span class="text-gray-900">#{{ $booking->id }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Booking Date:</span>
                    <span class="text-gray-900">{{ $booking->booked_at->format('M j, Y g:i A') }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Status:</span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' :
                           ($booking->status === 'cancelled' ? 'bg-red-100 text-red-800' :
                           'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Payment Method:</span>
                    <span class="text-gray-900">
                        {{ $booking->stripe_session_id ? 'Stripe' : 'Credits' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Class Information -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Class Information</h2>
            <div class="space-y-3">
                <div>
                    <span class="font-medium text-gray-700">Class Name:</span>
                    <span class="text-gray-900">{{ $booking->fitnessClass->name }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Class Date:</span>
                    <span class="text-gray-900">{{ ($booking->booking_date ?? $booking->fitnessClass->class_date)->format('M j, Y') }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Time:</span>
                    <span class="text-gray-900">{{ $booking->fitnessClass->start_time }}</span>
                </div>
                <div>
                    <span class="font-medium text-gray-700">Instructor:</span>
                    <span class="text-gray-900">{{ $booking->fitnessClass->instructor->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div>
                        <span class="font-medium text-gray-700">Name:</span>
                        <a href="{{ route('admin.users.edit', $booking->user) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                            {{ $booking->user->name }}
                        </a>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Email:</span>
                        <a href="{{ route('admin.users.edit', $booking->user) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                            {{ $booking->user->email }}
                        </a>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <span class="font-medium text-gray-700">Phone:</span>
                        <span class="text-gray-900">{{ $booking->user->phone ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-8 flex space-x-4">
        @if($booking->status === 'confirmed')
            <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="inline">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" onclick="return confirm('Are you sure you want to cancel this booking?')"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm">
                    Cancel Booking
                </button>
            </form>

            <form method="POST" action="{{ route('admin.bookings.resend-confirmation', $booking) }}" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                    Resend Confirmation
                </button>
            </form>
        @endif

        <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Are you sure you want to permanently delete this booking? This action cannot be undone.')"
                    class="bg-red-800 hover:bg-red-900 text-white px-4 py-2 rounded-md text-sm">
                Delete Booking
            </button>
        </form>

        <a href="{{ route('admin.bookings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm">
            Back to List
        </a>
    </div>
</div>
@endsection
