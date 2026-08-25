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

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm flex items-start gap-3 shadow-sm">
                <svg class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('error') }}</span>
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

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Material de referência da rede</h3>
                <p class="text-sm text-slate-500 mt-1">
                    Modelo de requisitos de planejamento, portaria, rubrica própria, recorte de currículo. O texto entra em
                    <strong>toda análise</strong> deste município, em seção separada, e a IANNE passa a cobrar essas exigências
                    citando o documento pelo título.
                </p>
            </div>

            <div class="px-6 py-5 border-b border-slate-100">
                <form method="POST" action="{{ route('superadmin.tenants.ianne.files.store', $tenant) }}"
                      enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Título (opcional)</label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="Ex: Modelo de requisitos 2026"
                               class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <p class="text-[11px] text-slate-500">Em branco, usa o nome do arquivo. É por este título que a IANNE cita a exigência.</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-600">Arquivo <span class="text-rose-500">*</span></label>
                        <input type="file" name="file" required accept=".docx,.pdf,.txt"
                               class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 cursor-pointer">
                        <p class="text-[11px] text-slate-500">.docx, .pdf ou .txt — até 10MB.</p>
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow transition duration-150 cursor-pointer">
                            Enviar arquivo
                        </button>
                    </div>
                </form>

                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                    <p class="text-xs text-amber-900 leading-relaxed">
                        <strong>Prefira .docx.</strong> Word e texto são lidos direto no servidor, sem limite de tamanho.
                        PDF é lido pela própria IA, cuja resposta é limitada — um PDF longo pode ser cortado no meio sem aviso.
                        PDF digitalizado (foto do papel) não é lido de jeito nenhum. O DCRR completo, de 586 páginas, não cabe
                        aqui: ele já está no sistema, fatiado por componente e ano.
                    </p>
                </div>
            </div>

            <div class="px-6 py-5">
                @forelse($arquivos as $arquivo)
                    <div class="flex items-start justify-between gap-4 py-3 {{ !$loop->first ? 'border-t border-slate-100' : '' }}">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-bold text-slate-900">{{ $arquivo->title }}</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 rounded-full px-2 py-0.5">{{ $arquivo->extension }}</span>
                                @if($arquivo->is_active)
                                    <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 rounded-full px-2 py-0.5">Em uso</span>
                                @else
                                    <span class="text-[10px] font-bold uppercase tracking-wider bg-slate-200 text-slate-600 rounded-full px-2 py-0.5">Inativo</span>
                                @endif
                            </div>

                            <p class="text-xs text-slate-500 mt-1">
                                {{ $arquivo->original_name }} &middot;
                                @if($arquivo->extraction_ok)
                                    {{ number_format($arquivo->chars, 0, ',', '.') }} caracteres extraídos
                                @else
                                    <span class="text-rose-600 font-semibold">sem texto extraído — não entra nas análises</span>
                                @endif
                                &middot; {{ $arquivo->created_at?->format('d/m/Y') }}
                            </p>

                            @if($arquivo->provavelmenteTruncado())
                                <p class="text-xs text-amber-700 mt-1">PDF longo: a extração pode ter sido cortada. Confira ou reenvie em .docx.</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            @if($arquivo->extraction_ok)
                                <form method="POST" action="{{ route('superadmin.tenants.ianne.files.toggle', [$tenant, $arquivo]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs font-bold text-slate-500 hover:text-slate-800 cursor-pointer">
                                        {{ $arquivo->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('superadmin.tenants.ianne.files.destroy', [$tenant, $arquivo]) }}"
                                  onsubmit="return confirm('Excluir este material? A IANNE deixa de considerá-lo nas próximas análises.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800 cursor-pointer">Excluir</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">
                        Nenhum material enviado. A IANNE analisa usando o DCRR fatiado que já vem no sistema.
                    </p>
                @endforelse
            </div>
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
