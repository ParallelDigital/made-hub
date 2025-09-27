@extends('layouts.admin')

@section('title', 'Class Pass Details: ' . $user->name)

@section('content')
@php
    $activeUnlimitedPass = $user->passes()->where('pass_type', 'unlimited')->where('expires_at', '>=', now()->toDateString())->orderBy('expires_at', 'desc')->first();
    $totalCredits = $user->getNonMemberAvailableCredits();
@endphp

<div class="bg-gray-900 rounded-lg border border-gray-700 p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">Class Pass Details</h1>
            <p class="text-gray-400 text-sm mt-1">Viewing passes for <a href="{{ route('admin.users.edit', $user) }}" class="text-primary hover:underline">{{ $user->name }}</a></p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.class-passes.index') }}" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">Back to List</a>
            <a href="{{ route('admin.class-passes.edit', $user) }}" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-md font-medium transition-colors">Edit Pass</a>
        </div>
    </div>

    <!-- Current Status -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gray-800 p-4 rounded-lg border border-gray-700">
            <h2 class="text-lg font-semibold text-white mb-3">Active Unlimited Pass</h2>
            @if($activeUnlimitedPass)
                <div class="flex items-center space-x-3">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-800 text-green-100">Active</span>
                    <span class="text-gray-300">Expires: {{ $activeUnlimitedPass->expires_at->format('M j, Y') }}</span>
                </div>
                <p class="text-gray-400 text-sm mt-2">Source: {{ Str::title(str_replace('_', ' ', $activeUnlimitedPass->source)) }} on {{ $activeUnlimitedPass->created_at->format('M j, Y') }}</p>
            @else
                <p class="text-gray-500">No active unlimited pass.</p>
            @endif
        </div>

        <div class="bg-gray-800 p-4 rounded-lg border border-gray-700">
            <h2 class="text-lg font-semibold text-white mb-3">Available Credits</h2>
            <div class="flex items-center space-x-3">
                <span class="text-2xl font-bold text-white">{{ $totalCredits }}</span>
                <span class="text-gray-300">credits</span>
            </div>
            <p class="text-gray-400 text-sm mt-2">Total from all active credit passes.</p>
        </div>
    </div>

    <!-- Pass History -->
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-white mb-4">Pass History</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Credits</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Expires At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date Acquired</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    @forelse($user->passes()->latest()->get() as $pass)
                        <tr class="hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $pass->pass_type === 'unlimited' ? 'text-purple-300' : 'text-blue-300' }}">
                                {{ Str::title($pass->pass_type) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                {{ $pass->credits ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $pass->expires_at ? $pass->expires_at->format('M j, Y') : 'Never' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                {{ Str::title(str_replace('_', ' ', $pass->source)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                {{ $pass->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($pass->expires_at && $pass->expires_at->isFuture())
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-800 text-green-100">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-800 text-red-100">Expired</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">No pass history found.</td>
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
