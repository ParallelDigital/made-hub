@extends('admin.layout')

@section('title', 'Manage Coupons')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Manage Coupons</h1>
    <a href="{{ route('admin.coupons.create') }}" class="bg-primary hover:bg-primary-dark text-black font-bold py-2 px-4 rounded-lg transition-colors">
        Add New Coupon
    </a>
</div>

<div class="bg-gray-800 shadow rounded-lg border border-gray-700">
    <div class="px-4 py-5 sm:p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    @forelse($coupons as $coupon)
                    <tr class="hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ $coupon->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ ucfirst($coupon->type) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            {{ $coupon->type === 'percentage' ? $coupon->value . '%' : '£' . number_format($coupon->value, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClass = $coupon->status === 'active' ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                {{ ucfirst($coupon->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="text-indigo-400 hover:text-indigo-300">Edit</a>
                            <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-400">No coupons found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $coupons->links() }}
        </div>
    </div>
</div>
@endsection
