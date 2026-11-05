<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiService
{
    protected string $apiKey;
    protected string $model = 'gemini-3.8-flash';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY', '');
    }

    /**
     * General text-prompt Q&A method for AI Study Assistant.
     */
    public function askQuestion(string $prompt): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("GEMINI_API_KEY is missing in your .env file.");
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        // Extended timeout to 60 seconds to prevent local cURL 28 timeouts
        $response = Http::withoutVerifying()
            ->timeout(60)
            ->connectTimeout(15)
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.4
                ]
            ]);

        if ($response->failed()) {
            $errorMsg = $response->json()['error']['message'] ?? $response->body();
            Log::error("Gemini API Error ({$response->status()}): {$errorMsg}");
            throw new Exception("Gemini API Error {$response->status()}: {$errorMsg}");
        }

        $data = $response->json();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? "I reviewed your question, but couldn't generate a response.";
    }

    /**
     * Generate quiz questions by sending raw PDF data directly to Gemini.
     */
    public function generateQuizFromPdf(string $pdfBase64, string $subject = 'Math', string $gradeLevel = 'Grade 3', int $itemCount = 5): array
    {
        if (empty($this->apiKey)) {
            throw new Exception("GEMINI_API_KEY is missing in your .env file.");
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $systemPrompt = "You are an AI educational assistant. Generate {$itemCount} multiple-choice questions based ONLY on the provided PDF document. Ensure questions match the target subject and grade level.";

        $userPrompt = "Subject: {$subject}\nGrade Level: {$gradeLevel}\n\n" .
            "Return ONLY a JSON array with no markdown blocks or extra commentary outside the JSON array, using this format:\n" .
            "[\n" .
            "  {\n" .
            "    \"question_text\": \"Question statement here\",\n" .
            "    \"options\": [\"Option A\", \"Option B\", \"Option C\", \"Option D\"],\n" .
            "    \"correct_answer\": \"Option A\",\n" .
            "    \"difficulty_level\": \"Easy\"\n" .
            "  }\n" .
            "]";

        try {
            $response = Http::withoutVerifying()
                ->timeout(180)
                ->connectTimeout(30)
                ->post($url, [
                    'system_instruction' => [
                        'parts' => [['text' => $systemPrompt]]
                    ],
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $userPrompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => 'application/pdf',
                                        'data'      => $pdfBase64
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'response_mime_type' => 'application/json',
                        'temperature'        => 0.2
                    ]
                ]);

            if ($response->failed()) {
                Log::error("Gemini API PDF Quiz Failed ({$response->status()}): " . $response->body());
                return [];
            }

            $jsonText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
            
            return json_decode(trim($jsonText), true) ?? [];

        } catch (Exception $e) {
            Log::error('System Error in GeminiService: ' . $e->getMessage());
            return [];
        }
    }
}