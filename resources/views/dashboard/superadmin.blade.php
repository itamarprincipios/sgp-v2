<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Painel SaaS - Visão Global') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Banner de Boas Vindas -->
        <div class="relative bg-slate-900 rounded-2xl overflow-hidden shadow-xl p-8 text-white border border-slate-850 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="absolute inset-0 bg-gradient-to-r from-violet-600/20 via-indigo-600/10 to-transparent pointer-events-none"></div>
            <div class="relative z-10 space-y-2 text-center md:text-left">
                <h3 class="text-2xl font-bold tracking-tight">Olá, Super Administrador!</h3>
                <p class="text-slate-400 text-sm max-w-xl">
                    Bem-vindo ao painel central do SGP. Aqui você pode gerenciar os municípios parceiros, acompanhar o limite de escolas contratadas, monitorar o uso da Inteligência Artificial IANNE e gerenciar o status das assinaturas SaaS.
                </p>
            </div>
            <div class="relative z-10 flex gap-3 flex-shrink-0">
                <a href="{{ route('superadmin.tenants.create') }}" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-750 text-white font-semibold rounded-xl text-sm shadow-lg shadow-violet-600/20 transition duration-150">
                    + Novo Município
                </a>
                <a href="{{ route('superadmin.tenants') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold rounded-xl text-sm transition duration-150">
                    Gerenciar Municípios
                </a>
            </div>
        </div>

        <!-- Estatísticas SaaS Globais -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card Inquilinos (Municípios) -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 hover:shadow-lg hover:shadow-violet-500/5 transition duration-200 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Municípios (Inquilinos)</p>
                        <h3 class="text-3xl font-bold text-slate-100 mt-2 group-hover:text-violet-400 transition duration-150">{{ $stats['tenants_count'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center border border-violet-500/20 group-hover:bg-violet-600 group-hover:text-white transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card Escolas -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 hover:shadow-lg hover:shadow-indigo-500/5 transition duration-200 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Escolas Totais</p>
                        <h3 class="text-3xl font-bold text-slate-100 mt-2 group-hover:text-indigo-400 transition duration-150">{{ $stats['schools_count'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20 group-hover:bg-indigo-600 group-hover:text-white transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card Usuários -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 hover:shadow-lg hover:shadow-emerald-500/5 transition duration-200 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Usuários Ativos</p>
                        <h3 class="text-3xl font-bold text-slate-100 mt-2 group-hover:text-emerald-400 transition duration-150">{{ $stats['users_count'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20 group-hover:bg-emerald-600 group-hover:text-white transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card Queries de IA -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 hover:shadow-lg hover:shadow-amber-500/5 transition duration-200 group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Análises de IA</p>
                        <h3 class="text-3xl font-bold text-slate-100 mt-2 group-hover:text-amber-400 transition duration-150">{{ $stats['ai_queries_count'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20 group-hover:bg-amber-600 group-hover:text-white transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364.364l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Tabela Municípios Recentes -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden lg:col-span-2 flex flex-col justify-between">
                <div>
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Municípios Recentes</h3>
                            <p class="text-xs text-slate-500 mt-1">Visão rápida das últimas prefeituras inseridas e status do contrato.</p>
                        </div>
                        <a href="{{ route('superadmin.tenants') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition duration-150">
                            Ver todos
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50/30">
                                    <th class="px-6 py-4">Município</th>
                                    <th class="px-6 py-4">Identificador (Slug)</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">IA IANNE</th>
                                    <th class="px-6 py-4">Expiração</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                @forelse($tenants as $tenant)
                                    <tr class="hover:bg-slate-50/40 transition duration-100">
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-slate-900">{{ $tenant->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $tenant->slug }}</td>
                                        <td class="px-6 py-4 text-center">
                                            @if($tenant->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    Ativo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 border border-rose-200">
                                                    Suspenso
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($tenant->ai_enabled)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800 border border-violet-200">
                                                    Habilitada
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                                                    Desabilitada
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 font-medium">
                                            @if($tenant->expires_at)
                                                {{ $tenant->expires_at->format('d/m/Y') }}
                                            @else
                                                <span class="text-slate-400">Vitalício / Sem prazo</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                            Nenhum município cadastrado no momento.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Painel de Ações Rápidas & Configuração SaaS -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Ações Rápidas SaaS</h3>
                    <p class="text-xs text-slate-500 mt-1 mb-6">Controle geral da sua infraestrutura multi-tenant.</p>
                    
                    <div class="space-y-4">
                        <a href="{{ route('superadmin.tenants.create') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 hover:border-violet-100 hover:bg-violet-50/30 transition duration-150 group">
                            <div class="w-10 h-10 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center font-bold group-hover:bg-violet-600 group-hover:text-white transition duration-150">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900 group-hover:text-violet-700 transition duration-150">Cadastrar Novo Inquilino</h4>
                                <p class="text-xs text-slate-400">Criar uma prefeitura/SEMED no sistema.</p>
                            </div>
                        </a>

                        <a href="{{ route('superadmin.tenants') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/30 transition duration-150 group">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold group-hover:bg-indigo-600 group-hover:text-white transition duration-150">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900 group-hover:text-indigo-700 transition duration-150">Limites & Recursos</h4>
                                <p class="text-xs text-slate-400">Modificar limites de escolas e uso da IA.</p>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-xs text-slate-500">
                    <span class="font-bold text-slate-700 block mb-1">Nota do Desenvolvedor</span>
                    Esta plataforma está utilizando roteamento multi-inquilino a nível de aplicação com banco de dados compartilhado (Single Database Multi-Tenant). Todas as tabelas principais são filtradas automaticamente por `tenant_id`.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
