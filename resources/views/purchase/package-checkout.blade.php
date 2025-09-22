<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ strtoupper($package->name) }}</title>
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

                <form action="{{ route('purchase.package.process', ['type' => $package->type]) }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="relative">
                        <input id="name" name="name" type="text" required
                               class="peer w-full px-4 py-3 rounded-lg border-2 border-gray-200 placeholder-transparent focus:border-primary transition-colors"
                               placeholder="Full Name" />
                        <label for="name" 
                               class="absolute left-2 -top-2.5 text-sm text-gray-600 transition-all
                                      peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-3.5 peer-placeholder-shown:left-4
                                      peer-focus:-top-2.5 peer-focus:left-2 peer-focus:text-sm peer-focus:text-primary floating-label">
                            Full Name
                        </label>
                        <div class="mt-1 text-sm text-gray-500">Enter your name as it appears on your card</div>
                    </div>

                    <div class="relative">
                        <input id="email" name="email" type="email" required
                               class="peer w-full px-4 py-3 rounded-lg border-2 border-gray-200 placeholder-transparent focus:border-primary transition-colors"
                               placeholder="Email Address" />
                        <label for="email"
                               class="absolute left-2 -top-2.5 text-sm text-gray-600 transition-all
                                      peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-3.5 peer-placeholder-shown:left-4
                                      peer-focus:-top-2.5 peer-focus:left-2 peer-focus:text-sm peer-focus:text-primary floating-label">
                            Email Address
                        </label>
                        <div class="mt-1 text-sm text-gray-500">We'll send your receipt to this email</div>
                    </div>

                    <div class="relative">
                        <input id="password" name="password" type="password" required
                               class="peer w-full px-4 py-3 rounded-lg border-2 border-gray-200 placeholder-transparent focus:border-primary transition-colors"
                               placeholder="Password" />
                        <label for="password"
                               class="absolute left-2 -top-2.5 text-sm text-gray-600 transition-all
                                      peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-3.5 peer-placeholder-shown:left-4
                                      peer-focus:-top-2.5 peer-focus:left-2 peer-focus:text-sm peer-focus:text-primary floating-label">
                            Password
                        </label>
                        <div class="mt-1 text-sm text-gray-500">Create a secure password for your account</div>
                    </div>

                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               class="peer w-full px-4 py-3 rounded-lg border-2 border-gray-200 placeholder-transparent focus:border-primary transition-colors"
                               placeholder="Confirm Password" />
                        <label for="password_confirmation"
                               class="absolute left-2 -top-2.5 text-sm text-gray-600 transition-all
                                      peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-3.5 peer-placeholder-shown:left-4
                                      peer-focus:-top-2.5 peer-focus:left-2 peer-focus:text-sm peer-focus:text-primary floating-label">
                            Confirm Password
                        </label>
                        <div class="mt-1 text-sm text-gray-500">Confirm your password</div>
                    </div>

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
            </div>
        </div>
    </div>
</body>
</html>
