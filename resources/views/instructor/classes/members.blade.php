@extends('admin.layout')

@section('content')
    <div class="container mx-auto px-4 sm:px-8">
        <div class="py-8">
            <div>
                <h2 class="text-2xl font-semibold leading-tight">Class Members - {{ $class->name }}</h2>
                <p class="text-sm text-gray-600">{{ $class->class_date->format('D, M j, Y') }} at {{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }}</p>
            </div>
            <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
                <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th
                                    class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Name
                                </th>
                                <th
                                    class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Email
                                </th>
                                <th
                                    class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($members as $booking)
                                <tr>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap">{{ $booking->user->name }}</p>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <p class="text-gray-900 whitespace-no-wrap">{{ $booking->user->email }}</p>
                                    </td>
                                     <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        @php
                                            $isCheckedIn = (bool)($booking->attended ?? false);
                                            $time = $booking->checked_in_at ? $booking->checked_in_at->format('g:i A') : null;
                                        @endphp
                                        <span class="relative inline-block px-3 py-1 font-semibold leading-tight {{ $isCheckedIn ? 'text-green-900' : 'text-yellow-900' }}">
                                            <span aria-hidden class="absolute inset-0 {{ $isCheckedIn ? 'bg-green-200' : 'bg-yellow-200' }} opacity-50 rounded-full"></span>
                                            <span class="relative">
                                                {{ $isCheckedIn ? ('Checked In' . ($time ? ' at ' . $time : '')) : 'Not Checked In' }}
                                            </span>
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                        <p class="text-gray-900 whitespace-no-wrap">No members have booked this class yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
