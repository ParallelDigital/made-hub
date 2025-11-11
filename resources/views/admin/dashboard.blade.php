@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

@if(session('success'))
<div class="mb-6 bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg relative" role="alert">
    <div class="flex items-start">
        <svg class="h-5 w-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <div class="flex-1">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
            
            @if(session('sent_count'))
            <div class="mt-3 text-sm">
                <p class="font-semibold">📊 Summary:</p>
                <p>✅ Sent: {{ session('sent_count') }} emails</p>
                @if(session('failed_count') > 0)
                <p class="text-red-300">❌ Failed: {{ session('failed_count') }} emails</p>
                @endif
                
                @if(session('sent_emails') && count(session('sent_emails')) > 0)
                <details class="mt-2">
                    <summary class="cursor-pointer hover:text-green-100">View sent emails ({{ count(session('sent_emails')) }})</summary>
                    <ul class="mt-2 ml-4 text-xs max-h-40 overflow-y-auto">
                        @foreach(session('sent_emails') as $email)
                        <li>• {{ $email }}</li>
                        @endforeach
                    </ul>
                </details>
                @endif
                
                @if(session('failed_emails') && count(session('failed_emails')) > 0)
                <details class="mt-2">
                    <summary class="cursor-pointer hover:text-red-200 text-red-300">View failed emails ({{ count(session('failed_emails')) }})</summary>
                    <ul class="mt-2 ml-4 text-xs max-h-40 overflow-y-auto">
                        @foreach(session('failed_emails') as $email)
                        <li>• {{ $email }}</li>
                        @endforeach
                    </ul>
                </details>
                @endif
            </div>
            @endif
            
            @if(session('updated_count'))
            <div class="mt-3 text-sm">
                <p class="font-semibold">📊 Summary:</p>
                <p>✅ Updated: {{ session('updated_count') }} member passwords</p>
                <p class="mt-1 text-xs">New password: <strong class="text-amber-300">Made2025!</strong></p>
                
                @if(session('updated_users') && count(session('updated_users')) > 0)
                <details class="mt-2">
                    <summary class="cursor-pointer hover:text-green-100">View updated members ({{ count(session('updated_users')) }})</summary>
                    <ul class="mt-2 ml-4 text-xs max-h-40 overflow-y-auto">
                        @foreach(session('updated_users') as $user)
                        <li>• {{ $user }}</li>
                        @endforeach
                    </ul>
                </details>
                @endif
            </div>
            @endif
            
            @if(session('created_count') !== null)
            <div class="mt-3 text-sm">
                <p class="font-semibold">📊 Stripe Sync Summary:</p>
                @if(session('created_count') > 0)
                <p>✅ Created: {{ session('created_count') }} new member accounts</p>
                @endif
                @if(session('updated_count') > 0)
                <p>🔄 Updated: {{ session('updated_count') }} existing accounts with membership access</p>
                @endif
                @if(session('created_count') == 0 && session('updated_count') == 0)
                <p>ℹ️ All Stripe members already have accounts with proper access</p>
                @endif
                <p class="mt-1 text-xs">Default password for new accounts: <strong class="text-green-300">Made2025!</strong></p>
                
                @if(session('created_users') && count(session('created_users')) > 0)
                <details class="mt-2">
                    <summary class="cursor-pointer hover:text-green-100">View created accounts ({{ count(session('created_users')) }})</summary>
                    <ul class="mt-2 ml-4 text-xs max-h-40 overflow-y-auto">
                        @foreach(session('created_users') as $user)
                        <li>• {{ $user }}</li>
                        @endforeach
                    </ul>
                </details>
                @endif
                
                @if(session('updated_users') && count(session('updated_users')) > 0)
                <details class="mt-2">
                    <summary class="cursor-pointer hover:text-green-100">View updated accounts ({{ count(session('updated_users')) }})</summary>
                    <ul class="mt-2 ml-4 text-xs max-h-40 overflow-y-auto">
                        @foreach(session('updated_users') as $user)
                        <li>• {{ $user }}</li>
                        @endforeach
                    </ul>
                </details>
                @endif
            </div>
            @endif
            
            @if(session('migrated_count'))
            <div class="mt-3 text-sm">
                <p class="font-semibold">📊 Role Migration Summary:</p>
                <p>✅ Migrated: {{ session('migrated_count') }} users to 'subscriber' role</p>
                <p class="mt-1 text-xs">All "user" and "member" roles have been converted to "subscriber"</p>
                
                @if(session('migrated_users') && count(session('migrated_users')) > 0)
                <details class="mt-2">
                    <summary class="cursor-pointer hover:text-green-100">View migrated users ({{ count(session('migrated_users')) }})</summary>
                    <ul class="mt-2 ml-4 text-xs max-h-40 overflow-y-auto">
                        @foreach(session('migrated_users') as $user)
                        <li>• {{ $user }}</li>
                        @endforeach
                    </ul>
                </details>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="mb-6 bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg relative" role="alert">
    <strong class="font-bold">Error!</strong>
    <span class="block sm:inline">{{ session('error') }}</span>
