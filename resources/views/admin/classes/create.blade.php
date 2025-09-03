@extends('admin.layout')

@section('title', 'Create New Class')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.classes.index') }}" class="text-gray-400 hover:text-white mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Create New Class</h1>
    </div>

    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <form action="{{ route('admin.classes.store') }}" method="POST" class="px-6 py-6 space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-300">Class Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" 
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
                          placeholder="Brief description of the class...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="instructor_id" class="block text-sm font-medium text-gray-300">Instructor</label>
                    <select name="instructor_id" id="instructor_id" 
                            class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                        <option value="">Select Instructor</option>
                        @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}" {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('instructor_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="max_spots" class="block text-sm font-medium text-gray-300">Max Spots</label>
                    <input type="number" name="max_spots" id="max_spots" value="{{ old('max_spots') }}" min="1" max="50"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('max_spots')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-300">Price (£)</label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" min="0" step="0.01"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('price')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="class_date" class="block text-sm font-medium text-gray-300">Class Date</label>
                    <input type="date" name="class_date" id="class_date" value="{{ old('class_date') }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('class_date')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-300">Start Time</label>
                    <select name="start_time" id="start_time" 
                            class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                        <option value="">Select Start Time</option>
                        @php
                            $times = [
                                '06:00' => '6:00 AM', '06:30' => '6:30 AM', '07:00' => '7:00 AM', '07:30' => '7:30 AM',
                                '08:00' => '8:00 AM', '08:30' => '8:30 AM', '09:00' => '9:00 AM', '09:30' => '9:30 AM',
                                '10:00' => '10:00 AM', '10:30' => '10:30 AM', '11:00' => '11:00 AM', '11:30' => '11:30 AM',
                                '12:00' => '12:00 PM', '12:30' => '12:30 PM', '13:00' => '1:00 PM', '13:30' => '1:30 PM',
                                '14:00' => '2:00 PM', '14:30' => '2:30 PM', '15:00' => '3:00 PM', '15:30' => '3:30 PM',
                                '16:00' => '4:00 PM', '16:30' => '4:30 PM', '17:00' => '5:00 PM', '17:30' => '5:30 PM',
                                '18:00' => '6:00 PM', '18:30' => '6:30 PM', '19:00' => '7:00 PM', '19:30' => '7:30 PM',
                                '20:00' => '8:00 PM', '20:30' => '8:30 PM', '21:00' => '9:00 PM'
                            ];
                        @endphp
                        @foreach($times as $value => $display)
                            <option value="{{ $value }}" {{ old('start_time') == $value ? 'selected' : '' }}>
                                {{ $display }}
                            </option>
                        @endforeach
                    </select>
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-300">End Time</label>
                    <select name="end_time" id="end_time" 
                            class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                        <option value="">Select End Time</option>
                        @foreach($times as $value => $display)
                            <option value="{{ $value }}" {{ old('end_time') == $value ? 'selected' : '' }}>
                                {{ $display }}
                            </option>
                        @endforeach
                    </select>
                    @error('end_time')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-center">
                    <input type="checkbox" name="active" id="active" value="1" {{ old('active', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-primary focus:ring-primary border-gray-600 bg-gray-700 rounded">
                    <label for="active" class="ml-2 block text-sm text-gray-300">
                        Active Class
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="recurring_weekly" id="recurring_weekly" value="1" {{ old('recurring_weekly') ? 'checked' : '' }}
                           class="h-4 w-4 text-primary focus:ring-primary border-gray-600 bg-gray-700 rounded">
                    <label for="recurring_weekly" class="ml-2 block text-sm text-gray-300">
                        Recurring Weekly
                    </label>
                </div>
            </div>

            <div id="recurring_days_section" class="hidden">
                <label class="block text-sm font-medium text-gray-300 mb-2">Select Days for Weekly Recurrence</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @php
                        $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
                    @endphp
                    @foreach($days as $value => $label)
                        <div class="flex items-center">
                            <input type="checkbox" name="recurring_days[]" id="day_{{ $value }}" value="{{ $value }}"
                                   {{ in_array($value, old('recurring_days', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-600 bg-gray-700 rounded">
                            <label for="day_{{ $value }}" class="ml-2 block text-sm text-gray-300">
                                {{ $label }}
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('recurring_days')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <script>
                document.getElementById('recurring_weekly').addEventListener('change', function() {
                    const recurringDaysSection = document.getElementById('recurring_days_section');
                    if (this.checked) {
                        recurringDaysSection.classList.remove('hidden');
                    } else {
                        recurringDaysSection.classList.add('hidden');
                        // Uncheck all day checkboxes
                        const dayCheckboxes = document.querySelectorAll('input[name="recurring_days[]"]');
                        dayCheckboxes.forEach(checkbox => checkbox.checked = false);
                    }
                });

                // Show/hide on page load if already checked
                if (document.getElementById('recurring_weekly').checked) {
                    document.getElementById('recurring_days_section').classList.remove('hidden');
                }
            </script>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-700">
                <a href="{{ route('admin.classes.index') }}" 
                   class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Create Class
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
