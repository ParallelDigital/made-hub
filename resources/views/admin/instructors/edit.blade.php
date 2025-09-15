@extends('layouts.admin')

@section('title', 'Edit Instructor')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.instructors.index') }}" class="text-gray-400 hover:text-white mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Edit Instructor: {{ $instructor->name }}</h1>
    </div>

    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <form id="instructor-form" action="{{ route('admin.instructors.update', $instructor) }}" method="POST" enctype="multipart/form-data" class="px-6 py-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-300">Full Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $instructor->name) }}" 
                       class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                       placeholder="e.g., Sarah Johnson" required>
                @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $instructor->email) }}" 
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                           placeholder="sarah@example.com" required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-300">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $instructor->phone) }}" 
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                           placeholder="+1 (555) 123-4567">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-700">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300">New Password</label>
                    <input type="password" name="password" id="password"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="mt-2 text-sm text-gray-400">Leave blank to keep the current password.</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>

            <div>
                <label for="photo" class="block text-sm font-medium text-gray-300">Photo</label>
                <input type="file" name="photo" id="photo"
                       class="mt-1 block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600"/>
                <p class="mt-2 text-sm text-gray-400">Leave blank to keep the current photo.</p>
                @if ($instructor->photo)
                    <div class="mt-4">
                        <img src="{{ asset('storage/' . $instructor->photo) }}" alt="{{ $instructor->name }}" class="h-20 w-20 rounded-full object-cover">
                    </div>
                @endif
                @error('photo')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="active" id="active" value="1" {{ old('active', $instructor->active) ? 'checked' : '' }}
                       class="h-4 w-4 text-primary focus:ring-primary border-gray-600 bg-gray-700 rounded">
                <label for="active" class="ml-2 block text-sm text-gray-300">
                    Active (instructor is available for classes)
                </label>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-700">
                <a href="{{ route('admin.instructors.index') }}" 
                   class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Update Instructor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('instructor-form').addEventListener('submit', function(e) {
        e.preventDefault();

        let form = e.target;
        let formData = new FormData(form);
        formData.append('_method', 'PUT'); // Explicitly set the method

        fetch(form.action, {
            method: 'POST', // Use POST, as the method is spoofed in the body
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => {
            if (response.ok) {
                window.location.href = '{{ route("admin.instructors.index") }}';
            } else {
                response.json().then(data => {
                    // Handle validation errors or other issues
                    console.error('Submission failed:', data);
                    showAlertModal('An error occurred. Please check the console for details.', 'error');
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlertModal('A network error occurred. Please try again.', 'error');
        });
    });
</script>
@endpush
