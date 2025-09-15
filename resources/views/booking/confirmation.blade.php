<x-checkout-layout :title="'Booking Confirmed'">
    <div class="max-w-2xl mx-auto">

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <!-- Success Icon -->
            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Booking Confirmed!</h1>
            <p class="text-gray-600 mb-8">Your class has been successfully booked. We look forward to seeing you!</p>

            <!-- Class Details -->
            <div class="bg-gray-50 rounded-xl p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Class Details</h2>
                
                <div class="space-y-4 text-left">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Class</div>
                            <div class="font-medium text-gray-900">{{ $class->name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Instructor</div>
                            <div class="font-medium text-gray-900">{{ $class->instructor->name ?? 'TBA' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Date</div>
                            <div class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($class->class_date)->format('l, F j, Y') }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Time</div>
                            <div class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-8">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 text-left">
                        <h3 class="text-sm font-medium text-blue-800">Important Information</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Please arrive 10 minutes before class starts</li>
                                <li>Bring water and a towel</li>
                                <li>Wear comfortable workout clothes</li>
                                <li>Cancellations must be made 24 hours in advance</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('welcome') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold transition-colors duration-200">
                    <span>Book Another Class</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
                <a href="{{ route('purchase.index') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition-colors duration-200">
                    <span>Buy Credits</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
    </div>
</x-checkout-layout>
