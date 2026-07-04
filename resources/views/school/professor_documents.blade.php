<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Documentos do Professor') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <a href="{{ route('school.professors') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800">
            ← Voltar aos professores
        </a>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xl shrink-0">
                {{ mb_strtoupper(mb_substr($professor->name, 0, 2)) }}
            </div>
            <div>
                <p class="text-lg font-bold text-slate-900">{{ $professor->name }}</p>
                <p class="text-sm text-slate-500">{{ $professor->schoolClass->name ?? 'Sem turma definida' }} · {{ $documents->count() }} documento(s)</p>
            </div>
        </div>

        @if($documents->isEmpty())
            <div class="bg-white border border-slate-200 rounded-xl p-8 text-center">
                <p class="text-sm text-slate-500">Nenhum documento vinculado a este professor ainda.</p>
            </div>
        @else
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Documento</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Turma</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Período</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($documents as $doc)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-slate-800">{{ $doc->title }}</p>
                                    <p class="text-xs text-slate-400">{{ ucfirst($doc->type) }}{{ $doc->discipline ? ' · ' . $doc->discipline : '' }} · {{ $doc->created_at->format('d/m/Y') }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ $doc->schoolClass->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ $doc->reference_label ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($doc->status === 'corrigido')
                                        <span class="text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 rounded-full px-2.5 py-1">Corrigido</span>
                                    @elseif($doc->status === 'em_correcao')
                                        <span class="text-[10px] font-bold uppercase bg-sky-100 text-sky-800 rounded-full px-2.5 py-1">Em correção</span>
                                    @else
                                        <span class="text-[10px] font-bold uppercase bg-amber-100 text-amber-800 rounded-full px-2.5 py-1">Aguardando</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ asset('uploads/' . $doc->file_path) }}" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Abrir</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
