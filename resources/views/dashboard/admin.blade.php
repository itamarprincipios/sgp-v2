<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-100 leading-tight">
            {{ __('Painel Administrativo') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Estatísticas Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card Escolas -->
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 hover:shadow-lg hover:shadow-indigo-500/5 transition duration-200 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Escolas</p>
                        <h3 class="text-3xl font-bold text-slate-100 mt-2 group-hover:text-indigo-400 transition duration-150">{{ $schoolCount }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20 group-hover:bg-indigo-600 group-hover:text-white transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card Classes -->
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 hover:shadow-lg hover:shadow-emerald-500/5 transition duration-200 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Turmas</p>
                        <h3 class="text-3xl font-bold text-slate-100 mt-2 group-hover:text-emerald-400 transition duration-150">{{ $classCount }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20 group-hover:bg-emerald-600 group-hover:text-white transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card Professores -->
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 hover:shadow-lg hover:shadow-violet-500/5 transition duration-200 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Professores</p>
                        <h3 class="text-3xl font-bold text-slate-100 mt-2 group-hover:text-violet-400 transition duration-150">{{ $professorCount }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center border border-violet-500/20 group-hover:bg-violet-600 group-hover:text-white transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card Coordenadores/Diretores -->
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 hover:shadow-lg hover:shadow-amber-500/5 transition duration-200 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Gestores Escolares</p>
                        <h3 class="text-3xl font-bold text-slate-100 mt-2 group-hover:text-amber-400 transition duration-150">{{ $coordinatorCount + $directorCount }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20 group-hover:bg-amber-600 group-hover:text-white transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Escolas -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Escolas Cadastradas</h3>
                    <p class="text-xs text-slate-500 mt-1">Lista de escolas e estatísticas básicas de usuários e turmas.</p>
                </div>
                <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow transition duration-150">
                    + Nova Escola
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50/30">
                            <th class="px-6 py-4">Nome da Escola</th>
                            <th class="px-6 py-4">Código INEP</th>
                            <th class="px-6 py-4">Diretor</th>
                            <th class="px-6 py-4 text-center">Turmas</th>
                            <th class="px-6 py-4 text-center">Profissionais</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($schools as $school)
                            <tr class="hover:bg-slate-50/40 transition duration-100">
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $school->name }}</td>
                                <td class="px-6 py-4 font-mono text-slate-500">{{ $school->inep_code ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">{{ $school->director_name ?? 'Não definido' }}</div>
                                    @if($school->director_phone)
                                        <div class="text-xs text-slate-400 mt-0.5">{{ $school->director_phone }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-slate-800">{{ $school->classes_count }}</td>
                                <td class="px-6 py-4 text-center font-semibold text-slate-800">{{ $school->users_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                    Nenhuma escola cadastrada no momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
