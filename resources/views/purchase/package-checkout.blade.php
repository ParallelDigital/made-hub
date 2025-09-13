<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ strtoupper($package->name) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#c8b7ed' } } } }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-black text-white px-6 py-4">
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <a href="{{ route('purchase.index') }}" class="flex items-center space-x-2">
                <img src="{{ asset('made-running.webp') }}" alt="Made Running" class="h-8 w-8">
                <span class="text-xl font-bold">MADE</span>
            </a>
            <a href="{{ route('welcome') }}" class="text-white hover:text-primary">Home</a>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-6 py-10">
        <h1 class="text-3xl font-black text-black mb-6">Checkout</h1>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Package</div>
                        <div class="text-xl font-bold">{{ strtoupper($package->name) }}</div>
                    </div>
                    <div class="text-2xl font-black">£{{ number_format($package->price, 2) }}</div>
                </div>
                @if(!empty($package->validity))
                    <div class="mt-1 text-xs text-gray-500">*{{ strtoupper($package->validity) }}*</div>
                @endif
            </div>

            @if(session('error'))
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
            @endif

            <form action="{{ route('purchase.package.process', ['type' => $package->type]) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input id="name" name="name" type="text" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary" />
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" name="email" type="email" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary" />
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-black text-white rounded-md font-semibold hover:bg-gray-800">Proceed to Payment</button>
            </form>
        </div>
    </div>
</body>
</html>
