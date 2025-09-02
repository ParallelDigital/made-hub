<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Tailwind CSS (CDN) and Alpine.js (for Breeze navigation) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
          tailwind.config = {
            theme: {
              extend: {
                colors: {
                  primary: '#c8b7ed',
                  secondary: '#ffffff',
                  accent: '#000000',
                }
              }
            }
          }
        </script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans antialiased bg-accent text-secondary">
        <div class="min-h-screen bg-accent">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-accent shadow border-b border-neutral-800">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-secondary">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
