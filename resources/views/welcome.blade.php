<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Made Running - Premium Fitness Experience</title>

        <!-- Favicon -->
        <link rel="icon" type="image/webp" href="{{ asset('favicon.webp') }}">
        <link rel="apple-touch-icon" href="{{ asset('made-running.png') }}">

        <!-- Vite Assets (optimized for performance) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-black text-white">
        <!-- Navigation -->
        <div x-data="{ open: false }" class="relative bg-black border-b border-gray-800">
            <nav class="flex items-center justify-between px-4 sm:px-6 py-4">
                <div class="flex items-center space-x-8">
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('made-running.png') }}" alt="Made Running" class="h-15 w-20">
                    </div>

        <!-- Members Only Modal -->
        <div id="membersOnlyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Members Only</h3>
                    <button onclick="closeMembersOnlyModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p class="text-gray-700 mb-4">This class is for members only. Become a member to attend this class and others for free.</p>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('purchase.package.checkout', ['type' => 'membership']) }}" class="w-full px-4 py-3 rounded bg-primary text-black font-semibold hover:bg-opacity-90 text-center">Members Only</a>
                    <button onclick="closeMembersOnlyModal()" class="w-full px-4 py-2 rounded text-gray-700 hover:bg-gray-50 border border-gray-300">Close</button>
                </div>
            </div>
        </div>
                    <div class="hidden lg:flex space-x-6">
                        <a href="{{ route('welcome') }}" class="text-white hover:text-primary transition-colors">SCHEDULE</a>
                        <a href="{{ route('purchase.index') }}" class="text-white hover:text-primary transition-colors">PURCHASE</a>
                        <a href="{{ route('admin.memberships.index') }}" class="text-white hover:text-primary transition-colors">MEMBERSHIPS</a>
                        <a href="#" class="text-white hover:text-primary transition-colors">THE COMMUNITY</a>
                    </div>
                </div>
                <div class="hidden lg:flex items-center space-x-4">
                    <?php if(auth()->check()): ?>
                        <a href="{{ url('/dashboard') }}" class="bg-primary text-black px-6 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
                            DASHBOARD
                        </a>
                    <?php else: ?>
                        <a href="{{ route('login') }}" class="text-white hover:text-primary transition-colors">LOG IN</a>
                        <a href="{{ route('register') }}" class="bg-primary text-black px-6 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
                            JOIN NOW
                        </a>
                    <?php endif; ?>
                </div>
                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button x-on:click="open = !open" class="text-white hover:text-primary focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </nav>

            <!-- Mobile Menu -->
            <div x-show="open" x-on:click.outside="open = false" class="lg:hidden bg-black border-b border-gray-800 absolute w-full z-40" x-transition>
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    <a href="{{ route('welcome') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:text-primary hover:bg-gray-900">SCHEDULE</a>
                    <a href="{{ route('purchase.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:text-primary hover:bg-gray-900">PURCHASE</a>
                    <a href="{{ route('admin.memberships.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:text-primary hover:bg-gray-900">MEMBERSHIPS</a>
                    <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:text-primary hover:bg-gray-900">THE COMMUNITY</a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-700">
                    <div class="px-2 space-y-2">
                        <?php if(auth()->check()): ?>
                            <a href="{{ url('/dashboard') }}" class="block w-full text-left bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">DASHBOARD</a>
                        <?php else: ?>
                            <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:text-primary hover:bg-gray-900">LOG IN</a>
                            <a href="{{ route('register') }}" class="block w-full text-center bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">JOIN NOW</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('error'))
        <div class="bg-red-600 text-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 text-sm font-medium">
                {{ session('error') }}
            </div>
        </div>
        @endif
        @if (session('success'))
        <div class="bg-green-600 text-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 text-sm font-medium">
                {{ session('success') }}
            </div>
        </div>
        @endif

        <!-- Hero Section -->
        <div class="hero-section relative h-[550px] md:h-[550px] flex items-center overflow-hidden">
            <!-- Background Image -->
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat z-5" style="background-image: url('{{ asset('made-club.jpg') }}');"></div>
            <div class="absolute inset-0 bg-black/70 z-20"></div>

            <!-- Content -->
            <div class="relative z-30 px-4 sm:px-6 max-w-6xl container mx-auto">

                <h1 class="hero-title text-4xl sm:text-5xl md:text-8xl font-black mb-6 tracking-tight">
                    <span class="block text-white">MADE TO</span>
                    <span class="block text-primary">ELEVATE</span>
                </h1>

                <div class="button-group flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                    <a href="#membership" class="w-full sm:w-auto bg-primary text-black px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-bold rounded hover:bg-opacity-90 transition-all transform hover:scale-105 text-center">
                        BECOME A MEMBER
                    </a>
                    <a href="#schedule" class="w-full sm:w-auto border-2 border-white text-white px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-bold rounded hover:bg-white hover:text-black transition-all text-center">
                        BOOK A CLASS
                    </a>
                </div>
            </div>
        </div>

        <!-- Membership Section -->
        <div id="membership" class="bg-black text-white py-14 sm:py-16 lg:py-20 uppercase">
            <div class="max-w-7xl mx-auto p-5 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-12 sm:mb-14">
                    <h3 class="text-3xl sm:text-4xl md:text-5xl font-black tracking-tight leading-tight">MADE MEMBERSHIP</h3>
                    <p class="mx-auto mt-3 max-w-2xl text-sm sm:text-base text-gray-300">Unlock unlimited access to our world-class facilities</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-center">
                    <!-- Features -->
                    <div class="space-y-6 sm:space-y-7 max-w-xl mx-auto lg:mx-0">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary text-purple-300 flex items-center justify-center">
                                <!-- dumbbell icon -->
                                <svg class="svg-inline--fa fa-dumbbell w-9 h-4" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="dumbbell" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" data-fa-i2svg=""><path fill="currentColor" d="M96 64c0-17.7 14.3-32 32-32h32c17.7 0 32 14.3 32 32V224v64V448c0 17.7-14.3 32-32 32H128c-17.7 0-32-14.3-32-32V384H64c-17.7 0-32-14.3-32-32V288c-17.7 0-32-14.3-32-32s14.3-32 32-32V160c0-17.7 14.3-32 32-32H96V64zm448 0v64h32c17.7 0 32 14.3 32 32v64c17.7 0 32 14.3 32 32s-14.3 32-32 32v64c0 17.7-14.3 32-32 32H544v64c0 17.7-14.3 32-32 32H480c-17.7 0-32-14.3-32-32V288 224 64c0-17.7 14.3-32 32-32h32c17.7 0 32 14.3 32 32zM416 224v64H224V224H416z"></path></svg>
                            </div>
                            <div>
                                <div class="font-semibold leading-tight">5 Classes Per Week</div>
                                <div class="text-gray-400 text-sm leading-relaxed">Access to all group fitness classes</div>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12  rounded-full bg-primary text-purple-300 flex items-center justify-center">
                                <!-- crown icon -->
                                <svg class="svg-inline--fa fa-crown w-9 h-4" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="crown" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><path fill="currentColor" d="M309 106c11.4-7 19-19.7 19-34c0-22.1-17.9-40-40-40s-40 17.9-40 40c0 14.4 7.6 27 19 34L209.7 220.6c-9.1 18.2-32.7 23.4-48.6 10.7L72 160c5-6.7 8-15 8-24c0-22.1-17.9-40-40-40S0 113.9 0 136s17.9 40 40 40c.2 0 .5 0 .7 0L86.4 427.4c5.5 30.4 32 52.6 63 52.6H426.6c30.9 0 57.4-22.1 63-52.6L535.3 176c.2 0 .5 0 .7 0c22.1 0 40-17.9 40-40s-17.9-40-40-40s-40 17.9-40 40c0 9 3 17.3 8 24l-89.1 71.3c-15.9 12.7-39.5 7.5-48.6-10.7L309 106z"></path></svg>
                            </div>
                            <div>
                                <div class="font-semibold leading-tight">Members Only Classes</div>
                                <div class="text-gray-400 text-sm leading-relaxed">Exclusive premium training sessions</div>
                            </div>
                        </div>

                        <!-- <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary text-purple-300 flex items-center justify-center">
                                <svg class="svg-inline--fa fa-calendar-check w-9 h-4" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="calendar-check" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zM329 305c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-95 95-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L329 305z"></path></svg>
                            </div>
                            <div>
                                <div class="font-semibold leading-tight">First Access on Events</div>
                                <div class="text-gray-400 text-sm leading-relaxed">Priority booking for special events</div>
                            </div>
                        </div> -->

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary text-purple-300 flex items-center justify-center">
                                <!-- clock icon -->
                                <svg class="svg-inline--fa fa-clock w-9 h-4" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="clock" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M256 0a256 256 0 1 1 0 512A256 256 0 1 1 256 0zM232 120V256c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2V120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"></path></svg>
                            </div>
                            <div>
                                <div class="font-semibold leading-tight">Early Booking Access</div>
                                <div class="text-gray-400 text-sm leading-relaxed">Book classes 48 hours in advance</div>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary text-purple-300 flex items-center justify-center">
                                <!-- building icon -->
                                <svg class="svg-inline--fa fa-users w-9 h-4" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="users" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" data-fa-i2svg=""><path fill="currentColor" d="M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z"></path></svg>
                            </div>
                            <div>
                                <div class="font-semibold leading-tight">Hubspace Access</div>
                                <div class="text-gray-400 text-sm leading-relaxed">Collaborative workspace and lounge</div>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary text-purple-300 flex items-center justify-center">
                                <!-- meeting room icon -->
                                <svg class="svg-inline--fa fa-door-open w-9 h-4" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="door-open" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><path fill="currentColor" d="M320 32c0-9.9-4.5-19.2-12.3-25.2S289.8-1.4 280.2 1l-179.9 45C79 51.3 64 70.5 64 92.5V448H32c-17.7 0-32 14.3-32 32s14.3 32 32 32H96 288h32V480 32zM256 256c0 17.7-10.7 32-24 32s-24-14.3-24-32s10.7-32 24-32s24 14.3 24 32zm96-128h96V480c0 17.7 14.3 32 32 32h64c17.7 0 32-14.3 32-32s-14.3-32-32-32H512V128c0-35.3-28.7-64-64-64H352v64z"></path></svg>    
                            </div>
                            <div>
                                <div class="font-semibold leading-tight">Free Meeting Room</div>
                                <div class="text-gray-400 text-sm leading-relaxed">Private space for consultations</div>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary text-purple-300 flex items-center justify-center">
                                <!-- gift icon -->
                                <svg class="svg-inline--fa fa-gift w-9 h-4" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="gift" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M190.5 68.8L225.3 128H224 152c-22.1 0-40-17.9-40-40s17.9-40 40-40h2.2c14.9 0 28.8 7.9 36.3 20.8zM64 88c0 14.4 3.5 28 9.6 40H32c-17.7 0-32 14.3-32 32v64c0 17.7 14.3 32 32 32H480c17.7 0 32-14.3 32-32V160c0-17.7-14.3-32-32-32H438.4c6.1-12 9.6-25.6 9.6-40c0-48.6-39.4-88-88-88h-2.2c-31.9 0-61.5 16.9-77.7 44.4L256 85.5l-24.1-41C215.7 16.9 186.1 0 154.2 0H152C103.4 0 64 39.4 64 88zm336 0c0 22.1-17.9 40-40 40H288h-1.3l34.8-59.2C329.1 55.9 342.9 48 357.8 48H360c22.1 0 40 17.9 40 40zM32 288V464c0 26.5 21.5 48 48 48H224V288H32zM288 512H432c26.5 0 48-21.5 48-48V288H288V512z"></path></svg>
                            </div>
                            <div>
                                <div class="font-semibold leading-tight">Monthly Giveaways</div>
                                <div class="text-gray-400 text-sm leading-relaxed">Exclusive prizes and rewards</div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Card -->
                    <div class="max-w-md w-full mx-auto lg:mx-0">
                        <div class="relative rounded-3xl border border-white/5 ring-1 ring-white/5 bg-gradient-to-br from-[#1a1527] via-[#141021] to-[#0e0a18] p-6 sm:p-8 shadow-2xl mt-5">
                            <div class="text-center">
                                <div class="text-base sm:text-lg font-semibold text-gray-100">Made Membership</div>
                                <div class="mt-3 text-5xl sm:text-6xl font-black tracking-tight">£30</div>
                                <div class="mt-1 mb-4 text-gray-400 text-sm">per month</div>

                                <a href="https://buy.stripe.com/3cscP32lx7Wr6cw3cd" class="relative block top-5 w-full sm:w-auto bg-primary text-black px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-bold rounded hover:bg-opacity-90 transition-all transform hover:scale-105 text-center">Get Signed Up</a>

                                <div class="mt-4 text-xs text-gray-500">Instant Access • No setup fees</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flexible Class Passes Section -->
        <div id="class-packages" class="relative bg-[#1a1a2e] text-white py-14 sm:py-16 lg:py-20 overflow-hidden">
            <div aria-hidden="true" class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('made-1.jpg') }}'); opacity: 0.10;"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h3 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-4">CLASS PASSESS</h3>
                    <p class="text-gray-400 text-lg uppercase">Choose the perfect plan for your lifestyle</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                   <!-- Single Class -->
                    <div class="pricing-card bg-black rounded-2xl p-8 flex flex-col border border-gray-700/50 hover:border-gray-600/50 transition-all duration-300">
                        <h3 class="text-xl font-bold text-white mb-6">5 CLASSES</h3>
                        <div class="mb-6">
                            <p class="text-4xl font-bold text-white mb-1">£32.50</p>
                            <p class="text-gray-400 text-sm">£6.50 per class</p>
                        </div>
                        <a href="https://gym.made-reg.co.uk/purchase/package/package_5" target="_blank" rel="noopener noreferrer" class="w-full">
                            <button class="w-full bg-primary text-black font-semibold py-3 rounded-lg hover:bg-[#d8c7ff] transition-colors">Purchase</button>
                        </a>
                    </div>

                    <!-- 10 Class Pack - Most Popular -->
                    <div class="relative pricing-card bg-black rounded-2xl p-8 flex flex-col border-2 border-primary hover:border-[#d8c7ff] transition-all duration-300">
                        <div class="popular-badge-wrapper">
                            <span class="popular-badge">Most Popular</span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-6 mt-2">10 CLASSES</h3>
                        <div class="mb-6 flex-grow">
                            <p class="text-4xl font-bold text-white mb-1">£50.00</p>
                            <p class="text-gray-400 text-sm">£5 per class</p>
                        </div>
                        <a href="https://gym.made-reg.co.uk/purchase/package/package_10" target="_blank" rel="noopener noreferrer" class="w-full">
                            <button class="w-full bg-primary text-black font-semibold py-3 rounded-lg hover:bg-[#d8c7ff] transition-colors">Purchase</button>
                        </a>
                    </div>

                    <!-- Monthly Unlimited -->
                    <div class="pricing-card bg-black rounded-2xl p-8 flex flex-col border border-gray-700/50 hover:border-gray-600/50 transition-all duration-300">
                        <h3 class="text-xl font-bold text-white mb-6">UNLIMITED CLASSES</h3>
                        <div class="mb-6">
                            <p class="text-4xl font-bold text-white mb-1">£90.00</p>
                            <p class="text-gray-400 text-sm">Unlimited classes</p>
                        </div>
                        <a href="https://gym.made-reg.co.uk/purchase/package/unlimted" target="_blank" rel="noopener noreferrer" class="w-full">
                            <button class="w-full bg-primary text-black font-semibold py-3 rounded-lg hover:bg-[#d8c7ff] transition-colors">Purchase</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Section -->
        <div id="schedule" class="bg-white text-black py-6 sm:py-8">
            <h3 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4 text-center mt-5 pt-5">BOOK A CLASS</h3>
            <div class="schedule-container max-w-7xl mx-auto" style="opacity: 0; transition: opacity 0.3s ease-in-out;">
                <!-- Week Navigation -->
                <div class="week-nav-container">
                    <div class="flex items-center justify-between">
                        <!-- Previous Week Arrow -->
                        <button onclick="onArrowNav('{{ $prevWeek }}')" class="nav-arrow" id="prev-week-btn">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>

                        <!-- Week Days -->
                        <div class="week-navigation" id="week-days">
                            <?php foreach($weekDays as $day): ?>
                            <button data-date="{{ $day['full_date'] }}" onclick="loadDate('{{ $day['full_date'] }}')" class="week-day-btn {{ $day['is_selected'] ? 'selected' : ($day['is_today'] ? 'today' : '') }}">
                                <div class="day-number">{{ $day['is_today'] ? 'Today' : \Carbon\Carbon::parse($day['full_date'])->format('M j') }}</div>
                                <div class="day-name">{{ strtoupper($day['day']) }}</div>
                            </button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Next Week Arrow -->
                        <button onclick="onArrowNav('{{ $nextWeek }}')" class="nav-arrow" id="next-week-btn">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Selected Date Header -->
                <div class="date-header">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h2 id="selected-date-header">{{ $selectedDate->format('l, F j, Y') }}</h2>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                onclick="loadDate('{{ now()->toDateString() }}')"
                                class="today-btn">
                                Today
                            </button>
                        </div>
                    </div>
                </div>

                    <!-- Loading Spinner -->
                    <div id="loading-spinner" class="hidden px-6 py-12">
                        <div class="text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            <p class="mt-4 text-gray-700">Loading classes...</p>
                        </div>
                    </div>

                    <!-- Selected Date Classes -->
                    <div class="classes-section" id="classes-container">
                        <?php if($selectedDateClasses->count() > 0): ?>
                            <div id="classes-list">
                                <?php foreach($selectedDateClasses as $class): ?>
                                @php
                                    $classDate = $class->class_date instanceof \Carbon\Carbon ? $class->class_date->toDateString() : (string) $class->class_date;
                                    $start = !empty($class->start_time)
                                        ? \Carbon\Carbon::parse(trim(($classDate ?: now()->toDateString()) . ' ' . $class->start_time))
                                        : null;
                                    $end = !empty($class->end_time)
                                        ? \Carbon\Carbon::parse(trim(($classDate ?: now()->toDateString()) . ' ' . $class->end_time))
                                        : null;
                                    if ($start && $end) {
                                        $endForDiff = $end->lessThan($start) ? $end->copy()->addDay() : $end;
                                        $duration = $start->diffInMinutes($endForDiff);
                                    } else {
                                        $duration = $class->duration ?? 60;
                                    }
                                    // Compute booking state for data attributes and reuse below
                                    $currentBookings = App\Models\Booking::where('fitness_class_id', $class->id)->count();
                                    $availableSpots = max(0, $class->max_spots - $currentBookings);
                                    $isFull = $availableSpots <= 0;
                                    $startDateTime = \Carbon\Carbon::parse($selectedDate->toDateString() . ' ' . ($class->start_time ?? '00:00'));
                                    $isPast = $startDateTime->lt(now());
                                    $isBookedByMe = auth()->check() ? $class->bookings->contains('user_id', auth()->id()) : false;
                                    $isMembersOnly = (bool) ($class->members_only ?? false);
                                @endphp
                                <div class="class-card" data-class-id="{{ $class->id }}" data-price="{{ $class->price ?? 0 }}" data-is-past="{{ $isPast ? '1' : '0' }}" data-is-full="{{ $isFull ? '1' : '0' }}" data-is-booked="{{ $isBookedByMe ? '1' : '0' }}" data-members-only="{{ $isMembersOnly ? '1' : '0' }}" data-description="{{ $class->description }}">
                                    @if($isMembersOnly)
                                        <div class="ribbon-members">Members Class</div>
                                    @endif
                                    <div class="class-time-section">
                                        <div class="class-time">{{ $start ? $start->format('g:i A') : '' }}</div>
                                        <div class="class-duration">{{ $duration }} min.</div>
                                    </div>
                                    
                                    <div class="class-location">
                                        Manchester
                                    </div>
                                    
                                    <div class="instructor-section">
                                        <img src="{{ $class->instructor->photo_url ?? 'https://www.gravatar.com/avatar/?d=mp&s=100' }}" 
                                             alt="{{ $class->instructor->name ?? 'Instructor' }}" 
                                             class="instructor-avatar">
                                    </div>
                                    
                                    <div class="class-info-section">
                                        <h3 class="class-title">{{ $class->name }} ({{ $duration }} Min)</h3>
                                        <p class="class-instructor-name">{{ $class->instructor->name ?? 'No Instructor' }}</p>
                                    </div>
                                    
                                    <div class="book-section">
                                        @if($isPast)
                                            <button disabled class="reserve-button">Past</button>
                                        @elseif($isBookedByMe)
                                            <button disabled class="reserve-button bg-green-100 text-green-700 border-green-300">Booked</button>
                                        @elseif($isFull)
                                            <button disabled class="reserve-button">Class Full</button>
                                        @else
                                            @if($isMembersOnly)
                                                @if(auth()->check() && auth()->user()->hasActiveMembership())
                                                    <button onclick="openBookingModal({{ $class->id }}, 0)" class="reserve-button">Book (Members)</button>
                                                @elseif(auth()->check())
                                                    <button onclick="openBookingModal({{ $class->id }}, 0)" class="reserve-button">Members Only</button>
                                                @else
                                                    <button onclick="openBookingModal({{ $class->id }}, 0)" class="reserve-button">Members Only</button>
                                                @endif
                                            @else
                                                <button onclick="openBookingModal({{ $class->id }}, {{ $class->price }})" class="reserve-button">Reserve</button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-classes" id="no-classes">
                                <svg class="no-classes-icon mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No classes scheduled</h3>
                                <p class="text-gray-600">There are no classes scheduled for this date.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Modal -->
        <div id="bookingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Book This Class</h3>
                    <button onclick="closeBookingModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="text-sm text-gray-600 mb-4">
                        <p id="bookingModalMessage">Choose how you'd like to book this class:</p>
                    </div>
                    
                    <div id="classDescription" class="mb-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-700" style="display: none;"></div>
                    
                    @auth
                        <button onclick="bookWithCredits(window.selectedClassId)" 
                                class="w-full flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div id="useCreditsLabel" class="font-medium text-gray-900">Use Credits</div>
                                    @if(auth()->user()->hasActiveUnlimitedPass())
                                        <div class="text-sm text-gray-500">Unlimited pass active @if(auth()->user()->unlimited_pass_expires_at) until {{ auth()->user()->unlimited_pass_expires_at->format('j M Y') }} @endif</div>
                                    @else
                                        <div class="text-sm text-gray-500">You have {{ auth()->user()->hasActiveMembership() ? auth()->user()->getAvailableCredits() : auth()->user()->getNonMemberAvailableCredits() }} {{ auth()->user()->hasActiveMembership() ? 'monthly credits' : 'credits' }}</div>
                                    @endif
                                    <span id="availableCreditsData" data-credits="{{ auth()->user()->hasActiveMembership() ? auth()->user()->getAvailableCredits() : auth()->user()->getNonMemberAvailableCredits() }}" class="hidden"></span>
                                </div>
                            </div>
                            <div class="text-primary font-semibold" id="useCreditsRight">1 Credit</div>
                        </button>
                    @else
                        <button onclick="openLoginModal()" 
                                class="w-full flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="font-medium text-gray-900">Sign in to continue</div>
                                    <div class="text-sm text-gray-500">Access member benefits and book classes</div>
                                </div>
                            </div>
                            <div class="text-green-600 font-semibold">Sign In</div>
                        </button>
                    @endauth
                    
                    <button id="payButton" onclick="buySpot(window.selectedClassId)" 
                            class="w-full flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-colors">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="font-medium text-gray-900">Reserve a spot</div>
                                <div class="text-sm text-gray-500">Pay with card</div>
                            </div>
                        </div>
                        <div class="text-gray-800 font-semibold" id="modalClassPrice">£0</div>
                    </button>

                    <!-- Members-only specific options -->
                    <div id="membersOnlyOptions" class="hidden space-y-3">
                        <div class="border-t border-gray-200 pt-4">
                            <p class="text-sm text-gray-600 mb-3">It looks like you don't have a membership</p>
                            <a href="{{ route('purchase.package.checkout', ['type' => 'membership']) }}" class="w-full flex items-center justify-between p-4 border border-primary rounded-lg hover:bg-primary hover:text-white transition-colors">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-black font-medium">Become a Member</div>
                                    </div>
                                </div>
                                <div class="font-semibold">Join Now</div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <button onclick="closeBookingModal()"
                            class="w-full px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Login Modal (for guests clicking Use Credits) -->
        <div id="loginModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Sign in to continue</h3>
                    <button onclick="closeLoginModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label for="loginEmail" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="loginEmail" type="email" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-black" placeholder="you@example.com">
                    </div>
                    <div>
                        <label for="loginPassword" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="loginPassword" type="password" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-black" placeholder="••••••••">
                    </div>
                    <p id="loginError" class="text-sm text-red-600 hidden"></p>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button onclick="closeLoginModal()" class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button onclick="submitModalLogin()" class="px-4 py-2 rounded bg-primary text-black font-semibold hover:bg-opacity-90">Sign In</button>
                </div>
            </div>
        </div>

        

        <!-- No Credits Modal (prompt to purchase) -->
        <div id="noCreditsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">You don’t have enough credits</h3>
                    <button onclick="closeNoCreditsModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p class="text-gray-700 mb-4">Would you like to buy credits or buy a class pass?</p>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('purchase.package.checkout', ['type' => 'package_5']) }}" class="w-full px-4 py-3 rounded border border-gray-300 text-black font-semibold hover:bg-gray-50 text-center">Buy Credits</a>
                    <a href="https://gym.made-reg.co.uk/purchase#class-packages" class="w-full px-4 py-3 rounded bg-primary text-black font-semibold hover:bg-opacity-90 text-center">Buy Class Pass</a>
                    <button onclick="closeNoCreditsModal()" class="w-full px-4 py-2 rounded text-gray-700 hover:bg-gray-50">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Confirm Modal -->
        <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Please confirm</h3>
                    <button onclick="confirmModalNo()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p id="confirmMessage" class="text-gray-700 mb-6">Are you sure?</p>
                <div class="flex justify-end gap-3">
                    <button onclick="confirmModalNo()" class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button onclick="confirmModalYes()" class="px-4 py-2 rounded bg-primary text-black font-semibold hover:bg-opacity-90">Yes, continue</button>
                </div>
            </div>
        </div>

        <!-- Feedback Modal -->
        <div id="feedbackModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <div class="flex justify-between items-start mb-2">
                    <h3 id="feedbackTitle" class="text-lg font-semibold text-gray-900">Notice</h3>
                    <button onclick="closeFeedbackModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p id="feedbackMessage" class="text-gray-700"></p>
                <div class="mt-6 text-right">
                    <button onclick="closeFeedbackModal()" class="px-4 py-2 rounded bg-primary text-black font-semibold hover:bg-opacity-90">OK</button>
                </div>
            </div>
        </div>

        <!-- Scrolling Images Section -->
        <section id="facilities" class="bg-black text-white py-10 sm:py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="carousel-container">
                    <button id="facilitiesPrev" class="carousel-arrow left" aria-label="Previous">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div id="facilitiesTrack" class="carousel-track">
                        <img src="{{ asset('meeting-room.jpg') }}" alt="Meeting room" class="carousel-item shadow-lg" loading="lazy">
                        <img src="{{ asset('gym-1.jpg') }}" alt="Made Running Gym" class="carousel-item shadow-lg" loading="lazy">
                        <img src="{{ asset('studio.jpg') }}" alt="Yoga Studio" class="carousel-item shadow-lg" loading="lazy">
                        <img src="{{ asset('physio-room.jpg') }}" alt="Physio Room" class="carousel-item shadow-lg" loading="lazy">
                    </div>
                    <button id="facilitiesNext" class="carousel-arrow right" aria-label="Next">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </section>


        <!-- Membership section has been moved above the Schedule section -->

        <!-- Footer -->
        <footer class="bg-black border-t border-gray-800 py-8 sm:py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="footer-grid grid grid-cols-1 md:grid-cols-4 gap-6 sm:gap-8 text-center md:text-left">
                    <div class="footer-section">
                        <div class="flex items-center justify-center md:justify-start space-x-2 mb-4">
                            <img src="{{ asset('made-running.png') }}" alt="Made Running" class="h-15 w-20">
                        </div>
                        <p class="text-gray-400 text-sm">
                            Transform your fitness journey with our high-intensity training programs designed to push your limits.
                        </p>
                    </div>

                    <!-- <div class="footer-section">
                        <h3 class="text-white font-semibold mb-4">COMPANY</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                        </ul>
                    </div>

                    <div class="footer-section">
                        <h3 class="text-white font-semibold mb-4">SUPPORT</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Terms</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Privacy</a></li>
                        </ul>
                    </div> -->

                    <!-- <div class="footer-section">
                        <h3 class="text-white font-semibold mb-4">CONNECT</h3>
                        <div class="flex space-x-4 justify-center md:justify-start">
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>
                                </svg>
                            </a>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>


        <!-- Laravel Auth Data for JavaScript -->
        <script>
            window.laravelAuth = {
                isAuth: {{ auth()->check() ? 'true' : 'false' }},
                isMember: {{ auth()->check() && auth()->user()->hasActiveMembership() ? 'true' : 'false' }},
                isUnlimited: {{ auth()->check() && method_exists(auth()->user(), 'hasActiveUnlimitedPass') && auth()->user()->hasActiveUnlimitedPass() ? 'true' : 'false' }}
            };
        </script>
    </body>
</html>
