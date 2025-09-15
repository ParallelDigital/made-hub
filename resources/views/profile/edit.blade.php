@extends('layouts.admin')

@section('title', 'Profile Settings')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Profile Settings</h1>
                <p class="text-purple-100">Manage your account information and security</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Profile Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Profile Information Card -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h2 class="text-xl font-semibold text-white">Profile Information</h2>
                    <p class="text-sm text-gray-400">Update your account's profile information and email address.</p>
                </div>
                <div class="p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Update Password Card -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h2 class="text-xl font-semibold text-white">Update Password</h2>
                    <p class="text-sm text-gray-400">Ensure your account is using a long, random password to stay secure.</p>
                </div>
                <div class="p-6">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <!-- Right Column - QR Code -->
        <div class="space-y-6">
            <!-- QR Code Card -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h2 class="text-xl font-semibold text-white">Your QR Code</h2>
                    <p class="text-sm text-gray-400">Use this code for quick check-ins</p>
                </div>
                <div class="p-6">
                    <div class="bg-gray-900 p-4 rounded-lg border border-gray-700">
                        <div class="bg-gray-800 p-4 rounded border border-gray-700 text-center mb-4">
                            <img src="{{ route('user.qr-code', auth()->user()) }}"
                                 alt="Your QR Code"
                                 class="mx-auto w-full max-w-[200px] h-auto"
                                 style="max-width: 200px; height: auto;">
                            <p class="text-xs text-gray-500 mt-2">Scan this QR code for check-ins</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-400 mb-2">QR Code Text:</p>
                            <div class="bg-gray-800 px-4 py-2 rounded border border-gray-700">
                                <span class="font-mono text-sm font-medium text-white break-all">
                                    {{ auth()->user()->qr_code }}
                                </span>
                            </div>
                            <button onclick="navigator.clipboard.writeText('{{ auth()->user()->qr_code }}').then(() => showAlertModal('Copied to clipboard!', 'success'))" 
                                    class="mt-3 text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                                Copy to clipboard
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Account Card -->
            <div class="bg-gray-800 rounded-xl border border-red-500/30 shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-red-500/30 bg-red-500/10">
                    <h2 class="text-xl font-semibold text-white">Delete Account</h2>
                    <p class="text-sm text-red-300">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                </div>
                <div class="p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
