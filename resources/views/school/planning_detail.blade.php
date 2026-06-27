<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Detalhes do Planejamento') }}
        </h2>
    </x-slot>

    @php
        // Group classes by grade/prefix
        $tabsData = [];
        foreach ($groupedData as $className => $records) {
            $className = $className ?: 'Sem Turma';
            $prefix = $className;
            if (preg_match('/^(.*)\s+[A-Z]$/', $className, $matches)) {
                $prefix = trim($matches[1]);
            }
            $prefix = mb_convert_case($prefix, MB_CASE_TITLE, "UTF-8");
            $tabsData[$prefix][$className] = $records;
        }
        ksort($tabsData);
        if (empty($tabsData)) {
            $tabsData['Geral'] = $groupedData;
        }
        
        $allTabKeys = array_keys($tabsData);
        $firstTabKey = $allTabKeys[0] ?? 'Geral';
    @endphp

    <div class="space-y-8" x-data="{ 
        activeTab: '{{ $firstTabKey }}', 
        showTextModal: false, 
        currentText: '', 
        currentProfName: '',
        showReviewModal: false,
        reviewDocId: null,
        reviewStatus: 'aprovado',
        reviewFeedback: '',
        showWhatsappModal: false,
        wsPhone: '',
        wsProfName: '',
        wsMessage: ''
    }">
        <!-- Alertas de Sucesso/Erro -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-xl font-bold text-slate-900">{{ $planning->name }}</h3>
                <p class="text-xs text-slate-500">
                    {{ $planning->description }} | 
                    <span class="font-semibold text-rose-500">Prazo Limite: {{ $planning->deadline->format('d/m/Y H:i') }}</span>
                </p>
            </div>
            <a href="{{ route('school.plannings') }}" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold rounded-xl text-xs transition">
                Voltar
            </a>
        </div>

        <!-- Abas por Série/Grupo -->
        <div class="flex border-b border-slate-200 gap-6 overflow-x-auto pb-1">
            @foreach(array_keys($tabsData) as $tabName)
                <button @click="activeTab = '{{ $tabName }}'" :class="activeTab === '{{ $tabName }}' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="pb-3 border-b-2 text-sm transition whitespace-nowrap">
                    {{ $tabName }}
                </button>
            @endforeach
        </div>

        <!-- Conteúdo das Abas -->
        @foreach($tabsData as $tabName => $classes)
            <div x-show="activeTab === '{{ $tabName }}'" class="space-y-6">
                @foreach($classes as $className => $records)
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center gap-2 font-bold text-slate-800 text-sm">
                            <span>👥</span> Turma: {{ $className }}
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-50/10">
                                        <th class="px-6 py-4">Professor</th>
                                        <th class="px-6 py-4">Data de Entrega</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4 text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                    @foreach($records as $rec)
                                        @if(!$rec->professor_name)
                                            <tr>
                                                <td colspan="4" class="px-6 py-8 text-center text-slate-400 font-medium">
                                                    Nenhum professor alocado nesta turma.
                                                </td>
                                            </tr>
                                        @else
                                            <tr class="hover:bg-slate-50/30 transition">
                                                <td class="px-6 py-4 font-bold text-slate-900">
                                                    {{ $rec->professor_name }}
                                                </td>
                                                <td class="px-6 py-4 text-slate-500 font-medium">
                                                    {{ $rec->submitted_at ? date('d/m/Y H:i', strtotime($rec->submitted_at)) : '-' }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($rec->status)
                                                        @php
                                                            $badgeClass = match($rec->status) {
                                                                'aprovado' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                                'rejeitado' => 'bg-rose-50 text-rose-700 border-rose-100',
                                                                'ajustado' => 'bg-amber-50 text-amber-700 border-amber-100',
                                                                'atrasado' => 'bg-orange-50 text-orange-700 border-orange-100',
                                                                default => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                                            };
                                                        @endphp
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize {{ $badgeClass }}">
                                                            {{ $rec->status }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                                            Pendente
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex items-center justify-end gap-3">
                                                        @if($rec->file_path)
                                                            <!-- Visualizar Texto Extraído (IANNE) -->
                                                            @if($rec->content_text)
                                                                <button @click="showTextModal = true; currentText = `{{ addslashes($rec->content_text) }}`; currentProfName = '{{ $rec->professor_name }}'" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg transition cursor-pointer" title="Ler conteúdo do plano">
                                                                    📄 Ler Conteúdo
                                                                </button>
                                                            @endif

                                                            <!-- Baixar/Ver Arquivo Físico -->
                                                            <a href="{{ asset('uploads/' . $rec->file_path) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition" title="Baixar arquivo original">
                                                                📥 Baixar .docx
                                                            </a>

                                                            <!-- Ações de Avaliação -->
                                                            @if($rec->status !== 'aprovado')
                                                                <button @click="showReviewModal = true; reviewDocId = {{ $rec->id }}; reviewStatus = 'aprovado'; reviewFeedback = ''" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg shadow-sm transition cursor-pointer">
                                                                    Avaliar
                                                                </button>
                                                            @endif
                                                        @endif

                                                        <!-- WhatsApp Link -->
                                                        @if($rec->whatsapp)
                                                            @php
                                                                $phone = preg_replace('/\D/', '', $rec->whatsapp);
                                                            @endphp
                                                            <button @click="showWhatsappModal = true; wsPhone = '{{ $phone }}'; wsProfName = '{{ $rec->professor_name }}'; wsMessage = ''" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1 cursor-pointer">
                                                                💬 WhatsApp
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <!-- ================= MODAL: LEITURA DE TEXTO EXTRAÍDO ================= -->
        <div x-show="showTextModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showTextModal = false">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                        <h3 class="text-base font-bold text-slate-900">
                            Conteúdo do Planejamento - <span x-text="currentProfName" class="text-indigo-600"></span>
                        </h3>
                        <button @click="showTextModal = false" class="text-slate-400 hover:text-slate-600">
                            ✕
                        </button>
                    </div>

                    <div class="p-6 max-h-[60vh] overflow-y-auto text-sm text-slate-700 leading-relaxed font-sans whitespace-pre-wrap" x-text="currentText">
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                        <button @click="showTextModal = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= MODAL: AVALIAÇÃO DE DOCUMENTO ================= -->
        <div x-show="showReviewModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showReviewModal = false">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                    <form action="{{ route('school.document.review') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" :value="reviewDocId">

                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                            <h3 class="text-base font-bold text-slate-900">Avaliar Planejamento</h3>
                            <button type="button" @click="showReviewModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>

                        <div class="p-6 space-y-4">
                            <!-- Seleção de Status -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">Resultado da Avaliação</label>
                                <select name="status" x-model="reviewStatus" required class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <option value="aprovado">Aprovado Diretamente</option>
                                    <option value="ajustado">Aprovado com Ajustes</option>
                                    <option value="rejeitado">Rejeitado (Devolver para Correção)</option>
                                </select>
                            </div>

                            <!-- Feedback/Observações -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-600">Observações / Feedback</label>
                                <textarea name="feedback" x-model="reviewFeedback" placeholder="Digite orientações pedagógicas para o professor..." class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 h-32 resize-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                            <button type="button" @click="showReviewModal = false" class="px-4 py-2 border border-slate-200 text-slate-650 hover:bg-slate-50 font-semibold rounded-xl text-xs transition">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow transition">
                                Confirmar Avaliação
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= MODAL: NOTIFICAÇÃO WHATSAPP ================= -->
        <div x-show="showWhatsappModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showWhatsappModal = false">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="text-emerald-500">💬</span> Falar com Prof. <span x-text="wsProfName"></span>
                        </h3>
                        <button type="button" @click="showWhatsappModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-600">Sua Mensagem</label>
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 text-xs text-slate-500 italic">
                                Olá Prof. <span x-text="wsProfName"></span>,
                            </div>
                            <textarea x-model="wsMessage" placeholder="Digite o conteúdo da mensagem..." class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 h-28 resize-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"></textarea>
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 text-[10px] text-slate-400 italic">
                                Mensagem enviada automaticamente pelo Sistema SGP-Coordenação
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                        <button type="button" @click="showWhatsappModal = false" class="px-4 py-2 border border-slate-200 text-slate-650 hover:bg-slate-50 font-semibold rounded-xl text-xs transition">
                            Cancelar
                        </button>
                        <button type="button" @click="
                            const url = 'https://web.whatsapp.com/send?phone=+55' + wsPhone + '&text=' + encodeURIComponent('Olá Prof. ' + wsProfName + ',\n\n' + wsMessage + '\n\nMensagem enviada automaticamente pelo Sistema SGP-Coordenação');
                            window.open(url, '_blank');
                            showWhatsappModal = false;
                        " class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl text-xs shadow transition flex items-center gap-1.5">
                            Abrir WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
