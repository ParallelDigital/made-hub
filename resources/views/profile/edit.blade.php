<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-white leading-tight">{{ __('Profile') }}</h2>
                <p class="text-gray-400 text-sm">Manage your account information and security</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Grid: Profile + Password -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Profile Information Card -->
                <div class="p-6 bg-gray-800/80 border border-gray-700 rounded-xl shadow-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Update Password Card -->
                <div class="p-6 bg-gray-800/80 border border-gray-700 rounded-xl shadow-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            <!-- QR Code Card -->
            <div class="p-6 bg-gray-800/80 border border-gray-700 rounded-xl shadow-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-white mb-4">Your QR Code</h3>
                    <div class="bg-gray-900 p-4 rounded-lg border border-gray-700">
                        <p class="text-sm text-gray-400 mb-4">Your unique QR code for check-ins:</p>
                        <div class="bg-gray-800 p-4 rounded border border-gray-700 text-center mb-4">
                            <img src="{{ route('user.qr-code', auth()->user()) }}"
                                 alt="Your QR Code"
                                 class="mx-auto mb-2"
                                 style="max-width: 200px; height: auto;">
                            <p class="text-xs text-gray-500">Scan this QR code for check-ins</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-400 mb-1">QR Code Text:</p>
                            <span class="font-mono text-sm font-bold text-white bg-gray-800 px-2 py-1 rounded border border-gray-700">
                                {{ auth()->user()->qr_code }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
