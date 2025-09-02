@extends('admin.layout')

@section('title', 'Edit Class')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.classes.index') }}" class="text-gray-400 hover:text-white mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Edit Class: {{ $class->name }}</h1>
    </div>

    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <form action="{{ route('admin.classes.update', $class) }}" method="POST" class="px-6 py-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-300">Class Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $class->name) }}" 
                       class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                       placeholder="e.g., Morning HIIT" required>
                @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-300">Description</label>
                <textarea name="description" id="description" rows="3" 
                          class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                          placeholder="Brief description of the class...">{{ old('description', $class->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-300">Class Type</label>
                    <select name="type" id="type" 
                            class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                        <option value="">Select Type</option>
                        <option value="HIIT" {{ old('type', $class->type) == 'HIIT' ? 'selected' : '' }}>HIIT</option>
                        <option value="Strength" {{ old('type', $class->type) == 'Strength' ? 'selected' : '' }}>Strength Training</option>
                        <option value="Cardio" {{ old('type', $class->type) == 'Cardio' ? 'selected' : '' }}>Cardio</option>
                        <option value="Yoga" {{ old('type', $class->type) == 'Yoga' ? 'selected' : '' }}>Yoga</option>
                        <option value="Pilates" {{ old('type', $class->type) == 'Pilates' ? 'selected' : '' }}>Pilates</option>
                        <option value="Boxing" {{ old('type', $class->type) == 'Boxing' ? 'selected' : '' }}>Boxing</option>
                        <option value="Running" {{ old('type', $class->type) == 'Running' ? 'selected' : '' }}>Running</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="instructor_id" class="block text-sm font-medium text-gray-300">Instructor</label>
                    <select name="instructor_id" id="instructor_id" 
                            class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                        <option value="">Select Instructor</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}" {{ old('instructor_id', $class->instructor_id) == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('instructor_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-300">Duration (minutes)</label>
                    <input type="number" name="duration" id="duration" value="{{ old('duration', $class->duration) }}" min="15" max="180"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('duration')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="max_spots" class="block text-sm font-medium text-gray-300">Max Spots</label>
                    <input type="number" name="max_spots" id="max_spots" value="{{ old('max_spots', $class->max_spots) }}" min="1" max="50"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('max_spots')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-300">Price ($)</label>
                    <input type="number" name="price" id="price" value="{{ old('price', $class->price) }}" min="0" step="0.01"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('price')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-300">Start Time</label>
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time', $class->start_time) }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-300">End Time</label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time', $class->end_time) }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('end_time')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="active" id="active" value="1" {{ old('active', $class->active) ? 'checked' : '' }}
                       class="h-4 w-4 text-primary focus:ring-primary border-gray-600 bg-gray-700 rounded">
                <label for="active" class="ml-2 block text-sm text-gray-300">
                    Active (class is available for booking)
                </label>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-700">
                <a href="{{ route('admin.classes.index') }}" 
                   class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Update Class
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
