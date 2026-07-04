<x-app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold text-slate-900">Nova Venda</h1></x-slot>

    <div class="max-w-2xl" x-data="{ plan: '{{ old('plan', 'coordenador') }}', billing: '{{ old('billing_type', 'mensal') }}' }">
        @if($errors->any())
            <div class="mb-4 p-4 bg-rose-100 text-rose-800 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.sale.store') }}" class="bg-slate-900/60 border border-slate-800 rounded-xl p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Nome do cliente (tenant)</label>
                <input type="text" name="tenant_name" value="{{ old('tenant_name') }}" required
                       class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100" placeholder="Ex: Coord. Maria — Escola Alfa / Prefeitura de ...">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Plano</label>
                    <select name="plan" x-model="plan" class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        <option value="coordenador">Coordenador (individual)</option>
                        <option value="escola">Escola</option>
                        <option value="semed">SEMED (rede)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Cobrança</label>
                    <select name="billing_type" x-model="billing" class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        <option value="mensal">Mensal</option>
                        <option value="vitalicio">Vitalícia</option>
                    </select>
                </div>
            </div>

            <div x-show="billing === 'mensal'">
                <label class="block text-sm font-medium text-slate-300 mb-1">Vencimento</label>
                <input type="date" name="expires_at" value="{{ old('expires_at', now()->addDays(30)->format('Y-m-d')) }}"
                       class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
            </div>

            <template x-if="plan !== 'semed'">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Nome da escola</label>
                        <input type="text" name="school_name" value="{{ old('school_name') }}"
                               class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="create_default_classes" value="1" checked class="rounded bg-slate-800 border-slate-700">
                        Criar turmas padrão (1º–5º Ano, A–F)
                    </label>
                </div>
            </template>

            <template x-if="plan === 'coordenador' || plan === 'escola'">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1" x-text="plan === 'coordenador' ? 'Nome do coordenador' : '1º coordenador (opcional)'"></label>
                        <input type="text" name="coordinator_name" value="{{ old('coordinator_name') }}"
                               class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">E-mail do coordenador</label>
                        <input type="email" name="coordinator_email" value="{{ old('coordinator_email') }}"
                               class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                    </div>
                </div>
            </template>

            <template x-if="plan === 'escola'">
                <div class="space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Nome do diretor</label>
                            <input type="text" name="director_name" value="{{ old('director_name') }}"
                                   class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">E-mail do diretor</label>
                            <input type="email" name="director_email" value="{{ old('director_email') }}"
                                   class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Vice-diretor (opcional)</label>
                            <input type="text" name="vice_name" value="{{ old('vice_name') }}"
                                   class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">E-mail do vice</label>
                            <input type="email" name="vice_email" value="{{ old('vice_email') }}"
                                   class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="plan === 'semed'">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Nome do usuário SEMED</label>
                        <input type="text" name="semed_name" value="{{ old('semed_name') }}"
                               class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">E-mail SEMED</label>
                        <input type="email" name="semed_email" value="{{ old('semed_email') }}"
                               class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                    </div>
                </div>
            </template>

            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg transition">
                Ativar venda
            </button>
        </form>
    </div>
</x-app-layout>
