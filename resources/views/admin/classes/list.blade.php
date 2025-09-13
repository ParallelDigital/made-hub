@extends('layouts.admin')

@section('title', 'Classes Management - List View')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Classes Management</h1>
    <div class="flex gap-3">
        <a href="{{ route('admin.classes.index') }}" class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
            Calendar View
        </a>
        <a href="{{ route('admin.classes.create') }}" class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
            Add New Class
        </a>
    </div>
</div>

<!-- Filters -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-6">
    <div class="px-4 py-4">
        <form method="GET" action="{{ route('admin.classes.index') }}" class="flex flex-wrap gap-4 items-end">
            <input type="hidden" name="view" value="list">
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
                <a href="{{ route('admin.classes.index', ['view' => 'list']) }}" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($classes as $class)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-700 rounded-full flex items-center justify-center">
                                            <span class="text-gray-300">{{ substr($class->classType->name, 0, 2) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-white">{{ $class->classType->name }}</div>
                                            <div class="text-sm text-gray-400">{{ $class->classType->duration }} min</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-white">{{ $class->instructor->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-white">{{ $class->class_date->format('D, M j, Y') }}</div>
                                    <div class="text-sm text-gray-400">{{ $class->start_time }} - {{ $class->end_time }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                    {{ $class->location }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($class->active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.classes.edit', $class) }}" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>
                                    <button onclick="showDeleteModal('{{ $class->id }}')" class="text-red-400 hover:text-red-300">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $classes->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-white">No classes found</h3>
                <p class="mt-1 text-sm text-gray-400">
                    Get started by creating a new class.
                </p>
                <div class="mt-6">
                    <a href="{{ route('admin.classes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-purple-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        New Class
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                        Delete Class
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-300">
                            Are you sure you want to delete this class? This action cannot be undone.
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <form id="deleteForm" method="POST" class="inline-flex w-full justify-center">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm">
                        Delete
                    </button>
                </form>
                <button type="button" onclick="hideDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:col-start-1 sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showDeleteModal(classId) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/classes/${classId}`;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
