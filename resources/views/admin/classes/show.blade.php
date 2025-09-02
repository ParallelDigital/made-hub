@extends('admin.layout')

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
            <a href="{{ route('admin.classes.edit', $class) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                Edit Class
            </a>
            <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this class?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Delete Class
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Class Information -->
        <div class="lg:col-span-2">
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Class Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Class Name</label>
                        <p class="mt-1 text-white">{{ $class->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Type</label>
                        <p class="mt-1 text-white">{{ $class->type }}</p>
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
                        <p class="mt-1 text-white">${{ number_format($class->price, 2) }}</p>
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
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Total Bookings</span>
                        <span class="text-white font-medium">{{ $class->bookings->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Available Spots</span>
                        <span class="text-white font-medium">{{ $class->max_spots - $class->bookings->where('status', 'confirmed')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Confirmed</span>
                        <span class="text-green-400 font-medium">{{ $class->bookings->where('status', 'confirmed')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Cancelled</span>
                        <span class="text-red-400 font-medium">{{ $class->bookings->where('status', 'cancelled')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Waitlist</span>
                        <span class="text-yellow-400 font-medium">{{ $class->bookings->where('status', 'waitlist')->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            @if($class->bookings->count() > 0)
                <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Recent Bookings</h3>
                    <div class="space-y-3">
                        @foreach($class->bookings->take(5) as $booking)
                            <div class="flex items-center justify-between py-2 border-b border-gray-700 last:border-b-0">
                                <div>
                                    <p class="text-white text-sm font-medium">{{ $booking->user->name }}</p>
                                    <p class="text-gray-400 text-xs">{{ $booking->booked_at->format('M j, Y g:i A') }}</p>
                                </div>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $booking->status === 'confirmed' ? 'bg-green-800 text-green-100' : 
                                       ($booking->status === 'cancelled' ? 'bg-red-800 text-red-100' : 'bg-yellow-800 text-yellow-100') }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    @if($class->bookings->count() > 5)
                        <div class="mt-4 text-center">
                            <span class="text-gray-400 text-sm">And {{ $class->bookings->count() - 5 }} more bookings...</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
