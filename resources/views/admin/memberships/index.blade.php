@extends('layouts.admin')

@section('title', 'Member Subscriptions')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Member Subscriptions</h1>
    <div class="flex items-center gap-3">
        @isset($stripeMode)
            <span class="text-xs px-2 py-1 rounded bg-gray-700 text-gray-300">Mode: {{ strtoupper($stripeMode) }}</span>
        @endisset
        <a href="{{ route('admin.memberships.export', request()->query()) }}"
           class="inline-flex items-center px-4 py-2 bg-primary hover:bg-purple-400 text-white rounded-md text-sm font-semibold shadow-sm">
            Export CSV
        </a>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-600 text-white p-4 rounded mb-6">
        {{ session('success') }}
    </div>
@endif

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Total Members -->
    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
        <label class="text-sm font-medium text-gray-400">Total Members</label>
        <p class="text-3xl font-bold text-white mt-1">{{ $totalMembers }}</p>
    </div>
    <!-- Active Members -->
    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
        <label class="text-sm font-medium text-gray-400">Active Members</label>
        <p class="text-3xl font-bold text-white mt-1">{{ $activeMembersCount }}</p>
    </div>
    <!-- Monthly Revenue -->
    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
        <label class="text-sm font-medium text-gray-400">Monthly Revenue</label>
        <p class="text-3xl font-bold text-white mt-1">£{{ number_format($monthlyRevenue, 0) }}</p>
    </div>
</div>

<!-- Filters -->
<div class="flex justify-end items-center mb-6">
    <div class="flex items-center gap-4">
        <form action="{{ route('admin.memberships.index') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <label for="per_page" class="text-sm text-gray-400">Per Page:</label>
            <select name="per_page" id="per_page" onchange="this.form.submit()" class="bg-gray-700 text-white text-sm rounded-md border-gray-600 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="20" @if($perPage == 20) selected @endif>20</option>
                <option value="50" @if($perPage == 50) selected @endif>50</option>
                <option value="100" @if($perPage == 100) selected @endif>100</option>
            </select>
        </form>
        <form action="{{ route('admin.memberships.index') }}" method="GET">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <x-forms.select name="status" :options="$statusOptions" :selected="$statusFilter" />
        </form>
    </div>
</div>

<!-- Stripe Subscriptions Table -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-8">
    <div class="px-4 py-5 sm:p-6">
        @if(isset($stripeError))
            <div class="text-red-400">Error: {{ $stripeError }}</div>
        @elseif(isset($stripeMembers) && $stripeMembers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Months Active</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($stripeMembers as $m)
                        <tr class="hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">{{ $m['name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $m['email'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $m['months_active'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $status = $m['status'];
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'trialing' => 'bg-blue-100 text-blue-800',
                                        'inactive' => 'bg-red-100 text-red-800',
                                        'canceled' => 'bg-gray-100 text-gray-800',
                                        'default' => 'bg-yellow-100 text-yellow-800',
                                    ];
                                    $colorClass = $statusColors[$status] ?? $statusColors['default'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $stripeMembers->links() }}
            </div>
        @else
            <div class="text-gray-400">
                No Stripe subscriptions found for the selected filter.
            </div>
        @endif
    </div>
</div>
@endsection
