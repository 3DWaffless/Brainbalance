{{-- FILE: resources/views/quiz/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Quizzes - ' . config('app.name', 'BrainBalance'))

@push('styles')
<style>
    .quiz-header-card {
        background: #fff;
        border: 3px solid #B8E2F8;
        border-radius: 24px;
        padding: 22px 26px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 5px 0 #D4F1FC;
    }
    .kid-list-banner {
        background: linear-gradient(135deg, #FFF7ED 0%, #EBF5FF 55%, #ECFCCB 100%);
        border: 3px solid #FF8400;
        border-radius: 28px;
        padding: 24px 28px;
        margin-bottom: 24px;
        box-shadow: 0 6px 0 #FDBA74;
    }
    .quiz-header-eyebrow {
        font-size: 11px;
        font-weight: 900;
        color: #8CA2C0;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 4px;
    }
    .quiz-header-title {
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 26px;
        font-weight: 700;
        color: #0066D6;
        margin: 0;
        line-height: 1.2;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .quiz-header-sub {
        font-size: 13px;
        font-weight: 700;
        color: #5A7696;
        margin: 6px 0 0 0;
    }
    .btn-create-quiz {
        background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);
        color: #fff;
        padding: 12px 20px;
        border-radius: 16px;
        border: 2px solid #004FA8;
        font-weight: 900;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 0 #004FA8;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .btn-create-quiz:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 0 #004FA8;
        color: #fff;
    }
    .quiz-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 20px;
    }
    .quiz-card {
        background: #fff;
        border: 3px solid #B8E2F8;
        border-radius: 24px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 6px 0 #D4F1FC;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .quiz-card:hover {
        transform: translateY(-4px);
        border-color: #1DC4DF;
    }
    .quiz-card.is-draft {
        border-color: #FDBA74;
        box-shadow: 0 6px 0 #FED7AA;
    }
    .quiz-card.is-draft:hover {
        border-color: #FF8400;
    }
    .quiz-card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }
    .quiz-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        background: #EBF5FF;
        color: #0066D6;
        border: 1.5px solid #93C5FD;
    }
    .status-pill-published {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 900;
        background: #ECFCCB;
        color: #365314;
        border: 1.5px solid #84CC16;
    }
    .status-pill-draft {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 900;
        background: #FFF7ED;
        color: #9A3412;
        border: 1.5px solid #FB923C;
    }
    .quiz-deadline-tag {
        font-size: 11px;
        font-weight: 800;
        color: #64748B;
    }
    .quiz-title {
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #0D2B4E;
        margin: 0 0 8px 0;
        line-height: 1.35;
    }
    .quiz-class-tag {
        font-size: 12px;
        font-weight: 800;
        color: #0066D6;
        margin-bottom: 10px;
    }
    .quiz-meta {
        font-size: 12.5px;
        font-weight: 800;
        color: #64748B;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .quiz-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 18px;
        padding-top: 16px;
        border-top: 2px dashed #D4F1FC;
    }
    .btn-action {
        flex: 1;
        text-align: center;
        padding: 10px 12px;
        border-radius: 16px;
        font-size: 12.5px;
        font-weight: 900;
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
    .btn-action-primary {
        background: #EBF5FF;
        color: #0066D6;
        border: 2px solid #93C5FD;
    }
    .btn-action-primary:hover {
        background: #0066D6;
        border-color: #0066D6;
        color: #fff;
    }
    .btn-action-publish {
        background: linear-gradient(135deg, #68CC18 0%, #52B00E 100%);
        color: #fff;
        border: 2px solid #3E8708;
        box-shadow: 0 3px 0 #3E8708;
    }
    .btn-action-publish:hover {
        filter: brightness(1.04);
        color: #fff;
    }
    .btn-action-unpublish {
        background: #FFF7ED;
        color: #C2410C;
        border: 2px solid #FDBA74;
    }
    .btn-action-unpublish:hover {
        background: #FF8400;
        border-color: #D96B00;
        color: #fff;
    }
    .btn-kid-card-play {
        background: linear-gradient(180deg, #7EE22B 0%, #68CC18 100%);
        color: #fff;
        border: 2.5px solid #4FA30D;
        box-shadow: 0 5px 0 #3E8708;
        font-size: 15px;
        padding: 12px 16px;
    }
    .btn-kid-card-play:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 0 #3E8708;
        color: #fff;
    }
    .btn-action-edit {
        background: #FFFBEB;
        color: #B45309;
        border: 2px solid #FDE68A;
        flex: 0 0 auto;
        padding: 10px 12px;
    }
    .btn-action-edit:hover {
        background: #F59E0B;
        border-color: #F59E0B;
        color: #fff;
    }
    .btn-action-danger {
        background: #FEF2F2;
        color: #DC2626;
        border: 2px solid #FECACA;
        flex: 0 0 auto;
        padding: 10px 12px;
    }
    .btn-action-danger:hover {
        background: #DC2626;
        border-color: #DC2626;
        color: #fff;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border-radius: 26px;
        border: 3px dashed #B8E2F8;
    }
    .alert-tactile-success {
        background: #F4FCEB;
        color: #365314;
        border: 2.5px solid #84CC16;
        border-radius: 18px;
        padding: 12px 18px;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 18px;
        box-shadow: 0 4px 0 #D9F99D;
    }
    .alert-tactile-info {
        background: #FFF7ED;
        color: #9A3412;
        border: 2.5px solid #FDBA74;
        border-radius: 18px;
        padding: 12px 18px;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 18px;
        box-shadow: 0 4px 0 #FED7AA;
    }
</style>
@endpush

@section('content')
@php
    $canManage = Auth::user()->isTeacher() || Auth::user()->role === 'admin';
    $typeBadges = [
        'multiple_choice' => '🔘 Multi-Choice',
        'multiple_select' => '☑️ Multi-Select',
        'true_false'      => '⚖️ True/False',
        'matching'        => '🔗 Matching',
        'fill_blank'      => '✏️ Fill-Blank',
        'short_answer'    => '💬 Short Answer',
        'essay'           => '📝 Essay',
    ];
@endphp

@if($canManage)
    <div class="quiz-header-card">
        <div>
            <div class="quiz-header-eyebrow">Workspace</div>
            <h1 class="quiz-header-title">Quizzes &amp; Drafts 📝</h1>
            <p class="quiz-header-sub">Create, edit, publish, or unpublish adaptive quizzes and drafts for your classrooms.</p>
        </div>
        <a href="{{ route('quiz.create') }}" class="btn-create-quiz">
            ➕ Create / Question Bank
        </a>
    </div>
@else
    <div class="kid-list-banner">
        <div class="quiz-header-eyebrow" style="color: #C2410C;">Learning Quest</div>
        <h1 class="quiz-header-title" style="color: #0D2B4E; font-size: 28px;">My Quiz Adventures 🚀</h1>
        <p class="quiz-header-sub" style="color: #475569; font-size: 14px;">
            Pick a quiz quest below, solve challenges, and collect XP stars &amp; treasure medals!
        </p>
    </div>
@endif

@if(session('success'))
    <div class="alert-tactile-success">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('info'))
    <div class="alert-tactile-info">
        ℹ️ {{ session('info') }}
    </div>
@endif

@if($quizzes->isEmpty())
    <div class="empty-state">
        <div style="font-size: 52px; margin-bottom: 12px;">{{ $canManage ? '📝' : '🎈' }}</div>
        <h3 class="font-display" style="font-size: 20px; font-weight: 700; color: #0D2B4E; margin-bottom: 6px;">
            {{ $canManage ? 'No quizzes or drafts available yet' : 'No quiz quests posted yet!' }}
        </h3>
        <p style="font-size: 13px; color: #64748B; font-weight: 700; margin-bottom: 20px;">
            @if($canManage)
                Generate your first quiz using AI, build with 7 question types, save a draft, or reuse from your Question Bank.
            @else
                Check back soon when your teacher adds a new quiz quest!
            @endif
        </p>
        @if($canManage)
            <a href="{{ route('quiz.create') }}" class="btn-create-quiz">
                ➕ Create First Quiz
            </a>
        @endif
    </div>
@else
    <div class="quiz-grid">
        @foreach($quizzes as $quiz)
            @php
                $isPublished = $quiz->isPublished();
                $qCount = $quiz->questions_count ?? $quiz->questions->count();
                $subjLower = strtolower($quiz->subject ?? '');
                $isMath = str_contains($subjLower, 'math');
                $isEng  = str_contains($subjLower, 'eng') || str_contains($subjLower, 'read');
                $badgeStyle = $isMath
                    ? 'background: #DBEAFE; color: #1E40AF; border-color: #93C5FD;'
                    : ($isEng
                        ? 'background: #DCFCE7; color: #166534; border-color: #86EFAC;'
                        : 'background: #FEF3C7; color: #92400E; border-color: #FCD34D;');
                $subjIcon = $isMath ? '🔢 ' : ($isEng ? '📖 ' : '📚 ');
                $distinctTypes = $quiz->questions->map(function ($q) {
                    $t = strtolower((string) ($q->question_type ?? $q->type ?? 'multiple_choice'));
                    return $t === 'identification' ? 'short_answer' : $t;
                })->unique()->take(3);
            @endphp
            <div class="quiz-card {{ ($canManage && !$isPublished) ? 'is-draft' : '' }}">
                <div>
                    <div class="quiz-card-top">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="quiz-badge" style="{{ !$canManage ? $badgeStyle : '' }}">
                                {{ !$canManage ? $subjIcon : '' }}{{ strtoupper($quiz->subject ?? 'General') }}
                            </span>
                            @if($canManage)
                                @if($isPublished)
                                    <span class="status-pill-published">🟢 Published</span>
                                @else
                                    <span class="status-pill-draft">📝 Draft</span>
                                @endif
                            @endif
                        </div>

                        @if($quiz->deadline_at)
                            <span class="quiz-deadline-tag">📅 {{ \Carbon\Carbon::parse($quiz->deadline_at)->format('M d') }}</span>
                        @endif
                    </div>

                    <h2 class="quiz-title">{{ $quiz->title }}</h2>

                    @if($quiz->relationLoaded('classroom') && $quiz->classroom)
                        <div class="quiz-class-tag">🏫 {{ $quiz->classroom->name }}</div>
                    @endif

                    <div class="quiz-meta mb-2.5">
                        <span>❓ {{ $qCount }} {{ Str::plural('Question', $qCount) }}</span>
                        <span>🔄 {{ $quiz->max_attempts ? $quiz->max_attempts . ' Tries' : 'Unlimited' }}</span>
                    </div>

                    @if($distinctTypes->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($distinctTypes as $dt)
                                <span class="px-2 py-0.5 rounded-lg bg-[#F4FAFF] border border-[#D4F1FC] text-[10.5px] font-black text-[#4A688A]">
                                    {{ $typeBadges[$dt] ?? '🔘 Multi-Choice' }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="quiz-actions">
                    @if($canManage)
                        <a href="{{ route('quiz.show', $quiz) }}" class="btn-action btn-action-primary">
                            Details
                        </a>

                        @if($isPublished)
                            <form action="{{ route('quiz.unpublish', $quiz) }}" method="POST" style="margin: 0; flex: 1; display: inline-flex;">
                                @csrf
                                <button type="submit" class="btn-action btn-action-unpublish w-full" title="Unpublish Quiz">
                                    ⏸️ Unpublish
                                </button>
                            </form>
                        @else
                            <form action="{{ route('quiz.publish', $quiz) }}" method="POST" style="margin: 0; flex: 1; display: inline-flex;">
                                @csrf
                                <button type="submit" class="btn-action btn-action-publish w-full" title="Publish Draft">
                                    🚀 Publish
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('quiz.edit', $quiz) }}"
                           onclick="{{ $isPublished ? "return confirm('Editing a published quiz will automatically unpublish it and open the quiz editor. Continue?');" : '' }}"
                           class="btn-action btn-action-edit"
                           title="{{ $isPublished ? 'Unpublish & Edit Quiz' : 'Edit Draft' }}">
                            ✏️
                        </a>

                        <form action="{{ route('quiz.destroy', $quiz) }}" method="POST" style="margin: 0; display: inline-flex;" onsubmit="return confirm('Are you sure you want to delete this quiz?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-action-danger" title="Delete Quiz">
                                🗑️
                            </button>
                        </form>
                    @else
                        <a href="{{ route('quiz.show', $quiz) }}" class="btn-action btn-kid-card-play">
                            🚀 Play Quest
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection