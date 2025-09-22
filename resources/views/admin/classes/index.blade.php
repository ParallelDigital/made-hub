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
            <!-- Quick Actions -->
            <div class="mb-6 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-400">
                        <span class="font-medium text-white">{{ $classes->total() }}</span> class {{ $classes->total() === 1 ? 'group' : 'groups' }} found
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-medium transition-colors text-sm" onclick="toggleAllGroups(true)">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        Expand All
                    </button>
                    <button type="button" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-medium transition-colors text-sm" onclick="toggleAllGroups(false)">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        </svg>
                        Collapse All
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($classes as $group)
                    @php
                        $groupId = 'group-' . str_replace(' ', '-', strtolower($group['name']));
                        $isFirst = $loop->first; // Default expand first group
                        $nextClass = $group['classes'][0] ?? null;
                        $hasRecurring = collect($group['classes'])->some(fn($c) => $c->recurring_frequency !== 'none');
                    @endphp

                    <!-- Class Group Card -->
                    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden shadow-sm">
                        <!-- Group Header -->
                        <div class="bg-gradient-to-r from-gray-800 to-gray-750 px-6 py-4 cursor-pointer hover:bg-gray-750 transition-colors"
                             data-group-toggle="{{ $groupId }}"
                             data-expanded="{{ $isFirst ? 'true' : 'false' }}"
                             onclick="toggleGroup('{{ $groupId }}')">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <!-- Class Icon -->
                                    <div class="flex-shrink-0 w-12 h-12 bg-primary rounded-lg flex items-center justify-center">
                                        <span class="text-white font-bold text-lg">{{ strtoupper(substr($group['name'], 0, 1)) }}</span>
                                    </div>

                                    <!-- Class Info -->
                                    <div>
                                        <h3 class="text-lg font-semibold text-white">{{ $group['name'] }}</h3>
                                        <div class="flex items-center space-x-4 text-sm text-gray-400">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                {{ $group['instructor'] }}
                                            </span>
                                            @if($hasRecurring)
                                                <span class="flex items-center text-blue-400">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    </svg>
                                                    Recurring
                                                </span>
                                            @endif
                                            <span class="bg-gray-700 px-2 py-1 rounded-full text-xs font-medium">
                                                {{ $group['total_instances'] }} {{ $group['total_instances'] === 1 ? 'class' : 'classes' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Expand/Collapse Icon -->
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-400">{{ $isFirst ? 'Click to collapse' : 'Click to expand' }}</span>
                                    <svg class="w-6 h-6 text-primary transition-transform duration-200"
                                         style="transform: rotate({{ $isFirst ? '90' : '0' }}deg)"
                                         data-chevron-for="{{ $groupId }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Class Instances -->
                        <div data-group-row="{{ $groupId }}" class="{{ $isFirst ? '' : 'hidden' }}">
                            <div class="border-t border-gray-700">
                                <div class="divide-y divide-gray-700">
                                    @foreach($group['classes'] as $class)
                                        <div class="px-6 py-4 hover:bg-gray-750 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <!-- Class Details -->
                                                <div class="flex items-center space-x-4">
                                                    <!-- Date & Time -->
                                                    <div class="flex-shrink-0">
                                                        <div class="text-sm font-medium text-white">
                                                            {{ $class->class_date ? $class->class_date->format('M j') : 'No Date' }}
                                                        </div>
                                                        <div class="text-xs text-gray-400">
                                                            {{ $class->start_time }} - {{ $class->end_time }}
                                                        </div>
                                                    </div>

                                                    <!-- Class Type & Location -->
                                                    <div class="flex-1">
                                                        @if($class->classType)
                                                            <div class="text-sm text-gray-300">{{ $class->classType->name }}</div>
                                                        @endif
                                                        @if($class->location)
                                                            <div class="text-xs text-gray-400 flex items-center">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                </svg>
                                                                {{ $class->location }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Status Badges -->
                                                    <div class="flex items-center space-x-2">
                                                        @if($class->active)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-900 text-green-200">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                                Active
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-900 text-red-200">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                                Inactive
                                                            </span>
                                                        @endif

                                                        @if($class->recurring_frequency !== 'none')
                                                            @if($class->isChildClass())
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-900 text-blue-200">
                                                                    Instance
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-900 text-purple-200">
                                                                    Parent
                                                                </span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Actions -->
                                                <div class="flex items-center space-x-1">
                                                    <a href="{{ route('admin.classes.show', $class) }}"
                                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-primary hover:bg-primary hover:text-white transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                        View
                                                    </a>
                                                    <a href="{{ route('admin.classes.edit', $class) }}"
                                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-blue-400 hover:bg-blue-400 hover:text-white transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                        Edit
                                                    </a>
                                                    <button onclick="showDeleteOptionsModal({{ $class->id }}, '{{ $class->name }}', {{ $class->isRecurring() && !$class->isChildClass() ? 'true' : 'false' }})"
                                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-400 hover:bg-red-400 hover:text-white transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
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
