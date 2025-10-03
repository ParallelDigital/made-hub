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

    <!-- Classes Table Section -->
    <div class="mt-8">
        <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-white">Classes</h2>
                    <a href="{{ route('admin.classes.create', ['instructor' => $instructor->id]) }}" class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
                        Add Class
                    </a>
                </div>

                @if($instructor->fitnessClasses->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Class Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-800 divide-y divide-gray-700">
                                @foreach($instructor->fitnessClasses as $class)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white">{{ $class->name }}</div>
                                            @if($class->location)
                                                <div class="text-sm text-gray-400">{{ $class->location }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-300">{{ $class->class_date ? $class->class_date->format('M j, Y') : 'No date' }}</div>
                                            <div class="text-sm text-gray-400">{{ $class->start_time }} - {{ $class->end_time }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-300">{{ $class->classType->name ?? 'No Type' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-300">£{{ number_format($class->price, 0) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($class->active)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-800 text-green-100">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-800 text-red-100">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.classes.show', $class) }}" class="text-primary hover:text-purple-400">View</a>
                                                <a href="{{ route('admin.classes.edit', $class) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-300">No classes assigned</h3>
                        <p class="mt-1 text-sm text-gray-400">This instructor doesn't have any classes yet.</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.classes.create', ['instructor' => $instructor->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-purple-400">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create First Class
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
