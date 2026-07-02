<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Painel da Secretaria (SEMED)') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Banner do Município -->
        <div class="relative bg-slate-900 rounded-2xl overflow-hidden shadow-xl p-8 text-white border border-slate-850 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/20 via-teal-600/10 to-transparent pointer-events-none"></div>
            <div class="relative z-10 space-y-2 text-center md:text-left">
                <span class="px-3 py-1 bg-emerald-500/10 text-emerald-300 rounded-full border border-emerald-500/20 text-xs font-semibold tracking-wide">
                    Rede Municipal
                </span>
                <h3 class="text-2xl font-bold tracking-tight mt-2">
                    {{ $user->tenant->name ?? 'Secretaria Municipal de Educação' }}
                </h3>
                <p class="text-slate-400 text-sm max-w-xl font-medium">
                    Seja bem-vindo ao portal de controle da SEMED. Monitore o progresso pedagógico das escolas, gerencie turmas e professores e controle os prazos de planejamento de aula.
                </p>
            </div>
            @if($user->tenant && $user->tenant->ai_enabled)
                <div class="relative z-10 flex-shrink-0 flex items-center gap-2.5 p-3.5 bg-violet-950/40 rounded-xl border border-violet-800/40">
                    <div class="w-8 h-8 rounded-lg bg-violet-600 flex items-center justify-center font-bold text-white shadow-sm shadow-violet-600/30 animate-pulse">
                        💡
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-violet-300">Assistente IA (IANNE)</h4>
                        <p class="text-[10px] text-slate-400">Ativa para o município</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Filtro Temporal -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div>
                <h3 class="text-base font-bold text-slate-900">Visão Geral da Rede</h3>
                <p class="text-xs text-slate-500 mt-0.5">Filtragem e consolidação temporal do progresso da rede municipal.</p>
            </div>
            
            <form action="{{ route('semed.dashboard') }}" method="GET" id="filterForm" class="flex items-center gap-2 w-full sm:w-auto">
                <select name="filter" onchange="document.getElementById('filterForm').submit()" class="w-full sm:w-48 text-xs font-bold border border-slate-200 rounded-xl px-4 py-2.5 bg-slate-50 text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 cursor-pointer">
                    <option value="annual" {{ $filter === 'annual' ? 'selected' : '' }}>Consolidado Anual</option>
                    <option value="1" {{ $filter === '1' ? 'selected' : '' }}>1º Bimestre</option>
                    <option value="2" {{ $filter === '2' ? 'selected' : '' }}>2º Bimestre</option>
                    <option value="3" {{ $filter === '3' ? 'selected' : '' }}>3º Bimestre</option>
                    <option value="4" {{ $filter === '4' ? 'selected' : '' }}>4º Bimestre</option>
                    @php
                        $months = [
                            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', 
                            '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                            '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro', 
                            '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
                        ];
                    @endphp
                    @foreach($months as $k => $v)
                        <option value="{{ $k }}" {{ $filter === $k ? 'selected' : '' }}>Mês de {{ $v }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Estatísticas Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Escolas Ativas</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2">{{ $schoolCount }}</h3>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Diretores</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2">{{ $directorCount }}</h3>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Coordenadores</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2">{{ $coordinatorCount }}</h3>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Professores</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2">{{ $professorCount }}</h3>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total de Envios</p>
                <h3 class="text-3xl font-black text-slate-900 mt-2">{{ $totalDocs }}</h3>
            </div>
        </div>

        <!-- Tabelas de Ranqueamento e Gamificação -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Ranking de Escolas -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col h-[400px]">
                <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center gap-2 text-sm font-bold text-slate-800">
                    🏆 Ranking de Escolas mais Pontuais
                </div>
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/20">
                                <th class="px-6 py-3 w-16 text-center">Posição</th>
                                <th class="px-6 py-3">Escola</th>
                                <th class="px-6 py-3 text-center">Pontualidade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @forelse($rankSchools as $index => $school)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 text-center font-bold text-lg text-slate-900">
                                        @if($index == 0) 🥇 @elseif($index == 1) 🥈 @elseif($index == 2) 🥉 @else {{ $index + 1 }}º @endif
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-900">{{ $school->school_name }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-indigo-600 text-base">
                                        {{ number_format($school->punctuality_percentage, 1) }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-slate-400 font-medium">
                                        Nenhum dado de envio aprovado para o período filtrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Coordenadores Destaque -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col h-[400px]">
                <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center gap-2 text-sm font-bold text-slate-800">
                    🧭 Coordenadores Destaque da Rede
                </div>
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/20">
                                <th class="px-6 py-3 w-16 text-center">Pos.</th>
                                <th class="px-6 py-3">Nome</th>
                                <th class="px-6 py-3 text-center">Pontualidade</th>
                                <th class="px-6 py-3 text-right">Contato</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @forelse($rankCoordinators as $index => $coord)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 text-center font-bold text-lg text-slate-900">
                                        @if($index == 0) 🥇 @elseif($index == 1) 🥈 @elseif($index == 2) 🥉 @else {{ $index + 1 }}º @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $coord->coordinator_name }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium">{{ $coord->school_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-violet-650 text-base">
                                        {{ number_format($coord->punctuality_percentage, 1) }}%
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($coord->whatsapp)
                                            @php
                                                $phone = preg_replace('/\D/', '', $coord->whatsapp);
                                                $url = "https://wa.me/{$phone}?text=" . urlencode("Olá, {$coord->coordinator_name}! Parabéns pelo seu excelente desempenho pedagógico como coordenador destaque da rede municipal no SGP!");
                                            @endphp
                                            <a href="{{ $url }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-650 hover:text-emerald-800 transition">
                                                💬 Falar
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-medium">
                                        Nenhum coordenador ranqueado no período.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Professores Destaque -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col h-[400px]">
                <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center gap-2 text-sm font-bold text-slate-800">
                    👩‍🏫 Professores Destaque (Regular)
                </div>
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/20">
                                <th class="px-6 py-3 w-16 text-center">Pos.</th>
                                <th class="px-6 py-3">Nome</th>
                                <th class="px-6 py-3 text-center">Pontos</th>
                                <th class="px-6 py-3 text-right">Contato</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @forelse($rankProfessors as $index => $prof)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 text-center font-bold text-lg text-slate-900">
                                        @if($index == 0) 🥇 @elseif($index == 1) 🥈 @elseif($index == 2) 🥉 @else {{ $index + 1 }}º @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $prof->professor_name }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium">{{ $prof->school_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-emerald-650 text-base">
                                        {{ number_format($prof->total_points, 1) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($prof->whatsapp)
                                            @php
                                                $phone = preg_replace('/\D/', '', $prof->whatsapp);
                                                $url = "https://wa.me/{$phone}?text=" . urlencode("Olá, {$prof->professor_name}! Parabéns pelo seu excelente desempenho no ranking de pontualidade de planejamentos do SGP!");
                                            @endphp
                                            <a href="{{ $url }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-650 hover:text-emerald-800 transition">
                                                💬 Falar
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-medium">
                                        Sem dados de ranqueamento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Monitores Destaque -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col h-[400px]">
                <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center gap-2 text-sm font-bold text-slate-800">
                    👨‍🏫 Monitores Destaque (M.A.E)
                </div>
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/20">
                                <th class="px-6 py-3 w-16 text-center">Pos.</th>
                                <th class="px-6 py-3">Nome</th>
                                <th class="px-6 py-3 text-center">Pontos</th>
                                <th class="px-6 py-3 text-right">Contato</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @forelse($rankMonitors as $index => $prof)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 text-center font-bold text-lg text-slate-900">
                                        @if($index == 0) 🥇 @elseif($index == 1) 🥈 @elseif($index == 2) 🥉 @else {{ $index + 1 }}º @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $prof->professor_name }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium">{{ $prof->school_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-teal-650 text-base">
                                        {{ number_format($prof->total_points, 1) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($prof->whatsapp)
                                            @php
                                                $phone = preg_replace('/\D/', '', $prof->whatsapp);
                                                $url = "https://wa.me/{$phone}?text=" . urlencode("Olá, {$prof->professor_name}! Parabéns pelo seu destaque como monitor da rede municipal!");
                                            @endphp
                                            <a href="{{ $url }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-650 hover:text-emerald-800 transition">
                                                💬 Falar
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-medium">
                                        Sem dados de monitoria ranqueados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabela de Escolas do Município -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Escolas Vinculadas ao Município</h3>
                    <p class="text-xs text-slate-500 mt-1">Lista de instituições escolares ativas cadastradas sob o seu contrato de locação.</p>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-50/30">
                            <th class="px-6 py-4">Nome da Escola</th>
                            <th class="px-6 py-4">Código INEP</th>
                            <th class="px-6 py-4">Diretor Responsável</th>
                            <th class="px-6 py-4 text-center">Total de Turmas</th>
                            <th class="px-6 py-4 text-center">Profissionais de Educação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($schools as $school)
                            <tr class="hover:bg-slate-50/40 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $school->name }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $school->inep_code ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $school->director->name ?? 'Não definido' }}</div>
                                    @if($school->director?->whatsapp)
                                        <div class="text-xs text-slate-400 font-medium mt-0.5">{{ $school->director->whatsapp }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $school->classes_count }}</td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $school->users_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                    Nenhuma escola cadastrada para este município.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
