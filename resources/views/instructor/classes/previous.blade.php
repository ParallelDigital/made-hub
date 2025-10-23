@extends('layouts.admin')

@section('title', 'Previous Classes')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-white">Your Previous Classes</h1>
        <a href="{{ route('instructor.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
            &larr; Back to Dashboard
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-lg">
        <div class="p-6">
            @if($previousClasses->isEmpty())
                <div class="text-center py-12">
                    <div class="mx-auto w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-white mb-2">No Previous Classes Found</h3>
                    <p class="text-gray-400">You haven't taught any classes yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Class Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Time</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-800 divide-y divide-gray-700">
                            @foreach($previousClasses as $class)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ $class->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">{{ \Carbon\Carbon::parse($class->class_date)->format('D, M j, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">{{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('instructor.classes.bookings', $class) }}" class="text-indigo-400 hover:text-indigo-300">View Bookings</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($previousClasses->hasPages())
                    <div class="mt-6 px-6">
                        {{ $previousClasses->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
