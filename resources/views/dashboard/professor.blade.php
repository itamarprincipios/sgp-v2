<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Painel do Professor') }}
        </h2>
    </x-slot>

    <div class="space-y-8" x-data="{ showUploadModal: false, selectedPeriodId: null, selectedPeriodName: '' }">
        <!-- Alertas de Sucesso/Erro -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm space-y-1 shadow-sm">
                <div class="flex items-center gap-3 font-semibold text-rose-900">
                    <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Ocorreu um erro ao enviar o arquivo:</span>
                </div>
                <ul class="list-disc list-inside text-xs pl-8 font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Banner de Boas-Vindas -->
        <div class="relative bg-slate-900 rounded-2xl overflow-hidden shadow-xl p-6 md:p-8 text-white border border-slate-850 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="absolute inset-0 bg-gradient-to-r from-violet-600/20 via-indigo-600/10 to-transparent pointer-events-none"></div>
            <div class="relative z-10 space-y-2 text-center md:text-left">
                <span class="px-3 py-1 bg-violet-500/10 text-violet-300 rounded-full border border-violet-500/20 text-xs font-semibold tracking-wide capitalize">
                    Perfil: {{ $activeProfile }}
                </span>
                <h3 class="text-2xl font-bold tracking-tight mt-2">Olá, Professor {{ $user->name }}!</h3>
                <p class="text-slate-400 text-sm max-w-xl">
                    Seja bem-vindo ao seu painel. Acompanhe os períodos de planejamento abertos, envie seus planos de aula e confira sua pontuação e conquistas.
                </p>
            </div>
            
            <!-- Contexto Escola/Turma -->
            <div class="relative z-10 flex gap-6 text-center md:text-left">
                <div class="space-y-1">
                    <span class="text-xs text-slate-400 block font-medium">Escola</span>
                    <span class="text-sm font-bold text-slate-100 bg-slate-800/60 px-3 py-1.5 rounded-lg border border-slate-700/50 block">
                        {{ $schoolData->name ?? 'Não vinculada' }}
                    </span>
                </div>
                <div class="space-y-1">
                    <span class="text-xs text-slate-400 block font-medium">Turma / Componente</span>
                    <span class="text-sm font-bold text-slate-100 bg-slate-800/60 px-3 py-1.5 rounded-lg border border-slate-700/50 block">
                        {{ $className }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Coluna Principal (Períodos e Planejamentos) -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Períodos de Planejamento Ativos -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-base font-bold text-slate-900">Períodos de Planejamento Abertos</h3>
                        <p class="text-xs text-slate-500">Envie seus planos de aula dentro do prazo estabelecido.</p>
                    </div>

                    <div class="p-6 divide-y divide-slate-100">
                        @forelse($periods as $period)
                            <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm">{{ $period->name }}</h4>
                                    <p class="text-xs text-slate-500 mt-1">{{ $period->description }}</p>
                                    <div class="flex items-center gap-4 mt-2 text-xs text-slate-400 font-medium">
                                        <span class="flex items-center gap-1">
                                            📅 Início: {{ $period->start_date ? $period->start_date->format('d/m/Y') : '-' }}
                                        </span>
                                        <span class="flex items-center gap-1 text-rose-500 font-semibold">
                                            ⏰ Limite: {{ $period->deadline ? $period->deadline->format('d/m/Y H:i') : '-' }}
                                        </span>
                                    </div>
                                </div>
                                <button @click="showUploadModal = true; selectedPeriodId = {{ $period->id }}; selectedPeriodName = '{{ $period->name }}'" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow transition duration-150 flex items-center gap-1.5 self-stretch sm:self-auto justify-center cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                    Enviar Planejamento
                                </button>
                            </div>
                        @empty
                            <div class="text-center py-6 text-slate-400 text-sm font-medium">
                                Nenhum período de planejamento ativo no momento.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Histórico de Envios (Documentos) -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Seus Planejamentos Recentes</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Histórico de planos de aula submetidos para aprovação.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50/30">
                                    <th class="px-6 py-4">Título</th>
                                    <th class="px-6 py-4">Período</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Nota Final</th>
                                    <th class="px-6 py-4">Enviado em</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                @forelse($documents as $doc)
                                    <tr class="hover:bg-slate-50/40 transition duration-100">
                                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $doc->title }}</td>
                                        <td class="px-6 py-4 text-slate-500">{{ $doc->period->name ?? 'Global' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            @php
                                                $badges = [
                                                    'rascunho' => 'bg-slate-100 text-slate-800 border-slate-200',
                                                    'enviado' => 'bg-sky-100 text-sky-800 border-sky-200',
                                                    'aprovado' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                    'recusado' => 'bg-rose-100 text-rose-800 border-rose-200',
                                                    'atrasado' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                    'ajustado' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                                ];
                                                $badgeClass = $badges[$doc->status] ?? 'bg-slate-100 text-slate-800';
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $badgeClass }} capitalize">
                                                {{ $doc->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-slate-850">
                                            {{ $doc->score_final ?? '0.00' }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 text-xs">
                                            {{ $doc->submitted_at ? $doc->submitted_at->format('d/m/Y H:i') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                            Você ainda não enviou nenhum planejamento.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral (Estatísticas, Medalhas e Suporte) -->
            <div class="space-y-8">
                <!-- Cartão Pontuação -->
                <div class="bg-gradient-to-br from-violet-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg shadow-indigo-600/10">
                    <span class="text-xs font-semibold text-indigo-200 uppercase tracking-wider block">Pontuação Acumulada</span>
                    <h3 class="text-5xl font-extrabold mt-3 tracking-tight">{{ number_format($totalPoints, 2) }}</h3>
                    <p class="text-xs text-indigo-200 mt-2 font-medium">Pontos obtidos com planejamentos pedagógicos aprovados.</p>
                </div>

                <!-- Medalhas / Conquistas -->
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>🏆</span> Suas Conquistas
                    </h4>
                    <div class="grid grid-cols-4 gap-3">
                        @forelse($medals as $medal)
                            <div class="group relative flex flex-col items-center p-2 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-100 hover:border-indigo-100 transition duration-150" title="{{ $medal->description ?? '' }}">
                                <span class="text-2xl">{{ $medal->icon ?? '🏅' }}</span>
                                <span class="text-[9px] font-bold text-slate-500 mt-1 truncate max-w-full">{{ $medal->name }}</span>
                            </div>
                        @empty
                            <div class="col-span-4 text-center py-4 text-slate-400 text-xs font-medium">
                                Nenhuma medalha conquistada ainda. Continue enviando planejamentos no prazo!
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Suporte / Coordenação -->
                @if($coordinatorPhone)
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-lg">
                                💬
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">Precisa de Ajuda?</h4>
                                <p class="text-xs text-slate-500 font-medium">Fale com seu Coordenador</p>
                            </div>
                        </div>
                        <a href="https://wa.me/{{ $coordinatorPhone }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition duration-150 shadow-md shadow-emerald-600/10">
                            Fale no WhatsApp
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal de Upload de Planejamento -->
        <div x-show="showUploadModal" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-150" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;" 
             role="dialog" 
             aria-modal="true">
            
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" aria-hidden="true" @click="showUploadModal = false"></div>

                <!-- Elemento para centralizar o modal no desktop -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Conteúdo do Modal -->
                <div x-show="showUploadModal" 
                     x-transition:enter="transition ease-out duration-300 transform" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="transition ease-in duration-200 transform" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                    
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Enviar Planejamento</h3>
                            <p class="text-xs text-slate-500 mt-1 font-semibold">Período: <span x-text="selectedPeriodName" class="text-indigo-600 font-bold"></span></p>
                        </div>
                        <button type="button" class="text-slate-400 hover:text-slate-600" @click="showUploadModal = false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form action="{{ route('professor.documents.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                        @csrf
                        <input type="hidden" name="period_id" :value="selectedPeriodId">

                        <!-- Título do Planejamento -->
                        <div>
                            <label for="title" class="block text-sm font-semibold text-slate-700 mb-2">Título do Planejamento</label>
                            <input type="text" name="title" id="title" placeholder="Ex: Planejamento Mensal de Matemática - Junho" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 text-sm py-2.5 transition" required>
                        </div>

                        <!-- Tipo de Atividade -->
                        <div>
                            <label for="type" class="block text-sm font-semibold text-slate-700 mb-2">Tipo de Planejamento</label>
                            <select name="type" id="type" class="w-full rounded-lg border-slate-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500/20 text-sm py-2.5 transition" required>
                                <option value="regente" {{ $activeProfile === 'titular' && !$user->is_physical_education ? 'selected' : '' }}>Regente / Comum</option>
                                <option value="ed_fisica" {{ $user->is_physical_education ? 'selected' : '' }}>Educação Física</option>
                                <option value="monitoria" {{ $activeProfile === 'monitor' ? 'selected' : '' }}>Monitoria M.A.E.</option>
                            </select>
                        </div>

                        <!-- Upload do Arquivo -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Arquivo do Planejamento</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-xl hover:border-indigo-500 transition duration-150">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h20a4 4 0 004-4V20m-6-12l-6-6m0 0L8 8m4-4v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-slate-600 justify-center">
                                        <label for="file" class="relative cursor-pointer bg-white rounded-md font-semibold text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Fazer upload</span>
                                            <input id="file" name="file" type="file" class="sr-only" accept=".docx" required>
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-400">Apenas arquivos .docx até 10MB</p>
                                </div>
                            </div>
                            <!-- Exibição do nome do arquivo selecionado -->
                            <div id="file-name-preview" class="text-xs text-indigo-600 font-semibold mt-2 hidden text-center"></div>
                        </div>

                        <!-- Rodapé do Modal -->
                        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/20 -mx-6 -mb-6 p-6">
                            <button type="button" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold rounded-lg text-sm transition" @click="showUploadModal = false">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-750 text-white font-semibold rounded-lg text-sm shadow transition duration-150">
                                Confirmar e Enviar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Script de Preview do Nome do Arquivo -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const fileInput = document.getElementById('file');
                const namePreview = document.getElementById('file-name-preview');

                if (fileInput && namePreview) {
                    fileInput.addEventListener('change', function () {
                        if (fileInput.files.length > 0) {
                            namePreview.textContent = 'Arquivo selecionado: ' + fileInput.files[0].name;
                            namePreview.classList.remove('hidden');
                        } else {
                            namePreview.classList.add('hidden');
                        }
                    });
                }
            });
        </script>
    </div>
</x-app-layout>
