@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div class="text-center">
                <div class="mx-auto h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Welcome, {{ $user->name }}!
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Your QR code check-in is successful
                </p>
            </div>

            <div class="mt-8">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Your Upcoming Bookings</h3>

                    @if($upcomingBookings->count() > 0)
                        <div class="space-y-3">
                            @foreach($upcomingBookings as $booking)
                                <div class="bg-white p-3 rounded border">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $booking->fitnessClass->name }}</h4>
                                            <p class="text-sm text-gray-600">
                                                {{ $booking->fitnessClass->class_date->format('M j, Y') }} at {{ $booking->fitnessClass->start_time }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                Instructor: {{ $booking->fitnessClass->instructor->name ?? 'N/A' }}
                                            </p>
                                        </div>
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            Confirmed
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No upcoming bookings found.</p>
                    @endif
                </div>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Your unique QR code: <span class="font-mono font-medium">{{ $user->qr_code }}</span>
                    </p>
                </div>

                <div class="mt-6">
                    <a href="{{ route('dashboard') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
