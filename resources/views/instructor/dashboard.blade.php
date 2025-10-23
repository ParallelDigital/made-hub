@extends('layouts.admin')

@section('title', 'Instructor Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="text-purple-100">Ready to inspire your students today?</p>
            </div>
            <div class="hidden md:block">
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4">
                    <div class="text-white text-center">
                        <div class="text-2xl font-bold">{{ $upcomingClasses->count() }}</div>
                        <div class="text-sm text-purple-100">Upcoming Classes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-500/20">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-400">Today's Classes</p>
                    <p class="text-2xl font-semibold text-white">{{ $upcomingClasses->where('class_date', now()->toDateString())->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-500/20">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10m6-10v10m-6-4h6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-400">This Week</p>
                    <p class="text-2xl font-semibold text-white">{{ $upcomingClasses->whereBetween('class_date', [now()->startOfWeek(), now()->endOfWeek()])->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-500/20">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-400">Total Bookings</p>
                    <p class="text-2xl font-semibold text-white">{{ $upcomingClasses->sum(function($class) { return $class->bookings->count(); }) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Classes Section -->
    <div>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white">Your Upcoming Classes</h2>
            <a href="{{ route('instructor.classes.previous') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                View Previous Classes
            </a>
        </div>

        @if($upcomingClasses->isEmpty())
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
                <div class="mx-auto w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10m6-10v10m-6-4h6"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-white mb-2">No upcoming classes</h3>
                <p class="text-gray-400">You have a clear schedule ahead. Time to relax or plan new classes!</p>
            </div>
        @else
            <div class="grid gap-4">
                @foreach($upcomingClasses->take(10) as $class)
                    @php
                        $classDate = \Carbon\Carbon::parse($class->class_date);
                        $startTime = \Carbon\Carbon::parse($class->start_time);
                        $endTime = \Carbon\Carbon::parse($class->end_time);
                        $isToday = $classDate->isToday();
                        $isTomorrow = $classDate->isTomorrow();
                        $bookingCount = $class->bookings->count();
                        $maxSpots = $class->max_spots;
                        $fillPercentage = $maxSpots > 0 ? ($bookingCount / $maxSpots) * 100 : 0;
                    @endphp
                    
                    <div class="bg-gray-800 rounded-lg border border-gray-700 hover:border-gray-600 transition-all duration-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between flex-col md:flex-row">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-xl font-semibold text-white">{{ $class->name }}</h3>
                                        @if($isToday)
                                            <span class="px-2 py-1 text-xs font-medium bg-green-500/20 text-green-400 rounded-full">Today</span>
                                        @elseif($isTomorrow)
                                            <span class="px-2 py-1 text-xs font-medium bg-blue-500/20 text-blue-400 rounded-full">Tomorrow</span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center space-x-6 text-sm text-gray-400 mb-4">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10m6-10v10m-6-4h6"></path>
                                            </svg>
                                            <span>{{ $classDate->format('D, M j, Y') }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>{{ $startTime->format('g:i A') }} - {{ $endTime->format('g:i A') }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <span>{{ $bookingCount }}/{{ $maxSpots }} booked</span>
                                        </div>
                                    </div>

                                    <!-- Booking Progress Bar -->
                                    <div class="mb-4">
                                        <div class="flex justify-between text-xs text-gray-400 mb-1">
                                            <span>Class Capacity</span>
                                            <span>{{ number_format($fillPercentage, 0) }}% full</span>
                                        </div>
                                        <div class="w-full bg-gray-700 rounded-full h-2">
                                            <div class="bg-gradient-to-r from-purple-500 to-indigo-500 h-2 rounded-full transition-all duration-300" 
                                                 style="width: {{ min($fillPercentage, 100) }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex space-x-2">
                                    <a href="{{ route('instructor.classes.members', $class) }}" 
                                       class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                        View Members
                                    </a>
                                    <a href="{{ route('instructor.classes.scanner', $class) }}" 
                                       class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                        </svg>
                                        QR Scanner
                                    </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($upcomingClasses->count() > 10)
                <div class="mt-6 text-center">
                    <p class="text-gray-400 text-sm">Showing 10 of {{ $upcomingClasses->count() }} upcoming classes</p>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
