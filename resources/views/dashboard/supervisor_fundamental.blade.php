<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Painel Supervisor do Ensino Fundamental') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Banner Supervisor -->
        <div class="relative bg-slate-900 rounded-2xl overflow-hidden shadow-xl p-8 text-white border border-slate-850 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="absolute inset-0 bg-gradient-to-r from-sky-600/20 via-cyan-600/10 to-transparent pointer-events-none"></div>
            <div class="relative z-10 space-y-2 text-center md:text-left">
                <span class="px-3 py-1 bg-sky-500/10 text-sky-300 rounded-full border border-sky-500/20 text-xs font-semibold tracking-wide">
                    Supervisão de Área
                </span>
                <h3 class="text-2xl font-bold tracking-tight mt-2">
                    Supervisão do Ensino Fundamental
                </h3>
                <p class="text-slate-400 text-sm max-w-xl">
                    Acompanhe o cumprimento dos planejamentos e relatórios das turmas do ensino fundamental nas escolas vinculadas.
                </p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8 text-center max-w-xl mx-auto space-y-4">
            <div class="w-16 h-16 bg-sky-50 text-sky-600 rounded-full flex items-center justify-center font-bold text-2xl mx-auto border border-sky-100">
                📚
            </div>
            <h3 class="text-lg font-bold text-slate-900">Módulo do Supervisor do Ensino Fundamental</h3>
            <p class="text-slate-500 text-sm">
                Centraliza e avalia planejamentos e relatórios do ensino fundamental nas escolas vinculadas a este supervisor.
            </p>
            <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl text-xs text-slate-400 font-mono">
                View: resources/views/dashboard/supervisor_fundamental.blade.php
            </div>
        </div>
    </div>
</x-app-layout>
