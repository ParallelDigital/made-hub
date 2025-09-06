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
            <form action="{{ route('admin.instructors.destroy', $instructor) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this instructor?')">
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
                        @if($instructor->photo)
                            <img class="h-20 w-20 rounded-full object-cover" src="{{ asset('storage/' . $instructor->photo) }}" alt="{{ $instructor->name }}">
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

            <!-- Classes List -->
            @if($instructor->fitnessClasses->count() > 0)
                <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Classes</h3>
                    <div class="space-y-3">
                        @foreach($instructor->fitnessClasses as $class)
                            <div class="flex items-center justify-between py-2 border-b border-gray-700 last:border-b-0">
                                <div>
                                    <p class="text-white text-sm font-medium">{{ $class->name }}</p>
                                    <p class="text-gray-400 text-xs">{{ $class->type }} • {{ $class->duration }}min</p>
                                    <p class="text-gray-400 text-xs">{{ $class->start_time }} - {{ $class->end_time }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $class->active ? 'bg-green-800 text-green-100' : 'bg-red-800 text-red-100' }}">
                                        {{ $class->active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <p class="text-gray-400 text-xs mt-1">£{{ number_format($class->price, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('admin.classes.index') }}?instructor={{ $instructor->id }}" class="text-primary hover:text-purple-400 text-sm">
                            View all classes →
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Classes</h3>
                    <div class="text-center py-6">
                        <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-400 text-sm">No classes assigned</p>
                        <a href="{{ route('admin.classes.create') }}" class="text-primary hover:text-purple-400 text-sm">
                            Create a class →
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
