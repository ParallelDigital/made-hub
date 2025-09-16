@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-grid max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
    <!-- QR Code Card -->
    <div class="dashboard-card bg-gray-800 rounded-lg border border-gray-700 p-4 sm:p-5 lg:col-span-1">
        <h3 class="text-base sm:text-lg font-semibold text-white mb-3">Your Check-in QR</h3>
        <p class="text-sm text-gray-300 mb-4">Show this QR at the studio to check in quickly.</p>
        <div class="qr-container bg-gray-900 rounded-lg p-3 sm:p-4 flex items-center justify-center">
            <div class="qr-code">{!! $qrSvg !!}</div>
        </div>
        <div class="mt-4 text-xs break-all text-gray-300">
            <span class="block mb-1 text-gray-400">Backup link:</span>
            <a href="{{ $userQrUrl }}" class="underline text-primary hover:opacity-90 break-all">{{ $userQrUrl }}</a>
        </div>
    </div>

    <!-- Profile Summary -->
    <div class="dashboard-card bg-gray-800 rounded-lg border border-gray-700 p-4 sm:p-5 lg:col-span-1">
        <h3 class="text-base sm:text-lg font-semibold text-white mb-3">Profile</h3>
        <div class="space-y-2 text-sm sm:text-base text-gray-200">
            <p class="break-words"><span class="text-gray-400">Name:</span> {{ Auth::user()->name }}</p>
            <p class="break-words"><span class="text-gray-400">Email:</span> {{ Auth::user()->email }}</p>
            <p class="break-words"><span class="text-gray-400">QR Code ID:</span> {{ Auth::user()->qr_code }}</p>
            @php 
                $role = Auth::user()->role; 
                $hasMembership = Auth::user()->hasActiveMembership();
                $currentCredits = $hasMembership ? Auth::user()->getAvailableCredits() : (Auth::user()->credits ?? 0);
                $hasAnyCredits = ($currentCredits ?? 0) > 0;
            @endphp
            @if($role === 'admin' || $role === 'instructor' || $hasAnyCredits)
                <p class="break-words"><span class="text-gray-400">Booking Code (PIN):</span> <span class="font-mono tracking-widest text-white text-sm sm:text-base">{{ Auth::user()->pin_code ?? '— — — —' }}</span></p>
            @else
                <p class="break-words"><span class="text-gray-400">Booking Code (PIN):</span> <span class="font-mono tracking-widest text-white text-sm sm:text-base">{{ Auth::user()->pin_code ? '••••' : '— — — —' }}</span></p>
                <p class="text-xs text-gray-400">Your PIN will be shown when you book with credits.</p>
            @endif
            <p>
                <span class="text-gray-400">Membership:</span>
                @if(Auth::user()->hasActiveMembership())
                    Active ({{ Auth::user()->membership?->name ?? 'Member' }})
                @else
                    None
                @endif
            </p>
            @php 
                $credits = Auth::user()->hasActiveMembership() 
                    ? Auth::user()->getAvailableCredits() 
                    : (Auth::user()->credits ?? 0);
            @endphp
            <p class="break-words"><span class="text-gray-400">Credits:</span> {{ $credits }}</p>
        </div>
        <div class="profile-actions flex flex-col sm:flex-row gap-3 mt-4">
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-700 text-white hover:bg-gray-600 transition text-sm sm:text-base min-h-[44px]">Edit Profile</a>
            <a href="{{ route('purchase.index') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-primary text-black hover:opacity-90 transition text-sm sm:text-base min-h-[44px]">Buy Credits / Membership</a>
        </div>
    </div>

    <!-- Upcoming Classes -->
    <div class="dashboard-card bg-gray-800 rounded-lg border border-gray-700 p-4 sm:p-5 lg:col-span-1">
        <h3 class="text-base sm:text-lg font-semibold text-white mb-3">Your Upcoming Classes</h3>
        @if($upcomingBookings->isEmpty())
            <p class="text-gray-300 text-sm sm:text-base">You have no upcoming classes booked.</p>
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center mt-4 px-4 py-2 rounded-md bg-primary text-black hover:opacity-90 transition text-sm sm:text-base min-h-[44px] w-full sm:w-auto">Book a Class</a>
        @else
            <ul class="divide-y divide-gray-700">
                @foreach($upcomingBookings as $booking)
                    <li class="upcoming-class py-3 flex flex-col sm:flex-row items-start justify-between gap-3 sm:gap-4">
                        <div class="upcoming-class-details flex-1">
                            <p class="text-white font-medium text-sm sm:text-base break-words">{{ $booking->fitnessClass->name }}</p>
                            <p class="text-gray-300 text-xs sm:text-sm break-words">
                                {{ \Carbon\Carbon::parse($booking->fitnessClass->class_date)->format('D, M j, Y') }} ·
                                {{ \Carbon\Carbon::parse($booking->fitnessClass->start_time)->format('g:i A') }}
                            </p>
                            <p class="text-gray-400 text-xs sm:text-sm break-words">Instructor: {{ $booking->fitnessClass->instructor->name ?? 'N/A' }}</p>
                        </div>
                        <div class="upcoming-class-actions shrink-0 w-full sm:w-auto">
                            <a href="{{ route('booking.confirmation', ['classId' => $booking->fitness_class_id]) }}" class="text-primary hover:underline text-sm inline-flex items-center justify-center min-h-[44px] w-full sm:w-auto text-center">Details</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
