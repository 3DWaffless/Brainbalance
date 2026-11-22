<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Classroom;
use App\Services\GroqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
{
    protected ?GroqService $ai = null;

    public function __construct()
    {
        try {
            $this->ai = app(GroqService::class);
        } catch (\Throwable $e) {
            $this->ai = null;
        }
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $modules = Module::latest()->get();
        } elseif ($user->isTeacher()) {
            $classIds = $user->ownedClasses->pluck('id');
            $modules = Module::whereIn('class_id', $classIds)->latest()->get();
        } else {
            $classIds = $user->joinedClasses->pluck('id');
            $modules = Module::whereIn('class_id', $classIds)->latest()->get();
        }

        return view('modules.index', compact('modules'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isTeacher() || $user->role === 'admin', 403);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'title'    => 'required|string|max:255',
            'file'     => 'required|file|max:102400',
        ]);

        if ($user->role !== 'admin') {
            abort_unless($user->ownedClasses->contains($request->class_id), 403);
        }

        $file = $request->file('file');
        $filePath = $file->store('modules', 'public');

        Module::create([
            'class_id'  => $request->class_id,
            'title'     => $request->title,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => strtolower($file->getClientOriginalExtension() ?: 'file'),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', 'Module uploaded successfully!');
    }

    /**
     * Split-screen reader: document on the left, AI study assistant on the right.
     */
    public function read(Module $module)
    {
        $this->authorizeModuleAccess($module);

        return view('modules.read', compact('module'));
    }

    protected function authorizeModuleAccess(Module $module): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        if ($user->isStudent() && !$user->joinedClasses->contains($module->class_id)) {
            abort(403, 'You are not enrolled in this class.');
        }

        if ($user->isTeacher() && !$user->ownedClasses->contains($module->class_id)) {
            abort(403, 'Unauthorized access to this module.');
        }
    }

    /**
     * Serve the file directly to the browser for inline viewing.
     */
    public function view(Module $module)
    {
        $this->authorizeModuleAccess($module);

        $fullPath = Storage::disk('public')->path($module->file_path);

        if (!file_exists($fullPath)) {
            abort(404, 'Module file not found on server.');
        }

        $mimeType = Storage::disk('public')->mimeType($module->file_path) ?? 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $module->file_name . '"',
        ]);
    }

    public function download(Module $module)
    {
        $this->authorizeModuleAccess($module);

        return Storage::disk('public')->download($module->file_path, $module->file_name);
    }

    public function destroy(Module $module)
    {
        $user = Auth::user();
        abort_unless($user->isTeacher() || $user->role === 'admin', 403);

        if ($user->role !== 'admin') {
            abort_unless($user->ownedClasses->contains($module->class_id), 403);
        }

        Storage::disk('public')->delete($module->file_path);
        $module->delete();

        return back()->with('success', 'Module deleted successfully!');
    }

    /**
     * Groq AI Study Assistant Handler with PDF Document Context.
     */
    public function aiChat(Request $request, Module $module)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            if ($user->role !== 'admin') {
                if ($user->isStudent() && !$user->joinedClasses->contains($module->class_id)) {
                    return response()->json(['error' => 'You are not enrolled in this class.'], 403);
                }

                if ($user->isTeacher() && !$user->ownedClasses->contains($module->class_id)) {
                    return response()->json(['error' => 'Unauthorized access to this module.'], 403);
                }
            }

            $userPrompt = $request->input('prompt') ?? $request->input('message');
            if (empty($userPrompt)) {
                return response()->json(['error' => 'A message prompt is required.'], 422);
            }

            if (!$this->ai) {
                $this->ai = app(GroqService::class);
            }

            $fullPath = Storage::disk('public')->path($module->file_path);

            // Recent chat turns so follow-up questions ("explain that again") work.
            $history = collect($request->input('history', []))
                ->filter(fn ($m) => is_array($m)
                    && in_array($m['role'] ?? null, ['user', 'assistant'], true)
                    && is_string($m['content'] ?? null))
                ->map(fn ($m) => [
                    'role'    => $m['role'],
                    'content' => mb_substr($m['content'], 0, 1500),
                ])
                ->take(-6)
                ->values()
                ->all();

            $reply = $this->ai->askQuestionAboutPdf(
                $fullPath,
                $module->title,
                $userPrompt,
                $history
            );

            return response()->json(['reply' => $reply]);
        } catch (\Throwable $e) {
            Log::error("Groq AI Chat Error: " . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'reply' => "⚠️ AI Error: " . $e->getMessage()
            ], 200);
        }
    }
}