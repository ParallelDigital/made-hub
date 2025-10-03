@extends('layouts.admin')

@section('title', 'Instructor Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <a href="{{ route('admin.instructors.index') }}" class="text-gray-400 hover:text-white mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $instructor->name }}</h1>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.instructors.edit', $instructor) }}" 
               class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                Edit Instructor
            </a>
            <form id="delete-instructor-form" action="{{ route('admin.instructors.destroy', $instructor) }}" method="POST" class="inline" onsubmit="event.preventDefault(); showConfirmModal('Are you sure you want to delete this instructor?', function(){ document.getElementById('delete-instructor-form').submit(); })">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Delete Instructor
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Instructor Information -->
        <div class="lg:col-span-2">
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
                <div class="flex items-start space-x-6 mb-6">
                    <div class="flex-shrink-0">
                        @if($instructor->photo_url)
                            <img class="h-20 w-20 rounded-full object-cover" src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}">
                        @else
                            <div class="h-20 w-20 rounded-full bg-primary flex items-center justify-center">
                                <span class="text-2xl font-medium text-white">{{ substr($instructor->name, 0, 2) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-semibold text-white mb-2">{{ $instructor->name }}</h2>
                        <div class="flex items-center mb-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $instructor->active ? 'bg-green-800 text-green-100' : 'bg-red-800 text-red-100' }}">
                                {{ $instructor->active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Email</label>
                        <p class="mt-1 text-white">{{ $instructor->email }}</p>
                    </div>
                    
                    @if($instructor->phone)
                        <div>
                            <label class="block text-sm font-medium text-gray-400">Phone</label>
                            <p class="mt-1 text-white">{{ $instructor->phone }}</p>
                        </div>
                    @endif
                </div>
                
            </div>
        </div>

        <!-- Stats & Classes -->
        <div class="space-y-6">
            <!-- Stats Card -->
            <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Statistics</h3>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Total Classes</span>
                        <span class="text-white font-medium">{{ $instructor->fitnessClasses->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Active Classes</span>
                        <span class="text-green-400 font-medium">{{ $instructor->fitnessClasses->where('active', true)->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Inactive Classes</span>
                        <span class="text-red-400 font-medium">{{ $instructor->fitnessClasses->where('active', false)->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Stats & Classes -->
        </div>
    </div>

    <!-- Calendar Section -->
    <div class="mt-8">
        <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
            <div class="px-4 py-5 sm:p-6">
                <!-- Calendar Header with Controls -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
                    <div>
                        <h2 class="text-lg leading-6 font-medium text-white">
                            @if($view === 'weekly')
                                Week of {{ $currentWeekStart->format('M j, Y') }}
                            @else
                                {{ $currentWeekStart->format('F Y') }}
                            @endif
                        </h2>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- View Toggle -->
                        <div class="flex bg-gray-700 rounded-lg p-1">
                            <a href="{{ route('admin.instructors.show', ['instructor' => $instructor->id, 'view' => 'weekly', 'week' => request('week', 0)]) }}" class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $view === 'weekly' ? 'bg-primary text-white' : 'text-gray-300 hover:text-white' }}">Weekly</a>
                            <a href="{{ route('admin.instructors.show', ['instructor' => $instructor->id, 'view' => 'monthly', 'week' => request('week', 0)]) }}" class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $view === 'monthly' ? 'bg-primary text-white' : 'text-gray-300 hover:text-white' }}">Monthly</a>
                        </div>

                        <!-- Navigation Controls -->
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.instructors.show', ['instructor' => $instructor->id, 'view' => $view, 'week' => (int)request('week', 0) - 1]) }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition-colors" aria-label="Previous">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </a>
                            <a href="{{ route('admin.instructors.show', ['instructor' => $instructor->id, 'view' => $view, 'week' => 0]) }}" class="px-3 py-1 text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-700 rounded-md transition-colors">Today</a>
                            <a href="{{ route('admin.instructors.show', ['instructor' => $instructor->id, 'view' => $view, 'week' => (int)request('week', 0) + 1]) }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition-colors" aria-label="Next">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Calendar Grid (dashboard style) -->
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
                                            <div class="bg-primary bg-opacity-20 border border-primary rounded p-2 text-xs cursor-pointer hover:bg-opacity-30 transition-colors" onclick="window.location='{{ route('admin.classes.show', $class) }}'">
                                                <div class="font-medium text-primary">{{ $class->start_time }}</div>
                                                <div class="text-white font-medium">{{ $class->name }}</div>
                                                <div class="text-gray-300">{{ $class->instructor->name ?? 'No Instructor' }}</div>
                                                <div class="text-gray-400">{{ $class->classType->name ?? 'No Type' }} • {{ $class->classType->duration ?? 60 }}min</div>
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
                            <div class="text-center text-sm font-medium text-gray-300 py-2 border-b border-gray-600">{{ $dayName }}</div>
                        @endforeach
                        @foreach($calendarDates as $index => $date)
                            <div class="min-h-[120px] border border-gray-700 p-1 {{ $date->isToday() ? 'bg-primary bg-opacity-10 border-primary border-2' : '' }} rounded-lg">
                                <div class="text-sm {{ $date->isToday() ? 'text-primary font-bold' : ($date->month !== $currentWeekStart->month ? 'text-gray-500' : 'text-gray-300') }}">{{ $date->format('j') }}</div>
                                <div class="space-y-1 mt-1">
                                    @if(isset($calendarData[$index]))
                                        @foreach($calendarData[$index]->take(2) as $class)
                                            <div class="bg-primary bg-opacity-20 border border-primary rounded p-1 text-xs cursor-pointer hover:bg-opacity-30 transition-colors" onclick="window.location='{{ route('admin.classes.show', $class) }}'">
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

                <!-- Footer -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-700 mt-4">
                    <div class="text-sm text-gray-400">Showing {{ $calendarData->flatten()->count() }} active classes</div>
                    <a href="{{ route('admin.classes.index') }}?instructor={{ $instructor->id }}" class="text-primary hover:text-purple-400 text-sm font-medium">View all classes →</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
