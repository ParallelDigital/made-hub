@extends('layouts.admin')

@section('title', 'Class Pass Details: ' . $user->name)

@section('content')
@php
    $hasActiveUnlimited = $user->hasActiveUnlimitedPass();
    $hasActiveCredits = $user->getNonMemberAvailableCredits() > 0;
    $unlimitedExpiry = $user->unlimited_pass_expires_at;
    $creditsExpiry = $user->credits_expires_at;
@endphp

<div class="bg-gray-900 rounded-lg border border-gray-700 p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">Class Pass Details</h1>
            <p class="text-gray-400 text-sm mt-1">Viewing passes for <a href="{{ route('admin.users.edit', $user) }}" class="text-primary hover:underline">{{ $user->name }}</a></p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.class-passes.index') }}" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                Back to List
            </a>
            <a href="{{ route('admin.class-passes.edit', $user) }}" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
                Edit Pass
            </a>
        </div>
    </div>

    <!-- Pass Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Unlimited Pass -->
        <div class="bg-gray-800 p-4 rounded-lg border border-gray-700">
            <h2 class="text-lg font-semibold text-white mb-3">Unlimited Pass</h2>
            @if($unlimitedExpiry)
                <div class="flex items-center space-x-3">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $hasActiveUnlimited ? 'bg-green-800 text-green-100' : 'bg-red-800 text-red-100' }}">
                        {{ $hasActiveUnlimited ? 'Active' : 'Expired' }}
                    </span>
                    <span class="text-gray-300">Expires: {{ $unlimitedExpiry->format('M j, Y') }}</span>
                </div>
                <p class="text-gray-400 text-sm mt-2">
                    {{ $hasActiveUnlimited ? 'User can book unlimited classes.' : 'Pass has expired.' }}
                </p>
            @else
                <p class="text-gray-500">No unlimited pass assigned.</p>
            @endif
        </div>

        <!-- Credits -->
        <div class="bg-gray-800 p-4 rounded-lg border border-gray-700">
            <h2 class="text-lg font-semibold text-white mb-3">Credits</h2>
            @if($user->credits > 0)
                <div class="flex items-center space-x-3">
                    <span class="text-2xl font-bold text-white">{{ $user->credits }}</span>
                    <span class="text-gray-300">credits available</span>
                </div>
                @if($creditsExpiry)
                    <div class="flex items-center space-x-3 mt-2">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $hasActiveCredits ? 'bg-green-800 text-green-100' : 'bg-red-800 text-red-100' }}">
                            {{ $hasActiveCredits ? 'Active' : 'Expired' }}
                        </span>
                        <span class="text-gray-300">Expires: {{ $creditsExpiry->format('M j, Y') }}</span>
                    </div>
                @else
                    <p class="text-gray-400 text-sm mt-2">No expiry date set for these credits.</p>
                @endif
            @else
                <p class="text-gray-500">No credits assigned.</p>
            @endif
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-white mb-4">Recent Bookings</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Class Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Booking Type</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    @forelse($user->bookings()->latest()->take(10)->get() as $booking)
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                <a href="{{ route('admin.classes.show', $booking->fitnessClass) }}" class="hover:underline">{{ $booking->fitnessClass->name }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $booking->fitnessClass->class_date->format('M j, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $booking->status === 'confirmed' ? 'bg-green-800 text-green-100' : 
                                       ($booking->status === 'cancelled' ? 'bg-red-800 text-red-100' : 'bg-yellow-800 text-yellow-100') }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                @if($booking->stripe_session_id)
                                    Stripe
                                @elseif($booking->booking_type === 'unlimited_pass')
                                    Unlimited Pass
                                @else
                                    Credits
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400">No recent bookings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="mt-8 border-t border-red-800/30 pt-6">
        <h2 class="text-lg font-semibold text-red-400 mb-3">Danger Zone</h2>
        <p class="text-gray-400 text-sm mb-4">These actions are irreversible. Please be certain.</p>
        <form method="POST" action="{{ route('admin.class-passes.destroy', $user) }}" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Are you sure you want to remove ALL class passes and credits for this user? This action cannot be undone.')"
                    class="bg-red-800 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                Remove All Passes
            </button>
        </form>
    </div>
</div>
@endsection
