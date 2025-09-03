<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Successful - Made Running</title>
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
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-lg text-center">
            <svg class="mx-auto h-12 w-12 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h1 class="mt-4 text-2xl font-bold text-gray-900">Booking Successful!</h1>
            <p class="mt-2 text-gray-600">Thank you for your purchase. A confirmation email has been sent to you.</p>
            <div class="mt-6">
                <a href="{{ route('welcome') }}" class="bg-primary text-black px-5 py-2.5 rounded-md text-sm font-semibold transition-colors shadow-md hover:bg-opacity-90">
                    Back to Classes
                </a>
            </div>
        </div>
    </div>
</body>
</html>
