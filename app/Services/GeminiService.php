<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class GeminiService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY', '');
    }

    /**
     * Generate quiz questions from text content using Google Gemini.
     */
    public function generateQuizFromText(string $text, string $subject = 'Math', string $gradeLevel = 'Grade 3', int $itemCount = 5): array
    {
        $systemPrompt = "You are an AI educational assistant for elementary students (Grades 1-6). Generate {$itemCount} multiple-choice questions based ONLY on the provided text. Ensure questions match the target subject and grade level.";

        $userPrompt = "Subject: {$subject}\nGrade Level: {$gradeLevel}\n\nPDF Content:\n{$text}\n\n" .
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
            $response = Http::post("{$this->apiUrl}?key={$this->apiKey}", [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemPrompt]
                    ]
                ],
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $userPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                    'temperature' => 0.2
                ]
            ]);

            if ($response->failed()) {
                throw new Exception('Gemini API Error: ' . $response->body());
            }

            $jsonText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
            
            return json_decode(trim($jsonText), true) ?? [];

        } catch (Exception $e) {
            logger()->error('Gemini Quiz Generation Error: ' . $e->getMessage());
            return [];
        }
    }
}