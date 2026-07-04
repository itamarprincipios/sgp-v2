<x-app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold text-slate-900">Venda ativada ✅</h1></x-slot>

    <div class="max-w-2xl space-y-4">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 text-sm font-medium">
            Cliente <strong>{{ $tenant->name }}</strong> (plano {{ $tenant->plan }}, {{ $tenant->billing_type }}) criado.
            {{ $tenant->expires_at ? 'Vence em ' . $tenant->expires_at->format('d/m/Y') . '.' : 'Sem vencimento (vitalício).' }}
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-6" x-data>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-slate-100 font-semibold">Credenciais de acesso</h2>
                <button @click="navigator.clipboard.writeText($refs.creds.innerText).then(() => $el.innerText = 'Copiado!')"
                        class="text-xs font-bold px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg">Copiar tudo</button>
            </div>
            <p class="text-xs text-amber-400 mb-3">⚠️ Esta tela é exibida uma única vez. Copie e envie ao cliente agora.</p>
            <div x-ref="creds" class="space-y-3 text-sm font-mono text-slate-200">
                <div>Acesso: {{ config('app.url') }}/login</div>
                @foreach($credentials as $cred)
                    <div class="border-t border-slate-800 pt-3">
                        <div>{{ ucfirst($cred['role']) }} — {{ $cred['name'] }}</div>
                        <div>E-mail: {{ $cred['email'] }}</div>
                        <div>Senha temporária: {{ $cred['password'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <a href="{{ route('superadmin.tenants') }}" class="inline-block text-sm text-indigo-600 hover:text-indigo-500 font-semibold">← Voltar aos clientes</a>
    </div>
</x-app-layout>
