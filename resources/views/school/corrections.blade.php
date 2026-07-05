<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('Correções de Planejamentos') }}
        </h2>
    </x-slot>

    <div class="space-y-8" x-data="correctionsPage()">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Central de Correção</h3>
            <p class="text-xs text-slate-500">Arraste os planejamentos e relatórios que você recebeu (Word ou PDF). A IANNE identifica o professor, a turma e o período — você só confirma.</p>
        </div>

        <!-- Dropzone -->
        <div
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="dragging = false; addFiles($event.dataTransfer.files)"
            @click="$refs.fileInput.click()"
            :class="dragging ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300 bg-white hover:border-indigo-400'"
            class="border-2 border-dashed rounded-2xl p-10 text-center cursor-pointer transition duration-150">
            <input type="file" x-ref="fileInput" class="hidden" multiple accept=".docx,.pdf" @change="addFiles($event.target.files); $event.target.value = ''">
            <svg class="w-12 h-12 mx-auto text-indigo-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="text-sm font-bold text-slate-700">Arraste os arquivos aqui ou clique para selecionar</p>
            <p class="text-xs text-slate-500 mt-1">Word (.docx) ou PDF — até 10MB por arquivo</p>
        </div>

        <!-- Fila de uploads em andamento -->
        <template x-if="queue.length > 0">
            <div class="space-y-3">
                <template x-for="item in queue" :key="item.key">
                    <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-center gap-4">
                        <template x-if="item.state === 'working'">
                            <svg class="w-5 h-5 text-indigo-600 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </template>
                        <template x-if="item.state === 'error'">
                            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </template>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate" x-text="item.name"></p>
                            <p class="text-xs" :class="item.state === 'error' ? 'text-rose-600' : 'text-slate-500'"
                               x-text="item.state === 'error' ? item.error : 'Enviando e analisando com a IANNE...'"></p>
                        </div>
                        <button x-show="item.state === 'error'" @click="removeFromQueue(item.key)" class="text-xs text-slate-400 hover:text-slate-600 font-medium">Fechar</button>
                    </div>
                </template>
            </div>
        </template>

        <!-- Cards de confirmação de metadados -->
        <template x-if="pending.length > 0">
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <h4 class="text-sm font-bold text-slate-900">Aguardando sua confirmação</h4>
                    <span class="text-xs font-bold text-white bg-amber-500 rounded-full px-2 py-0.5" x-text="pending.length"></span>
                </div>
                <template x-for="doc in pending" :key="doc.id">
                    <div class="bg-white border border-amber-300 rounded-xl p-5 space-y-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs text-slate-400 truncate" x-text="doc.file_name"></p>
                                <template x-if="doc.ai_error">
                                    <p class="text-xs text-rose-600 mt-1" x-text="doc.ai_error"></p>
                                </template>
                                <template x-if="!doc.ai_error && doc.professor_name_detected && !doc.professor_id">
                                    <p class="text-xs text-amber-600 mt-1">
                                        A IA detectou "<span x-text="doc.professor_name_detected"></span>" mas não encontrou esse professor no cadastro — selecione manualmente.
                                    </p>
                                </template>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 rounded-full px-2.5 py-1 shrink-0">Confirmar dados</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div class="sm:col-span-2 lg:col-span-3">
                                <label class="text-xs font-semibold text-slate-600">Título</label>
                                <input type="text" x-model="doc.title" class="mt-1 w-full text-sm rounded-lg border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Professor(a)</label>
                                <select x-model="doc.professor_id" class="mt-1 w-full text-sm rounded-lg border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">— não identificado —</option>
                                    <option value="__new__">➕ Criar novo professor</option>
                                    @foreach($professors as $professor)
                                        <option value="{{ $professor->id }}">{{ $professor->name }}</option>
                                    @endforeach
                                </select>
                                <template x-if="doc.professor_id === '__new__'">
                                    <input type="text" x-model="doc.new_professor_name" placeholder="Nome do professor"
                                           class="mt-2 w-full text-sm rounded-lg border-indigo-300 bg-indigo-50 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                                </template>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Turma</label>
                                <select x-model="doc.class_id" class="mt-1 w-full text-sm rounded-lg border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">— não identificada —</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Tipo</label>
                                <select x-model="doc.type" class="mt-1 w-full text-sm rounded-lg border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="planejamento">Planejamento</option>
                                    <option value="relatorio">Relatório</option>
                                    <option value="outro">Outro documento</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Disciplina</label>
                                <input type="text" x-model="doc.discipline" placeholder="Ex: Polivalente" class="mt-1 w-full text-sm rounded-lg border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Período de referência</label>
                                <input type="text" x-model="doc.reference_label" placeholder="Ex: 1º Bimestre {{ now()->year }}" class="mt-1 w-full text-sm rounded-lg border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-1">
                            <button @click="discardDoc(doc)" :disabled="doc.busy" class="px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-lg transition">Descartar</button>
                            <button @click="confirmDoc(doc)" :disabled="doc.busy" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow transition disabled:opacity-50">
                                <span x-show="!doc.busy">Confirmar e enviar para correção</span>
                                <span x-show="doc.busy">Salvando...</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Documentos em correção -->
        <div class="space-y-4">
            <h4 class="text-sm font-bold text-slate-900">Documentos em correção</h4>
            @php $confirmed = $documents->whereIn('status', ['em_correcao', 'corrigido']); @endphp
            @if($confirmed->isEmpty())
                <div class="bg-white border border-slate-200 rounded-xl p-8 text-center">
                    <p class="text-sm text-slate-500">Nenhum documento em correção ainda. Arraste os arquivos acima para começar.</p>
                </div>
            @else
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Documento</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Professor(a)</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Turma</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Período</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($confirmed as $doc)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-slate-800">{{ $doc->title }}</p>
                                        <p class="text-xs text-slate-400">{{ ucfirst($doc->type) }}{{ $doc->discipline ? ' · ' . $doc->discipline : '' }} · enviado por {{ $doc->uploadedBy->name ?? 'N/A' }} em {{ $doc->created_at->format('d/m/Y') }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $doc->user->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $doc->schoolClass->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $doc->reference_label ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if($doc->status === 'corrigido')
                                            <span class="text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 rounded-full px-2.5 py-1">Corrigido</span>
                                        @else
                                            <span class="text-[10px] font-bold uppercase bg-sky-100 text-sky-800 rounded-full px-2.5 py-1">Em correção</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button @click="openAnalysis(@js($doc->id), @js($doc->title), @js($doc->reference_label ?? ''), @js($doc->analysis ?? ''))" class="text-xs font-bold text-violet-600 hover:text-violet-800">{{ $doc->analysis ? 'Ver análise' : 'Analisar' }}</button>
                                            @if($doc->analysis)
                                                <button @click="approveDoc({{ $doc->id }})" class="text-xs font-bold text-emerald-600 hover:text-emerald-800">Aprovar</button>
                                            @endif
                                            <a href="{{ $doc->viewer_url }}" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Abrir</a>
                                            <a href="{{ $doc->download_url }}" download class="text-xs font-bold text-slate-500 hover:text-slate-700">Baixar</a>
                                            @if($doc->status === 'em_correcao')
                                                <button @click="deleteConfirmed({{ $doc->id }})" class="text-xs font-bold text-rose-500 hover:text-rose-700">Excluir</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <style>[x-cloak]{display:none!important}</style>

        <!-- Modal de análise da IANNE (Fase 2 — correção assistida) -->
        <div x-show="analysisOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60" @click="closeAnalysis()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white">
                    <h3 class="font-bold text-slate-900 text-sm">Análise da IANNE — <span class="text-slate-600" x-text="analysisDoc.title"></span></h3>
                    <button @click="closeAnalysis()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="p-6 space-y-5">
                    <p class="text-xs text-slate-500">Confirme os dados abaixo — a IANNE usa isso para checar a vigência e a carga horária — e gere o parecer.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-600">Período de vigência</label>
                            <input type="text" x-model="analysisDoc.vigencia" placeholder="Ex: 04/05 a 29/05/2026" class="mt-1 w-full text-sm rounded-lg border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-600">Nº de aulas previstas</label>
                            <input type="text" x-model="analysisDoc.aulas" placeholder="Ex: 20" class="mt-1 w-full text-sm rounded-lg border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-600">Observações (opcional)</label>
                            <input type="text" x-model="analysisDoc.observacoes" placeholder="Algo a destacar" class="mt-1 w-full text-sm rounded-lg border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="runAnalysis()" :disabled="analysisBusy" class="px-5 py-2 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-lg shadow transition disabled:opacity-50">
                            <span x-show="!analysisBusy" x-text="analysisDoc.report ? 'Refazer análise' : 'Analisar com a IANNE'"></span>
                            <span x-show="analysisBusy">Analisando com a IANNE...</span>
                        </button>
                        <span x-show="analysisBusy" class="text-xs text-slate-500">Pode levar alguns segundos.</span>
                    </div>
                    <template x-if="analysisDoc.report">
                        <div class="space-y-2">
                            <label class="text-xs font-semibold text-slate-600">Parecer (edite antes de salvar, se quiser)</label>
                            <textarea x-model="analysisDoc.report" rows="18" class="w-full text-sm rounded-lg border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            <div class="flex items-center justify-end gap-3">
                                <button @click="closeAnalysis()" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">Fechar</button>
                                <button @click="saveAnalysisEdit()" :disabled="analysisBusy" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow transition disabled:opacity-50">Salvar parecer</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @php
        // Docs aguardando confirmação (para recarregamento de página). Montado
        // aqui, e não inline no @json, para não esbarrar no parser de diretiva
        // do Blade com um arrow-function que retorna array.
        $pendingDocs = $documents->where('status', 'aguardando_confirmacao')->map(function ($d) {
            return [
                'id' => $d->id,
                'title' => $d->title,
                'type' => $d->type,
                'file_name' => $d->file_path,
                'professor_id' => $d->user_id ? (string) $d->user_id : '',
                'class_id' => $d->class_id ? (string) $d->class_id : '',
                'discipline' => $d->discipline,
                'reference_label' => $d->reference_label,
                'professor_name_detected' => null,
                'class_name_detected' => null,
                'new_professor_name' => '',
                'busy' => false,
            ];
        })->values();
    @endphp

    <script>
        function correctionsPage() {
            return {
                dragging: false,
                queue: [],
                // Documentos já subidos que ainda aguardam confirmação (recarregamento de página)
                pending: @json($pendingDocs),
                csrf: document.querySelector('meta[name="csrf-token"]').content,
                uploading: 0,

                addFiles(fileList) {
                    for (const file of fileList) {
                        const ext = file.name.split('.').pop().toLowerCase();
                        const key = Date.now() + '_' + Math.random().toString(36).slice(2);
                        if (!['docx', 'pdf'].includes(ext)) {
                            this.queue.push({ key, name: file.name, state: 'error', error: 'Formato não suportado (envie .docx ou .pdf).' });
                            continue;
                        }
                        if (file.size > 10 * 1024 * 1024) {
                            this.queue.push({ key, name: file.name, state: 'error', error: 'Arquivo muito grande (máximo 10MB).' });
                            continue;
                        }
                        this.queue.push({ key, name: file.name, state: 'working' });
                        this.uploadFile(file, key);
                    }
                },

                async uploadFile(file, key) {
                    const formData = new FormData();
                    formData.append('file', file);
                    try {
                        const res = await fetch('{{ route('school.corrections.upload') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                            body: formData,
                        });
                        if (res.status === 419 || res.status === 401) {
                            alert('Sua sessão expirou. A página será recarregada — envie os arquivos novamente.');
                            window.location.reload();
                            return;
                        }
                        const data = await res.json();
                        if (!res.ok || !data.success) {
                            throw new Error(data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Falha no envio.'));
                        }
                        this.removeFromQueue(key);
                        const d = data.document;
                        const hasProf = !!d.professor_id;
                        // Se a IA leu um nome mas não casou com o cadastro, já
                        // deixa "criar novo" pronto com o nome detectado.
                        this.pending.push({
                            ...d,
                            professor_id: hasProf ? String(d.professor_id) : (d.professor_name_detected ? '__new__' : ''),
                            new_professor_name: (!hasProf && d.professor_name_detected) ? d.professor_name_detected : '',
                            class_id: d.class_id ? String(d.class_id) : '',
                            busy: false,
                        });
                    } catch (e) {
                        const item = this.queue.find(i => i.key === key);
                        if (item) { item.state = 'error'; item.error = e.message; }
                    }
                },

                removeFromQueue(key) {
                    this.queue = this.queue.filter(i => i.key !== key);
                },

                // Helper central: trata sessão expirada (419) com aviso claro
                // em vez do críptico "CSRF token mismatch".
                async request(url, method, body) {
                    const res = await fetch(url, {
                        method,
                        headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify(body),
                    });
                    if (res.status === 419 || res.status === 401) {
                        alert('Sua sessão expirou. A página será recarregada — tente a ação novamente.');
                        window.location.reload();
                        throw new Error('Sessão expirada.');
                    }
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Falha na operação.'));
                    }
                    return data;
                },

                async confirmDoc(doc) {
                    if (!doc.title || !doc.title.trim()) { alert('Informe um título para o documento.'); return; }
                    const creatingProf = doc.professor_id === '__new__';
                    if (creatingProf && (!doc.new_professor_name || !doc.new_professor_name.trim())) {
                        alert('Informe o nome do professor a criar (ou escolha um existente).'); return;
                    }
                    doc.busy = true;
                    try {
                        await this.request('{{ route('school.corrections.confirm') }}', 'POST', {
                            id: doc.id,
                            title: doc.title,
                            type: doc.type,
                            professor_id: creatingProf ? null : (doc.professor_id || null),
                            new_professor_name: creatingProf ? doc.new_professor_name.trim() : null,
                            class_id: doc.class_id || null,
                            discipline: doc.discipline || null,
                            reference_label: doc.reference_label || null,
                        });
                        this.pending = this.pending.filter(p => p.id !== doc.id);
                        if (this.queue.filter(i => i.state === 'working').length === 0 && this.pending.length === 0) {
                            window.location.reload();
                        }
                    } catch (e) {
                        alert(e.message);
                        doc.busy = false;
                    }
                },

                async discardDoc(doc) {
                    if (!confirm('Descartar este documento? O arquivo será removido.')) return;
                    doc.busy = true;
                    try {
                        await this.deleteRequest(doc.id);
                        this.pending = this.pending.filter(p => p.id !== doc.id);
                    } catch (e) {
                        alert(e.message);
                        doc.busy = false;
                    }
                },

                async deleteConfirmed(id) {
                    if (!confirm('Excluir este documento do fluxo de correção?')) return;
                    try {
                        await this.deleteRequest(id);
                        window.location.reload();
                    } catch (e) {
                        alert(e.message);
                    }
                },

                async approveDoc(id) {
                    if (!confirm('Aprovar este planejamento? Ele sai da Central de Correção e passa a compor o histórico do professor.')) return;
                    try {
                        await this.request('{{ route('school.corrections.approve') }}', 'POST', { id });
                        window.location.reload();
                    } catch (e) {
                        alert(e.message);
                    }
                },

                async deleteRequest(id) {
                    await this.request('{{ route('school.corrections.delete') }}', 'DELETE', { id });
                },

                // ===== Análise da IANNE (Fase 2) =====
                analysisOpen: false,
                analysisBusy: false,
                analysisDoc: { id: null, title: '', vigencia: '', aulas: '', observacoes: '', report: '' },

                openAnalysis(id, title, vigencia, existing) {
                    this.analysisDoc = { id, title, vigencia: vigencia || '', aulas: '', observacoes: '', report: existing || '' };
                    this.analysisOpen = true;
                },

                closeAnalysis() {
                    this.analysisOpen = false;
                },

                async runAnalysis() {
                    this.analysisBusy = true;
                    try {
                        const data = await this.request('{{ route('school.corrections.analyze') }}', 'POST', {
                            id: this.analysisDoc.id,
                            vigencia: this.analysisDoc.vigencia || null,
                            aulas: this.analysisDoc.aulas || null,
                            observacoes: this.analysisDoc.observacoes || null,
                        });
                        this.analysisDoc.report = data.analysis;
                    } catch (e) {
                        alert(e.message);
                    } finally {
                        this.analysisBusy = false;
                    }
                },

                async saveAnalysisEdit() {
                    if (!this.analysisDoc.report || !this.analysisDoc.report.trim()) { alert('Nada para salvar.'); return; }
                    this.analysisBusy = true;
                    try {
                        await this.request('{{ route('school.corrections.analysis.save') }}', 'POST', { id: this.analysisDoc.id, analysis: this.analysisDoc.report });
                        this.analysisOpen = false;
                        window.location.reload();
                    } catch (e) {
                        alert(e.message);
                    } finally {
                        this.analysisBusy = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>
