<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Segurança da Conta') }}
        </h2>
    </x-slot>

    <div class="max-w-lg mx-auto space-y-6">

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium rounded-xl px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <h3 class="font-bold text-slate-900 text-sm mb-1">Alterar Senha da Secretaria (SEMED)</h3>
            <p class="text-xs text-slate-500 mb-5">
                Defina uma senha forte e exclusiva para esta conta de acesso à rede municipal.
            </p>

            <form action="{{ route('semed.security.password') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Senha Atual</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                        class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    @error('current_password')
                        <p class="text-rose-600 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nova Senha</label>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password"
                        class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    @error('password')
                        <p class="text-rose-600 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Confirmar Nova Senha</label>
                    <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                        class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>

                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs shadow-sm transition">
                    Salvar Nova Senha
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
