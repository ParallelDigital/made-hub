<x-checkout-layout :title="'Check-In Details'">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <!-- Booking ID -->
            <div class="flex items-center justify-center mb-8">
                <div class="bg-gray-50 rounded-lg px-4 py-2 inline-flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                    </svg>
                    <span class="font-mono text-gray-900 font-medium">#{{ $booking->id }}</span>
                </div>
            </div>

            <!-- Member & Class Details -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Member Info -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-primary bg-opacity-10 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Member Details</h3>
                    </div>
                    <div class="space-y-1">
                        <div class="text-base font-medium text-gray-900">{{ optional($booking->user)->name ?? 'Guest' }}</div>
                        <div class="text-sm text-gray-500">{{ optional($booking->user)->email }}</div>
                    </div>
                </div>

                <!-- Class Info -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-primary bg-opacity-10 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">Class Details</h3>
                    </div>
                    <div class="space-y-2">
                        <div class="text-base font-medium text-gray-900">{{ optional($booking->fitnessClass)->name ?? 'Class' }}</div>
                        @php
                            $bookingDisplayDate = $booking->booking_date ?? optional($booking->fitnessClass)->class_date;
                        @endphp
                        @if($bookingDisplayDate)
                            <div class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($bookingDisplayDate)->format('l, F j, Y') }}
                                @if(optional($booking->fitnessClass)->start_time)
                                    <br>{{ \Carbon\Carbon::parse($booking->fitnessClass->start_time)->format('g:i A') }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="flex items-center justify-center">
                <div class="flex items-center space-x-2 {{ $booking->status === 'confirmed' ? 'text-green-700' : 'text-yellow-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($booking->status === 'confirmed')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        @endif
                    </svg>
                    <span class="text-sm font-medium">Status: {{ ucfirst($booking->status) }}</span>
                </div>
            </div>

            <div class="mt-8 text-center text-sm text-gray-500">
                <p>This is a preview of the check-in page that your QR codes will point to.</p>
                <p>In a future iteration, scanning this QR will mark attendance.</p>
            </div>
        </div>
    </div>
</x-checkout-layout>
