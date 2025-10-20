<x-checkout-layout>
    <div class="grid md:grid-cols-5 gap-8">
            <!-- Package Summary -->
            <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Package Summary</h2>
                <div class="space-y-4">
                    <div class="flex items-start justify-between pb-4 border-b border-gray-100">
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ strtoupper($class->name) }}</div>
                            <div class="mt-1 text-sm text-gray-600">{{ $class->instructor->name ?? 'No Instructor' }}</div>
                            <div class="mt-2 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($class->class_date)->format('l, F j, Y') }}<br>
                                {{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}
                            </div>
                        </div>
                        <div class="text-2xl font-black text-gray-900">£{{ number_format($class->price, 2) }}</div>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Instant Access After Payment
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Secure Payment Processing
                    </div>
                    
                    <!-- Coupon Section -->
                    <div class="border-t border-gray-200 pt-4">
                        <form id="coupon-form" class="flex items-center space-x-2">
                            <input type="text" id="coupon-code" name="coupon_code" placeholder="Enter coupon code" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <button type="submit" class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition-colors">Apply</button>
                        </form>
                        <div id="coupon-message" class="mt-2 text-sm"></div>
                    </div>

                    <!-- Pricing Summary -->
                    <div class="border-t border-gray-200 pt-4 space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-700">Subtotal:</span>
                            <span class="font-semibold text-black">£{{ number_format($class->price, 2) }}</span>
                        </div>
                        <div id="discount-row" class="flex justify-between items-center text-sm text-green-600 hidden">
                            <span class="text-gray-600">Discount:</span>
                            <span id="discount-amount" class="font-medium">-£0.00</span>
                        </div>
                        <div class="flex justify-between items-center text-lg font-extrabold">
                            <span class="text-black">Total:</span>
                            <span id="total-price" class="text-black">£{{ number_format($class->price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Tabs and Panels -->
            <div class="space-y-4 md:col-span-3">
                <!-- Tabs -->
                @php $openCredits = ($autoOpenCredits ?? false); @endphp
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-2">
                    <div class="flex">
                        <button id="tab-btn-card" type="button" class="flex-1 px-4 py-2 rounded-md text-sm font-semibold transition-colors {{ $openCredits ? 'text-gray-700 hover:text-black' : 'bg-black text-white' }}">Pay with Card</button>
                        <button id="tab-btn-credits" type="button" class="flex-1 px-4 py-2 rounded-md text-sm font-semibold transition-colors {{ $openCredits ? 'bg-black text-white' : 'text-gray-700' }}">Use Credits</button>
                        <button id="tab-btn-arrival" type="button" class="flex-1 px-4 py-2 rounded-md text-sm font-semibold transition-colors text-gray-700">Pay on Arrival</button>
                    </div>
                </div>

                <!-- Panel: Card -->
                <div id="tab-panel-card" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 {{ $openCredits ? 'hidden' : '' }}">

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('booking.process-checkout', $class->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="coupon_code" id="applied-coupon-code">
                    @if(isset($bookingDate))
                    <input type="hidden" name="selected_date" value="{{ $bookingDate }}">
                    @endif

                    <x-checkout-input
                        id="name"
                        name="name"
                        type="text"
                        label="Full Name"
                        :value="old('name', auth()->check() ? auth()->user()->name : '')"
                        required
                        helper=""
                    />
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <x-checkout-input
                        id="email"
                        name="email"
                        type="email"
                        label="Email Address"
                        :value="old('email', auth()->check() ? auth()->user()->email : '')"
                        required
                        helper="We'll send your receipt to this email"
                    />
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <button type="submit" id="pay-button"
                            class="w-full bg-primary hover:bg-primary-dark text-white rounded-lg px-6 py-4 font-semibold text-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                        <span>Proceed to Payment</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    <div class="mt-4 flex items-center justify-center space-x-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>Secure, encrypted payment processing</span>
                    </div>
                    </form>
                </div>

                <!-- Panel: Credits -->
                <div id="tab-panel-credits" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 {{ $openCredits ? '' : 'hidden' }}">
                    <h2 class="text-xl font-bold text-black mb-4">Use Credits</h2>
                @auth
                    <div class="mb-4">
                        <p class="text-sm text-gray-700">Available Credits</p>
                        <div class="text-3xl font-extrabold text-black">{{ $availableCredits ?? 0 }}</div>
                    </div>

                    <button id="useCreditsCheckoutBtn"
                            class="w-full mt-3 flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md bg-purple-50 hover:bg-purple-100 text-black font-semibold transition-colors {{ ($availableCredits ?? 0) > 0 ? '' : 'opacity-50 cursor-not-allowed' }}"
                            {{ ($availableCredits ?? 0) > 0 ? '' : 'disabled' }}>
                        <span id="useCreditsCheckoutLabel">Use 1 Credit</span>
                    </button>
                @else
                    <p class="text-gray-700 mb-3">Sign in to use your credits.</p>
                    <a href="{{ route('login') }}" class="inline-flex px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800">Sign In</a>
                @endauth
                </div>

                <!-- Panel: Pay on Arrival -->
                <div id="tab-panel-arrival" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hidden">
                    <h2 class="text-xl font-bold text-black mb-4">Pay on Arrival</h2>
                @auth
                    <div class="mb-4">
                        <p class="text-sm text-gray-700 mb-3">Reserve your spot now and pay when you arrive at the studio.</p>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium">Please note:</p>
                                    <p>Payment is required upon arrival. We accept cash and card payments at the studio.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button id="payOnArrivalBtn"
                            class="w-full mt-3 flex items-center justify-center px-4 py-2 border border-blue-300 rounded-md bg-blue-50 hover:bg-blue-100 text-black font-semibold transition-colors">
                        <span>Reserve Spot - Pay on Arrival</span>
                    </button>
                @else
                    <p class="text-gray-700 mb-3">Sign in to reserve your spot.</p>
                    <a href="{{ route('login') }}" class="inline-flex px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800">Sign In</a>
                @endauth
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add CSRF token to meta tag if missing
        if (!document.querySelector('meta[name="csrf-token"]')) {
            const meta = document.createElement('meta');
            meta.name = 'csrf-token';
            meta.content = '{{ csrf_token() }}';
            document.head.appendChild(meta);
        }
        // Tabs logic
        (function() {
            const btnCard = document.getElementById('tab-btn-card');
            const btnCredits = document.getElementById('tab-btn-credits');
            const btnArrival = document.getElementById('tab-btn-arrival');
            const panelCard = document.getElementById('tab-panel-card');
            const panelCredits = document.getElementById('tab-panel-credits');
            const panelArrival = document.getElementById('tab-panel-arrival');
            if (!btnCard || !btnCredits || !btnArrival || !panelCard || !panelCredits || !panelArrival) return;

            function activate(tab) {
                // Hide all panels
                panelCard.classList.add('hidden');
                panelCredits.classList.add('hidden');
                panelArrival.classList.add('hidden');
                
                // Reset all buttons
                [btnCard, btnCredits, btnArrival].forEach(btn => {
                    btn.classList.remove('bg-black', 'text-white');
                    btn.classList.add('text-gray-700');
                });
                
                // Show active panel and style active button
                if (tab === 'card') {
                    panelCard.classList.remove('hidden');
                    btnCard.classList.add('bg-black', 'text-white');
                    btnCard.classList.remove('text-gray-700');
                } else if (tab === 'credits') {
                    panelCredits.classList.remove('hidden');
                    btnCredits.classList.add('bg-black', 'text-white');
                    btnCredits.classList.remove('text-gray-700');
                } else if (tab === 'arrival') {
                    panelArrival.classList.remove('hidden');
                    btnArrival.classList.add('bg-black', 'text-white');
                    btnArrival.classList.remove('text-gray-700');
                }
            }

            btnCard.addEventListener('click', () => activate('card'));
            btnCredits.addEventListener('click', () => activate('credits'));
            btnArrival.addEventListener('click', () => activate('arrival'));
        })();
        // Auto-open credits tab if requested (no PIN)
        document.addEventListener('DOMContentLoaded', function() {
            const shouldOpen = {{ ($autoOpenCredits ?? false) ? 'true' : 'false' }};
            if (shouldOpen) {
                const btnCredits = document.getElementById('tab-btn-credits');
                if (btnCredits) btnCredits.click();
            }
        });

        // Use credits booking
        (function() {
            const btn = document.getElementById('useCreditsCheckoutBtn');
            if (!btn) return;
            btn.addEventListener('click', function() {
                const label = document.getElementById('useCreditsCheckoutLabel');
                // Simple confirmation
                if (!confirm('Use 1 credit to book this class?')) return;

                // Disable button during request
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');
                if (label) label.textContent = 'Booking...';

                fetch(`/book-with-credits/{{ $class->id }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        @if(isset($bookingDate))
                        selected_date: '{{ $bookingDate }}'
                        @endif
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.href = `/booking/confirmation/{{ $class->id }}`;
                        }
                    } else {
                        showAlertModal(data.message || 'Booking failed. Please try again.', 'error');
                    }
                })
                .catch(() => {
                    showAlertModal('An error occurred. Please try again.', 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-70', 'cursor-not-allowed');
                    if (label) label.textContent = 'Use 1 Credit';
                });
            });
        })();

        // Pay on Arrival booking
        (function() {
            const btn = document.getElementById('payOnArrivalBtn');
            if (!btn) return;
            btn.addEventListener('click', function() {
                // Simple confirmation
                if (!confirm('Reserve this class and pay on arrival?')) return;

                // Disable button during request
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');
                btn.textContent = 'Booking...';

                fetch(`/book-pay-on-arrival/{{ $class->id }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        @if(isset($bookingDate))
                        selected_date: '{{ $bookingDate }}'
                        @endif
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.href = `/booking/confirmation/{{ $class->id }}`;
                        }
                    } else {
                        showAlertModal(data.message || 'Booking failed. Please try again.', 'error');
                    }
                })
                .catch(() => {
                    showAlertModal('An error occurred. Please try again.', 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-70', 'cursor-not-allowed');
                    btn.textContent = 'Reserve Spot - Pay on Arrival';
                });
            });
        })();

        document.getElementById('coupon-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const couponCode = document.getElementById('coupon-code').value;
            const classId = {{ $class->id }};

            fetch('{{ route("booking.apply-coupon") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ coupon_code: couponCode, class_id: classId })
            })
            .then(response => response.json())
            .then(data => {
                const messageEl = document.getElementById('coupon-message');
                if (data.success) {
                    messageEl.textContent = data.message;
                    messageEl.className = 'mt-2 text-sm text-green-600';
                    
                    document.getElementById('discount-row').classList.remove('hidden');
                    document.getElementById('discount-amount').textContent = `-£${data.discount_amount.toFixed(2)}`;
                    document.getElementById('total-price').textContent = `£${data.new_total.toFixed(2)}`;
                    document.getElementById('pay-button').textContent = `Pay with Stripe — £${data.new_total.toFixed(2)}`;
                    document.getElementById('applied-coupon-code').value = couponCode;
                } else {
                    messageEl.textContent = data.message;
                    messageEl.className = 'mt-2 text-sm text-red-600';
                }
            })
            .catch(error => {
                const messageEl = document.getElementById('coupon-message');
                messageEl.textContent = 'An unexpected error occurred.';
                messageEl.className = 'mt-2 text-sm text-red-600';
            });
        });
    </script>
</x-checkout-layout>
