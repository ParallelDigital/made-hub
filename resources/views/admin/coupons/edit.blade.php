@extends('layouts.admin')

@section('title', 'Edit Coupon')

@section('content')
<div class="bg-gray-800 p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold text-white mb-6">Edit Coupon</h2>

    @if ($errors->any())
        <div class="bg-red-900 border border-red-600 text-red-100 px-4 py-3 rounded-lg mb-4">
            <strong class="font-bold">Whoops!</strong>
            <span class="block sm:inline">There were some problems with your input.</span>
            <ul class="mt-3 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="code" class="block text-sm font-medium text-gray-300 mb-1">Coupon Code</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" /></svg>
                </div>
                <input type="text" name="code" id="code" class="block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-primary focus:border-primary pl-10 py-2.5" value="{{ old('code', $coupon->code) }}" required placeholder="e.g., SUMMER25">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-300 mb-1">Type</label>
                <select name="type" id="type" class="block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-primary focus:border-primary py-2.5" required>
                    <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Fixed Amount (£)</option>
                    <option value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                </select>
            </div>
            <div>
                <label for="value" class="block text-sm font-medium text-gray-300 mb-1">Value</label>
                <div class="relative">
                     <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 7.756a4.5 4.5 0 100 8.488M7.5 10.5h5.25m-5.25 3h5.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <input type="number" name="value" id="value" step="0.01" class="block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-primary focus:border-primary pl-10 py-2.5" value="{{ old('value', $coupon->value) }}" required placeholder="e.g., 10.50 or 25">
                </div>
            </div>
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-300 mb-1">Status</label>
            <select name="status" id="status" class="block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-primary focus:border-primary py-2.5" required>
                <option value="active" {{ old('status', $coupon->status) == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $coupon->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="mt-8 flex justify-end space-x-4 border-t border-gray-700 pt-6">
            <a href="{{ route('admin.coupons.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-2.5 rounded-md text-sm font-medium transition-colors">Cancel</a>
            <button type="submit" class="bg-primary hover:bg-opacity-90 text-black px-5 py-2.5 rounded-md text-sm font-semibold transition-colors shadow-md">Update Coupon</button>
        </div>
    </form>
</div>
@endsection
