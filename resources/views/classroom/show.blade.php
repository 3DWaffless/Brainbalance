@extends('layouts.brainbalance')

@section('title', ($quiz->title ?? 'Quiz Adventure') . ' — BrainBalance')

@push('styles')
<style>
    :root {
        --bb-cream: #FFF8F0;
        --bb-card: #FFFFFF;
        --bb-ink: #1E293B;
        --bb-muted: #64748B;
        --bb-blue: #2563EB;
        --bb-blue-dark: #1D4ED8;
        --bb-cyan: #06B6D4;
        --bb-coral: #F97316;
        --bb-coral-dark: #EA580C;
        --bb-lime: #65A30D;
        --bb-lime-dark: #4D7C0F;
        --bb-gold: #F59E0B;
        --bb-border: #E2E8F0;
    }

    .bb-quiz-arena {
        min-height: calc(100vh - 80px);
        background: radial-gradient(circle at 12% 10%, rgba(37, 99, 235, 0.08), transparent 36%),
                    radial-gradient(circle at 88% 15%, rgba(249, 115, 22, 0.08), transparent 36%),
                    var(--bb-cream);
        padding: 1.75rem 1rem 4.5rem;
        font-family: 'Nunito', sans-serif;
        color: var(--bb-ink);
    }

    .bb-display-font {
        font-family: 'Fredoka', sans-serif;
    }

    .bb-card {
        background: var(--bb-card);
        border: 2px solid var(--bb-border);
        border-bottom-width: 6px;
        border-radius: 1.5rem;
        box-shadow: 0 12px 28px -8px rgba(15, 23, 42, 0.07);
    }

    .bb-btn-3d {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        padding: 0.85rem 1.6rem;
        border-radius: 1.1rem;
        font-family: 'Fredoka', sans-serif;
        font-size: 1.02rem;
        font-weight: 600;
        border: none;
        border-bottom: 5px solid transparent;
        cursor: pointer;
        transition: transform 0.1s ease, border-bottom-width 0.1s ease;
    }

    .bb-btn-3d:active {
        transform: translateY(3px);
        border-bottom-width: 2px;
    }

    .bb-btn-lime {
        background: linear-gradient(135deg, #84CC16, #65A30D);
        border-bottom-color: var(--bb-lime-dark);
        color: #FFFFFF;
    }

    .bb-btn-blue {
        background: linear-gradient(135deg, var(--bb-blue), var(--bb-cyan));
        border-bottom-color: var(--bb-blue-dark);
        color: #FFFFFF;
    }

    .bb-btn-coral {
        background: linear-gradient(135deg, var(--bb-coral), var(--bb-gold));
        border-bottom-color: var(--bb-coral-dark);
        color: #FFFFFF;
    }

    .bb-option-tile {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        width: 100%;
        padding: 1rem 1.2rem;
        border: 2px solid var(--bb-border);
        border-bottom-width: 5px;
        border-radius: 1.15rem;
        background: #FFFFFF;
        text-align: left;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.12s ease;
    }

    .bb-option-tile:hover {
        border-color: #93C5FD;
        transform: translateY(-1px);
    }

    .bb-option-tile.selected {
        border-color: var(--bb-blue);
        background: #EFF6FF;
        color: #1E3A8A;
    }

    .bb-toast-fade {
        animation: toastSlideIn 0.35s ease forwards, toastFadeOut 0.45s ease 2.55s forwards;
    }

    @keyframes toastSlideIn {
        from { opacity: 0; transform: translateY(-16px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes toastFadeOut {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-10px); }
    }
</style>
@endpush

@section('content')
@php
    $quizQuestions = $questions ?? $quiz->questions ?? collect();
    $studentAttempts = $attempts ?? collect();
    $isTeacher = $isTeacher ?? (auth()->user()?->role === 'teacher' || ($quiz->classroom && $quiz->classroom->teacher_id === auth()->id()));
    $subjectName = ucfirst($quiz->subject ?? $quiz->classroom?->subject ?? 'General');
    $topicName = $quiz->topic?->name ?? $subjectName;
    $isMath = strtolower($subjectName) === 'math' || strtolower($subjectName) === 'mathematics';

    $heroGradient = $isMath
        ? 'linear-gradient(135deg, #1D4ED8 0%, #2563EB 52%, #06B6D4 100%)'
        : 'linear-gradient(135deg, #EA580C 0%, #F97316 52%, #F59E0B 100%)';

    $backUrl = $quiz->classroom && \Illuminate\Support\Facades\Route::has('classroom.show')
        ? route('classroom.show', $quiz->classroom)
        : route('dashboard');

    $totalPoints = $quizQuestions->sum('points') ?: ($quizQuestions->count() * 10);

    // Serialize questions cleanly for the interactive JS quizRunnerModal
    $runnerPayload = $quizQuestions->map(function ($q) {
        $opts = is_array($q->options) ? $q->options : (json_decode($q->options ?? '[]', true) ?: []);
        $pairs = is_array($q->matching_pairs) ? $q->matching_pairs : (json_decode($q->matching_pairs ?? '[]', true) ?: []);
        $rightOptions = collect($pairs)->pluck('right')->filter()->shuffle()->values()->all();

        return [
            'id' => $q->id,
            'type' => $q->type ?? 'multiple_choice',
            'question_text' => $q->question_text,
            'points' => $q->points ?? 10,
            'options' => $opts,
            'matching_left' => collect($pairs)->pluck('left')->values()->all(),
            'matching_right_choices' => $rightOptions,
            'explanation' => $q->explanation ?? '',
        ];
    })->values();
@endphp

{{-- 3-Second Fading Badge Notification Toast Container --}}
<div id="badgeToastContainer" class="fixed top-5 right-5 z-[70] flex flex-col gap-3 pointer-events-none max-w-sm w-full"></div>

<div class="bb-quiz-arena">
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Top Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-slate-600 hover:text-blue-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to {{ $quiz->classroom->name ?? 'Dashboard' }}
            </a>
            <div class="scale-90 origin-right">
                <x-brand-logo />
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-800 font-bold text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Quiz Hero Banner --}}
        <div class="rounded-3xl text-white p-6 sm:p-8 shadow-lg" style="background: {{ $heroGradient }};">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="space-y-2.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-white/20">
                            {{ $subjectName }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-black/15">
                            {{ $topicName }}
                        </span>
                        @if (!empty($quiz->difficulty))
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase bg-amber-400 text-slate-900">
                                {{ $quiz->difficulty }}
                            </span>
                        @endif
                    </div>

                    <h1 class="bb-display-font text-3xl sm:text-4xl font-bold tracking-tight">
                        {{ $quiz->title ?? 'Learning Adventure' }}
                    </h1>

                    @if (!empty($quiz->description))
                        <p class="text-white/90 font-semibold text-sm sm:text-base max-w-2xl">
                            {{ $quiz->description }}
                        </p>
                    @endif

                    <div class="flex flex-wrap items-center gap-4 pt-1 text-xs sm:text-sm font-extrabold text-white/90">
                        <span>{{ $quizQuestions->count() }} Questions</span>
                        <span>&bull;</span>
                        <span>{{ $totalPoints }} Base XP</span>
                        @if (!empty($quiz->time_limit))
                            <span>&bull;</span>
                            <span>{{ $quiz->time_limit }} Min Limit</span>
                        @endif
                    </div>
                </div>

                {{-- Role Action Button --}}
                <div class="shrink-0">
                    @if ($isTeacher)
                        <div class="flex flex-wrap items-center gap-3">
                            @if (\Illuminate\Support\Facades\Route::has('quiz.edit'))
                                <a href="{{ route('quiz.edit', $quiz) }}" class="bb-btn-3d bg-white text-slate-900 border-slate-200">
                                    Edit Quiz
                                </a>
                            @endif
                            @if (\Illuminate\Support\Facades\Route::has('quiz.destroy'))
                                <form action="{{ route('quiz.destroy', $quiz) }}" method="POST" onsubmit="return confirm('Delete this quiz permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bb-btn-3d bg-red-600 text-white border-red-800">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    @else
                        @if ($quizQuestions->isNotEmpty())
                            <button type="button" onclick="openQuizRunner()" class="bb-btn-3d bb-btn-lime text-lg px-7 py-4 shadow-lg">
                                Start Quiz Adventure
                            </button>
                        @else
                            <span class="inline-block px-4 py-2 rounded-xl bg-white/20 text-sm font-extrabold">
                                Questions coming soon
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- TEACHER VIEW: AI DIAGNOSTICS, ANSWER KEY & STUDENT ATTEMPTS           --}}
        {{-- ===================================================================== --}}
        @if ($isTeacher)
            {{-- AI Struggle Diagnostics Panel --}}
            <div class="bb-card p-6 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="bb-display-font text-xl font-bold text-slate-900">AI Class Struggle Diagnostics</h2>
                        <p class="text-xs font-bold text-slate-500">
                            Plain-language summary of concepts students missed across submitted attempts.
                        </p>
                    </div>
                    @if (\Illuminate\Support\Facades\Route::has('quiz.analyzeStruggles'))
                        <button type="button" id="analyzeStrugglesBtn" onclick="runStruggleAnalysis()" class="bb-btn-3d bb-btn-coral">
                            Analyze Class Struggles with AI
                        </button>
                    @endif
                </div>

                <div id="aiDiagnosticsOutput" class="p-4 rounded-2xl bg-amber-50/70 border-2 border-amber-200 text-sm font-semibold text-slate-800 space-y-2">
                    @if (!empty($diagnosticSummary))
                        <ul class="list-disc list-inside space-y-1.5">
                            @foreach (explode("\n", trim($diagnosticSummary)) as $line)
                                @if (trim($line) !== '')
                                    <li>{{ ltrim(trim($line), '-*• ') }}</li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <p class="text-slate-600">
                            Click <strong>Analyze Class Struggles with AI</strong> once students complete attempts to generate plain-language bullet insights.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Answer Key Preview --}}
            <div class="bb-card p-6 space-y-4">
                <h2 class="bb-display-font text-xl font-bold text-slate-900">
                    Question & Answer Key ({{ $quizQuestions->count() }})
                </h2>

                <div class="space-y-4">
                    @foreach ($quizQuestions as $index => $q)
                        @php
                            $opts = is_array($q->options) ? $q->options : (json_decode($q->options ?? '[]', true) ?: []);
                            $pairs = is_array($q->matching_pairs) ? $q->matching_pairs : (json_decode($q->matching_pairs ?? '[]', true) ?: []);
                        @endphp
                        <div class="p-4 rounded-2xl border-2 border-slate-200 bg-slate-50/70 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-blue-700">
                                    Q{{ $index + 1 }} &bull; {{ str_replace('_', ' ', $q->type ?? 'multiple_choice') }}
                                </span>
                                <span class="text-xs font-extrabold text-slate-500">{{ $q->points ?? 10 }} pts</span>
                            </div>

                            <p class="font-extrabold text-slate-900">{{ $q->question_text }}</p>

                            @if (!empty($opts))
                                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-bold">
                                    @foreach ($opts as $oIdx => $optText)
                                        <li class="px-3 py-2 rounded-xl bg-white border border-slate-200">
                                            {{ chr(65 + $oIdx) }}. {{ $optText }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if (!empty($pairs))
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-bold">
                                    @foreach ($pairs as $pair)
                                        <div class="px-3 py-2 rounded-xl bg-white border border-slate-200">
                                            {{ $pair['left'] ?? '' }} &rarr; <span class="text-emerald-700">{{ $pair['right'] ?? '' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="text-xs font-extrabold text-emerald-700">
                                Correct Answer: {{ is_array($q->correct_answer) ? implode(', ', $q->correct_answer) : ($q->correct_answer ?? 'See rubric') }}
                            </div>

                            @if (!empty($q->explanation))
                                <p class="text-xs font-semibold text-slate-500">
                                    Explanation: {{ $q->explanation }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Student Attempts Table --}}
            <div class="bb-card p-6 space-y-4">
                <h2 class="bb-display-font text-xl font-bold text-slate-900">
                    Student Submissions ({{ $studentAttempts->count() }})
                </h2>

                @if ($studentAttempts->isEmpty())
                    <p class="text-sm font-semibold text-slate-500">No students have submitted this quiz yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b-2 border-slate-200 text-xs font-extrabold uppercase text-slate-500">
                                    <th class="py-3 px-3">Student</th>
                                    <th class="py-3 px-3">Score</th>
                                    <th class="py-3 px-3">XP Earned</th>
                                    <th class="py-3 px-3">Time</th>
                                    <th class="py-3 px-3">Submitted</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm font-bold">
                                @foreach ($studentAttempts as $att)
                                    <tr>
                                        <td class="py-3 px-3 text-slate-900">{{ $att->user->name ?? 'Explorer' }}</td>
                                        <td class="py-3 px-3 text-blue-700">{{ $att->score ?? 0 }} / {{ $att->total_points ?? $totalPoints }}</td>
                                        <td class="py-3 px-3 text-amber-600">+{{ $att->xp_earned ?? 0 }} XP</td>
                                        <td class="py-3 px-3 text-slate-600">{{ $att->time_taken ? $att->time_taken . 's' : '—' }}</td>
                                        <td class="py-3 px-3 text-slate-400 text-xs">{{ $att->created_at?->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        {{-- ===================================================================== --}}
        {{-- STUDENT VIEW: ADVENTURE LAUNCHER & ATTEMPT HISTORY                    --}}
        {{-- ===================================================================== --}}
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 bb-card p-6 space-y-4">
                    <h2 class="bb-display-font text-xl font-bold text-slate-900">Your Attempt History</h2>

                    @if ($studentAttempts->isEmpty())
                        <div class="text-center py-8 px-4 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200">
                            <p class="bb-display-font text-lg font-bold text-slate-700">Ready for your first run?</p>
                            <p class="text-sm font-semibold text-slate-500 mt-1">
                                Click <strong>Start Quiz Adventure</strong> above to earn XP, build your streak, and unlock classroom medals!
                            </p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($studentAttempts as $att)
                                <div class="p-4 rounded-2xl border-2 border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="font-extrabold text-slate-900">
                                            Score: {{ $att->score ?? 0 }} / {{ $att->total_points ?? $totalPoints }}
                                        </div>
                                        <div class="text-xs font-bold text-slate-500">
                                            Completed {{ $att->created_at?->diffForHumans() }}
                                            @if (!empty($att->time_taken))
                                                &bull; {{ $att->time_taken }}s
                                            @endif
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800">
                                        +{{ $att->xp_earned ?? 0 }} XP
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bb-card p-6 space-y-4 text-center">
                    <img
                        src="{{ asset('images/medalbox.png') }}"
                        onerror="this.style.display='none'"
                        alt="Medal Treasure Chest"
                        class="w-20 h-20 mx-auto object-contain"
                    >
                    <h3 class="bb-display-font text-lg font-bold text-slate-900">Medal Rewards</h3>
                    <p class="text-xs font-semibold text-slate-500">
                        Finish strong and beat your fastest time to unlock shiny badges in your classroom treasure chest!
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ===================================================================== --}}
{{-- INTERACTIVE STUDENT QUIZ RUNNER MODAL (#quizRunnerModal)              --}}
{{-- ===================================================================== --}}
@if (!$isTeacher && $quizQuestions->isNotEmpty())
<div
    id="quizRunnerModal"
    class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-6 bg-slate-900/75 backdrop-blur-md overflow-y-auto"
>
    <div class="bb-card max-w-3xl w-full p-5 sm:p-8 relative my-auto">

        {{-- Runner Top HUD --}}
        <div id="runnerHud" class="flex flex-wrap items-center justify-between gap-4 pb-4 mb-6 border-b-2 border-slate-100">
            <div class="flex-1 min-w-[180px]">
                <div class="flex items-center justify-between text-xs font-extrabold text-slate-500 mb-1.5">
                    <span id="runnerProgressLabel">Question 1 of {{ $quizQuestions->count() }}</span>
                    <span id="runnerTimerLabel" class="font-mono text-blue-700">00:00</span>
                </div>
                <div class="w-full h-3 rounded-full bg-slate-100 overflow-hidden">
                    <div id="runnerProgressBar" class="h-full rounded-full transition-all duration-300" style="width: 0%; background: linear-gradient(90deg, #2563EB, #06B6D4, #84CC16);"></div>
                </div>
            </div>

            <button
                type="button"
                onclick="closeQuizRunner()"
                class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs"
            >
                Exit
            </button>
        </div>

        {{-- Active Question Viewport --}}
        <div id="runnerQuestionStage" class="space-y-6"></div>

        {{-- Runner Footer Controls --}}
        <div id="runnerFooter" class="flex items-center justify-between gap-3 pt-6 mt-6 border-t-2 border-slate-100">
            <button type="button" id="prevQuestionBtn" onclick="navigateQuestion(-1)" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-sm">
                &larr; Previous
            </button>

            <div class="flex items-center gap-2">
                <button type="button" id="nextQuestionBtn" onclick="navigateQuestion(1)" class="bb-btn-3d bb-btn-blue">
                    Next Question &rarr;
                </button>
                <button type="button" id="submitQuizBtn" onclick="submitQuizAttempt()" class="bb-btn-3d bb-btn-lime hidden">
                    Finish & Submit
                </button>
            </div>
        </div>

        {{-- Completion Summary Stage (Shown after async submit) --}}
        <div id="runnerResultStage" class="hidden text-center space-y-6 py-4"></div>
    </div>
</div>
@endif

@push('scripts')
<script>
    const quizQuestionsData = @json($runnerPayload);
    const submitEndpoint = "{{ \Illuminate\Support\Facades\Route::has('quiz.submit') ? route('quiz.submit', $quiz) : '' }}";
    const analyzeEndpoint = "{{ \Illuminate\Support\Facades\Route::has('quiz.analyzeStruggles') ? route('quiz.analyzeStruggles', $quiz) : '' }}";
    const csrfToken = "{{ csrf_token() }}";
    const timeLimitMinutes = {{ (int) ($quiz->time_limit ?? 0) }};

    let currentIdx = 0;
    let userAnswers = {};
    let timerInterval = null;
    let elapsedSeconds = 0;

    function openQuizRunner() {
        const modal = document.getElementById('quizRunnerModal');
        if (!modal || !quizQuestionsData.length) return;

        currentIdx = 0;
        userAnswers = {};
        elapsedSeconds = 0;

        document.getElementById('runnerHud').classList.remove('hidden');
        document.getElementById('runnerQuestionStage').classList.remove('hidden');
        document.getElementById('runnerFooter').classList.remove('hidden');
        document.getElementById('runnerResultStage').classList.add('hidden');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        startRunnerTimer();
        renderCurrentQuestion();
    }

    function closeQuizRunner() {
        const modal = document.getElementById('quizRunnerModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        clearInterval(timerInterval);
    }

    function startRunnerTimer() {
        clearInterval(timerInterval);
        const label = document.getElementById('runnerTimerLabel');
        const maxSeconds = timeLimitMinutes > 0 ? timeLimitMinutes * 60 : null;

        timerInterval = setInterval(() => {
            elapsedSeconds++;
            const displaySec = maxSeconds ? Math.max(0, maxSeconds - elapsedSeconds) : elapsedSeconds;
            const mins = String(Math.floor(displaySec / 60)).padStart(2, '0');
            const secs = String(displaySec % 60).padStart(2, '0');
            if (label) label.innerText = `${mins}:${secs}`;

            if (maxSeconds && elapsedSeconds >= maxSeconds) {
                clearInterval(timerInterval);
                submitQuizAttempt();
            }
        }, 1000);
    }

    function renderCurrentQuestion() {
        const q = quizQuestionsData[currentIdx];
        const stage = document.getElementById('runnerQuestionStage');
        const progressLabel = document.getElementById('runnerProgressLabel');
        const progressBar = document.getElementById('runnerProgressBar');
        const prevBtn = document.getElementById('prevQuestionBtn');
        const nextBtn = document.getElementById('nextQuestionBtn');
        const submitBtn = document.getElementById('submitQuizBtn');

        if (!q || !stage) return;

        progressLabel.innerText = `Question ${currentIdx + 1} of ${quizQuestionsData.length}`;
        progressBar.style.width = `${((currentIdx + 1) / quizQuestionsData.length) * 100}%`;

        prevBtn.style.visibility = currentIdx === 0 ? 'hidden' : 'visible';
        if (currentIdx === quizQuestionsData.length - 1) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }

        const saved = userAnswers[q.id];
        let inputHtml = '';

        if (q.type === 'multiple_choice') {
            inputHtml = `<div class="space-y-3">
                ${(q.options || []).map((opt, i) => {
                    const selected = String(saved) === String(i) || String(saved) === String(opt);
                    return `
                        <button type="button" onclick="selectSingleOption(${q.id}, ${i})" class="bb-option-tile ${selected ? 'selected' : ''}">
                            <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 font-extrabold flex items-center justify-center shrink-0">${String.fromCharCode(65 + i)}</span>
                            <span>${escapeHtml(opt)}</span>
                        </button>
                    `;
                }).join('')}
            </div>`;
        } else if (q.type === 'multiple_select') {
            const selectedArr = Array.isArray(saved) ? saved.map(String) : [];
            inputHtml = `<div class="space-y-3">
                ${(q.options || []).map((opt, i) => {
                    const isSel = selectedArr.includes(String(i));
                    return `
                        <button type="button" onclick="toggleMultiOption(${q.id}, ${i})" class="bb-option-tile ${isSel ? 'selected' : ''}">
                            <span class="w-8 h-8 rounded-xl ${isSel ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700'} font-extrabold flex items-center justify-center shrink-0">
                                ${isSel ? '✓' : String.fromCharCode(65 + i)}
                            </span>
                            <span>${escapeHtml(opt)}</span>
                        </button>
                    `;
                }).join('')}
            </div>`;
        } else if (q.type === 'true_false') {
            inputHtml = `<div class="grid grid-cols-2 gap-4">
                ${['true', 'false'].map(val => {
                    const isSel = String(saved).toLowerCase() === val;
                    return `
                        <button type="button" onclick="selectSingleOption(${q.id}, '${val}')" class="bb-option-tile justify-center text-lg ${isSel ? 'selected' : ''}">
                            ${val === 'true' ? 'True' : 'False'}
                        </button>
                    `;
                }).join('')}
            </div>`;
        } else if (q.type === 'fill_in_the_blank' || q.type === 'short_answer') {
            inputHtml = `
                <input
                    type="text"
                    value="${escapeHtml(saved ?? '')}"
                    oninput="userAnswers[${q.id}] = this.value"
                    placeholder="Type your answer here..."
                    class="w-full p-4 rounded-2xl border-2 border-slate-300 font-bold text-base focus:outline-none focus:border-blue-600"
                >
            `;
        } else if (q.type === 'matching') {
            const currentMap = (saved && typeof saved === 'object') ? saved : {};
            const choices = q.matching_right_choices || [];
            inputHtml = `<div class="space-y-3">
                ${(q.matching_left || []).map((leftText, lIdx) => `
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center p-3 rounded-2xl bg-slate-50 border-2 border-slate-200">
                        <div class="font-extrabold text-sm text-slate-800">${escapeHtml(leftText)}</div>
                        <select
                            onchange="updateMatchingAnswer(${q.id}, ${lIdx}, this.value)"
                            class="p-2.5 rounded-xl border-2 border-slate-200 font-bold text-sm bg-white"
                        >
                            <option value="">-- Choose Match --</option>
                            ${choices.map(c => `<option value="${escapeHtml(c)}" ${currentMap[lIdx] === c ? 'selected' : ''}>${escapeHtml(c)}</option>`).join('')}
                        </select>
                    </div>
                `).join('')}
            </div>`;
        } else if (q.type === 'essay') {
            inputHtml = `
                <textarea
                    rows="5"
                    oninput="userAnswers[${q.id}] = this.value"
                    placeholder="Write your full response here..."
                    class="w-full p-4 rounded-2xl border-2 border-slate-300 font-semibold text-sm focus:outline-none focus:border-blue-600"
                >${escapeHtml(saved ?? '')}</textarea>
            `;
        }

        stage.innerHTML = `
            <div class="space-y-2">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold uppercase bg-blue-50 text-blue-700">
                    ${q.type.replace(/_/g, ' ')} &bull; ${q.points} pts
                </span>
                <h3 class="bb-display-font text-2xl font-bold text-slate-900 leading-snug">
                    ${escapeHtml(q.question_text)}
                </h3>
            </div>
            ${inputHtml}
        `;
    }

    function selectSingleOption(questionId, value) {
        userAnswers[questionId] = value;
        renderCurrentQuestion();
    }

    function toggleMultiOption(questionId, index) {
        const current = Array.isArray(userAnswers[questionId]) ? [...userAnswers[questionId]] : [];
        const strIdx = String(index);
        const pos = current.indexOf(strIdx);
        if (pos >= 0) {
            current.splice(pos, 1);
        } else {
            current.push(strIdx);
        }
        userAnswers[questionId] = current;
        renderCurrentQuestion();
    }

    function updateMatchingAnswer(questionId, leftIdx, rightValue) {
        if (!userAnswers[questionId] || typeof userAnswers[questionId] !== 'object') {
            userAnswers[questionId] = {};
        }
        userAnswers[questionId][leftIdx] = rightValue;
    }

    function navigateQuestion(delta) {
        const next = currentIdx + delta;
        if (next >= 0 && next < quizQuestionsData.length) {
            currentIdx = next;
            renderCurrentQuestion();
        }
    }

    async function submitQuizAttempt() {
        if (!submitEndpoint) return;
        clearInterval(timerInterval);

        const submitBtn = document.getElementById('submitQuizBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Grading...';
        }

        try {
            const response = await fetch(submitEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    answers: userAnswers,
                    time_taken: elapsedSeconds
                })
            });

            const data = await response.json();

            // Trigger 3-second fading badge notifications returned by BadgeService
            const notifications = data.badge_notifications || data.notifications || data.badges || [];
            if (Array.isArray(notifications)) {
                notifications.forEach(item => triggerBadgeToast(item));
            }

            // Show celebration summary inside modal
            document.getElementById('runnerHud').classList.add('hidden');
            document.getElementById('runnerQuestionStage').classList.add('hidden');
            document.getElementById('runnerFooter').classList.add('hidden');

            const resultStage = document.getElementById('runnerResultStage');
            resultStage.classList.remove('hidden');
            resultStage.innerHTML = `
                <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-400 text-slate-900 flex items-center justify-center text-4xl font-extrabold shadow">
                    ★
                </div>
                <div>
                    <h2 class="bb-display-font text-3xl font-bold text-slate-900">Adventure Complete!</h2>
                    <p class="text-slate-500 font-bold mt-1">Great effort! Here is how you scored:</p>
                </div>
                <div class="grid grid-cols-3 gap-3 max-w-md mx-auto">
                    <div class="p-3.5 rounded-2xl bg-blue-50 border-2 border-blue-200">
                        <div class="text-xs font-extrabold text-blue-600 uppercase">Score</div>
                        <div class="bb-display-font text-2xl font-bold text-slate-900">${data.score ?? 0}/${data.total_points ?? ''}</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-amber-50 border-2 border-amber-200">
                        <div class="text-xs font-extrabold text-amber-600 uppercase">XP Gained</div>
                        <div class="bb-display-font text-2xl font-bold text-slate-900">+${data.xp_earned ?? 0}</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-lime-50 border-2 border-lime-200">
                        <div class="text-xs font-extrabold text-lime-700 uppercase">Time</div>
                        <div class="bb-display-font text-2xl font-bold text-slate-900">${elapsedSeconds}s</div>
                    </div>
                </div>
                <div class="pt-2">
                    <button type="button" onclick="window.location.reload()" class="bb-btn-3d bb-btn-blue">
                        Continue to Summary
                    </button>
                </div>
            `;
        } catch (err) {
            alert('Could not submit quiz right now. Please check your connection and try again.');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Finish & Submit';
            }
        }
    }

    function triggerBadgeToast(notification) {
        const container = document.getElementById('badgeToastContainer');
        if (!container) return;

        const type = notification.type || notification.status || 'earned';
        const isRevoked = type === 'revoked';
        const title = notification.badge_name || notification.title || (isRevoked ? 'Record Reclaimed' : 'New Medal Unlocked!');
        const message = notification.message || (isRevoked ? 'Another explorer beat the fastest record.' : 'Added to your classroom treasure chest!');

        const toast = document.createElement('div');
        toast.className = `bb-toast-fade pointer-events-auto p-4 rounded-2xl border-2 shadow-xl flex items-center gap-3.5 ${
            isRevoked
                ? 'bg-slate-900 border-slate-700 text-white'
                : 'bg-amber-50 border-amber-400 text-slate-900'
        }`;

        toast.innerHTML = `
            <div class="w-11 h-11 rounded-2xl ${isRevoked ? 'bg-slate-700 text-amber-300' : 'bg-amber-400 text-slate-900'} flex items-center justify-center font-extrabold text-xl shrink-0">
                ${isRevoked ? '!' : '★'}
            </div>
            <div>
                <div class="bb-display-font text-sm font-bold">${escapeHtml(title)}</div>
                <div class="text-xs font-semibold opacity-85">${escapeHtml(message)}</div>
            </div>
        `;

        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3100);
    }

    async function runStruggleAnalysis() {
        if (!analyzeEndpoint) return;
        const btn = document.getElementById('analyzeStrugglesBtn');
        const output = document.getElementById('aiDiagnosticsOutput');
        if (!btn || !output) return;

        btn.disabled = true;
        btn.innerText = 'Analyzing Student Responses...';

        try {
            const res = await fetch(analyzeEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const data = await res.json();
            const bullets = Array.isArray(data.bullets)
                ? data.bullets
                : String(data.analysis || data.summary || 'No struggle patterns detected yet.')
                    .split('\n')
                    .map(s => s.replace(/^[-*•\s]+/, '').trim())
                    .filter(Boolean);

            output.innerHTML = `
                <ul class="list-disc list-inside space-y-1.5">
                    ${bullets.map(b => `<li>${escapeHtml(b)}</li>`).join('')}
                </ul>
            `;
        } catch (e) {
            output.innerHTML = `<p class="text-red-600 font-bold">Unable to run AI struggle analysis right now.</p>`;
        } finally {
            btn.disabled = false;
            btn.innerText = 'Analyze Class Struggles with AI';
        }
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endpush
@endsection