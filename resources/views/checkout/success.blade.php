<x-checkout-layout :title="'Booking Confirmed'">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Booking Successful!</h2>
            <p class="text-gray-600 mb-8">Thank you for your purchase. A confirmation email has been sent to you with all the details.</p>
            
            <div class="space-y-4">
                <a href="{{ route('welcome') }}" 
                   class="inline-flex items-center justify-center w-full md:w-auto px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold transition-colors duration-200">
                    <span>View More Classes</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
                
                <div class="flex items-center justify-center text-sm text-gray-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    Check your email for the confirmation details
                </div>
            </div>
        </div>
    </div>
</x-checkout-layout>
