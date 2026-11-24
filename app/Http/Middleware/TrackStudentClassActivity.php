<?php

namespace App\Http\Middleware;

use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\StudentActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackStudentClassActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $user = Auth::user();
            if (!$user || !$user->isStudent() || $response->getStatusCode() >= 400) {
                return $response;
            }

            $routeName = $request->route()?->getName();

            // 1. Opening or downloading a class announcement file/video/worksheet
            if (in_array($routeName, ['announcements.view', 'announcements.download'], true)) {
                $announcement = $request->route('announcement');
                if (!$announcement instanceof Announcement && $announcement) {
                    $announcement = Announcement::find($announcement);
                }

                if ($announcement && $announcement->class_id && $this->isEnrolledInClass($user->id, (int) $announcement->class_id)) {
                    $className = Classroom::where('id', $announcement->class_id)->value('name') ?? 'Classroom';
                    $itemTitle = $announcement->title ?: ($announcement->file_name ?: 'Class Material');
                    $action    = $routeName === 'announcements.download' ? 'Downloaded Class File' : 'Opened Class File';

                    if (!$this->recentlyLogged($user->id, (int) $announcement->class_id, $action, $itemTitle)) {
                        StudentActivityLog::record(
                            $user->id,
                            $action,
                            'file',
                            "{$action}: \"{$itemTitle}\" in {$className}",
                            [
                                'class_id'        => (int) $announcement->class_id,
                                'class_name'      => $className,
                                'announcement_id' => (int) $announcement->id,
                                'file_name'       => $announcement->file_name ?? $itemTitle,
                            ],
                            (int) $announcement->class_id
                        );
                    }
                }
            }

            // 2. Starting a class quiz
            if ($routeName === 'quiz.start') {
                $quizId = (int) $request->input('quiz_id');
                $quiz   = $quizId ? Quiz::with('classroom')->find($quizId) : null;

                if ($quiz && $quiz->class_id && $this->isEnrolledInClass($user->id, (int) $quiz->class_id)) {
                    $className = $quiz->classroom?->name ?? 'Classroom';

                    if (!$this->recentlyLogged($user->id, (int) $quiz->class_id, 'Started Quiz', $quiz->title)) {
                        StudentActivityLog::record(
                            $user->id,
                            'Started Quiz',
                            'quiz',
                            "Started quiz \"{$quiz->title}\" in {$className}",
                            [
                                'class_id'   => (int) $quiz->class_id,
                                'class_name' => $className,
                                'quiz_id'    => (int) $quiz->id,
                                'quiz_title' => $quiz->title,
                            ],
                            (int) $quiz->class_id
                        );
                    }
                }
            }

            // 3. Submitting a class assignment
            if ($routeName === 'assignments.submit') {
                $announcement = $request->route('announcement');
                if (!$announcement instanceof Announcement && $announcement) {
                    $announcement = Announcement::find($announcement);
                }

                if ($announcement && $announcement->class_id && $this->isEnrolledInClass($user->id, (int) $announcement->class_id)) {
                    $className = Classroom::where('id', $announcement->class_id)->value('name') ?? 'Classroom';
                    $title     = $announcement->title ?: 'Class Assignment';

                    if (!$this->recentlyLogged($user->id, (int) $announcement->class_id, 'Submitted Assignment', $title)) {
                        StudentActivityLog::record(
                            $user->id,
                            'Submitted Assignment',
                            'assignment',
                            "Submitted assignment \"{$title}\" in {$className}",
                            [
                                'class_id'        => (int) $announcement->class_id,
                                'class_name'      => $className,
                                'announcement_id' => (int) $announcement->id,
                                'title'           => $title,
                            ],
                            (int) $announcement->class_id
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            // Never interrupt student file delivery or quiz flow if logging fails
        }

        return $response;
    }

    protected function isEnrolledInClass(int $studentId, int $classId): bool
    {
        if (!Schema::hasTable('class_student')) {
            return false;
        }

        $studentFk = Schema::hasColumn('class_student', 'user_id') ? 'user_id' : 'student_id';
        $classFk   = Schema::hasColumn('class_student', 'class_id') ? 'class_id' : 'classroom_id';

        return DB::table('class_student')
            ->where($classFk, $classId)
            ->where($studentFk, $studentId)
            ->exists();
    }

    protected function recentlyLogged(int $studentId, int $classId, string $action, string $itemTitle): bool
    {
        if (!Schema::hasTable('student_activity_logs')) {
            return false;
        }

        $query = StudentActivityLog::where('user_id', $studentId)
            ->where('action', $action)
            ->where('description', 'like', '%' . $itemTitle . '%')
            ->where('created_at', '>=', now()->subMinutes(5));

        if (Schema::hasColumn('student_activity_logs', 'class_id')) {
            $query->where('class_id', $classId);
        }

        return $query->exists();
    }
}