<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integração com a API da Claude (Anthropic) — endpoint Messages.
 * Mantém a mesma interface pública que o restante do SGP já consome
 * (query, queryWithFile), então RAGController/MetadataInference/DocumentExtractor
 * não mudam. HTTP direto (sem SDK) para não adicionar dependência de composer
 * no shared hosting.
 */
class AIService
{
    private $apiKey;
    private $model;
    private $maxTokens;

    /**
     * stop_reason da última chamada. 'max_tokens' significa que a resposta foi
     * CORTADA no meio — quem transcreve documento precisa saber disso, senão
     * entrega meio texto como se fosse o documento inteiro.
     */
    private ?string $lastStopReason = null;

    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';
    private const VERSION = '2023-06-01';

    public function lastStopReason(): ?string
    {
        return $this->lastStopReason;
    }

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key');
        $this->model = config('services.anthropic.model', 'claude-opus-4-8');
        $this->maxTokens = (int) config('services.anthropic.max_tokens', 1024);

        if (empty($this->apiKey)) {
            throw new Exception('ANTHROPIC_API_KEY não configurada no arquivo .env');
        }
    }

    /**
     * Envia uma pergunta em texto para a Claude e retorna a resposta.
     *
     * @param string $prompt O prompt a ser enviado
     * @param bool $jsonMode Se true, pede JSON puro e remove cercas de código da resposta
     * @return string A resposta da IA
     * @throws Exception Em caso de erro na API
     */
    public function query(string $prompt, bool $jsonMode = false, ?int $maxTokens = null, int $timeout = 30): string
    {
        // Sanitizar o prompt para evitar problemas com JSON
        $prompt = mb_convert_encoding($prompt, 'UTF-8', 'UTF-8');
        $prompt = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $prompt);

        if ($jsonMode) {
            $prompt .= "\n\nResponda APENAS com o objeto JSON pedido — sem texto antes ou depois e sem blocos de código markdown.";
        }

        return $this->generateContent([
            ['type' => 'text', 'text' => $prompt],
        ], $jsonMode, $maxTokens, $timeout);
    }

    /**
     * Envia um prompt acompanhado de um arquivo (PDF via base64) para a Claude.
     * Usado para extrair texto de PDFs sem depender de libs de parsing no servidor.
     *
     * @param string $prompt Instrução sobre o que fazer com o arquivo
     * @param string $filePath Caminho completo do arquivo
     * @param string $mimeType MIME type (ex: application/pdf)
     * @return string A resposta da IA
     * @throws Exception Em caso de erro na API ou arquivo inacessível
     */
    public function queryWithFile(string $prompt, string $filePath, string $mimeType, ?int $maxTokens = null): string
    {
        $maxTokens = $maxTokens ?? (int) config('services.anthropic.file_max_tokens', 32000);

        if (!file_exists($filePath)) {
            throw new Exception("Arquivo não encontrado: {$filePath}");
        }

        // base64_encode não insere quebras de linha (a API exige data sem \n).
        $data = base64_encode(file_get_contents($filePath));

        return $this->generateContent([
            ['type' => 'document', 'source' => [
                'type' => 'base64',
                'media_type' => $mimeType,
                'data' => $data,
            ]],
            ['type' => 'text', 'text' => $prompt],
        ], false, $maxTokens, 180);
    }

    /**
     * Chamada base ao endpoint Messages da Claude.
     *
     * @param array $content Blocos de conteúdo do turno do usuário (text / document)
     */
    private function generateContent(array $content, bool $jsonMode = false, ?int $maxTokens = null, int $timeout = 30): string
    {
        $tenant = auth()->user()?->tenant;
        AiQuota::assertAvailable($tenant);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => self::VERSION,
                'content-type' => 'application/json',
            ])
            ->timeout($timeout)
            ->post(self::ENDPOINT, [
                'model' => $this->model,
                'max_tokens' => $maxTokens ?? $this->maxTokens,
                'messages' => [
                    ['role' => 'user', 'content' => $content],
                ],
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json('error.message', $response->body());
                Log::error("Claude API Error (HTTP {$response->status()}): {$errorMsg}");
                throw new Exception("Claude API Error (HTTP {$response->status()}): {$errorMsg}");
            }

            $this->lastStopReason = $response->json('stop_reason');

            // A resposta traz content[] com blocos; juntamos o texto de todos os blocos type=text.
            $text = '';
            foreach ($response->json('content', []) as $block) {
                if (($block['type'] ?? null) === 'text') {
                    $text .= $block['text'];
                }
            }

            if ($text === '') {
                $stopReason = $response->json('stop_reason');
                if ($stopReason === 'refusal') {
                    throw new Exception('A Claude recusou responder a esta solicitação.');
                }
                throw new Exception('Resposta vazia da Claude API: ' . $response->body());
            }

            AiQuota::record($tenant?->id);

            return $jsonMode ? $this->stripJsonFences($text) : $text;
        } catch (Exception $e) {
            Log::error('Erro na consulta à Claude API: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove cercas de código markdown (```json ... ```) que o modelo às vezes
     * coloca em volta do JSON, para o json_decode do chamador não falhar.
     */
    private function stripJsonFences(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        return trim($text);
    }
}
