<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Editar Contato: ' . $user->name) }}
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
                <h3 class="text-base font-bold text-slate-900">Dados de Contato</h3>
                <p class="text-xs text-slate-500">Município vinculado: <strong>{{ $user->tenant->name ?? '—' }}</strong> (não pode ser alterado aqui).</p>
            </div>

            <form action="{{ route('superadmin.seducs.update', $user) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Nome da Secretaria / Responsável</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full rounded-lg border-slate-200 focus:border-emerald-500 focus:ring focus:ring-emerald-500/20 text-sm py-2.5 transition @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">E-mail de Acesso</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full rounded-lg border-slate-200 focus:border-emerald-500 focus:ring focus:ring-emerald-500/20 text-sm py-2.5 transition @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                        @error('email')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- WhatsApp -->
                    <div>
                        <label for="whatsapp" class="block text-sm font-semibold text-slate-700 mb-2">WhatsApp (opcional)</label>
                        <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="5595999999999" class="w-full rounded-lg border-slate-200 focus:border-emerald-500 focus:ring focus:ring-emerald-500/20 text-sm py-2.5 transition @error('whatsapp') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('whatsapp')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Footer do Form -->
                <div class="pt-6 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/20 -mx-6 -mb-6 p-6">
                    <a href="{{ route('superadmin.seducs') }}" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold rounded-lg text-sm transition duration-150">
                        Cancelar
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg text-sm shadow-md shadow-emerald-600/10 transition duration-150">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Reset de Senha -->
        <div class="bg-white border border-amber-200 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-900 mb-1">Redefinir Senha de Acesso</h3>
            <p class="text-xs text-slate-500 mb-4">
                Gera uma nova senha temporária para esta secretaria, substituindo a senha atual imediatamente.
            </p>
            <form action="{{ route('superadmin.seducs.reset-password', $user) }}" method="POST" onsubmit="return confirm('Redefinir a senha de {{ $user->name }}? Uma nova senha temporária será gerada.');">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold rounded-xl text-xs border border-amber-200 transition">
                    Resetar Senha
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
