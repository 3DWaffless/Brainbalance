<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Classroom;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
{
    protected ?GeminiService $ai = null;

    public function __construct()
    {
        try {
            $this->ai = app(GeminiService::class);
        } catch (\Throwable $e) {
            $this->ai = null;
        }
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->isTeacher()) {
            $classIds = $user->ownedClasses->pluck('id');
        } else {
            $classIds = $user->joinedClasses->pluck('id');
        }

        $modules = Module::whereIn('class_id', $classIds)->latest()->get();
        return view('modules.index', compact('modules'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isTeacher(), 403);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'title'    => 'required|string|max:255',
            'file'     => 'required|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png|max:10240',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('modules', 'public');

        Module::create([
            'class_id'  => $request->class_id,
            'title'     => $request->title,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
        ]);

        return back()->with('success', 'Module uploaded successfully!');
    }

    /**
     * Serve the file directly to the browser for inline viewing (PDFs/Images/Docs).
     */
    public function view(Module $module)
    {
        $user = Auth::user();

        if ($user->isStudent() && !$user->joinedClasses->contains($module->class_id)) {
            abort(403, 'You are not enrolled in this class.');
        }

        if ($user->isTeacher() && !$user->ownedClasses->contains($module->class_id)) {
            abort(403, 'Unauthorized access to this module.');
        }

        $fullPath = Storage::disk('public')->path($module->file_path);

        if (!file_exists($fullPath)) {
            abort(404, 'Module file not found on server.');
        }

        $mimeType = Storage::disk('public')->mimeType($module->file_path) ?? 'application/pdf';

        return response()->file($fullPath, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $module->file_name . '"',
        ]);
    }

    public function download(Module $module)
    {
        $user = Auth::user();

        if ($user->isStudent() && !$user->joinedClasses->contains($module->class_id)) {
            abort(403);
        }

        if ($user->isTeacher() && !$user->ownedClasses->contains($module->class_id)) {
            abort(403);
        }

        return Storage::disk('public')->download($module->file_path, $module->file_name);
    }

    public function destroy(Module $module)
    {
        abort_unless(Auth::user()->isTeacher(), 403);
        abort_unless(Auth::user()->ownedClasses->contains($module->class_id), 403);

        Storage::disk('public')->delete($module->file_path);
        $module->delete();

        return back()->with('success', 'Module deleted successfully!');
    }

    /**
     * AI Study Assistant Handler for Module Guidance.
     */
    public function aiChat(Request $request, Module $module)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            if ($user->isStudent() && method_exists($user, 'joinedClasses') && $user->joinedClasses) {
                if (!$user->joinedClasses->contains($module->class_id)) {
                    return response()->json(['error' => 'You are not enrolled in this class.'], 403);
                }
            }

            $userPrompt = $request->input('prompt') ?? $request->input('message');
            if (empty($userPrompt)) {
                return response()->json(['error' => 'A message prompt is required.'], 422);
            }

            if (!$this->ai) {
                $this->ai = app(GeminiService::class);
            }

            $prompt = "You are BrainBalance AI, a friendly elementary school tutor. " .
                      "The student is asking a question about the study module titled '{$module->title}'. " .
                      "Student Question: \"{$userPrompt}\". " .
                      "Provide a simple, clear, encouraging answer suitable for young learners.";

            $reply = $this->ai->askQuestion($prompt);

            return response()->json(['reply' => $reply]);
        } catch (\Throwable $e) {
            Log::error("Gemini AI Chat Error: " . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'reply' => "⚠️ AI Error: " . $e->getMessage()
            ], 200);
        }
    }
}