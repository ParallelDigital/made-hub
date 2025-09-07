@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold text-white">Pricing Tiers</h1>
    <a href="{{ route('admin.pricing.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 focus:ring-offset-gray-900">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add Pricing Tier
    </a>
</div>

<div class="bg-gray-900/50 border border-gray-700/50 rounded-lg p-6 mb-8">
    <form action="{{ route('admin.pricing.index') }}" method="GET">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Search -->
            <div>
                <label for="search" class="text-sm font-medium text-gray-400">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3" 
                       placeholder="Pricing tier name...">
            </div>

            <!-- Type Filter -->
            <div>
                <label for="type" class="text-sm font-medium text-gray-400">Type</label>
                <select name="type" id="type" 
                        class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="text-sm font-medium text-gray-400">Status</label>
                <select name="status" id="status" 
                        class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="valid" {{ request('status') == 'valid' ? 'selected' : '' }}>Valid Now</option>
                </select>
            </div>

        </div>
        <div class="mt-6 pt-4 border-t border-gray-700/50 flex items-center justify-end space-x-4">
            <a href="{{ route('admin.pricing.index') }}" class="text-sm font-medium text-gray-400 hover:text-white">Clear Filters</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 focus:ring-offset-gray-900">
                Apply Filters
            </button>
        </div>
    </form>
</div>

<div class="bg-gray-800 shadow rounded-lg border border-gray-700 overflow-hidden">
    <div class="p-4 border-b border-gray-700">
        <p class="text-sm text-gray-400">Showing {{ $pricingTiers->total() }} pricing tiers</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Discount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Valid Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                @forelse($pricingTiers as $tier)
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-white">{{ $tier->name }}</div>
                            @if($tier->description)
                                <div class="text-sm text-gray-400">{{ Str::limit($tier->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst($tier->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-white">
                                @if($tier->discount_percentage > 0)
                                    <span class="line-through text-gray-400">£{{ number_format($tier->base_price, 2) }}</span>
                                    <span class="ml-2 font-semibold text-green-400">£{{ number_format($tier->final_price, 2) }}</span>
                                @else
                                    <span class="font-semibold">£{{ number_format($tier->final_price, 2) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            @if($tier->discount_percentage > 0)
                                <span class="text-green-400 font-medium">{{ $tier->discount_percentage }}% off</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            @if($tier->valid_from || $tier->valid_until)
                                <div>
                                    @if($tier->valid_from)
                                        From: {{ $tier->valid_from->format('M j, Y') }}
                                    @endif
                                </div>
                                <div>
                                    @if($tier->valid_until)
                                        Until: {{ $tier->valid_until->format('M j, Y') }}
                                    @endif
                                </div>
                            @else
                                No restrictions
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($tier->status_text === 'Active') bg-green-100 text-green-800
                                @elseif($tier->status_text === 'Scheduled') bg-yellow-100 text-yellow-800
                                @elseif($tier->status_text === 'Expired') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $tier->status_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.pricing.show', $tier) }}" class="text-purple-400 hover:text-purple-300">View</a>
                                <a href="{{ route('admin.pricing.edit', $tier) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                                <form action="{{ route('admin.pricing.destroy', $tier) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this pricing tier?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-lg font-medium">No pricing tiers found</div>
                            <div class="text-sm">Create your first pricing tier to get started</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($pricingTiers->hasPages())
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $pricingTiers->links() }}
        </div>
    @endif
</div>
@endsection
