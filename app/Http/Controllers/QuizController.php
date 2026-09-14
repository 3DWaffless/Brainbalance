<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Topic;
use App\Services\AnthropicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class QuizController extends Controller
{
    protected ?AnthropicService $ai = null;

    public function __construct()
    {
        try {
            $this->ai = app(AnthropicService::class);
        } catch (\Throwable $e) {
            // No API key configured yet — the quiz will fall back to the static question bank.
            $this->ai = null;
        }
    }

    /**
     * Show the subject picker (start screen) — for students.
     */
    public function index()
    {
        abort_unless(Auth::user()->isStudent(), 403);

        if (Auth::user()->joinedClasses()->count() === 0) {
            return redirect()->route('dashboard')
                ->with('error', 'You need to join a class before you can take a quiz.');
        }

        return view('quiz.play');
    }

    /**
     * Show all quiz attempt results — for teachers.
     */
    public function results()
    {
        abort_unless(Auth::user()->isTeacher(), 403);

        $sessions = QuizAttempt::with(['student', 'quiz.topic', 'weakestTopic'])
            ->whereHas('student.joinedClasses', function ($query) {
                $query->where('teacher_id', Auth::id());
            })
            ->latest('created_at')
            ->get();

        return view('quiz.results', compact('sessions'));
    }

    /**
     * Find (or create) the Topic row that represents a subject's AI-driven
     * adaptive quiz track, so generated content has a real topic_id to hang off.
     */
    protected function topicForSubject(string $subject): Topic
    {
        return Topic::firstOrCreate(
            ['subject' => ucfirst($subject), 'name' => ucfirst($subject) . ' Adaptive Quiz'],
            ['difficulty_level' => 'mixed', 'order_index' => 0]
        );
    }

    /**
     * Find (or create) the Quiz row representing the AI-generated adaptive
     * quiz for a subject.
     */
    protected function quizForSubject(string $subject, Topic $topic): Quiz
    {
        return Quiz::firstOrCreate(
            ['topic_id' => $topic->id, 'type' => 'standard'],
            ['title' => ucfirst($subject) . ' Adaptive Quiz', 'config' => ['ai_generated' => true]]
        );
    }

    /**
     * Start a new session: pick subject, reset in-memory progress, get Q1.
     */
    public function start(Request $request)
    {
        abort_unless(Auth::user()->isStudent(), 403);

        if (Auth::user()->joinedClasses()->count() === 0) {
            return response()->json(['error' => 'You need to join a class before you can take a quiz.'], 403);
        }

        $request->validate([
            'subject' => 'required|in:math,english',
        ]);

        $progress = [
            'subject'        => $request->subject,
            'level'          => 'easy',
            'index'          => 0,
            'total'          => 5,
            'score'          => 0,
            'current_streak' => 0,
            'max_streak'     => 0,
            'total_xp'       => 0,
            'log'            => [],
            'asked_ids'      => [],
            'started_at'     => now()->toDateTimeString(),
        ];

        Session::put('quiz_progress', $progress);

        $question = $this->generateQuestion($progress['subject'], $progress['level'], $progress['asked_ids']);

        if (!empty($question['id'])) {
            $progress['asked_ids'][] = $question['id'];
            Session::put('quiz_progress', $progress);
        }

        return response()->json([
            'question'        => $question,
            'question_number' => 1,
            'total_questions' => $progress['total'],
            'level'           => $progress['level'],
            'current_streak'  => 0,
            'total_xp'        => 0,
        ]);
    }

    /**
     * Submit an answer for the current question, evaluate gamification (streaks/XP/time),
     * and get next question (or finish attempt).
     */
    public function submitAnswer(Request $request)
    {
        $request->validate([
            'question_id'        => 'nullable|integer',
            'selected_index'     => 'required|integer',
            'question_text'      => 'required|string',
            'correct_index'      => 'required|integer',
            'options'            => 'nullable|array',
            'time_taken_seconds' => 'nullable|integer|min:0',
        ]);

        $progress = Session::get('quiz_progress');

        if (!$progress) {
            return response()->json(['error' => 'No active quiz session.'], 422);
        }

        $isCorrect = $request->selected_index === $request->correct_index;
        $timeTaken = $request->input('time_taken_seconds', 0);

        $baseXp = 100;
        $earnedXp = 0;
        $multiplier = 1.0;

        if ($isCorrect) {
            $progress['score']++;
            $progress['current_streak'] = ($progress['current_streak'] ?? 0) + 1;

            if ($progress['current_streak'] > ($progress['max_streak'] ?? 0)) {
                $progress['max_streak'] = $progress['current_streak'];
            }

            // Streak Multipliers
            if ($progress['current_streak'] >= 5) {
                $multiplier = 2.0;
            } elseif ($progress['current_streak'] >= 3) {
                $multiplier = 1.5;
            }

            // Speed Bonus (15s standard timer)
            $timeBonus = max(0, (15 - $timeTaken) * 5);
            $earnedXp = (int) round(($baseXp + $timeBonus) * $multiplier);
            $progress['total_xp'] = ($progress['total_xp'] ?? 0) + $earnedXp;

            $progress['level'] = $this->stepUp($progress['level']);
        } else {
            // Reset streak on incorrect answer or timeout
            $progress['current_streak'] = 0;
            $progress['level'] = $this->stepDown($progress['level']);
        }

        $progress['log'][] = [
            'question_id'        => $request->question_id,
            'question'           => $request->question_text,
            'options'            => $request->options,
            'correct_index'      => $request->correct_index,
            'selected'           => $request->selected_index,
            'correct'            => $isCorrect,
            'level'              => $progress['level'],
            'time_taken_seconds' => $timeTaken,
            'xp_earned'          => $earnedXp,
            'multiplier_applied' => $multiplier,
        ];

        $progress['index']++;
        $finished = $progress['index'] >= $progress['total'];

        if ($finished) {
            $topic = $this->topicForSubject($progress['subject']);
            $quiz  = $this->quizForSubject($progress['subject'], $topic);

            $startedAt = isset($progress['started_at']) ? \Carbon\Carbon::parse($progress['started_at']) : now();
            $totalTimeTaken = (int) round(abs($startedAt->diffInSeconds(now())));

            // Save Attempt with Gamification stats
            $attempt = QuizAttempt::create([
                'student_id'         => Auth::id(),
                'quiz_id'            => $quiz->id,
                'weakest_topic_id'   => !$isCorrect ? $topic->id : null,
                'score'              => $progress['score'],
                'total_questions'    => $progress['total'],
                'time_taken_seconds' => $totalTimeTaken,
                'current_streak'     => $progress['current_streak'],
                'max_streak'         => $progress['max_streak'],
                'total_xp'           => $progress['total_xp'],
            ]);

            // Update Student Global XP and Level on User model
            $user = Auth::user();
            if ($user) {
                $user->total_xp = ($user->total_xp ?? 0) + $progress['total_xp'];
                // Level up formula: Level increases every 1,000 XP
                $user->level = (int) floor($user->total_xp / 1000) + 1;
                $user->save();
            }

            // Save Question Responses
            foreach ($progress['log'] as $entry) {
                $options = $entry['options'] ?? null;
                $correctAnswer = ($options && isset($entry['correct_index']))
                    ? ($options[$entry['correct_index']] ?? null)
                    : null;

                $question = $entry['question_id'] ? Question::find($entry['question_id']) : null;

                if (!$question) {
                    $question = Question::create([
                        'topic_id'         => $topic->id,
                        'question_text'    => $entry['question'],
                        'question_type'    => 'multiple_choice',
                        'difficulty_level' => $entry['level'],
                        'correct_answer'   => $correctAnswer,
                        'options'          => $options,
                    ]);
                }

                $quiz->questions()->syncWithoutDetaching([$question->id]);

                QuestionResponse::create([
                    'quiz_attempt_id'    => $attempt->id,
                    'question_id'        => $question->id,
                    'student_answer'     => ($options && isset($entry['selected'])) ? ($options[$entry['selected']] ?? null) : null,
                    'is_correct'         => $entry['correct'],
                    'time_taken_seconds' => $entry['time_taken_seconds'] ?? null,
                    'xp_earned'          => $entry['xp_earned'] ?? 0,
                    'multiplier_applied' => $entry['multiplier_applied'] ?? 1.0,
                ]);
            }

            Session::forget('quiz_progress');

            return response()->json([
                'finished' => true,
                'summary'  => [
                    'score'      => $progress['score'],
                    'total'      => $progress['total'],
                    'accuracy'   => $attempt->accuracy,
                    'level'      => $progress['level'],
                    'max_streak' => $progress['max_streak'],
                    'total_xp'   => $progress['total_xp'],
                ],
            ]);
        }

        Session::put('quiz_progress', $progress);

        $question = $this->generateQuestion($progress['subject'], $progress['level'], $progress['asked_ids']);

        if (!empty($question['id'])) {
            $progress['asked_ids'][] = $question['id'];
            Session::put('quiz_progress', $progress);
        }

        return response()->json([
            'finished'        => false,
            'was_correct'     => $isCorrect,
            'question'        => $question,
            'question_number' => $progress['index'] + 1,
            'total_questions' => $progress['total'],
            'level'           => $progress['level'],
            'current_streak'  => $progress['current_streak'],
            'max_streak'      => $progress['max_streak'],
            'earned_xp'       => $earnedXp,
            'multiplier'      => $multiplier,
            'total_xp'        => $progress['total_xp'],
        ]);
    }

    /**
     * Pull one question from the static question bank (falling back to
     * live Claude generation if configured, then to a hardcoded default).
     */
    protected function generateQuestion(string $subject, string $level, array $excludeIds = []): array
    {
        $topic = $this->topicForSubject($subject);

        // 1) Try the static question bank for this subject + difficulty level.
        $query = Question::where('topic_id', $topic->id)->where('difficulty_level', $level);
        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }
        $question = $query->inRandomOrder()->first();

        // 2) If that level's pool is exhausted, try any difficulty for this subject.
        if (!$question) {
            $query = Question::where('topic_id', $topic->id);
            if (!empty($excludeIds)) {
                $query->whereNotIn('id', $excludeIds);
            }
            $question = $query->inRandomOrder()->first();
        }

        if ($question) {
            $options = $question->options ?? [];
            $correctIndex = array_search($question->correct_answer, $options, true);

            return [
                'id'            => $question->id,
                'text'          => $question->question_text,
                'options'       => $options,
                'correct_index' => $correctIndex !== false ? $correctIndex : 0,
            ];
        }

        // 3) If the bank has nothing left and Claude is configured, generate one live.
        if ($this->ai) {
            $subjectLabel = $subject === 'math' ? 'elementary Math' : 'elementary English';

            $system = "You are a quiz generator for elementary school students (grades 1-6). "
                . "Generate exactly one multiple-choice {$subjectLabel} question at {$level} difficulty. "
                . "Return JSON with keys: text (string), options (array of exactly 4 short strings), correct_index (integer 0-3). "
                . "Keep language simple and age-appropriate. Do not repeat common example questions.";

            $user = "Generate one {$level}-difficulty {$subjectLabel} multiple-choice question.";

            try {
                $data = $this->ai->askJson($system, $user, 400);

                return [
                    'id'            => null,
                    'text'          => $data['text'] ?? 'What is 2 + 2?',
                    'options'       => $data['options'] ?? ['3', '4', '5', '6'],
                    'correct_index' => $data['correct_index'] ?? 1,
                ];
            } catch (\Throwable $e) {
                // fall through to hardcoded fallback below
            }
        }

        // 4) Last-resort hardcoded fallback so the quiz flow never breaks.
        return [
            'id'            => null,
            'text'          => 'What is 2 + 2?',
            'options'       => ['3', '4', '5', '6'],
            'correct_index' => 1,
        ];
    }

    protected function stepUp(string $level): string
    {
        return match ($level) {
            'easy'   => 'medium',
            'medium' => 'hard',
            default  => 'hard',
        };
    }

    protected function stepDown(string $level): string
    {
        return match ($level) {
            'hard'   => 'medium',
            'medium' => 'easy',
            default  => 'easy',
        };
    }
}