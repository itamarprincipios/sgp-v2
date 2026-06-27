<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Editar Município: ' . $tenant->name) }}
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Botão Voltar -->
        <div>
            <a href="{{ route('superadmin.tenants') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-700 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar para lista
            </a>
        </div>

        <!-- Formulário -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-900">Editar Configurações do Inquilino</h3>
                <p class="text-xs text-slate-500 font-medium">Modifique limites de escolas, prazos de expiração ou permissões da IA IANNE.</p>
            </div>

            <form action="{{ route('superadmin.tenants.update', $tenant) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome do Município -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Nome do Município / Secretaria</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $tenant->name) }}" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 transition @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-semibold text-slate-700 mb-2">Identificador único (Slug)</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $tenant->slug) }}" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 font-mono transition @error('slug') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                        
                        <div class="p-3 bg-amber-50 border border-amber-100 rounded-lg text-amber-800 text-xs mt-2 flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>
                                <strong>Atenção:</strong> Alterar o slug de um inquilino ativo pode quebrar links, subdomínios ou rotas associadas a ele. Proceda com cuidado.
                            </span>
                        </div>
                        @error('slug')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Limite de Escolas -->
                    <div>
                        <label for="max_schools_limit" class="block text-sm font-semibold text-slate-700 mb-2">Limite Máximo de Escolas</label>
                        <input type="number" name="max_schools_limit" id="max_schools_limit" value="{{ old('max_schools_limit', $tenant->max_schools_limit) }}" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 transition @error('max_schools_limit') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" min="1" required>
                        <p class="text-[11px] text-slate-400 mt-1.5">Número máximo de escolas permitidas para cadastro sob este inquilino.</p>
                        @error('max_schools_limit')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Expira em -->
                    <div>
                        <label for="expires_at" class="block text-sm font-semibold text-slate-700 mb-2">Data de Expiração do Contrato</label>
                        <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at', $tenant->expires_at ? $tenant->expires_at->format('Y-m-d') : '') }}" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 transition @error('expires_at') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        <p class="text-[11px] text-slate-400 mt-1.5">Deixe em branco para contratos permanentes/sem expiração.</p>
                        @error('expires_at')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Toggles / Checkboxes -->
                <div class="pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- IA IANNE Ativa -->
                    <div class="flex items-start gap-3">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="ai_enabled" id="ai_enabled" value="1" {{ old('ai_enabled', $tenant->ai_enabled) ? 'checked' : '' }} class="rounded border-slate-350 text-violet-600 focus:ring-violet-500/20 w-4 h-4 transition">
                        </div>
                        <div>
                            <label for="ai_enabled" class="text-sm font-semibold text-slate-800">Habilitar Inteligência Artificial (IANNE)</label>
                            <p class="text-xs text-slate-400 mt-0.5">Permite que diretores, coordenadores e professores do município realizem análises inteligentes com IA nos planejamentos.</p>
                        </div>
                    </div>

                    <!-- Município Ativo -->
                    <div class="flex items-start gap-3">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $tenant->is_active) ? 'checked' : '' }} class="rounded border-slate-350 text-violet-600 focus:ring-violet-500/20 w-4 h-4 transition">
                        </div>
                        <div>
                            <label for="is_active" class="text-sm font-semibold text-slate-800">Inquilino Ativo (Contrato Ativo)</label>
                            <p class="text-xs text-slate-400 mt-0.5">Se desativado, o acesso ao painel de todas as escolas e usuários desse município será temporariamente bloqueado.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer do Form -->
                <div class="pt-6 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/20 -mx-6 -mb-6 p-6">
                    <a href="{{ route('superadmin.tenants') }}" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold rounded-lg text-sm transition duration-150">
                        Cancelar
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-750 text-white font-semibold rounded-lg text-sm shadow-md shadow-violet-600/10 transition duration-150">
                        Atualizar Município
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
