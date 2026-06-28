<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Escolas da Rede Municipal') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Barra de Ações -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <div>
                <h3 class="text-base font-bold text-slate-900">Escolas Cadastradas</h3>
                <p class="text-xs text-slate-500">Cadastre as escolas da rede antes de vincular diretores e supervisores.</p>
            </div>
            <a href="{{ route('semed.schools.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-md shadow-indigo-600/10 transition duration-150 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Cadastrar Escola
            </a>
        </div>

        <!-- Alertas -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Tabela -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                            <th class="px-6 py-4">Escola</th>
                            <th class="px-6 py-4">Código INEP</th>
                            <th class="px-6 py-4">Endereço</th>
                            <th class="px-6 py-4">Diretor(a)</th>
                            <th class="px-6 py-4 text-center">Profissionais</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($schools as $school)
                            <tr class="hover:bg-slate-50/40 transition duration-100">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">{{ $school->name }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $school->inep_code ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $school->address ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $school->director_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-center font-semibold text-slate-700">{{ $school->users_count }}</td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('semed.schools.edit', $school) }}" class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition duration-150">
                                        <svg class="w-3.5 h-3.5 text-slate-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Editar
                                    </a>
                                    <form action="{{ route('semed.schools.delete', $school) }}" method="POST" class="inline" onsubmit="return confirm('Excluir a escola {{ $school->name }}? Profissionais vinculados perderão o vínculo com esta escola.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-rose-200 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 transition duration-150">
                                            Excluir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">
                                    Nenhuma escola cadastrada até o momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($schools->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $schools->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
