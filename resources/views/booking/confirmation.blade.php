<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - Made Running</title>
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

    <div class="max-w-2xl mx-auto px-6 py-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Booking Confirmed!</h1>
            <p class="text-gray-600 mb-8">Your class has been successfully booked. We look forward to seeing you!</p>

            <!-- Class Details -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Class Details</h2>
                
                <div class="space-y-3 text-left">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Class:</span>
                        <span class="font-medium">{{ $class->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Instructor:</span>
                        <span class="font-medium">{{ $class->instructor->name ?? 'TBA' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Date:</span>
                        <span class="font-medium">{{ \Carbon\Carbon::parse($class->class_date)->format('l, F j, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Time:</span>
                        <span class="font-medium">{{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}</span>
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-8">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 text-left">
                        <h3 class="text-sm font-medium text-blue-800">Important Information</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Please arrive 10 minutes before class starts</li>
                                <li>Bring water and a towel</li>
                                <li>Wear comfortable workout clothes</li>
                                <li>Cancellations must be made 24 hours in advance</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('welcome') }}" 
                   class="bg-primary text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700 transition-colors">
                    Book Another Class
                </a>
                <a href="{{ route('purchase.index') }}" 
                   class="bg-gray-600 text-white px-6 py-3 rounded-md font-medium hover:bg-gray-700 transition-colors">
                    Buy Credits
                </a>
            </div>
        </div>
    </div>
</body>
</html>
