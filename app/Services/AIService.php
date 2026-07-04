<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private $apiKey;
    private $model;
    private $maxTokens;
    private $temperature;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->model = config('services.gemini.model', 'gemini-2.0-flash');
        $this->maxTokens = (int) config('services.gemini.max_tokens', 1000);
        $this->temperature = (float) config('services.gemini.temperature', 0.3);

        if (empty($this->apiKey) || $this->apiKey === 'sua-chave-aqui') {
            throw new Exception('GEMINI_API_KEY não configurada no arquivo .env');
        }
    }

    /**
     * Envia uma pergunta para a Gemini API (Google) e retorna a resposta.
     *
     * @param string $prompt O prompt a ser enviado
     * @param bool $jsonMode Se true, força a resposta em JSON (responseMimeType)
     * @return string A resposta da IA
     * @throws Exception Em caso de erro na API
     */
    public function query(string $prompt, bool $jsonMode = false): string
    {
        // Sanitizar o prompt para evitar problemas com JSON
        $prompt = mb_convert_encoding($prompt, 'UTF-8', 'UTF-8');
        $prompt = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $prompt);

        return $this->generateContent([
            ['text' => $prompt]
        ], $jsonMode);
    }

    /**
     * Envia um prompt acompanhado de um arquivo (inline base64) para a Gemini API.
     * Usado para extrair texto de PDFs sem depender de libs de parsing no servidor.
     *
     * @param string $prompt Instrução sobre o que fazer com o arquivo
     * @param string $filePath Caminho completo do arquivo
     * @param string $mimeType MIME type (ex: application/pdf)
     * @return string A resposta da IA
     * @throws Exception Em caso de erro na API ou arquivo inacessível
     */
    public function queryWithFile(string $prompt, string $filePath, string $mimeType): string
    {
        if (!file_exists($filePath)) {
            throw new Exception("Arquivo não encontrado: {$filePath}");
        }

        return $this->generateContent([
            ['inline_data' => [
                'mime_type' => $mimeType,
                'data' => base64_encode(file_get_contents($filePath)),
            ]],
            ['text' => $prompt],
        ], false, 8192, 90);
    }

    /**
     * Chamada base ao endpoint generateContent da Gemini API.
     */
    private function generateContent(array $parts, bool $jsonMode = false, ?int $maxTokens = null, int $timeout = 30): string
    {
        $tenant = auth()->user()?->tenant;
        AiQuota::assertAvailable($tenant);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        $generationConfig = [
            'temperature' => $this->temperature,
            'maxOutputTokens' => $maxTokens ?? $this->maxTokens,
        ];

        if ($jsonMode) {
            $generationConfig['responseMimeType'] = 'application/json';
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'x-goog-api-key' => $this->apiKey,
            ])
            ->timeout($timeout)
            ->post($url, [
                'contents' => [
                    ['role' => 'user', 'parts' => $parts]
                ],
                'generationConfig' => $generationConfig,
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json('error.message', $response->body());
                Log::error("Gemini API Error (HTTP {$response->status()}): {$errorMsg}");
                throw new Exception("Gemini API Error (HTTP {$response->status()}): {$errorMsg}");
            }

            $content = $response->json('candidates.0.content.parts.0.text');
            if (is_null($content)) {
                $blockReason = $response->json('promptFeedback.blockReason');
                if ($blockReason) {
                    throw new Exception("Resposta bloqueada pela Gemini API: {$blockReason}");
                }
                throw new Exception("Resposta inválida da Gemini API: " . $response->body());
            }

            AiQuota::record($tenant?->id);

            return $content;
        } catch (Exception $e) {
            Log::error("Erro na consulta à Gemini API: " . $e->getMessage());
            throw $e;
        }
    }
}
