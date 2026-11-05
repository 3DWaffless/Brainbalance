<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Topic;
use App\Models\Classroom;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class QuizController extends Controller
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
            $classes = $user->ownedClasses;
            $quizzes = Quiz::whereIn('class_id', $classes->pluck('id'))->latest()->get();
            return view('quiz.index', compact('classes', 'quizzes'));
        }

        if ($user->joinedClasses()->count() === 0) {
            return redirect()->route('dashboard')->with('error', 'You need to join a class before you can take a quiz.');
        }

        $quizzes = Quiz::whereIn('class_id', $user->joinedClasses->pluck('id'))->latest()->get();
        return view('quiz.play', compact('quizzes'));
    }

    public function history()
    {
        $user = Auth::user();
        abort_unless($user->isStudent(), 403, 'Only students can view their quiz history.');

        $attempts = QuizAttempt::with('quiz.classroom')
            ->where('student_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('quiz.history', compact('attempts'));
    }

    public function review(QuizAttempt $attempt)
    {
        $user = Auth::user();

        if ($user->isStudent() && $attempt->student_id !== $user->id) {
            abort(403, 'Unauthorized access to this quiz review.');
        }

        if ($user->isTeacher()) {
            $attempt->load('quiz');
            abort_unless($user->ownedClasses->contains($attempt->quiz->class_id), 403, 'Unauthorized access.');
        }

        $attempt->load(['quiz.classroom', 'responses.question', 'student']);

        return view('quiz.review', compact('attempt'));
    }

    public function show(Quiz $quiz)
    {
        $user = Auth::user();

        if ($user->isStudent()) {
            if (!$user->joinedClasses->contains($quiz->class_id)) {
                abort(403, 'You are not enrolled in the class for this quiz.');
            }

            $attemptsCount = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $user->id)
                ->count();

            if ($quiz->max_attempts && $attemptsCount >= $quiz->max_attempts) {
                return redirect()->route('dashboard')->with('error', 'This quiz is closed. You have used all available attempts.');
            }
        }

        if ($quiz->deadline_at && now()->greaterThan($quiz->deadline_at)) {
            abort(403, 'The deadline for this quiz has passed.');
        }

        $quiz->load('questions');
        return view('quiz.play', compact('quiz'));
    }

    public function results()
    {
        abort_unless(Auth::user()->isTeacher(), 403);

        $user = Auth::user();
        $ownedClassIds = $user->ownedClasses->pluck('id');

        $sessions = QuizAttempt::with(['student', 'quiz.classroom', 'quiz.topic'])
            ->whereHas('quiz', function ($query) use ($ownedClassIds) {
                $query->whereIn('class_id', $ownedClassIds);
            })
            ->latest('created_at')
            ->get();

        // Calculate Analytics KPIs
        $totalAttempts = $sessions->count();
        $avgScore = $totalAttempts > 0 ? round($sessions->avg('score'), 1) : 0;
        $totalQuestionsAsked = $sessions->sum('total_questions');
        $totalCorrectAnswers = $sessions->sum('score');
        $overallAccuracy = $totalQuestionsAsked > 0 ? round(($totalCorrectAnswers / $totalQuestionsAsked) * 100) : 0;

        // Weak Topic Diagnostic Engine
        $responses = QuestionResponse::with(['question.topic', 'quizAttempt.quiz'])
            ->whereHas('quizAttempt.quiz', function ($query) use ($ownedClassIds) {
                $query->whereIn('class_id', $ownedClassIds);
            })
            ->get();

        $topicStats = [];
        foreach ($responses as $resp) {
            $topicName = $resp->question->topic->name ?? ($resp->quizAttempt->quiz->subject ?? 'General');
            $topicName = ucfirst($topicName);

            if (!isset($topicStats[$topicName])) {
                $topicStats[$topicName] = ['correct' => 0, 'total' => 0];
            }
            $topicStats[$topicName]['total']++;
            if ($resp->is_correct) {
                $topicStats[$topicName]['correct']++;
            }
        }

        uasort($topicStats, function ($a, $b) {
            $accA = $a['total'] > 0 ? ($a['correct'] / $a['total']) : 0;
            $accB = $b['total'] > 0 ? ($b['correct'] / $b['total']) : 0;
            return $accA <=> $accB; // Weakest topics first
        });

        return view('quiz.results', compact('sessions', 'totalAttempts', 'avgScore', 'overallAccuracy', 'topicStats'));
    }

    public function create(Request $request)
    {
        abort_unless(Auth::user()->isTeacher(), 403, 'Unauthorized.');

        $user = Auth::user();
        $classes = $user->ownedClasses;

        if ($request->has('class_id')) {
            $selectedClass = $classes->where('id', $request->query('class_id'))->firstOrFail();
            return view('quiz.create', compact('selectedClass', 'classes'));
        }

        return view('quiz.select-class', compact('classes'));
    }

    public function generate(Request $request)
    {
        abort_unless(Auth::user()->isTeacher(), 403, 'Unauthorized.');

        $request->validate([
            'module_pdf'     => 'required|mimes:pdf|max:10240',
            'class_id'       => 'required|exists:classes,id',
            'question_count' => 'required|integer|min:1|max:50',
            'quiz_title'     => 'required|string',
            'subject'        => 'required|string',
            'published_at'   => 'required|date',
            'deadline_at'    => 'required|date',
            'max_attempts'   => 'nullable|integer|min:1',
        ]);

        try {
            $pdfBase64 = base64_encode(file_get_contents($request->file('module_pdf')->getPathname()));
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not read the uploaded file.');
        }

        if (!$this->ai) {
            return back()->with('error', 'Gemini AI service is not configured.');
        }

        $aiQuestions = $this->ai->generateQuizFromPdf($pdfBase64, $request->subject, 'Grade 3', $request->question_count);

        if (empty($aiQuestions)) {
            return back()->with('error', 'Failed to generate quiz questions using Gemini AI.');
        }

        $generatedQuestions = array_map(function($q) {
            return [
                'question'   => $q['question_text'] ?? 'Question Text Missing',
                'answer'     => $q['correct_answer'] ?? 'Answer Missing',
                'options'    => $q['options'] ?? [],
                'difficulty' => strtolower($q['difficulty_level'] ?? 'medium'),
            ];
        }, $aiQuestions);

        $user = Auth::user();
        $classes = $user->ownedClasses;
        $selectedClass = $classes->where('id', $request->class_id)->firstOrFail();
        
        $quiz_title   = $request->quiz_title;
        $subject      = $request->subject;
        $published_at = $request->published_at;
        $deadline_at  = $request->deadline_at;
        $max_attempts = $request->max_attempts ?? 1;

        return view('quiz.create', compact('classes', 'selectedClass', 'generatedQuestions', 'quiz_title', 'subject', 'published_at', 'deadline_at', 'max_attempts'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isTeacher(), 403);

        $request->validate([
            'class_id'     => 'required|exists:classes,id',
            'quiz_title'   => 'required|string',
            'subject'      => 'required|string',
            'published_at' => 'required|date',
            'deadline_at'  => 'required|date',
            'max_attempts' => 'nullable|integer|min:1',
            'quiz_mode'    => 'required|in:manual,ai',
            'questions'    => 'required|array|min:1',
        ]);

        $topic = Topic::firstOrCreate(
            ['subject' => ucfirst($request->subject), 'name' => ucfirst($request->subject) . ' Adaptive Quiz'],
            ['difficulty_level' => 'mixed', 'order_index' => 0]
        );

        $quiz = Quiz::create([
            'class_id'     => $request->class_id,
            'topic_id'     => $topic->id,
            'type'         => $request->quiz_mode === 'ai' ? 'ai_generated' : 'manual',
            'title'        => $request->quiz_title,
            'subject'      => strtolower($request->subject),
            'published_at' => Carbon::parse($request->published_at),
            'deadline_at'  => Carbon::parse($request->deadline_at),
            'max_attempts' => $request->max_attempts ?? 1,
        ]);

        foreach ($request->questions as $q) {
            $questionModel = Question::create([
                'topic_id'           => $quiz->topic_id,
                'question_text'      => $q['text'],
                'question_type'      => 'multiple_choice',
                'difficulty_level'   => $q['difficulty'],
                'correct_answer'     => $q['answer'],
                'options'            => $q['options'],
                'time_limit_seconds' => $q['time_limit'],
                'points'             => $q['points'],
            ]);
            $quiz->questions()->syncWithoutDetaching([$questionModel->id]);
        }

        return redirect()->route('dashboard')->with('success', 'Quiz published successfully!');
    }

    public function edit(Quiz $quiz)
    {
        abort_unless(Auth::user()->isTeacher(), 403);
        abort_unless(Auth::user()->ownedClasses->contains($quiz->class_id), 403, 'You do not own this class.');

        $quiz->load('questions', 'classroom');
        return view('quiz.edit', compact('quiz'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        abort_unless(Auth::user()->isTeacher(), 403);
        abort_unless(Auth::user()->ownedClasses->contains($quiz->class_id), 403, 'You do not own this class.');

        $request->validate([
            'quiz_title'   => 'required|string',
            'published_at' => 'required|date',
            'deadline_at'  => 'required|date',
            'max_attempts' => 'nullable|integer|min:1',
            'questions'    => 'required|array|min:1',
        ]);

        $quiz->update([
            'title'        => $request->quiz_title,
            'published_at' => Carbon::parse($request->published_at),
            'deadline_at'  => Carbon::parse($request->deadline_at),
            'max_attempts' => $request->max_attempts,
        ]);

        $keptQuestionIds = [];

        foreach ($request->questions as $q) {
            if (isset($q['id'])) {
                $question = Question::find($q['id']);
                if ($question) {
                    $question->update([
                        'question_text'      => $q['text'],
                        'difficulty_level'   => $q['difficulty'] ?? 'medium',
                        'correct_answer'     => $q['answer'],
                        'options'            => $q['options'],
                        'time_limit_seconds' => $q['time_limit'],
                        'points'             => $q['points'],
                    ]);
                    $keptQuestionIds[] = $question->id;
                }
            } else {
                $newQuestion = Question::create([
                    'topic_id'           => $quiz->topic_id,
                    'question_text'      => $q['text'],
                    'question_type'      => 'multiple_choice',
                    'difficulty_level'   => $q['difficulty'] ?? 'medium',
                    'correct_answer'     => $q['answer'],
                    'options'            => $q['options'],
                    'time_limit_seconds' => $q['time_limit'],
                    'points'             => $q['points'],
                ]);
                $quiz->questions()->attach($newQuestion->id);
                $keptQuestionIds[] = $newQuestion->id;
            }
        }

        $existingQuestionIds = $quiz->questions->pluck('id')->toArray();
        $questionsToDelete = array_diff($existingQuestionIds, $keptQuestionIds);
        
        if (!empty($questionsToDelete)) {
            $quiz->questions()->detach($questionsToDelete);
        }

        return redirect()->route('dashboard')->with('success', 'Quiz updated successfully!');
    }

    public function destroy(Quiz $quiz)
    {
        abort_unless(Auth::user()->isTeacher(), 403);
        abort_unless(Auth::user()->ownedClasses->contains($quiz->class_id), 403, 'You do not own this class.');

        $quiz->questions()->detach();
        $quiz->delete();

        return redirect()->route('dashboard')->with('success', 'Quiz deleted successfully!');
    }

    public function start(Request $request)
    {
        $request->validate(['quiz_id' => 'required|exists:quizzes,id']);
        $quiz = Quiz::with('questions')->findOrFail($request->quiz_id);
        $user = Auth::user();

        abort_unless($user->isStudent(), 403);

        $attemptsCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $user->id)
            ->count();

        if ($quiz->max_attempts && $attemptsCount >= $quiz->max_attempts) {
            return response()->json([
                'error' => 'This quiz is closed. You have used all available attempts.'
            ], 403);
        }

        $progress = [
            'quiz_id'        => $quiz->id,
            'subject'        => $quiz->subject,
            'level'          => 'easy',
            'index'          => 0,
            'total'          => $quiz->questions->count(),
            'score'          => 0,
            'current_streak' => 0,
            'max_streak'     => 0,
            'total_xp'       => 0,
            'log'            => [],
            'asked_ids'      => [],
            'started_at'     => now()->toDateTimeString(),
        ];

        Session::put('quiz_progress', $progress);
        $question = $this->generateQuestion($progress);

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

    public function submitAnswer(Request $request)
    {
        $request->validate([
            'question_id'        => 'required|integer',
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
        $questionModel = Question::find($request->question_id);
        
        $baseXp = $questionModel->points ?? 100;
        $questionTimeLimit = $questionModel->time_limit_seconds ?? 30;
        $earnedXp = 0;
        $multiplier = 1.0;

        if ($isCorrect) {
            $progress['score']++;
            $progress['current_streak']++;

            if ($progress['current_streak'] > $progress['max_streak']) {
                $progress['max_streak'] = $progress['current_streak'];
            }
            if ($progress['current_streak'] >= 5) {
                $multiplier = 2.0;
            } elseif ($progress['current_streak'] >= 3) {
                $multiplier = 1.5;
            }

            $timeBonus = max(0, ($questionTimeLimit - $timeTaken) * 2);
            $earnedXp = (int) round(($baseXp + $timeBonus) * $multiplier);
            $progress['total_xp'] += $earnedXp;
        } else {
            $progress['current_streak'] = 0;
        }

        $progress['log'][] = [
            'question_id'        => $request->question_id,
            'selected'           => $request->selected_index,
            'correct'            => $isCorrect,
            'time_taken_seconds' => $timeTaken,
            'xp_earned'          => $earnedXp,
            'multiplier_applied' => $multiplier,
        ];

        $progress['index']++;
        $finished = $progress['index'] >= $progress['total'];

        if ($finished) {
            $quiz = Quiz::find($progress['quiz_id']);
            $startedAt = isset($progress['started_at']) ? Carbon::parse($progress['started_at']) : now();
            $totalTimeTaken = (int) round(abs($startedAt->diffInSeconds(now())));

            $attempt = QuizAttempt::create([
                'student_id'         => Auth::id(),
                'quiz_id'            => $quiz->id,
                'score'              => $progress['score'],
                'total_questions'    => $progress['total'],
                'time_taken_seconds' => $totalTimeTaken,
                'current_streak'     => $progress['current_streak'],
                'max_streak'         => $progress['max_streak'],
                'total_xp'           => $progress['total_xp'],
            ]);

            $user = Auth::user();
            if ($user) {
                $user->total_xp = ($user->total_xp ?? 0) + $progress['total_xp'];
                $user->level = (int) floor($user->total_xp / 1000) + 1;
                $user->save();
            }

            foreach ($progress['log'] as $entry) {
                QuestionResponse::create([
                    'quiz_attempt_id'    => $attempt->id,
                    'question_id'        => $entry['question_id'],
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
                    'accuracy'   => $progress['total'] > 0 ? ($progress['score'] / $progress['total']) * 100 : 0,
                    'level'      => $progress['level'],
                    'max_streak' => $progress['max_streak'],
                    'total_xp'   => $progress['total_xp'],
                ],
            ]);
        }

        Session::put('quiz_progress', $progress);
        $question = $this->generateQuestion($progress);

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
            'current_streak'  => $progress['current_streak'],
            'max_streak'      => $progress['max_streak'],
            'earned_xp'       => $earnedXp,
            'multiplier'      => $multiplier,
            'total_xp'        => $progress['total_xp'],
        ]);
    }

    protected function generateQuestion(array $progress): array
    {
        $quiz = Quiz::with('questions')->find($progress['quiz_id']);
        
        $question = $quiz->questions()
            ->whereNotIn('questions.id', $progress['asked_ids'])
            ->inRandomOrder()
            ->first();

        if (!$question) {
            $question = $quiz->questions()->inRandomOrder()->first();
        }

        if ($question) {
            $options = $question->options ?? [];
            $correctIndex = array_search($question->correct_answer, $options, true);

            return [
                'id'            => $question->id,
                'text'          => $question->question_text,
                'options'       => $options,
                'correct_index' => $correctIndex !== false ? $correctIndex : 0,
                'time_limit'    => $question->time_limit_seconds ?? 30,
                'points'        => $question->points ?? 100,
            ];
        }

        return [];
    }
}