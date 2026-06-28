<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SGP') }} - Entrar</title>

        <!-- Google Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="h-full bg-slate-900 text-slate-200 antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12 relative overflow-hidden">

            {{-- Background glow --}}
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-1/4 left-1/3 w-96 h-96 bg-indigo-500/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-1/4 right-1/3 w-72 h-72 bg-purple-500/5 rounded-full blur-3xl"></div>
            </div>

            {{-- Logo + Branding --}}
            <div class="relative z-10 flex flex-col items-center mb-8 space-y-4">
                <a href="/">
                    <img src="{{ asset('images/ncircuits-logo.png') }}" alt="N Circuits Technologies" class="h-16 w-auto">
                </a>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-bold text-indigo-400 tracking-wide">SGP</span>
                    <span class="text-[10px] font-semibold px-2 py-0.5 bg-indigo-500/10 text-indigo-300 rounded border border-indigo-500/20">v2</span>
                </div>
                <p class="text-xs text-slate-500 font-medium">Sistema de Gestão Pedagógica</p>
            </div>

            {{-- Login Card --}}
            <div class="relative z-10 w-full sm:max-w-md bg-slate-950/60 border border-slate-800 backdrop-blur-sm rounded-2xl shadow-2xl shadow-slate-950/50 px-8 py-8">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            <div class="relative z-10 mt-8 text-center space-y-1">
                <p class="text-xs text-slate-600">
                    &copy; 2026 SGP - Todos os direitos reservados.
                </p>
                <p class="text-xs text-slate-600">
                    Desenvolvido por <a href="https://wa.me/5595991248941" target="_blank" class="font-semibold text-indigo-500 hover:text-indigo-400 transition">N Circuits Technologies</a>
                </p>
            </div>
        </div>
    </body>
</html>
