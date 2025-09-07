@extends('layouts.admin')

@section('title', 'Instructors Management')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Instructors Management</h1>
    <a href="{{ route('admin.instructors.create') }}" class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
        Add New Instructor
    </a>
</div>

<div class="bg-gray-800 shadow rounded-lg border border-gray-700">
    <div class="px-4 py-5 sm:p-6">
        @if($instructors->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Instructor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Contact</th>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Classes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($instructors as $instructor)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($instructor->photo)
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $instructor->photo) }}" alt="{{ $instructor->name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center">
                                                <span class="text-sm font-medium text-white">{{ substr($instructor->name, 0, 2) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white">{{ $instructor->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-300">{{ $instructor->email }}</div>
                                @if($instructor->phone)
                                    <div class="text-sm text-gray-400">{{ $instructor->phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $instructor->fitness_classes_count }} classes
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($instructor->active)
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
                                    <a href="{{ route('admin.instructors.show', $instructor) }}" class="text-primary hover:text-purple-400">View</a>
                                    <a href="{{ route('admin.instructors.edit', $instructor) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <form action="{{ route('admin.instructors.destroy', $instructor) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this instructor?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $instructors->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-300">No instructors</h3>
                <p class="mt-1 text-sm text-gray-400">Get started by adding a new instructor.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.instructors.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-purple-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Instructor
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
