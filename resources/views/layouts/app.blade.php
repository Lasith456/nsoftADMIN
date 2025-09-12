<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100 dark:bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NSoftPOS') }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Scrollbar styling */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .sub-menu-content::-webkit-scrollbar { width: 8px; }
        .sub-menu-content::-webkit-scrollbar-track { background: #e5e7eb; border-radius: 10px; }
        .sub-menu-content::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 10px; }
        .sub-menu-content::-webkit-scrollbar-thumb:hover { background: #6b7280; }

        /* Sub-menu scroll behavior */
        .overflow-y-hidden { overflow-y: hidden; }
        .sub-menu-content:hover { overflow-y: auto; }
        .sub-menu-open { max-height: 250px; overflow-y: auto; }
    </style>
</head>
<body class="h-full font-sans antialiased">
<div x-data="{
        sidebarOpen: false,
        sidebarHover: false,
        // Initialize state by reading from localStorage, default to 'false' if not set
        sidebarCollapsed: JSON.parse(localStorage.getItem('sidebarCollapsed')) || false,
        
        // Use x-init to watch for changes and save them back to localStorage
        init() {
            this.$watch('sidebarCollapsed', value => localStorage.setItem('sidebarCollapsed', JSON.stringify(value)))
        }
    }" 
    @keydown.window.escape="sidebarOpen = false">
            @auth
        <div x-cloak class="relative z-50 lg:hidden" role="dialog" aria-modal="true" x-show="sidebarOpen">
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/80"></div>
            <div class="fixed inset-0 flex">
                <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative mr-16 flex w-full max-w-xs flex-1">
                    <div x-show="sidebarOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                            <span class="sr-only">Close sidebar</span>
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6 pb-4 ring-1 ring-white/10 no-scrollbar">
                        <div class="flex h-16 shrink-0 items-center">
                            <a href="/home" class="text-white font-bold text-lg"> <span class='text-white font-bold text-lg'>NSoftAdmin</span></a>
                        </div>
                        @include('layouts.navigation')
                    </div>
                </div>
            </div>
        </div>

        <div @mouseenter="sidebarHover = true" @mouseleave="sidebarHover = false"
             class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:flex-col transition-all duration-300"
             :class="{ 'lg:w-16': sidebarCollapsed && !sidebarHover, 'lg:w-72': !sidebarCollapsed || sidebarHover }">
            
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-4 pb-4 no-scrollbar">
                <div class="flex h-16 shrink-0 items-center" :class="{'justify-center': sidebarCollapsed && !sidebarHover, 'justify-start px-2': !sidebarCollapsed || sidebarHover}">
                   <a href="/home" class="text-white font-bold text-lg">
                       <span class='whitespace-nowrap' x-show="!sidebarCollapsed || sidebarHover">NSoftAdmin</span>
                   </a>
                    <a href="/home" class="text-white font-bold text-lg">
                        <span x-show="sidebarCollapsed && !sidebarHover">NS</span>
                    </a>
                </div>
                {{-- Navigation links are injected here --}}
                @include('layouts.navigation')
            </div>
             <div class="border-t border-gray-700 p-2 bg-gray-900">
                <button @click="sidebarCollapsed = !sidebarCollapsed" class="w-full flex items-center p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-md transition-colors duration-200"
                        :class="{'justify-center': sidebarCollapsed, 'justify-end': !sidebarCollapsed}">
                    <span class="sr-only">Toggle sidebar</span>
                    <svg x-show="!sidebarCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                    <svg x-show="sidebarCollapsed" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="transition-all duration-300" :class="{ 'lg:pl-16': sidebarCollapsed && !sidebarHover, 'lg:pl-72': !sidebarCollapsed || sidebarHover }">
            <div class="sticky top-0 z-40 flex h-10 shrink-0 items-center gap-x-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 dark:text-gray-400 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <div class="h-6 w-px bg-gray-900/10 dark:bg-gray-700 lg:hidden" aria-hidden="true"></div>
                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6 ">
                    <div class="relative flex flex-1"></div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <div class="h-6 w-px bg-gray-900/10 dark:bg-gray-700 hidden lg:block" aria-hidden="true"></div>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button" class="-m-1.5 flex items-center p-1.5" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                <span class="sr-only">Open user menu</span>
                                <img class="h-8 w-8 rounded-full bg-gray-50" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                <span class="hidden lg:flex lg:items-center">
                                    <span class="ml-4 text-sm font-semibold leading-6 text-gray-900 dark:text-gray-200" aria-hidden="true">{{ Auth::user()->name }}</span>
                                    <svg class="ml-2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition x-cloak class="absolute right-0 z-10 mt-2.5 w-32 origin-top-right rounded-md bg-white dark:bg-gray-800 py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                                <a href="#" class="block px-3 py-1 text-sm leading-6 text-gray-900 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700" role="menuitem" tabindex="-1">Your profile</a>
                                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-3 py-1 text-sm leading-6 text-gray-900 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700" role="menuitem" tabindex="-1">Sign out</a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <main class="py-1">
                <div class="px-1 sm:px-2 lg:px-3">
                    @yield('content')
                </div>
            </main>
        </div>
        @else
        <main>
            @yield('content')
        </main>
        @endauth
    </div>
</body>
</html>