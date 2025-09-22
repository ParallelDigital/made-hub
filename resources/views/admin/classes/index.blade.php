@extends('layouts.admin')

@section('title', 'Classes Management')


@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Classes Calendar</h1>
    <div class="flex gap-3">
        <a href="{{ route('admin.classes.index', ['view' => 'list']) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-medium transition-colors">
            List View
        </a>
        <a href="{{ route('admin.classes.create') }}" class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
            Add New Class
        </a>
    </div>
</div>

<!-- Calendar View -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-6">
    <div class="px-4 py-4">
        <div class="flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[200px]">
                <label for="instructor" class="block text-sm font-medium text-gray-300 mb-1">Instructor</label>
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
                <label for="status" class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                <select name="status" id="status" class="w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div class="flex-1 min-w-[150px]">
                <label for="view" class="block text-sm font-medium text-gray-300 mb-1">View</label>
                <select id="view-selector" class="w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="timeGridWeek">Week</option>
                    <option value="dayGridMonth">Month</option>
                    <option value="timeGridDay">Day</option>
                </select>
            </div>
            
            <div class="flex gap-2 items-end">
                <button type="button" id="apply-filters" class="bg-primary hover:bg-purple-400 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Apply Filters
                </button>
                <a href="{{ route('admin.classes.index') }}" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    Reset
                </a>
            </div>
        </div>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Recurrence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($classes as $class)
                        <tr class="hover:bg-gray-700 cursor-pointer transition-colors" onclick="window.location='{{ route('admin.classes.show', $class) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">{{ $class->name }}</div>
                               
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
                                @if($class->recurring_frequency !== 'none')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($class->recurring_frequency) }}
                                    </span>
                                    @if($class->isChildClass())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-1">
                                            Instance
                                        </span>
                                    @endif
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
                                    <button onclick="showDeleteOptionsModal({{ $class->id }}, '{{ $class->name }}', {{ $class->isRecurring() && !$class->isChildClass() ? 'true' : 'false' }})" class="text-red-400 hover:text-red-300">Delete</button>
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

<!-- Delete Options Modal -->
<div id="deleteOptionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-gray-800">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-white" id="deleteOptionsModalTitle">Delete Class Options</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-300" id="deleteOptionsModalMessage">
                    Choose how you want to delete this class.
                </p>
                <div class="mt-4 space-y-3">
                    <!-- Delete This Class Only -->
                    <form id="deleteSingleForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Delete This Class Only
                        </button>
                    </form>

                    <!-- Delete After Date (for recurring classes) -->
                    <div id="deleteAfterSection" class="hidden">
                        <form id="deleteAfterForm" method="POST" class="mt-3">
                            @csrf
                            <div class="mb-3">
                                <label for="delete_after_date" class="block text-sm font-medium text-gray-300 mb-2">Delete After Date</label>
                                <input type="date" name="delete_after_date" id="delete_after_date" required
                                       class="w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <button type="submit" class="w-full px-4 py-2 bg-orange-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-300">
                                Delete All Future Instances
                            </button>
                        </form>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" onclick="hideDeleteOptionsModal()" class="w-full px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showDeleteOptionsModal(classId, className, isRecurring) {
    document.getElementById('deleteOptionsModalTitle').textContent = `Delete "${className}"`;
    document.getElementById('deleteSingleForm').action = `/admin/classes/${classId}`;

    // Show delete after section only for recurring classes
    const deleteAfterSection = document.getElementById('deleteAfterSection');
    if (isRecurring) {
        deleteAfterSection.classList.remove('hidden');
        document.getElementById('deleteAfterForm').action = `/admin/classes/${classId}/delete-after-date`;
    } else {
        deleteAfterSection.classList.add('hidden');
    }

    document.getElementById('deleteOptionsModal').classList.remove('hidden');
}

function hideDeleteOptionsModal() {
    document.getElementById('deleteOptionsModal').classList.add('hidden');
    document.getElementById('delete_after_date').value = '';
}

// Close modal when clicking outside
document.getElementById('deleteOptionsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteOptionsModal();
    }
});
</script>
@endsection
