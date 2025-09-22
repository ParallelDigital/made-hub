<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Checkout' }} - {{ config('app.name') }}</title>
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
                <img src="{{ asset('made-running.webp') }}" alt="Made Running" class="h-15 w-20 transition-transform group-hover:scale-105">
            </a>
            <a href="{{ route('welcome') }}" class="text-white hover:text-primary transition-colors duration-200">Back to Home</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-6 py-12">
        <div class="flex items-center space-x-2 mb-8">
            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <h1 class="text-4xl font-black text-gray-900">{{ $title ?? 'Secure Checkout' }}</h1>
        </div>

        {{ $slot }}
    </div>

    {{ $scripts ?? '' }}
</body>
</html>
