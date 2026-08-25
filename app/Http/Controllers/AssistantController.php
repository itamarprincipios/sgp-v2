<?php

namespace App\Http\Controllers;

use App\Models\AiQuery;
use App\Services\AIService;
use App\Services\PlanCorpus;
use Illuminate\Http\Request;

/**
 * IANNE Assistente — perguntas amplas sobre o conjunto de planejamentos da
 * escola (metodologias usadas, contagens, erros recorrentes para oficinas
 * de formação etc.). Fase 3 do pivô — AGENTS.md seção 14.
 */
class AssistantController extends Controller
{
    /**
     * Página do chat, com o histórico recente do usuário.
     */
    public function index()
    {
        $history = AiQuery::where('user_id', auth()->id())
            ->where('context_filters->tipo', 'planejamentos_escola')
            ->orderByDesc('id')
            ->limit(15)
            ->get()
            ->reverse()
            ->values();

        return view('school.assistant', compact('history'));
    }

    /**
     * Recebe a pergunta, monta o corpus dos planejamentos e responde.
     */
    public function ask(Request $request, PlanCorpus $corpus, AIService $ai)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate([
            'question' => ['required', 'string', 'max:1000'],
        ]);

        $data = $corpus->build($schoolIds);

        if ($data['included'] === 0) {
            return response()->json([
                'success' => true,
                'answer' => 'Ainda não há planejamentos com texto extraído nesta escola. Envie documentos pela Central de Correção e volte a perguntar.',
            ]);
        }

        $cobertura = $data['included'] < $data['total']
            ? "ATENÇÃO: por limite de espaço, você está vendo os {$data['included']} documentos mais recentes de um total de {$data['total']}. Ao responder contagens ou levantamentos, deixe claro que a análise cobre os {$data['included']} planejamentos mais recentes."
            : "Você está vendo TODOS os {$data['total']} documentos da escola.";

        $b = \App\Services\PromptSettings::for($user->tenant_id);
        $regrasExtra = trim($b['assistente_extra']) !== '' ? "
- " . str_replace("
", "
- ", trim($b['assistente_extra'])) : '';

        $prompt = <<<PROMPT
{$b['assistente_persona']}

REGRAS:
- Responda SOMENTE com base nos documentos fornecidos. Não invente professores, planos ou práticas que não estejam neles.
- Ao citar um achado, identifique a fonte: nome do professor e título/período do documento.
- Em contagens e levantamentos ("quantos professores usaram X"), liste nominalmente quem usou, para a resposta ser conferível.
- Se a pergunta não puder ser respondida com os documentos disponíveis, diga isso claramente.
- {$cobertura}
- Responda em português, em markdown, de forma organizada e direta.{$regrasExtra}

DOCUMENTOS DA ESCOLA:
{$data['text']}

PERGUNTA DO COORDENADOR:
{$request->question}
PROMPT;

        try {
            $start = microtime(true);
            $answer = $ai->query($prompt, false, 3000, 120);
            $elapsed = (int) ((microtime(true) - $start) * 1000);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'A IANNE não conseguiu responder agora: ' . $e->getMessage(),
            ], 502);
        }

        AiQuery::create([
            'user_id' => $user->id,
            'question' => $request->question,
            'response' => $answer,
            'response_time_ms' => $elapsed,
            'context_filters' => ['tipo' => 'planejamentos_escola', 'docs_incluidos' => $data['included'], 'docs_total' => $data['total']],
        ]);

        return response()->json(['success' => true, 'answer' => $answer]);
    }
}
