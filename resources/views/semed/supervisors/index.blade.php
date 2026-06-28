<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Supervisores da Rede') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Barra de Ações -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <div>
                <h3 class="text-base font-bold text-slate-900">Supervisores Cadastrados</h3>
                <p class="text-xs text-slate-500">Cada supervisor(a) pode ser vinculado a uma ou mais escolas e só acessa dados das escolas vinculadas.</p>
            </div>
            @if($schools->isEmpty())
                <span class="px-4 py-2 bg-amber-50 text-amber-700 text-xs font-semibold rounded-lg border border-amber-200">
                    Cadastre uma escola antes de adicionar supervisores
                </span>
            @else
                <a href="{{ route('semed.supervisors.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-md shadow-indigo-600/10 transition duration-150 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Cadastrar Supervisor(a)
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
                            <th class="px-6 py-4">Supervisor(a)</th>
                            <th class="px-6 py-4">Tipo</th>
                            <th class="px-6 py-4">Escolas Vinculadas</th>
                            <th class="px-6 py-4">E-mail</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($supervisors as $supervisor)
                            <tr class="hover:bg-slate-50/40 transition duration-100">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">{{ $supervisor->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-md text-[11px] font-bold border border-indigo-100">
                                        {{ $supervisorTypes[$supervisor->role] ?? $supervisor->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600 text-xs">
                                    @forelse($supervisor->schools as $s)
                                        <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md text-[10px] font-bold mr-1 mb-1">{{ $s->name }}</span>
                                    @empty
                                        —
                                    @endforelse
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $supervisor->email }}</td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('semed.supervisors.edit', $supervisor) }}" class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition duration-150">
                                        Editar
                                    </a>
                                    <form action="{{ route('semed.supervisors.reset-password', $supervisor) }}" method="POST" class="inline" onsubmit="return confirm('Redefinir a senha de {{ $supervisor->name }}? Uma nova senha temporária será gerada.');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-amber-200 rounded-lg text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 transition duration-150">
                                            Resetar Senha
                                        </button>
                                    </form>
                                    <form action="{{ route('semed.supervisors.delete', $supervisor) }}" method="POST" class="inline" onsubmit="return confirm('Excluir o(a) supervisor(a) {{ $supervisor->name }}? Esta ação não pode ser desfeita.');">
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
                                    Nenhum supervisor(a) cadastrado até o momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($supervisors->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $supervisors->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