</div>
@endif

<div class="admin-stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-400 truncate">Total Members</dt>
                        <dd class="text-lg font-medium text-white">{{ $stats['total_users'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-400 truncate">Instructors</dt>
                        <dd class="text-lg font-medium text-white">{{ $stats['total_instructors'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-400 truncate">Classes</dt>
                        <dd class="text-lg font-medium text-white">{{ $stats['total_classes'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-700 cursor-pointer hover:border-primary transition-colors" onclick="window.location='{{ route('admin.bookings.index') }}'" role="button" tabindex="0">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-400 truncate">Bookings</dt>
                        <dd class="text-lg font-medium text-white">{{ $stats['total_bookings'] }}</dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('admin.bookings.index') }}" role="button" class="inline-flex items-center min-h-[44px] text-primary text-sm hover:text-purple-400">View bookings →</a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-8">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-white mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-3">
            <form action="{{ route('admin.send-welcome-emails') }}" method="POST" id="sendWelcomeEmailsForm">
                @csrf
                <button type="button" onclick="confirmSendEmails()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Send Welcome Emails to All Members
                </button>
            </form>
            
            <form action="{{ route('admin.reset-member-passwords') }}" method="POST" id="resetPasswordsForm">
                @csrf
                <button type="button" onclick="confirmResetPasswords()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    Reset All Member Passwords to Made2025!
                </button>
            </form>
            
            <form action="{{ route('admin.create-member-accounts') }}" method="POST" id="createAccountsForm">
                @csrf
                <button type="button" onclick="confirmCreateAccounts()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Sync Stripe Members
                </button>
            </form>
            
            <form action="{{ route('admin.migrate-to-subscriber-role') }}" method="POST" id="migrateRolesForm">
                @csrf
                <button type="button" onclick="confirmMigrateRoles()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    Setup Subscriber Role & Member Membership
                </button>
            </form>
            
            <form action="{{ route('admin.refresh-member-credits') }}" method="POST" id="refreshCreditsForm">
                @csrf
                <button type="button" onclick="confirmRefreshCredits()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Refresh Member Credits (5 per month)
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Reset Individual User Password -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-8">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-white mb-4">Reset Individual User Password</h3>
        <p class="text-sm text-gray-400 mb-4">Enter a user's email address to reset their password to <strong class="text-amber-300">Made2025!</strong></p>
        
        <form action="{{ route('admin.reset-user-password') }}" method="POST" class="flex gap-3 items-start">
            @csrf
            <div class="flex-1">
                <label for="user_email" class="block text-sm font-medium text-gray-300 mb-2">User Email Address</label>
                <input 
                    type="email" 
                    name="email" 
                    id="user_email" 
                    required 
                    placeholder="user@example.com"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="pt-7">
                <button 
                    type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmSendEmails() {
    if (confirm('This will send welcome emails to all members with active subscriptions. Continue?')) {
        const form = document.getElementById('sendWelcomeEmailsForm');
        const button = form.querySelector('button');
        
        // Disable button and show loading state
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Sending emails...';
        
        form.submit();
    }
}

function confirmResetPasswords() {
    if (confirm('⚠️ WARNING: This will reset passwords for ALL MEMBERS WITH ACTIVE SUBSCRIPTIONS to "Made2025!"\n\nOnly members with monthly subscriptions will be affected.\nAdmins, instructors, and regular users will NOT be affected.\n\nContinue?')) {
        const form = document.getElementById('resetPasswordsForm');
        const button = form.querySelector('button');
        
        // Disable button and show loading state
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Resetting passwords...';
        
        form.submit();
    }
}

function confirmCreateAccounts() {
    if (confirm('This will sync all active Stripe subscriptions:\n\n✅ Create accounts for new members\n🔄 Update existing members with proper access\n👤 Set role to "subscriber"\n🎫 Assign "member" membership (5 classes/month)\n\nPassword for new accounts: Made2025!\n\nContinue?')) {
        const form = document.getElementById('createAccountsForm');
        const button = form.querySelector('button');
        
        // Disable button and show loading state
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Syncing...';
        
        form.submit();
    }
}

function confirmMigrateRoles() {
    if (confirm('This will setup all monthly paying members:\n\n👤 Change role to "subscriber"\n🎫 Assign "member" membership (5 classes/month)\n\nThis affects all users with subscriptions.\nAdmins and instructors will NOT be affected.\n\nContinue?')) {
        const form = document.getElementById('migrateRolesForm');
        const button = form.querySelector('button');
        
        // Disable button and show loading state
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Setting up...';
        
        form.submit();
    }
}

function confirmRefreshCredits() {
    if (confirm('This will refresh monthly credits for all active members:\n\n💳 Give 5 credits to members who haven\'t used credits this month\n🔄 Reset credits to 5 for the current month\n\nOnly affects users with active Stripe subscriptions.\n\nContinue?')) {
        const form = document.getElementById('refreshCreditsForm');
        const button = form.querySelector('button');
        
        // Disable button and show loading state
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Refreshing...';
        
        form.submit();
    }
}
</script>

<!-- Recent Bookings -->
<div class="bg-gray-800 shadow rounded-lg border border-gray-700 mb-8">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg leading-6 font-medium text-white">Recent Bookings</h3>
            <a href="{{ route('admin.bookings.index') }}" role="button" class="inline-flex items-center min-h-[44px] text-primary hover:text-purple-400 text-sm font-medium">
                View all bookings →
            </a>
        </div>

        @if($recentBookings->count() > 0)
            <div class="overflow-x-auto">
                <table class="admin-table min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Member</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Booked</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        @foreach($recentBookings as $booking)
                        <tr class="hover:bg-gray-700/50 cursor-pointer" onclick="window.location='{{ route('admin.bookings.show', $booking) }}'">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center">
                                            <span class="text-xs font-medium text-white">
                                                {{ strtoupper(substr($booking->user->name ?? 'U', 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white">{{ $booking->user->name ?? 'Unknown User' }}</div>
                                        <div class="text-sm text-gray-400">{{ $booking->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-white">{{ $booking->fitnessClass->name ?? 'Unknown Class' }}</div>
                                <div class="text-sm text-gray-400">{{ $booking->fitnessClass->instructor->name ?? 'No Instructor' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                @if($booking->fitnessClass)
                                    @php
                                        // Use booking_date if available (for recurring classes), otherwise use class_date
                                        $displayDate = $booking->booking_date ?? $booking->fitnessClass->class_date;
                                    @endphp
                                    <div>{{ $displayDate ? $displayDate->format('M j, Y') : 'No Date' }}</div>
                                    <div class="text-gray-400">{{ $booking->fitnessClass->start_time }} - {{ $booking->fitnessClass->end_time }}</div>
                                @else
                                    <div class="text-gray-400">Class not found</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                    @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                    @elseif($booking->status === 'waitlist') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($booking->status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                {{ $booking->created_at->format('M j, Y') }}
                                <div class="text-xs text-gray-500">{{ $booking->created_at->format('g:i A') }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-300">No recent bookings</h3>
                <p class="mt-1 text-sm text-gray-400">Recent bookings will appear here once classes are booked.</p>
            </div>
        @endif
    </div>
</div>

<!-- Calendar View -->
<div class="mb-8">
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <!-- Calendar Header with Controls -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-white">
                        @if($view === 'weekly')
                            Week of {{ $currentWeekStart->format('M j, Y') }}
                        @else
                            {{ $currentWeekStart->format('F Y') }}
                        @endif
                    </h3>
                </div>
                
                <div class="admin-top-controls flex items-center space-x-4 overflow-x-auto -mx-2 px-2 sm:overflow-visible sm:mx-0 sm:px-0">
                    <!-- View Toggle -->
                    <div class="flex bg-gray-700 rounded-lg p-1">
                        <a href="{{ route('admin.dashboard', ['view' => 'weekly', 'week' => $weekOffset]) }}" 
                           class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $view === 'weekly' ? 'bg-primary text-white' : 'text-gray-300 hover:text-white' }}" role="button">
                            Weekly
                        </a>
                        <a href="{{ route('admin.dashboard', ['view' => 'monthly', 'week' => $weekOffset]) }}" 
                           class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $view === 'monthly' ? 'bg-primary text-white' : 'text-gray-300 hover:text-white' }}" role="button">
                            Monthly
                        </a>
                    </div>
                    
                    <!-- Navigation Controls -->
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.dashboard', ['view' => $view, 'week' => $weekOffset - 1]) }}" 
                           class="inline-flex items-center p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition-colors" role="button">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </a>
                        
                        <a href="{{ route('admin.dashboard', ['view' => $view, 'week' => 0]) }}" 
                           class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-700 rounded-md transition-colors" role="button">
                            Today
                        </a>
                        
                        <a href="{{ route('admin.dashboard', ['view' => $view, 'week' => $weekOffset + 1]) }}" 
                           class="inline-flex items-center p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition-colors" role="button">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Calendar Grid -->
            @if($view === 'weekly')
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                <div class="min-w-[720px] sm:min-w-0 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-1 mb-4 px-4 sm:px-0">
                    @foreach($calendarDates as $index => $date)
                    <div class="text-center {{ $date->isToday() ? 'bg-primary bg-opacity-10 border-primary border-2' : '' }} rounded-lg">
                        <div class="text-sm font-medium text-gray-300 py-2 border-b border-gray-600">
                            <div>{{ $date->format('D') }}</div>
                            <div class="text-lg {{ $date->isToday() ? 'text-primary font-bold' : '' }}">{{ $date->format('j') }}</div>
                        </div>
                        <div class="min-h-[160px] sm:min-h-[200px] p-2 space-y-1">
                                @if(isset($calendarData[$index]))
                                    @foreach($calendarData[$index] as $class)
                                        <div class="bg-primary bg-opacity-20 border border-primary rounded p-2 text-xs cursor-pointer hover:bg-opacity-30 transition-colors"
                                             onclick="window.location='{{ route('admin.classes.show', $class) }}'">
                                            <div class="font-medium text-primary">{{ $class->start_time }}</div>
                                            <div class="text-white font-medium">{{ $class->name }}</div>
                                            <div class="text-gray-300">{{ $class->instructor->name ?? 'No Instructor' }}</div>
                                            <div class="text-gray-400">{{ $class->type }} • {{ $class->duration }}min</div>
                                            <div class="text-gray-400">£{{ number_format($class->price, 0) }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                </div>
            @else
                <!-- Monthly View -->
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                <div class="min-w-[720px] sm:min-w-0 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-1 mb-4 px-4 sm:px-0">
                    @php $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; @endphp
                    @foreach($dayNames as $dayName)
                        <div class="text-center text-sm font-medium text-gray-300 py-2 border-b border-gray-600">
                            {{ $dayName }}
                        </div>
                    @endforeach
                    
                    @foreach($calendarDates as $index => $date)
                        <div class="min-h-[120px] border border-gray-700 p-1 {{ $date->isToday() ? 'bg-primary bg-opacity-10 border-primary border-2' : '' }} rounded-lg">
                            <div class="text-sm {{ $date->isToday() ? 'text-primary font-bold' : ($date->month !== $currentWeekStart->month ? 'text-gray-500' : 'text-gray-300') }}">
                                {{ $date->format('j') }}
                            </div>
                            <div class="space-y-1 mt-1">
                                @if(isset($calendarData[$index]))
                                    @foreach($calendarData[$index]->take(2) as $class)
                                        <div class="bg-primary bg-opacity-20 border border-primary rounded p-1 text-xs cursor-pointer hover:bg-opacity-30 transition-colors"
                                             onclick="window.location='{{ route('admin.classes.show', $class) }}'">
                                            <div class="font-medium text-primary">{{ $class->start_time }}</div>
                                            <div class="text-white text-xs truncate">{{ $class->name }}</div>
                                        </div>
                                    @endforeach
                                    @if(isset($calendarData[$index]) && $calendarData[$index]->count() > 2)
                                        <div class="text-xs text-gray-400">+{{ $calendarData[$index]->count() - 2 }} more</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                </div>
            @endif
            
            <div class="flex justify-between items-center pt-4 border-t border-gray-700">
                <div class="text-sm text-gray-400">
                    Showing {{ $calendarData->flatten()->count() }} active classes
                </div>
                <a href="{{ route('admin.classes.index') }}" role="button" class="inline-flex items-center min-h-[44px] text-primary hover:text-purple-400 text-sm font-medium">
                    View all classes →
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-white">Quick Actions</h3>
            <div class="mt-5 grid grid-cols-1 gap-3">
                <a href="{{ route('admin.classes.create') }}" role="button" class="w-full inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-purple-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New Class
                </a>
                <a href="{{ route('admin.instructors.create') }}" role="button" class="w-full inline-flex items-center px-4 py-2 border border-gray-600 text-sm font-medium rounded-md text-gray-300 bg-transparent hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New Instructor
                </a>
                <a href="{{ route('admin.memberships.create') }}" role="button" class="w-full inline-flex items-center px-4 py-2 border border-gray-600 text-sm font-medium rounded-md text-gray-300 bg-transparent hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New Membership
                </a>
                <form method="POST" action="{{ route('admin.members.ensure-login-access') }}" class="inline-block w-full">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center px-4 py-2 border border-gray-600 text-sm font-medium rounded-md text-gray-300 bg-transparent hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Ensure Member Login Access
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.members.ensure-subscription-users') }}" class="inline-block w-full">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center px-4 py-2 border border-gray-600 text-sm font-medium rounded-md text-gray-300 bg-transparent hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Ensure Subscription Users
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.members.sync-stripe-members') }}" class="inline-block w-full">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center px-4 py-2 border border-gray-600 text-sm font-medium rounded-md text-gray-300 bg-transparent hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Sync Stripe Subscriptions
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.members.verify-accounts') }}" class="inline-block w-full">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center px-4 py-2 border border-gray-600 text-sm font-medium rounded-md text-gray-300 bg-transparent hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Verify Membership Accounts
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.members.create-account') }}" class="inline-block w-full space-y-2">
                    @csrf
                    <div class="flex space-x-2">
                        <input type="email" name="email" placeholder="Email address" required 
                               class="flex-1 px-3 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary">
                        <input type="text" name="name" placeholder="Full name (optional)" 
                               class="flex-1 px-3 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary">
                        <label class="flex items-center">
                            <input type="checkbox" name="send_reset" checked class="mr-2">
                            <span class="text-sm text-gray-300">Send password reset</span>
                        </label>
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-600 text-sm font-medium rounded-md text-gray-300 bg-transparent hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Create Member Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 shadow rounded-lg border border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-white">System Overview</h3>
            <div class="mt-5 text-sm text-gray-300">
                <p class="mb-2">Welcome to the Made Running admin dashboard. From here you can:</p>
                <ul class="list-disc list-inside space-y-1 text-gray-400">
                    <li>Manage fitness classes and schedules</li>
                    <li>Add and edit instructor profiles</li>
                    <li>Configure membership plans</li>
                    <li>View user registrations and bookings</li>
                    <li>Generate reports and analytics</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
