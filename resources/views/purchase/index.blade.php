<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Classes - Made Hub</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('made-running.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('made-running.png') }}">
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
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-black text-white px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <a href="{{ route('welcome') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('made-running.png') }}" alt="Made Running" class="h-15 w-20">
                </a>
                <div class="hidden md:flex space-x-6">
                    <a href="{{ route('welcome') }}" class="text-white hover:text-primary transition-colors">SCHEDULE</a>
                    <a href="{{ route('purchase.index') }}" class="text-primary font-semibold">PURCHASE</a>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="text-white hover:text-primary transition-colors">
                        DASHBOARD
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-white hover:text-primary transition-colors">LOG IN</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="bg-primary text-black px-6 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
                            JOIN NOW
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Header -->
        <div class="mb-8">
            <p class="text-gray-600 text-sm mb-2">Purchase Classes for</p>
            <h1 class="text-4xl font-black text-black">Manchester</h1>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <nav class="space-y-2">
                        <a href="#memberships" class="block py-2 px-3 text-gray-600 hover:text-black hover:bg-gray-50 rounded">MEMBERSHIPS</a>
                        <a href="#class-packages" class="block py-2 px-3 text-gray-600 hover:text-black hover:bg-gray-50 rounded">CLASS PASSES</a>
                    </nav>
                    
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:w-3/4">
                <!-- Memberships Section -->
                <div id="memberships" class="mb-12 lg:w-2/4">
                    <h2 class="text-2xl font-black text-black mb-6">MEMBERSHIPS</h2>
                    @foreach($packages as $package)
                        @if(($package['type'] ?? '') === 'membership')
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                                <div class="bg-primary text-white px-6 py-3">
                                    <h3 class="font-bold text-lg">{{ strtoupper($package['name']) }}</h3>
                                </div>
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="text-xl font-bold text-black">£{{ number_format($package['price'], 2) }}</div>
                                        <div class="text-sm text-gray-600">*{{ strtoupper($package['billing'] ?? 'PER MONTH') }}*</div>
                                    </div>
                                    <a href="{{ env('STRIPE_MEMBERSHIP_LINK', 'https://buy.stripe.com/3cscP32lx7Wr6cw3cd') }}" target="_blank" rel="noopener" class="my-2 inline-flex w-full justify-center items-center px-4 py-2 bg-black text-white rounded-md font-semibold hover:bg-gray-800 transition-colors">BUY NOW</a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Class Packages Section -->
                <div id="class-packages">
                    <h2 class="text-2xl font-black text-black mb-6">CLASS PACKAGES</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($packages as $package)
                            @if(($package['type'] ?? '') !== 'membership')
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                                <!-- Header -->
                                <div class="bg-primary text-white px-4 py-3 text-center">
                                    <h3 class="font-bold text-sm">MANCHESTER CLASS PACKAGE</h3>
                                </div>
                                
                                <div class="p-6 flex flex-col h-full">
                                    <h4 class="font-bold text-black mb-4">{{ strtoupper($package['name']) }}</h4>
                                    
                                    <div class="flex items-baseline mb-4">
                                        <span class="text-3xl font-black text-black">£{{ number_format($package['price'], 2) }}</span>
                                        <div class="ml-auto text-center">
                                            @if(!is_null($package['classes']))
                                                <div class="text-3xl font-black text-black">{{ $package['classes'] }}</div>
                                                <div class="text-xs text-gray-600">{{ $package['classes'] > 1 ? 'classes' : 'class' }}</div>
                                            @else
                                                <div class="text-xl font-bold text-black">Unlimited</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="my-auto">
                                        <a href="{{ route('purchase.package.checkout', ['type' => $package['type']]) }}" class="my-2 inline-flex w-full justify-center items-center px-4 py-2 bg-black text-white rounded-md font-semibold hover:bg-gray-800 transition-colors">BUY NOW</a>
                                        @if(isset($package['validity']))
                                            <div class="my-2 text-xs text-gray-500 text-center">*{{ strtoupper($package['validity']) }}*</div>
                                        @elseif(isset($package['billing']))
                                            <div class="my-2 text-xs text-gray-500 text-center">*{{ strtoupper($package['billing']) }}*</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
