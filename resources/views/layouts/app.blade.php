<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SGP') }}</title>

        <!-- Google Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
        </style>
    </head>
    <body class="h-full text-slate-800 antialiased" x-data="{ mobileSidebarOpen: false }">
        <div class="flex h-full min-h-screen">
            
            <!-- Sidebar para Desktop -->
            <aside class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 bg-slate-900 border-r border-slate-800">
                <!-- Header / Logo -->
                <div class="flex items-center h-16 px-6 bg-slate-950 border-b border-slate-800/50">
                    <span class="text-xl font-bold text-indigo-400 tracking-wide flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        SGP
                    </span>
                </div>

                <!-- Perfil / Info do Usuário -->
                <div class="p-4 mx-3 my-4 bg-slate-950/40 rounded-xl border border-slate-800/40">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-white shadow-sm shadow-indigo-600/30">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-100 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-400 font-medium capitalize">{{ auth()->user()->role }}</p>
                        </div>
                    </div>
                </div>

                <!-- Menu de Navegação -->
                <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
                    @include('layouts.sidebar-menu')
                </nav>

                <!-- Footer Sidebar (Sair) -->
                <div class="p-4 border-t border-slate-800/50">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 w-full px-4 py-2 text-sm font-medium text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition duration-150 group">
                            <svg class="w-5 h-5 text-slate-500 group-hover:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Sair da Conta
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Sidebar para Mobile (Mobile Drawer) -->
            <div x-show="mobileSidebarOpen" class="fixed inset-0 flex z-40 md:hidden" role="dialog" aria-modal="true">
                <div x-show="mobileSidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="mobileSidebarOpen = false"></div>

                <div x-show="mobileSidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex-1 flex flex-col max-w-xs w-full bg-slate-900 border-r border-slate-800">
                    <!-- Close button -->
                    <div class="absolute top-0 right-0 -mr-12 pt-2">
                        <button type="button" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" @click="mobileSidebarOpen = false">
                            <span class="sr-only">Close sidebar</span>
                            <svg class="h-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Header Mobile -->
                    <div class="flex items-center h-16 px-6 bg-slate-950 border-b border-slate-800/50">
                        <span class="text-xl font-bold text-indigo-400 tracking-wide flex items-center gap-2">
                            SGP
                        </span>
                    </div>

                    <!-- Perfil Mobile -->
                    <div class="p-4 mx-3 my-4 bg-slate-950/40 rounded-xl border border-slate-800/40">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-white">
                                {{ substr(auth()->user()->name, 0, 2) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-100">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-400 capitalize">{{ auth()->user()->role }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Mobile -->
                    <nav class="flex-1 px-3 space-y-1 overflow-y-auto">
                        @include('layouts.sidebar-menu')
                    </nav>

                    <!-- Footer Mobile (Sair) -->
                    <div class="p-4 border-t border-slate-800/50">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full px-4 py-2 text-sm font-medium text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition duration-150">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Sair da Conta
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 flex flex-col md:pl-64">
                <!-- Topbar -->
                <header class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white border-b border-slate-200/80 shadow-sm shadow-slate-100/50 px-4 md:px-8">
                    <!-- Mobile Hamburger -->
                    <button type="button" class="px-4 border-r border-slate-200 text-slate-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 md:hidden" @click="mobileSidebarOpen = true">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    
                    <!-- Header Info / Actions -->
                    <div class="flex-1 flex justify-between items-center px-4 md:px-0">
                        <div class="flex-1">
                            @if (isset($header))
                                {{ $header }}
                            @else
                                <h1 class="text-lg font-semibold text-slate-900">Dashboard</h1>
                            @endif
                        </div>
                        
                        <!-- Extra Info or Notification Icons -->
                        <div class="flex items-center gap-4">
                            @if(auth()->user()->role === 'superadmin')
                                <span class="text-xs font-semibold px-3 py-1 bg-violet-100 text-violet-800 rounded-full border border-violet-200">
                                    Administração SaaS
                                </span>
                            @elseif(auth()->user()->school_id)
                                <span class="text-xs font-semibold px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full border border-indigo-100">
                                    Escola Exemplo 1
                                </span>
                            @else
                                <span class="text-xs font-semibold px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-100">
                                    Rede Municipal (SEMED)
                                </span>
                            @endif
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto p-4 md:p-8">
                    {{ $slot }}
                </main>
            </div>
            
        </div>
    </body>
</html>
