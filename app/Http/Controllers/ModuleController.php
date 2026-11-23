<?php

namespace App\Http\Controllers;

use App\Models\Module;
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

    protected function activeDisk(): string
    {
        return config('filesystems.default') === 'supabase' ? 'supabase' : 'public';
    }

    protected function resolveDiskForPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $primary = $this->activeDisk();

        try {
            if (Storage::disk($primary)->fileExists($path)) {
                return $primary;
            }
        } catch (\Throwable $e) {
            Log::warning(
                "Storage check failed on [{$primary}] for [{$path}]: " . ($e->getPrevious()?->getMessage() ?? $e->getMessage())
            );
        }

        if ($primary !== 'public') {
            try {
                if (Storage::disk('public')->fileExists($path)) {
                    return 'public';
                }
            } catch (\Throwable $e) {
                // Ignore local fallback check errors
            }
        }

        return null;
    }

    protected function supabasePublicUrl(string $path): ?string
    {
        $endpoint = rtrim((string) config('filesystems.disks.supabase.endpoint'), '/');
        $bucket   = trim((string) config('filesystems.disks.supabase.bucket'), '/');

        if ($endpoint === '' || $bucket === '') {
            return null;
        }

        $publicBase = preg_replace('#/storage/v1/s3$#', '/storage/v1/object/public', $endpoint);
        return $publicBase . '/' . $bucket . '/' . ltrim($path, '/');
    }

    protected function detectMimeType(string $ext): string
    {
        return match (strtolower($ext)) {
            'pdf'         => 'application/pdf',
            'png'         => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            'svg'         => 'image/svg+xml',
            'mp3'         => 'audio/mpeg',
            'wav'         => 'audio/wav',
            'm4a'         => 'audio/mp4',
            'ogg'         => 'audio/ogg',
            'mp4'         => 'video/mp4',
            'webm'        => 'video/webm',
            'mov'         => 'video/quicktime',
            default       => 'application/octet-stream',
        };
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
            'file'     => 'required|file|max:51200',
        ]);

        if ($user->role !== 'admin') {
            abort_unless($user->ownedClasses->contains($request->class_id), 403);
        }

        $file = $request->file('file');
        $disk = $this->activeDisk();

        try {
            $filePath = $file->store('modules', $disk);
            if (!$filePath) {
                throw new \RuntimeException('Storage returned an empty path.');
            }
        } catch (\Throwable $e) {
            $reason = $e->getPrevious()?->getMessage() ?? $e->getMessage();
            return back()->withErrors(['file' => 'Upload failed (' . $disk . '): ' . $reason])->withInput();
        }

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

    public function view(Module $module)
    {
        $this->authorizeModuleAccess($module);

        $disk = $this->resolveDiskForPath($module->file_path);
        if (!$disk) {
            abort(404, 'Module file not found on server.');
        }

        $ext = strtolower($module->file_type ?: pathinfo((string) $module->file_name, PATHINFO_EXTENSION));
        $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'], true);

        if ($disk === 'supabase' && $isVideo && ($publicUrl = $this->supabasePublicUrl($module->file_path))) {
            return redirect()->away($publicUrl);
        }

        $contents = Storage::disk($disk)->get($module->file_path);

        return response($contents, 200, [
            'Content-Type'        => $this->detectMimeType($ext),
            'Content-Length'      => strlen($contents),
            'Content-Disposition' => 'inline; filename="' . addslashes($module->file_name) . '"',
        ]);
    }

    public function download(Module $module)
    {
        $this->authorizeModuleAccess($module);

        $disk = $this->resolveDiskForPath($module->file_path);
        if (!$disk) {
            abort(404, 'Module file not found on server.');
        }

        return Storage::disk($disk)->download($module->file_path, $module->file_name);
    }

    public function destroy(Module $module)
    {
        $user = Auth::user();
        abort_unless($user->isTeacher() || $user->role === 'admin', 403);

        if ($user->role !== 'admin') {
            abort_unless($user->ownedClasses->contains($module->class_id), 403);
        }

        $disk = $this->resolveDiskForPath($module->file_path);
        if ($disk) {
            Storage::disk($disk)->delete($module->file_path);
        }

        $module->delete();

        return back()->with('success', 'Module deleted successfully!');
    }

    public function aiChat(Request $request, Module $module)
    {
        $tempFile = null;

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

            $disk = $this->resolveDiskForPath($module->file_path);
            if (!$disk) {
                return response()->json(['reply' => '⚠️ File not found on storage server.'], 200);
            }

            if ($disk === 'public') {
                $fullPath = Storage::disk('public')->path($module->file_path);
            } else {
                $ext = strtolower($module->file_type ?: pathinfo((string) $module->file_name, PATHINFO_EXTENSION) ?: 'pdf');
                $tempBase = tempnam(sys_get_temp_dir(), 'bb_mod_');
                $tempFile = $tempBase . '.' . $ext;
                @unlink($tempBase);
                file_put_contents($tempFile, Storage::disk($disk)->get($module->file_path));
                $fullPath = $tempFile;
            }

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
        } finally {
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }
}