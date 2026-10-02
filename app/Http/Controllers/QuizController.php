<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionResponse;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Topic;
use App\Models\Classroom;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Smalot\PdfParser\Parser as PdfParser;

class QuizController extends Controller
{
    protected ?GeminiService $ai = null;

    public function __construct()
    {
        try {
            $this->ai = app(GeminiService::class);
        } catch (\Throwable $e) {
            // No API key configured yet — the quiz will fall back to the static question bank.
            $this->ai = null;
        }
    }

    /**
     * Show all quizzes available to the user or quiz creation hub.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isTeacher()) {
            $classes = $user->ownedClasses;
            $quizzes = Quiz::whereIn('class_id', $classes->pluck('id'))->latest()->get();
            return view('quiz.index', compact('classes', 'quizzes'));
        }

        if ($user->joinedClasses()->count() === 0) {
            return redirect()->route('dashboard')
                ->with('error', 'You need to join a class before you can take a quiz.');
        }

        $quizzes = Quiz::whereIn('class_id', $user->joinedClasses->pluck('id'))->latest()->get();
        return view('quiz.play', compact('quizzes'));
    }

    /**
     * Display a specific class quiz for a student to attempt.
     */
    public function show(Quiz $quiz)
    {
        $user = Auth::user();

        // Security check: Ensure student belongs to the class
        if ($user->isStudent() && !$user->joinedClasses->contains($quiz->class_id)) {
            abort(403, 'You are not enrolled in the class for this quiz.');
        }

        $quiz->load('questions');

        return view('quiz.show', compact('quiz'));
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
     * Upload PDF and generate an AI-driven Quiz assigned to a class using Gemini AI (TEACHERS ONLY).
     */
    public function generateFromPdf(Request $request)
    {
        abort_unless(Auth::user()->isTeacher(), 403, 'Unauthorized. Only teachers can create quizzes from PDF modules.');

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'pdf_file' => 'required|mimes:pdf|max:10240', // 10MB limit
            'subject'  => 'required|in:math,english',
            'topic_id' => 'nullable|exists:topics,id',
        ]);

        $file = $request->file('pdf_file');
        
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($file->getPathname());
            $extractedText = substr($pdf->getText(), 0, 4000); // Context length limit
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to extract text from the PDF file.');
        }

        if (empty(trim($extractedText))) {
            return back()->with('error', 'Could not read text from the uploaded PDF.');
        }

        if (!$this->ai) {
            return back()->with('error', 'Gemini AI service is not configured.');
        }

        // Generate Questions via Gemini Service
        $questions = $this->ai->generateQuizFromText(
            $extractedText,
            ucfirst($request->input('subject')),
            'Grade 3',
            5
        );

        if (empty($questions)) {
            return back()->with('error', 'Failed to generate quiz questions using Gemini AI.');
        }

        $topic = $this->topicForSubject($request->input('subject'));

        $quiz = Quiz::create([
            'class_id' => $request->input('class_id'),
            'topic_id' => $request->input('topic_id') ?? $topic->id,
            'type'     => 'ai_generated',
            'title'    => 'Gemini AI Quiz: ' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'subject'  => $request->input('subject'),
            'config'   => ['source' => $file->getClientOriginalName()]
        ]);

        foreach ($questions as $q) {
            $questionModel = Question::create([
                'topic_id'         => $quiz->topic_id,
                'question_text'    => $q['question_text'],
                'question_type'    => 'multiple_choice',
                'difficulty_level' => strtolower($q['difficulty_level'] ?? 'easy'),
                'correct_answer'   => $q['correct_answer'],
                'options'          => $q['options']
            ]);

            $quiz->questions()->syncWithoutDetaching([$questionModel->id]);
        }

        return redirect()->route('subjects.show', $request->input('class_id'))
                         ->with('success', 'Gemini AI Quiz generated successfully!');
    }

    /**
     * Find (or create) the Topic row that represents a subject's AI-driven
     * adaptive quiz track.
     */
    protected function topicForSubject(string $subject): Topic
    {
        return Topic::firstOrCreate(
            ['subject' => ucfirst($subject), 'name' => ucfirst($subject) . ' Adaptive Quiz'],
            ['difficulty_level' => 'mixed', 'order_index' => 0]
        );
    }

    /**
     * Find (or create) the Quiz row representing the AI-generated adaptive quiz.
     */
    protected function quizForSubject(string $subject, Topic $topic): Quiz
    {
        return Quiz::firstOrCreate(
            ['topic_id' => $topic->id, 'type' => 'standard'],
            ['title' => ucfirst($subject) . ' Adaptive Quiz', 'config' => ['ai_generated' => true]]
        );
    }

    /**
     * Start a new interactive session: pick subject, reset in-memory progress, get Q1.
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
     * Submit an answer for the current question, evaluate gamification stats,
     * and proceed or finish attempt.
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

            if ($progress['current_streak'] >= 5) {
                $multiplier = 2.0;
            } elseif ($progress['current_streak'] >= 3) {
                $multiplier = 1.5;
            }

            $timeBonus = max(0, (15 - $timeTaken) * 5);
            $earnedXp = (int) round(($baseXp + $timeBonus) * $multiplier);
            $progress['total_xp'] = ($progress['total_xp'] ?? 0) + $earnedXp;

            $progress['level'] = $this->stepUp($progress['level']);
        } else {
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

            $user = Auth::user();
            if ($user) {
                $user->total_xp = ($user->total_xp ?? 0) + $progress['total_xp'];
                $user->level = (int) floor($user->total_xp / 1000) + 1;
                $user->save();
            }

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
     * Pull one question from the static question bank.
     */
    protected function generateQuestion(string $subject, string $level, array $excludeIds = []): array
    {
        $topic = $this->topicForSubject($subject);

        $query = Question::where('topic_id', $topic->id)->where('difficulty_level', $level);
        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }
        $question = $query->inRandomOrder()->first();

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

        if ($this->ai) {
            $subjectLabel = $subject === 'math' ? 'elementary Math' : 'elementary English';

            $userPrompt = "Generate one {$level}-difficulty {$subjectLabel} multiple-choice question.\n" .
                "Return ONLY a JSON object with this exact structure:\n" .
                "{\n" .
                "  \"text\": \"Question string\",\n" .
                "  \"options\": [\"Option A\", \"Option B\", \"Option C\", \"Option D\"],\n" .
                "  \"correct_index\": 1\n" .
                "}";

            try {
                $questions = $this->ai->generateQuizFromText($userPrompt, ucfirst($subject), 'Grade 3', 1);

                if (!empty($questions[0])) {
                    $item = $questions[0];
                    $options = $item['options'] ?? ['3', '4', '5', '6'];
                    $correctIndex = array_search($item['correct_answer'] ?? '', $options, true);

                    return [
                        'id'            => null,
                        'text'          => $item['question_text'] ?? 'What is 2 + 2?',
                        'options'       => $options,
                        'correct_index' => $correctIndex !== false ? $correctIndex : 1,
                    ];
                }
            } catch (\Throwable $e) {
                logger()->error('Gemini live question generation failed: ' . $e->getMessage());
            }
        }

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