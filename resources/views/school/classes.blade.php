<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Controle de Turmas') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form de Cadastro -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 h-fit">
                <h3 class="text-base font-bold text-slate-900 mb-6">Cadastrar Nova Turma</h3>
                
                <form action="{{ route('school.class.store') }}" method="POST" class="space-y-4">
                    @csrf

                    @if(count($schools) > 1)
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600">Escola</label>
                            <select name="school_id" required class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                @foreach($schools as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="school_id" value="{{ $schools[0]->id ?? '' }}">
                    @endif

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Nome da Turma</label>
                        <input type="text" name="name" required placeholder="Ex: 5º Ano A" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <button type="submit" class="w-full mt-4 py-2.5 bg-indigo-600 hover:bg-indigo-750 text-white font-bold rounded-xl text-xs shadow transition duration-150 cursor-pointer">
                        Salvar Turma
                    </button>
                </form>
            </div>

            <!-- Listagem -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 lg:col-span-2">
                <h3 class="text-base font-bold text-slate-900 mb-6">Turmas Cadastradas</h3>
                
                <div class="divide-y divide-slate-100">
                    @forelse($classes as $c)
                        <div class="py-4 first:pt-0 last:pb-0 flex justify-between items-center text-sm">
                            <div class="space-y-1">
                                <span class="font-bold text-slate-900 flex items-center gap-2">
                                    @if(count($schools) > 1)
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md text-[10px] font-bold">
                                            {{ $c->school->name ?? 'N/A' }}
                                        </span>
                                    @endif
                                    {{ $c->name }}
                                </span>
                                
                                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                                    @php
                                        // Find teacher for this class
                                        $teacher = $c->users->where('role', 'professor')->first();
                                    @endphp
                                    @if($teacher)
                                        <span class="text-slate-600">👨‍🏫 Titular: <strong>{{ $teacher->name }}</strong></span>
                                    @else
                                        <span class="text-rose-500 font-semibold flex items-center gap-1">
                                            ⚠️ Sem professor titular
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('school.class.edit') }}?id={{ $c->id }}" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-md transition" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>

                                <form action="{{ route('school.class.delete') }}" method="POST" onsubmit="return confirm('ATENÇÃO: Tem certeza que deseja excluir esta turma? Os professores vinculados ficarão sem turma.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="id" value="{{ $c->id }}">
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md transition cursor-pointer" title="Excluir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 font-medium">
                            Nenhuma turma cadastrada.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
