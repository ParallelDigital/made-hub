@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- QR Code Card -->
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-5 lg:col-span-1">
        <h3 class="text-lg font-semibold text-white mb-3">Your Check-in QR</h3>
        <p class="text-sm text-gray-300 mb-4">Show this QR at the studio to check in quickly.</p>
        <div class="bg-gray-900 rounded-lg p-4 flex items-center justify-center">
            {!! $qrSvg !!}
        </div>
        <div class="mt-4 text-xs break-all text-gray-300">
            <span class="block mb-1 text-gray-400">Backup link:</span>
            <a href="{{ $userQrUrl }}" class="underline text-primary hover:opacity-90">{{ $userQrUrl }}</a>
        </div>
    </div>

    <!-- Profile Summary -->
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-5 lg:col-span-1">
        <h3 class="text-lg font-semibold text-white mb-3">Profile</h3>
        <div class="space-y-2 text-gray-200">
            <p><span class="text-gray-400">Name:</span> {{ Auth::user()->name }}</p>
            <p><span class="text-gray-400">Email:</span> {{ Auth::user()->email }}</p>
            <p><span class="text-gray-400">QR Code ID:</span> {{ Auth::user()->qr_code }}</p>
            @php 
                $role = Auth::user()->role; 
                $hasMembership = Auth::user()->hasActiveMembership();
                $currentCredits = $hasMembership ? Auth::user()->getAvailableCredits() : (Auth::user()->credits ?? 0);
                $hasAnyCredits = ($currentCredits ?? 0) > 0;
            @endphp
            @if($role === 'admin' || $role === 'instructor' || $hasAnyCredits)
                <p><span class="text-gray-400">Booking Code (PIN):</span> <span class="font-mono tracking-widest text-white">{{ Auth::user()->pin_code ?? '— — — —' }}</span></p>
            @else
                <p><span class="text-gray-400">Booking Code (PIN):</span> <span class="font-mono tracking-widest text-white">{{ Auth::user()->pin_code ? '••••' : '— — — —' }}</span></p>
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
            <p><span class="text-gray-400">Credits:</span> {{ $credits }}</p>
        </div>
        <div class="flex gap-3 mt-4">
            <a href="{{ route('profile.edit') }}" class="inline-flex px-4 py-2 rounded-md bg-gray-700 text-white hover:bg-gray-600 transition">Edit Profile</a>
            <a href="{{ route('purchase.index') }}" class="inline-flex px-4 py-2 rounded-md bg-primary text-black hover:opacity-90 transition">Buy Credits / Membership</a>
        </div>
    </div>

    <!-- Upcoming Classes -->
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-5 lg:col-span-1">
        <h3 class="text-lg font-semibold text-white mb-3">Your Upcoming Classes</h3>
        @if($upcomingBookings->isEmpty())
            <p class="text-gray-300">You have no upcoming classes booked.</p>
            <a href="{{ url('/') }}" class="inline-flex mt-4 px-4 py-2 rounded-md bg-primary text-black hover:opacity-90 transition">Book a Class</a>
        @else
            <ul class="divide-y divide-gray-700">
                @foreach($upcomingBookings as $booking)
                    <li class="py-3 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-white font-medium">{{ $booking->fitnessClass->name }}</p>
                            <p class="text-gray-300 text-sm">
                                {{ \Carbon\Carbon::parse($booking->fitnessClass->class_date)->format('D, M j, Y') }} ·
                                {{ \Carbon\Carbon::parse($booking->fitnessClass->start_time)->format('g:i A') }}
                            </p>
                            <p class="text-gray-400 text-sm">Instructor: {{ $booking->fitnessClass->instructor->name ?? 'N/A' }}</p>
                        </div>
                        <div class="shrink-0">
                            <a href="{{ route('booking.confirmation', ['classId' => $booking->fitness_class_id]) }}" class="text-primary hover:underline text-sm">Details</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
