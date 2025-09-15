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
                        <button id="tab-btn-credits" type="button" class="flex-1 px-4 py-2 rounded-md text-sm font-semibold transition-colors {{ $openCredits ? 'bg-black text-white' : 'text-gray-700 hover:text-black' }}">Use Credits</button>
                    </div>
                </div>

                <!-- Panel: Card -->
                <div id="tab-panel-card" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 {{ $openCredits ? 'hidden' : '' }}">
                    <h2 class="text-xl font-bold text-black mb-4">Pay with Card</h2>

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('booking.process-checkout', $class->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="coupon_code" id="applied-coupon-code">

                    <x-checkout-input
                        id="name"
                        name="name"
                        type="text"
                        label="Full Name"
                        :value="old('name')"
                        required
                        helper="Enter your name as it appears on your card"
                    />
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <x-checkout-input
                        id="email"
                        name="email"
                        type="email"
                        label="Email Address"
                        :value="old('email')"
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

                    <div id="creditsPinWrap" class="space-y-2 {{ ($autoOpenCredits ?? false) ? '' : 'hidden' }}">
                        <label for="creditsPinInput" class="block text-sm font-medium text-gray-800">Enter your 4-digit booking code (PIN)</label>
                        <div class="relative">
                            <input id="creditsPinInput" name="pin_code" inputmode="numeric" pattern="\\d{4}" maxlength="4" type="password"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                   placeholder="0000">
                            <button type="button" id="toggleCreditsPin" aria-label="Show PIN"
                                    class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3C5 3 1.73 7.11 1 10c.73 2.89 4 7 9 7s8.27-4.11 9-7c-.73-2.89-4-7-9-7zm0 12a5 5 0 110-10 5 5 0 010 10z"/><circle cx="10" cy="10" r="3" fill="currentColor"/></svg>
                            </button>
                        </div>
                        <p id="creditsPinError" class="text-sm text-red-600 hidden">Please enter your 4-digit PIN.</p>
                    </div>

                    <button id="useCreditsCheckoutBtn"
                            class="w-full mt-3 flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md bg-purple-50 hover:bg-purple-100 text-black font-semibold transition-colors {{ ($availableCredits ?? 0) > 0 ? '' : 'opacity-50 cursor-not-allowed' }}"
                            {{ ($availableCredits ?? 0) > 0 ? '' : 'disabled' }}>
                        <span id="useCreditsCheckoutLabel">{{ ($autoOpenCredits ?? false) ? 'Confirm with Credits' : 'Use 1 Credit' }}</span>
                    </button>
                @else
                    <p class="text-gray-700 mb-3">Sign in to use your credits.</p>
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
            const panelCard = document.getElementById('tab-panel-card');
            const panelCredits = document.getElementById('tab-panel-credits');
            if (!btnCard || !btnCredits || !panelCard || !panelCredits) return;

            function activate(tab) {
                const isCard = tab === 'card';
                panelCard.classList.toggle('hidden', !isCard);
                panelCredits.classList.toggle('hidden', isCard);
                btnCard.classList.toggle('bg-black', isCard);
                btnCard.classList.toggle('text-white', isCard);
                btnCard.classList.toggle('text-gray-700', !isCard);
                btnCredits.classList.toggle('bg-black', !isCard);
                btnCredits.classList.toggle('text-white', !isCard);
                btnCredits.classList.toggle('text-gray-700', isCard);
            }

            btnCard.addEventListener('click', () => activate('card'));
            btnCredits.addEventListener('click', () => activate('credits'));
        })();
        // Auto-open credits block
        document.addEventListener('DOMContentLoaded', function() {
            const shouldOpen = {{ ($autoOpenCredits ?? false) ? 'true' : 'false' }};
            if (shouldOpen) {
                const wrap = document.getElementById('creditsPinWrap');
                if (wrap) {
                    wrap.classList.remove('hidden');
                    const input = document.getElementById('creditsPinInput');
                    if (input) input.focus();
                }
            }
        });

        // Toggle PIN visibility
        (function() {
            const toggleBtn = document.getElementById('toggleCreditsPin');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const input = document.getElementById('creditsPinInput');
                    if (!input) return;
                    input.type = input.type === 'password' ? 'text' : 'password';
                    this.setAttribute('aria-label', input.type === 'password' ? 'Show PIN' : 'Hide PIN');
                });
            }
        })();

        // Use credits booking
        (function() {
            const btn = document.getElementById('useCreditsCheckoutBtn');
            if (!btn) return;
            btn.addEventListener('click', function() {
                const wrap = document.getElementById('creditsPinWrap');
                const label = document.getElementById('useCreditsCheckoutLabel');
                if (wrap && wrap.classList.contains('hidden')) {
                    wrap.classList.remove('hidden');
                    if (label) label.textContent = 'Confirm with Credits';
                    const input = document.getElementById('creditsPinInput');
                    if (input) input.focus();
                    return;
                }

                const input = document.getElementById('creditsPinInput');
                const pinError = document.getElementById('creditsPinError');
                const pin = input ? input.value.trim() : '';
                if (!/^\d{4}$/.test(pin)) {
                    if (pinError) pinError.classList.remove('hidden');
                    if (input) input.focus();
                    return;
                }
                if (pinError) pinError.classList.add('hidden');

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
                    body: JSON.stringify({ pin_code: pin })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = `/booking/confirmation/{{ $class->id }}`;
                    } else {
                        if (pinError) {
                            pinError.textContent = data.message || 'Booking failed. Please try again.';
                            pinError.classList.remove('hidden');
                        } else {
                            showAlertModal(data.message || 'Booking failed. Please try again.', 'error');
                        }
                    }
                })
                .catch(() => {
                    if (pinError) {
                        pinError.textContent = 'An error occurred. Please try again.';
                        pinError.classList.remove('hidden');
                    }
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-70', 'cursor-not-allowed');
                    if (label) label.textContent = 'Confirm with Credits';
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
