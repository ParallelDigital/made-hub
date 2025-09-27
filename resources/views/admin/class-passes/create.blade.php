@extends('layouts.admin')

@section('title', 'Add New Class Pass')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-control {
        background-color: #374151 !important;
        border-color: #4b5563 !important;
        color: #fff !important;
    }
    .ts-dropdown {
        background-color: #374151 !important;
        border-color: #4b5563 !important;
    }
    .ts-dropdown .option {
        color: #d1d5db;
    }
    .ts-dropdown .option:hover, .ts-dropdown .active {
        background-color: #4b5563 !important;
        color: #fff !important;
    }
</style>
@endpush

@section('content')
<div class="bg-gray-900 rounded-lg border border-gray-700 p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">Add New Class Pass</h1>
            <p class="text-gray-400 text-sm mt-1">Assign an unlimited pass or credits to a user.</p>
        </div>
        <a href="{{ route('admin.class-passes.index') }}" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
            Back to List
        </a>
    </div>

    <form method="POST" action="{{ route('admin.class-passes.store') }}">
        @csrf
        <div class="space-y-6">
            <!-- User Selection -->
            <div>
                <label for="user_email" class="block text-sm font-medium text-gray-300 mb-1">User</label>
                <select id="user_email" name="user_email" required placeholder="Search for a user by name or email..."></select>
                @error('user_email')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pass Type -->
            <div>
                <label for="pass_type" class="block text-sm font-medium text-gray-300 mb-1">Pass Type</label>
                <select id="pass_type" name="pass_type" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="unlimited">Unlimited Pass</option>
                    <option value="credits">Credit Bundle</option>
                </select>
            </div>

            <!-- Credits Amount (Conditional) -->
            <div id="credits-section" class="hidden">
                <label for="credits_amount" class="block text-sm font-medium text-gray-300 mb-1">Number of Credits</label>
                <input type="number" id="credits_amount" name="credits_amount" min="1" max="100" value="10"
                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                @error('credits_amount')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Expiry Date -->
            <div>
                <label for="expires_at" class="block text-sm font-medium text-gray-300 mb-1">Expires At</label>
                <input type="date" id="expires_at" name="expires_at" required value="{{ now()->addMonth()->format('Y-m-d') }}"
                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                @error('expires_at')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="border-t border-gray-700 pt-6">
                <button type="submit" class="bg-primary hover:bg-purple-400 text-black px-6 py-2 rounded-md font-medium transition-colors">
                    Add Pass
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pass Type Toggle
    const passTypeSelect = document.getElementById('pass_type');
    const creditsSection = document.getElementById('credits-section');
    passTypeSelect.addEventListener('change', function() {
        creditsSection.classList.toggle('hidden', this.value !== 'credits');
    });

    // Tom-Select for User Search
    new TomSelect('#user_email', {
        valueField: 'email',
        labelField: 'display',
        searchField: ['name', 'email'],
        maxItems: 1,
        create: false,
        load: function(query, callback) {
            if (!query.length) return callback();
            fetch(`{{ route('admin.class-passes.users.suggest') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(json => {
                    callback(json);
                }).catch(()=>{
                    callback();
                });
        },
        render: {
            option: function(item, escape) {
                return `<div><span class="font-medium">${escape(item.name)}</span> <span class="text-gray-400">(${escape(item.email)})</span></div>`;
            },
            item: function(item, escape) {
                return `<div><span class="font-medium">${escape(item.name)}</span> <span class="text-gray-400">(${escape(item.email)})</span></div>`;
            }
        }
    });
});
</script>
@endpush
