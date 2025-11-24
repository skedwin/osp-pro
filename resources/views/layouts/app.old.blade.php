<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'Online Service Portal' }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <!-- Add Font Awesome for better icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
        
        <style>
            /* Custom scrollbar */
            .custom-scrollbar::-webkit-scrollbar {
                width: 4px;
            }
            
            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }
            
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 2px;
            }
            
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #9ca3af;
            }
            
            .dark .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #4b5563;
            }
            
            .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #6b7280;
            }
            
            /* No scrollbar utility */
            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }
            
            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            /* Improved sidebar text visibility */
            .menu-item-text {
                color: #374151; /* gray-700 */
                font-weight: 500;
            }

            .dark .menu-item-text {
                color: #d1d5db; /* gray-300 */
            }

            .menu-item-active .menu-item-text {
                color: #2563eb; /* blue-600 */
                font-weight: 600;
            }

            .dark .menu-item-active .menu-item-text {
                color: #60a5fa; /* blue-400 */
            }

            /* Improved hover states */
            .menu-item:hover .menu-item-text {
                color: #1f2937; /* gray-800 */
            }

            .dark .menu-item:hover .menu-item-text {
                color: #f9fafb; /* gray-50 */
            }

            /* Better tooltip visibility */
            .menu-item .menu-item-text.lg\:opacity-0 {
                background: #111827; /* gray-900 */
                color: #ffffff;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border: 1px solid #374151;
            }

            .dark .menu-item .menu-item-text.lg\:opacity-0 {
                background: #1f2937;
                color: #f9fafb;
                border: 1px solid #4b5563;
            }

            /* Improved menu group titles */
            .menu-group-title {
                color: #6b7280; /* gray-500 */
                font-weight: 600;
            }

            .dark .menu-group-title {
                color: #9ca3af; /* gray-400 */
            }
        </style>
    </head>
    <body 
        x-data="{ 
            page: 'dashboard', 
            loaded: false, 
            darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false'),
            stickyMenu: false, 
            sidebarToggle: false, 
            scrollTop: false 
        }"
        x-init="
            $watch('darkMode', value => {
                localStorage.setItem('darkMode', JSON.stringify(value));
                if (value) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            });
            if (darkMode) {
                document.documentElement.classList.add('dark');
            }
            loaded = true;
        "
        :class="{
            'dark bg-gray-900': darkMode,
            'bg-gray-50': !darkMode
        }"
        class="font-sans antialiased"
    >
        @php
            $isAuthRoute = request()->routeIs('login.*');
        @endphp

        @if ($isAuthRoute)
            <div class="flex flex-col min-h-screen">
                <main class="flex-1 flex items-center justify-center px-4 py-16">
                    <div class="w-full max-w-lg space-y-6">
                        <!-- Auth content remains the same -->
                        @yield('content')
                    </div>
                </main>
            </div>
        @else
        <!-- ===== Preloader Start ===== -->
        <div
            x-show="loaded"
            x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
            class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-gray-900"
        >
            <div
                class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-blue-500 border-t-transparent"
            ></div>
        </div>
        <!-- ===== Preloader End ===== -->

        <!-- ===== Page Wrapper Start ===== -->
        <div class="flex h-screen overflow-hidden">
            <!-- ===== Sidebar Start ===== -->
            <aside
                :class="sidebarToggle ? 'translate-x-0 lg:w-20' : '-translate-x-full'"
                class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-72 flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 transition-all duration-300 ease-in-out dark:border-gray-800 dark:bg-gray-900 lg:static lg:translate-x-0"
            >
                <!-- SIDEBAR HEADER -->
                <div
                    :class="sidebarToggle ? 'justify-center' : 'justify-between'"
                    class="flex items-center gap-2 pt-8 sidebar-header pb-7"
                >
                    <a href="{{ route('portal.dashboard') }}">
                        <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                            <span class="inline-flex items-center gap-2 text-xl font-semibold text-gray-800 dark:text-white">
                                <span class="inline-flex items-center justify-center w-10 h-10 font-bold text-white rounded-lg bg-blue-600">
                                    OSP
                                </span>
                                <span class="hidden lg:inline">Online Service Portal</span>
                            </span>
                        </span>
                        <span
                            class="logo-icon"
                            :class="sidebarToggle ? 'lg:block' : 'hidden'"
                        >
                            <span class="inline-flex items-center justify-center w-10 h-10 font-bold text-white rounded-lg bg-blue-600">
                                OSP
                            </span>
                        </span>
                    </a>
                </div>
                <!-- SIDEBAR HEADER -->

                <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
                    <!-- Sidebar Menu -->
                    <nav x-data="{selected: $persist('Dashboard')}">
                        <!-- Menu Group -->
                        <div>
                            <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-500 dark:text-gray-400 font-semibold">
                                <span
                                    class="menu-group-title"
                                    :class="sidebarToggle ? 'lg:hidden' : ''"
                                >
                                    MENU
                                </span>
                                <i 
                                    :class="sidebarToggle ? 'lg:block hidden mx-auto text-gray-500 dark:text-gray-400' : 'hidden'"
                                    class="fas fa-bars text-sm"
                                ></i>
                            </h3>

                            <ul class="flex flex-col gap-4 mb-6">
                                <!-- Menu Item Dashboard -->
                                <li>
                                    <a
                                        href="{{ route('portal.dashboard') }}"
                                        @click="selected = (selected === 'Dashboard' ? '' : 'Dashboard')"
                                        class="menu-item group relative flex items-center gap-3 rounded-lg px-3 py-3 font-medium transition-all"
                                        :class="(selected === 'Dashboard') || request()->routeIs('portal.dashboard') 
                                            ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' 
                                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                                    >
                                        <i 
                                            :class="(selected === 'Dashboard') || request()->routeIs('portal.dashboard') 
                                                ? 'fas fa-chart-pie text-blue-600 dark:text-blue-400' 
                                                : 'fas fa-chart-pie text-gray-600 group-hover:text-gray-800 dark:text-gray-400 dark:group-hover:text-gray-200'"
                                            class="text-lg w-6 text-center"
                                        ></i>

                                        <span
                                            class="menu-item-text transition-all duration-200"
                                            :class="sidebarToggle ? 'lg:opacity-0 lg:invisible lg:absolute lg:left-full lg:ml-4 lg:px-3 lg:py-2 lg:bg-gray-900 lg:text-white lg:rounded-lg lg:text-sm lg:shadow-lg lg:border lg:border-gray-700 lg:invisible lg:opacity-0 lg:group-hover:opacity-100 lg:group-hover:visible lg:z-50' : ''"
                                        >
                                            Dashboard
                                        </span>

                                        <svg
                                            class="menu-item-arrow transition-transform duration-200"
                                            :class="[
                                                (selected === 'Dashboard') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500', 
                                                sidebarToggle ? 'lg:hidden' : ''
                                            ]"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 20 20"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </a>

                                    <!-- Dropdown Menu Start -->
                                    <div
                                        class="overflow-hidden transform transition-all duration-200"
                                        :class="(selected === 'Dashboard') ? 'max-h-32 opacity-100' : 'max-h-0 opacity-0'"
                                    >
                                        <ul
                                            :class="sidebarToggle ? 'lg:hidden' : 'flex'"
                                            class="flex flex-col gap-1 mt-2 pl-12"
                                        >
                                            <li>
                                                <a
                                                    href="{{ route('portal.dashboard') }}"
                                                    class="group flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                                                    :class="request()->routeIs('portal.dashboard') 
                                                        ? 'text-blue-600 dark:text-blue-400 font-semibold' 
                                                        : 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200'"
                                                >
                                                    <i class="fas fa-chart-line text-sm"></i>
                                                    <span :class="sidebarToggle ? 'lg:hidden' : ''">Overview</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <!-- Dropdown Menu End -->
                                </li>
                                <!-- Menu Item Dashboard -->

                                <!-- Menu Item Profile -->
                                @if (session()->has('api_token'))
                                <li>
                                    <a
                                        href="{{ route('portal.dashboard') }}"
                                        @click="selected = (selected === 'Profile' ? '' : 'Profile')"
                                        class="menu-item group relative flex items-center gap-3 rounded-lg px-3 py-3 font-medium transition-all"
                                        :class="(selected === 'Profile') && request()->routeIs('portal.dashboard') 
                                            ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' 
                                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                                    >
                                        <i 
                                            :class="(selected === 'Profile') && request()->routeIs('portal.dashboard') 
                                                ? 'fas fa-user-circle text-blue-600 dark:text-blue-400' 
                                                : 'fas fa-user-circle text-gray-600 group-hover:text-gray-800 dark:text-gray-400 dark:group-hover:text-gray-200'"
                                            class="text-lg w-6 text-center"
                                        ></i>

                                        <span
                                            class="menu-item-text transition-all duration-200"
                                            :class="sidebarToggle ? 'lg:opacity-0 lg:invisible lg:absolute lg:left-full lg:ml-4 lg:px-3 lg:py-2 lg:bg-gray-900 lg:text-white lg:rounded-lg lg:text-sm lg:shadow-lg lg:border lg:border-gray-700 lg:invisible lg:opacity-0 lg:group-hover:opacity-100 lg:group-hover:visible lg:z-50' : ''"
                                        >
                                            User Profile
                                        </span>
                                    </a>
                                </li>
                                <!-- Menu Item Profile -->
                                @endif
                            </ul>
                        </div>

                        @if (session()->has('api_token'))
                        <!-- Others Group -->
                        <div>
                            <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-500 dark:text-gray-400 font-semibold">
                                <span
                                    class="menu-group-title"
                                    :class="sidebarToggle ? 'lg:hidden' : ''"
                                >
                                    others
                                </span>

                                <i 
                                    :class="sidebarToggle ? 'lg:block hidden mx-auto text-gray-500 dark:text-gray-400' : 'hidden'"
                                    class="fas fa-ellipsis-h text-sm"
                                ></i>
                            </h3>

                            <ul class="flex flex-col gap-4 mb-6">
                                <!-- Menu Item Logout -->
                                <li class="mt-auto">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="menu-item group relative flex items-center gap-3 rounded-lg px-3 py-3 font-medium transition-all w-full text-left text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                                        >
                                            <i class="fas fa-sign-out-alt text-lg w-6 text-center text-gray-600 group-hover:text-gray-800 dark:text-gray-400 dark:group-hover:text-gray-200"></i>
                                            <span
                                                class="menu-item-text transition-all duration-200"
                                                :class="sidebarToggle ? 'lg:opacity-0 lg:invisible lg:absolute lg:left-full lg:ml-4 lg:px-3 lg:py-2 lg:bg-gray-900 lg:text-white lg:rounded-lg lg:text-sm lg:shadow-lg lg:border lg:border-gray-700 lg:invisible lg:opacity-0 lg:group-hover:opacity-100 lg:group-hover:visible lg:z-50' : ''"
                                            >
                                                Sign out
                                            </span>
                                        </button>
                                    </form>
                                </li>
                                <!-- Menu Item Logout -->
                            </ul>
                        </div>
                        @endif
                    </nav>
                    <!-- Sidebar Menu -->
                </div>
            </aside>
            <!-- ===== Sidebar End ===== -->

            <!-- ===== Content Area Start ===== -->
            <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
                <!-- Small Device Overlay Start -->
                <div
                    @click="sidebarToggle = false"
                    :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                    class="fixed w-full h-screen z-9 bg-gray-900/50"
                ></div>
                <!-- Small Device Overlay End -->

                <!-- ===== Header Start ===== -->
                <header
                    x-data="{menuToggle: false}"
                    class="sticky top-0 z-99999 flex w-full border-gray-200 bg-white lg:border-b dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="flex grow flex-col items-center justify-between lg:flex-row lg:px-6">
                        <div class="flex w-full items-center justify-between gap-2 border-b border-gray-200 px-3 py-3 sm:gap-4 lg:justify-normal lg:border-b-0 lg:px-0 lg:py-4 dark:border-gray-800">
                            <!-- Hamburger Toggle BTN -->
                            <button
                                :class="sidebarToggle ? 'lg:bg-transparent dark:lg:bg-transparent bg-gray-100 dark:bg-gray-800' : ''"
                                class="z-99999 flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 lg:h-11 lg:w-11 lg:border dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                                @click.stop="sidebarToggle = !sidebarToggle"
                            >
                                <!-- SVG icons remain the same -->
                            </button>
                            <!-- Hamburger Toggle BTN -->

                            <a href="{{ route('portal.dashboard') }}" class="lg:hidden">
                                <span class="inline-flex items-center gap-2 text-xl font-semibold text-gray-800 dark:text-white">
                                    <span class="inline-flex items-center justify-center w-8 h-8 font-bold text-white rounded-lg bg-blue-600 text-sm">
                                        OSP
                                    </span>
                                </span>
                            </a>

                            <div class="hidden lg:block">
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                                    {{ $title ?? 'Portal' }}
                                </h2>
                            </div>
                        </div>

                        <div
                            :class="menuToggle ? 'flex' : 'hidden'"
                            class="shadow-theme-md w-full items-center justify-between gap-4 px-5 py-4 lg:flex lg:justify-end lg:px-0 lg:shadow-none"
                        >
                            @if (session()->has('api_token'))
                                <!-- Header content remains the same -->
                            @endif
                        </div>
                    </div>
                </header>
                <!-- ===== Header End ===== -->

                <!-- ===== Main Content Start ===== -->
                <main>
                    <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                        <!-- Session messages and content -->
                        @yield('content')
                    </div>
                </main>
                <!-- ===== Main Content End ===== -->

                <!-- ===== Footer Start ===== -->
                <footer class="sticky bottom-0 z-999 flex w-full flex-col flex-wrap items-center justify-between gap-4 border-t border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900 lg:flex-row">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        &copy; {{ now()->year }} Nursing Council of Kenya | Online Service Portal
                    </p>
                </footer>
                <!-- ===== Footer End ===== -->
            </div>
            <!-- ===== Content Area End ===== -->
        </div>
        <!-- ===== Page Wrapper End ===== -->
        @endif
        @stack('scripts')
    </body>
</html>