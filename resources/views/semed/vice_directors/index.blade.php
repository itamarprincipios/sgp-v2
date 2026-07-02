<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Vice-Diretores das Escolas') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Barra de Ações -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <div>
                <h3 class="text-base font-bold text-slate-900">Vice-Diretores Cadastrados</h3>
                <p class="text-xs text-slate-500">Nem toda escola precisa de um(a) vice-diretor(a) — cadastre apenas onde existir o cargo.</p>
            </div>
            @if($schools->isEmpty())
                <span class="px-4 py-2 bg-amber-50 text-amber-700 text-xs font-semibold rounded-lg border border-amber-200">
                    Cadastre uma escola antes de adicionar vice-diretores
                </span>
            @else
                <a href="{{ route('semed.vice-directors.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-md shadow-indigo-600/10 transition duration-150 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Cadastrar Vice-Diretor(a)
                </a>
            @endif
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
                            <th class="px-6 py-4">Vice-Diretor(a)</th>
                            <th class="px-6 py-4">Escola</th>
                            <th class="px-6 py-4">E-mail</th>
                            <th class="px-6 py-4">WhatsApp</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($viceDirectors as $viceDirector)
                            <tr class="hover:bg-slate-50/40 transition duration-100">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">{{ $viceDirector->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $viceDirector->school->name ?? '—' }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $viceDirector->email }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $viceDirector->whatsapp ?? '—' }}</td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('semed.vice-directors.edit', $viceDirector) }}" class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition duration-150">
                                        Editar
                                    </a>
                                    <form action="{{ route('semed.vice-directors.reset-password', $viceDirector) }}" method="POST" class="inline" onsubmit="return confirm('Redefinir a senha de {{ $viceDirector->name }}? Uma nova senha temporária será gerada.');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-amber-200 rounded-lg text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 transition duration-150">
                                            Resetar Senha
                                        </button>
                                    </form>
                                    <form action="{{ route('semed.vice-directors.delete', $viceDirector) }}" method="POST" class="inline" onsubmit="return confirm('Excluir o(a) vice-diretor(a) {{ $viceDirector->name }}? Esta ação não pode ser desfeita.');">
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
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">
                                    Nenhum vice-diretor(a) cadastrado até o momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($viceDirectors->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $viceDirectors->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
