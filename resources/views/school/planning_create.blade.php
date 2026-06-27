<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Cadastrar Novo Período de Planejamento') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Novo Período de Envio</h3>
                <p class="text-xs text-slate-500">Defina o nome, vigência e prazos limite do planejamento pedagógico.</p>
            </div>
            <a href="{{ route('school.plannings') }}" class="px-4 py-2 border border-slate-200 text-slate-650 hover:bg-slate-50 font-semibold rounded-xl text-xs transition">
                Voltar
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8">
            <form action="{{ route('school.planning.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Selecionar Escola (se o usuário gerenciar mais de uma) -->
                @if(count($schools) > 1)
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Selecione a Escola</label>
                        <select name="school_id" required class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            @foreach($schools as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="school_id" value="{{ $schools[0]->id ?? '' }}">
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl text-sm font-semibold text-slate-700">
                        Escola: {{ $schools[0]->name ?? 'N/A' }}
                    </div>
                @endif

                <!-- Nome -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs font-semibold text-slate-600">Nome do Planejamento</label>
                    <input type="text" name="name" id="name" required placeholder="Ex: Planejamento Bimestral 01" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <p class="text-[10px] text-slate-400">Identificação principal visível para os professores.</p>
                </div>

                <!-- Descrição -->
                <div class="space-y-1.5">
                    <label for="description" class="block text-xs font-semibold text-slate-600">Descrição do Período</label>
                    <input type="text" name="description" id="description" required placeholder="Ex: Período de 02 a 13 de Março/2026" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <p class="text-[10px] text-slate-400">Mensagem de orientação explicativa.</p>
                </div>

                <!-- Vigência -->
                <div class="space-y-1.5">
                    <label for="start_date" class="block text-xs font-semibold text-slate-600">Início da Vigência</label>
                    <input type="date" name="start_date" id="start_date" required value="{{ date('Y-m-d') }}" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <p class="text-[10px] text-slate-400">
                        O sistema calculará automaticamente o prazo final de entrega para o dia anterior a esta data (às 23:59) e o início das submissões para 7 dias antes.
                    </p>
                </div>

                <!-- End Date -->
                <input type="hidden" name="end_date" value="{{ date('Y-m-d H:i:s', strtotime('+30 days')) }}">

                <!-- Condicionalidades / Exclusividades -->
                <div class="border-t border-slate-100 pt-4 space-y-3">
                    <h4 class="text-xs font-bold text-slate-900 mb-2">Exclusividades do Período</h4>
                    
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_physical_education" id="is_physical_education" value="1" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        <label for="is_physical_education" class="text-xs font-semibold text-slate-700 cursor-pointer">
                            Este planejamento é exclusivo para <span class="text-indigo-600 font-bold">Educação Física</span>?
                        </label>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_monitor" id="is_monitor" value="1" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        <label for="is_monitor" class="text-xs font-semibold text-slate-700 cursor-pointer">
                            Este planejamento é exclusivo para <span class="text-indigo-600 font-bold">Professor Monitor</span>?
                        </label>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_first_grade" id="is_first_grade" value="1" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        <label for="is_first_grade" class="text-xs font-semibold text-slate-700 cursor-pointer">
                            Este planejamento é exclusivo para turmas do <span class="text-indigo-600 font-bold">1º Ano</span>?
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <a href="{{ route('school.plannings') }}" class="px-4 py-2 border border-slate-200 text-slate-650 hover:bg-slate-50 font-semibold rounded-xl text-sm transition">
                        Cancelar
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow transition">
                        Salvar Cronograma
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
