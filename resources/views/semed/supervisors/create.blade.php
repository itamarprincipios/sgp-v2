<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Cadastrar Supervisor(a)') }}
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <a href="{{ route('semed.supervisors') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-700 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar para lista de supervisores
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
                <h3 class="text-base font-bold text-slate-900">Dados do Supervisor(a)</h3>
                <p class="text-xs text-slate-500">O(a) supervisor(a) terá acesso apenas às documentações, relatórios e planejamentos das escolas marcadas abaixo.</p>
            </div>

            <form action="{{ route('semed.supervisors.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="role" class="block text-sm font-semibold text-slate-700 mb-2">Tipo de Supervisor(a)</label>
                        <select name="role" id="role" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 text-sm py-2.5 transition @error('role') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                            <option value="">Selecione o tipo...</option>
                            @foreach($supervisorTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Escolas Vinculadas</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-4 border border-slate-200 rounded-lg max-h-56 overflow-y-auto @error('school_ids') border-rose-500 @enderror">
                            @foreach($schools as $s)
                                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="school_ids[]" value="{{ $s->id }}" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500" {{ in_array($s->id, old('school_ids', [])) ? 'checked' : '' }}>
                                    {{ $s->name }}
                                </label>
                            @endforeach
                        </div>
                        @error('school_ids')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                        @error('school_ids.*')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">Uma ou mais escolas selecionadas são inválidas.</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Nome Completo</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Ex: Maria Souza" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 text-sm py-2.5 transition @error('name') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">E-mail (Login)</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 text-sm py-2.5 transition @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror" required>
                        @error('email')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="whatsapp" class="block text-sm font-semibold text-slate-700 mb-2">WhatsApp (opcional)</label>
                        <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp') }}" placeholder="5595999999999" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 text-sm py-2.5 transition @error('whatsapp') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('whatsapp')
                            <p class="text-xs text-rose-600 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-lg text-indigo-800 text-xs flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>
                        Uma senha temporária será gerada automaticamente e exibida apenas uma vez após o cadastro. Oriente o(a) supervisor(a) a trocá-la no primeiro acesso.
                    </span>
                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/20 -mx-6 -mb-6 p-6">
                    <a href="{{ route('semed.supervisors') }}" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold rounded-lg text-sm transition duration-150">
                        Cancelar
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm shadow-md shadow-indigo-600/10 transition duration-150">
                        Salvar Supervisor(a)
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
