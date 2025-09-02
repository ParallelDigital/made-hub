@extends('admin.layout')

@section('title', 'Edit Pricing Tier')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Edit Pricing Tier</h1>
    <a href="{{ route('admin.pricing.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded font-semibold hover:bg-gray-700 transition-all">
        Back to Pricing
    </a>
</div>

@if ($errors->any())
    <div class="bg-red-600 text-white p-4 rounded mb-6">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-gray-800 shadow rounded-lg border border-gray-700">
    <div class="px-4 py-5 sm:p-6">
        <form action="{{ route('admin.pricing.update', $pricing) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Pricing Tier Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $pricing->name) }}" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="e.g., Early Bird Special">
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Describe this pricing tier...">{{ old('description', $pricing->description) }}</textarea>
                </div>

                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-300 mb-2">Type</label>
                    <select name="type" id="type" required
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select Type</option>
                        <option value="class" {{ old('type', $pricing->type) === 'class' ? 'selected' : '' }}>Class</option>
                        <option value="membership" {{ old('type', $pricing->type) === 'membership' ? 'selected' : '' }}>Membership</option>
                        <option value="package" {{ old('type', $pricing->type) === 'package' ? 'selected' : '' }}>Package</option>
                    </select>
                </div>

                <!-- Base Price -->
                <div>
                    <label for="base_price" class="block text-sm font-medium text-gray-300 mb-2">Base Price (£)</label>
                    <input type="number" name="base_price" id="base_price" value="{{ old('base_price', $pricing->base_price) }}" step="0.01" min="0" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="0.00" onchange="calculateFinalPrice()">
                </div>

                <!-- Discount Percentage -->
                <div>
                    <label for="discount_percentage" class="block text-sm font-medium text-gray-300 mb-2">Discount (%)</label>
                    <input type="number" name="discount_percentage" id="discount_percentage" value="{{ old('discount_percentage', $pricing->discount_percentage) }}" step="0.01" min="0" max="100"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="0" onchange="calculateFinalPrice()">
                </div>

                <!-- Final Price (calculated) -->
                <div>
                    <label for="final_price_display" class="block text-sm font-medium text-gray-300 mb-2">Final Price (£)</label>
                    <input type="text" id="final_price_display" readonly
                           class="w-full px-3 py-2 bg-gray-600 border border-gray-600 rounded-md text-gray-300"
                           placeholder="Calculated automatically">
                </div>

                <!-- Valid From -->
                <div>
                    <label for="valid_from" class="block text-sm font-medium text-gray-300 mb-2">Valid From</label>
                    <input type="date" name="valid_from" id="valid_from" value="{{ old('valid_from', $pricing->valid_from?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-400 mt-1">Leave empty for immediate activation</p>
                </div>

                <!-- Valid Until -->
                <div>
                    <label for="valid_until" class="block text-sm font-medium text-gray-300 mb-2">Valid Until</label>
                    <input type="date" name="valid_until" id="valid_until" value="{{ old('valid_until', $pricing->valid_until?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-400 mt-1">Leave empty for no expiry</p>
                </div>

                <!-- Min Quantity -->
                <div>
                    <label for="min_quantity" class="block text-sm font-medium text-gray-300 mb-2">Minimum Quantity</label>
                    <input type="number" name="min_quantity" id="min_quantity" value="{{ old('min_quantity', $pricing->min_quantity) }}" min="1" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="1">
                </div>

                <!-- Max Quantity -->
                <div>
                    <label for="max_quantity" class="block text-sm font-medium text-gray-300 mb-2">Maximum Quantity</label>
                    <input type="number" name="max_quantity" id="max_quantity" value="{{ old('max_quantity', $pricing->max_quantity) }}" min="1"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Leave empty for no limit">
                </div>

                <!-- Active Toggle -->
                <div class="flex items-center md:col-span-2">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="active" id="active" value="1" {{ old('active', $pricing->active) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary bg-gray-700 border-gray-600 rounded focus:ring-primary focus:ring-2">
                    </div>
                    <div class="ml-3">
                        <label for="active" class="text-sm font-medium text-gray-300">Active</label>
                        <p class="text-xs text-gray-400">Uncheck to disable this pricing tier</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.pricing.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded font-semibold hover:bg-gray-700 transition-all">
                    Cancel
                </a>
                <button type="submit" class="bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
                    Update Pricing Tier
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function calculateFinalPrice() {
    const basePrice = parseFloat(document.getElementById('base_price').value) || 0;
    const discountPercentage = parseFloat(document.getElementById('discount_percentage').value) || 0;
    
    const finalPrice = basePrice * (1 - discountPercentage / 100);
    document.getElementById('final_price_display').value = '£' + finalPrice.toFixed(2);
}

// Initialize calculation on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateFinalPrice();
});
</script>
@endsection
