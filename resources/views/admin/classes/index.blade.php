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
            <div class="mb-3 flex justify-end gap-2">
                <button type="button" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded text-sm" onclick="toggleAllGroups(true)">Expand all</button>
                <button type="button" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded text-sm" onclick="toggleAllGroups(false)">Collapse all</button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Class Group</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Instructor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Total Instances</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($classes as $group)
                            @php
                                $groupId = 'group-' . str_replace(' ', '-', strtolower($group['name']));
                                $isFirst = $loop->first; // Default expand first group
                            @endphp
                            <tr class="bg-gray-900 hover:bg-gray-800 cursor-pointer" data-group-toggle="{{ $groupId }}" data-expanded="{{ $isFirst ? 'true' : 'false' }}" onclick="toggleGroup('{{ $groupId }}')">
                                <td colspan="4" class="px-6 py-3">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-semibold text-white">
                                            {{ $group['name'] }}
                                            <span class="ml-2 text-gray-400 font-normal">({{ $group['total_instances'] }} {{ $group['total_instances'] === 1 ? 'instance' : 'instances' }})</span>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-300 transition-transform duration-200"
                                             style="transform: rotate({{ $isFirst ? '90' : '0' }}deg)"
                                             data-chevron-for="{{ $groupId }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </td>
                            </tr>
                            @foreach($group['classes'] as $class)
                                <tr data-group-row="{{ $groupId }}" class="{{ $isFirst ? '' : 'hidden' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-700 rounded-full flex items-center justify-center">
                                                <span class="text-gray-300">{{ strtoupper(substr($class->name, 0, 2)) }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-white">{{ $class->name }}</div>
                                                @if($class->classType)
                                                    <div class="text-sm text-gray-400">{{ $class->classType->name }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $class->instructor->name ?? 'No Instructor' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        <div>{{ $class->class_date ? $class->class_date->format('D, M j, Y') : 'No Date' }}</div>
                                        <div class="text-gray-400">{{ $class->start_time }} - {{ $class->end_time }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.classes.show', $class) }}" class="text-primary hover:text-purple-400">View</a>
                                            <a href="{{ route('admin.classes.edit', $class) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                                            <button onclick="showDeleteOptionsModal({{ $class->id }}, '{{ $class->name }}', {{ $class->isRecurring() && !$class->isChildClass() ? 'true' : 'false' }})" class="text-red-400 hover:text-red-300">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
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
function toggleGroup(groupId) {
    const rows = document.querySelectorAll(`[data-group-row="${groupId}"]`);
    const header = document.querySelector(`[data-group-toggle="${groupId}"]`);
    const chevron = document.querySelector(`[data-chevron-for="${groupId}"]`);
    const expanded = header && header.getAttribute('data-expanded') === 'true';
    const nextState = !expanded;
    rows.forEach(r => r.classList.toggle('hidden', !nextState));
    if (header) header.setAttribute('data-expanded', nextState ? 'true' : 'false');
    if (chevron) chevron.style.transform = nextState ? 'rotate(90deg)' : 'rotate(0)';
}

function toggleAllGroups(expand) {
    const headers = document.querySelectorAll('[data-group-toggle]');
    headers.forEach(h => {
        const id = h.getAttribute('data-group-toggle');
        const rows = document.querySelectorAll(`[data-group-row="${id}"]`);
        const chevron = document.querySelector(`[data-chevron-for="${id}"]`);
        rows.forEach(r => r.classList.toggle('hidden', !expand));
        h.setAttribute('data-expanded', expand ? 'true' : 'false');
        if (chevron) chevron.style.transform = expand ? 'rotate(90deg)' : 'rotate(0)';
    });
}

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
