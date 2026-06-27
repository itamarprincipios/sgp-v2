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
        $this->apiKey = env('OPENAI_API_KEY');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');
        $this->maxTokens = (int) env('OPENAI_MAX_TOKENS', 1000);
        $this->temperature = (float) env('OPENAI_TEMPERATURE', 0.3);

        if (empty($this->apiKey) || $this->apiKey === 'sua-chave-aqui') {
            throw new Exception('OPENAI_API_KEY não configurada no arquivo .env');
        }
    }

    /**
     * Envia uma pergunta para a OpenAI e retorna a resposta.
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

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
            ])
            ->withToken($this->apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json('error.message', $response->body());
                Log::error("OpenAI API Error (HTTP {$response->status()}): {$errorMsg}");
                throw new Exception("OpenAI API Error (HTTP {$response->status()}): {$errorMsg}");
            }

            $content = $response->json('choices.0.message.content');
            if (is_null($content)) {
                throw new Exception("Resposta inválida da OpenAI: " . $response->body());
            }

            return $content;
        } catch (Exception $e) {
            Log::error("Erro na consulta à OpenAI: " . $e->getMessage());
            throw $e;
        }
    }
}
