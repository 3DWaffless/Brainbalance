<?php
// FILE: app/Http/Controllers/QuizController.php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Classroom;
use App\Models\QuizAttempt;
use App\Models\QuestionResponse;
use App\Models\Module;
use App\Services\GroqService;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class QuizController extends Controller
{
    protected GroqService $groqService;

    public const QUESTION_TYPES = [
        'multiple_choice',
        'multiple_select',
        'true_false',
        'matching',
        'fill_blank',
        'short_answer',
        'essay',
        'identification',
    ];

    public function __construct(GroqService $groqService)
    {
        $this->groqService = $groqService;
    }

    protected function canManageQuizzes($user): bool
    {
        return $user && ($user->isTeacher() || $user->role === 'admin');
    }

    protected function resolveModuleDisk(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $primary = config('filesystems.default') === 'supabase' ? 'supabase' : 'public';

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

    protected function hasQuestionsQuizIdColumn(): bool
    {
        return Cache::rememberForever('schema_questions_has_quiz_id', fn () => Schema::hasColumn('questions', 'quiz_id'));
    }

    protected function hasQuestionsTypeColumn(): bool
    {
        return Cache::rememberForever('schema_questions_has_type', fn () => Schema::hasColumn('questions', 'type'));
    }

    protected function hasQuestionsMatchingPairsColumn(): bool
    {
        return Cache::rememberForever('schema_questions_has_matching_pairs', fn () => Schema::hasColumn('questions', 'matching_pairs'));
    }

    protected function hasQuestionsCorrectAnswersColumn(): bool
    {
        return Cache::rememberForever('schema_questions_has_correct_answers', fn () => Schema::hasColumn('questions', 'correct_answers'));
    }

    protected function hasQuestionsExplanationColumn(): bool
    {
        return Cache::rememberForever('schema_questions_has_explanation', fn () => Schema::hasColumn('questions', 'explanation'));
    }

    protected function hasQuizQuestionsPivotTable(): bool
    {
        return Cache::rememberForever('schema_has_quiz_questions', fn () => Schema::hasTable('quiz_questions'));
    }

    protected function hasQuizzesIsPublishedColumn(): bool
    {
        return Cache::rememberForever('schema_quizzes_has_is_published', fn () => Schema::hasColumn('quizzes', 'is_published'));
    }

    protected function hasQuizzesStatusColumn(): bool
    {
        return Cache::rememberForever('schema_quizzes_has_status', fn () => Schema::hasColumn('quizzes', 'status'));
    }

    protected function hasResponseSelectedIndexColumn(): bool
    {
        return Cache::rememberForever('schema_responses_has_selected_index', fn () => Schema::hasColumn('question_responses', 'selected_index'));
    }

    protected function hasResponseTypedAnswerColumn(): bool
    {
        return Cache::rememberForever('schema_responses_has_typed_answer', fn () => Schema::hasColumn('question_responses', 'typed_answer'));
    }

    protected function normalizeQuestionType(?string $rawType): string
    {
        $t = strtolower(trim((string) $rawType));
        if ($t === 'identification') {
            return 'short_answer';
        }
        return in_array($t, self::QUESTION_TYPES, true) ? $t : 'multiple_choice';
    }

    /**
     * Resolve per-question time limit in seconds from (time_value + time_unit) or legacy time_limit.
     */
    protected function resolveQuestionSeconds(array $qData): int
    {
        if (isset($qData['time_value']) && isset($qData['time_unit'])) {
            $val = max(1, (int) $qData['time_value']);
            $multiplier = match (strtolower((string) $qData['time_unit'])) {
                'hours', 'hour'     => 3600,
                'minutes', 'minute' => 60,
                default             => 1,
            };
            return max(5, $val * $multiplier);
        }

        return max(5, (int) ($qData['time_limit'] ?? 30));
    }

    /**
     * Extract structured question payload from a Question model (supports legacy & 7-type formats).
     */
    protected function serializeQuestionForBankOrArena(Question $q): array
    {
        $type = $this->normalizeQuestionType($q->question_type ?? $q->type ?? 'multiple_choice');
        $options = is_array($q->options) ? $q->options : (json_decode($q->options ?? '[]', true) ?: []);

        if ($type === 'true_false' && empty($options)) {
            $options = ['True', 'False'];
        }

        $matchingPairs = [];
        if ($type === 'matching') {
            if (!empty($q->matching_pairs) && is_array($q->matching_pairs)) {
                $matchingPairs = $q->matching_pairs;
            } else {
                $decoded = json_decode((string) $q->correct_answer, true);
                if (is_array($decoded)) {
                    $matchingPairs = $decoded;
                }
            }
        }

        $correctAnswers = [];
        if (!empty($q->correct_answers) && is_array($q->correct_answers)) {
            $correctAnswers = $q->correct_answers;
        } elseif ($type === 'multiple_select' || $type === 'fill_blank') {
            $decoded = json_decode((string) $q->correct_answer, true);
            $correctAnswers = is_array($decoded) ? $decoded : (trim((string) $q->correct_answer) !== '' ? [trim((string) $q->correct_answer)] : []);
        } elseif ($type === 'matching') {
            $correctAnswers = $matchingPairs;
        } else {
            $correctAnswers = trim((string) $q->correct_answer) !== '' ? [trim((string) $q->correct_answer)] : [];
        }

        return [
            'id'              => $q->id,
            'type'            => $type,
            'prompt'          => $q->question_text,
            'options'         => array_values($options),
            'correct_answer'  => (string) ($q->correct_answer ?? ''),
            'correct_answers' => array_values($correctAnswers),
            'matching_pairs'  => array_values($matchingPairs),
            'difficulty'      => strtolower($q->difficulty_level ?? 'easy'),
            'time_limit'      => (int) ($q->time_limit_seconds ?? 30),
            'points'          => (int) ($q->points ?? 100),
            'explanation'     => (string) ($q->explanation ?? ''),
        ];
    }

    /**
     * Build published class files list from App\Models\Module for the AI Generator drawer.
     */
    protected function buildClassModulesList($classes): array
    {
        $classIds = $classes->pluck('id');
        if ($classIds->isEmpty()) {
            return [];
        }

        return Module::whereIn('class_id', $classIds)
            ->latest()
            ->get()
            ->map(function (Module $m) use ($classes) {
                $cls = $classes->firstWhere('id', $m->class_id);
                return [
                    'id'         => $m->id,
                    'title'      => $m->title,
                    'file_name'  => $m->file_name ?? basename((string) ($m->file_path ?? '')),
                    'class_id'   => $m->class_id,
                    'class_name' => $cls->name ?? 'Class',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Build per-quiz Question Bank dataset from already-published quizzes.
     */
    protected function buildQuestionBank($user, $classes): array
    {
        $classIds = $classes->pluck('id');

        $publishedQuizzes = Quiz::with(['classroom', 'questions'])
            ->whereIn('class_id', $classIds)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->get()
            ->filter(fn ($quiz) => $quiz->isPublished() && $quiz->questions->isNotEmpty())
            ->values();

        return $publishedQuizzes->map(function ($quiz) {
            return [
                'id'           => $quiz->id,
                'title'        => $quiz->title,
                'subject'      => ucfirst((string) ($quiz->subject ?? 'General')),
                'class_name'   => $quiz->classroom->name ?? 'Class',
                'published_at' => $quiz->published_at ? Carbon::parse($quiz->published_at)->format('M d, Y') : '',
                'questions'    => $quiz->questions->map(fn ($q) => $this->serializeQuestionForBankOrArena($q))->values()->all(),
            ];
        })->values()->all();
    }

    public function index()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            $quizzes = Quiz::with(['classroom', 'questions'])->withCount('questions')->latest()->get();
        } elseif ($user->isTeacher()) {
            $classIds = $user->ownedClasses()->pluck('id');
            $quizzes = Quiz::with(['classroom', 'questions'])->withCount('questions')->whereIn('class_id', $classIds)->latest()->get();
        } else {
            $classIds = $user->joinedClasses()->pluck('classes.id');
            $quizzes = Quiz::with(['classroom', 'questions'])
                ->withCount('questions')
                ->whereIn('class_id', $classIds)
                ->whereNotNull('published_at')
                ->latest()
                ->get()
                ->filter(fn ($quiz) => $quiz->isPublished())
                ->values();
        }

        return view('quiz.index', compact('quizzes'));
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        $query = QuizAttempt::with(['quiz.classroom', 'student'])->latest();

        if ($user->role === 'admin') {
            // Admins can view all quiz attempts across the platform
        } elseif ($this->canManageQuizzes($user)) {
            $classIds = $user->ownedClasses()->pluck('id');

            $query->where(function ($q) use ($classIds, $user) {
                $q->whereHas('quiz', function ($quizQuery) use ($classIds) {
                    $quizQuery->whereIn('class_id', $classIds);
                })->orWhere('student_id', $user->id);
            });
        } else {
            $query->where('student_id', $user->id);
        }

        $attempts = $query->paginate(10);

        return view('quiz.history', compact('attempts'));
    }

    public function review(QuizAttempt $attempt)
    {
        $user = Auth::user();
        $attempt->load(['quiz.classroom', 'student', 'responses.question']);

        $isStudentOwner = (int) $attempt->student_id === (int) $user->id;
        $isClassTeacher = $this->canManageQuizzes($user)
            && ($user->role === 'admin' || $user->ownedClasses()->where('id', $attempt->quiz?->class_id)->exists());

        abort_unless($isStudentOwner || $isClassTeacher, 403);

        $quiz = $attempt->quiz;
        $responses = $attempt->responses;

        return view('quiz.review', compact('attempt', 'quiz', 'responses'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        abort_unless($this->canManageQuizzes($user), 403);

        $classes = $user->role === 'admin' ? Classroom::all() : $user->ownedClasses;
        $selectedClass = null;
        $preselectedModule = null;

        if ($request->filled('module_id')) {
            $candidate = Module::find($request->module_id);
            if ($candidate && $classes->contains('id', $candidate->class_id)) {
                $preselectedModule = $candidate;
                $selectedClass = $classes->firstWhere('id', $candidate->class_id);
            }
        }

        if (!$selectedClass && $request->has('class_id')) {
            $selectedClass = $classes->firstWhere('id', $request->class_id);
        }

        if (!$selectedClass && $classes->isNotEmpty()) {
            $selectedClass = $classes->first();
        }

        $questionBank = $this->buildQuestionBank($user, $classes);
        $classModules = $this->buildClassModulesList($classes);

        return view('quiz.create', compact(
            'classes',
            'selectedClass',
            'preselectedModule',
            'questionBank',
            'classModules'
        ));
    }

    public function generate(Request $request)
    {
        $user = Auth::user();
        abort_unless($this->canManageQuizzes($user), 403);

        $request->validate([
            'class_id'       => 'required|exists:classes,id',
            'topic_id'       => 'nullable|integer',
            'quiz_title'     => 'nullable|string|max:255',
            'subject'        => 'required|string|max:255',
            'difficulty'     => 'nullable|string|in:mixed,easy,medium,hard',
            'question_type'  => 'nullable|string|in:multiple_choice,identification,short_answer,true_false',
            'question_count' => 'required|integer|min:1|max:60',
            'type_counts'    => 'nullable|array',
            'max_attempts'   => 'nullable|integer|min:1',
            'module_id'      => 'nullable|exists:modules,id',
            'module_pdf'     => 'required_without:module_id|nullable|file|mimes:pdf,txt,md,docx,pptx,jpg,jpeg,png,webp,mp3,wav,m4a,ogg,flac,mp4,webm|max:102400',
            'published_at'   => 'nullable|date',
            'deadline_at'    => 'nullable|date',
        ]);

        $classes = $user->role === 'admin' ? Classroom::all() : $user->ownedClasses;
        $selectedClass = $classes->firstWhere('id', $request->class_id) ?? $classes->first();
        $preselectedModule = null;
        $tempModuleFile = null;

        try {
            if ($request->hasFile('module_pdf')) {
                $file = $request->file('module_pdf');
                $filePath = $file->getRealPath();
                $originalName = $file->getClientOriginalName();
            } else {
                $preselectedModule = Module::findOrFail($request->module_id);
                abort_unless($classes->contains('id', $preselectedModule->class_id), 403);

                $disk = $this->resolveModuleDisk($preselectedModule->file_path);
                if (!$disk) {
                    throw new Exception('The selected class file could not be found on the server.');
                }

                $originalName = $preselectedModule->file_name;

                if ($disk === 'public') {
                    $filePath = Storage::disk('public')->path($preselectedModule->file_path);
                } else {
                    $ext = strtolower($preselectedModule->file_type ?: pathinfo((string) $originalName, PATHINFO_EXTENSION) ?: 'pdf');
                    $tempBase = tempnam(sys_get_temp_dir(), 'bb_quiz_');
                    $tempModuleFile = $tempBase . '.' . $ext;
                    @unlink($tempBase);
                    file_put_contents($tempModuleFile, Storage::disk($disk)->get($preselectedModule->file_path));
                    $filePath = $tempModuleFile;
                }
            }

            $typeCounts = (array) $request->input('type_counts', []);
            $legacyType = $request->input('question_type', 'multiple_choice');
            $groqType = in_array($legacyType, ['identification', 'short_answer'], true)
                ? 'identification'
                : 'multiple_choice';

            $aiQuestions = $this->groqService->generateQuizFromFile(
                $filePath,
                $originalName,
                $request->subject,
                'Grade 3',
                (int) $request->question_count,
                $groqType,
                $typeCounts,
                (string) $request->input('difficulty', 'mixed')
            );

            $generatedQuestions = [];
            foreach ($aiQuestions as $q) {
                $qType = $this->normalizeQuestionType($q['type'] ?? $legacyType);
                $ans = (string) ($q['correct_answer'] ?? $q['answer'] ?? '');
                $correctAnswers = is_array($q['correct_answers'] ?? null)
                    ? array_values($q['correct_answers'])
                    : ($ans !== '' ? [$ans] : []);
                $matchingPairs = is_array($q['matching_pairs'] ?? null)
                    ? array_values($q['matching_pairs'])
                    : [];

                $options = is_array($q['options'] ?? null) ? array_values($q['options']) : [];
                if ($qType === 'true_false' && empty($options)) {
                    $options = ['True', 'False'];
                }

                $generatedQuestions[] = [
                    'prompt'          => $q['question_text'] ?? $q['question'] ?? '',
                    'type'            => $qType,
                    'options'         => $options,
                    'correct_answer'  => $ans,
                    'correct_answers' => $correctAnswers,
                    'matching_pairs'  => $matchingPairs,
                    'difficulty'      => strtolower($q['difficulty_level'] ?? $q['difficulty'] ?? 'easy'),
                    'time_limit'      => 30,
                    'points'          => 100,
                ];
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'   => true,
                    'questions' => $generatedQuestions,
                ]);
            }

            $questionBank = $this->buildQuestionBank($user, $classes);
            $classModules = $this->buildClassModulesList($classes);

            return view('quiz.create', [
                'classes'            => $classes,
                'selectedClass'      => $selectedClass,
                'preselectedModule'  => $preselectedModule,
                'questionBank'       => $questionBank,
                'classModules'       => $classModules,
                'generatedQuestions' => $generatedQuestions,
                'quiz_title'         => $request->quiz_title,
                'subject'            => $request->subject,
                'topic_id'           => $request->topic_id,
                'question_type'      => $legacyType,
                'max_attempts'       => $request->max_attempts ?? 1,
                'published_at'       => $request->published_at,
                'deadline_at'        => $request->deadline_at,
            ]);
        } catch (Exception $e) {
            Log::error('Quiz Generation Error: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors(['module_pdf' => 'Failed to generate quiz: ' . $e->getMessage()]);
        } finally {
            if ($tempModuleFile && file_exists($tempModuleFile)) {
                @unlink($tempModuleFile);
            }
        }
    }

    public function generateFromPdf(Request $request)
    {
        abort_unless($this->canManageQuizzes(Auth::user()), 403);

        $request->validate([
            'pdf_file'      => 'required|file|mimes:pdf|max:10240',
            'subject'       => 'required|string',
            'grade_level'   => 'required|string',
            'item_count'    => 'required|integer|min:1|max:50',
            'question_type' => 'nullable|string|in:multiple_choice,identification,short_answer',
        ]);

        try {
            $file = $request->file('pdf_file');
            $tempPath = $file->getRealPath();

            $questions = $this->groqService->generateQuizFromPdf(
                $tempPath,
                $request->subject,
                $request->grade_level,
                (int) $request->item_count,
                $request->input('question_type', 'multiple_choice')
            );

            return response()->json([
                'success'   => true,
                'questions' => $questions
            ]);
        } catch (Exception $e) {
            Log::error('Quiz PDF Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Normalize incoming question payload across all 7 question types.
     */
    protected function buildQuestionAttributes(array $qData, ?int $topicId = null): array
    {
        $type = $this->normalizeQuestionType($qData['type'] ?? 'multiple_choice');
        $prompt = trim((string) ($qData['text'] ?? $qData['prompt'] ?? ''));

        $options = [];
        $correctAnswers = [];
        $matchingPairs = [];
        $correctAnswerString = '';

        if ($type === 'multiple_choice') {
            $options = array_values(array_filter(
                array_map(fn ($v) => trim((string) $v), (array) ($qData['options'] ?? [])),
                fn ($v) => $v !== ''
            ));
            $correctIdx = $qData['correct_index'] ?? null;
            if ($correctIdx !== null && isset($options[(int) $correctIdx])) {
                $correctAnswerString = $options[(int) $correctIdx];
            } else {
                $correctAnswerString = trim((string) ($qData['answer'] ?? ($options[0] ?? '')));
            }
            $correctAnswers = $correctAnswerString !== '' ? [$correctAnswerString] : [];
        } elseif ($type === 'multiple_select') {
            $options = array_values(array_filter(
                array_map(fn ($v) => trim((string) $v), (array) ($qData['options'] ?? [])),
                fn ($v) => $v !== ''
            ));
            $checkedIndices = array_map('intval', (array) ($qData['correct_indices'] ?? []));
            $selectedTexts = [];
            foreach ($checkedIndices as $idx) {
                if (isset($options[$idx])) {
                    $selectedTexts[] = $options[$idx];
                }
            }
            if (empty($selectedTexts) && !empty($qData['answer'])) {
                $decoded = json_decode((string) $qData['answer'], true);
                $selectedTexts = is_array($decoded) ? $decoded : array_map('trim', explode(',', (string) $qData['answer']));
            }
            $correctAnswers = array_values(array_unique(array_filter($selectedTexts, fn ($v) => $v !== '')));
            $correctAnswerString = json_encode($correctAnswers);
        } elseif ($type === 'true_false') {
            $options = ['True', 'False'];
            $rawTf = trim((string) ($qData['answer'] ?? 'True'));
            $correctAnswerString = strcasecmp($rawTf, 'False') === 0 ? 'False' : 'True';
            $correctAnswers = [$correctAnswerString];
        } elseif ($type === 'matching') {
            foreach ((array) ($qData['pairs'] ?? []) as $pair) {
                $left  = trim((string) ($pair['left'] ?? ''));
                $right = trim((string) ($pair['right'] ?? ''));
                if ($left !== '' && $right !== '') {
                    $matchingPairs[] = ['left' => $left, 'right' => $right];
                }
            }
            if (empty($matchingPairs) && !empty($qData['answer'])) {
                $decoded = json_decode((string) $qData['answer'], true);
                if (is_array($decoded)) {
                    $matchingPairs = $decoded;
                }
            }
            $correctAnswers = $matchingPairs;
            $correctAnswerString = json_encode($matchingPairs);
        } elseif ($type === 'fill_blank') {
            $blanks = array_values(array_filter(
                array_map(fn ($v) => trim((string) $v), (array) ($qData['blanks'] ?? [])),
                fn ($v) => $v !== ''
            ));
            if (empty($blanks) && !empty($qData['answer'])) {
                $decoded = json_decode((string) $qData['answer'], true);
                $blanks = is_array($decoded) ? $decoded : [trim((string) $qData['answer'])];
            }
            $correctAnswers = $blanks;
            $correctAnswerString = json_encode($blanks);
        } elseif ($type === 'short_answer') {
            $rawShort = trim((string) ($qData['answer'] ?? ''));
            $correctAnswerString = $rawShort;
            $correctAnswers = $rawShort !== ''
                ? array_values(array_filter(array_map('trim', explode(',', $rawShort)), fn ($v) => $v !== ''))
                : [];
        } elseif ($type === 'essay') {
            $rubric = trim((string) ($qData['answer'] ?? ''));
            $correctAnswerString = $rubric;
            $correctAnswers = $rubric !== '' ? [$rubric] : [];
        }

        $attributes = [
            'topic_id'           => $topicId,
            'question_text'      => $prompt,
            'question_type'      => $type,
            'options'            => $options,
            'correct_answer'     => $correctAnswerString,
            'difficulty_level'   => strtolower($qData['difficulty'] ?? 'easy'),
            'time_limit_seconds' => $this->resolveQuestionSeconds($qData),
            'points'             => (int) ($qData['points'] ?? 100),
        ];

        if ($this->hasQuestionsTypeColumn()) {
            $attributes['type'] = $type;
        }
        if ($this->hasQuestionsMatchingPairsColumn()) {
            $attributes['matching_pairs'] = $matchingPairs;
        }
        if ($this->hasQuestionsCorrectAnswersColumn()) {
            $attributes['correct_answers'] = $correctAnswers;
        }
        if ($this->hasQuestionsExplanationColumn()) {
            $attributes['explanation'] = trim((string) ($qData['explanation'] ?? '')) ?: null;
        }

        return $attributes;
    }

    /**
     * Find an existing quiz by quiz_id or draft_token owned by the current user so auto-saves never create duplicates.
     */
    protected function findExistingDraftForUser($user, Request $request): ?Quiz
    {
        $allowedClassIds = $user->role === 'admin'
            ? Classroom::pluck('id')
            : $user->ownedClasses()->pluck('id');

        if ($request->filled('quiz_id')) {
            $byId = Quiz::where('id', (int) $request->input('quiz_id'))
                ->whereIn('class_id', $allowedClassIds)
                ->first();
            if ($byId) {
                return $byId;
            }
        }

        $draftToken = trim((string) $request->input('draft_token', ''));
        if ($draftToken !== '') {
            $candidates = Quiz::whereIn('class_id', $allowedClassIds)
                ->latest('id')
                ->limit(25)
                ->get();

            foreach ($candidates as $candidate) {
                $cfg = is_array($candidate->config) ? $candidate->config : [];
                if (($cfg['draft_token'] ?? '') === $draftToken) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($this->canManageQuizzes($user), 403);

        // If this session already created/owns a quiz (via quiz_id or draft_token), update it in place instead of creating a duplicate
        $existingQuiz = $this->findExistingDraftForUser($user, $request);
        if ($existingQuiz) {
            return $this->update($request, $existingQuiz);
        }

        $isAutoSave = $request->boolean('auto_save') || $request->expectsJson() || $request->ajax();

        if ($isAutoSave) {
            $request->validate([
                'class_id'               => 'required|exists:classes,id',
                'topic_id'               => 'nullable|integer',
                'quiz_id'                => 'nullable|integer',
                'draft_token'            => 'nullable|string|max:120',
                'quiz_title'             => 'nullable|string|max:255',
                'subject'                => 'nullable|string|max:255',
                'max_attempts'           => 'nullable|integer|min:1',
                'published_at'           => 'nullable|date',
                'deadline_at'            => 'nullable|date',
                'save_action'            => 'nullable|string|in:draft,publish',
                'questions'              => 'nullable|array',
                'questions.*.id'         => 'nullable|integer',
                'questions.*.text'       => 'nullable|string',
                'questions.*.type'       => 'nullable|string|in:' . implode(',', self::QUESTION_TYPES),
                'questions.*.options'    => 'nullable|array',
                'questions.*.answer'     => 'nullable|string',
                'questions.*.difficulty' => 'nullable|string|in:easy,medium,hard',
                'questions.*.time_value' => 'nullable|integer|min:1',
                'questions.*.time_unit'  => 'nullable|string|in:seconds,minutes,hours',
            ]);

            $rawTitle = trim((string) $request->input('quiz_title', ''));
            $rawQuestions = (array) $request->input('questions', []);
            $hasAnyQuestionText = collect($rawQuestions)->contains(fn ($q) => trim((string) ($q['text'] ?? '')) !== '');

            if ($rawTitle === '' && !$hasAnyQuestionText) {
                return response()->json([
                    'success' => true,
                    'skipped' => true,
                ]);
            }
        } else {
            $request->validate([
                'class_id'               => 'required|exists:classes,id',
                'topic_id'               => 'nullable|integer',
                'quiz_id'                => 'nullable|integer',
                'draft_token'            => 'nullable|string|max:120',
                'quiz_title'             => 'required|string|max:255',
                'subject'                => 'required|string|max:255',
                'max_attempts'           => 'nullable|integer|min:1',
                'published_at'           => 'nullable|date',
                'deadline_at'            => 'nullable|date',
                'save_action'            => 'nullable|string|in:draft,publish',
                'questions'              => 'required|array|min:1',
                'questions.*.text'       => 'required|string',
                'questions.*.type'       => 'nullable|string|in:' . implode(',', self::QUESTION_TYPES),
                'questions.*.options'    => 'nullable|array',
                'questions.*.answer'     => 'nullable|string',
                'questions.*.difficulty' => 'nullable|string|in:easy,medium,hard',
                'questions.*.time_value' => 'nullable|integer|min:1',
                'questions.*.time_unit'  => 'nullable|string|in:seconds,minutes,hours',
            ]);
        }

        $hasQuizId = $this->hasQuestionsQuizIdColumn();
        $hasPivot  = $this->hasQuizQuestionsPivotTable();
        $isPublish = !$isAutoSave && $request->input('save_action', 'publish') === 'publish';
        $draftToken = trim((string) $request->input('draft_token', ''));

        $result = DB::transaction(function () use ($request, $hasQuizId, $hasPivot, $isPublish, $draftToken) {
            $title = trim((string) $request->input('quiz_title', '')) ?: 'Untitled Draft Quiz';
            $subject = trim((string) $request->input('subject', '')) ?: 'General';

            $config = [
                'is_draft'             => !$isPublish,
                'scheduled_publish_at' => $request->published_at,
            ];
            if ($draftToken !== '') {
                $config['draft_token'] = $draftToken;
            }

            $quizAttributes = [
                'class_id'     => $request->class_id,
                'topic_id'     => $request->topic_id ?? null,
                'title'        => $title,
                'subject'      => $subject,
                'type'         => $request->quiz_mode ?? 'standard',
                'config'       => $config,
                'published_at' => $isPublish ? ($request->published_at ?? now()) : null,
                'deadline_at'  => $request->deadline_at ?? now()->addDays(7),
                'max_attempts' => $request->max_attempts ?? 1,
            ];

            if ($this->hasQuizzesIsPublishedColumn()) {
                $quizAttributes['is_published'] = $isPublish;
            }
            if ($this->hasQuizzesStatusColumn()) {
                $quizAttributes['status'] = $isPublish ? 'published' : 'draft';
            }

            $quiz = Quiz::create($quizAttributes);

            $questionIds = [];
            foreach ((array) $request->input('questions', []) as $qData) {
                $questionData = $this->buildQuestionAttributes($qData, $request->topic_id ?? null);

                if ($hasQuizId) {
                    $questionData['quiz_id'] = $quiz->id;
                }

                $question = Question::create($questionData);
                $questionIds[] = $question->id;
            }

            if ($hasPivot && !empty($questionIds)) {
                $quiz->questions()->syncWithoutDetaching($questionIds);
            }

            return [
                'quiz'         => $quiz,
                'question_ids' => $questionIds,
            ];
        });

        $createdQuiz = $result['quiz'];

        if ($isAutoSave) {
            return response()->json([
                'success'      => true,
                'quiz_id'      => $createdQuiz->id,
                'update_url'   => route('quiz.update', $createdQuiz),
                'question_ids' => $result['question_ids'],
                'status'       => 'draft',
                'saved_at'     => now()->format('h:i:s A'),
            ]);
        }

        $redirectRoute = $user->role === 'admin' ? 'admin.quizzes.index' : 'quiz.index';
        $message = $isPublish
            ? 'Quiz published & added to your Question Bank!'
            : 'Quiz saved as a Draft! You can edit or publish it anytime.';

        return redirect()->route($redirectRoute)->with('success', $message);
    }

    public function edit(Quiz $quiz)
    {
        $user = Auth::user();
        abort_unless($this->canManageQuizzes($user), 403);

        if ($user->role !== 'admin') {
            abort_unless($user->ownedClasses->contains($quiz->class_id), 403);
        }

        // Automatically unpublish a published quiz when entering the quiz editor
        if ($quiz->isPublished()) {
            $config = is_array($quiz->config) ? $quiz->config : [];
            $config['is_draft'] = true;
            $config['scheduled_publish_at'] = $quiz->published_at?->toDateTimeString();

            $unpublishData = [
                'published_at' => null,
                'config'       => $config,
            ];
            if ($this->hasQuizzesIsPublishedColumn()) {
                $unpublishData['is_published'] = false;
            }
            if ($this->hasQuizzesStatusColumn()) {
                $unpublishData['status'] = 'draft';
            }

            $quiz->update($unpublishData);
            session()->flash('info', 'This published quiz was automatically unpublished so you can edit it safely. Click "Publish Quiz Now" when finished.');
        }

        $quiz->load('questions');
        $classes = $user->role === 'admin' ? Classroom::all() : $user->ownedClasses;
        $selectedClass = $classes->firstWhere('id', $quiz->class_id) ?? $classes->first();
        $questionBank = $this->buildQuestionBank($user, $classes);
        $classModules = $this->buildClassModulesList($classes);

        $generatedQuestions = $quiz->questions
            ->map(fn ($q) => $this->serializeQuestionForBankOrArena($q))
            ->values()
            ->all();

        $scheduledPublish = $quiz->published_at
            ?? ($quiz->config['scheduled_publish_at'] ?? null);

        return view('quiz.create', [
            'classes'            => $classes,
            'selectedClass'      => $selectedClass,
            'preselectedModule'  => null,
            'questionBank'       => $questionBank,
            'classModules'       => $classModules,
            'editingQuiz'        => $quiz,
            'generatedQuestions' => $generatedQuestions,
            'quiz_title'         => $quiz->title,
            'subject'            => $quiz->subject,
            'max_attempts'       => $quiz->max_attempts,
            'published_at'       => $scheduledPublish,
            'deadline_at'        => $quiz->deadline_at,
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $user = Auth::user();
        abort_unless($this->canManageQuizzes($user), 403);

        if ($user->role !== 'admin') {
            abort_unless($user->ownedClasses->contains($quiz->class_id), 403);
        }

        $isAutoSave = $request->boolean('auto_save') || $request->expectsJson() || $request->ajax();

        $request->validate([
            'title'                  => 'nullable|string|max:255',
            'quiz_title'             => 'nullable|string|max:255',
            'quiz_id'                => 'nullable|integer',
            'draft_token'            => 'nullable|string|max:120',
            'class_id'               => 'nullable|exists:classes,id',
            'subject'                => 'nullable|string|max:255',
            'max_attempts'           => 'nullable|integer|min:1',
            'published_at'           => 'nullable|date',
            'deadline_at'            => 'nullable|date',
            'save_action'            => 'nullable|string|in:draft,publish',
            'questions'              => 'nullable|array',
            'questions.*.id'         => 'nullable|integer',
            'questions.*.text'       => $isAutoSave ? 'nullable|string' : 'required_with:questions|string',
            'questions.*.type'       => 'nullable|string|in:' . implode(',', self::QUESTION_TYPES),
            'questions.*.options'    => 'nullable|array',
            'questions.*.answer'     => 'nullable|string',
            'questions.*.difficulty' => 'nullable|string|in:easy,medium,hard',
            'questions.*.time_value' => 'nullable|integer|min:1',
            'questions.*.time_unit'  => 'nullable|string|in:seconds,minutes,hours',
        ]);

        $hasQuizId = $this->hasQuestionsQuizIdColumn();
        $hasPivot  = $this->hasQuizQuestionsPivotTable();
        $isPublish = !$isAutoSave && $request->input('save_action', 'publish') === 'publish';
        $draftToken = trim((string) $request->input('draft_token', ''));

        $keptIds = DB::transaction(function () use ($request, $quiz, $hasQuizId, $hasPivot, $isPublish, $draftToken) {
            $incomingTitle = trim((string) $request->input('quiz_title', $request->input('title', '')));
            if ($incomingTitle !== '') {
                $quiz->title = $incomingTitle;
            }
            if ($request->filled('class_id')) {
                $quiz->class_id = $request->class_id;
            }
            if ($request->filled('subject')) {
                $quiz->subject = $request->subject;
            }
            if ($request->filled('max_attempts')) {
                $quiz->max_attempts = $request->max_attempts;
            }
            if ($request->filled('deadline_at')) {
                $quiz->deadline_at = $request->deadline_at;
            }

            $config = is_array($quiz->config) ? $quiz->config : [];
            $config['is_draft'] = !$isPublish;
            if ($draftToken !== '') {
                $config['draft_token'] = $draftToken;
            }
            if ($request->filled('published_at')) {
                $config['scheduled_publish_at'] = $request->published_at;
            }
            $quiz->config = $config;

            if ($isPublish) {
                $quiz->published_at = $request->published_at ?? ($config['scheduled_publish_at'] ?? now());
            } else {
                $quiz->published_at = null;
            }

            if ($this->hasQuizzesIsPublishedColumn()) {
                $quiz->is_published = $isPublish;
            }
            if ($this->hasQuizzesStatusColumn()) {
                $quiz->status = $isPublish ? 'published' : 'draft';
            }

            $quiz->save();

            $savedQuestionIds = [];
            if ($request->has('questions')) {
                $incomingIds = collect($request->questions)->pluck('id')->filter()->map(fn ($v) => (int) $v);
                $existingQuestions = Question::whereIn('id', $incomingIds)->get()->keyBy('id');

                foreach ((array) $request->questions as $qData) {
                    $attributes = $this->buildQuestionAttributes($qData, $quiz->topic_id);
                    $qId = !empty($qData['id']) ? (int) $qData['id'] : null;

                    if ($qId && $existingQuestions->has($qId)) {
                        $question = $existingQuestions->get($qId);
                        $question->update($attributes);
                    } else {
                        if ($hasQuizId) {
                            $attributes['quiz_id'] = $quiz->id;
                        }
                        $question = Question::create($attributes);
                    }

                    $savedQuestionIds[] = $question->id;
                }

                if ($hasPivot) {
                    $quiz->questions()->sync($savedQuestionIds);
                } elseif ($hasQuizId) {
                    Question::where('quiz_id', $quiz->id)->whereNotIn('id', $savedQuestionIds)->delete();
                }
            }

            return $savedQuestionIds;
        });

        if ($isAutoSave) {
            return response()->json([
                'success'      => true,
                'quiz_id'      => $quiz->id,
                'update_url'   => route('quiz.update', $quiz),
                'status'       => $isPublish ? 'published' : 'draft',
                'question_ids' => $keptIds,
                'saved_at'     => now()->format('h:i:s A'),
            ]);
        }

        $redirectRoute = $user->role === 'admin' ? 'admin.quizzes.index' : 'quiz.index';
        $message = $isPublish
            ? 'Quiz updated and published!'
            : 'Draft changes saved!';

        return redirect()->route($redirectRoute)->with('success', $message);
    }

    public function publish(Quiz $quiz)
    {
        $user = Auth::user();
        abort_unless($this->canManageQuizzes($user), 403);

        if ($user->role !== 'admin') {
            abort_unless($user->ownedClasses->contains($quiz->class_id), 403);
        }

        $config = is_array($quiz->config) ? $quiz->config : [];
        $config['is_draft'] = false;

        $updateData = [
            'published_at' => $quiz->published_at ?? ($config['scheduled_publish_at'] ?? now()),
            'config'       => $config,
        ];
        if ($this->hasQuizzesIsPublishedColumn()) {
            $updateData['is_published'] = true;
        }
        if ($this->hasQuizzesStatusColumn()) {
            $updateData['status'] = 'published';
        }

        $quiz->update($updateData);

        return redirect()->route('quiz.index')->with('success', "'{$quiz->title}' is now published to students!");
    }

    public function unpublish(Quiz $quiz)
    {
        $user = Auth::user();
        abort_unless($this->canManageQuizzes($user), 403);

        if ($user->role !== 'admin') {
            abort_unless($user->ownedClasses->contains($quiz->class_id), 403);
        }

        $config = is_array($quiz->config) ? $quiz->config : [];
        $config['is_draft'] = true;
        $config['scheduled_publish_at'] = $quiz->published_at?->toDateTimeString();

        $updateData = [
            'published_at' => null,
            'config'       => $config,
        ];
        if ($this->hasQuizzesIsPublishedColumn()) {
            $updateData['is_published'] = false;
        }
        if ($this->hasQuizzesStatusColumn()) {
            $updateData['status'] = 'draft';
        }

        $quiz->update($updateData);

        return redirect()->route('quiz.index')->with('info', "'{$quiz->title}' was unpublished and moved back to Drafts.");
    }

    public function show(Quiz $quiz)
    {
        $user = Auth::user();
        if ($user->isStudent()) {
            abort_unless($user->joinedClasses->contains($quiz->class_id) && $quiz->isPublished(), 403);
        }

        if ($this->canManageQuizzes($user)) {
            $quiz->load('questions');
            $attempts = QuizAttempt::with('student')
                ->where('quiz_id', $quiz->id)
                ->latest()
                ->get();
        } else {
            $quiz->loadCount('questions');
            $attempts = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $user->id)
                ->latest()
                ->get();
        }

        return view('quiz.show', compact('quiz', 'attempts'));
    }

    public function analyzeStruggles(Request $request, Quiz $quiz)
    {
        abort_unless($this->canManageQuizzes(Auth::user()), 403);

        $attemptId = $request->input('attempt_id');
        $studentName = null;

        if ($attemptId) {
            $attempts = QuizAttempt::with(['student', 'responses.question'])
                ->where('quiz_id', $quiz->id)
                ->where('id', $attemptId)
                ->get();

            if ($attempts->isNotEmpty() && $attempts->first()->student) {
                $studentName = $attempts->first()->student->first_name . ' ' . $attempts->first()->student->last_name;
            }
        } else {
            $attempts = QuizAttempt::with(['responses.question'])
                ->where('quiz_id', $quiz->id)
                ->get();
        }

        $wrongQuestions = [];
        foreach ($attempts as $attempt) {
            foreach ($attempt->responses as $resp) {
                if (!$resp->is_correct && $resp->question) {
                    $decodedAns = json_decode((string) $resp->question->correct_answer, true);
                    $readableAns = is_array($decodedAns)
                        ? json_encode($decodedAns, JSON_UNESCAPED_UNICODE)
                        : (string) $resp->question->correct_answer;

                    $wrongQuestions[] = [
                        'question'       => $resp->question->question_text,
                        'correct_answer' => $readableAns,
                        'difficulty'     => $resp->question->difficulty_level ?? 'easy',
                        'question_type'  => $resp->question->question_type ?? 'multiple_choice',
                    ];
                }
            }
        }

        try {
            $analysis = $this->groqService->analyzeStudentStruggles($wrongQuestions, $studentName);

            return response()->json([
                'success'     => true,
                'analysis'    => $analysis,
                'wrong_count' => count($wrongQuestions),
                'student'     => $studentName,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 422);
        }
    }

    protected function prepareClientQuestion(array $q): array
    {
        $clientQuestion = $q;
        unset($clientQuestion['correct_answer'], $clientQuestion['correct_index'], $clientQuestion['correct_answers']);

        if (($q['type'] ?? '') === 'matching') {
            $pairs = $q['matching_pairs'] ?? [];
            $leftItems = array_map(fn ($p) => (string) ($p['left'] ?? ''), $pairs);
            $rightItems = array_map(fn ($p) => (string) ($p['right'] ?? ''), $pairs);
            shuffle($rightItems);

            $clientQuestion['left_items'] = $leftItems;
            $clientQuestion['right_items'] = $rightItems;
            unset($clientQuestion['matching_pairs']);
        }

        return $clientQuestion;
    }

    public function start(Request $request)
    {
        $request->validate(['quiz_id' => 'required|exists:quizzes,id']);
        $quiz = Quiz::with('questions')->findOrFail($request->quiz_id);
        $user = Auth::user();

        abort_unless($user->isStudent() && $quiz->isPublished(), 403);

        $attemptsCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $user->id)
            ->count();

        if ($quiz->max_attempts && $attemptsCount >= $quiz->max_attempts) {
            return response()->json([
                'error' => 'This quiz is closed. You have used all available attempts.'
            ], 403);
        }

        $userRating = $user->skill_rating ?? 1200;
        $startingLevel = 'easy';
        if ($userRating >= 1400) {
            $startingLevel = 'hard';
        } elseif ($userRating >= 1100) {
            $startingLevel = 'medium';
        }

        $questionsPool = [];
        foreach ($quiz->questions as $q) {
            $serialized = $this->serializeQuestionForBankOrArena($q);
            $correctIndex = array_search($serialized['correct_answer'], $serialized['options'], true);

            $questionsPool[$q->id] = [
                'id'              => $q->id,
                'text'            => $serialized['prompt'],
                'type'            => $serialized['type'],
                'options'         => $serialized['options'],
                'correct_answer'  => $serialized['correct_answer'],
                'correct_answers' => $serialized['correct_answers'],
                'matching_pairs'  => $serialized['matching_pairs'],
                'correct_index'   => $correctIndex !== false ? $correctIndex : 0,
                'difficulty'      => $serialized['difficulty'],
                'time_limit'      => $serialized['time_limit'],
                'points'          => $serialized['points'],
            ];
        }

        $progress = [
            'quiz_id'        => $quiz->id,
            'subject'        => $quiz->subject,
            'level'          => $startingLevel,
            'index'          => 0,
            'total'          => count($questionsPool),
            'score'          => 0,
            'current_streak' => 0,
            'max_streak'     => 0,
            'total_xp'       => 0,
            'log'            => [],
            'asked_ids'      => [],
            'questions_pool' => $questionsPool,
            'started_at'     => now()->toDateTimeString(),
        ];

        $question = $this->pickNextQuestion($progress);

        if (!empty($question['id'])) {
            $progress['asked_ids'][] = $question['id'];
        }

        Session::put('quiz_progress', $progress);

        return response()->json([
            'question'        => $this->prepareClientQuestion($question),
            'question_number' => 1,
            'total_questions' => $progress['total'],
            'level'           => $progress['level'],
            'current_streak'  => 0,
            'total_xp'        => 0,
        ]);
    }

    public function saveAndExit(Request $request)
    {
        $progress = Session::get('quiz_progress');

        if (!$progress) {
            return response()->json([
                'success' => true,
                'message' => 'No active quiz session found.'
            ]);
        }

        $answeredCount = count($progress['log'] ?? []);

        if ($answeredCount > 0) {
            try {
                $this->persistAttempt($progress, false);
            } catch (\Throwable $e) {
                Log::error("Quiz Save & Exit DB Error: " . $e->getMessage());
            }
        }

        Session::forget('quiz_progress');

        return response()->json([
            'success'  => true,
            'message'  => 'Quiz exited and progress saved.',
            'redirect' => route('quiz.show', $progress['quiz_id'])
        ]);
    }

    /**
     * Evaluate student response across all 7 question types.
     */
    protected function evaluateStudentAnswer(array $questionData, Request $request): array
    {
        $type = $this->normalizeQuestionType($questionData['type'] ?? 'multiple_choice');
        $options = $questionData['options'] ?? [];
        $correctAnswers = $questionData['correct_answers'] ?? [];
        $isCorrect = false;
        $recordedTyped = $request->input('typed_answer');
        $recordedIndex = $request->input('selected_index');

        if ($type === 'multiple_choice' || $type === 'true_false') {
            if ($recordedIndex !== null && isset($options[$recordedIndex])) {
                $userChoice = trim((string) $options[$recordedIndex]);
                $correctChoice = trim((string) ($questionData['correct_answer'] ?? ($correctAnswers[0] ?? '')));
                $isCorrect = ($correctChoice !== '' && mb_strtolower($userChoice) === mb_strtolower($correctChoice));
            }
        } elseif ($type === 'multiple_select') {
            $selectedIndices = array_map('intval', (array) $request->input('selected_indices', []));
            $chosenTexts = [];
            foreach ($selectedIndices as $idx) {
                if (isset($options[$idx])) {
                    $chosenTexts[] = mb_strtolower(trim((string) $options[$idx]));
                }
            }
            $expectedTexts = array_map(fn ($v) => mb_strtolower(trim((string) $v)), $correctAnswers);
            sort($chosenTexts);
            sort($expectedTexts);
            $chosenTexts = array_values(array_unique($chosenTexts));
            $expectedTexts = array_values(array_unique($expectedTexts));

            $isCorrect = !empty($expectedTexts) && $chosenTexts === $expectedTexts;
            $recordedTyped = json_encode(array_map(fn ($i) => $options[$i] ?? $i, $selectedIndices));
        } elseif ($type === 'matching') {
            $pairs = $questionData['matching_pairs'] ?? [];
            $submittedPairs = (array) $request->input('matched_pairs', []);
            if (!empty($pairs) && count($submittedPairs) >= count($pairs)) {
                $allMatched = true;
                foreach ($pairs as $idx => $pair) {
                    $expectedRight = mb_strtolower(trim((string) ($pair['right'] ?? '')));
                    $givenRight = mb_strtolower(trim((string) ($submittedPairs[$idx] ?? '')));
                    if ($expectedRight === '' || $expectedRight !== $givenRight) {
                        $allMatched = false;
                        break;
                    }
                }
                $isCorrect = $allMatched;
            }
            $recordedTyped = json_encode($submittedPairs);
        } elseif ($type === 'fill_blank') {
            $submittedBlanks = array_values((array) $request->input('blank_answers', []));
            if (!empty($correctAnswers)) {
                $allBlanksOk = true;
                foreach ($correctAnswers as $idx => $acceptedRaw) {
                    $studentVal = mb_strtolower(trim((string) ($submittedBlanks[$idx] ?? '')));
                    $variants = array_map(fn ($v) => mb_strtolower(trim($v)), explode('|', (string) $acceptedRaw));
                    if ($studentVal === '' || !in_array($studentVal, $variants, true)) {
                        $allBlanksOk = false;
                        break;
                    }
                }
                $isCorrect = $allBlanksOk;
            }
            $recordedTyped = json_encode($submittedBlanks);
        } elseif ($type === 'short_answer') {
            $userAns = mb_strtolower(trim((string) $recordedTyped));
            if (empty($correctAnswers)) {
                $isCorrect = ($userAns !== '');
            } else {
                $acceptedList = array_map(fn ($v) => mb_strtolower(trim((string) $v)), $correctAnswers);
                $isCorrect = ($userAns !== '' && in_array($userAns, $acceptedList, true));
            }
        } elseif ($type === 'essay') {
            $essayText = trim((string) $recordedTyped);
            $isCorrect = ($essayText !== '');
        }

        return [
            'is_correct'     => $isCorrect,
            'selected_index' => $recordedIndex,
            'typed_answer'   => $recordedTyped,
        ];
    }

    public function submitAnswer(Request $request)
    {
        $request->validate([
            'question_id'        => 'required|integer',
            'selected_index'     => 'nullable|integer',
            'selected_indices'   => 'nullable|array',
            'matched_pairs'      => 'nullable|array',
            'blank_answers'      => 'nullable|array',
            'typed_answer'       => 'nullable|string',
            'time_taken_seconds' => 'nullable|integer|min:0',
        ]);

        $progress = Session::get('quiz_progress');

        if (!$progress) {
            return response()->json(['error' => 'No active quiz session. Please restart the quiz.'], 422);
        }

        $questionData = $progress['questions_pool'][$request->question_id] ?? null;
        if (!$questionData) {
            return response()->json(['error' => 'Invalid question ID.'], 422);
        }

        $eval = $this->evaluateStudentAnswer($questionData, $request);
        $isCorrect = $eval['is_correct'];

        $timeTaken = $request->input('time_taken_seconds', 0);
        $baseXp = $questionData['points'] ?? 100;
        $questionTimeLimit = $questionData['time_limit'] ?? 30;
        $earnedXp = 0;
        $streakMultiplier = 1.0;

        $difficultyMultiplier = match (strtolower($questionData['difficulty'] ?? 'easy')) {
            'hard'   => 2.0,
            'medium' => 1.5,
            default  => 1.0,
        };

        if ($isCorrect) {
            $progress['score']++;
            $progress['current_streak']++;

            if ($progress['current_streak'] > $progress['max_streak']) {
                $progress['max_streak'] = $progress['current_streak'];
            }
            if ($progress['current_streak'] >= 5) {
                $streakMultiplier = 2.0;
            } elseif ($progress['current_streak'] >= 3) {
                $streakMultiplier = 1.5;
            }

            if ($progress['current_streak'] >= 4 && $progress['level'] === 'medium') {
                $progress['level'] = 'hard';
            } elseif ($progress['current_streak'] >= 2 && $progress['level'] === 'easy') {
                $progress['level'] = 'medium';
            }

            $timeBonus = max(0, ($questionTimeLimit - $timeTaken) * 2);
            $earnedXp = (int) round(($baseXp + $timeBonus) * $streakMultiplier * $difficultyMultiplier);
            $progress['total_xp'] += $earnedXp;
        } else {
            $progress['current_streak'] = 0;

            if ($progress['level'] === 'hard') {
                $progress['level'] = 'medium';
            } elseif ($progress['level'] === 'medium') {
                $progress['level'] = 'easy';
            }
        }

        $progress['log'][] = [
            'question_id'        => $request->question_id,
            'selected'           => $eval['selected_index'],
            'typed'              => $eval['typed_answer'],
            'correct'            => $isCorrect,
            'time_taken_seconds' => $timeTaken,
            'xp_earned'          => $earnedXp,
            'multiplier_applied' => $streakMultiplier * $difficultyMultiplier,
        ];

        $progress['index']++;
        $finished = $progress['index'] >= $progress['total'];

        if ($finished) {
            $newBadges = [];

            try {
                $newBadges = $this->persistAttempt($progress, true);
            } catch (\Throwable $e) {
                Log::error("Quiz Completion DB Save Error: " . $e->getMessage());
            }

            Session::forget('quiz_progress');

            return response()->json([
                'finished'    => true,
                'was_correct' => $isCorrect,
                'earned_xp'   => $earnedXp,
                'multiplier'  => $streakMultiplier * $difficultyMultiplier,
                'new_badges'  => $newBadges,
                'summary'     => [
                    'score'      => $progress['score'],
                    'total'      => $progress['total'],
                    'accuracy'   => $progress['total'] > 0 ? ($progress['score'] / $progress['total']) * 100 : 0,
                    'level'      => $progress['level'],
                    'max_streak' => $progress['max_streak'],
                    'total_xp'   => $progress['total_xp'],
                ],
            ]);
        }

        $question = $this->pickNextQuestion($progress);

        if (!empty($question['id'])) {
            $progress['asked_ids'][] = $question['id'];
        }

        Session::put('quiz_progress', $progress);

        return response()->json([
            'finished'        => false,
            'was_correct'     => $isCorrect,
            'question'        => $this->prepareClientQuestion($question),
            'question_number' => $progress['index'] + 1,
            'total_questions' => $progress['total'],
            'current_streak'  => $progress['current_streak'],
            'max_streak'      => $progress['max_streak'],
            'earned_xp'       => $earnedXp,
            'multiplier'      => $streakMultiplier * $difficultyMultiplier,
            'total_xp'        => $progress['total_xp'],
        ]);
    }

    protected function persistAttempt(array $progress, bool $isCompleted): array
    {
        return DB::transaction(function () use ($progress, $isCompleted) {
            $startedAt = isset($progress['started_at']) ? Carbon::parse($progress['started_at']) : now();
            $totalTimeTaken = (int) round(abs($startedAt->diffInSeconds(now())));

            $attempt = QuizAttempt::create([
                'student_id'         => Auth::id(),
                'quiz_id'            => $progress['quiz_id'],
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
                $user->level = (int) floor(($user->total_xp ?? 0) / 1000) + 1;

                if ($isCompleted) {
                    $accuracy = $progress['total'] > 0 ? ($progress['score'] / $progress['total']) : 0;
                    $currentRating = $user->skill_rating ?? 1200;
                    if ($accuracy >= 0.8) {
                        $user->skill_rating = $currentRating + 30;
                    } elseif ($accuracy >= 0.5) {
                        $user->skill_rating = $currentRating + 10;
                    } else {
                        $user->skill_rating = max(800, $currentRating - 20);
                    }
                }

                $user->save();
            }

            $now = now()->toDateTimeString();
            $hasSelIdx = $this->hasResponseSelectedIndexColumn();
            $hasTyped  = $this->hasResponseTypedAnswerColumn();

            $rows = [];
            foreach ($progress['log'] as $entry) {
                $row = [
                    'quiz_attempt_id'    => $attempt->id,
                    'question_id'        => $entry['question_id'],
                    'is_correct'         => $entry['correct'],
                    'time_taken_seconds' => $entry['time_taken_seconds'] ?? null,
                    'xp_earned'          => $entry['xp_earned'] ?? 0,
                    'multiplier_applied' => $entry['multiplier_applied'] ?? 1.0,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
                if ($hasSelIdx) {
                    $row['selected_index'] = $entry['selected'] ?? null;
                }
                if ($hasTyped) {
                    $row['typed_answer'] = is_string($entry['typed'] ?? null) ? $entry['typed'] : null;
                }
                $rows[] = $row;
            }

            if (!empty($rows)) {
                QuestionResponse::insert($rows);
            }

            if ($isCompleted) {
                $quiz = Quiz::find($progress['quiz_id']);
                if ($quiz) {
                    return app(BadgeService::class)->evaluateQuizBadges($quiz, (int) Auth::id());
                }
            }

            return [];
        });
    }

    protected function pickNextQuestion(array $progress): array
    {
        $pool = $progress['questions_pool'] ?? [];
        if (empty($pool)) {
            return [];
        }

        $askedLookup = array_flip($progress['asked_ids'] ?? []);
        $remaining = array_filter($pool, fn ($q) => !isset($askedLookup[$q['id']]));

        if (empty($remaining)) {
            return [];
        }

        $currentLevel = strtolower($progress['level'] ?? 'easy');
        $matchingLevel = array_filter($remaining, fn ($q) => ($q['difficulty'] ?? 'easy') === $currentLevel);

        $candidates = !empty($matchingLevel) ? array_values($matchingLevel) : array_values($remaining);
        return $candidates[array_rand($candidates)];
    }

    public function destroy(Quiz $quiz)
    {
        $user = Auth::user();
        abort_unless($this->canManageQuizzes($user), 403);

        if ($user->role !== 'admin') {
            abort_unless($user->ownedClasses->contains($quiz->class_id), 403);
        }

        $quiz->delete();
        $redirectRoute = $user->role === 'admin' ? 'admin.quizzes.index' : 'quiz.index';
        return redirect()->route($redirectRoute)->with('success', 'Quiz deleted successfully!');
    }
}