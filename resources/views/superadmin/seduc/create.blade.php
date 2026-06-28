<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Cadastrar Seduc (Secretaria Municipal de Educação)') }}
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Botão Voltar -->
        <div>
            <a href="{{ route('superadmin.seducs') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-700 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar para lista de secretarias
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl px-4 py-3">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulário -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-900">Dados da Secretaria de Educação</h3>
                <p class="text-xs text-slate-500">Cadastre o acesso da SEMED/Deaps (Departamento de Assuntos Pedagógicos) responsável pelo município e defina o limite de escolas contratadas.</p>
            </div>

            <form action="{{ route('superadmin.seduc.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Município -->
                    <div class="md:col-span-2">
                        <label for="tenant_id" class="block text-sm font-semibold text-slate-700 mb-2">Município (Inquilino)</label>
                        <select name="tenant_id" id="tenant_id" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 transition @error('tenant_id') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                            <option value="">Selecione o município...</option>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                        @error('tenant_id')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nome -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Nome da Secretaria / Responsável</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Ex: SEMED Rorainópolis / Deaps" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 transition @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">E-mail de Acesso</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 transition @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                        @error('email')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- WhatsApp -->
                    <div>
                        <label for="whatsapp" class="block text-sm font-semibold text-slate-700 mb-2">WhatsApp (opcional)</label>
                        <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp') }}" placeholder="5595999999999" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 transition @error('whatsapp') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('whatsapp')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Limite de Escolas -->
                    <div class="md:col-span-2">
                        <label for="max_schools_limit" class="block text-sm font-semibold text-slate-700 mb-2">Limite Máximo de Escolas</label>
                        <input type="number" name="max_schools_limit" id="max_schools_limit" value="{{ old('max_schools_limit', 10) }}" class="w-full rounded-lg border-slate-200 focus:border-violet-500 focus:ring focus:ring-violet-500/20 text-sm py-2.5 transition @error('max_schools_limit') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" min="1" required>
                        <p class="text-[11px] text-slate-400 mt-1.5">Número máximo de escolas permitidas para cadastro sob este município. Atualiza o limite do inquilino selecionado.</p>
                        @error('max_schools_limit')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-lg text-indigo-800 text-xs flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>
                        Uma senha temporária será gerada automaticamente e exibida apenas uma vez após o cadastro. Oriente a secretaria a trocá-la no primeiro acesso.
                    </span>
                </div>

                <!-- Footer do Form -->
                <div class="pt-6 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/20 -mx-6 -mb-6 p-6">
                    <a href="{{ route('superadmin.tenants') }}" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold rounded-lg text-sm transition duration-150">
                        Cancelar
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-750 text-white font-semibold rounded-lg text-sm shadow-md shadow-violet-600/10 transition duration-150">
                        Salvar Seduc
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
