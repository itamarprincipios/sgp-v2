<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Painel Supervisor de Educação Infantil') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Banner Supervisor -->
        <div class="relative bg-slate-900 rounded-2xl overflow-hidden shadow-xl p-8 text-white border border-slate-850 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="absolute inset-0 bg-gradient-to-r from-pink-600/20 via-rose-600/10 to-transparent pointer-events-none"></div>
            <div class="relative z-10 space-y-2 text-center md:text-left">
                <span class="px-3 py-1 bg-pink-500/10 text-pink-300 rounded-full border border-pink-500/20 text-xs font-semibold tracking-wide">
                    Supervisão de Área
                </span>
                <h3 class="text-2xl font-bold tracking-tight mt-2">
                    Supervisão da Educação Infantil
                </h3>
                <p class="text-slate-400 text-sm max-w-xl">
                    Acompanhe o cumprimento dos planejamentos e relatórios das turmas de educação infantil nas escolas vinculadas.
                </p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8 text-center max-w-xl mx-auto space-y-4">
            <div class="w-16 h-16 bg-pink-50 text-pink-600 rounded-full flex items-center justify-center font-bold text-2xl mx-auto border border-pink-100">
                🧸
            </div>
            <h3 class="text-lg font-bold text-slate-900">Módulo do Supervisor de Educação Infantil</h3>
            <p class="text-slate-500 text-sm">
                Centraliza e avalia planejamentos e relatórios da educação infantil nas escolas vinculadas a este supervisor.
            </p>
            <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-400 font-mono">
                View: resources/views/dashboard/supervisor_infantil.blade.php
            </div>
        </div>
    </div>
</x-app-layout>
