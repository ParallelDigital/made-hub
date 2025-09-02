@extends('layouts.admin')

@section('title', 'Membership Details')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-white">Membership Details</h1>
    <div class="flex space-x-3">
        <a href="{{ route('admin.memberships.edit', $membership) }}" class="bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
            Edit Membership
        </a>
        <a href="{{ route('admin.memberships.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded font-semibold hover:bg-gray-700 transition-all">
            Back to Memberships
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Membership Details -->
    <div class="lg:col-span-2">
        <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-white mb-4">{{ $membership->name }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-400">Price</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">£{{ number_format($membership->price, 2) }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-400">Duration</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">{{ $membership->duration_text }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-400">Class Credits</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">
                            @if($membership->unlimited)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    Unlimited
                                </span>
                            @else
                                {{ $membership->class_credits ?? 'N/A' }}
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-400">Status</dt>
                        <dd class="mt-1">
                            @if($membership->active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </dd>
                    </div>
                </div>

                @if($membership->description)
                <div class="mt-6">
                    <dt class="text-sm font-medium text-gray-400">Description</dt>
                    <dd class="mt-1 text-sm text-gray-300">{{ $membership->description }}</dd>
                </div>
                @endif

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-400">Created</dt>
                        <dd class="mt-1 text-sm text-gray-300">{{ $membership->created_at->format('M j, Y g:i A') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-400">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-300">{{ $membership->updated_at->format('M j, Y g:i A') }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="lg:col-span-1">
        <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-white mb-4">Actions</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('admin.memberships.edit', $membership) }}" class="w-full bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all text-center block">
                        Edit Membership
                    </a>
                    
                    <form action="{{ route('admin.memberships.destroy', $membership) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this membership? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded font-semibold hover:bg-red-700 transition-all">
                            Delete Membership
                        </button>
                    </form>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-700">
                    <h4 class="text-sm font-medium text-gray-400 mb-2">Quick Stats</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Active Members:</span>
                            <span class="text-white">{{ $membership->users()->wherePivot('status', 'active')->count() }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Total Members:</span>
                            <span class="text-white">{{ $membership->users()->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
