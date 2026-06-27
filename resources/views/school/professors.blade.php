<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Gerenciamento de Professores') }}
        </h2>
    </x-slot>

    <div class="space-y-8 print:space-y-0">
        <!-- Grid: Cadastro e Lista -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 print:block">
            <!-- Coluna de Cadastro (Oculta na Impressão) -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 print:hidden h-fit">
                <h3 class="text-base font-bold text-slate-900 mb-6">Cadastrar Novo Professor</h3>
                
                <form action="{{ route('school.professor.store') }}" method="POST" class="space-y-4">
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
                        <label class="block text-xs font-semibold text-slate-600">Nome Completo</label>
                        <input type="text" name="name" required placeholder="Ex: Maria Souza" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">E-mail (Login)</label>
                        <input type="email" name="email" required placeholder="Ex: maria.souza@escola.com" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">WhatsApp</label>
                        <input type="text" name="whatsapp" placeholder="Ex: 5581999999999" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Vincular a Turma (Titular)</label>
                        <select name="class_id" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <option value="">Selecione uma turma...</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">
                                    @if(count($schools) > 1) [{{ $c->school->name ?? 'N/A' }}] @endif
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Vincular a Turma (Monitoria M.A.E)</label>
                        <select name="monitor_class_id" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <option value="">Selecione uma turma...</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">
                                    @if(count($schools) > 1) [{{ $c->school->name ?? 'N/A' }}] @endif
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400">Preencha apenas se for monitor especial M.A.E.</p>
                    </div>

                    <div class="border-t border-slate-150 pt-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_physical_education" id="prof_is_pe" value="1" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <label for="prof_is_pe" class="text-xs font-semibold text-slate-700 cursor-pointer">Professor de Educação Física?</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_monitor" id="prof_is_monitor" value="1" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <label for="prof_is_monitor" class="text-xs font-semibold text-slate-700 cursor-pointer">Professor Monitor?</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_first_grade" id="prof_is_first_grade" value="1" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <label for="prof_is_first_grade" class="text-xs font-semibold text-slate-700 cursor-pointer">Professor do 1º Ano?</label>
                        </div>
                    </div>

                    <button type="submit" class="w-full mt-4 py-2.5 bg-indigo-600 hover:bg-indigo-750 text-white font-bold rounded-xl text-xs shadow transition duration-150 cursor-pointer">
                        Cadastrar Professor
                    </button>
                </form>
            </div>

            <!-- Coluna de Lista (Imprimível) -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 lg:col-span-2 print:border-none print:shadow-none print:p-0">
                <div class="flex justify-between items-center mb-6 print:hidden">
                    <h3 class="text-base font-bold text-slate-900">Professores da Unidade</h3>
                    <button onclick="window.print()" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                        🖨️ Imprimir Lista
                    </button>
                </div>

                <!-- Cabeçalho de Impressão Corporativa (Oculto na Tela, Visível na Impressão) -->
                <div class="hidden print:block border-b-2 border-slate-950 pb-4 mb-6">
                    <h1 class="text-center text-lg font-bold tracking-wider text-slate-950">SISTEMA DE GESTÃO PEDAGÓGICA - SGP</h1>
                    <h2 class="text-center text-sm font-semibold text-slate-700 mt-1">RELAÇÃO DE PROFESSORES DA UNIDADE ESCOLAR</h2>
                    <div class="grid grid-cols-2 text-xs text-slate-600 mt-4 gap-2 font-medium">
                        <div><strong>Escola:</strong> {{ $school->name ?? 'N/A' }}</div>
                        <div><strong>Diretor(a):</strong> {{ $school->director_name ?? 'N/A' }}</div>
                        <div><strong>Coordenador(a):</strong> {{ $user->name }}</div>
                        <div><strong>Emitido em:</strong> {{ now()->format('d/m/Y H:i') }}</div>
                    </div>
                </div>

                <div class="overflow-x-auto print:overflow-visible">
                    <table class="w-full text-left border-collapse print:text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/50 print:bg-transparent text-xs font-bold text-slate-500 print:text-slate-950 uppercase tracking-wider">
                                @if(count($schools) > 1)
                                    <th class="px-6 py-4 print:border print:px-3 print:py-2">Escola</th>
                                @endif
                                <th class="px-6 py-4 print:border print:px-3 print:py-2">Nome</th>
                                <th class="px-6 py-4 print:border print:px-3 print:py-2">Turma / Vinculação</th>
                                <th class="px-6 py-4 print:border print:px-3 print:py-2">E-mail</th>
                                <th class="px-6 py-4 print:border print:px-3 print:py-2">WhatsApp</th>
                                <th class="px-6 py-4 text-right print:hidden">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 print:divide-y-0 text-sm text-slate-700">
                            @forelse($professors as $prof)
                                <tr class="hover:bg-slate-50/50 print:hover:bg-transparent transition print:border-b print:border-slate-200">
                                    @if(count($schools) > 1)
                                        <td class="px-6 py-4 print:border print:px-3 print:py-2">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md text-[10px] font-bold">
                                                {{ $prof->school->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 print:border print:px-3 print:py-2 font-semibold text-slate-900">
                                        {{ $prof->name }}
                                    </td>
                                    <td class="px-6 py-4 print:border print:px-3 print:py-2 text-xs">
                                        @if($prof->is_physical_education)
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold border border-emerald-100 rounded-full">Ed. Física</span>
                                        @else
                                            @if($prof->schoolClass)
                                                <div class="font-medium text-slate-900"><strong>Titular:</strong> {{ $prof->schoolClass->name }}</div>
                                            @endif
                                            @if($prof->monitorClass)
                                                <div class="text-indigo-600 mt-1 font-medium"><strong>Monitor M.A.E:</strong> {{ $prof->monitorClass->name }}</div>
                                            @endif
                                            @if(!$prof->schoolClass && !$prof->monitorClass)
                                                <span class="text-rose-500 font-semibold">Não vinculada</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 print:border print:px-3 print:py-2 text-slate-500 font-mono text-xs">
                                        {{ $prof->email }}
                                    </td>
                                    <td class="px-6 py-4 print:border print:px-3 print:py-2 font-medium">
                                        {{ $prof->whatsapp }}
                                    </td>
                                    <td class="px-6 py-4 text-right print:hidden">
                                        <div class="flex items-center justify-end gap-2.5">
                                            <a href="{{ route('school.professor.edit') }}?id={{ $prof->id }}" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-md transition" title="Editar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            
                                            @if(!empty($prof->whatsapp))
                                                @php
                                                    $phone = preg_replace('/\D/', '', $prof->whatsapp);
                                                    if (strlen($phone) >= 10 && substr($phone, 0, 2) != '55') {
                                                        $phone = '55' . $phone;
                                                    }
                                                @endphp
                                                <a href="https://wa.me/{{ $phone }}?text=Olá, professor(a) {{ urlencode($prof->name) }}!" target="_blank" class="p-1.5 text-slate-400 hover:text-emerald-500 rounded-md transition" title="Enviar Mensagem">
                                                    💬
                                                </a>
                                            @endif

                                            <form action="{{ route('school.professor.reset-password') }}" method="POST" onsubmit="return confirm('Resetar a senha do professor {{ $prof->name }} para \'professor123\'?')" class="inline">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $prof->id }}">
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-amber-500 rounded-md transition cursor-pointer" title="Resetar Senha">
                                                    🔑
                                                </button>
                                            </form>

                                            <form action="{{ route('school.professor.delete') }}" method="POST" onsubmit="return confirm('Tem certeza que vai excluir o professor? (Esta ação não pode ser desfeita)')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="id" value="{{ $prof->id }}">
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md transition cursor-pointer" title="Excluir">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-400 font-medium">
                                        Nenhum professor cadastrado para esta escola.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
