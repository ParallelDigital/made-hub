@extends('layouts.admin')

@section('title', 'Memberships')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Memberships</h1>
    <a href="{{ route('admin.memberships.create') }}" class="bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
        Add New Membership
    </a>
</div>

@if(session('success'))
    <div class="bg-green-600 text-white p-4 rounded mb-6">
        {{ session('success') }}
    </div>
@endif

<div class="bg-gray-800 shadow rounded-lg border border-gray-700">
    <div class="px-4 py-5 sm:p-6">
        @if($memberships->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Credits</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($memberships as $membership)
                        <tr class="hover:bg-gray-700 cursor-pointer transition-colors" onclick="window.location='{{ route('admin.memberships.show', $membership) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">{{ $membership->name }}</div>
                                @if($membership->description)
                                    <div class="text-sm text-gray-400">{{ Str::limit($membership->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                £{{ number_format($membership->price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $membership->duration_text }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                @if($membership->unlimited)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Unlimited
                                    </span>
                                @else
                                    {{ $membership->class_credits ?? 'N/A' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($membership->active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2" onclick="event.stopPropagation()">
                                    <a href="{{ route('admin.memberships.edit', $membership) }}" class="text-primary hover:text-primary-dark">Edit</a>
                                    <form action="{{ route('admin.memberships.destroy', $membership) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this membership?')">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-white mb-2">No memberships found</h3>
                <p class="text-gray-400 mb-4">Get started by creating your first membership plan.</p>
                <a href="{{ route('admin.memberships.create') }}" class="bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
                    Add New Membership
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
