<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            Prompts da IANNE — {{ $tenant->name }}
        </h2>
    </x-slot>

    @php
        // Montado aqui fora do @js para não cair no ParseError de closure
        // multilinha dentro da diretiva.
        $padroesJs = $padroes;
        $grupos = \App\Services\PromptSettings::GROUPS;
    @endphp

    <div class="space-y-8 max-w-4xl">

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm shadow-sm">
                <p class="font-semibold mb-1">Não foi possível salvar:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-3">
            <p class="text-sm text-slate-600">
                Aqui você ajusta o comportamento da IANNE <strong>só para {{ $tenant->name }}</strong>, sem precisar de deploy.
                As mudanças valem a partir da próxima análise — pareceres já gerados não são refeitos.
            </p>
            <p class="text-sm text-slate-600">
                <strong>Campo em branco volta ao padrão.</strong> Não há como deixar um buraco no meio do prompt: se você apagar
                um bloco, o texto original entra no lugar.
            </p>
        </div>

        <form method="POST" action="{{ route('superadmin.tenants.ianne.update', $tenant) }}" class="space-y-8"
              x-data="{ padroes: @js($padroesJs) }">
            @csrf
            @method('PUT')

            @foreach($grupos as $chaveGrupo => $grupo)
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-base font-bold text-slate-900">{{ $grupo['titulo'] }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ $grupo['descricao'] }}</p>
                    </div>

                    <div class="px-6 py-5 space-y-6">
                        @foreach(\App\Services\PromptSettings::blocksOfGroup($chaveGrupo) as $chave => $bloco)
                            @php
                                $valor = old("blocks.{$chave}", $blocos[$chave]);
                                $ehPadrao = trim($valor) === trim($padroes[$chave]);
                                $linhas = max(3, min(14, substr_count($valor, "\n") + 2));
                            @endphp

                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <label for="{{ $chave }}" class="block text-sm font-semibold text-slate-700">
                                            {{ $bloco['label'] }}
                                        </label>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $bloco['ajuda'] }}</p>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0 pt-0.5">
                                        @if($ehPadrao)
                                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Padrão</span>
                                        @else
                                            <span class="text-[11px] font-semibold text-amber-600 uppercase tracking-wide">Personalizado</span>
                                        @endif
                                        <button type="button"
                                                @click="$refs.{{ $chave }}.value = padroes['{{ $chave }}']"
                                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800 cursor-pointer">
                                            Restaurar padrão
                                        </button>
                                    </div>
                                </div>

                                <textarea name="blocks[{{ $chave }}]" id="{{ $chave }}" x-ref="{{ $chave }}"
                                          rows="{{ $linhas }}"
                                          class="w-full text-sm font-mono leading-relaxed border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ $valor }}</textarea>
                            </div>
                        @endforeach
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                        <p class="text-xs text-slate-500 leading-relaxed">
                            <span class="font-semibold text-slate-600">O que não muda:</span>
                            {{ $grupo['fixo'] }}
                        </p>
                    </div>
                </div>
            @endforeach

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('superadmin.tenants') }}"
                   class="px-4 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold rounded-xl text-sm transition">
                    Voltar
                </a>
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow transition duration-150 cursor-pointer">
                    Salvar prompts
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
