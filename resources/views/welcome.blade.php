<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Made Running - Premium Fitness Experience</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Vite Assets (single source of CSS/JS to keep styles stable) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-black text-white font-inter">
        <!-- Navigation -->
        <div x-data="{ open: false }" class="relative bg-black border-b border-gray-800">
            <nav class="flex items-center justify-between px-4 sm:px-6 py-4">
                <div class="flex items-center space-x-8">
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('made-running.webp') }}" alt="Made Running" class="h-15 w-20">
                    </div>
                    <div class="hidden lg:flex space-x-6">
                        <a href="{{ route('welcome') }}" class="text-white hover:text-primary transition-colors">SCHEDULE</a>
                        <a href="{{ route('purchase.index') }}" class="text-white hover:text-primary transition-colors">PURCHASE</a>
                        <a href="{{ route('admin.memberships.index') }}" class="text-white hover:text-primary transition-colors">MEMBERSHIPS</a>
                        <a href="#" class="text-white hover:text-primary transition-colors">THE COMMUNITY</a>
                    </div>
                </div>
                <div class="hidden lg:flex items-center space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-primary text-black px-6 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">
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
                <!-- Mobile menu button -->
                <div class="lg:hidden">
                    <button @click="open = !open" class="text-white hover:text-primary focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </nav>

            <!-- Mobile Menu -->
            <div x-show="open" @click.away="open = false" class="lg:hidden bg-black border-b border-gray-800 absolute w-full z-40" x-transition>
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    <a href="{{ route('welcome') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:text-primary hover:bg-gray-900">SCHEDULE</a>
                    <a href="{{ route('purchase.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:text-primary hover:bg-gray-900">PURCHASE</a>
                    <a href="{{ route('admin.memberships.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:text-primary hover:bg-gray-900">MEMBERSHIPS</a>
                    <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:text-primary hover:bg-gray-900">THE COMMUNITY</a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-700">
                    <div class="px-2 space-y-2">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="block w-full text-left bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">DASHBOARD</a>
                        @else
                            <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:text-primary hover:bg-gray-900">LOG IN</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="block w-full text-center bg-primary text-black px-4 py-2 rounded font-semibold hover:bg-opacity-90 transition-all">JOIN NOW</a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="relative h-[550px] md:h-[550px] flex items-center overflow-hidden">
            <!-- Background Image -->
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat z-5" style="background-image: url('{{ asset('made-club.jpg') }}');"></div>
            <div class="absolute inset-0 bg-black/70 z-20"></div>

            <!-- Content -->
            <div class="relative z-30 px-6 max-w-6xl container mx-auto">
                
                <h1 class="text-5xl md:text-8xl font-black mb-6 tracking-tight">
                    <span class="block text-white">MADE TO</span>
                    <span class="block text-primary">ELEVATE</span>
                </h1>
                
                <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto bg-primary text-black px-8 py-4 text-lg font-bold rounded hover:bg-opacity-90 transition-all transform hover:scale-105 text-center">
                        BOOK YOUR CLASS
                    </a>
                    <a href="#schedule" class="w-full sm:w-auto border-2 border-white text-white px-8 py-4 text-lg font-bold rounded hover:bg-white hover:text-black transition-all text-center">
                        VIEW SCHEDULE
                    </a>
                </div>
            </div>
        </div>

        <!-- Membership Section -->
        <div id="membership" class="bg-white text-black py-12 sm:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <!-- Image Column -->
                    <div>
                        <img src="{{ asset('made-club.jpg') }}" alt="Group fitness class" class="rounded-lg shadow-lg w-full h-full object-cover">
                    </div>
                    <!-- Content Column -->
                    <div class="text-left">
                        <h2 class="text-4xl md:text-5xl font-black text-gray-900 mb-6 leading-tight">ARE YOU READY <br>TO ELEVATE</h2>
                        <p class="text-sm font-bold uppercase tracking-widest text-gray-500 mb-2">PERKS OF MEMBERSHIP</p>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <svg class="w-6 h-6 text-primary mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-semibold text-gray-700 uppercase">Personal Accountability Adviser</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-6 h-6 text-primary mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-semibold text-gray-700 uppercase">Access to Co working Spaces (Free Wi-fi)</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-6 h-6 text-primary mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-semibold text-gray-700 uppercase">Seminars – marketing, personal finance, plus more</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-6 h-6 text-primary mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-semibold text-gray-700 uppercase">Access to the Made Gym</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-6 h-6 text-primary mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-semibold text-gray-700 uppercase">Exclusive networking Group Chat</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-6 h-6 text-primary mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-semibold text-gray-700 uppercase">5 classes a month (HIIT, Yoga, Dance)</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-6 h-6 text-primary mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-semibold text-gray-700 uppercase">Early access to Events</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-6 h-6 text-primary mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-semibold text-gray-700 uppercase">1 Free Physio Consultation</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-6 h-6 text-primary mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-semibold text-gray-700 uppercase">50% off Room Hire</span>
                            </li>
                        </ul>
                        <a href="#" class="inline-block bg-black text-white px-10 py-4 text-sm font-bold uppercase tracking-widest rounded hover:bg-gray-800 transition-all">
                            SIGN UP NOW
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Section -->
        <div id="schedule" class="bg-black text-white py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Week Navigation -->
                <div class="px-4 sm:px-6 py-4">
                        <div class="flex items-center justify-between">
                            <!-- Previous Week Arrow -->
                            <button onclick="loadDate('{{ $prevWeek }}')" class="p-2 text-gray-300 transition-colors" id="prev-week-btn">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Week Days -->
                            <div class="flex space-x-2 flex-1 overflow-x-auto px-2" id="week-days">
                                @foreach($weekDays as $day)
                                <button onclick="loadDate('{{ $day['full_date'] }}')" class="text-center px-3 py-2 transition-colors cursor-pointer flex-1 min-w-[60px]
                                    {{ $day['is_selected'] ? 'text-white' : ($day['is_today'] ? 'text-white font-bold' : 'text-gray-400') }}">
                                    <div class="text-xs font-medium">{{ $day['day'] }}</div>
                                    <div class="text-xl font-bold">{{ $day['date'] }}</div>
                                    @if($day['is_selected'])
                                        <div class="w-8 h-1 bg-primary mx-auto mt-1"></div>
                                    @endif
                                </button>
                                @endforeach
                            </div>
                            
                            <!-- Next Week Arrow -->
                            <button onclick="loadDate('{{ $nextWeek }}')" class="p-2 text-gray-300 transition-colors" id="next-week-btn">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                </div>

                <!-- Selected Date Header -->
                <div class="px-4 sm:px-6 py-4 bg-black border-t border-b border-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white" id="selected-date-header">{{ $selectedDate->format('l, F j, Y') }}</h3>
                            <button
                                type="button"
                                onclick="loadDate('{{ now()->toDateString() }}')"
                                class="inline-flex items-center px-3 py-1 rounded-md bg-white border border-gray-300 text-black text-sm hover:bg-gray-50">
                                Today
                            </button>
                        </div>
                </div>

                    <!-- Loading Spinner -->
                    <div id="loading-spinner" class="hidden px-6 py-12">
                        <div class="text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            <p class="mt-4 text-gray-600">Loading classes...</p>
                        </div>
                    </div>

                    <!-- Selected Date Classes -->
                    <div class="px-6 py-4" id="classes-container">
                        @if($selectedDateClasses->count() > 0)
                            <div class="space-y-2 sm:space-y-3" id="classes-list">
                                @foreach($selectedDateClasses as $class)
                                <div class="flex items-start py-6 border-b border-gray-800 last:border-b-0">
                                    <div class="flex-shrink-0 w-20 text-left">
                                        <div class="text-lg font-bold text-white">{{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }}</div>
                                        <div class="text-sm text-gray-400">{{ \Carbon\Carbon::parse($class->end_time)->diffInMinutes(\Carbon\Carbon::parse($class->start_time)) }} min.</div>
                                    </div>

                                    <div class="flex-1 ml-6">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center mr-4">
                                                <img src="{{ $class->instructor && $class->instructor->photo ? asset('storage/' . $class->instructor->photo) : 'https://www.gravatar.com/avatar/?d=mp&s=100' }}"
                                                     alt="{{ $class->instructor->name ?? 'Instructor' }}"
                                                     class="w-10 h-10 rounded-full object-cover">
                                            </div>

                                            <div class="flex-1">
                                                <div class="text-lg font-semibold text-white mb-1">{{ $class->name }} ({{ \Carbon\Carbon::parse($class->end_time)->diffInMinutes(\Carbon\Carbon::parse($class->start_time)) }} Min)</div>
                                                <div class="text-sm text-gray-300 mb-1">{{ $class->instructor->name ?? 'No Instructor' }}</div>
                                                <div class="text-sm text-gray-400">Manchester Red Room</div>
                                                <div class="text-sm text-gray-400">Manchester</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12" id="no-classes">
                                
                                <h3 class="text-lg font-medium text-white mb-2">No classes scheduled</h3>
                                <p class="text-gray-400">There are no classes scheduled for this date.</p>
                            </div>
                        @endif
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
                        <p>Choose how you'd like to book this class:</p>
                    </div>
                    
                    @auth
                        <button onclick="bookWithCredits(window.selectedClassId)" 
                                class="w-full flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="font-medium text-gray-900">Use Credits</div>
                                    <div class="text-sm text-gray-500">You have {{ auth()->user()->getAvailableCredits() }} {{ auth()->user()->hasActiveMembership() ? 'monthly credits' : 'credits' }}</div>
                                </div>
                            </div>
                            <div class="text-green-600 font-semibold">1 Credit</div>
                        </button>
                    @else
                        <button onclick="redirectToLogin()" 
                                class="w-full flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="font-medium text-gray-900">Use Credits</div>
                                    <div class="text-sm text-gray-500">Sign in to use credits</div>
                                </div>
                            </div>
                            <div class="text-green-600 font-semibold">1 Credit</div>
                        </button>
                    @endauth
                    
                    <button onclick="buySpot(window.selectedClassId)" 
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
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <button onclick="closeBookingModal()" 
                            class="w-full px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-black border-t border-gray-800 py-12">
            <div class="max-w-6xl mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center md:text-left">
                    <div>
                        <div class="flex items-center justify-center md:justify-start space-x-2 mb-4">
                            <img src="{{ asset('made-running.webp') }}" alt="Made Running" class="h-15 w-20">
                        </div>
                        <p class="text-gray-400 text-sm">
                            Transform your fitness journey with our high-intensity training programs designed to push your limits.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-white font-semibold mb-4">COMPANY</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-white font-semibold mb-4">SUPPORT</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Terms</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Privacy</a></li>
                        </ul>
                    </div>
                    
                    <div>
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
                    </div>
                </div>
            </div>
        </div>

        <script>
            let currentDate = '{{ $selectedDate->format("Y-m-d") }}';
            let isLoading = false;

            function loadDate(date) {
                if (isLoading) return;
                
                isLoading = true;
                currentDate = date;
                
                // Show loading spinner
                document.getElementById('loading-spinner').classList.remove('hidden');
                document.getElementById('classes-container').classList.add('hidden');
                
                // Update URL without page reload
                const url = new URL(window.location);
                url.searchParams.set('date', date);
                window.history.pushState({}, '', url);
                
                // Fetch new data
                fetch(`/api/classes?date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        updateUI(data);
                        isLoading = false;
                    })
                    .catch(error => {
                        console.error('Error loading classes:', error);
                        isLoading = false;
                        // Hide loading spinner on error
                        document.getElementById('loading-spinner').classList.add('hidden');
                        document.getElementById('classes-container').classList.remove('hidden');
                    });
            }

            function updateUI(data) {
                // Update date header
                document.getElementById('selected-date-header').textContent = data.selectedDate;
                
                // Update week navigation
                updateWeekNavigation(data.weekDays, data.prevWeek, data.nextWeek);
                
                // Update classes list
                updateClassesList(data.classes);
                
                // Hide loading spinner and show content
                document.getElementById('loading-spinner').classList.add('hidden');
                document.getElementById('classes-container').classList.remove('hidden');
            }

            function updateWeekNavigation(weekDays, prevWeek, nextWeek) {
                const weekDaysContainer = document.getElementById('week-days');
                weekDaysContainer.innerHTML = '';
                
                weekDays.forEach(day => {
                    const button = document.createElement('button');
                    button.onclick = () => loadDate(day.full_date);
                    
                    let classes = 'text-center px-6 py-4 rounded-lg transition-colors cursor-pointer flex-1 ';
                    if (day.is_selected) {
                        classes += 'bg-primary text-white';
                    } else if (day.is_today) {
                        classes += 'bg-gray-200 text-gray-800 font-semibold';
                    } else {
                        classes += 'text-gray-600 hover:bg-gray-100';
                    }
                    
                    button.className = classes;
                    button.innerHTML = `
                        <div class="text-sm font-medium uppercase">${day.day}</div>
                        <div class="text-xl font-bold">${day.date}</div>
                    `;
                    
                    weekDaysContainer.appendChild(button);
                });
                
                // Update arrow buttons
                document.getElementById('prev-week-btn').setAttribute('onclick', `loadDate('${prevWeek}')`);
                document.getElementById('next-week-btn').setAttribute('onclick', `loadDate('${nextWeek}')`);
            }

            function updateClassesList(classes) {
                const container = document.getElementById('classes-container');
                
                if (classes.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-12">
                           
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No classes scheduled</h3>
                            <p class="text-gray-500">There are no classes scheduled for this date.</p>
                        </div>
                    `;
                } else {
                    const classesHTML = classes.map(classItem => {
                    // Ensure price is defined and a number
                    classItem.price = classItem.price || 0;
                        const startTime = new Date(`2000-01-01T${classItem.start_time}`);
                        const endTime = new Date(`2000-01-01T${classItem.end_time}`);
                        const duration = Math.round((endTime - startTime) / (1000 * 60));
                        
                        return `
                            <div class="flex items-center p-4 bg-white rounded-lg border border-gray-200 hover:shadow-md transition-all">
                                
                                <div class="flex-shrink-0 w-16 text-center">
                                    <div class="text-sm font-semibold text-gray-900">${startTime.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true})}</div>
                                    <div class="text-xs text-gray-500">${duration} min</div>
                                </div>
                                
                                <div class="flex-shrink-0 w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-4">
                                    <img src="${classItem.instructor.photo_url}" 
                                         alt="${classItem.instructor.name}" 
                                         class="w-10 h-10 rounded-full object-cover">
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 truncate">${classItem.name}</h3>
                                    <p class="text-xs text-gray-600">${classItem.instructor.name}</p>
                                    <p class="text-xs text-gray-500">${classItem.available_spots} spots available</p>
                                </div>
                                
                                <div class="flex-shrink-0 ml-4">
                                    ${classItem.available_spots <= 0 ? 
                                        `<button disabled class="whitespace-nowrap px-4 py-2 bg-gray-400 text-white text-xs font-medium rounded-md cursor-not-allowed">
                                            Class Full
                                        </button>` :
                                        `<button onclick="openBookingModal(${classItem.id}, ${classItem.price})" 
                                                class="whitespace-nowrap px-4 py-2 bg-primary text-white text-xs font-medium rounded-md transition-colors hover:opacity-90">
                                            Book Class (${classItem.available_spots} left)
                                        </button>`
                                    }
                                </div>
                            </div>
                        `;
                    }).join('');
                    
                    container.innerHTML = `<div class="space-y-3" id="classes-list">${classesHTML}</div>`;
                }
            }

            // Handle browser back/forward buttons
            window.addEventListener('popstate', function(event) {
                const urlParams = new URLSearchParams(window.location.search);
                const date = urlParams.get('date') || '{{ now()->format("Y-m-d") }}';
                if (date !== currentDate) {
                    loadDate(date);
                }
            });
        </script>

        <script>
            window.selectedClassId = null;
            window.selectedClassPrice = 0;

            function openBookingModal(classId, price) {
                window.selectedClassId = classId;
                window.selectedClassPrice = price || 0;
                
                // Ensure price is a valid number
                const priceNum = parseInt(price) || 0;
                // Update the price in the modal
                document.getElementById('modalClassPrice').textContent = `£${priceNum.toLocaleString()}`;
                
                document.getElementById('bookingModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeBookingModal() {
                document.getElementById('bookingModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
                window.selectedClassId = null;
            }

            function bookWithCredits(classId) {
                closeBookingModal();
                
                // Check if user is authenticated
                @auth
                    // User is logged in, proceed with credit booking
                    if (confirm('Book this class using your credits?')) {
                        fetch(`/book-with-credits/${classId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Class booked successfully with credits!');
                                // Optionally refresh the page or update the UI
                                location.reload();
                            } else {
                                alert(data.message || 'Booking failed. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred. Please try again.');
                        });
                    }
                @else
                    // User not logged in, redirect to login
                    if (confirm('You need to sign in to use credits. Redirect to login?')) {
                        window.location.href = '/login';
                    }
                @endauth
            }

            function buySpot(classId) {
                closeBookingModal();
                // Redirect to checkout page
                window.location.href = `/checkout/${classId}`;
            }

            function redirectToLogin() {
                closeBookingModal();
                window.location.href = '/login';
            }

            // Close modal when clicking outside
            document.addEventListener('click', function(event) {
                const modal = document.getElementById('bookingModal');
                if (event.target === modal) {
                    closeBookingModal();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeBookingModal();
                }
            });
        </script>
    </body>
</html>
