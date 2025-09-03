@extends('admin.layout')

@section('title', 'Instructor Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold text-white mb-6">Instructor Dashboard</h1>

    <div class="bg-gray-800 shadow rounded-lg border border-gray-700 p-6">
        <p class="text-white">Welcome, {{ Auth::user()->name }}!</p>
        <p class="text-gray-400 mt-2">This is your dashboard. You can view your upcoming classes and manage your schedule here.</p>
    </div>

    <!-- Upcoming Classes Section -->
    <div class="mt-8">
        <h2 class="text-xl font-bold text-white mb-4">Your Upcoming Classes</h2>
        <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
            @if($upcomingClasses->isEmpty())
                <div class="p-6 text-gray-400">
                    You have no upcoming classes.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-gray-400">
                        <thead class="text-xs text-gray-300 uppercase bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3">Class Name</th>
                                <th scope="col" class="px-6 py-3">Date</th>
                                <th scope="col" class="px-6 py-3">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingClasses as $class)
                                <tr class="bg-gray-800 border-b border-gray-700 hover:bg-gray-600">
                                    <td class="px-6 py-4 font-medium text-white whitespace-nowrap">{{ $class->name }}</td>
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($class->class_date)->format('D, M j, Y') }}</td>
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
