<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ strtoupper($package->name) }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/webp" href="{{ asset('favicon.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('made-running.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { 
            theme: { 
                extend: { 
                    colors: { 
                        primary: '#c8b7ed',
                        'primary-dark': '#b094e8'
                    } 
                } 
            } 
        }
    </script>
    <style>
        input:focus-visible {
            outline: none;
            box-shadow: 0 0 0 2px #c8b7ed;
        }
        .floating-label {
            transform: translateY(-1.5rem) scale(0.85);
            background-color: white;
            padding: 0 0.25rem;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-black text-white px-6 py-4 shadow-md">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <a href="{{ route('purchase.index') }}" class="flex items-center space-x-3 group">
                <img src="{{ asset('made-running.png') }}" alt="Made Running" class="h-15 w-20 transition-transform group-hover:scale-105">
            </a>
            <a href="{{ route('welcome') }}" class="text-white hover:text-primary transition-colors duration-200">Back to Home</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-6 py-12">
        <div class="flex items-center space-x-2 mb-8">
            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <h1 class="text-4xl font-black text-gray-900">Secure Checkout</h1>
        </div>

        <div class="grid md:grid-cols-5 gap-8">
            <!-- Package Summary -->
            <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Package Summary</h2>
                <div class="space-y-4 md:col-span-3">
                    <div class="flex items-start justify-between pb-4 border-b border-gray-100">
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ strtoupper($package->name) }}</div>
                            @if(!empty($package->validity))
                                <div class="mt-1 text-sm text-gray-600">{{ strtoupper($package->validity) }}</div>
                            @endif
                        </div>
                        <div class="text-2xl font-black text-gray-900">£{{ number_format($package->price, 2) }}</div>
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
                        <div class="flex items-center space-x-2">
                            <input type="text" id="coupon-code" name="coupon_code" placeholder="Enter discount code" value="{{ old('coupon_code') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <button type="button" id="apply-coupon" class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition-colors">Apply</button>
                        </div>
                        <div id="coupon-message" class="mt-2 text-sm"></div>
                    </div>

                    <!-- Pricing Summary -->
                    <div class="border-t border-gray-200 pt-4 space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-700">Subtotal:</span>
                            <span class="font-semibold text-black">£{{ number_format($package->price, 2) }}</span>
                        </div>
                        <div id="discount-row" class="flex justify-between items-center text-sm text-green-600 hidden">
                            <span class="text-gray-600">Discount:</span>
                            <span id="discount-amount" class="font-medium">-£0.00</span>
                        </div>
                        <div class="flex justify-between items-center text-lg font-extrabold">
                            <span class="text-black">Total:</span>
                            <span id="total-price" class="text-black">£{{ number_format($package->price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <div class="md:col-span-3 bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                @if(session('error'))
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-red-800">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="text-red-800 font-semibold mb-1">Please fix the following:</div>
                                <ul class="list-disc list-inside text-red-700 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('purchase.package.process', ['type' => $package->type]) }}" method="POST" class="space-y-6">
                    @csrf
                    @auth
                        <input type="hidden" name="checkout_mode" value="account" />
                    @endauth
                    @guest
                        <fieldset class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <legend class="text-sm font-semibold text-gray-700">Checkout options</legend>
                            <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <label class="flex items-start space-x-3 cursor-pointer border rounded-lg p-3 hover:border-primary">
                                    <input type="radio" name="checkout_mode" value="guest" class="mt-1"
                                           {{ old('checkout_mode', 'guest') === 'guest' ? 'checked' : '' }}>
                                    <div>
                                        <div class="font-semibold text-gray-900">Guest checkout</div>
                                        <div class="text-sm text-gray-600">Buy credits without signing in. We'll allocate them to the email below and email a link to set a password if needed.</div>
                                    </div>
                                </label>
                                <label class="flex items-start space-x-3 cursor-pointer border rounded-lg p-3 hover:border-primary">
                                    <input type="radio" name="checkout_mode" value="account" class="mt-1"
                                           {{ old('checkout_mode') === 'account' ? 'checked' : '' }}>
                                    <div>
                                        <div class="font-semibold text-gray-900">Sign in to account</div>
                                        <div class="text-sm text-gray-600">Use your existing password to sign in and buy credits.</div>
                                    </div>
                                </label>
                            </div>
                        </fieldset>
                    @endguest
                    <div class="relative">
                        <input id="name" name="name" type="text" required
                               value="{{ old('name', auth()->user()->name ?? '') }}" @auth readonly @endauth
                               class="peer w-full px-4 py-3 rounded-lg border-2 border-gray-200 placeholder-transparent focus:border-primary transition-colors"
                               placeholder="Full Name" />
                        <label for="name" 
                               class="absolute left-2 -top-2.5 text-sm text-gray-600 transition-all
                                      peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-3.5 peer-placeholder-shown:left-4
                                      peer-focus:-top-2.5 peer-focus:left-2 peer-focus:text-sm peer-focus:text-primary floating-label">
                            Full Name
                        </label>
                        <div class="mt-1 text-sm text-gray-500">Enter your name as it appears on your card</div>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative">
                        <input id="email" name="email" type="email" required
                               value="{{ old('email', auth()->user()->email ?? '') }}" @auth readonly @endauth
                               class="peer w-full px-4 py-3 rounded-lg border-2 border-gray-200 placeholder-transparent focus:border-primary transition-colors"
                               placeholder="Email Address" />
                        <label for="email"
                               class="absolute left-2 -top-2.5 text-sm text-gray-600 transition-all
                                      peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-3.5 peer-placeholder-shown:left-4
                                      peer-focus:-top-2.5 peer-focus:left-2 peer-focus:text-sm peer-focus:text-primary floating-label">
                            Email Address
                        </label>
                        <div class="mt-1 text-sm text-gray-500">We'll send your receipt to this email</div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @guest
                        <div id="account-password-block" class="relative" style="display: none;">
                            <input id="password" name="password" type="password"
                                   class="peer w-full px-4 py-3 rounded-lg border-2 border-gray-200 placeholder-transparent focus:border-primary transition-colors"
                                   placeholder="Password" />
                            <label for="password"
                                   class="absolute left-2 -top-2.5 text-sm text-gray-600 transition-all
                                          peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-3.5 peer-placeholder-shown:left-4
                                          peer-focus:-top-2.5 peer-focus:left-2 peer-focus:text-sm peer-focus:text-primary floating-label">
                                Password
                            </label>
                            <div class="mt-1 text-sm text-gray-500">Enter your existing password to sign in. If you prefer, choose Guest checkout to buy credits without signing in.</div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endguest

                    <!-- Hidden field for coupon code (will be populated by JavaScript) -->
                    <input type="hidden" name="coupon_code" id="applied-coupon-code" value="{{ old('coupon_code') }}">

                    <div class="pt-4">
                        <button type="submit" 
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
                    </div>
                </form>
                @guest
                    <script>
                        (function(){
                            const modeInputs = document.querySelectorAll('input[name="checkout_mode"]');
                            const pwBlock = document.getElementById('account-password-block');
                            const update = () => {
                                const selected = document.querySelector('input[name="checkout_mode"]:checked');
                                if (!selected) return;
                                pwBlock.style.display = selected.value === 'account' ? '' : 'none';
                            };
                            modeInputs.forEach(i => i.addEventListener('change', update));
                            // Initialize on load based on old() value
                            update();
                        })();
                    </script>
                @endguest
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

        // Coupon code functionality
        document.getElementById('apply-coupon').addEventListener('click', function(e) {
            e.preventDefault();
            const couponCode = document.getElementById('coupon-code').value;
            const packageType = '{{ $package->type }}';
            const originalPrice = {{ $package->price }};

            if (!couponCode.trim()) {
                const messageEl = document.getElementById('coupon-message');
                messageEl.textContent = 'Please enter a discount code';
                messageEl.className = 'mt-2 text-sm text-red-600';
                return;
            }

            // Disable button during request
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Applying...';

            fetch('{{ route("booking.apply-coupon") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    coupon_code: couponCode, 
                    package_type: packageType,
                    original_price: originalPrice
                })
            })
            .then(response => response.json())
            .then(data => {
                const messageEl = document.getElementById('coupon-message');
                if (data.success) {
                    messageEl.textContent = data.message;
                    messageEl.className = 'mt-2 text-sm text-green-600';
                    
                    // Show discount in pricing summary
                    document.getElementById('discount-row').classList.remove('hidden');
                    document.getElementById('discount-amount').textContent = `-£${data.discount_amount.toFixed(2)}`;
                    document.getElementById('total-price').textContent = `£${data.new_total.toFixed(2)}`;
                    
                    // Update form with applied coupon
                    document.getElementById('applied-coupon-code').value = couponCode;
                    
                    // Disable input and change button text
                    const couponInput = document.getElementById('coupon-code');
                    couponInput.disabled = true;
                    couponInput.classList.add('bg-gray-100', 'text-gray-500');
                    btn.textContent = 'Applied';
                    btn.classList.add('bg-green-600', 'hover:bg-green-700');
                    btn.classList.remove('bg-black', 'hover:bg-gray-800');
                } else {
                    messageEl.textContent = data.message;
                    messageEl.className = 'mt-2 text-sm text-red-600';
                }
            })
            .catch(error => {
                const messageEl = document.getElementById('coupon-message');
                messageEl.textContent = 'An unexpected error occurred.';
                messageEl.className = 'mt-2 text-sm text-red-600';
            })
            .finally(() => {
                if (!document.getElementById('applied-coupon-code').value) {
                    btn.disabled = false;
                    btn.textContent = 'Apply';
                }
            });
        });

        // Allow applying coupon with Enter key
        document.getElementById('coupon-code').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('apply-coupon').click();
            }
        });
    </script>
</body>
</html>
