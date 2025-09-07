@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-400 truncate">Total Members</dt>
                        <dd class="text-lg font-medium text-white">{{ $stats['total_users'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-400 truncate">Instructors</dt>
                        <dd class="text-lg font-medium text-white">{{ $stats['total_instructors'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-400 truncate">Classes</dt>
                        <dd class="text-lg font-medium text-white">{{ $stats['total_classes'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700 cursor-pointer hover:border-primary transition-colors" onclick="window.location='{{ route('admin.bookings.index') }}'">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-400 truncate">Bookings</dt>
                        <dd class="text-lg font-medium text-white">{{ $stats['total_bookings'] }}</dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('admin.bookings.index') }}" class="text-primary text-sm hover:text-purple-400">View bookings →</a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-8">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg leading-6 font-medium text-white">Recent Bookings</h3>
            <a href="{{ route('admin.bookings.index') }}" class="text-primary hover:text-purple-400 text-sm font-medium">
                View all bookings →
            </a>
        </div>

        @if($recentBookings->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Member</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Booked</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($recentBookings as $booking)
                        <tr class="hover:bg-gray-700/50 cursor-pointer" onclick="window.location='{{ route('admin.bookings.show', $booking) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center">
                                            <span class="text-xs font-medium text-white">
                                                {{ strtoupper(substr($booking->user->name ?? 'U', 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white">{{ $booking->user->name ?? 'Unknown User' }}</div>
                                        <div class="text-sm text-gray-400">{{ $booking->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-white">{{ $booking->fitnessClass->name ?? 'Unknown Class' }}</div>
                                <div class="text-sm text-gray-400">{{ $booking->fitnessClass->instructor->name ?? 'No Instructor' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                @if($booking->fitnessClass)
                                    <div>{{ $booking->fitnessClass->class_date ? $booking->fitnessClass->class_date->format('M j, Y') : 'No Date' }}</div>
                                    <div class="text-gray-400">{{ $booking->fitnessClass->start_time }} - {{ $booking->fitnessClass->end_time }}</div>
                                @else
                                    <div class="text-gray-400">Class not found</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                    @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                    @elseif($booking->status === 'waitlist') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($booking->status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $booking->created_at->format('M j, Y') }}
                                <div class="text-xs text-gray-500">{{ $booking->created_at->format('g:i A') }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-300">No recent bookings</h3>
                <p class="mt-1 text-sm text-gray-400">Recent bookings will appear here once classes are booked.</p>
            </div>
        @endif
    </div>
</div>

<!-- Calendar View -->
<div class="mb-8">
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <!-- Calendar Header with Controls -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-white">
                        @if($view === 'weekly')
                            Week of {{ $currentWeekStart->format('M j, Y') }}
                        @else
                            {{ $currentWeekStart->format('F Y') }}
                        @endif
                    </h3>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- View Toggle -->
                    <div class="flex bg-gray-700 rounded-lg p-1">
                        <a href="{{ route('admin.dashboard', ['view' => 'weekly', 'week' => $weekOffset]) }}" 
                           class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $view === 'weekly' ? 'bg-primary text-white' : 'text-gray-300 hover:text-white' }}">
                            Weekly
                        </a>
                        <a href="{{ route('admin.dashboard', ['view' => 'monthly', 'week' => $weekOffset]) }}" 
                           class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $view === 'monthly' ? 'bg-primary text-white' : 'text-gray-300 hover:text-white' }}">
                            Monthly
                        </a>
                    </div>
                    
                    <!-- Navigation Controls -->
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.dashboard', ['view' => $view, 'week' => $weekOffset - 1]) }}" 
                           class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        
                        <a href="{{ route('admin.dashboard', ['view' => $view, 'week' => 0]) }}" 
                           class="px-3 py-1 text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-700 rounded-md transition-colors">
                            Today
                        </a>
                        
                        <a href="{{ route('admin.dashboard', ['view' => $view, 'week' => $weekOffset + 1]) }}" 
                           class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Calendar Grid -->
            @if($view === 'weekly')
                <div class="grid grid-cols-7 gap-1 mb-4">
                    @foreach($calendarDates as $index => $date)
                    <div class="text-center {{ $date->isToday() ? 'bg-primary bg-opacity-10 border-primary border-2' : '' }} rounded-lg">
                        <div class="text-sm font-medium text-gray-300 py-2 border-b border-gray-600">
                            <div>{{ $date->format('D') }}</div>
                            <div class="text-lg {{ $date->isToday() ? 'text-primary font-bold' : '' }}">{{ $date->format('j') }}</div>
                        </div>
                        <div class="min-h-[200px] p-2 space-y-1">
                                @if(isset($calendarData[$index]))
                                    @foreach($calendarData[$index] as $class)
                                        <div class="bg-primary bg-opacity-20 border border-primary rounded p-2 text-xs cursor-pointer hover:bg-opacity-30 transition-colors"
                                             onclick="window.location='{{ route('admin.classes.show', $class) }}'">
                                            <div class="font-medium text-primary">{{ $class->start_time }}</div>
                                            <div class="text-white font-medium">{{ $class->name }}</div>
                                            <div class="text-gray-300">{{ $class->instructor->name ?? 'No Instructor' }}</div>
                                            <div class="text-gray-400">{{ $class->type }} • {{ $class->duration }}min</div>
                                            <div class="text-gray-400">£{{ number_format($class->price, 0) }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Monthly View -->
                <div class="grid grid-cols-7 gap-1 mb-4">
                    @php $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; @endphp
                    @foreach($dayNames as $dayName)
                        <div class="text-center text-sm font-medium text-gray-300 py-2 border-b border-gray-600">
                            {{ $dayName }}
                        </div>
                    @endforeach
                    
                    @foreach($calendarDates as $index => $date)
                        <div class="min-h-[120px] border border-gray-700 p-1 {{ $date->isToday() ? 'bg-primary bg-opacity-10 border-primary border-2' : '' }} rounded-lg">
                            <div class="text-sm {{ $date->isToday() ? 'text-primary font-bold' : ($date->month !== $currentWeekStart->month ? 'text-gray-500' : 'text-gray-300') }}">
                                {{ $date->format('j') }}
                            </div>
                            <div class="space-y-1 mt-1">
                                @if(isset($calendarData[$index]))
                                    @foreach($calendarData[$index]->take(2) as $class)
                                        <div class="bg-primary bg-opacity-20 border border-primary rounded p-1 text-xs cursor-pointer hover:bg-opacity-30 transition-colors"
                                             onclick="window.location='{{ route('admin.classes.show', $class) }}'">
                                            <div class="font-medium text-primary">{{ $class->start_time }}</div>
                                            <div class="text-white text-xs truncate">{{ $class->name }}</div>
                                        </div>
                                    @endforeach
                                    @if(isset($calendarData[$index]) && $calendarData[$index]->count() > 2)
                                        <div class="text-xs text-gray-400">+{{ $calendarData[$index]->count() - 2 }} more</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            
            <div class="flex justify-between items-center pt-4 border-t border-gray-700">
                <div class="text-sm text-gray-400">
                    Showing {{ $calendarData->flatten()->count() }} active classes
                </div>
                <a href="{{ route('admin.classes.index') }}" class="text-primary hover:text-purple-400 text-sm font-medium">
                    View all classes →
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-white">Quick Actions</h3>
            <div class="mt-5 grid grid-cols-1 gap-3">
                <a href="{{ route('admin.classes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-purple-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New Class
                </a>
                <a href="{{ route('admin.instructors.create') }}" class="inline-flex items-center px-4 py-2 border border-gray-600 text-sm font-medium rounded-md text-gray-300 bg-transparent hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New Instructor
                </a>
                <a href="{{ route('admin.memberships.create') }}" class="inline-flex items-center px-4 py-2 border border-gray-600 text-sm font-medium rounded-md text-gray-300 bg-transparent hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New Membership
                </a>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-white">System Overview</h3>
            <div class="mt-5 text-sm text-gray-300">
                <p class="mb-2">Welcome to the Made Running admin dashboard. From here you can:</p>
                <ul class="list-disc list-inside space-y-1 text-gray-400">
                    <li>Manage fitness classes and schedules</li>
                    <li>Add and edit instructor profiles</li>
                    <li>Configure membership plans</li>
                    <li>View user registrations and bookings</li>
                    <li>Generate reports and analytics</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
