<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Painel da Escola (Gestão Escolar)') }}
        </h2>
    </x-slot>

    @php
        $tab = request()->query('tab', 'home');
    @endphp

    <div class="space-y-8">
        <!-- Alertas de Sucesso/Erro -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Banner da Escola -->
        <div class="relative bg-slate-900 rounded-2xl overflow-hidden shadow-xl p-6 md:p-8 text-white border border-slate-850 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/20 via-sky-600/10 to-transparent pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6 text-center md:text-left">
                <!-- Avatar & Foto Upload -->
                <div class="relative group">
                    @php
                        $photoUrl = !empty($user->profile_photo) ? asset('uploads/avatars/' . $user->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff';
                    @endphp
                    <img src="{{ $photoUrl }}" alt="Perfil" class="w-24 h-24 rounded-full object-cover border-4 border-slate-800 shadow-md">
                    
                    <form action="{{ route('school.photo.upload') }}" method="POST" enctype="multipart/form-data" id="photo-form" class="hidden">
                        @csrf
                        <input type="file" name="photo" id="photo-input" accept="image/png, image/jpeg" onchange="document.getElementById('photo-form').submit()">
                    </form>
                    
                    <button onclick="document.getElementById('photo-input').click()" title="Alterar Foto" class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-white text-slate-950 flex items-center justify-center shadow-lg border border-slate-200 hover:scale-110 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-2">
                    <span class="px-3 py-1 bg-indigo-500/10 text-indigo-300 rounded-full border border-indigo-500/20 text-xs font-semibold tracking-wide capitalize">
                        {{ $user->role === 'director' ? 'Diretor Escolar' : 'Coordenador Pedagógico' }}
                    </span>
                    <h3 class="text-2xl font-bold tracking-tight mt-1">{{ $user->name }}</h3>
                    <p class="text-slate-400 text-sm max-w-xl flex items-center gap-1.5 justify-center md:justify-start">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        {{ $school->name ?? 'Nenhuma escola vinculada' }}
                    </p>
                </div>
            </div>
            
            <!-- Ações Rápidas -->
            <div class="relative z-10 self-stretch md:self-auto flex flex-col justify-center">
                <a href="{{ route('school.planning.create') }}" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-750 text-white rounded-xl text-xs font-bold shadow-lg transition duration-150 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Novo Período de Planejamento
                </a>
            </div>
        </div>

        @if($tab === 'home')
            <!-- ================= TAB: HOME ================= -->
            <!-- Cards Estatísticos (KPIs) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Professores -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-semibold">
                        👨‍🏫
                    </div>
                    <div>
                        <span class="text-2xl font-bold text-slate-900">{{ $professorsCount }}</span>
                        <span class="text-xs text-slate-500 block font-medium">Professores Cadastrados</span>
                    </div>
                </div>

                <!-- Turmas -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-semibold">
                        🏫
                    </div>
                    <div>
                        <span class="text-2xl font-bold text-slate-900">{{ $classesCount }}</span>
                        <span class="text-xs text-slate-500 block font-medium">Turmas Ativas</span>
                    </div>
                </div>

                <!-- Planejamentos (Períodos) -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-semibold">
                        📅
                    </div>
                    <div>
                        <span class="text-2xl font-bold text-slate-900">{{ $periodsCount }}</span>
                        <span class="text-xs text-slate-500 block font-medium">Períodos de Planejamento</span>
                    </div>
                </div>

                <!-- Pendências -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-semibold">
                        ⚠️
                    </div>
                    <div>
                        <span class="text-2xl font-bold text-slate-900">{{ $pendingCount }}</span>
                        <span class="text-xs text-slate-500 block font-medium">Entregas Pendentes</span>
                    </div>
                </div>
            </div>

            <!-- GAMIFICATION (Apenas para Diretores) -->
            @if($user->role === 'director')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Ranking de Escolas -->
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-6 space-y-4">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                            <span class="text-2xl">🏆</span>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Ranking de Escolas mais Pontuais</h4>
                                <p class="text-xs text-slate-500">Média municipal baseada na taxa de pontualidade dos envios</p>
                            </div>
                        </div>
                        
                        <div class="divide-y divide-slate-100">
                            <!-- Minha Escola -->
                            <div class="py-3 flex items-center justify-between text-sm bg-indigo-50/50 -mx-6 px-6 font-semibold border-y border-indigo-100/50">
                                <div class="flex items-center gap-4">
                                    <span class="text-indigo-600 text-xs w-8 text-center">{{ $mySchoolRank }}º</span>
                                    <span class="text-slate-950 font-bold">{{ $mySchoolData['school_name'] }} (Sua Escola)</span>
                                </div>
                                <span class="text-indigo-600 font-bold">{{ number_format($mySchoolData['avg_score'], 1) }}%</span>
                            </div>

                            <!-- Outras Escolas (Top 3) -->
                            @foreach(array_slice($globalPunctuality, 0, 3) as $index => $row)
                                @if($row['school_name'] !== ($school->name ?? ''))
                                    <div class="py-3 flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-4">
                                            <span class="text-slate-400 text-xs w-8 text-center">{{ $index + 1 }}º</span>
                                            <span class="text-slate-700">{{ $row['school_name'] }}</span>
                                        </div>
                                        <span class="text-slate-950 font-semibold">{{ number_format($row['avg_score'], 1) }}%</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Professors Ranking -->
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-6 space-y-4">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                            <span class="text-2xl">🎖️</span>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Professores Destaque</h4>
                                <p class="text-xs text-slate-500">Professores mais engajados com o envio no prazo</p>
                            </div>
                        </div>
                        
                        <div class="divide-y divide-slate-100">
                            @forelse($topProfessors as $i => $prof)
                                <div class="py-3.5 flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-4">
                                        <span class="text-emerald-500 text-xs font-bold w-6 text-center">
                                            {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : '🥉') }}
                                        </span>
                                        <div>
                                            <span class="text-slate-900 font-bold block">{{ $prof['name'] }}</span>
                                            <span class="text-xs text-slate-400 block">{{ $prof['school_name'] }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg">{{ $prof['points'] }} pts</span>
                                        @if(!empty($prof['whatsapp']))
                                            @php
                                                $phone = preg_replace('/\D/','', $prof['whatsapp']);
                                            @endphp
                                            <a href="https://wa.me/{{ $phone }}" target="_blank" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition flex items-center justify-center font-bold" title="Parabenizar no WhatsApp">
                                                💬
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6 text-slate-400 text-xs">
                                    Nenhum envio registrado para pontuação ainda.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            <!-- Alterar Senha -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 max-w-lg">
                <h4 class="font-bold text-slate-900 text-sm mb-3">Alterar Sua Senha</h4>
                <form action="{{ route('school.password.change') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nova Senha</label>
                        <input type="password" name="password" required class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="px-4 py-2.5 bg-slate-950 hover:bg-slate-900 text-white font-bold rounded-xl text-xs shadow-sm transition">
                        Salvar Nova Senha
                    </button>
                </form>
            </div>
        @elseif($tab === 'uploads')
            <!-- ================= TAB: ENVIOS RECENTES ================= -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Envios Recentes</h3>
                        <p class="text-xs text-slate-500">Lista completa dos planejamentos enviados pelos professores da sua escola.</p>
                    </div>
                    <a href="{{ route('school.dashboard') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-850 flex items-center gap-1">
                        ← Voltar ao Início
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-50/10">
                                <th class="px-6 py-4">Professor</th>
                                <th class="px-6 py-4">Planejamento</th>
                                <th class="px-6 py-4">Data do Envio</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @forelse($allUploads as $doc)
                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $doc->user->name ?? 'N/A' }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium">{{ $doc->user->schoolClass->name ?? 'Sem Turma' }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-900">
                                        {{ $doc->period->name ?? 'Sem Período' }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 font-medium">
                                        {{ $doc->submitted_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badgeClass = match($doc->status) {
                                                'aprovado' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                'rejeitado' => 'bg-rose-50 text-rose-700 border-rose-100',
                                                'ajustado' => 'bg-amber-50 text-amber-700 border-amber-100',
                                                'atrasado' => 'bg-orange-50 text-orange-700 border-orange-100',
                                                default => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize {{ $badgeClass }}">
                                            {{ $doc->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('school.planning.view', ['id' => $doc->period_id]) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-750 text-white font-bold rounded-lg text-xs transition">
                                            Visualizar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">
                                        Nenhum envio recebido até o momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($tab === 'coordinators' && $user->role === 'director')
            <!-- ================= TAB: GESTÃO DE COORDENADORES ================= -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cadastro Coordenador -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 h-fit">
                    <h3 class="text-base font-bold text-slate-900 mb-6">Cadastrar Coordenador</h3>
                    
                    <form action="{{ route('school.coordinator.store') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <input type="hidden" name="school_id" value="{{ $school->id ?? '' }}">

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600">Nome Completo</label>
                            <input type="text" name="name" required placeholder="Ex: Milza Souza" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600">E-mail de Acesso</label>
                            <input type="email" name="email" required placeholder="Ex: milza@sgp.com" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600">WhatsApp</label>
                            <input type="text" name="whatsapp" placeholder="Ex: 5595999999999" class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <button type="submit" class="w-full mt-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow transition duration-150 cursor-pointer">
                            Salvar Coordenador
                        </button>
                    </form>
                </div>

                <!-- Lista de Coordenadores -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 lg:col-span-2 flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-base font-bold text-slate-900">Coordenadores Cadastrados</h3>
                        <a href="{{ route('school.dashboard') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                            ← Voltar ao Início
                        </a>
                    </div>
                    
                    <div class="divide-y divide-slate-100 flex-1">
                        @forelse($coordinators as $coord)
                            <div class="py-4 first:pt-0 last:pb-0 flex justify-between items-center text-sm">
                                <div class="space-y-0.5">
                                    <span class="font-bold text-slate-900 block">{{ $coord->name }}</span>
                                    <span class="text-xs text-slate-500 block font-medium">{{ $coord->email }}</span>
                                    @if($coord->whatsapp)
                                        <span class="text-[10px] text-slate-400 block font-medium">💬 WhatsApp: {{ $coord->whatsapp }}</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    <form action="{{ route('school.coordinator.reset-password') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $coord->id }}">
                                        <button type="submit" class="px-2.5 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-lg transition cursor-pointer" title="Redefinir senha para padrão: coord123">
                                            Redefinir Senha
                                        </button>
                                    </form>

                                    <a href="{{ route('school.coordinator.edit') }}?id={{ $coord->id }}" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-md transition" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>

                                    <form action="{{ route('school.coordinator.delete') }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este coordenador?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="id" value="{{ $coord->id }}">
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md transition cursor-pointer" title="Excluir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-400 text-xs font-semibold">
                                Nenhum coordenador cadastrado.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
