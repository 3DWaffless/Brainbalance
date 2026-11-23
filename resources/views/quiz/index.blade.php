{{-- FILE: resources/views/quiz/index.blade.php --}}
@extends('layouts.app')

@section('title', (Auth::user()->isStudent() ? 'Quiz Quests' : 'Quizzes & Drafts') . ' - ' . config('app.name', 'BrainBalance'))

@push('styles')
<style>
    /* =========================================================
       HERO HEADER & FILTER BAR
    ========================================================= */
    .quiz-workspace-hero {
        background: #FFFFFF;
        border: 3px solid #B8E2F8;
        border-radius: 26px;
        padding: 24px 28px;
        box-shadow: 0 6px 0 #D4F1FC;
        margin-bottom: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    .hero-eyebrow {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.9px;
        color: #64748B;
        margin-bottom: 4px;
    }
    .hero-heading {
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 26px;
        font-weight: 700;
        color: #0066D6;
        margin: 0 0 4px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .hero-subtext {
        font-size: 13.5px;
        font-weight: 700;
        color: #5A7696;
        margin: 0;
    }
    .btn-hero-create {
        background: linear-gradient(180deg, #1A84F8 0%, #0066D6 100%);
        color: #FFFFFF;
        border: 2.5px solid #004FA8;
        border-radius: 16px;
        padding: 12px 20px;
        font-size: 13.5px;
        font-weight: 900;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        box-shadow: 0 5px 0 #004FA8;
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }
    .btn-hero-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 0 #004FA8;
        color: #FFFFFF;
    }
    .btn-hero-create:active {
        transform: translateY(2px);
        box-shadow: 0 2px 0 #004FA8;
    }

    /* Filter & Search Toolbar */
    .quiz-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }
    .quiz-filter-tabs {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .quiz-tab-btn {
        background: #FFFFFF;
        color: #4A688A;
        border: 2px solid #B8E2F8;
        border-radius: 50px;
        padding: 7px 15px;
        font-size: 12.5px;
        font-weight: 900;
        cursor: pointer;
        transition: all 0.14s ease;
        white-space: nowrap;
    }
    .quiz-tab-btn:hover {
        border-color: #1DC4DF;
        color: #0066D6;
    }
    .quiz-tab-btn.active {
        background: #0066D6;
        color: #FFFFFF;
        border-color: #004FA8;
        box-shadow: 0 3px 0 #BAE6FD;
    }
    .quiz-search-box {
        background: #FFFFFF;
        border: 2px solid #B8E2F8;
        border-radius: 14px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 800;
        color: #0D2B4E;
        min-width: 240px;
        outline: none;
    }
    .quiz-search-box:focus {
        border-color: #0066D6;
    }

    /* =========================================================
       STRICT 3-COLUMN WIDE CARD GRID
    ========================================================= */
    .quiz-cards-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 22px;
        width: 100%;
    }
    @media (max-width: 1180px) {
        .quiz-cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 720px) {
        .quiz-cards-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    .quiz-item-card {
        background: #FFFFFF;
        border: 3px solid #B8E2F8;
        border-radius: 24px;
        padding: 22px;
        box-shadow: 0 6px 0 #D4F1FC;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        min-width: 0;
    }
    .quiz-item-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 9px 0 #BAE6FD;
    }
    .quiz-item-card.is-draft {
        border-color: #FDBA74;
        box-shadow: 0 6px 0 #FED7AA;
    }
    .quiz-item-card.is-draft:hover {
        box-shadow: 0 9px 0 #FDBA74;
    }

    .quiz-card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }
    .quiz-top-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }
    .subject-chip {
        padding: 4px 11px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        background: #EBF5FF;
        color: #0066D6;
        border: 1.5px solid #93C5FD;
        white-space: nowrap;
    }
    .status-chip-live {
        padding: 4px 11px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 900;
        background: #ECFCCB;
        color: #365314;
        border: 1.5px solid #84CC16;
        white-space: nowrap;
    }
    .status-chip-draft {
        padding: 4px 11px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 900;
        background: #FFF7ED;
        color: #C2410C;
        border: 1.5px solid #FB923C;
        white-space: nowrap;
    }
    .quiz-date-tag {
        font-size: 11.5px;
        font-weight: 800;
        color: #64748B;
        white-space: nowrap;
    }

    .quiz-card-title {
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #0D2B4E;
        margin: 0 0 8px 0;
        line-height: 1.3;
        word-break: break-word;
    }
    .quiz-class-line {
        font-size: 13px;
        font-weight: 900;
        color: #0066D6;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .quiz-meta-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        font-size: 12.5px;
        font-weight: 800;
        color: #4A688A;
        margin-bottom: 12px;
    }
    .quiz-types-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 18px;
    }
    .qtype-mini-pill {
        background: #F4FAFF;
        border: 1.5px solid #CBE6FF;
        color: #1E3A5F;
        border-radius: 50px;
        padding: 3px 10px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    /* =========================================================
       UNIFORM SINGLE-ROW TACTILE CARD BUTTONS
    ========================================================= */
    .quiz-card-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-top: 14px;
        border-top: 2px dashed #E2F1FC;
        margin-top: auto;
    }
    .quiz-card-actions form {
        margin: 0;
        display: flex;
    }
    .quiz-card-actions form.flex-action-form {
        flex: 1;
        min-width: 0;
    }

    .btn-q-details,
    .btn-q-publish,
    .btn-q-unpublish,
    .btn-q-play {
        flex: 1;
        min-width: 0;
        height: 40px;
        padding: 0 14px;
        border-radius: 13px;
        font-size: 12.5px;
        font-weight: 900;
        white-space: nowrap;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.14s ease;
    }

    .btn-q-details {
        background: #F2FAFF;
        color: #0066D6;
        border: 2px solid #93C5FD;
        box-shadow: 0 3px 0 #BAE6FD;
    }
    .btn-q-details:hover {
        background: #E0F2FE;
        transform: translateY(-1px);
        color: #004FA8;
    }

    .btn-q-publish,
    .btn-q-play {
        background: linear-gradient(180deg, #7EE22B 0%, #68CC18 100%);
        color: #FFFFFF;
        border: 2px solid #4FA30D;
        box-shadow: 0 3px 0 #3E8708;
    }
    .btn-q-publish:hover,
    .btn-q-play:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 0 #3E8708;
        color: #FFFFFF;
    }

    .btn-q-unpublish {
        background: #FFF7ED;
        color: #C2410C;
        border: 2px solid #FDBA74;
        box-shadow: 0 3px 0 #FED7AA;
    }
    .btn-q-unpublish:hover {
        background: #FFEDD5;
        transform: translateY(-1px);
    }

    .btn-q-icon {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        border-radius: 13px;
        font-size: 14px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.12s ease, background 0.14s ease;
    }
    .btn-q-edit {
        background: #FFFBEB;
        color: #B45309;
        border: 2px solid #FCD34D;
        box-shadow: 0 3px 0 #FEF3C7;
    }
    .btn-q-edit:hover {
        background: #FEF3C7;
        transform: translateY(-1px);
    }
    .btn-q-delete {
        background: #FFF1F2;
        color: #E11D48;
        border: 2px solid #FECDD3;
        box-shadow: 0 3px 0 #FFE4E6;
    }
    .btn-q-delete:hover {
        background: #FFE4E6;
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
@php
    $isStudent = Auth::user()->isStudent();
    $quizCollection = $quizzes instanceof \Illuminate\Pagination\AbstractPaginator
        ? $quizzes->getCollection()
        : collect($quizzes);

    $publishedCount = $quizCollection->filter(fn ($q) => method_exists($q, 'isPublished') ? $q->isPublished() : !is_null($q->published_at))->count();
    $draftCount = $quizCollection->count() - $publishedCount;

    $typeBadgeMap = [
        'multiple_choice' => '🔘 Multi-Choice',
        'multiple_select' => '☑️ Multi-Select',
        'true_false'      => '⚖️ True / False',
        'matching'        => '🔗 Matching',
        'fill_blank'      => '✏️ Fill-in-Blank',
        'short_answer'    => '💬 Short Answer',
        'identification'  => '💬 Short Answer',
        'essay'           => '📝 Essay',
    ];
@endphp

<!-- TOP WORKSPACE HEADER -->
<div class="quiz-workspace-hero">
    <div>
        <div class="hero-eyebrow">{{ $isStudent ? 'ADVENTURE ARENA' : 'WORKSPACE' }}</div>
        <h1 class="hero-heading">
            {{ $isStudent ? 'Quizzes' : 'Quizzes & Drafts' }}
        </h1>
        <p class="hero-subtext">
            {{ $isStudent
                ? 'Choose a quiz quest from your classes, build combo streaks, and earn treasure chest medals!'
                : 'Create, edit, publish, or unpublish adaptive quizzes and drafts for your classrooms.' }}
        </p>
    </div>

    <div class="flex items-center gap-3 flex-wrap">
        @if($isStudent && \Illuminate\Support\Facades\Route::has('quiz.history'))
            <a href="{{ route('quiz.history') }}" class="btn-q-details" style="flex: initial; padding: 0 18px; height: 44px;">
                📜 My Quiz History
            </a>
        @elseif(!$isStudent)
            <a href="{{ route('quiz.create') }}" class="btn-hero-create">
                <span>+</span> Create / Question Bank
            </a>
        @endif
    </div>
</div>

<!-- FILTER TABS & SEARCH BAR -->
@if($quizCollection->isNotEmpty())
    <div class="quiz-toolbar">
        @if(!$isStudent)
            <div class="quiz-filter-tabs">
                <button type="button" class="quiz-tab-btn active" onclick="filterQuizCards('all', this)">
                    All ({{ $quizCollection->count() }})
                </button>
                <button type="button" class="quiz-tab-btn" onclick="filterQuizCards('published', this)">
                    🟢 Published ({{ $publishedCount }})
                </button>
                <button type="button" class="quiz-tab-btn" onclick="filterQuizCards('draft', this)">
                    Draft ({{ $draftCount }})
                </button>
            </div>
        @else
            <div class="text-xs font-black text-[#0066D6] uppercase tracking-wider">
                🌟 {{ $quizCollection->count() }} {{ Str::plural('Quest', $quizCollection->count()) }} Available
            </div>
        @endif

        <input type="text" id="quizSearchInput" oninput="searchQuizCards()" placeholder="🔍 Search quiz or class..." class="quiz-search-box">
    </div>
@endif

<!-- 3-COLUMN QUIZ CARDS GRID -->
@if($quizCollection->isEmpty())
    <div style="background: #FFFFFF; border: 3px dashed #B8E2F8; border-radius: 26px; padding: 48px 24px; text-align: center;">
        <div style="font-size: 48px; margin-bottom: 10px;">{{ $isStudent ? '🧭' : '' }}</div>
        <h3 class="font-display" style="font-size: 20px; font-weight: 700; color: #0D2B4E; margin-bottom: 6px;">
            {{ $isStudent ? 'No quiz quests available right now' : 'No quizzes created yet' }}
        </h3>
        <p style="font-size: 13.5px; font-weight: 700; color: #5A7696; margin-bottom: 18px;">
            {{ $isStudent
                ? 'Join a class or check back once your teacher publishes a new quiz!'
                : 'Click the button below to build a quiz manually or generate questions with Groq AI.' }}
        </p>
        @if(!$isStudent)
            <a href="{{ route('quiz.create') }}" class="btn-hero-create">
                + Create Your First Quiz
            </a>
        @endif
    </div>
@else
    <div class="quiz-cards-grid" id="quizCardsGrid">
        @foreach($quizzes as $quiz)
            @php
                $isPublished = method_exists($quiz, 'isPublished')
                    ? $quiz->isPublished()
                    : !is_null($quiz->published_at);

                $qCount = $quiz->questions_count ?? $quiz->questions->count();
                $className = $quiz->classroom->name ?? 'General Class';

                $distinctTypes = $quiz->questions
                    ->map(fn ($q) => strtolower((string) ($q->question_type ?? $q->type ?? 'multiple_choice')))
                    ->unique()
                    ->values();

                if ($distinctTypes->isEmpty()) {
                    $distinctTypes = collect(['multiple_choice']);
                }

                // Student-specific attempt stats
                $studentAttempts = $isStudent
                    ? \App\Models\QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', Auth::id())->get()
                    : collect();
                $attemptCount = $studentAttempts->count();
                $bestScore = $studentAttempts->max('score');
                $latestAttempt = $studentAttempts->sortByDesc('created_at')->first();
                $isClosed = $isStudent && $quiz->max_attempts && $attemptCount >= $quiz->max_attempts;
            @endphp

            <div class="quiz-item-card {{ !$isPublished ? 'is-draft' : '' }}"
                 data-status="{{ $isPublished ? 'published' : 'draft' }}"
                 data-search="{{ strtolower($quiz->title . ' ' . $className . ' ' . ($quiz->subject ?? '')) }}">

                <div>
                    <!-- Top Row: Subject + Status + Date -->
                    <div class="quiz-card-top">
                        <div class="quiz-top-badges">
                            <span class="subject-chip">{{ strtoupper($quiz->subject ?? 'General') }}</span>
                            @if(!$isStudent)
                                @if($isPublished)
                                    <span class="status-chip-live">🟢 Published</span>
                                @else
                                    <span class="status-chip-draft">📝 Draft</span>
                                @endif
                            @elseif($bestScore !== null)
                                <span class="status-chip-live">🏆 Best: {{ $bestScore }}/{{ $qCount }}</span>
                            @endif
                        </div>
                        <span class="quiz-date-tag">
                            📅 {{ $quiz->created_at?->format('M d') }}
                        </span>
                    </div>

                    <!-- Quiz Title -->
                    <h3 class="quiz-card-title">{{ $quiz->title }}</h3>

                    <!-- Class Name -->
                    <div class="quiz-class-line">
                        <span>🏫</span>
                        <span>{{ $className }}</span>
                    </div>

                    <!-- Questions & Attempts Metadata -->
                    <div class="quiz-meta-row">
                        <span>❓ {{ $qCount }} {{ Str::plural('Question', $qCount) }}</span>
                        <span>
                            🔄 {{ $isStudent
                                ? ($quiz->max_attempts ? "{$attemptCount}/{$quiz->max_attempts} Tries" : "{$attemptCount} Tries")
                                : ($quiz->max_attempts ? "{$quiz->max_attempts} " . Str::plural('Try', $quiz->max_attempts) : 'Unlimited Tries') }}
                        </span>
                    </div>

                    <!-- Question Type Pills -->
                    <div class="quiz-types-row">
                        @foreach($distinctTypes as $tKey)
                            <span class="qtype-mini-pill">
                                {{ $typeBadgeMap[$tKey] ?? '🔘 Multi-Choice' }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <!-- Card Action Buttons (Uniform Single Row) -->
                <div class="quiz-card-actions">
                    @if($isStudent)
                        @if($latestAttempt && \Illuminate\Support\Facades\Route::has('quiz.review'))
                            <a href="{{ route('quiz.review', $latestAttempt->id) }}" class="btn-q-details">
                                📊 Review
                            </a>
                        @endif

                        @if($isClosed)
                            <span class="btn-q-details" style="background: #F1F5F9; border-color: #CBD5E1; color: #64748B; box-shadow: none; cursor: default;">
                                🔒 Completed
                            </span>
                        @else
                            <a href="{{ route('quiz.show', $quiz->id) }}" class="btn-q-play">
                                🚀 Play Quest
                            </a>
                        @endif
                    @else
                        <a href="{{ route('quiz.show', $quiz->id) }}" class="btn-q-details">
                            Details
                        </a>

                        @if($isPublished)
                            <form action="{{ route('quiz.unpublish', $quiz->id) }}" method="POST" class="flex-action-form">
                                @csrf
                                <button type="submit" class="btn-q-unpublish">
                                    Unpublish
                                </button>
                            </form>
                        @else
                            <form action="{{ route('quiz.publish', $quiz->id) }}" method="POST" class="flex-action-form">
                                @csrf
                                <button type="submit" class="btn-q-publish">
                                    Publish
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('quiz.edit', $quiz->id) }}"
                           title="{{ $isPublished ? 'Unpublish & Edit' : 'Edit Draft' }}"
                           onclick="{{ $isPublished ? "return confirm('Editing a published quiz will automatically unpublish it first. Continue?');" : '' }}"
                           class="btn-q-icon btn-q-edit">
                            ✏️
                        </a>

                        <form action="{{ route('quiz.destroy', $quiz->id) }}" method="POST"
                              onsubmit="return confirm('Delete this quiz permanently?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Delete Quiz" class="btn-q-icon btn-q-delete">
                                🗑️
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if(method_exists($quizzes, 'links'))
        <div class="mt-6">
            {{ $quizzes->links() }}
        </div>
    @endif
@endif
@endsection

@push('scripts')
<script>
    let activeQuizStatusFilter = 'all';

    function filterQuizCards(status, clickedBtn) {
        activeQuizStatusFilter = status;
        document.querySelectorAll('.quiz-tab-btn').forEach(btn => btn.classList.remove('active'));
        if (clickedBtn) clickedBtn.classList.add('active');
        applyQuizCardFilters();
    }

    function searchQuizCards() {
        applyQuizCardFilters();
    }

    function applyQuizCardFilters() {
        const query = (document.getElementById('quizSearchInput')?.value || '').trim().toLowerCase();
        document.querySelectorAll('.quiz-item-card').forEach(card => {
            const matchesStatus = activeQuizStatusFilter === 'all' || card.dataset.status === activeQuizStatusFilter;
            const matchesQuery = !query || (card.dataset.search || '').includes(query);
            card.style.display = (matchesStatus && matchesQuery) ? 'flex' : 'none';
        });
    }
</script>
@endpush