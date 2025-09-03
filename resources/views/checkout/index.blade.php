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

    <div class="max-w-4xl mx-auto px-6 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Class Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Class Details</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center">
                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?w=100&h=100&fit=crop&crop=face" 
                                 alt="{{ $class->instructor->name ?? 'Instructor' }}" 
                                 class="w-16 h-16 rounded-full object-cover">
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-900">{{ $class->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $class->instructor->name ?? 'No Instructor' }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Date:</span>
                                <p class="font-medium">{{ \Carbon\Carbon::parse($class->class_date)->format('l, F j, Y') }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Time:</span>
                                <p class="font-medium">{{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Duration:</span>
                                <p class="font-medium">{{ \Carbon\Carbon::parse($class->end_time)->diffInMinutes(\Carbon\Carbon::parse($class->start_time)) }} minutes</p>
                            </div>
                          
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Total:</span>
                            <span class="text-2xl font-bold text-blue-600">£{{ number_format($class->price, 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Details</h2>

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('booking.process-checkout', $class->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stripe Checkout handles payment collection on Stripe-hosted page -->

                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-md font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        Pay with Stripe — £{{ number_format($class->price, 0) }}
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
