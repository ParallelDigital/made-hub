@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-white">Reports & Analytics</h2>
    <p class="mt-1 text-sm text-gray-400">Overview of bookings, revenue, and class performance</p>
</div>

<!-- Key Metrics -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8">
    <!-- Total Revenue -->
    <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-400 truncate">Total Revenue</dt>
                        <dd class="text-lg font-medium text-white">£{{ number_format($totalRevenue, 2) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Bookings -->
    <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-400 truncate">Total Confirmed Bookings</dt>
                        <dd class="text-lg font-medium text-white">{{ number_format($totalBookings) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Per Booking -->
    <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-400 truncate">Average Per Paid Booking</dt>
                        <dd class="text-lg font-medium text-white">
                            @php
                                $paidBookings = \App\Models\Booking::whereNotNull('stripe_session_id')->where('status', 'confirmed')->count();
                                $avgRevenue = $paidBookings > 0 ? $totalRevenue / $paidBookings : 0;
                            @endphp
                            £{{ number_format($avgRevenue, 2) }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top 3 Most Booked Classes -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-8">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-white mb-4">Top 3 Most Booked Classes</h3>
        
        @if($topClasses->count() > 0)
            <div class="space-y-4">
                @foreach($topClasses as $index => $item)
                    @php
                        $class = $item['class'];
                        $count = $item['booking_count'];
                        $badgeColors = ['bg-yellow-500', 'bg-gray-400', 'bg-orange-600'];
                        $badgeColor = $badgeColors[$index] ?? 'bg-gray-600';
                    @endphp
                    <div class="flex items-center justify-between p-4 bg-gray-700 rounded-lg border border-gray-600 hover:border-primary transition-colors">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full {{ $badgeColor }} text-white font-bold">
                                    #{{ $index + 1 }}
                                </span>
                            </div>
                            <div>
                                <h4 class="text-white font-medium">{{ $class->name ?? 'Unknown Class' }}</h4>
                                <div class="flex items-center space-x-3 text-sm text-gray-400 mt-1">
                                    <span>Instructor: {{ $class->instructor->name ?? 'N/A' }}</span>
                                    <span>•</span>
                                    <span>{{ $class->classType->name ?? 'Uncategorized' }}</span>
                                    <span>•</span>
                                    <span>£{{ number_format($class->price ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-primary">{{ $count }}</div>
                            <div class="text-xs text-gray-400">bookings</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-300">No bookings yet</h3>
                <p class="mt-1 text-sm text-gray-400">Classes will appear here once bookings are made.</p>
            </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Top User -->
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-white mb-4">Most Active User</h3>
            
            @if($topUser)
                <div class="flex items-center justify-between p-4 bg-gray-700 rounded-lg border border-gray-600">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div class="h-12 w-12 rounded-full bg-primary flex items-center justify-center">
                                <span class="text-lg font-medium text-white">
                                    {{ strtoupper(substr($topUser->user->name ?? 'U', 0, 1)) }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-white font-medium">{{ $topUser->user->name ?? 'Unknown User' }}</h4>
                            <div class="text-sm text-gray-400">{{ $topUser->user->email ?? '' }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-primary">{{ $topUser->booking_count }}</div>
                        <div class="text-xs text-gray-400">bookings</div>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-300">No users yet</h3>
                </div>
            @endif
        </div>
    </div>

    <!-- Class Type Distribution -->
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-white mb-4">Class Type Distribution</h3>
            
            @if($classTypeDistribution->count() > 0)
                <div class="space-y-3">
                    @foreach($classTypeDistribution as $typeData)
                        @php
                            $percentage = $totalBookings > 0 ? ($typeData->booking_count / $totalBookings) * 100 : 0;
                            $typeName = $typeData->type ?? 'Uncategorized';
                        @endphp
                        @if($typeData->booking_count > 0)
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-gray-300">{{ $typeName }}</span>
                                <span class="text-sm text-gray-400">{{ $typeData->booking_count }} ({{ number_format($percentage, 1) }}%)</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-300">No data available</h3>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Monthly Revenue Trend -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-8">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-white mb-4">Monthly Revenue (Last 6 Months)</h3>
        
        @if($monthlyRevenue->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Bookings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Avg per Booking</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($monthlyRevenue as $month)
                            <tr class="hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                    {{ \Carbon\Carbon::parse($month->month . '-01')->format('F Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                    £{{ number_format($month->revenue, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                    {{ $month->bookings }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                    £{{ number_format($month->revenue / $month->bookings, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-300">No revenue data yet</h3>
                <p class="mt-1 text-sm text-gray-400">Monthly revenue will appear here once paid bookings are made.</p>
            </div>
        @endif
    </div>
</div>

<!-- Top 5 Users -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-white mb-4">Top 5 Most Active Users</h3>
        
        @if($topUsers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Total Bookings</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($topUsers as $index => $userData)
                            <tr class="hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full 
                                        @if($index === 0) bg-yellow-500
                                        @elseif($index === 1) bg-gray-400
                                        @elseif($index === 2) bg-orange-600
                                        @else bg-gray-600
                                        @endif text-white font-bold text-sm">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center">
                                                <span class="text-xs font-medium text-white">
                                                    {{ strtoupper(substr($userData->user->name ?? 'U', 0, 1)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-white">{{ $userData->user->name ?? 'Unknown User' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                    {{ $userData->user->email ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-lg font-bold text-primary">{{ $userData->booking_count }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-300">No users yet</h3>
                <p class="mt-1 text-sm text-gray-400">Top users will appear here once bookings are made.</p>
            </div>
        @endif
    </div>
</div>
@endsection
