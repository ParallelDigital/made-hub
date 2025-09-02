@extends('admin.layout')

@section('title', 'Pricing Management')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Pricing Management</h1>
    <a href="{{ route('admin.pricing.create') }}" class="bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
        Add New Pricing Tier
    </a>
</div>

@if(session('success'))
    <div class="bg-green-600 text-white p-4 rounded mb-6">
        {{ session('success') }}
    </div>
@endif

<!-- Filter Tabs -->
<div class="mb-6">
    <div class="border-b border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <button class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm" onclick="filterByType('all')">
                All Pricing
            </button>
            <button class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm" onclick="filterByType('class')">
                Class Pricing
            </button>
            <button class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm" onclick="filterByType('membership')">
                Membership Pricing
            </button>
            <button class="border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm" onclick="filterByType('package')">
                Package Pricing
            </button>
        </nav>
    </div>
</div>

<div class="bg-gray-800 shadow rounded-lg border border-gray-700">
    <div class="px-4 py-5 sm:p-6">
        @if($pricingTiers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Base Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Discount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Final Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Valid Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($pricingTiers as $tier)
                        <tr class="hover:bg-gray-700 cursor-pointer transition-colors pricing-row" data-type="{{ $tier->type }}" onclick="window.location='{{ route('admin.pricing.show', $tier) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">{{ $tier->name }}</div>
                                @if($tier->description)
                                    <div class="text-sm text-gray-400">{{ Str::limit($tier->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $tier->type === 'class' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $tier->type === 'membership' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $tier->type === 'package' ? 'bg-green-100 text-green-800' : '' }}">
                                    {{ ucfirst($tier->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                £{{ number_format($tier->base_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                @if($tier->discount_percentage > 0)
                                    <span class="text-red-400">{{ $tier->discount_percentage }}%</span>
                                @else
                                    <span class="text-gray-500">None</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-white">
                                £{{ number_format($tier->final_price, 2) }}
                                @if($tier->savings > 0)
                                    <div class="text-xs text-green-400">Save £{{ number_format($tier->savings, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                @if($tier->valid_from || $tier->valid_until)
                                    <div>
                                        @if($tier->valid_from)
                                            From: {{ $tier->valid_from->format('M j, Y') }}
                                        @endif
                                        @if($tier->valid_until)
                                            <br>Until: {{ $tier->valid_until->format('M j, Y') }}
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500">No expiry</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $tier->status_text === 'Active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $tier->status_text === 'Inactive' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $tier->status_text === 'Scheduled' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $tier->status_text === 'Expired' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ $tier->status_text }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2" onclick="event.stopPropagation()">
                                    <a href="{{ route('admin.pricing.edit', $tier) }}" class="text-primary hover:text-primary-dark">Edit</a>
                                    <form action="{{ route('admin.pricing.destroy', $tier) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this pricing tier?')">
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
        @else
            <div class="text-center py-12">
                <div class="text-gray-400 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-white mb-2">No pricing tiers found</h3>
                <p class="text-gray-400 mb-4">Get started by creating your first pricing tier.</p>
                <a href="{{ route('admin.pricing.create') }}" class="bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
                    Add New Pricing Tier
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function filterByType(type) {
    const rows = document.querySelectorAll('.pricing-row');
    const tabs = document.querySelectorAll('nav button');
    
    // Update tab styles
    tabs.forEach(tab => {
        tab.classList.remove('border-primary', 'text-white');
        tab.classList.add('border-transparent', 'text-gray-400');
    });
    
    event.target.classList.remove('border-transparent', 'text-gray-400');
    event.target.classList.add('border-primary', 'text-white');
    
    // Filter rows
    rows.forEach(row => {
        if (type === 'all' || row.dataset.type === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Initialize first tab as active
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('nav button').click();
});
</script>
@endsection
