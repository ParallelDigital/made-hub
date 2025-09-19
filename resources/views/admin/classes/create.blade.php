@extends('layouts.admin')

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
        <form id="class-form" action="{{ route('admin.classes.store') }}" method="POST" class="px-6 py-6 space-y-6">
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
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-300">End Time</label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
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

                <div>
                    <label for="recurring_frequency" class="block text-sm font-medium text-gray-300">Recurring Frequency</label>
                    <select name="recurring_frequency" id="recurring_frequency" 
                            class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="none" {{ old('recurring_frequency', 'none') == 'none' ? 'selected' : '' }}>No Recurring</option>
                        <option value="weekly" {{ old('recurring_frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="biweekly" {{ old('recurring_frequency') == 'biweekly' ? 'selected' : '' }}>Biweekly</option>
                        <option value="monthly" {{ old('recurring_frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                    @error('recurring_frequency')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="members_only" id="members_only" value="1" {{ old('members_only') ? 'checked' : '' }}
                       class="h-4 w-4 text-primary focus:ring-primary border-gray-600 bg-gray-700 rounded">
                <label for="members_only" class="ml-2 block text-sm text-gray-300">
                    Members Only (Free for members)
                </label>
            </div>

            <p class="text-xs text-gray-400 -mt-3 mb-2">Members-only classes are free and do not use credits. Only users with an active membership can book.</p>

            <div id="recurring_options_section" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="recurring_until" class="block text-sm font-medium text-gray-300">Recurring Until</label>
                        <input type="date" name="recurring_until" id="recurring_until" value="{{ old('recurring_until') }}"
                               class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        @error('recurring_until')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div id="recurring_days_section" class="mt-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Select Days for Recurrence</label>
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
            </div>

            <script>
                document.getElementById('recurring_frequency').addEventListener('change', function() {
                    const recurringOptionsSection = document.getElementById('recurring_options_section');
                    if (this.value !== 'none') {
                        recurringOptionsSection.classList.remove('hidden');
                    } else {
                        recurringOptionsSection.classList.add('hidden');
                        // Clear recurring until date
                        document.getElementById('recurring_until').value = '';
                        // Uncheck all day checkboxes
                        const dayCheckboxes = document.querySelectorAll('input[name="recurring_days[]"]');
                        dayCheckboxes.forEach(checkbox => checkbox.checked = false);
                    }
                });

                // Show/hide on page load if already selected
                if (document.getElementById('recurring_frequency').value !== 'none') {
                    document.getElementById('recurring_options_section').classList.remove('hidden');
                }

                // Members-only toggle -> set price to 0 and disable field
                const membersOnly = document.getElementById('members_only');
                const priceInput = document.getElementById('price');
                function syncMembersOnlyUI() {
                    if (!membersOnly || !priceInput) return;
                    if (membersOnly.checked) {
                        priceInput.value = 0;
                        priceInput.setAttribute('disabled', 'disabled');
                    } else {
                        priceInput.removeAttribute('disabled');
                    }
                }
                if (membersOnly) {
                    membersOnly.addEventListener('change', syncMembersOnlyUI);
                    syncMembersOnlyUI();
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
