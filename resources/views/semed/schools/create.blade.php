<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Cadastrar Escola') }}
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <a href="{{ route('semed.schools') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-700 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar para lista de escolas
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

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-base font-bold text-slate-900">Dados da Escola</h3>
                <p class="text-xs text-slate-500">As turmas padrão (1º ao 5º ano, A a F) são criadas automaticamente ao cadastrar a escola.</p>
            </div>

            <form action="{{ route('semed.schools.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Nome da Escola</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Ex: Escola Municipal João da Silva" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 text-sm py-2.5 transition @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="inep_code" class="block text-sm font-semibold text-slate-700 mb-2">Código INEP (opcional)</label>
                        <input type="text" name="inep_code" id="inep_code" value="{{ old('inep_code') }}" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 text-sm py-2.5 transition @error('inep_code') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('inep_code')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-semibold text-slate-700 mb-2">Endereço (opcional)</label>
                        <input type="text" name="address" id="address" value="{{ old('address') }}" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 text-sm py-2.5 transition @error('address') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('address')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="director_name" class="block text-sm font-semibold text-slate-700 mb-2">Diretor(a) - nome de exibição (opcional)</label>
                        <input type="text" name="director_name" id="director_name" value="{{ old('director_name') }}" placeholder="Pode ser preenchido depois ao cadastrar o diretor" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 text-sm py-2.5 transition @error('director_name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('director_name')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="director_phone" class="block text-sm font-semibold text-slate-700 mb-2">Telefone do(a) Diretor(a) (opcional)</label>
                        <input type="text" name="director_phone" id="director_phone" value="{{ old('director_phone') }}" placeholder="5595999999999" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 text-sm py-2.5 transition @error('director_phone') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('director_phone')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/20 -mx-6 -mb-6 p-6">
                    <a href="{{ route('semed.schools') }}" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold rounded-lg text-sm transition duration-150">
                        Cancelar
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm shadow-md shadow-indigo-600/10 transition duration-150">
                        Salvar Escola
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
