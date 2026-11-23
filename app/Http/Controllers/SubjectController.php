<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SubjectController extends Controller
{
    protected function activeDisk(): string
    {
        if (config('filesystems.default') === 'supabase' || filled(config('filesystems.disks.supabase.key'))) {
            return 'supabase';
        }

        if (app()->environment('production')) {
            throw new \RuntimeException('SUPABASE_S3_KEY is not configured in Render Environment Variables.');
        }

        return 'public';
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
            $classes = Classroom::withCount('students')->latest()->get();
        } elseif ($user->isTeacher()) {
            $classes = Classroom::where('teacher_id', $user->id)->withCount('students')->latest()->get();
        } else {
            $classes = $user->joinedClasses()->withCount('students')->latest()->get();
        }

        return view('subjects.index', compact('classes'));
    }

    public function show(Classroom $classroom)
    {
        $user = Auth::user();

        if ($user->isTeacher() && $classroom->teacher_id !== $user->id) {
            abort(403);
        }

        if ($user->isStudent() && !$user->joinedClasses->contains($classroom->id)) {
            abort(403);
        }

        $relations = [
            'teacher',
            'students',
            'modules',
            'announcements.teacher',
        ];

        if (Schema::hasColumn('announcements', 'quiz_id')) {
            $relations[] = 'announcements.quiz.questions';
        }
        if (Schema::hasColumn('announcements', 'module_id')) {
            $relations[] = 'announcements.module';
        }
        if (Schema::hasTable('assignment_submissions')) {
            $relations[] = 'announcements.submissions.student';
        }

        if ($user->isStudent()) {
            $relations['quizzes'] = fn ($q) => $q->whereNotNull('published_at')->with('questions')->latest();
            $classroom->load($relations);
            $classroom->setRelation(
                'quizzes',
                $classroom->quizzes->filter(fn ($q) => $q->isPublished())->values()
            );
        } else {
            $relations[] = 'quizzes.questions';
            $classroom->load($relations);
        }

        return view('subjects.show', compact('classroom'));
    }

    public function storeAnnouncement(Request $request, Classroom $classroom)
    {
        $user = Auth::user();
        abort_unless(($user->isTeacher() && $classroom->teacher_id === $user->id) || $user->role === 'admin', 403);

        $type = $request->input('type', 'text');

        $rules = [
            'type'    => 'required|in:text,file,video,quiz,assignment',
            'title'   => 'nullable|string|max:255',
            'content' => 'nullable|string|max:5000',
        ];

        if ($type === 'text') {
            $rules['content'] = 'required|string|max:5000';
        } elseif ($type === 'file') {
            $rules['title'] = 'required|string|max:255';
            $rules['file']  = 'required|file|max:51200';
        } elseif ($type === 'video') {
            $rules['title']      = 'required|string|max:255';
            $rules['video_file'] = 'nullable|file|mimes:mp4,webm,ogg,mov,avi,mkv|max:51200';
            $rules['video_url']  = 'nullable|url|max:1000';
        } elseif ($type === 'quiz') {
            $rules['quiz_id'] = 'required|exists:quizzes,id';
        } elseif ($type === 'assignment') {
            $rules['title']   = 'required|string|max:255';
            $rules['content'] = 'required|string|max:5000';
            $rules['due_at']  = 'required|date';
            $rules['points']  = 'nullable|integer|min:1|max:1000';
            $rules['file']    = 'nullable|file|max:51200';
        }

        $validated = $request->validate($rules);

        if ($type === 'video' && !$request->hasFile('video_file') && empty(trim((string) $request->video_url))) {
            return back()->withErrors(['video_url' => 'Please upload a video file or paste a video link.'])->withInput();
        }

        $disk = $this->activeDisk();

        $data = [
            'class_id'   => $classroom->id,
            'teacher_id' => $user->id,
            'type'       => $type,
            'title'      => $validated['title'] ?? null,
            'content'    => trim((string) ($validated['content'] ?? '')),
        ];

        try {
            if ($type === 'file' && $request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('modules', $disk);
                if (!$path) {
                    throw new \RuntimeException('Storage returned an empty path.');
                }
                $ext = strtolower($file->getClientOriginalExtension() ?: 'file');

                $module = Module::create([
                    'class_id'  => $classroom->id,
                    'title'     => $validated['title'],
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $ext,
                    'file_size' => $file->getSize(),
                ]);

                $data['file_path'] = $path;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $ext;
                $data['file_size'] = $file->getSize();
                $data['module_id'] = $module->id;
            }

            if ($type === 'video') {
                if ($request->hasFile('video_file')) {
                    $video = $request->file('video_file');
                    $path  = $video->store('modules', $disk);
                    if (!$path) {
                        throw new \RuntimeException('Storage returned an empty path.');
                    }
                    $ext = strtolower($video->getClientOriginalExtension() ?: 'mp4');

                    $module = Module::create([
                        'class_id'  => $classroom->id,
                        'title'     => $validated['title'],
                        'file_name' => $video->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $ext,
                        'file_size' => $video->getSize(),
                    ]);

                    $data['file_path'] = $path;
                    $data['file_name'] = $video->getClientOriginalName();
                    $data['file_type'] = $ext;
                    $data['file_size'] = $video->getSize();
                    $data['module_id'] = $module->id;
                } else {
                    $data['video_url'] = trim((string) $request->video_url);
                }
            }

            if ($type === 'quiz') {
                $quiz = Quiz::where('class_id', $classroom->id)->findOrFail($validated['quiz_id']);
                if (!$quiz->isPublished()) {
                    $quiz->update(['published_at' => now()]);
                }
                $data['quiz_id'] = $quiz->id;
                if (empty($data['title'])) {
                    $data['title'] = $quiz->title;
                }
            }

            if ($type === 'assignment') {
                $data['due_at'] = $validated['due_at'];
                $data['points'] = $validated['points'] ?? 100;

                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $path = $file->store('assignments', $disk);
                    if (!$path) {
                        throw new \RuntimeException('Storage returned an empty path.');
                    }
                    $data['file_path'] = $path;
                    $data['file_name'] = $file->getClientOriginalName();
                    $data['file_type'] = strtolower($file->getClientOriginalExtension() ?: 'file');
                    $data['file_size'] = $file->getSize();
                }
            }
        } catch (\Throwable $e) {
            $reason = $e->getPrevious()?->getMessage() ?? $e->getMessage();
            Log::error("Announcement upload failed on [{$disk}]: " . $reason, ['exception' => $e]);

            return back()
                ->withErrors(['file' => 'Upload failed (' . $disk . '): ' . $reason])
                ->withInput();
        }

        Announcement::create($data);

        $messages = [
            'text'       => 'Announcement posted to class!',
            'file'       => 'File material posted to class!',
            'video'      => 'Video lesson posted to class!',
            'quiz'       => 'Quiz quest posted to class!',
            'assignment' => 'Assignment posted and added to student Assignment tabs!',
        ];

        return back()->with('success', $messages[$type] ?? 'Posted to class!');
    }

    public function viewAttachment(Announcement $announcement)
    {
        $this->authorizeAnnouncementAccess($announcement);

        $disk = $this->resolveDiskForPath($announcement->file_path);
        abort_unless($disk, 404);

        $ext = strtolower($announcement->file_type ?: pathinfo((string) $announcement->file_name, PATHINFO_EXTENSION));
        $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'], true);

        if ($disk === 'supabase' && $isVideo && ($publicUrl = $this->supabasePublicUrl($announcement->file_path))) {
            return redirect()->away($publicUrl);
        }

        $fileName = $announcement->file_name ?? 'attachment';
        $contents = Storage::disk($disk)->get($announcement->file_path);

        return response($contents, 200, [
            'Content-Type'        => $this->detectMimeType($ext),
            'Content-Length'      => strlen($contents),
            'Content-Disposition' => 'inline; filename="' . addslashes($fileName) . '"',
        ]);
    }

    public function downloadAttachment(Announcement $announcement)
    {
        $this->authorizeAnnouncementAccess($announcement);

        $disk = $this->resolveDiskForPath($announcement->file_path);
        abort_unless($disk, 404);

        return Storage::disk($disk)->download($announcement->file_path, $announcement->file_name ?? 'attachment');
    }

    public function destroyAnnouncement(Announcement $announcement)
    {
        $user = Auth::user();
        abort_unless(($user->isTeacher() && $announcement->teacher_id === $user->id) || $user->role === 'admin', 403);

        if ($announcement->module_id) {
            $module = Module::find($announcement->module_id);
            if ($module) {
                $modDisk = $this->resolveDiskForPath($module->file_path);
                if ($modDisk) {
                    Storage::disk($modDisk)->delete($module->file_path);
                }
                $module->delete();
            }
        } elseif ($announcement->file_path) {
            $attDisk = $this->resolveDiskForPath($announcement->file_path);
            if ($attDisk) {
                Storage::disk($attDisk)->delete($announcement->file_path);
            }
        }

        $announcement->delete();
        return back()->with('success', 'Post deleted from class stream.');
    }

    protected function authorizeAnnouncementAccess(Announcement $announcement): void
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return;
        }
        if ($user->isTeacher() && $user->ownedClasses->contains($announcement->class_id)) {
            return;
        }
        if ($user->isStudent() && $user->joinedClasses->contains($announcement->class_id)) {
            return;
        }
        abort(403);
    }
}