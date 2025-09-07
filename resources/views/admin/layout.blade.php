<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - {{ (auth()->check() && auth()->user()->role === 'instructor') ? 'Instructor Dashboard' : 'Admin Dashboard' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    <style>[x-cloak]{ display:none !important; }</style>

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#c8b7ed',
                        secondary: '#ffffff',
                        accent: '#000000',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-gray-900 text-white" x-data="{ sidebarOpen:false, sidebarCollapsed:false }" x-init="sidebarOpen = window.innerWidth >= 1024; window.addEventListener('resize', () => { if (window.innerWidth >= 1024) { sidebarOpen = true } })">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div
            class="bg-gray-800 shadow-lg fixed inset-y-0 left-0 transform transition-transform duration-300 ease-in-out z-40 lg:z-auto lg:static lg:translate-x-0"
            :class="{
                '-translate-x-full': !sidebarOpen,
                'translate-x-0': sidebarOpen,
            }"
        >
            <div class="transition-all duration-300" :class="sidebarCollapsed ? 'w-20' : 'w-64'">
            <div class="p-6">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('made-running.webp') }}" alt="Made Running" class="w-10 h-10 rounded-full">
                    <div class="transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'">
                        <h1 class="text-xl font-bold text-primary">Made Running</h1>
                        <p class="text-sm text-gray-400">{{ (auth()->check() && auth()->user()->role === 'instructor') ? 'Instructor Dashboard' : 'Admin Dashboard' }}</p>
                    </div>
                </div>
            </div>

            <nav class="mt-6">
                @php $isInstructor = auth()->check() && auth()->user()->role === 'instructor'; @endphp
                <div class="px-6 py-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider" x-show="!sidebarCollapsed">Main</p>
                </div>

                @if ($isInstructor)
                    <!-- Instructor menu: only Classes and Profile -->
                    <a href="{{ route('instructor.dashboard') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('instructor.*') ? 'bg-gray-700 text-white border-r-2 border-primary' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                        </svg>
                        <span class="transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'">My Classes</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('profile.*') ? 'bg-gray-700 text-white border-r-2 border-primary' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 018 17h8a4 4 0 013 1.196M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'">My Profile</span>
                    </a>
                @else
                    <!-- Admin menu -->
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 text-white border-r-2 border-primary' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                        </svg>
                        <span class="transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'">Dashboard</span>
                    </a>

                    <div class="px-6 py-3 mt-6">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider" x-show="!sidebarCollapsed">Management</p>
                    </div>
                    <a href="{{ route('admin.classes.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.classes.*') ? 'bg-gray-700 text-white border-r-2 border-primary' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'">Classes</span>
                    </a>
                    <a href="{{ route('admin.bookings.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.bookings.*') ? 'bg-gray-700 text-white border-r-2 border-primary' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span class="transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'">Bookings</span>
                    </a>
                    <a href="{{ route('admin.instructors.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.instructors.*') ? 'bg-gray-700 text-white border-r-2 border-primary' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'">Instructors</span>
                    </a>
                    <a href="{{ route('admin.memberships.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.memberships.*') ? 'bg-gray-700 text-white border-r-2 border-primary' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span class="transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'">Memberships</span>
                    </a>
                    <a href="{{ route('admin.coupons.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.coupons.*') ? 'bg-gray-700 text-white border-r-2 border-primary' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                        <span class="transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'">Coupons</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.users.*') ? 'bg-gray-700 text-white border-r-2 border-primary' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <span class="transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'">Users</span>
                    </a>

                    <div class="px-6 py-3 mt-6">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider" x-show="!sidebarCollapsed">Analytics</p>
                    </div>
                    <a href="{{ route('admin.reports') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white {{ request()->routeIs('admin.reports') ? 'bg-gray-700 text-white border-r-2 border-primary' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 pointer-events-none hidden' : 'opacity-100'">Reports</span>
                    </a>
                @endif
            </nav>
            </div>
        </div>

        <!-- Mobile overlay -->
        <div class="fixed inset-0 bg-black/50 z-30 lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-gray-800 shadow-sm border-b border-gray-700">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <!-- Mobile hamburger -->
                            <button class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                    @click="sidebarOpen = !sidebarOpen" aria-label="Toggle Menu">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <!-- Desktop collapse toggle -->
                            <button class="hidden lg:inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                                    @click="sidebarCollapsed = !sidebarCollapsed" aria-label="Collapse Sidebar">
                                <svg x-show="!sidebarCollapsed" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <svg x-show="sidebarCollapsed" x-cloak class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h12m0 0l-4-4m4 4l-4 4" />
                                </svg>
                            </button>
                            <h2 class="font-semibold text-xl text-white leading-tight">
                                @yield('title', (auth()->check() && auth()->user()->role === 'instructor') ? 'Instructor Dashboard' : 'Admin Dashboard')
                            </h2>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-300">Welcome, {{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Are you sure you want to log out?')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-900">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @if (session('success'))
                        <div class="mb-4 bg-green-800 border border-green-600 text-green-100 px-4 py-3 rounded relative">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-800 border border-red-600 text-red-100 px-4 py-3 rounded relative">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
