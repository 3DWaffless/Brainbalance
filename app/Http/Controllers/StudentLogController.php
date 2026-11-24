<?php
// FILE: app/Http/Controllers/StudentLogController.php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\QuizAttempt;
use App\Models\StudentActivityLog;
use App\Models\StudentBadge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class StudentLogController extends Controller
{
    protected function authorizeTeacher(): void
    {
        $user = auth()->user();

        if (!$user || !in_array($user->role, ['teacher', 'admin'], true)) {
            abort(403, 'Only teachers can access student activity logs.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeTeacher();

        $students   = User::where('role', 'student')->orderBy('name')->get();
        $classrooms = Schema::hasTable('classes') ? Classroom::orderBy('name')->get() : collect();
        $allLogs    = $this->buildUnifiedStudentLogs($students, $request);

        $perPage = 20;
        $page    = max(1, (int) $request->input('page', 1));
        $items   = $allLogs->forPage($page, $perPage)->values();

        $logs = new LengthAwarePaginator(
            $items,
            $allLogs->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = [
            'total_students' => $students->count(),
            'active_today'   => $allLogs->filter(fn ($row) => $row['timestamp'] && $row['timestamp']->isToday())->pluck('student_id')->unique()->count(),
            'total_quizzes'  => Schema::hasTable('quiz_attempts') ? QuizAttempt::count() : 0,
            'total_logs'     => $allLogs->count(),
        ];

        return view('teacher.student-logs', compact('logs', 'students', 'classrooms', 'stats'));
    }

    public function export(Request $request)
    {
        $this->authorizeTeacher();

        $students = User::where('role', 'student')->orderBy('name')->get();
        $allLogs  = $this->buildUnifiedStudentLogs($students, $request);

        $filename = 'brainbalance_student_logs_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($allLogs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Timestamp', 'Student Name', 'Student Email', 'Category', 'Action', 'Description', 'Details']);

            foreach ($allLogs as $log) {
                fputcsv($handle, [
                    $log['timestamp'] ? $log['timestamp']->format('Y-m-d H:i:s') : '',
                    $log['student_name'],
                    $log['student_email'],
                    strtoupper($log['category']),
                    $log['action'],
                    $log['description'],
                    $log['detail_badge'] ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function buildUnifiedStudentLogs(Collection $students, Request $request): Collection
    {
        $filteredStudents = $students;

        if ($request->filled('classroom_id') && Schema::hasTable('class_student')) {
            $classId   = (int) $request->input('classroom_id');
            $studentFk = Schema::hasColumn('class_student', 'user_id') ? 'user_id' : 'student_id';
            $classFk   = Schema::hasColumn('class_student', 'class_id') ? 'class_id' : 'classroom_id';

            $enrolledIds = DB::table('class_student')
                ->where($classFk, $classId)
                ->pluck($studentFk)
                ->map(fn ($id) => (int) $id)
                ->all();

            $filteredStudents = $filteredStudents->filter(fn ($s) => in_array((int) $s->id, $enrolledIds, true));
        }

        $studentMap = $filteredStudents->keyBy('id');
        $studentIds = $filteredStudents->pluck('id')->all();
        $entries    = collect();

        if (empty($studentIds)) {
            return $entries;
        }

        // 1. Explicit StudentActivityLog records
        if (Schema::hasTable('student_activity_logs')) {
            $dbLogs = StudentActivityLog::whereIn('user_id', $studentIds)
                ->latest()
                ->limit(500)
                ->get();

            foreach ($dbLogs as $log) {
                $student = $studentMap->get($log->user_id);
                if (!$student) {
                    continue;
                }

                $entries->push([
                    'id'            => 'log_' . $log->id,
                    'student_id'    => $student->id,
                    'student_name'  => $this->displayName($student),
                    'student_email' => $student->email,
                    'avatar'        => $student->avatar_url ?? $student->avatar ?? null,
                    'category'      => $log->category ?: 'activity',
                    'action'        => $log->action,
                    'description'   => $log->description,
                    'detail_badge'  => $log->ip_address ? 'IP: ' . $log->ip_address : null,
                    'review_url'    => null,
                    'timestamp'     => $log->created_at ? Carbon::parse($log->created_at) : null,
                ]);
            }
        }

        // 2. QuizAttempt submissions (with direct link to review screen)
        if (Schema::hasTable('quiz_attempts') && class_exists(QuizAttempt::class)) {
            $attemptUserCol = Schema::hasColumn('quiz_attempts', 'user_id') ? 'user_id' : 'student_id';

            $attempts = QuizAttempt::with('quiz')
                ->whereIn($attemptUserCol, $studentIds)
                ->latest()
                ->limit(500)
                ->get();

            foreach ($attempts as $attempt) {
                $sid     = $attempt->{$attemptUserCol};
                $student = $studentMap->get($sid);
                if (!$student) {
                    continue;
                }

                $quizTitle = $attempt->quiz?->title ?? 'Quiz #' . $attempt->quiz_id;
                $score     = $attempt->score ?? 0;
                $total     = $attempt->total_questions ?? $attempt->max_score ?? null;
                $scoreText = $total ? "{$score}/{$total}" : "{$score} pts";
                $xpEarned  = $attempt->xp_earned ?? null;

                $entries->push([
                    'id'            => 'attempt_' . $attempt->id,
                    'student_id'    => $student->id,
                    'student_name'  => $this->displayName($student),
                    'student_email' => $student->email,
                    'avatar'        => $student->avatar_url ?? $student->avatar ?? null,
                    'category'      => 'quiz',
                    'action'        => 'Completed Quiz',
                    'description'   => "Completed \"{$quizTitle}\" with a score of {$scoreText}",
                    'detail_badge'  => $xpEarned ? "+{$xpEarned} XP • {$scoreText}" : $scoreText,
                    'review_url'    => Route::has('quizzes.review') ? route('quizzes.review', $attempt->id) : null,
                    'timestamp'     => $attempt->completed_at
                        ? Carbon::parse($attempt->completed_at)
                        : ($attempt->created_at ? Carbon::parse($attempt->created_at) : null),
                ]);
            }
        }

        // 3. Badge unlocks
        if (Schema::hasTable('student_badges') && class_exists(StudentBadge::class)) {
            $badgeUserCol = Schema::hasColumn('student_badges', 'student_id') ? 'student_id' : 'user_id';

            $badges = StudentBadge::with('badge')
                ->whereIn($badgeUserCol, $studentIds)
                ->latest()
                ->limit(300)
                ->get();

            foreach ($badges as $studentBadge) {
                $sid     = $studentBadge->{$badgeUserCol};
                $student = $studentMap->get($sid);
                if (!$student) {
                    continue;
                }

                $badgeName = $studentBadge->badge?->name ?? 'Achievement Badge';

                $entries->push([
                    'id'            => 'badge_' . $studentBadge->id,
                    'student_id'    => $student->id,
                    'student_name'  => $this->displayName($student),
                    'student_email' => $student->email,
                    'avatar'        => $student->avatar_url ?? $student->avatar ?? null,
                    'category'      => 'badge',
                    'action'        => 'Badge Earned',
                    'description'   => "Unlocked badge \"{$badgeName}\"",
                    'detail_badge'  => '🏆 ' . $badgeName,
                    'review_url'    => null,
                    'timestamp'     => $studentBadge->created_at ? Carbon::parse($studentBadge->created_at) : null,
                ]);
            }
        }

        // 4. Student account registrations
        foreach ($filteredStudents as $student) {
            $entries->push([
                'id'            => 'joined_' . $student->id,
                'student_id'    => $student->id,
                'student_name'  => $this->displayName($student),
                'student_email' => $student->email,
                'avatar'        => $student->avatar_url ?? $student->avatar ?? null,
                'category'      => 'auth',
                'action'        => 'Account Created',
                'description'   => 'Registered student account on BrainBalance',
                'detail_badge'  => !empty($student->google_id) ? 'Google OAuth' : 'Email Registration',
                'review_url'    => null,
                'timestamp'     => $student->created_at ? Carbon::parse($student->created_at) : null,
            ]);
        }

        // Apply filters
        if ($request->filled('student_id')) {
            $targetId = (int) $request->input('student_id');
            $entries = $entries->filter(fn ($row) => (int) $row['student_id'] === $targetId);
        }

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $cat = strtolower((string) $request->input('category'));
            $entries = $entries->filter(fn ($row) => strtolower($row['category']) === $cat);
        }

        if ($request->filled('date')) {
            $date = (string) $request->input('date');
            $entries = $entries->filter(fn ($row) => $row['timestamp'] && $row['timestamp']->toDateString() === $date);
        }

        if ($request->filled('search')) {
            $q = mb_strtolower(trim((string) $request->input('search')));
            $entries = $entries->filter(function ($row) use ($q) {
                return str_contains(mb_strtolower($row['student_name']), $q)
                    || str_contains(mb_strtolower($row['student_email']), $q)
                    || str_contains(mb_strtolower($row['action']), $q)
                    || str_contains(mb_strtolower($row['description']), $q);
            });
        }

        return $entries
            ->sortByDesc(fn ($row) => $row['timestamp'] ? $row['timestamp']->timestamp : 0)
            ->values();
    }

    protected function displayName(User $user): string
    {
        $full = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        return $full !== '' ? $full : ($user->name ?? 'Student #' . $user->id);
    }
}