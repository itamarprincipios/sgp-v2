<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight print:hidden">
            {{ __('Relatórios e Métricas da Escola') }}
        </h2>
    </x-slot>

    @php
        $baseReportUrl = $user->role === 'semed' ? route('semed.dashboard') : route('school.reports');
    @endphp

    <div class="space-y-8">
        <!-- Seletor de Tipo de Relatório (Print Hidden) -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm print:hidden">
            <div>
                <h3 class="text-base font-bold text-slate-900">Relatórios de Rede</h3>
                <p class="text-xs text-slate-500 mt-0.5">Analise e imprima folhas de desempenho e conformidade de prazos.</p>
            </div>
            
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('school.reports', ['type' => 'submissions']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $type === 'submissions' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-50 border border-slate-200 text-slate-650 hover:bg-slate-100' }}">
                    Envios
                </a>
                <a href="{{ route('school.reports', ['type' => 'pendencies']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $type === 'pendencies' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-50 border border-slate-200 text-slate-650 hover:bg-slate-100' }}">
                    Pendências
                </a>
                <a href="{{ route('school.reports', ['type' => 'punctuality']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $type === 'punctuality' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-50 border border-slate-200 text-slate-650 hover:bg-slate-100' }}">
                    Pontualidade
                </a>
            </div>
        </div>

        <!-- Filtros (Print Hidden) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm print:hidden">
            <form action="{{ route('school.reports') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 xl:grid-cols-4 gap-4 items-end">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="school_id" value="{{ $schoolId }}">

                @if(count($schools) > 1)
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Escola</label>
                        <select name="school_id" onchange="this.form.submit()" class="w-full text-xs font-bold border border-slate-200 rounded-xl px-4 py-2.5 bg-slate-50 text-slate-700">
                            @foreach($schools as $s)
                                <option value="{{ $s->id }}" {{ $schoolId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-600">Professor</label>
                    <select name="professor_id" onchange="this.form.submit()" class="w-full text-xs font-bold border border-slate-200 rounded-xl px-4 py-2.5 bg-slate-50 text-slate-700">
                        <option value="">Todos os Professores</option>
                        @foreach($professors as $prof)
                            <option value="{{ $prof->id }}" {{ $professorId == $prof->id ? 'selected' : '' }}>{{ $prof->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if($professorId)
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Filtro Temporal</label>
                        <select name="period" onchange="this.form.submit()" class="w-full text-xs font-bold border border-slate-200 rounded-xl px-4 py-2.5 bg-slate-50 text-slate-700">
                            <option value="annual" {{ $period === 'annual' ? 'selected' : '' }}>Consolidado Anual</option>
                            <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Mês de Referência (Atual)</option>
                        </select>
                    </div>
                @endif

                <div>
                    <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-slate-950 hover:bg-slate-900 text-white font-bold rounded-xl text-xs shadow-sm transition flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto">
                        🖨️ Imprimir Relatório
                    </button>
                </div>
            </form>
        </div>

        <!-- Conteúdo do Relatório -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-6 md:p-8 list-section">
            
            <!-- Cabeçalho Exclusivo de Impressão (Hidden on Screen, Visible on Print) -->
            <div class="hidden print:block border-b-2 border-slate-950 pb-4 mb-6 text-center space-y-2">
                <h1 class="text-xl font-bold uppercase tracking-wide text-slate-900">SGP - Sistema de Gestão Pedagógica</h1>
                <h2 class="text-sm font-semibold text-slate-700">Relatório da Unidade Escolar</h2>
                <div class="text-xs text-slate-500 font-medium grid grid-cols-2 text-left gap-2 pt-4">
                    <div><strong>Escola:</strong> {{ $schools->firstWhere('id', $schoolId)->name ?? ($school->name ?? 'N/A') }}</div>
                    <div><strong>Diretor(a):</strong> {{ $user->name }}</div>
                    <div><strong>Tipo de Filtro:</strong> {{ strtoupper($type) }}</div>
                    <div><strong>Emitido em:</strong> {{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            @if($professorId && isset($data['stats']))
                @php
                    $stats = $data['stats'];
                    $submissions = $data['submissions'];
                    $selectedProf = $professors->firstWhere('id', $professorId);
                @endphp

                <!-- Detalhes do Professor Selecionado -->
                <div class="mb-8 space-y-6">
                    <div class="flex flex-col sm:flex-row items-center gap-6 pb-6 border-b border-slate-100">
                        @php
                            $photoUrl = !empty($selectedProf->profile_photo) ? asset('uploads/avatars/' . $selectedProf->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($selectedProf->name) . '&background=6366f1&color=fff';
                        @endphp
                        <img src="{{ $photoUrl }}" alt="Foto do Professor" class="w-24 h-24 rounded-full object-cover border border-slate-200 shadow-sm print:hidden">
                        
                        <div class="text-center sm:text-left space-y-1">
                            <h3 class="text-lg font-bold text-slate-900 flex items-center justify-center sm:justify-start gap-2">
                                {{ $selectedProf->name }}
                                @if($selectedProf->is_monitor)
                                    <span class="px-2 py-0.5 bg-sky-100 text-sky-850 rounded text-xs font-bold">M.A.E</span>
                                @endif
                            </h3>
                            <p class="text-xs text-slate-500 font-medium">Histórico Individual de Conformidade e Pontualidade</p>
                            @if($selectedProf->whatsapp)
                                <p class="text-xs text-slate-400 font-medium print:hidden">💬 WhatsApp: {{ $selectedProf->whatsapp }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Métricas Rápidas do Professor -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div class="bg-slate-50 border border-slate-150 rounded-xl p-4 text-center">
                            <span class="text-xs text-slate-500 font-medium block">Total Enviado</span>
                            <span class="text-xl font-bold text-slate-900 mt-1 block">{{ $stats['total_sent'] ?? 0 }}</span>
                        </div>
                        <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 text-center">
                            <span class="text-xs text-emerald-800 font-medium block">No Prazo</span>
                            <span class="text-xl font-bold text-emerald-700 mt-1 block">{{ $stats['on_time'] ?? 0 }}</span>
                        </div>
                        <div class="bg-rose-50/50 border border-rose-100 rounded-xl p-4 text-center">
                            <span class="text-xs text-rose-800 font-medium block">Com Atraso</span>
                            <span class="text-xl font-bold text-rose-700 mt-1 block">{{ $stats['late_docs'] ?? 0 }}</span>
                        </div>
                        <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 text-center">
                            <span class="text-xs text-emerald-850 font-medium block">Aprovados</span>
                            <span class="text-xl font-bold text-emerald-800 mt-1 block">{{ $stats['approved'] ?? 0 }}</span>
                        </div>
                        <div class="bg-amber-50/50 border border-amber-100 rounded-xl p-4 text-center">
                            <span class="text-xs text-amber-850 font-medium block">Ajustes</span>
                            <span class="text-xl font-bold text-amber-800 mt-1 block">{{ $stats['adjusted'] ?? 0 }}</span>
                        </div>
                        <div class="bg-rose-50/50 border border-rose-100 rounded-xl p-4 text-center">
                            <span class="text-xs text-rose-850 font-medium block">Rejeitados</span>
                            <span class="text-xl font-bold text-rose-800 mt-1 block">{{ $stats['rejected'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <h4 class="text-sm font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Histórico de Envios Detalhado</h4>
                <table class="w-full text-left border-collapse data-table">
                    <thead>
                        <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/20">
                            <th class="px-4 py-3">Planejamento</th>
                            <th class="px-4 py-3">Envio</th>
                            <th class="px-4 py-3">Prazo</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-center">Nota Final</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($submissions as $sub)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-4 py-3.5 font-bold text-slate-900">{{ $sub->period_name }}</td>
                                <td class="px-4 py-3.5">
                                    {{ \Carbon\Carbon::parse($sub->submitted_at)->format('d/m/Y H:i') }}
                                    @if(\Carbon\Carbon::parse($sub->submitted_at)->gt(\Carbon\Carbon::parse($sub->deadline)))
                                        <span class="ml-2 text-[10px] font-bold bg-rose-50 text-rose-700 px-2 py-0.5 rounded border border-rose-100">Atrasado</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">{{ \Carbon\Carbon::parse($sub->deadline)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3.5 capitalize font-medium">{{ $sub->status }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-slate-950">
                                    {{ number_format($sub->score_final, 1) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400 font-semibold">
                                    Nenhum envio encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($type === 'submissions')
                <h4 class="text-sm font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Resumo de Entregas por Professor</h4>
                <table class="w-full text-left border-collapse data-table">
                    <thead>
                        <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/20">
                            <th class="px-4 py-3">Escola</th>
                            <th class="px-4 py-3">Professor</th>
                            <th class="px-4 py-3 text-center">Total Enviado</th>
                            <th class="px-4 py-3 text-center">Aprovados</th>
                            <th class="px-4 py-3 text-center">Com Atraso</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($data as $row)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-4 py-3.5 text-slate-500 font-medium">{{ $row->school_name }}</td>
                                <td class="px-4 py-3.5">
                                    <a href="{{ route('school.reports', ['type' => $type, 'professor_id' => $row->professor_id, 'school_id' => $schoolId]) }}" class="font-bold text-indigo-600 hover:text-indigo-800 print:text-slate-900 print:pointer-events-none">
                                        {{ $row->professor_name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold">{{ $row->total_sent }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-emerald-650">{{ $row->approved }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-rose-650">{{ $row->late_docs }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400 font-semibold">
                                    Sem dados cadastrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($type === 'pendencies')
                <h4 class="text-sm font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Planejamentos Pendentes (Atrasados)</h4>
                <table class="w-full text-left border-collapse data-table">
                    <thead>
                        <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/20">
                            <th class="px-4 py-3">Escola</th>
                            <th class="px-4 py-3">Professor</th>
                            <th class="px-4 py-3">Planejamento</th>
                            <th class="px-4 py-3">Prazo Limite</th>
                            <th class="px-4 py-3 text-center">Dias de Atraso</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($data as $row)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-4 py-3.5 text-slate-500 font-medium">{{ $row->school_name }}</td>
                                <td class="px-4 py-3.5 font-bold text-slate-900">{{ $row->professor_name }}</td>
                                <td class="px-4 py-3.5 font-semibold text-slate-900">{{ $row->period_name }}</td>
                                <td class="px-4 py-3.5 text-slate-500">{{ \Carbon\Carbon::parse($row->deadline)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-rose-650">{{ $row->days_late }} dias</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-slate-400 font-semibold">
                                    🎉 Excelente! Nenhuma pendência de envio identificada na rede.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($type === 'punctuality')
                <h4 class="text-sm font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Ranqueamento de Pontualidade</h4>
                <table class="w-full text-left border-collapse data-table">
                    <thead>
                        <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/20">
                            <th class="px-4 py-3 w-16 text-center">Posição</th>
                            <th class="px-4 py-3">Professor</th>
                            <th class="px-4 py-3 text-center">Média de Notas</th>
                            <th class="px-4 py-3 text-center">Entregas Validadas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($data as $index => $row)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-4 py-3.5 text-center font-bold text-slate-900">
                                    @if($index == 0) 🥇 @elseif($index == 1) 🥈 @elseif($index == 2) 🥉 @else {{ $index + 1 }}º @endif
                                </td>
                                <td class="px-4 py-3.5 font-bold text-slate-900">
                                    {{ $row->professor_name }}
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-indigo-600">
                                    {{ number_format($row->avg_score, 1) }} pts
                                </td>
                                <td class="px-4 py-3.5 text-center text-slate-500 font-medium">
                                    {{ $row->total_docs }} documentos
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-400 font-semibold">
                                    Nenhum dado consolidado de pontualidade.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- Professional Print Customizations Stylesheet -->
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .print\:hidden {
                display: none !important;
            }
            .list-section {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            table.data-table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 11px !important;
            }
            table.data-table th, table.data-table td {
                border: 1px solid #cbd5e1 !important;
                padding: 8px 12px !important;
            }
            table.data-table thead {
                display: table-header-group !important;
                background-color: #f1f5f9 !important;
                color: #0f172a !important;
            }
            table.data-table tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
</x-app-layout>
