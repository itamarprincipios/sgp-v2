<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            {{ __('IANNE — Assistente Pedagógica') }}
        </h2>
    </x-slot>

    <div class="space-y-6" x-data="assistantPage()">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Pergunte sobre os planejamentos da escola</h3>
            <p class="text-xs text-slate-500">A IANNE varre os planejamentos e pareceres da escola para responder perguntas analíticas — sobre um professor, uma turma, um ano inteiro ou a escola toda.</p>
        </div>

        <!-- Conversa -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col" style="min-height: 420px;">
            <div class="flex-1 p-5 space-y-4 overflow-y-auto" x-ref="thread" style="max-height: 60vh;">
                <template x-if="messages.length === 0">
                    <div class="text-center py-12">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-violet-100 text-violet-600 flex items-center justify-center font-bold text-xl mb-3">IA</div>
                        <p class="text-sm text-slate-500">Faça sua primeira pergunta sobre os planejamentos da escola.</p>
                        <p class="text-xs text-slate-400 mt-1">Pode perguntar sobre um professor específico, uma turma, todos os 5º anos, a escola inteira...</p>
                    </div>
                </template>
                <template x-for="(msg, i) in messages" :key="i">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.role === 'user'
                            ? 'bg-indigo-600 text-white rounded-2xl rounded-br-sm max-w-[80%] px-4 py-2.5 text-sm'
                            : 'bg-slate-100 text-slate-800 rounded-2xl rounded-bl-sm max-w-[90%] px-4 py-3 text-sm'">
                            <pre class="whitespace-pre-wrap font-sans leading-relaxed" x-text="msg.text"></pre>
                        </div>
                    </div>
                </template>
                <div x-show="busy" class="flex justify-start">
                    <div class="bg-slate-100 text-slate-500 rounded-2xl rounded-bl-sm px-4 py-3 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin text-violet-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        A IANNE está varrendo os planejamentos...
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 p-4">
                <form @submit.prevent="send()" class="flex items-end gap-3">
                    <textarea x-model="question" @keydown.enter.prevent="send()" rows="2" maxlength="1000" placeholder="Escreva sua pergunta sobre os planejamentos da escola..." class="flex-1 text-sm rounded-xl border-slate-300 text-slate-900 focus:border-violet-500 focus:ring-violet-500 resize-none"></textarea>
                    <button type="submit" :disabled="busy || !question.trim()" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl shadow transition disabled:opacity-50 shrink-0">
                        Perguntar
                    </button>
                </form>
                <p class="text-[10px] text-slate-400 mt-2">A IANNE responde com base nos documentos enviados à Central de Correção. Perguntas amplas podem levar até 1 minuto.</p>
            </div>
        </div>
    </div>

    @php
        // Montado aqui, e não inline no @json, porque o parser de diretivas do
        // Blade quebra com arrow-function multilinha (mesmo caso do ebafa59).
        $chatHistory = $history->flatMap(function ($h) {
            return [
                ['role' => 'user', 'text' => $h->question],
                ['role' => 'assistant', 'text' => $h->response],
            ];
        })->values();
    @endphp
    <script>
        function assistantPage() {
            return {
                busy: false,
                question: '',
                messages: @json($chatHistory),
                csrf: document.querySelector('meta[name="csrf-token"]').content,

                async send() {
                    const q = this.question.trim();
                    if (!q || this.busy) return;
                    this.messages.push({ role: 'user', text: q });
                    this.question = '';
                    this.busy = true;
                    this.scrollDown();
                    try {
                        const res = await fetch('{{ route('school.assistant.ask') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ question: q }),
                        });
                        if (res.status === 419 || res.status === 401) {
                            alert('Sua sessão expirou. A página será recarregada — pergunte novamente.');
                            window.location.reload();
                            return;
                        }
                        const data = await res.json();
                        if (!res.ok || !data.success) throw new Error(data.message || 'Falha ao consultar a IANNE.');
                        this.messages.push({ role: 'assistant', text: data.answer });
                    } catch (e) {
                        this.messages.push({ role: 'assistant', text: '⚠️ ' + e.message });
                    } finally {
                        this.busy = false;
                        this.scrollDown();
                    }
                },

                scrollDown() {
                    this.$nextTick(() => { this.$refs.thread.scrollTop = this.$refs.thread.scrollHeight; });
                },
            };
        }
    </script>
</x-app-layout>
