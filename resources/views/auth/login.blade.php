@extends('layouts.app', ['title' => 'Sign In'])

@section('content')
    <div class="flex flex-col lg:flex-row w-full h-screen overflow-hidden">
        <!-- Left Side - Enhanced Photo/Background -->
        <div class="hidden lg:flex lg:w-1/2 relative items-center justify-center overflow-hidden">
            <!-- Multi-layer Gradient Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-indigo-700 via-blue-800 to-purple-900 dark:from-blue-950 dark:via-indigo-950 dark:to-purple-950"></div>
            
            <!-- Animated Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-tr from-blue-500/20 via-transparent to-purple-500/20 animate-pulse"></div>
            
            <!-- Geometric Pattern Overlay -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=%22100%22 height=%22100%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cdefs%3E%3Cpattern id=%22grid%22 width=%22100%22 height=%22100%22 patternUnits=%22userSpaceOnUse%22%3E%3Cpath d=%22M 100 0 L 0 0 0 100%22 fill=%22none%22 stroke=%22%23ffffff%22 stroke-width=%221%22/%3E%3C/pattern%3E%3C/defs%3E%3Crect width=%22100%25%22 height=%22100%25%22 fill=%22url(%23grid)%22 /%3E%3C/svg%3E');"></div>
            </div>
            
            <!-- Circular Decorative Elements -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
            <div class="absolute top-1/2 left-1/2 w-72 h-72 bg-indigo-400/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
            
            <!-- Diagonal Lines Pattern -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute inset-0" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255,255,255,0.1) 10px, rgba(255,255,255,0.1) 20px);"></div>
            </div>
            
            <!-- Shimmer Effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent -skew-x-12 animate-shimmer"></div>
            
            <!-- Content on Left Side -->
            <div class="relative z-10 flex flex-col items-center justify-center max-w-md px-8 text-center">
                <!-- Logo with Glow Effect -->
                <div class="mb-8 relative">
                    <div class="absolute inset-0 bg-white/20 rounded-2xl blur-xl"></div>
                    <div class="relative">
                        <img 
                            class="h-20 mx-auto dark:hidden drop-shadow-2xl" 
                            src="{{ asset('tailadmin/src/images/logo/logo.svg') }}" 
                            alt="Logo"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        />
                        <img
                            class="hidden h-20 mx-auto dark:block drop-shadow-2xl"
                            src="{{ asset('tailadmin/src/images/logo/logo-dark.svg') }}"
                            alt="Logo"
                            onerror="this.style.display='none'; this.previousElementSibling.style.display='flex';"
                        />
                        <div class="hidden items-center justify-center w-20 h-20 mx-auto text-2xl font-bold text-white rounded-xl bg-white/20 shadow-2xl backdrop-blur-sm" style="display: none;">
                            <span>OSP</span>
                        </div>
                    </div>
                </div>
                
                <!-- Welcome Text with Better Typography -->
                <h2 class="text-4xl font-bold text-blue mb-4 drop-shadow-lg">
                    Welcome to NCK Online Service Portal
                </h2>
                <p class="text-blue-50 text-lg leading-relaxed mb-8 drop-shadow-md">
                    Access your professional services and manage your nursing credentials with ease. Secure, reliable, and designed for healthcare professionals.
                </p>
                
                <!-- Enhanced Decorative Elements -->
                <div class="flex items-center gap-3 mt-8">
                    <div class="w-3 h-3 rounded-full bg-white/40 shadow-lg animate-pulse"></div>
                    <div class="w-1 h-12 bg-gradient-to-b from-white/30 to-transparent"></div>
                    <div class="w-3 h-3 rounded-full bg-white/60 shadow-lg animate-pulse" style="animation-delay: 0.2s;"></div>
                    <div class="w-1 h-12 bg-gradient-to-b from-white/30 to-transparent"></div>
                    <div class="w-3 h-3 rounded-full bg-white/40 shadow-lg animate-pulse" style="animation-delay: 0.4s;"></div>
                </div>
                
                <!-- Feature Icons -->
                <div class="mt-12 flex gap-8 text-white/80">
                    <div class="flex flex-col items-center">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span class="text-xs font-medium">Secure</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="text-xs font-medium">Fast</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span class="text-xs font-medium">Reliable</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="flex-1 flex flex-col items-center justify-center bg-gradient-to-br from-gray-50 via-white to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
            <!-- Subtle Background Pattern -->
            <div class="absolute inset-0 opacity-30">
                <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, rgba(0,0,0,0.05) 1px, transparent 0); background-size: 40px 40px;"></div>
            </div>
            <div class="w-full max-w-md relative z-10">
                <!-- Logo (Mobile/Tablet) -->
                <div class="flex justify-center mb-8 lg:hidden">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-3">
                        <img 
                            class="h-10 dark:hidden" 
                            src="{{ asset('tailadmin/src/images/logo/logo.svg') }}" 
                            alt="Logo"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
                        />
                        <img
                            class="hidden h-10 dark:block"
                            src="{{ asset('tailadmin/src/images/logo/logo-dark.svg') }}"
                            alt="Logo"
                            onerror="this.style.display='none'; this.previousElementSibling.style.display='inline-flex';"
                        />
                        <span class="inline-flex items-center justify-center w-10 h-10 text-lg font-bold text-white rounded-lg bg-blue-600 shadow-lg" style="display: none;">
                            OSP
                        </span>
                    </a>
                </div>

                <!-- Login Form Card -->
                <div class="w-full p-8 bg-white shadow-xl border border-gray-200 rounded-2xl dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex flex-col items-center mb-8 text-center">
                        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Sign In</h1>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Enter your credentials to access your portal
                        </p>
                    </div>

                    <!-- Flash Messages -->
                    <div class="mb-6">
                        <x-flash-messages />
                    </div>

                    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5" novalidate>
                        @csrf
                        <div class="space-y-2">
                            <label for="username" class="text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                            <input
                                type="text"
                                class="block w-full px-4 py-3 text-sm transition border rounded-xl @error('username') border-red-300 focus:border-red-600 focus:ring-red-100 @else border-gray-200 focus:border-blue-600 focus:ring-blue-100 @enderror placeholder:text-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder:text-gray-400"
                                id="username"
                                name="username"
                                value="{{ old('username') }}"
                                placeholder="Enter your username"
                                required
                                autofocus
                            >
                            @error('username')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                            <input
                                type="password"
                                class="block w-full px-4 py-3 text-sm transition border rounded-xl @error('password') border-red-300 focus:border-red-600 focus:ring-red-100 @else border-gray-200 focus:border-blue-600 focus:ring-blue-100 @enderror placeholder:text-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder:text-gray-400"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                required
                            >
                            @error('password')
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col gap-3 pt-2">
                            <button
                                type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-white transition-all duration-200 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-md hover:shadow-lg"
                                style="background-color: #2563eb;">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Sign In
                            </button>
                            
                            @php
                                $registerRoute = \Illuminate\Support\Facades\Route::has('register') ? route('register') : '#';
                            @endphp
                            <a
                                href="{{ $registerRoute }}"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-gray-700 transition rounded-xl bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 dark:bg-gray-700 dark:text-white/90 dark:hover:bg-gray-600 shadow-theme-xs"
                            >
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                Register
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

