@extends('admin.layout')

@section('title', 'Classes Management')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Classes Management</h1>
    <a href="{{ route('admin.classes.create') }}" class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
        Add New Class
    </a>
</div>

<!-- Filters -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-6">
    <div class="px-4 py-4">
        <form method="GET" action="{{ route('admin.classes.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="instructor" class="block text-sm font-medium text-gray-300 mb-1">Filter by Instructor</label>
                <select name="instructor" id="instructor" class="w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Instructors</option>
                    @foreach($instructors as $instructor)
                        <option value="{{ $instructor->id }}" {{ request('instructor') == $instructor->id ? 'selected' : '' }}>
                            {{ $instructor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            
            <div class="flex-1 min-w-[150px]">
                <label for="status" class="block text-sm font-medium text-gray-300 mb-1">Filter by Status</label>
                <select name="status" id="status" class="w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.classes.index') }}" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Clear
                </a>
            </div>
        </form>
    </div>
</div>

<div class="bg-gray-800 shadow rounded-lg border border-gray-700">
    <div class="px-4 py-5 sm:p-6">
        @if($classes->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Instructor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Spots</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Recurrence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($classes as $class)
                        <tr class="hover:bg-gray-700 cursor-pointer transition-colors" onclick="window.location='{{ route('admin.classes.show', $class) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">{{ $class->name }}</div>
                                @if($class->description)
                                    <div class="text-sm text-gray-400">{{ Str::limit($class->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $class->instructor->name ?? 'No Instructor' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $class->class_date ? $class->class_date->format('M j, Y') : 'No Date' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $class->start_time }} - {{ $class->end_time }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $class->max_spots }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                £{{ number_format($class->price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                @if($class->recurring_weekly)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Weekly
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        One-time
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation()">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.classes.show', $class) }}" class="text-primary hover:text-purple-400">View</a>
                                    <a href="{{ route('admin.classes.edit', $class) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                                    <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this class?')">
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
                {{ $classes->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-300">No classes</h3>
                <p class="mt-1 text-sm text-gray-400">Get started by creating a new fitness class.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.classes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-purple-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Class
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
