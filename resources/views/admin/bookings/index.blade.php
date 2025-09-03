@extends('admin.layout')

@section('title', 'Bookings')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Bookings</h1>
</div>

<div class="bg-gray-800 shadow rounded-lg border border-gray-700 overflow-hidden">
    <div class="p-4 border-b border-gray-700">
        <p class="text-sm text-gray-400">Showing {{ $bookings->total() }} bookings</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Booked At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ optional($booking->booked_at)->format('M j, Y g:i A') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $booking->user->name ?? 'Unknown' }} <span class="text-gray-400">({{ $booking->user->email ?? 'no-email' }})</span></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a class="text-primary hover:text-purple-400" href="{{ route('admin.classes.show', $booking->fitnessClass) }}">{{ $booking->fitnessClass->name ?? 'Class #' . $booking->fitness_class_id }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $booking->status === 'confirmed' ? 'bg-green-800 text-green-100' : 
                                   ($booking->status === 'cancelled' ? 'bg-red-800 text-red-100' : 'bg-yellow-800 text-yellow-100') }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400">No bookings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-700">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
