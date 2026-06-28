<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Cadastrar Município (Inquilino)') }}
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
                <h3 class="text-base font-bold text-slate-900">Configurações do Novo Inquilino</h3>
                <p class="text-xs text-slate-500">Defina o nome, slug, expiração e ativação de recursos da inteligência artificial.</p>
            </div>

            <form action="{{ route('superadmin.tenants.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome do Município -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Nome do Município / Secretaria</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Ex: Prefeitura de Boa Vista" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 transition @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-semibold text-slate-700 mb-2">Identificador único (Slug)</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="ex: boavista" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 font-mono transition @error('slug') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                        <p class="text-[11px] text-slate-400 mt-1.5">Usado para subdomínios ou isolamento de rotas. Apenas letras minúsculas, números e traços.</p>
                        @error('slug')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Expira em -->
                    <div>
                        <label for="expires_at" class="block text-sm font-semibold text-slate-700 mb-2">Data de Expiração do Contrato</label>
                        <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 transition @error('expires_at') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        <p class="text-[11px] text-slate-400 mt-1.5">Deixe em branco para contratos permanentes/sem expiração.</p>
                        @error('expires_at')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-lg text-indigo-800 text-xs flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>
                        O limite de escolas e o primeiro acesso da Seduc (SEMED/Deaps) são definidos depois, em <strong>Cadastrar Seduc</strong>.
                    </span>
                </div>

                <!-- Toggles / Checkboxes -->
                <div class="pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- IA IANNE Ativa -->
                    <div class="flex items-start gap-3">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="ai_enabled" id="ai_enabled" value="1" {{ old('ai_enabled', true) ? 'checked' : '' }} class="rounded border-slate-350 text-violet-600 focus:ring-violet-500/20 w-4 h-4 transition">
                        </div>
                        <div>
                            <label for="ai_enabled" class="text-sm font-semibold text-slate-800">Habilitar Inteligência Artificial (IANNE)</label>
                            <p class="text-xs text-slate-400 mt-0.5">Permite que diretores, coordenadores e professores do município realizem análises inteligentes com IA nos planejamentos.</p>
                        </div>
                    </div>

                    <!-- Município Ativo -->
                    <div class="flex items-start gap-3">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-350 text-violet-600 focus:ring-violet-500/20 w-4 h-4 transition">
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
                        Salvar Município
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script para Auto-Slug -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');

            nameInput.addEventListener('input', function () {
                // Generate slug only if user hasn't manually edited it significantly yet
                const name = nameInput.value;
                const slug = name
                    .toLowerCase()
                    .normalize('NFD') // Remove accents
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/prefeitura de /gi, '')
                    .replace(/prefeitura municipal de /gi, '')
                    .replace(/semed /gi, '')
                    .replace(/secretaria de educacao de /gi, '')
                    .replace(/[^a-z0-9\s-]/g, '') // remove special characters
                    .replace(/\s+/g, '-') // replace spaces with hyphens
                    .replace(/-+/g, '-') // collapse multiple hyphens
                    .trim()
                    .replace(/^-+|-+$/g, ''); // trim hyphens

                slugInput.value = slug;
            });
        });
    </script>
</x-app-layout>
