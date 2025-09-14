<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout - Made Running</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#c8b7ed',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-black shadow-sm border-b border-gray-800">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <img src="{{ asset('made-running.webp') }}" alt="Made Running" class="h-8 w-8">
                    <span class="text-xl font-bold text-primary">MADE RUNNING</span>
                </div>
                <a href="{{ route('welcome') }}" class="text-gray-300 hover:text-white transition-colors">
                    ← Back to Classes
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-10">
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
            <!-- Class Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-black mb-4">Class Details</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?w=100&h=100&fit=crop&crop=face" 
                                 alt="{{ $class->instructor->name ?? 'Instructor' }}" 
                                 class="w-16 h-16 rounded-full object-cover">
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-black">{{ $class->name }}</h3>
                            <p class="text-sm text-gray-700">{{ $class->instructor->name ?? 'No Instructor' }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Date:</span>
                                <p class="font-medium text-black">{{ \Carbon\Carbon::parse($class->class_date)->format('l, F j, Y') }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Time:</span>
                                <p class="font-medium text-black">{{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Duration:</span>
                                <p class="font-medium text-black">{{ \Carbon\Carbon::parse($class->end_time)->diffInMinutes(\Carbon\Carbon::parse($class->start_time)) }} minutes</p>
                            </div>
                        
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <form id="coupon-form" class="flex items-center space-x-2">
                            <input type="text" id="coupon-code" name="coupon_code" placeholder="Enter coupon code" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <button type="submit" class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition-colors">Apply</button>
                        </form>
                        <div id="coupon-message" class="mt-2 text-sm"></div>
                    </div>

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
            <div class="space-y-4">
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

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-800 mb-1">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-800 mb-1">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" id="pay-button"
                            class="w-full bg-primary text-black py-3 px-4 rounded-md font-semibold hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                        Pay with Card — £{{ number_format($class->price, 2) }}
                    </button>
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
                            alert(data.message || 'Booking failed. Please try again.');
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
</body>
</html>
