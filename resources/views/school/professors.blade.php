<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Professores') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Professores da escola</h3>
            <p class="text-xs text-slate-500">
                Os professores são identificados automaticamente pela IANNE a partir dos documentos que você envia para correção.
                Clique num card para ver todos os documentos daquele professor.
            </p>
        </div>

        @if($professors->isEmpty())
            <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <p class="text-sm font-bold text-slate-700">Nenhum professor ainda</p>
                <p class="text-xs text-slate-500 mt-1">
                    Envie um planejamento em
                    <a href="{{ route('school.corrections') }}" class="text-indigo-600 font-semibold hover:text-indigo-800">Correções</a>
                    e a IANNE cria o professor a partir do documento.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($professors as $professor)
                    <a href="{{ route('school.professor.documents', ['id' => $professor->id]) }}"
                       class="group bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:border-indigo-400 hover:shadow-md transition flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shrink-0">
                            {{ mb_strtoupper(mb_substr($professor->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900 truncate group-hover:text-indigo-700">{{ $professor->name }}</p>
                            <p class="text-xs text-slate-500">{{ $professor->schoolClass->name ?? 'Sem turma definida' }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-lg font-bold text-slate-900">{{ $professor->documents_count }}</p>
                            <p class="text-[10px] text-slate-400 uppercase tracking-wider">docs</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
