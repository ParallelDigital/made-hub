@extends('admin.layout')

@section('title', 'Pricing Tier Details')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Pricing Tier Details</h1>
    <div class="flex space-x-3">
        <a href="{{ route('admin.pricing.edit', $pricing) }}" class="bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
            Edit Pricing Tier
        </a>
        <a href="{{ route('admin.pricing.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded font-semibold hover:bg-gray-700 transition-all">
            Back to Pricing
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Pricing Details -->
    <div class="lg:col-span-2">
        <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-white">{{ $pricing->name }}</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium 
                        {{ $pricing->type === 'class' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $pricing->type === 'membership' ? 'bg-purple-100 text-purple-800' : '' }}
                        {{ $pricing->type === 'package' ? 'bg-green-100 text-green-800' : '' }}">
                        {{ ucfirst($pricing->type) }}
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-400">Base Price</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">£{{ number_format($pricing->base_price, 2) }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-400">Discount</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">
                            @if($pricing->discount_percentage > 0)
                                <span class="text-red-400">{{ $pricing->discount_percentage }}%</span>
                                <div class="text-sm text-gray-400">-£{{ number_format($pricing->discount_amount, 2) }}</div>
                            @else
                                <span class="text-gray-500">None</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-400">Final Price</dt>
                        <dd class="mt-1 text-2xl font-bold text-primary">£{{ number_format($pricing->final_price, 2) }}</dd>
                        @if($pricing->savings > 0)
                            <div class="text-sm text-green-400">Save £{{ number_format($pricing->savings, 2) }}</div>
                        @endif
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                                {{ $pricing->status_text === 'Active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $pricing->status_text === 'Inactive' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $pricing->status_text === 'Scheduled' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $pricing->status_text === 'Expired' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ $pricing->status_text }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-400">Quantity Range</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">
                            {{ $pricing->min_quantity }}
                            @if($pricing->max_quantity)
                                - {{ $pricing->max_quantity }}
                            @else
                                +
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-400">Valid Period</dt>
                        <dd class="mt-1 text-sm text-gray-300">
                            @if($pricing->valid_from || $pricing->valid_until)
                                @if($pricing->valid_from)
                                    From: {{ $pricing->valid_from->format('M j, Y') }}<br>
                                @endif
                                @if($pricing->valid_until)
                                    Until: {{ $pricing->valid_until->format('M j, Y') }}
                                @endif
                            @else
                                <span class="text-gray-500">No restrictions</span>
                            @endif
                        </dd>
                    </div>
                </div>

                @if($pricing->description)
                <div class="mt-6">
                    <dt class="text-sm font-medium text-gray-400">Description</dt>
                    <dd class="mt-1 text-sm text-gray-300">{{ $pricing->description }}</dd>
                </div>
                @endif

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-400">Created</dt>
                        <dd class="mt-1 text-sm text-gray-300">{{ $pricing->created_at->format('M j, Y g:i A') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-400">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-300">{{ $pricing->updated_at->format('M j, Y g:i A') }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions & Info -->
    <div class="lg:col-span-1">
        <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-white mb-4">Actions</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('admin.pricing.edit', $pricing) }}" class="w-full bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all text-center block">
                        Edit Pricing Tier
                    </a>
                    
                    <form action="{{ route('admin.pricing.destroy', $pricing) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this pricing tier? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded font-semibold hover:bg-red-700 transition-all">
                            Delete Pricing Tier
                        </button>
                    </form>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-700">
                    <h4 class="text-sm font-medium text-gray-400 mb-3">Pricing Calculator</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Quantity</label>
                            <input type="number" id="calc_quantity" value="{{ $pricing->min_quantity }}" min="{{ $pricing->min_quantity }}" 
                                   {{ $pricing->max_quantity ? 'max=' . $pricing->max_quantity : '' }}
                                   class="w-full px-2 py-1 bg-gray-700 border border-gray-600 rounded text-white text-sm"
                                   onchange="calculateTotal()">
                        </div>
                        <div class="text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Subtotal:</span>
                                <span class="text-white" id="calc_subtotal">£{{ number_format($pricing->base_price * $pricing->min_quantity, 2) }}</span>
                            </div>
                            @if($pricing->discount_percentage > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-400">Discount:</span>
                                <span class="text-red-400" id="calc_discount">-£{{ number_format($pricing->discount_amount * $pricing->min_quantity, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between font-semibold border-t border-gray-600 pt-1 mt-1">
                                <span class="text-gray-300">Total:</span>
                                <span class="text-primary" id="calc_total">£{{ number_format($pricing->final_price * $pricing->min_quantity, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateTotal() {
    const quantity = parseInt(document.getElementById('calc_quantity').value) || {{ $pricing->min_quantity }};
    const basePrice = {{ $pricing->base_price }};
    const finalPrice = {{ $pricing->final_price }};
    const discountAmount = {{ $pricing->discount_amount }};
    
    const subtotal = basePrice * quantity;
    const discount = discountAmount * quantity;
    const total = finalPrice * quantity;
    
    document.getElementById('calc_subtotal').textContent = '£' + subtotal.toFixed(2);
    @if($pricing->discount_percentage > 0)
    document.getElementById('calc_discount').textContent = '-£' + discount.toFixed(2);
    @endif
    document.getElementById('calc_total').textContent = '£' + total.toFixed(2);
}
</script>
@endsection
