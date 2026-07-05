<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Documentos do Professor') }}
        </h2>
    </x-slot>

    <div class="space-y-6" x-data="{ analysisOpen: false, analysisTitle: '', analysisText: '' }">
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
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ações</th>
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
                                    @if($doc->status === 'aprovado')
                                        <span class="text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 rounded-full px-2.5 py-1">Aprovado</span>
                                    @elseif($doc->status === 'corrigido')
                                        <span class="text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 rounded-full px-2.5 py-1">Corrigido</span>
                                    @elseif($doc->status === 'em_correcao')
                                        <span class="text-[10px] font-bold uppercase bg-sky-100 text-sky-800 rounded-full px-2.5 py-1">Em correção</span>
                                    @else
                                        <span class="text-[10px] font-bold uppercase bg-amber-100 text-amber-800 rounded-full px-2.5 py-1">Aguardando</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        @if($doc->analysis)
                                            <button @click="analysisTitle = @js($doc->title); analysisText = @js($doc->analysis); analysisOpen = true" class="text-xs font-bold text-violet-600 hover:text-violet-800">Rever análise</button>
                                        @endif
                                        <a href="{{ $doc->viewer_url }}" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Abrir</a>
                                        <a href="{{ $doc->download_url }}" download class="text-xs font-bold text-slate-500 hover:text-slate-700">Baixar</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <style>[x-cloak]{display:none!important}</style>

        <!-- Modal de releitura do parecer da IANNE -->
        <div x-show="analysisOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60" @click="analysisOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white">
                    <h3 class="font-bold text-slate-900 text-sm">Análise da IANNE — <span class="text-slate-600" x-text="analysisTitle"></span></h3>
                    <button @click="analysisOpen = false" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="p-6">
                    <pre class="whitespace-pre-wrap text-sm text-slate-800 font-sans leading-relaxed" x-text="analysisText"></pre>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
