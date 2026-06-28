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
        $this->apiKey = env('GEMINI_API_KEY');
        $this->model = env('GEMINI_MODEL', 'gemini-1.5-flash');
        $this->maxTokens = (int) env('GEMINI_MAX_TOKENS', 1000);
        $this->temperature = (float) env('GEMINI_TEMPERATURE', 0.3);

        if (empty($this->apiKey) || $this->apiKey === 'sua-chave-aqui') {
            throw new Exception('GEMINI_API_KEY não configurada no arquivo .env');
        }
    }

    /**
     * Envia uma pergunta para a Gemini API (Google) e retorna a resposta.
     *
     * @param string $prompt O prompt a ser enviado
     * @return string A resposta da IA
     * @throws Exception Em caso de erro na API
     */
    public function query(string $prompt): string
    {
        // Sanitizar o prompt para evitar problemas com JSON
        $prompt = mb_convert_encoding($prompt, 'UTF-8', 'UTF-8');
        $prompt = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $prompt);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'x-goog-api-key' => $this->apiKey,
            ])
            ->timeout(30)
            ->post($url, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => $this->temperature,
                    'maxOutputTokens' => $this->maxTokens,
                ],
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

            return $content;
        } catch (Exception $e) {
            Log::error("Erro na consulta à Gemini API: " . $e->getMessage());
            throw $e;
        }
    }
}
