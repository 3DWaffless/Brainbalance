<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use ZipArchive;
use Exception;

class GroqService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.key') ?? env('GROQ_API_KEY', '');
        $this->model  = config('services.groq.model') ?? env('GROQ_MODEL', 'llama-3.3-70b-versatile');
    }

    protected function client()
    {
        $client = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ]);

        if (app()->environment('local')) {
            $client->withoutVerifying();
        }

        return $client;
    }

    public function getAvailableModels(): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        return Cache::remember('groq_available_models_' . md5($this->apiKey), now()->addHours(24), function () {
            try {
                $response = $this->client()->timeout(10)->get('https://api.groq.com/openai/v1/models');
                if ($response->successful()) {
                    $data = $response->json('data') ?? [];
                    return array_column($data, 'id');
                }
            } catch (\Throwable $e) {
                Log::warning("Could not query Groq models endpoint: " . $e->getMessage());
            }

            return [];
        });
    }

    protected function resolveModel(): string
    {
        $available = $this->getAvailableModels();

        if (!empty($available) && in_array($this->model, $available)) {
            return $this->model;
        }

        $preferredList = [
            'llama-3.3-70b-versatile',
            'llama-3.1-8b-instant',
            'llama3-70b-8192',
            'llama3-8b-8192',
            'llama-3.1-70b-versatile',
            'mixtral-8x7b-32768',
            'gemma2-9b-it',
        ];

        foreach ($preferredList as $pref) {
            if (in_array($pref, $available)) {
                return $pref;
            }
        }

        if (!empty($available)) {
            foreach ($available as $modId) {
                if (!preg_match('/(whisper|guard|canopylabs|orpheus|tts|audio|vision)/i', $modId)) {
                    return $modId;
                }
            }
        }

        return 'llama-3.3-70b-versatile';
    }

    protected function resolveVisionModel(): string
    {
        $available = $this->getAvailableModels();

        $preferredVision = [
            'meta-llama/llama-4-scout-17b-16e-instruct',
            'meta-llama/llama-4-maverick-17b-128e-instruct',
            'llama-3.2-90b-vision-preview',
            'llama-3.2-11b-vision-preview',
        ];

        foreach ($preferredVision as $pref) {
            if (in_array($pref, $available)) {
                return $pref;
            }
        }

        foreach ($available as $modId) {
            if (preg_match('/(vision|llama-4)/i', $modId)) {
                return $modId;
            }
        }

        return 'meta-llama/llama-4-scout-17b-16e-instruct';
    }

    protected function resolveWhisperModel(): string
    {
        $available = $this->getAvailableModels();

        $preferredWhisper = [
            'whisper-large-v3-turbo',
            'whisper-large-v3',
            'distil-whisper-large-v3-en',
        ];

        foreach ($preferredWhisper as $pref) {
            if (in_array($pref, $available)) {
                return $pref;
            }
        }

        return 'whisper-large-v3-turbo';
    }

    public function askQuestion(string $prompt): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("GROQ_API_KEY is missing in your .env file.");
        }

        $activeModel = $this->resolveModel();

        $response = $this->client()->timeout(15)->post($this->apiUrl, [
            'model' => $activeModel,
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => 'You are BrainBalance AI, a friendly elementary school tutor. Provide simple, clear, encouraging answers suitable for young learners.'
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.5,
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json()['error']['message'] ?? $response->body();
            Log::error("Groq API Chat Error ({$response->status()}) [Model: {$activeModel}]: {$errorMsg}");
            throw new Exception("Groq API Error ({$response->status()}): {$errorMsg}");
        }

        return $response->json('choices.0.message.content') ?? "I reviewed your question, but couldn't generate a response.";
    }

    public function extractTextFromPdf(string $pdfFilePath): string
    {
        $text = '';

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfFilePath);
            $text = $pdf->getText();
        } catch (\Throwable $e) {
            Log::warning('Smalot PdfParser exception: ' . $e->getMessage());
        }

        if (mb_strlen(trim($text)) < 20 && file_exists($pdfFilePath)) {
            $rawContent = @file_get_contents($pdfFilePath);

            if ($rawContent !== false) {
                if (preg_match_all('/(?:\((.*?)\)\s*Tj|\[(.*?)\]\s*TJ)/s', $rawContent, $matches)) {
                    $rawExtracted = implode(' ', array_filter(array_merge($matches[1], $matches[2])));
                    $rawExtracted = preg_replace('/\\\([0-7]{1,3})/', ' ', $rawExtracted);
                    $rawExtracted = preg_replace('/\\\[\(\)]/', '', $rawExtracted);
                    if (mb_strlen(trim($rawExtracted)) > 20) {
                        $text .= " " . $rawExtracted;
                    }
                }

                if (mb_strlen(trim($text)) < 20 && preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $rawContent, $streams)) {
                    foreach ($streams[1] as $stream) {
                        $decompressed = @gzuncompress($stream);
                        $target = ($decompressed !== false) ? $decompressed : $stream;

                        if (preg_match_all('/[a-zA-Z0-9\x80-\xFF\s.,?!\'"\/\-]{4,}/', $target, $strMatches)) {
                            $text .= " " . implode(' ', $strMatches[0]);
                        }
                    }
                }
            }
        }

        return $this->normalizeToUtf8($text);
    }

    public function extractTextFromDocx(string $filePath): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new Exception('PHP ZipArchive extension is required to read Word (.docx) files.');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception('Unable to open the uploaded Word document.');
        }

        $xmlContent = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xmlContent === false) {
            throw new Exception('Could not read document text from the Word (.docx) file.');
        }

        $xmlContent = str_replace(['</w:p>', '</w:tr>', '<w:br/>'], ["\n", "\n", ' '], $xmlContent);
        $plainText = strip_tags($xmlContent);

        return $this->normalizeToUtf8($plainText);
    }

    public function extractTextFromPptx(string $filePath): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new Exception('PHP ZipArchive extension is required to read PowerPoint (.pptx) files.');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception('Unable to open the uploaded PowerPoint file.');
        }

        $slidesText = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (preg_match('#^ppt/slides/slide\d+\.xml$#', $entryName)) {
                $xml = $zip->getFromIndex($i);
                if ($xml !== false) {
                    $xml = str_replace(['</a:p>', '<a:br/>'], ["\n", ' '], $xml);
                    $slidesText[] = strip_tags($xml);
                }
            }
        }
        $zip->close();

        return $this->normalizeToUtf8(implode("\n", $slidesText));
    }

    public function transcribeAudioOrVideo(string $filePath, string $originalFilename): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("GROQ_API_KEY is missing in your .env file.");
        }

        $whisperModel = $this->resolveWhisperModel();

        $whisperClient = Http::withToken($this->apiKey)->timeout(90);
        if (app()->environment('local')) {
            $whisperClient->withoutVerifying();
        }

        $response = $whisperClient->attach(
            'file',
            file_get_contents($filePath),
            $originalFilename
        )->post('https://api.groq.com/openai/v1/audio/transcriptions', [
            'model'           => $whisperModel,
            'response_format' => 'text',
        ]);

        if ($response->failed()) {
            if ($response->status() === 413) {
                throw new Exception("The audio/video file exceeds Groq's transcription upload limit (25MB on free tier). Please upload a smaller or compressed file.");
            }
            $errorMsg = $response->json()['error']['message'] ?? $response->body();
            Log::error("Groq Whisper Transcription Error ({$response->status()}) [Model: {$whisperModel}]: {$errorMsg}");
            throw new Exception("Audio/Video Transcription Error ({$response->status()}): {$errorMsg}");
        }

        return $this->normalizeToUtf8($response->body());
    }

    protected function normalizeToUtf8(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $detected = mb_detect_encoding($text, ['UTF-8', 'UTF-16', 'UTF-16BE', 'UTF-16LE', 'ASCII', 'ISO-8859-1', 'Windows-1252', 'SJIS', 'EUC-JP'], true);

        if ($detected && $detected !== 'UTF-8') {
            $converted = @mb_convert_encoding($text, 'UTF-8', $detected);
            if ($converted !== false && !empty($converted)) {
                $text = $converted;
            }
        }

        if (function_exists('mb_scrub')) {
            $text = mb_scrub($text, 'UTF-8');
        } else {
            $text = @iconv('UTF-8', 'UTF-8//IGNORE', $text) ?: $text;
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $text);
        
        if ($cleaned !== null) {
            $text = $cleaned;
        }

        $text = preg_replace('/\$([^\$]+)\$/', '$1', $text);
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    protected function cachedPdfText(string $pdfFilePath): string
    {
        if (empty($pdfFilePath) || !file_exists($pdfFilePath)) {
            return '';
        }

        $key = 'pdf_text_' . md5($pdfFilePath . '|' . filemtime($pdfFilePath));

        return Cache::remember($key, now()->addHours(6), function () use ($pdfFilePath) {
            return $this->extractTextFromPdf($pdfFilePath);
        });
    }

    public function askQuestionAboutPdf(
        string $pdfFilePath,
        string $moduleTitle,
        string $userPrompt,
        array $history = []
    ): string {
        if (empty($this->apiKey)) {
            throw new Exception("GROQ_API_KEY is missing in your .env file.");
        }

        $documentText = mb_substr($this->cachedPdfText($pdfFilePath), 0, 8000);

        $system = "You are BrainBalance AI, a friendly elementary school tutor.\n" .
                  "The student is reading a study module titled '{$moduleTitle}'.\n" .
                  "Answer using the module content below whenever it is relevant, and say so if the answer is not in the module. " .
                  "Keep explanations simple, clear and encouraging for young learners.";

        if ($documentText !== '') {
            $system .= "\n\n--- MODULE CONTENT START ---\n{$documentText}\n--- MODULE CONTENT END ---";
        } else {
            $system .= "\n\n(No readable text could be extracted from this file, so answer from general knowledge and tell the student you could not read the document.)";
        }

        $messages = [['role' => 'system', 'content' => $system]];
        foreach ($history as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $userPrompt];

        $activeModel = $this->resolveModel();

        $response = $this->client()->timeout(25)->post($this->apiUrl, [
            'model'       => $activeModel,
            'messages'    => $messages,
            'temperature' => 0.4,
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json()['error']['message'] ?? $response->body();
            Log::error("Groq API Module Chat Error ({$response->status()}) [Model: {$activeModel}]: {$errorMsg}");
            throw new Exception("Groq API Error ({$response->status()}): {$errorMsg}");
        }

        return $response->json('choices.0.message.content') ?? "I reviewed your question, but couldn't generate a response.";
    }

    public function generateQuizFromFile(
        string $filePath,
        string $originalFilename,
        string $subject = 'Math',
        string $gradeLevel = 'Grade 3',
        int $itemCount = 5,
        string $questionType = 'multiple_choice',
        array $typeCounts = [],
        string $difficulty = 'mixed'
    ): array {
        if (empty($this->apiKey)) {
            throw new Exception("GROQ_API_KEY is missing in your .env file.");
        }

        $cleanTypeCounts = [];
        foreach ($typeCounts as $tKey => $tVal) {
            $cnt = max(0, (int) $tVal);
            if ($cnt > 0) {
                $cleanTypeCounts[$tKey] = $cnt;
            }
        }

        if (!empty($cleanTypeCounts)) {
            $itemCount = array_sum($cleanTypeCounts);
        }

        $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION) ?: pathinfo($filePath, PATHINFO_EXTENSION));

        // 1. Photos / Worksheet Images -> Groq Vision
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $this->generateQuizFromImage($filePath, $ext, $subject, $gradeLevel, $itemCount, $questionType, $cleanTypeCounts, $difficulty);
        }

        // 2. Audio & Video -> Groq Whisper Transcription -> Text Quiz Generator
        if (in_array($ext, ['mp3', 'wav', 'm4a', 'ogg', 'flac', 'mp4', 'webm', 'mpeg', 'mpga'], true)) {
            $transcript = $this->transcribeAudioOrVideo($filePath, $originalFilename);
            if (empty($transcript) || mb_strlen($transcript) < 15) {
                throw new Exception('No clear spoken words could be transcribed from the uploaded audio/video file.');
            }
            return $this->generateQuizFromText($transcript, $subject, $gradeLevel, $itemCount, $questionType, $cleanTypeCounts, $difficulty);
        }

        // 3. Documents & Text Files
        $extractedText = match ($ext) {
            'pdf'              => $this->cachedPdfText($filePath),
            'docx'             => $this->extractTextFromDocx($filePath),
            'pptx'             => $this->extractTextFromPptx($filePath),
            'txt', 'md', 'csv' => $this->normalizeToUtf8((string) @file_get_contents($filePath)),
            'doc'              => throw new Exception('Legacy .doc files are not supported. Please save your Word document as .docx and try again.'),
            default            => throw new Exception("Unsupported file format (.{$ext}) for AI quiz generation."),
        };

        if (empty($extractedText) || mb_strlen($extractedText) < 15) {
            throw new Exception('The uploaded file contains no readable text. Please ensure the file has text content.');
        }

        return $this->generateQuizFromText($extractedText, $subject, $gradeLevel, $itemCount, $questionType, $cleanTypeCounts, $difficulty);
    }

    public function generateQuizFromPdf(
        string $pdfFilePath,
        string $subject = 'Math',
        string $gradeLevel = 'Grade 3',
        int $itemCount = 5,
        string $questionType = 'multiple_choice'
    ): array {
        return $this->generateQuizFromFile($pdfFilePath, 'module.pdf', $subject, $gradeLevel, $itemCount, $questionType);
    }

    protected function generateQuizFromImage(
        string $imagePath,
        string $ext,
        string $subject,
        string $gradeLevel,
        int $itemCount,
        string $questionType,
        array $typeCounts = [],
        string $difficulty = 'mixed'
    ): array {
        $mimeType = match ($ext) {
            'png'   => 'image/png',
            'webp'  => 'image/webp',
            default => 'image/jpeg',
        };

        $imageData = @file_get_contents($imagePath);
        if ($imageData === false) {
            throw new Exception('Unable to read the uploaded image file.');
        }

        $base64Url = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        $prompt = $this->buildQuizPrompt('(See attached lesson/worksheet image)', $subject, $gradeLevel, $itemCount, $questionType, $typeCounts, $difficulty);
        $visionModel = $this->resolveVisionModel();

        $response = $this->client()->timeout(60)->post($this->apiUrl, [
            'model' => $visionModel,
            'messages' => [
                [
                    'role'    => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        ['type' => 'image_url', 'image_url' => ['url' => $base64Url]],
                    ],
                ],
            ],
            'temperature' => 0.2,
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json()['error']['message'] ?? $response->body();
            Log::error("Groq Vision Quiz Error ({$response->status()}) [Model: {$visionModel}]: {$errorMsg}");
            throw new Exception("Groq Vision Error ({$response->status()}): {$errorMsg}");
        }

        $rawContent = $response->json('choices.0.message.content') ?? '{}';
        return $this->parseQuizJsonResponse($rawContent, $itemCount);
    }

    protected function generateQuizFromText(
        string $sourceText,
        string $subject,
        string $gradeLevel,
        int $itemCount,
        string $questionType,
        array $typeCounts = [],
        string $difficulty = 'mixed'
    ): array {
        $truncatedText = mb_substr($sourceText, 0, 12000);

        $systemPrompt = "You are an AI educational assistant. Generate EXACTLY {$itemCount} questions based ONLY on the provided lesson content. You must return a JSON object containing an array of EXACTLY {$itemCount} question objects. Do not stop until you have generated all {$itemCount} questions.";
        $userPrompt   = $this->buildQuizPrompt($truncatedText, $subject, $gradeLevel, $itemCount, $questionType, $typeCounts, $difficulty);
        $activeModel  = $this->resolveModel();

        $response = $this->client()->timeout(50)->post($this->apiUrl, [
            'model' => $activeModel,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.2,
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json()['error']['message'] ?? $response->body();
            Log::error("Groq API Quiz Error ({$response->status()}) [Model: {$activeModel}]: {$errorMsg}");
            throw new Exception("Groq API Error ({$response->status()}): {$errorMsg}");
        }

        $rawContent = $response->json('choices.0.message.content') ?? '{}';
        return $this->parseQuizJsonResponse($rawContent, $itemCount);
    }

    protected function buildQuizPrompt(
        string $lessonContent,
        string $subject,
        string $gradeLevel,
        int $itemCount,
        string $questionType,
        array $typeCounts = [],
        string $difficulty = 'mixed'
    ): string {
        $diffInstruction = match (strtolower($difficulty)) {
            'easy'   => "All questions MUST have \"difficulty_level\": \"easy\".",
            'medium' => "All questions MUST have \"difficulty_level\": \"medium\".",
            'hard'   => "All questions MUST have \"difficulty_level\": \"hard\".",
            default  => "Distribute \"difficulty_level\" evenly across \"easy\", \"medium\", and \"hard\".",
        };

        if (!empty($typeCounts)) {
            $breakdownLines = [];
            foreach ($typeCounts as $tName => $cnt) {
                $breakdownLines[] = "- EXACTLY {$cnt} question(s) of type \"{$tName}\"";
            }
            $breakdownText = implode("\n", $breakdownLines);

            return "Subject: {$subject}\n" .
                "Grade Level: {$gradeLevel}\n" .
                "Total Questions Required: {$itemCount}\n\n" .
                "REQUIRED QUESTION TYPE BREAKDOWN:\n{$breakdownText}\n\n" .
                "Lesson Content:\n{$lessonContent}\n\n" .
                "SCHEMA RULES PER QUESTION TYPE:\n" .
                "- \"multiple_choice\": \"options\" has 4 choices; \"correct_answer\" is the exact string of the 1 right choice; \"correct_answers\" is [\"exact right choice\"]; \"matching_pairs\" is [].\n" .
                "- \"multiple_select\": \"options\" has 4-5 choices with at least 2 right answers; \"correct_answers\" is an array of ALL right option strings; \"correct_answer\" is the first right choice; \"matching_pairs\" is [].\n" .
                "- \"true_false\": \"options\" is [\"True\", \"False\"]; \"correct_answer\" is \"True\" or \"False\"; \"correct_answers\" is [\"True\" or \"False\"]; \"matching_pairs\" is [].\n" .
                "- \"matching\": \"matching_pairs\" has 3-4 objects [{\"left\": \"Item A\", \"right\": \"Match A\"}]; \"options\" is []; \"correct_answer\" is \"\"; \"correct_answers\" is [].\n" .
                "- \"fill_blank\": \"question_text\" MUST contain ___ where each blank goes; \"correct_answers\" is an array of exact answer strings matching each ___ in order; \"options\" is []; \"matching_pairs\" is [].\n" .
                "- \"short_answer\": \"options\" is []; \"correct_answer\" is a concise expected phrase; \"correct_answers\" is [\"concise expected phrase\"]; \"matching_pairs\" is [].\n" .
                "- \"essay\": \"options\" is []; \"correct_answer\" is a short grading rubric; \"correct_answers\" is [\"short grading rubric\"]; \"matching_pairs\" is [].\n\n" .
                "CRITICAL REQUIREMENTS:\n" .
                "1. Generate EXACTLY {$itemCount} questions matching the exact counts per type above.\n" .
                "2. {$diffInstruction}\n\n" .
                "Return ONLY a valid JSON object matching this schema:\n" .
                "{\n" .
                "  \"questions\": [\n" .
                "    {\n" .
                "      \"question_text\": \"Question prompt here\",\n" .
                "      \"type\": \"multiple_choice\",\n" .
                "      \"options\": [\"Option 1\", \"Option 2\", \"Option 3\", \"Option 4\"],\n" .
                "      \"correct_answer\": \"Option 1\",\n" .
                "      \"correct_answers\": [\"Option 1\"],\n" .
                "      \"matching_pairs\": [],\n" .
                "      \"difficulty_level\": \"easy\"\n" .
                "    }\n" .
                "  ]\n" .
                "}";
        }

        $typeInstruction = match ($questionType) {
            'identification', 'short_answer' => "All {$itemCount} questions MUST be direct short answer questions with \"type\": \"short_answer\". Set \"options\" to []. \"correct_answer\" must be a short, clear phrase or single word.",
            default          => "All {$itemCount} questions MUST be multiple-choice with \"type\": \"multiple_choice\". Populate \"options\" with exactly 4 choices. \"correct_answer\" MUST match one of the choices in \"options\" exactly."
        };

        return "Subject: {$subject}\n" .
            "Grade Level: {$gradeLevel}\n" .
            "Target Item Count: {$itemCount}\n" .
            "Question Format: {$questionType}\n\n" .
            "Lesson Content:\n{$lessonContent}\n\n" .
            "CRITICAL REQUIREMENTS:\n" .
            "1. Generate EXACTLY {$itemCount} questions. The 'questions' array MUST contain {$itemCount} elements.\n" .
            "2. {$typeInstruction}\n" .
            "3. {$diffInstruction}\n\n" .
            "Return ONLY a valid JSON object matching this exact schema (no markdown or extra commentary):\n" .
            "{\n" .
            "  \"questions\": [\n" .
            "    {\n" .
            "      \"question_text\": \"Question string here\",\n" .
            "      \"type\": \"{$questionType}\",\n" .
            "      \"options\": " . (in_array($questionType, ['identification', 'short_answer'], true) ? "[]" : "[\"Option 1\", \"Option 2\", \"Option 3\", \"Option 4\"]") . ",\n" .
            "      \"correct_answer\": \"Correct answer string\",\n" .
            "      \"correct_answers\": [\"Correct answer string\"],\n" .
            "      \"matching_pairs\": [],\n" .
            "      \"difficulty_level\": \"easy\"\n" .
            "    }\n" .
            "  ]\n" .
            "}";
    }

    protected function parseQuizJsonResponse(string $rawText, int $itemCount): array
    {
        $jsonText = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($rawText));
        $decoded  = json_decode($jsonText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $start = strpos($jsonText, '{');
            $end   = strrpos($jsonText, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $decoded = json_decode(substr($jsonText, $start, $end - $start + 1), true);
            }
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON returned by Groq AI: " . json_last_error_msg());
        }

        $questionsList = [];
        if (isset($decoded['questions']) && is_array($decoded['questions'])) {
            $questionsList = $decoded['questions'];
        } elseif (is_array($decoded) && isset($decoded[0]['question_text'])) {
            $questionsList = $decoded;
        }

        if (empty($questionsList)) {
            throw new Exception("AI response structure did not contain valid question objects.");
        }

        return array_slice($questionsList, 0, $itemCount);
    }

    public function analyzeStudentStruggles(array $wrongQuestionsData, ?string $studentName = null): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("GROQ_API_KEY is missing in your .env file.");
        }

        if (empty($wrongQuestionsData)) {
            return "Great news! All questions were answered correctly. No learning gaps were detected.";
        }

        $activeModel = $this->resolveModel();
        $targetAudience = $studentName ? "for student '{$studentName}'" : "for the class";

        $prompt = "You are BrainBalance AI, a helpful elementary school teaching assistant.\n" .
                  "Review these questions that were answered INCORRECTLY {$targetAudience}:\n\n" .
                  json_encode($wrongQuestionsData, JSON_PRETTY_PRINT) . "\n\n" .
                  "Give a brief, plain-language summary for the teacher using ONLY these 3 short sections:\n\n" .
                  "**Topics Missed**\n" .
                  "• 1–2 short bullet points naming the exact concepts struggled with.\n\n" .
                  "**Why It Happened**\n" .
                  "• 1–2 short bullet points explaining the likely mistake in simple terms.\n\n" .
                  "**Next Steps**\n" .
                  "• 2 quick, practical classroom tips or practice ideas to help.\n\n" .
                  "STRICT FORMATTING RULES:\n" .
                  "- Keep the entire response under 130 words.\n" .
                  "- NEVER use tables, grids, pipes (|), or + and - border lines.\n" .
                  "- Use simple bullet points (•) and plain sentences only.";

        $response = $this->client()->timeout(25)->post($this->apiUrl, [
            'model' => $activeModel,
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => 'You are BrainBalance AI. Write short, simple, encouraging teacher summaries using bullet points only. Never draw tables or ASCII grids.'
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.2,
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json()['error']['message'] ?? $response->body();
            Log::error("Groq API Struggle Analysis Error ({$response->status()}): {$errorMsg}");
            throw new Exception("Groq API Error ({$response->status()}): {$errorMsg}");
        }

        $content = $response->json('choices.0.message.content') ?? "Could not generate diagnostic analysis.";
        $content = preg_replace('/^[+\-|=\s]{4,}$/m', '', $content);

        return trim($content);
    }
}