<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class GeminiService
{
    protected string $apiKey;
    // CORRECT MODEL: gemini-3.8-flash
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY', '');
    }

    /**
     * Generate quiz questions by sending the raw PDF directly to Gemini.
     */
    public function generateQuizFromPdf(string $pdfBase64, string $subject = 'Math', string $gradeLevel = 'Grade 3', int $itemCount = 5): array
    {
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
            // By-pass SSL, increase timeout to 3 minutes, and make the request
            $response = Http::withoutVerifying()->timeout(180)->post("{$this->apiUrl}?key={$this->apiKey}", [
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
                    'temperature' => 0.2
                ]
            ]);

            if ($response->failed()) {
                // TEMPORARY: Show exact error from Google
                dd('Google API Error: ' . $response->body());
            }

            $jsonText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
            
            return json_decode(trim($jsonText), true) ?? [];

        } catch (Exception $e) {
            // TEMPORARY: Show exact system error
            dd('System Error in GeminiService: ' . $e->getMessage());
        }
    }
}