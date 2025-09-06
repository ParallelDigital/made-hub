<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Your QR Code</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600 mb-4">Your unique QR code for check-ins:</p>
                        <div class="bg-white p-4 rounded border-2 border-gray-200 text-center mb-4">
                            <img src="{{ route('user.qr-code', auth()->user()) }}"
                                 alt="Your QR Code"
                                 class="mx-auto mb-2"
                                 style="max-width: 200px; height: auto;">
                            <p class="text-xs text-gray-500">Scan this QR code for check-ins</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-1">QR Code Text:</p>
                            <span class="font-mono text-sm font-bold text-gray-800 bg-gray-100 px-2 py-1 rounded">
                                {{ auth()->user()->qr_code }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
