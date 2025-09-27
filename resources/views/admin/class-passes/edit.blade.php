@extends('layouts.admin')

@section('title', 'Edit Class Pass: ' . $user->name)

@section('content')
<div class="bg-gray-900 rounded-lg border border-gray-700 p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">Edit Class Pass</h1>
            <p class="text-gray-400 text-sm mt-1">Editing passes for <a href="{{ route('admin.users.edit', $user) }}" class="text-primary hover:underline">{{ $user->name }}</a></p>
        </div>
        <a href="{{ route('admin.class-passes.show', $user) }}" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md font-medium transition-colors">
            Back to Details
        </a>
    </div>

    <form method="POST" action="{{ route('admin.class-passes.update', $user) }}">
        @csrf
        @method('PATCH')
        <div class="space-y-6">
            <!-- Action Selection -->
            <div>
                <label for="action" class="block text-sm font-medium text-gray-300 mb-1">Action</label>
                <select id="action" name="action" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">-- Select an Action --</option>
                    <option value="extend_unlimited">Extend Unlimited Pass</option>
                    <option value="add_credits">Add/Extend Credits</option>
                    <option value="expire_unlimited">Manually Expire Unlimited Pass</option>
                    <option value="expire_credits">Manually Expire Credits</option>
                </select>
            </div>

            <!-- Conditional Fields -->
            <div id="conditional-fields" class="space-y-6 hidden">
                <!-- Expiry Date -->
                <div id="expires_at_section">
                    <label for="expires_at" class="block text-sm font-medium text-gray-300 mb-1">New Expiry Date</label>
                    <input type="date" id="expires_at" name="expires_at" value="{{ now()->addMonth()->format('Y-m-d') }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <!-- Credits Amount -->
                <div id="credits_amount_section">
                    <label for="credits_amount" class="block text-sm font-medium text-gray-300 mb-1">Credits to Add</label>
                    <input type="number" id="credits_amount" name="credits_amount" min="1" max="100" value="10"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="border-t border-gray-700 pt-6">
                <button type="submit" class="bg-primary hover:bg-purple-400 text-black px-6 py-2 rounded-md font-medium transition-colors">
                    Update Pass
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const actionSelect = document.getElementById('action');
    const conditionalFields = document.getElementById('conditional-fields');
    const expiresAtSection = document.getElementById('expires_at_section');
    const creditsAmountSection = document.getElementById('credits_amount_section');

    actionSelect.addEventListener('change', function() {
        const selectedAction = this.value;
        
        // Hide all conditional sections first
        conditionalFields.classList.add('hidden');
        expiresAtSection.classList.add('hidden');
        creditsAmountSection.classList.add('hidden');

        if (selectedAction === 'extend_unlimited') {
            conditionalFields.classList.remove('hidden');
            expiresAtSection.classList.remove('hidden');
        } else if (selectedAction === 'add_credits') {
            conditionalFields.classList.remove('hidden');
            expiresAtSection.classList.remove('hidden');
            creditsAmountSection.classList.remove('hidden');
        } else if (selectedAction === 'expire_unlimited' || selectedAction === 'expire_credits') {
            // No extra fields needed, but show the container to enable the submit button area
            conditionalFields.classList.remove('hidden');
        }
    });
});
</script>
@endpush
