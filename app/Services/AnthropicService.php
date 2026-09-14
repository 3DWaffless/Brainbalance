<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AnthropicService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key');
        $this->model  = config('services.anthropic.model');

        if (empty($this->apiKey)) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not set in .env');
        }
    }

    public function ask(string $systemPrompt, string $userMessage, int $maxTokens = 1024): string
    {
        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post($this->baseUrl, [
            'model'      => $this->model,
            'max_tokens' => $maxTokens,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        if ($response->failed()) {
            Log::error('Anthropic API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException('Anthropic API request failed: ' . $response->status());
        }

        $data = $response->json();

        return $data['content'][0]['text'] ?? '';
    }

    public function askJson(string $systemPrompt, string $userMessage, int $maxTokens = 1024): array
    {
        $text = $this->ask(
            $systemPrompt . "\n\nRespond with ONLY valid JSON. No preamble, no markdown code fences, no explanation.",
            $userMessage,
            $maxTokens
        );

        $clean = preg_replace('/^```json\s*|\s*```$/', '', trim($text));

        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Failed to decode Anthropic JSON response', ['raw' => $text]);
            throw new RuntimeException('Anthropic returned invalid JSON');
        }

        return $decoded;
    }
}