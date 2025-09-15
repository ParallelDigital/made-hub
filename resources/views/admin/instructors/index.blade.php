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
        <!-- Calendar Header with Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
            <div>
                <h3 class="text-lg leading-6 font-medium text-white">
                    @if($view === 'weekly')
                        Week of {{ $currentWeekStart->format('M j, Y') }}
                    @else
                        {{ $currentWeekStart->format('F Y') }}
                    @endif
                </h3>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Instructor Filter -->
                <form method="GET" action="{{ route('admin.instructors.index') }}" class="flex items-center space-x-2">
                    <input type="hidden" name="view" value="{{ $view }}" />
                    <input type="hidden" name="week" value="{{ $weekOffset }}" />
                    <x-custom-select 
                        name="instructor"
                        :options="collect($allInstructors)->mapWithKeys(fn($inst) => [$inst->id => $inst->name])->toArray()"
                        :selected="$selectedInstructor"
                        placeholder="All Instructors" />
                    <button type="submit" class="px-3 py-2 text-sm font-medium bg-primary text-white rounded-md">Apply</button>
                </form>

                <!-- View Toggle -->
                <div class="flex bg-gray-700 rounded-lg p-1">
                    <a href="{{ route('admin.instructors.index', ['view' => 'weekly', 'week' => $weekOffset, 'instructor' => $selectedInstructor]) }}" 
                       class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $view === 'weekly' ? 'bg-primary text-white' : 'text-gray-300 hover:text-white' }}">
                        Weekly
                    </a>
                    <a href="{{ route('admin.instructors.index', ['view' => 'monthly', 'week' => $weekOffset, 'instructor' => $selectedInstructor]) }}" 
                       class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $view === 'monthly' ? 'bg-primary text-white' : 'text-gray-300 hover:text-white' }}">
                        Monthly
                    </a>
                </div>
                
                <!-- Navigation Controls -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.instructors.index', ['view' => $view, 'week' => $weekOffset - 1, 'instructor' => $selectedInstructor]) }}" 
                       class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    
                    <a href="{{ route('admin.instructors.index', ['view' => $view, 'week' => 0, 'instructor' => $selectedInstructor]) }}" 
                       class="px-3 py-1 text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-700 rounded-md transition-colors">
                        Today
                    </a>
                    
                    <a href="{{ route('admin.instructors.index', ['view' => $view, 'week' => $weekOffset + 1, 'instructor' => $selectedInstructor]) }}" 
                       class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        @if($view === 'weekly')
            <div class="grid grid-cols-7 gap-1 mb-4">
                @foreach($calendarDates as $index => $date)
                <div class="text-center {{ $date->isToday() ? 'bg-primary bg-opacity-10 border-primary border-2' : '' }} rounded-lg">
                    <div class="text-sm font-medium text-gray-300 py-2 border-b border-gray-600">
                        <div>{{ $date->format('D') }}</div>
                        <div class="text-lg {{ $date->isToday() ? 'text-primary font-bold' : '' }}">{{ $date->format('j') }}</div>
                    </div>
                    <div class="min-h-[200px] p-2 space-y-1">
                        @if(isset($calendarData[$index]))
                            @foreach($calendarData[$index] as $class)
                                <div class="bg-primary bg-opacity-20 border border-primary rounded p-2 text-xs cursor-pointer hover:bg-opacity-30 transition-colors"
                                     onclick="window.location='{{ route('admin.classes.show', $class) }}'">
                                    <div class="font-medium text-primary">{{ $class->start_time }}</div>
                                    <div class="text-white font-medium">{{ $class->name }}</div>
                                    <div class="text-gray-300">{{ $class->instructor->name ?? 'No Instructor' }}</div>
                                    <div class="text-gray-400">{{ $class->type }} • {{ $class->duration }}min</div>
                                    <div class="text-gray-400">£{{ number_format($class->price, 0) }}</div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <!-- Monthly View -->
            <div class="grid grid-cols-7 gap-1 mb-4">
                @php $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; @endphp
                @foreach($dayNames as $dayName)
                    <div class="text-center text-sm font-medium text-gray-300 py-2 border-b border-gray-600">
                        {{ $dayName }}
                    </div>
                @endforeach
                
                @foreach($calendarDates as $index => $date)
                    <div class="min-h-[120px] border border-gray-700 p-1 {{ $date->isToday() ? 'bg-primary bg-opacity-10 border-primary border-2' : '' }} rounded-lg">
                        <div class="text-sm {{ $date->isToday() ? 'text-primary font-bold' : ($date->month !== $currentWeekStart->month ? 'text-gray-500' : 'text-gray-300') }}">
                            {{ $date->format('j') }}
                        </div>
                        <div class="space-y-1 mt-1">
                            @if(isset($calendarData[$index]))
                                @foreach($calendarData[$index]->take(2) as $class)
                                    <div class="bg-primary bg-opacity-20 border border-primary rounded p-1 text-xs cursor-pointer hover:bg-opacity-30 transition-colors"
                                         onclick="window.location='{{ route('admin.classes.show', $class) }}'">
                                        <div class="font-medium text-primary">{{ $class->start_time }}</div>
                                        <div class="text-white text-xs truncate">{{ $class->name }}</div>
                                    </div>
                                @endforeach
                                @if(isset($calendarData[$index]) && $calendarData[$index]->count() > 2)
                                    <div class="text-xs text-gray-400">+{{ $calendarData[$index]->count() - 2 }} more</div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex justify-between items-center pt-4 border-top border-gray-700">
            <div class="text-sm text-gray-400">
                Showing {{ $calendarData->flatten()->count() }} active classes
            </div>
            <a href="{{ route('admin.classes.index') }}" class="text-primary hover:text-purple-400 text-sm font-medium">
                View all classes →
            </a>
        </div>

        @if($instructors->count() > 0)
            <div class="overflow-x-auto mt-8">
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
                                    <form id="delete-instructor-{{ $instructor->id }}-form" action="{{ route('admin.instructors.destroy', $instructor) }}" method="POST" class="inline" onsubmit="event.preventDefault(); showConfirmModal('Are you sure you want to delete this instructor?', function(){ document.getElementById('delete-instructor-{{ $instructor->id }}-form').submit(); })">
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
