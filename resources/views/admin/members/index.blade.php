@extends('layouts.admin')

@section('title', 'Members')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold text-white">Members</h1>
</div>

<div class="bg-gray-900/50 border border-gray-700/50 rounded-lg p-6 mb-6">
    <form action="{{ route('admin.members.index') }}" method="GET" id="membersFilter">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label for="search" class="text-sm font-medium text-gray-400">Search</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}"
                    class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3"
                    placeholder="Name or email...">
            </div>
            <div>
                <label for="status" class="text-sm font-medium text-gray-400">Status</label>
                <select id="status" name="status"
                    class="mt-2 block w-full bg-gray-800 border-gray-600 text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm h-10 px-3">
                    <option value="active" {{ (request('status', $status) === 'active') ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ (request('status', $status) === 'inactive') ? 'selected' : '' }}>Inactive</option>
                    <option value="all" {{ (request('status', $status) === 'all') ? 'selected' : '' }}>All</option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-3">
            <a href="{{ route('admin.members.index') }}" class="text-sm text-gray-400 hover:text-white">Clear</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm font-semibold shadow-sm">Apply</button>
        </div>
    </form>
</div>

<div class="bg-gray-800 shadow rounded-lg border border-gray-700 overflow-hidden">
    <div class="p-4 border-b border-gray-700">
        <p class="text-sm text-gray-400">Showing {{ $members->total() }} {{ \Illuminate\Support\Str::plural('member', $members->total()) }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Membership</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Monthly Credits</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Last Refreshed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Next Reset</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Membership Ends</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                @forelse($members as $u)
                    @php
                        // Determine active status: either active Stripe subscription OR valid local membership window
                        $hasActiveStripe = in_array($u->subscription_status, ['active', 'trialing'], true);
                        $hasActiveMembership = $u->membership_start_date && $u->membership_start_date <= now() && (!$u->membership_end_date || $u->membership_end_date >= now());
                        $isActive = $hasActiveStripe || $hasActiveMembership;

                        // Status label clarifies the source where helpful
                        if ($isActive) {
                            $statusLabel = $hasActiveStripe ? 'Active (Stripe)' : 'Active';
                            $statusClass = 'bg-green-100 text-green-800';
                        } else {
                            $statusLabel = 'Inactive';
                            $statusClass = 'bg-gray-100 text-gray-800';
                        }

                        $lastRef = $u->credits_last_refreshed ? \Carbon\Carbon::parse($u->credits_last_refreshed)->format('D, M j, Y') : '—';
                        $nextReset = '—';
                        if (!empty($u->stripe_subscription_id)) {
                            $nextReset = 'On renewal';
                        } else {
                            $nextReset = \Carbon\Carbon::now()->startOfMonth()->addMonth()->format('D, M j, Y');
                        }
                        $membershipEnds = $u->membership_end_date ? \Carbon\Carbon::parse($u->membership_end_date)->format('D, M j, Y') : '—';
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-white">{{ $u->name ?: ($u->first_name.' '.$u->last_name) ?: '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $u->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $u->membership?->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ (int)($u->monthly_credits ?? 0) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $lastRef }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $nextReset }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $membershipEnds }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                            <div class="text-lg font-medium">No members found</div>
                            <div class="text-sm">Try changing your filters or search query</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($members->hasPages())
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $members->links() }}
        </div>
    @endif
</div>
@endsection
