<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Cronogramas de Planejamento') }}
        </h2>
    </x-slot>

    <div class="space-y-8" x-data="{ activeTab: 'active' }">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Períodos de Envio</h3>
                <p class="text-xs text-slate-500">Monitore, edite e acompanhe os cronogramas de entrega de planejamentos da sua escola.</p>
            </div>
            <a href="{{ route('school.planning.create') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow transition duration-150 flex items-center gap-1.5 self-stretch sm:self-auto justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Novo Cronograma
            </a>
        </div>

        <!-- Filtros Tab -->
        <div class="flex border-b border-slate-200 gap-6">
            <button @click="activeTab = 'active'" :class="activeTab === 'active' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-350'" class="pb-4 border-b-2 text-sm transition">
                Ativos (Em Andamento)
            </button>
            <button @click="activeTab = 'closed'" :class="activeTab === 'closed' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-350'" class="pb-4 border-b-2 text-sm transition">
                Encerrados (Histórico)
            </button>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            @if($showSchool)
                                <th class="px-6 py-4">Escola</th>
                            @endif
                            <th class="px-6 py-4">Nome do Cronograma</th>
                            <th class="px-6 py-4">Descrição / Vigência</th>
                            <th class="px-6 py-4">Prazo Limite</th>
                            <th class="px-6 py-4 text-center">Tipo</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($plannings as $p)
                            @php
                                $isClosed = $p->deadline->isPast();
                            @endphp
                            <tr x-show="(activeTab === 'active' && !@json($isClosed)) || (activeTab === 'closed' && @json($isClosed))" class="hover:bg-slate-50/50 transition">
                                @if($showSchool)
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-md text-xs font-semibold">
                                            {{ $p->school->name ?? 'Global' }}
                                        </span>
                                    </td>
                                @endif
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $p->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    <div class="text-xs">{{ $p->description }}</div>
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        Vigência: {{ $p->start_date ? $p->start_date->format('d/m/Y') : '-' }} - {{ $p->end_date ? $p->end_date->format('d/m/Y') : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold {{ $isClosed ? 'text-rose-500' : 'text-emerald-600' }}">
                                        {{ $p->deadline ? $p->deadline->format('d/m/Y H:i') : '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-xs">
                                    @if($p->is_physical_education)
                                        <span class="px-2 py-0.5 bg-sky-50 text-sky-700 rounded-full font-bold border border-sky-100">Ed. Física</span>
                                    @elseif($p->is_monitor)
                                        <span class="px-2 py-0.5 bg-violet-50 text-violet-700 rounded-full font-bold border border-violet-100">Monitoria</span>
                                    @elseif($p->is_first_grade)
                                        <span class="px-2 py-0.5 bg-teal-50 text-teal-700 rounded-full font-bold border border-teal-100">1º Ano</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full font-bold">Comum</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('school.planning.view') }}?id={{ $p->id }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg transition">
                                            Controle de Envios
                                        </a>
                                        
                                        <div class="flex items-center gap-2 border-l border-slate-200 pl-3">
                                            <a href="{{ route('school.planning.edit') }}?id={{ $p->id }}" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-md transition" title="Editar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            
                                            <form action="{{ route('school.planning.delete') }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este planejamento? Todos os envios relacionados também serão afetados.')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="id" value="{{ $p->id }}">
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md transition cursor-pointer" title="Excluir">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400 font-medium">
                                    Nenhum cronograma cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
