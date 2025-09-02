@extends('layouts.admin')

@section('title', 'Create Membership')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Create New Membership</h1>
    <a href="{{ route('admin.memberships.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded font-semibold hover:bg-gray-700 transition-all">
        Back to Memberships
    </a>
</div>

@if ($errors->any())
    <div class="bg-red-600 text-white p-4 rounded mb-6">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-gray-800 shadow rounded-lg border border-gray-700">
    <div class="px-4 py-5 sm:p-6">
        <form action="{{ route('admin.memberships.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Membership Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="e.g., Premium Monthly">
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Describe what this membership includes...">{{ old('description') }}</textarea>
                </div>

                <!-- Price -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-300 mb-2">Price (£)</label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01" min="0" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="0.00">
                </div>

                <!-- Duration -->
                <div>
                    <label for="duration_days" class="block text-sm font-medium text-gray-300 mb-2">Duration (Days)</label>
                    <input type="number" name="duration_days" id="duration_days" value="{{ old('duration_days') }}" min="1" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="30">
                    <p class="text-xs text-gray-400 mt-1">Common values: 30 (month), 90 (3 months), 365 (year)</p>
                </div>

                <!-- Class Credits -->
                <div>
                    <label for="class_credits" class="block text-sm font-medium text-gray-300 mb-2">Class Credits</label>
                    <input type="number" name="class_credits" id="class_credits" value="{{ old('class_credits') }}" min="0"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="10">
                    <p class="text-xs text-gray-400 mt-1">Leave empty if unlimited classes</p>
                </div>

                <!-- Unlimited Toggle -->
                <div class="flex items-center">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="unlimited" id="unlimited" value="1" {{ old('unlimited') ? 'checked' : '' }}
                               class="w-4 h-4 text-primary bg-gray-700 border-gray-600 rounded focus:ring-primary focus:ring-2"
                               onchange="toggleClassCredits()">
                    </div>
                    <div class="ml-3">
                        <label for="unlimited" class="text-sm font-medium text-gray-300">Unlimited Classes</label>
                        <p class="text-xs text-gray-400">Check if this membership includes unlimited class access</p>
                    </div>
                </div>

                <!-- Active Toggle -->
                <div class="flex items-center md:col-span-2">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="active" id="active" value="1" {{ old('active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary bg-gray-700 border-gray-600 rounded focus:ring-primary focus:ring-2">
                    </div>
                    <div class="ml-3">
                        <label for="active" class="text-sm font-medium text-gray-300">Active</label>
                        <p class="text-xs text-gray-400">Uncheck to disable this membership plan</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.memberships.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded font-semibold hover:bg-gray-700 transition-all">
                    Cancel
                </a>
                <button type="submit" class="bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
                    Create Membership
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleClassCredits() {
    const unlimited = document.getElementById('unlimited');
    const classCredits = document.getElementById('class_credits');
    
    if (unlimited.checked) {
        classCredits.value = '';
        classCredits.disabled = true;
        classCredits.classList.add('bg-gray-600', 'text-gray-400');
    } else {
        classCredits.disabled = false;
        classCredits.classList.remove('bg-gray-600', 'text-gray-400');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleClassCredits();
});
</script>
@endsection
