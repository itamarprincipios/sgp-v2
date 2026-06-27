<?php

namespace App\Http\Controllers;

use App\Models\AiQuery;
use App\Services\AIService;
use App\Services\ContextBuilder;
use App\Services\PromptBuilder;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RAGController extends Controller
{
    protected $contextBuilder;
    protected $promptBuilder;
    protected $aiService;

    public function __construct(
        ContextBuilder $contextBuilder,
        PromptBuilder $promptBuilder
    ) {
        $this->contextBuilder = $contextBuilder;
        $this->promptBuilder = $promptBuilder;
        
        try {
            $this->aiService = new AIService();
        } catch (Exception $e) {
            // Fail silently on construct to avoid throwing errors if OpenAI Key is missing during loading
            $this->aiService = null;
        }
    }

    /**
     * Endpoint principal para consultas RAG.
     */
    public function query(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $allowedRoles = ['semed', 'admin', 'superadmin', 'coordinator', 'supervisor_edfis'];
            
            if (!$user || !in_array($user->role, $allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Acesso negado. Apenas usuários autorizados podem usar esta funcionalidade.'
                ], 403);
            }

            $question = $request->input('question');
            $filters = $request->input('filters', []);

            if (empty($question)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Pergunta não fornecida.'
                ], 400);
            }

            // Se usuário é COORDENADOR, forçar filtro de escola dele
            if ($user->role === 'coordinator') {
                $schoolId = $user->school_id;
                if (!$schoolId) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Coordenador não está vinculado a nenhuma escola.'
                    ], 400);
                }
                $filters['school_id'] = $schoolId;
            } elseif ($user->role === 'semed') {
                // SEMED pode ver a rede global ou escola específica
                if (isset($filters['school_id']) && !empty($filters['school_id'])) {
                    // Validar se escola pertence ao SEMED
                    $hasAccess = \DB::table('user_schools')
                        ->where('user_id', $user->id)
                        ->where('school_id', $filters['school_id'])
                        ->exists();
                    
                    if (!$hasAccess && $user->role !== 'admin') {
                        return response()->json([
                            'success' => false,
                            'error' => 'Você não tem acesso a esta escola.'
                        ], 403);
                    }
                } else {
                    // Agregar todas as escolas vinculadas do SEMED
                    $schoolIds = \DB::table('user_schools')
                        ->where('user_id', $user->id)
                        ->pluck('school_id')
                        ->toArray();
                        
                    if (empty($schoolIds)) {
                        $filters['context_type'] = 'network'; // Fallback global se nenhum vínculo
                    } else {
                        $filters['school_ids'] = $schoolIds;
                    }
                }
            } elseif ($user->role === 'supervisor_edfis') {
                $filters['context_type'] = 'physical_education';
            }

            if (!$this->aiService) {
                return response()->json([
                    'success' => false,
                    'error' => 'Serviço de Inteligência Artificial indisponível (Chave API não configurada).'
                ], 500);
            }

            // Iniciar contagem de tempo
            $startTime = microtime(true);

            // 1. Identificar e recuperar contexto
            $context = $this->buildContext($filters);

            // 2. Construir prompt
            $prompt = $this->promptBuilder->buildAnalysisPrompt($context, $question);

            // 3. Consultar IA
            $aiResponse = $this->aiService->query($prompt);

            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);

            // Salvar consulta no histórico
            AiQuery::create([
                'user_id' => $user->id,
                'question' => $question,
                'context_filters' => $filters,
                'response' => $aiResponse,
                'response_time_ms' => $responseTimeMs
            ]);

            return response()->json([
                'success' => true,
                'question' => $question,
                'response' => $aiResponse,
                'context_type' => $context['tipo'] ?? 'desconhecido',
                'response_time_ms' => $responseTimeMs
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            Log::error("Erro no RAGController@query: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Ocorreu um erro no processamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna histórico de consultas do usuário.
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'error' => 'Não autenticado'], 401);
            }

            $history = AiQuery::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->take(20)
                ->get(['id', 'question', 'response', 'created_at']);

            return response()->json([
                'success' => true,
                'history' => $history
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function buildContext(array $filters): array
    {
        if (isset($filters['professor_id'])) {
            return $this->contextBuilder->getProfessorContext(
                $filters['professor_id'],
                $filters['period_id'] ?? null
            );
        }

        if (isset($filters['school_id'])) {
            return $this->contextBuilder->getSchoolContext($filters['school_id']);
        }

        if (isset($filters['school_ids']) && !empty($filters['school_ids'])) {
            return $this->contextBuilder->getMultiSchoolContext($filters['school_ids']);
        }

        if (isset($filters['context_type']) && $filters['context_type'] === 'physical_education') {
            return $this->contextBuilder->getPhysicalEducationContext();
        }

        return $this->contextBuilder->getNetworkContext();
    }
}
