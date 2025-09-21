<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Made Running - Premium Fitness Experience</title>

        <!-- Preload ProFontWindows to minimise FOUT on homepage -->
        <link rel="preload" href="{{ Vite::asset('resources/fonts/ProFontWindows.ttf') }}" as="font" type="font/ttf" crossorigin>

        <!-- Vite Assets (single source of CSS/JS to keep styles stable) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>

            /* Week scroller arrow behavior is implemented in the JS script block below */
            
            /* Clean minimalist calendar design matching brand */
            .schedule-container {
                background: white;
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid #e5e7eb;
            }
            
            /* Week navigation styling */
            .week-nav-container {
                background: #f9fafb;
                padding: 1rem 1.5rem;
                border-bottom: 1px solid #e5e7eb;
            }
            
            html { scroll-behavior: smooth; }
            
            .week-navigation {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 1.5rem;
                max-width: 100%;
                flex: 1; /* fill space between arrows */
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                -ms-overflow-style: none;
                padding: 0.5rem 0.75rem; /* give breathing room near arrows */
                scroll-behavior: smooth; /* smoother programmatic scrolls */
                scroll-snap-type: x proximity; /* smoother day snapping */
                scroll-padding-left: 24px;
                scroll-padding-right: 24px;
            }
            
            .week-navigation::-webkit-scrollbar {
                display: none;
            }
            
            .week-day-btn {
                flex-shrink: 0;
                min-width: 80px;
                padding: 0.5rem;
                text-align: center;
                border: none;
                background: transparent;
                transition: all 0.2s ease;
                cursor: pointer;
                font-family: inherit;
                scroll-snap-align: center;
                scroll-margin-inline: 24px; /* avoid clipping near edges */
            }
            
            .week-day-btn .day-name {
                font-size: 0.875rem;
                color: #6b7280;
                font-weight: 500;
                margin-bottom: 0.25rem;
            }
            
            .week-day-btn .day-number {
                font-size: 1.5rem;
                font-weight: 700;
                color: #374151;
            }
            
            .week-day-btn.selected .day-name {
                color: #000;
                font-weight: 600;
            }
            
            .week-day-btn.selected .day-number {
                color: #000;
            }
            
            .week-day-btn.selected::after {
                content: '';
                display: block;
                width: 24px;
                height: 3px;
                background: #000;
                margin: 0.5rem auto 0;
                border-radius: 2px;
            }
            
            .week-day-btn.today:not(.selected) .day-name {
                color: #000;
                font-weight: 600;
            }
            
            .week-day-btn.today:not(.selected) .day-number {
                color: #000;
                font-weight: 800;
            }
            
            .nav-arrow {
                flex-shrink: 0;
                width: 32px;
                height: 32px;
                background: transparent;
                border: none;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s ease;
                margin: -1rem;
                z-index: 13;
            }
            
            .nav-arrow:hover {
                background: #f3f4f6;
                border-radius: 50%;
            }
            
            /* Date header styling */
            .date-header {
                background: white;
                color: #000;
                padding: 1rem 1.5rem;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .date-header h2 {
                font-size: 1.125rem;
                font-weight: 600;
                margin: 0;
                color: #000;
            }
            
            .today-btn {
                background: transparent;
                color: #6b7280;
                border: 1px solid #d1d5db;
                padding: 0.5rem 1rem;
                border-radius: 6px;
                font-size: 0.875rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .today-btn:hover {
                background: #f9fafb;
                border-color: #9ca3af;
            }
            
            /* Class cards styling */
            .classes-section {
                padding: 0;
                background: white;
            }
            
            .class-card {
                background: white;
                border: none;
                border-bottom: 1px solid #f3f4f6;
                padding: 1.5rem;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 1rem;
                transition: background-color 0.2s ease;
            }
            
            .class-card:hover {
                background: #fafafa;
            }
            
            .class-card:last-child {
                border-bottom: none;
            }
            
            .class-time-section {
                flex-shrink: 0;
                width: 80px;
                text-align: left;
            }
            
            .class-time {
                font-size: 1rem;
                font-weight: 700;
                color: #000;
                line-height: 1.2;
            }
            
            .class-duration {
                font-size: 0.875rem;
                color: #6b7280;
                margin-top: 0.125rem;
            }
            
            .class-location {
                flex-shrink: 0;
                width: 100px;
                font-size: 0.875rem;
                color: #6b7280;
                text-align: left;
            }
            
            .instructor-section {
                flex-shrink: 0;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                width: 60px;
            }
            
            .instructor-avatar {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                object-fit: cover;
            }
            
            .class-info-section {
                flex: 1;
                min-width: 0;
            }
            
            .class-title {
                font-size: 1rem;
                font-weight: 700;
                color: #000;
                margin: 0 0 0.25rem 0;
                line-height: 1.3;
            }
            
            .class-instructor-name {
                font-size: 0.875rem;
                color: #6b7280;
                margin: 0 0 0.25rem 0;
            }
            
            .class-room {
                font-size: 0.875rem;
                color: #6b7280;
                margin: 0;
            }
            
            .book-section {
                flex-shrink: 0;
                width: 120px;
            }
            
            .reserve-button {
                background: transparent;
                color: #000;
                border: 1px solid #000;
                padding: 0.75rem 1.5rem;
                border-radius: 4px;
                font-weight: 600;
                font-size: 0.875rem;
                cursor: pointer;
                transition: all 0.2s ease;
                width: 100%;
                min-height: 44px;
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }
            
            .reserve-button:hover {
                background: #000;
                color: white;
            }
            
            .reserve-button:disabled {
                background: #f3f4f6;
                color: #9ca3af;
                border-color: #d1d5db;
                cursor: not-allowed;
            }
            
            .reserve-button:disabled:hover {
                background: #f3f4f6;
                color: #9ca3af;
            }

            /* Ribbon for members-only classes */
            .class-card { position: relative; }
            .ribbon-members {
                position: absolute;
                top: 0;
                left: 0;
                background: #111;
                color: #fff;
                font-weight: 800;
                font-size: 0.65rem;
                padding: 0.35rem 0.5rem;
                border-bottom-right-radius: 6px;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                z-index: 2;
            }
            
            .no-classes {
                text-align: center;
                padding: 3rem 1rem;
                color: #6b7280;
            }
            
            .no-classes-icon {
                width: 48px;
                height: 48px;
                margin: 0 auto 1rem;
                opacity: 0.5;
            }
            
            /* Mobile optimizations */
            @media (max-width: 768px) {
                .schedule-container {
                    border-radius: 0;
                    border-left: none;
                    border-right: none;
                    overflow: hidden;
                    width: 100%;
                }
                
                .week-nav-container {
                    padding: 1rem;
                    margin-bottom: 0;
                }
                
                .week-navigation {
                    display: grid;
                    grid-template-columns: repeat(7, minmax(42px, 1fr));
                    gap: 0.5rem;
                    padding: 0.5rem 0.75rem; /* match desktop spacing */
                    scroll-behavior: auto; /* no horizontal scroll needed */
                    overflow: visible;
                }
                
                .week-day-btn {
                    min-width: 0;
                    width: 100%;
                    padding: 0.25rem 0.25rem;
                    font-size: 0.9rem;
                    scroll-margin-inline: 0;
                }
                
                .week-day-btn .day-number {
                    font-size: 0.8rem;
                    font-weight: 500;
                    color: #6b7280;
                    margin-bottom: 0.25rem;
                }
                
                .week-day-btn .day-name {
                    font-size: 1.05rem;
                    font-weight: 700;
                    color: #6b7280;
                }
                
                .week-day-btn.selected .day-name {
                    color: #000;
                }
                
                .week-day-btn.today .day-name {
                    color: #000;
                    font-weight: 800;
                }
                
                .date-header {
                    padding: 1.5rem;
                    text-align: left;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .date-header h2 {
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: #000;
                }
                
                .today-btn {
                    background: transparent;
                    border: 1px solid #d1d5db;
                    color: #6b7280;
                    padding: 0.5rem 1rem;
                    border-radius: 0.375rem;
                    font-size: 0.875rem;
                    font-weight: 500;
                }
                
                .class-card {
                    flex-direction: row;
                    align-items: flex-start;
                    gap: 1rem;
                    padding: 1.5rem;
                    border-bottom: 1px solid #f3f4f6;
                }
                
                .class-time-section {
                    width: 80px;
                    flex-shrink: 0;
                }
                
                .class-time {
                    font-size: 1.125rem;
                    font-weight: 700;
                    color: #6b7280;
                    line-height: 1.2;
                }
                
                .class-duration {
                    font-size: 0.875rem;
                    color: #9ca3af;
                    margin-top: 0.25rem;
                }
                
                .instructor-section {
                    width: 60px;
                    flex-shrink: 0;
                    display: flex;
                    justify-content: center;
                }
                
                .instructor-avatar {
                    width: 48px;
                    height: 48px;
                }
                
                .class-info-section {
                    flex: 1;
                    min-width: 0;
                }
                
                .class-title {
                    font-size: 1rem;
                    font-weight: 700;
                    color: #000;
                    margin: 0 0 0.5rem 0;
                    text-decoration: underline;
                }
                
                .class-instructor-name {
                    font-size: 0.875rem;
                    color: #6b7280;
                    margin: 0 0 0.25rem 0;
                }
                
                .class-room {
                    font-size: 0.875rem;
                    color: #6b7280;
                    margin: 0 0 0.125rem 0;
                }
                
                .class-location {
                    display: none; /* Hide on mobile */
                }

                /* Hide Reserve button on mobile; make entire card tappable */
                .book-section {
                    display: none !important;
                }
                .class-card {
                    cursor: pointer;
                    /* Grid layout: time above image on left, info spanning right */
                    display: grid;
                    grid-template-columns: 80px 1fr;
                    grid-template-rows: auto auto;
                    grid-template-areas: 'time info' 'instructor info';
                    gap: 0.75rem;
                }
                .class-time-section { grid-area: time; }
                .instructor-section { grid-area: instructor; }
                .class-info-section { grid-area: info; }
            }
        </style>
    </head>
    <body class="bg-black text-white">
        <!-- Navigation -->
        <div x-data="{ open: false }" class="relative bg-black border-b border-gray-800">
            <nav class="flex items-center justify-between px-4 sm:px-6 py-4">
                <div class="flex items-center space-x-8">
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('made-running.webp') }}" alt="Made Running" class="h-15 w-20">
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
                    <a href="{{ route('purchase.package.checkout', ['type' => 'membership']) }}" class="w-full px-4 py-3 rounded bg-primary text-black font-semibold hover:bg-opacity-90 text-center">Become a Member</a>
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
                        <a href="#" class="inline-block bg-black text-white px-10 py-4 text-sm font-bold uppercase tracking-widest rounded hover:bg-gray-800 transition-all">
                            SIGN UP NOW
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Section -->
        <div id="schedule" class="bg-white text-black py-6 sm:py-8">
            <div class="schedule-container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="opacity: 0; transition: opacity 0.3s ease-in-out;">
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
                                <div class="class-card" data-class-id="{{ $class->id }}" data-price="{{ $class->price ?? 0 }}" data-is-past="{{ $isPast ? '1' : '0' }}" data-is-full="{{ $isFull ? '1' : '0' }}" data-is-booked="{{ $isBookedByMe ? '1' : '0' }}" data-members-only="{{ $isMembersOnly ? '1' : '0' }}">
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
                                        <img src="{{ $class->instructor && $class->instructor->photo ? asset('storage/' . $class->instructor->photo) : 'https://www.gravatar.com/avatar/?d=mp&s=100' }}" 
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
                                                    <button onclick="openBookingModal({{ $class->id }}, 0)" class="reserve-button">Become Member</button>
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

        <!-- PIN Modal (for confirming credit booking) -->
        <div id="pinModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Confirm with Credits</h3>
                    <button onclick="closePinModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p class="text-gray-700 mb-3">Enter your 4-digit booking code (PIN) to confirm booking with credits.</p>
                <div class="relative mb-2">
                    <input id="pinModalInput" type="password" inputmode="numeric" pattern="\\d{4}" maxlength="4" placeholder="0000" class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    <button type="button" onclick="togglePinVisibility('pinModalInput', this)" aria-label="Show PIN" class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3C5 3 1.73 7.11 1 10c.73 2.89 4 7 9 7s8.27-4.11 9-7c-.73-2.89-4-7-9-7zm0 12a5 5 0 110-10 5 5 0 010 10z"/><circle cx="10" cy="10" r="3" fill="currentColor"/></svg>
                    </button>
                </div>
                <p id="pinModalError" class="text-sm text-red-600 hidden">Please enter your 4-digit PIN.</p>
                <div class="mt-4 flex justify-end gap-3">
                    <button onclick="closePinModal()" class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button onclick="confirmPinAndBook()" class="px-4 py-2 rounded bg-primary text-black font-semibold hover:bg-opacity-90">Confirm</button>
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
                    <a href="{{ route('purchase.package.checkout', ['type' => 'package_10']) }}" class="w-full px-4 py-3 rounded bg-primary text-black font-semibold hover:bg-opacity-90 text-center">Buy Class Pass</a>
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
            window.IS_MEMBER = {{ auth()->check() && auth()->user()->hasActiveMembership() ? 'true' : 'false' }};
            window.IS_UNLIMITED = {{ auth()->check() && method_exists(auth()->user(), 'hasActiveUnlimitedPass') && auth()->user()->hasActiveUnlimitedPass() ? 'true' : 'false' }};
            let currentDate = '{{ $selectedDate->format("Y-m-d") }}';
            const CLASSES_API = '{{ url('/api/classes') }}';
            const MEMBERSHIP_URL = '{{ route('purchase.package.checkout', ['type' => 'membership']) }}';
            let isLoading = false;
            window.SHOW_PAST = {{ ($showPast ?? false) ? 'true' : 'false' }};

            // Animate week scroller before loading a new week for a smoother transition
            function onArrowNav(date) {
                const weekDaysEl = document.getElementById('week-days');
                if (weekDaysEl && typeof weekDaysEl.scrollBy === 'function') {
                    try {
                        const dir = (new Date(date) < new Date(currentDate)) ? -1 : 1;
                        const delta = Math.max(weekDaysEl.clientWidth * 0.6, 240);
                        weekDaysEl.scrollBy({ left: dir * delta, behavior: 'smooth' });
                    } catch (e) { /* no-op */ }
                }
                setTimeout(() => loadDate(date), 160);
            }

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
                if (window.SHOW_PAST) { url.searchParams.set('show_past', '1'); } else { url.searchParams.delete('show_past'); }
                window.history.pushState({}, '', url);
                
                // Fetch new data
                fetch(`/api/classes?date=${date}&show_past=${window.SHOW_PAST ? 1 : 0}`)
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
                const weekDays = Array.isArray(data.weekDays) ? data.weekDays : [];

                // Update classes list
                updateClassesList(data.classes);

                // Hide loading spinner and show content
                document.getElementById('loading-spinner').classList.add('hidden');
                document.getElementById('classes-container').classList.remove('hidden');

                // Sync show past state and button label
                window.SHOW_PAST = !!data.showPast;
                const weekDaysContainer = document.getElementById('week-days');
                weekDaysContainer.innerHTML = '';

                weekDays.forEach(day => {
                    const button = document.createElement('button');
                    button.setAttribute('data-date', day.full_date);
                    button.onclick = () => loadDate(day.full_date);
                    
                    let classes = 'week-day-btn ';
                    if (day.is_selected) {
                        classes += 'selected';
                    } else if (day.is_today) {
                        classes += 'today';
                    }

                    button.className = classes;
                    const label = day.is_today ? 'Today' : formatMonthDay(day.full_date);
                    const dayName = (day.day || '').toString().toUpperCase();
                    button.innerHTML = `
                        <div class="day-number">${label}</div>
                        <div class="day-name">${dayName}</div>
                    `;

                    weekDaysContainer.appendChild(button);
                });

                // Update arrow buttons
                document.getElementById('prev-week-btn').setAttribute('onclick', `onArrowNav('${data.prevWeek}')`);
                document.getElementById('next-week-btn').setAttribute('onclick', `onArrowNav('${data.nextWeek}')`);

                // Smoothly center the selected day in the scroll container
                const selectedBtn = weekDaysContainer.querySelector('.week-day-btn.selected') || weekDaysContainer.querySelector('.week-day-btn.today');
                if (selectedBtn && typeof selectedBtn.scrollIntoView === 'function') {
                    selectedBtn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                }
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

            // Format ISO date (YYYY-MM-DD) as "Mon 15" style: "Sep 15"
            function formatMonthDay(iso) {
                if (!iso) return '';
                const d = new Date(iso + 'T00:00:00');
                if (isNaN(d.getTime())) return '';
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                return `${months[d.getMonth()]} ${d.getDate()}`;
            }

            function updateClassesList(classes) {
                const container = document.getElementById('classes-container');

                if (classes.length === 0) {
                    container.innerHTML = `
                        <div class="no-classes">
                            <svg class="no-classes-icon mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No classes scheduled</h3>
                            <p class="text-gray-600">There are no classes scheduled for this date.</p>
                        </div>
                    `;
                } else {
                    const classesHTML = classes.map(classItem => {
                        // Ensure price is defined and a number
                        classItem.price = classItem.price || 0;

                        // Use duration from API if available, otherwise default
                        const duration = classItem.duration || 60;

                        const startLabel = formatTime12(classItem.start_time);
                        const photo = (classItem && classItem.instructor && classItem.instructor.photo_url) ? classItem.instructor.photo_url : 'https://www.gravatar.com/avatar/?d=mp&s=100';
                        const instrName = (classItem && classItem.instructor && classItem.instructor.name) ? classItem.instructor.name : 'No Instructor';
                        const isMembersOnly = !!classItem.members_only;

                        return `
                            <div class=\"class-card\" data-class-id=\"${classItem.id}\" data-price=\"${classItem.price || 0}\" data-is-past=\"${classItem.is_past ? 1 : 0}\" data-is-full=\"${(classItem.available_spots <= 0) ? 1 : 0}\" data-is-booked=\"${classItem.is_booked_by_me ? 1 : 0}\" data-members-only=\"${isMembersOnly ? 1 : 0}\">
                                ${isMembersOnly ? '<div class="ribbon-members">Members Class</div>' : ''}
                                <div class="class-time-section">
                                    <div class="class-time">${startLabel}</div>
                                    <div class="class-duration">${duration} min.</div>
                                </div>
                                
                                <div class="class-location">
                                    Manchester
                                </div>
                                
                                <div class="instructor-section">
                                    <img src="${photo}" alt="${instrName}" class="instructor-avatar">
                                </div>
                                
                                <div class="class-info-section">
                                    <h3 class="class-title">${classItem.name} (${duration} Min)</h3>
                                    <p class="class-instructor-name">${instrName}</p>
                                </div>
                                
                                <div class="book-section">
                                    ${classItem.is_past
                                        ? `<button disabled class="reserve-button">Past</button>`
                                        : (classItem.is_booked_by_me
                                            ? `<button disabled class="reserve-button bg-green-100 text-green-700 border-green-300">Booked</button>`
                                            : (classItem.available_spots <= 0
                                                ? `<button disabled class="reserve-button">Class Full</button>`
                                                : (isMembersOnly
                                                    ? (window.IS_MEMBER
                                                        ? `<button onclick=\"openBookingModal(${classItem.id}, 0)\" class=\"reserve-button\">Book (Members)</button>`
                                                        : (window.IS_AUTH
                                                            ? `<button onclick=\"openBookingModal(${classItem.id}, 0)\" class=\"reserve-button\">Become Member</button>`
                                                            : `<button onclick=\"openBookingModal(${classItem.id}, 0)\" class=\"reserve-button\">Members Only</button>`
                                                          )
                                                      )
                                                    : `<button onclick=\"openBookingModal(${classItem.id}, ${classItem.price})\" class=\"reserve-button\">Reserve</button>`
                                                  )
                                              )
                                          )
                                    }
                                </div>
                            </div>
                        `;
                    }).join('');

                    container.innerHTML = `<div class="classes-section">${classesHTML}</div>`;
                }
            }

            // Mobile: tap class card to open booking modal
            if (!window.__classCardClickBound) {
                const container = document.getElementById('classes-container');
                if (container) {
                    container.addEventListener('click', function(e) {
                        const isMobile = window.matchMedia('(max-width: 768px)').matches;
                        if (!isMobile) return;
                        const card = e.target.closest('.class-card');
                        if (!card || !container.contains(card)) return;
                        const ds = card.dataset || {};
                        const classId = parseInt(ds.classId || '0', 10);
                        if (!classId) return;
                        const price = parseInt(ds.price || '0', 10) || 0;
                        const isPast = ds.isPast === '1';
                        const isFull = ds.isFull === '1';
                        const isBooked = ds.isBooked === '1';
                        const isMembersOnly = ds.membersOnly === '1';
                        if (isPast) { openFeedbackModal('Unavailable', 'This class has already happened.'); return; }
                        if (isBooked) { openFeedbackModal('Already booked', 'You have already booked this class.'); return; }
                        if (isFull) { openFeedbackModal('Class full', 'This class is fully booked.'); return; }
                        if (isMembersOnly && !window.IS_MEMBER) { 
                            if (window.IS_AUTH) {
                                openBookingModal(classId, 0); 
                            } else {
                                openBookingModal(classId, 0); 
                            }
                            return; 
                        }
                        openBookingModal(classId, price);
                    });
                    window.__classCardClickBound = true;
                }
            }

            // Handle browser back/forward buttons
            window.addEventListener('popstate', function(event) {
                const urlParams = new URLSearchParams(window.location.search);
                const date = urlParams.get('date') || '{{ now()->format("Y-m-d") }}';
                const sp = urlParams.get('show_past');
                window.SHOW_PAST = (sp === '1' || sp === 'true');
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
                        // After reveal, center currently selected week day
                        const container = document.getElementById('week-days');
                        const selected = container?.querySelector('.week-day-btn.selected') || container?.querySelector('.week-day-btn.today');
                        if (selected && typeof selected.scrollIntoView === 'function') {
                            selected.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                        }
                    }, 100);
                }
            }

            // Toggle show/hide past
            function toggleShowPast() {
                window.SHOW_PAST = !window.SHOW_PAST;
                loadDate(currentDate);
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

                // Adjust modal labels and content for members-only classes
                const card = document.querySelector(`.class-card[data-class-id="${classId}"]`);
                const isMembersOnly = card && card.dataset && card.dataset.membersOnly === '1';
                const useCreditsLabel = document.getElementById('useCreditsLabel');
                const useCreditsRight = document.getElementById('useCreditsRight');
                const payBtn = document.getElementById('payButton');
                const membersOnlyOptions = document.getElementById('membersOnlyOptions');
                const bookingModalMessage = document.getElementById('bookingModalMessage');
                const useCreditsBtn = document.querySelector('#bookingModal button[onclick*="bookWithCredits"]');

                if (isMembersOnly) {
                    bookingModalMessage.textContent = 'This class is for members only:';
                    
                    if (window.IS_MEMBER) {
                        // Member: show booking options
                        if (useCreditsLabel) useCreditsLabel.textContent = 'Book (Members)';
                        if (useCreditsRight) useCreditsRight.textContent = 'Free';
                        if (payBtn) payBtn.classList.add('hidden');
                        if (membersOnlyOptions) membersOnlyOptions.classList.add('hidden');
                        if (useCreditsBtn) useCreditsBtn.classList.remove('hidden');
                    } else {
                        // Non-member: hide all booking options, show only membership
                        if (payBtn) payBtn.classList.add('hidden');
                        if (membersOnlyOptions) membersOnlyOptions.classList.remove('hidden');
                        if (useCreditsBtn) useCreditsBtn.classList.add('hidden');
                    }
                } else {
                    // Regular class: show normal options
                    bookingModalMessage.textContent = 'Choose how you\'d like to book this class:';
                    if (useCreditsLabel) useCreditsLabel.textContent = 'Use Credits';
                    if (useCreditsRight) useCreditsRight.textContent = '1 Credit';
                    if (payBtn) payBtn.classList.remove('hidden');
                    if (membersOnlyOptions) membersOnlyOptions.classList.add('hidden');
                    if (useCreditsBtn) useCreditsBtn.classList.remove('hidden');
                }

                document.getElementById('bookingModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeBookingModal() {
                document.getElementById('bookingModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
                // Do not clear selectedClassId so we can continue flows (login/confirm) reliably
            }

            function bookWithCredits(classId) {
                // Capture the class id before any UI changes
                const cid = classId || window.selectedClassId;
                @auth
                    const card = document.querySelector(`.class-card[data-class-id="${cid}"]`);
                    const isMembersOnly = card && card.dataset && card.dataset.membersOnly === '1';
                    if (isMembersOnly && window.IS_MEMBER) {
                        closeBookingModal();
                        openConfirmModal('Book this members-only class for free?', function() {
                            performCreditBooking(cid);
                        });
                    } else if (isMembersOnly && !window.IS_MEMBER) {
                        closeBookingModal();
                        openMembersOnlyModal();
                    } else {
                        // If user has an unlimited pass, allow booking without checking numeric credits
                        if (window.IS_UNLIMITED) {
                            closeBookingModal();
                            openConfirmModal('Book with your unlimited pass?', function() {
                                performCreditBooking(cid);
                            });
                            return;
                        }
                        // Determine available credits from hidden data attribute
                        const span = document.getElementById('availableCreditsData');
                        const available = span ? (parseInt(span.getAttribute('data-credits')) || 0) : 0;
                        if (available > 0) {
                            // Hide the booking modal then confirm using the captured id
                            closeBookingModal();
                            openConfirmModal('Use 1 credit to book this class?', function() {
                                performCreditBooking(cid);
                            });
                        } else {
                            closeBookingModal();
                            openNoCreditsModal();
                        }
                    }
                @else
                    // Keep booking modal in background, and open login so we preserve cid for redirect
                    openLoginModal();
                @endauth
            }

            function buySpot(classId) {
                closeBookingModal();
                const card = document.querySelector(`.class-card[data-class-id="${classId}"]`);
                const isMembersOnly = card && card.dataset && card.dataset.membersOnly === '1';
                if (isMembersOnly && !window.IS_MEMBER) {
                    openMembersOnlyModal();
                    return;
                }
                // Redirect to checkout page
                window.location.href = `/checkout/${classId}`;
            }

            function redirectToLogin(classId, price) {
                closeBookingModal();
                const cid = (typeof classId !== 'undefined' && classId !== null) ? classId : window.selectedClassId;
                const prRaw = (typeof price !== 'undefined' && price !== null) ? price : window.selectedClassPrice;
                const priceNum = parseInt(prRaw || 0) || 0;
                // Pass a plain absolute path as redirect so backend accepts it
                const redirectPath = `/?openBooking=1&classId=${cid||''}&price=${priceNum}`;
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

            // Login modal helpers
            function openLoginModal() {
                const modal = document.getElementById('loginModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    const email = document.getElementById('loginEmail');
                    setTimeout(() => { email && email.focus(); }, 0);
                }
            }
            function closeLoginModal() {
                const modal = document.getElementById('loginModal');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }

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
                const payload = {};
                if (pin && /^\d{4}$/.test(pin)) payload.pin_code = pin;
                fetch(`/book-with-credits/${classId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(data.message || `Request failed (${res.status})`);
                    return data;
                })
                .then((data) => {
                    // Redirect to the same confirmation page used for Stripe flow
                    window.location.href = `/booking/confirmation/${classId}`;
                })
                .catch((err) => {
                    openFeedbackModal('Booking failed', err.message || 'Unable to book with credits.');
                });
            }

            // Optional PIN helpers (modal exists; PIN is optional)
            function togglePinVisibility(inputId, btn) {
                const input = document.getElementById(inputId);
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
                if (btn && btn.setAttribute) {
                    const shown = input.type === 'text';
                    btn.setAttribute('aria-label', shown ? 'Hide PIN' : 'Show PIN');
                }
            }

            function closePinModal() {
                const modal = document.getElementById('pinModal');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }

            function confirmPinAndBook() {
                const input = document.getElementById('pinModalInput');
                const pin = (input?.value || '').trim();
                const cid = window.selectedClassId;
                closePinModal();
                performCreditBooking(cid, pin);
            }

            // No-credits modal helpers
            function openNoCreditsModal() {
                const modal = document.getElementById('noCreditsModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            }
            function closeNoCreditsModal() {
                const modal = document.getElementById('noCreditsModal');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }

            // Members-only modal helpers
            function openMembersOnlyModal() {
                const modal = document.getElementById('membersOnlyModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            }
            function closeMembersOnlyModal() {
                const modal = document.getElementById('membersOnlyModal');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
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
