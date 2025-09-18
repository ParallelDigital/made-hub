<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Made Running - Premium Fitness Experience</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Vite Assets (single source of CSS/JS to keep styles stable) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- Force Inter on homepage only; overrides global ProFontWindows enforcement -->
        <style>
            /* Override ProFontWindows enforcement for homepage */
            body.font-inter, body.font-inter *,
            body.font-inter *::before, body.font-inter *::after,
            .font-inter, .font-inter *, .font-inter *::before, .font-inter *::after {
                font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, 'Apple Color Emoji', 'Segoe UI Emoji', sans-serif !important;
                font-weight: 400 !important;
                font-style: normal !important;
            }
            
            /* Fix calendar layout issues */
            .schedule-container {
                min-height: 400px;
                position: relative;
            }
            
            .week-navigation {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: nowrap;
                gap: 0.5rem;
            }
            
            .week-navigation button {
                flex-shrink: 0;
                white-space: nowrap;
            }
            
            /* Ensure proper class card layout */
            .class-card {
                display: flex;
                align-items: center;
                justify-content: space-between;
                min-height: 80px;
                width: 100%;
            }
            
            @media (max-width: 640px) {
                .class-card {
                    flex-direction: column;
                    align-items: flex-start;
                    min-height: auto;
                    padding: 1rem;
                }
                
                .class-time, .class-instructor, .class-details, .class-booking {
                    width: 100%;
                    margin: 0.25rem 0;
                }
            }
        </style>
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
                    <a href="{{ route('register') }}" class="w-full sm:w-auto bg-primary text-black px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-bold rounded hover:bg-opacity-90 transition-all transform hover:scale-105 text-center">
                        BOOK YOUR CLASS
                    </a>
                    <a href="#schedule" class="w-full sm:w-auto border-2 border-white text-white px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-bold rounded hover:bg-white hover:text-black transition-all text-center">
                        VIEW SCHEDULE
                    </a>
                </div>
            </div>
        </div>

        <!-- Membership Section -->
        <div id="membership" class="bg-white text-black py-8 sm:py-12 lg:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="membership-grid grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
                    <!-- Image Column -->
                    <div class="membership-image">
                        <img src="{{ asset('made-club.jpg') }}" alt="Group fitness class" class="rounded-lg shadow-lg w-full h-full object-cover">
                    </div>
                    <!-- Content Column -->
                    <div class="membership-content text-left">
                        <h2 class="membership-title text-3xl sm:text-4xl md:text-5xl font-black text-gray-900 mb-4 sm:mb-6 leading-tight">ARE YOU READY <br>TO ELEVATE</h2>
                        <p class="text-sm font-bold uppercase tracking-widest text-gray-500 mb-2">PERKS OF MEMBERSHIP</p>
                        <ul class="membership-list space-y-3 sm:space-y-4 mb-6 sm:mb-8">
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
        <div id="schedule" class="bg-white text-black py-6 sm:py-8">
            <div class="schedule-container max-w-7xl mx-auto px-2 sm:px-4 lg:px-8" style="opacity: 0; transition: opacity 0.3s ease-in-out;">
                <!-- Week Navigation -->
                <div class="px-2 sm:px-4 lg:px-6 py-4">
                        <div class="flex items-center justify-between gap-2">
                            <!-- Previous Week Arrow -->
                            <button onclick="loadDate('{{ $prevWeek }}')" class="p-2 text-gray-700 hover:text-gray-900 transition-colors min-w-[44px] min-h-[44px] flex items-center justify-center flex-shrink-0" id="prev-week-btn">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>

                            <!-- Week Days -->
                            <div class="flex space-x-1 sm:space-x-2 flex-1 overflow-x-auto px-1 sm:px-2 justify-center" id="week-days">
                                <?php foreach($weekDays as $day): ?>
                                <button data-date="{{ $day['full_date'] }}" onclick="loadDate('{{ $day['full_date'] }}')" class="text-center px-2 sm:px-3 py-2 transition-colors cursor-pointer flex-shrink-0 min-w-[60px] sm:min-w-[80px] min-h-[44px] rounded-lg
                                    {{ $day['is_selected'] ? 'bg-gray-800 text-white' : ($day['is_today'] ? 'bg-gray-200 text-black font-bold' : 'text-gray-600 hover:bg-gray-100') }}">
                                    <div class="text-xs font-medium uppercase">{{ $day['day'] }}</div>
                                    <div class="text-lg sm:text-xl font-bold">{{ $day['date'] }}</div>
                                    <?php if(!empty($day['is_selected'])): ?>
                                        <div class="w-6 sm:w-8 h-1 bg-primary mx-auto mt-1 rounded"></div>
                                    <?php endif; ?>
                                </button>
                                <?php endforeach; ?>
                            </div>

                            <!-- Next Week Arrow -->
                            <button onclick="loadDate('{{ $nextWeek }}')" class="p-2 text-gray-700 hover:text-gray-900 transition-colors min-w-[44px] min-h-[44px] flex items-center justify-center flex-shrink-0" id="next-week-btn">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                </div>

                <!-- Selected Date Header -->
                <div class="px-2 sm:px-4 lg:px-6 py-3 sm:py-4 bg-gray-100 border-t border-b border-gray-300">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <h3 class="text-base sm:text-lg font-semibold text-black" id="selected-date-header">{{ $selectedDate->format('l, F j, Y') }}</h3>
                            <button
                                type="button"
                                onclick="loadDate('{{ now()->toDateString() }}')"
                                class="inline-flex items-center px-3 py-2 rounded-md bg-white border border-gray-300 text-black text-sm hover:bg-gray-50 min-h-[44px]">
                                Today
                            </button>
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
                    <div class="px-2 sm:px-4 lg:px-6 py-4" id="classes-container">
                        <?php if($selectedDateClasses->count() > 0): ?>
                            <div class="space-y-3 sm:space-y-4" id="classes-list">
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
                                            $duration = $class->duration ?? null;
                                        }
                                    @endphp
                                    <div class="class-card flex flex-col sm:flex-row items-start sm:items-center py-4 sm:py-6 border-b border-gray-300 last:border-b-0 gap-3 sm:gap-4">
                                    <div class="class-time flex-shrink-0 w-full sm:w-20 text-left">
                                        <div class="text-base sm:text-lg font-bold text-black">{{ $start ? $start->format('g:i A') : '' }}</div>
                                        <div class="text-sm text-gray-600">{{ $duration !== null ? $duration . ' min.' : '' }}</div>
                                    </div>
                                    
                                    <div class="class-instructor flex-shrink-0 w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                                        <img src="{{ $class->instructor && $class->instructor->photo ? asset('storage/' . $class->instructor->photo) : 'https://www.gravatar.com/avatar/?d=mp&s=100' }}" 
                                             alt="{{ $class->instructor->name ?? 'Instructor' }}" 
                                             class="w-12 h-12 rounded-full object-cover">
                                    </div>
                                    
                                    <div class="class-details flex-1">
                                        <div class="font-semibold text-gray-900 text-sm sm:text-base">{{ $class->name }} {{ $duration !== null ? '(' . $duration . ' Min)' : '' }}</div>
                                        <div class="text-sm text-gray-600">{{ $class->instructor->name ?? 'No Instructor' }}</div>
                                    </div>
                                    
                                    <div class="class-booking flex-shrink-0 w-full sm:w-auto">
                                        @php
                                            $currentBookings = App\Models\Booking::where('fitness_class_id', $class->id)->count();
                                            $availableSpots = max(0, $class->max_spots - $currentBookings);
                                            $isFull = $availableSpots <= 0;
                                        @endphp
                                        
                                        @if($isFull)
                                            <button disabled 
                                                    class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-gray-400 text-white text-sm sm:text-base font-medium rounded-md cursor-not-allowed min-h-[44px]">
                                                Class Full
                                            </button>
                                        @else
                                            <button onclick="openBookingModal({{ $class->id }}, {{ $class->price }})" 
                                                    class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-primary text-black text-sm sm:text-base font-medium rounded-md transition-colors hover:opacity-90 min-h-[44px]">
                                                Book Class ({{ $availableSpots }} left)
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12" id="no-classes">
                                <h3 class="text-lg font-medium text-black mb-2">No classes scheduled</h3>
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
                        <p>Choose how you'd like to book this class:</p>
                    </div>
                    
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
                                    <div class="text-sm text-gray-500">You have {{ auth()->user()->getAvailableCredits() }} {{ auth()->user()->hasActiveMembership() ? 'monthly credits' : 'credits' }}</div>
                                </div>
                            </div>
                            <div class="text-primary font-semibold" id="useCreditsRight">1 Credit</div>
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

        <!-- Footer -->
        <footer class="bg-black border-t border-gray-800 py-8 sm:py-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="footer-grid grid grid-cols-1 md:grid-cols-4 gap-6 sm:gap-8 text-center md:text-left">
                    <div class="footer-section">
                        <div class="flex items-center justify-center md:justify-start space-x-2 mb-4">
                            <img src="{{ asset('made-running.webp') }}" alt="Made Running" class="h-12 sm:h-15 w-16 sm:w-20">
                        </div>
                        <p class="text-gray-400 text-sm">
                            Transform your fitness journey with our high-intensity training programs designed to push your limits.
                        </p>
                    </div>

                    <div class="footer-section">
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
                    </div>

                    <div class="footer-section">
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
            window.IS_AUTH = {{ auth()->check() ? 'true' : 'false' }};
            let currentDate = '{{ $selectedDate->format("Y-m-d") }}';
            const CLASSES_API = '{{ url('/api/classes') }}';
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
                        // Hide loading spinner on error and show a friendly message
                        document.getElementById('loading-spinner').classList.add('hidden');
                        const container = document.getElementById('classes-container');
                        container.classList.remove('hidden');
                        container.innerHTML = `
                            <div class="text-center py-12">
                                <h3 class="text-lg font-medium text-black mb-2">Unable to load classes</h3>
                                <p class="text-gray-600">Please refresh the page or try again in a moment.</p>
                            </div>
                        `;
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
                    button.setAttribute('data-date', day.full_date);
                    button.onclick = () => loadDate(day.full_date);
                    
                    let classes = 'text-center px-2 sm:px-3 py-2 transition-colors cursor-pointer flex-shrink-0 min-w-[60px] sm:min-w-[80px] min-h-[44px] rounded-lg ';
                    if (day.is_selected) {
                        classes += 'bg-gray-800 text-white';
                    } else if (day.is_today) {
                        classes += 'bg-gray-200 text-black font-bold';
                    } else {
                        classes += 'text-gray-600 hover:bg-gray-100';
                    }

                    button.className = classes;
                    button.innerHTML = `
                        <div class="text-xs font-medium uppercase">${day.day}</div>
                        <div class="text-lg sm:text-xl font-bold">${day.date}</div>
                        ${day.is_selected ? '<div class="w-6 sm:w-8 h-1 bg-primary mx-auto mt-1 rounded"></div>' : ''}
                    `;

                    weekDaysContainer.appendChild(button);
                });

                // Update arrow buttons
                document.getElementById('prev-week-btn').setAttribute('onclick', `loadDate('${prevWeek}')`);
                document.getElementById('next-week-btn').setAttribute('onclick', `loadDate('${nextWeek}')`);
            }

            // Delegated click listener to ensure date selection works even if inline handlers fail
            if (!window.__weekDaysClickBound) {
                const weekDaysContainer = document.getElementById('week-days');
                if (weekDaysContainer) {
                    weekDaysContainer.addEventListener('click', (e) => {
                        const target = e.target.closest('[data-date]');
                        if (target && weekDaysContainer.contains(target)) {
                            const date = target.getAttribute('data-date');
                            if (date) {
                                loadDate(date);
                            }
                        }
                    });
                    window.__weekDaysClickBound = true;
                }
            }

            // Helpers to safely parse and format times from "HH:mm" or "HH:mm:ss"
            function parseTimeToMinutes(t) {
                if (!t || typeof t !== 'string') return null;
                const parts = t.split(':').map(v => parseInt(v, 10));
                if (Number.isNaN(parts[0])) return null;
                const h = parts[0] || 0;
                const m = parts[1] || 0;
                return h * 60 + m;
            }

            function formatTime12(t) {
                const mins = parseTimeToMinutes(t);
                if (mins === null) return '';
                let h = Math.floor(mins / 60);
                const m = mins % 60;
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12;
                if (h === 0) h = 12;
                const mm = m.toString().padStart(2, '0');
                return `${h}:${mm} ${ampm}`;
            }

            function updateClassesList(classes) {
                const container = document.getElementById('classes-container');

                if (classes.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-12">
                           
                            <h3 class="text-lg font-medium text-black mb-2">No classes scheduled</h3>
                            <p class="text-gray-600">There are no classes scheduled for this date.</p>
                        </div>
                    `;
                } else {
                    const classesHTML = classes.map(classItem => {
                        // Ensure price is defined and a number
                        classItem.price = classItem.price || 0;

                        const startMins = parseTimeToMinutes(classItem.start_time);
                        const endMins = parseTimeToMinutes(classItem.end_time);
                        // Use duration from API if available, otherwise calculate
                        const duration = classItem.duration || 60;

                        const startLabel = formatTime12(classItem.start_time);
                        const photo = (classItem && classItem.instructor && classItem.instructor.photo_url) ? classItem.instructor.photo_url : 'https://www.gravatar.com/avatar/?d=mp&s=100';
                        const instrName = (classItem && classItem.instructor && classItem.instructor.name) ? classItem.instructor.name : 'No Instructor';

                        return `
                            <div class="class-card flex flex-col sm:flex-row items-start sm:items-center py-4 sm:py-6 border-b border-gray-300 last:border-b-0 gap-3 sm:gap-4">
                                <div class="class-time flex-shrink-0 w-full sm:w-20 text-left">
                                    <div class="text-base sm:text-lg font-bold text-black">${startLabel}</div>
                                    <div class="text-sm text-gray-600">${duration} min.</div>
                                </div>
                                <div class="class-instructor flex-shrink-0 w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                                    <img src="${photo}" alt="${instrName}" class="w-12 h-12 rounded-full object-cover">
                                </div>
                                <div class="class-details flex-1">
                                    <div class="font-semibold text-gray-900 text-sm sm:text-base">${classItem.name} ${duration ? `(${duration} Min)` : ''}</div>
                                    <div class="text-sm text-gray-600">${instrName}</div>
                                </div>
                                <div class="class-booking flex-shrink-0 w-full sm:w-auto">
                                    ${classItem.available_spots <= 0
                                        ? `<button disabled class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-gray-400 text-white text-sm sm:text-base font-medium rounded-md cursor-not-allowed min-h-[44px]">Class Full</button>`
                                        : `<button onclick="openBookingModal(${classItem.id}, ${classItem.price})" class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-primary text-black text-sm sm:text-base font-medium rounded-md transition-colors hover:opacity-90 min-h-[44px]">Book Class (${classItem.available_spots} left)</button>`
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

            // Ensure calendar shows only after page is fully loaded and layout is stable
            function initializeCalendar() {
                const scheduleContainer = document.querySelector('.schedule-container');
                if (scheduleContainer) {
                    // Force a layout reflow to ensure all CSS is applied
                    scheduleContainer.offsetHeight;
                    
                    // Add a small delay to ensure all fonts and styles are loaded
                    setTimeout(() => {
                        scheduleContainer.style.opacity = '1';
                    }, 100);
                }
            }

            // Wait for DOM and all resources to be fully loaded
            if (document.readyState === 'complete') {
                initializeCalendar();
            } else {
                window.addEventListener('load', initializeCalendar);
            }

            // Fallback: ensure calendar shows after a reasonable timeout
            setTimeout(() => {
                const scheduleContainer = document.querySelector('.schedule-container');
                if (scheduleContainer && scheduleContainer.style.opacity === '0') {
                    scheduleContainer.style.opacity = '1';
                }
            }, 1000);
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

            function redirectToLogin(classId, price) {
                closeBookingModal();
                const priceNum = parseInt(price || 0) || 0;
                // Pass a plain absolute path as redirect so backend accepts it
                const redirectPath = `/?openBooking=1&classId=${classId||''}&price=${priceNum}`;
                window.location.href = `/login?redirect=${redirectPath}`;
            }

            // Submit inline login (AJAX) for guests within the modal
            function submitModalLogin() {
                const email = (document.getElementById('loginEmail')?.value || '').trim();
                const password = (document.getElementById('loginPassword')?.value || '').trim();
                const errorEl = document.getElementById('loginError');
                if (!email || !password) {
                    errorEl?.classList.remove('hidden');
                    errorEl.textContent = 'Please enter your email and password.';
                    return;
                }
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch('/ajax/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.success) throw new Error(data.message || 'Invalid credentials.');
                    return data;
                })
                .then(() => {
                    // After login, reload and auto-open the booking modal via query params to render authenticated content
                    const classId = window.selectedClassId;
                    const price = window.selectedClassPrice || 0;
                    window.location.href = `/?openBooking=1&classId=${classId||''}&price=${price||0}`;
                })
                .catch((err) => {
                    errorEl?.classList.remove('hidden');
                    errorEl.textContent = err.message || 'Invalid email or password.';
                });
            }

            // Auto-open modal after login redirect if instructed
            (function() {
                const url = new URL(window.location.href);
                const sp = url.searchParams;
                if (sp.get('openBooking') === '1') {
                    const classId = parseInt(sp.get('classId')) || null;
                    const price = parseInt(sp.get('price')) || 0;
                    if (classId) {
                        // Ensure state then open
                        window.selectedClassId = classId;
                        window.selectedClassPrice = price;
                        // Open modal now
                        openBookingModal(classId, price);
                        // Clean the URL so refresh doesn't reopen
                        sp.delete('openBooking');
                        // do not remove classId/price to allow re-open if needed; or clean all:
                        sp.delete('classId');
                        sp.delete('price');
                        const newUrl = url.pathname + (sp.toString() ? ('?' + sp.toString()) : '');
                        window.history.replaceState({}, '', newUrl);
                    }
                }
            })();

            // Toggle PIN visibility
            (function(){
                const toggleBtn = document.getElementById('togglePinVisibility');
                const input = document.getElementById('pinCodeInput');
                if (toggleBtn && input) {
                    toggleBtn.addEventListener('click', function(){
                        input.type = input.type === 'password' ? 'text' : 'password';
                    });
                }
            })();

            // Modal utilities (confirm + feedback)
            function openConfirmModal(message, onConfirm) {
                const modal = document.getElementById('confirmModal');
                const msg = document.getElementById('confirmMessage');
                msg.textContent = message || 'Are you sure?';
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                window.__confirmCb = function(){ try { onConfirm && onConfirm(); } finally { closeConfirmModal(); } };
            }
            function closeConfirmModal() {
                const modal = document.getElementById('confirmModal');
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                window.__confirmCb = null;
            }
            function confirmModalYes(){ if (window.__confirmCb) window.__confirmCb(); }
            function confirmModalNo(){ closeConfirmModal(); }

            function openFeedbackModal(title, message) {
                const modal = document.getElementById('feedbackModal');
                document.getElementById('feedbackTitle').textContent = title || 'Notice';
                document.getElementById('feedbackMessage').textContent = message || '';
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            function closeFeedbackModal() {
                document.getElementById('feedbackModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            // Perform booking with credits (AJAX) after confirmation
            function performCreditBooking(classId, pin) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch(`/book-with-credits/${classId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ pin_code: pin })
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(data.message || `Request failed (${res.status})`);
                    return data;
                })
                .then((data) => {
                    closeBookingModal();
                    openFeedbackModal('Booked with credits', data.message || 'Your class has been booked.');
                    // Refresh after a brief delay to update availability
                    setTimeout(() => window.location.reload(), 1200);
                })
                .catch((err) => {
                    openFeedbackModal('Booking failed', err.message || 'Unable to book with credits.');
                });
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
