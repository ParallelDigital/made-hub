@extends('admin.layout')

@section('title', 'Booking Check-In')

@section('content')
<div class="max-w-xl mx-auto bg-gray-800 border border-gray-700 rounded-lg p-6">
    <h1 class="text-2xl font-bold text-white mb-4">Booking Check-In</h1>
    <p class="text-gray-300 mb-4">This is a preview of the check-in page that your QR codes will point to. In a future iteration, scanning this QR will mark attendance.</p>

    <div class="bg-gray-900 border border-gray-700 rounded p-4 mb-4">
        <p class="text-gray-400 text-sm">Booking ID</p>
        <p class="text-white font-mono">#{{ $booking->id }}</p>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-gray-900 border border-gray-700 rounded p-4">
            <p class="text-gray-400 text-sm">Member</p>
            <p class="text-white">{{ optional($booking->user)->name ?? 'Guest' }}</p>
            <p class="text-gray-300 text-sm">{{ optional($booking->user)->email }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-700 rounded p-4">
            <p class="text-gray-400 text-sm">Class</p>
            <p class="text-white">{{ optional($booking->fitnessClass)->name ?? 'Class' }}</p>
            @if(optional($booking->fitnessClass)->class_date)
                <p class="text-gray-300 text-sm">
                    {{ \Carbon\Carbon::parse($booking->fitnessClass->class_date)->format('l, F j, Y') }}
                    @if(optional($booking->fitnessClass)->start_time)
                        at {{ \Carbon\Carbon::parse($booking->fitnessClass->start_time)->format('g:i A') }}
                    @endif
                </p>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
            Status: {{ ucfirst($booking->status) }}
        </span>
    </div>
</div>
@endsection
