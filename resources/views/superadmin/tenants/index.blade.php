<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Municípios Parceiros (SaaS)') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Barra de Ações e Busca -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
            <div>
                <h3 class="text-base font-bold text-slate-900">Gerenciamento de Inquilinos</h3>
                <p class="text-xs text-slate-500">Adicione, edite ou gerencie os limites e licenças de municípios integrados ao SGP.</p>
            </div>
            <a href="{{ route('superadmin.tenants.create') }}" class="px-4 py-2 bg-violet-600 hover:bg-violet-750 text-white text-xs font-semibold rounded-lg shadow-md shadow-violet-600/10 transition duration-150 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Cadastrar Município
            </a>
        </div>

        <!-- Alertas de Sucesso -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Tabela Completa de Tenants -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                            <th class="px-6 py-4">Nome do Município</th>
                            <th class="px-6 py-4">Identificador</th>
                            <th class="px-6 py-4 text-center">Escolas Contratadas</th>
                            <th class="px-6 py-4 text-center">Uso de IA</th>
                            <th class="px-6 py-4 text-center">Contrato</th>
                            <th class="px-6 py-4">Expiração</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($tenants as $tenant)
                            <tr class="hover:bg-slate-50/40 transition duration-100">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">{{ $tenant->name }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5 font-medium">
                                        {{ $tenant->users_count }} usuários cadastrados
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $tenant->slug }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="font-semibold text-slate-800">
                                            {{ $tenant->schools_count }} / {{ $tenant->max_schools_limit }}
                                        </span>
                                        <div class="w-20 bg-slate-100 h-1.5 rounded-full overflow-hidden mt-1">
                                            @php
                                                $percentage = min(100, ($tenant->schools_count / max(1, $tenant->max_schools_limit)) * 100);
                                                $color = $percentage >= 90 ? 'bg-rose-500' : ($percentage >= 70 ? 'bg-amber-500' : 'bg-violet-600');
                                            @endphp
                                            <div class="h-full {{ $color }} rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
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
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('superadmin.tenants.toggle', $tenant) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition cursor-pointer {{ $tenant->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 border border-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200 border border-rose-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $tenant->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                            {{ $tenant->is_active ? 'Ativo' : 'Suspenso' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-medium">
                                    @if($tenant->expires_at)
                                        {{ $tenant->expires_at->format('d/m/Y') }}
                                    @else
                                        <span class="text-slate-400">Vitalício / Sem prazo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition duration-150">
                                        <svg class="w-3.5 h-3.5 text-slate-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">
                                    Nenhum município cadastrado até o momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            @if($tenants->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $tenants->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
