<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::with('topic')->latest()->get();
        $topics = Topic::all();

        return view('modules.index', compact('modules', 'topics'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403, 'Unauthorized action. Only teachers can upload modules.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'topic_id' => ['nullable', 'exists:topics,id'],
            'module_file' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip', 'max:10240'],
        ]);

        if ($request->hasFile('module_file')) {
            $file = $request->file('module_file');
            
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileSize = round($file->getSize() / 1024);
            
            $path = $file->store('modules', 'public');

            Module::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'topic_id' => $validated['topic_id'] ?? null,
                'file_path' => $path,
                'file_name' => $originalName,
                'file_size' => $fileSize,
                'file_type' => strtolower($extension),
            ]);
        }

        return redirect()->back()->with('status', 'module-uploaded');
    }

    // Serve file inline for browser embedding
    public function view(Module $module)
    {
        if (!Storage::disk('public')->exists($module->file_path)) {
            abort(404, 'File not found.');
        }

        $fullPath = storage_path('app/public/' . $module->file_path);

        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        $contentType = $mimeTypes[$module->file_type] ?? 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . $module->file_name . '"',
        ]);
    }

    public function download(Module $module)
    {
        if (!Storage::disk('public')->exists($module->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($module->file_path, $module->file_name);
    }

    public function destroy(Module $module)
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403, 'Unauthorized action. Only teachers can delete modules.');
        }

        if (Storage::disk('public')->exists($module->file_path)) {
            Storage::disk('public')->delete($module->file_path);
        }

        $module->delete();

        return redirect()->back()->with('status', 'module-deleted');
    }

    // Gemini AI Chat endpoint
    public function aiChat(Request $request, Module $module)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        $apiKey = env('GEMINI_API_KEY') ?? config('services.gemini.key');

        if (!$apiKey) {
            return response()->json([
                'reply' => "Gemini API Key is not set in `.env` (`GEMINI_API_KEY`). Please add it to start chatting!"
            ]);
        }

        $systemContext = "You are BrainBalance AI Study Assistant. The user is currently studying the module entitled: '{$module->title}'. Module description: '{$module->description}'. Answer the student concisely, supportively, and accurately to help them learn this topic.";

        try {
            $response = Http::withoutVerifying()->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $systemContext . "\n\nStudent Question: " . $request->prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error', ['body' => $response->body()]);
                $errorMsg = $response->json()['error']['message'] ?? 'API request failed.';
                return response()->json([
                    'reply' => "Gemini API Error ({$response->status()}): {$errorMsg}"
                ]);
            }

            $responseData = $response->json();
            $reply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not generate a response right now.';

            return response()->json(['reply' => $reply]);
        } catch (\Exception $e) {
            Log::error('Gemini Exception', ['message' => $e->getMessage()]);
            return response()->json(['reply' => 'Connection error: ' . $e->getMessage()], 500);
        }
    }
}