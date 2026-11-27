{{-- FILE: resources/views/quiz/show.blade.php --}}
@extends('layouts.app')

@section('title', $quiz->title . ' - ' . config('app.name', 'BrainBalance'))

@push('styles')
<style>
    /* =========================================================
       SHARED & TEACHER STYLES
    ========================================================= */
    .quiz-detail-card {
        background: #fff;
        border: 3px solid #B8E2F8;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 6px 0 #D4F1FC;
        margin-bottom: 24px;
    }
    .quiz-detail-card.is-draft {
        border-color: #FDBA74;
        box-shadow: 0 6px 0 #FED7AA;
    }
    .badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #EBF5FF;
        color: #0066D6;
        border: 2px solid #93C5FD;
    }
    .status-pill-published {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 13px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 900;
        background: #ECFCCB;
        color: #365314;
        border: 2px solid #84CC16;
    }
    .status-pill-draft {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 13px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 900;
        background: #FFF7ED;
        color: #9A3412;
        border: 2px solid #FB923C;
    }
    .btn-teacher-publish {
        background: linear-gradient(135deg, #68CC18 0%, #52B00E 100%);
        color: #fff;
        border: 2px solid #3E8708;
        padding: 9px 16px;
        border-radius: 14px;
        font-size: 12.5px;
        font-weight: 900;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 0 #3E8708;
        transition: transform 0.12s ease;
    }
    .btn-teacher-publish:hover { transform: translateY(-2px); color: #fff; }
    .btn-teacher-unpublish {
        background: #FFF7ED;
        color: #C2410C;
        border: 2px solid #FDBA74;
        padding: 9px 16px;
        border-radius: 14px;
        font-size: 12.5px;
        font-weight: 900;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 0 #FED7AA;
        transition: transform 0.12s ease;
    }
    .btn-teacher-unpublish:hover { background: #FF8400; border-color: #D96B00; color: #fff; }
    .btn-teacher-edit {
        background: #FFFBEB;
        color: #B45309;
        border: 2px solid #FDE68A;
        padding: 9px 16px;
        border-radius: 14px;
        font-size: 12.5px;
        font-weight: 900;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 0 #FEF3C7;
        transition: transform 0.12s ease;
    }
    .btn-teacher-edit:hover { background: #F59E0B; border-color: #F59E0B; color: #fff; }

    .question-list-item {
        background: #F4FAFF;
        border: 2px solid #B8E2F8;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
    }
    .attempts-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }
    .attempts-table th, .attempts-table td {
        padding: 12px 14px;
        border-bottom: 1.5px solid #D4F1FC;
        text-align: left;
        font-size: 13px;
    }
    .attempts-table th {
        font-weight: 900;
        color: #4A688A;
        text-transform: uppercase;
        font-size: 11px;
    }
    .btn-ai-analyze {
        background: #6C5CE7;
        color: #fff;
        border: none;
        padding: 8px 14px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 0 #4834D4;
        transition: all 0.15s ease;
    }
    .btn-ai-analyze:hover { background: #5b4bc4; transform: translateY(-2px); }
    .ai-analysis-box {
        background: #F4F0FF;
        border: 2px solid #D6C7FF;
        border-radius: 16px;
        padding: 20px;
        margin-top: 20px;
        display: none;
    }
    .ai-analysis-header {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 900;
        font-size: 15px;
        color: #5138EE;
        margin-bottom: 12px;
    }
    .ai-analysis-content {
        font-size: 13px;
        line-height: 1.6;
        color: #0D2B4E;
        white-space: pre-wrap;
    }

    /* =========================================================
       KID-FRIENDLY STUDENT QUIZ LANDING & STATS (GRADES 1-6)
    ========================================================= */
    .kid-hero-banner {
        background: linear-gradient(135deg, #FFF7ED 0%, #EBF5FF 52%, #ECFCCB 100%);
        border: 3px solid #FF8400;
        border-radius: 28px;
        padding: 28px;
        box-shadow: 0 8px 0 #FDBA74;
        margin-bottom: 26px;
        position: relative;
        overflow: hidden;
    }
    .kid-hero-grid {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }
    .kid-subject-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #FF8400;
        color: #FFFFFF;
        border: 2px solid #D96B00;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 10px;
    }
    .kid-quiz-title {
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 28px;
        font-weight: 700;
        color: #0D2B4E;
        margin: 0 0 14px 0;
        line-height: 1.25;
    }
    .kid-stat-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .kid-stat-pill {
        background: #fff;
        border: 2px solid #B8E2F8;
        border-radius: 16px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 900;
        color: #0D2B4E;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 3px 0 #D4F1FC;
    }
    .btn-kid-play {
        background: linear-gradient(180deg, #7EE22B 0%, #68CC18 100%);
        color: #fff;
        border: 3px solid #4FA30D;
        border-radius: 22px;
        padding: 16px 30px;
        font-size: 18px;
        font-weight: 900;
        letter-spacing: 0.4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 7px 0 #3E8708;
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }
    .btn-kid-play:hover { transform: translateY(-2px); box-shadow: 0 9px 0 #3E8708; }
    .btn-kid-play:active { transform: translateY(4px); box-shadow: 0 3px 0 #3E8708; }

    .kid-attempts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 14px;
        margin-top: 14px;
    }
    .kid-attempt-card {
        background: #F4FAFF;
        border: 2.5px solid #B8E2F8;
        border-radius: 20px;
        padding: 16px;
        box-shadow: 0 4px 0 #D4F1FC;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* =========================================================
       GAMIFIED FULLSCREEN QUIZ PLAYGROUND MODAL (7 TYPES)
    ========================================================= */
    .quiz-runner-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: radial-gradient(circle at top, rgba(0, 102, 214, 0.84), rgba(13, 43, 78, 0.92));
        backdrop-filter: blur(6px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 14px;
        overflow-y: auto;
    }
    .quiz-runner-modal.active { display: flex; }

    .quiz-runner-card {
        background: #FFFFFF;
        border-radius: 32px;
        border: 4px solid #1DC4DF;
        width: 100%;
        max-width: 720px;
        padding: 24px 26px;
        box-shadow: 0 12px 0 #0066D6, 0 24px 48px rgba(0, 0, 0, 0.35);
        position: relative;
        overflow: hidden;
        animation: popInCard 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        max-height: 94vh;
        overflow-y: auto;
    }
    @keyframes popInCard {
        0% { transform: scale(0.92); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }

    .kid-hud-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }
    .kid-hud-pills {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
    }
    .level-badge {
        font-size: 11.5px;
        font-weight: 900;
        padding: 5px 11px;
        border-radius: 50px;
    }
    .level-easy   { background: #ECFCCB; color: #365314; border: 2px solid #84CC16; }
    .level-medium { background: #E0F7FC; color: #0E7490; border: 2px solid #1DC4DF; }
    .level-hard   { background: #FFF7ED; color: #9A3412; border: 2px solid #FF8400; }

    .streak-badge {
        background: #FFF7ED;
        color: #C2410C;
        border: 2px solid #FDBA74;
        padding: 5px 11px;
        border-radius: 50px;
        font-weight: 900;
        font-size: 11.5px;
    }
    .xp-badge {
        background: #EBF5FF;
        color: #0066D6;
        border: 2px solid #93C5FD;
        padding: 5px 11px;
        border-radius: 50px;
        font-weight: 900;
        font-size: 11.5px;
    }
    .btn-exit-quiz {
        background: #FFF1F2;
        color: #BE123C;
        border: 2px solid #FECDD3;
        padding: 5px 12px;
        border-radius: 14px;
        font-weight: 900;
        font-size: 11.5px;
        cursor: pointer;
    }

    .kid-progress-wrap { margin-bottom: 12px; }
    .kid-progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 900;
        color: #0066D6;
        margin-bottom: 5px;
    }
    .progress-bar-container {
        width: 100%;
        height: 13px;
        background: #E0F2FE;
        border-radius: 20px;
        overflow: hidden;
        border: 2px solid #BAE6FD;
    }
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #0066D6 0%, #1DC4DF 35%, #68CC18 70%, #FF8400 100%);
        width: 0%;
        transition: width 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        border-radius: 20px;
    }

    .timer-container {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #F4FAFF;
        border: 2px solid #D4F1FC;
        border-radius: 16px;
        padding: 7px 14px;
        margin-bottom: 16px;
    }
    .timer-bar-outer {
        flex: 1;
        height: 9px;
        background: #E2E8F0;
        border-radius: 10px;
        overflow: hidden;
    }
    .timer-bar-inner {
        height: 100%;
        background: #68CC18;
        width: 100%;
        transition: width 1s linear, background-color 0.3s ease;
        border-radius: 10px;
    }
    .timer-text {
        font-size: 13px;
        font-weight: 900;
        color: #0D2B4E;
        min-width: 40px;
        text-align: right;
    }

    .kid-question-box {
        background: #F2FAFF;
        border: 3px solid #99E2F5;
        border-radius: 22px;
        padding: 16px 18px;
        margin-bottom: 16px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }
    .kid-question-text {
        font-size: 19px;
        font-weight: 900;
        color: #0D2B4E;
        margin: 0;
        line-height: 1.45;
    }
    .btn-read-aloud {
        flex-shrink: 0;
        background: #FFF;
        border: 2.5px solid #1DC4DF;
        color: #0066D6;
        border-radius: 14px;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 3px 0 #BAE6FD;
    }

    /* Choice Buttons (Multiple Choice & Multiple Select) */
    .kid-options-stack {
        display: flex;
        flex-direction: column;
        gap: 11px;
        margin-bottom: 12px;
    }
    .option-btn {
        width: 100%;
        text-align: left;
        padding: 13px 16px;
        border-radius: 18px;
        border: 3px solid #B8E2F8;
        background: #FFFFFF;
        font-size: 15.5px;
        font-weight: 900;
        color: #0D2B4E;
        cursor: pointer;
        transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.15s ease;
        box-shadow: 0 5px 0 #D4F1FC;
        display: flex;
        align-items: center;
        gap: 13px;
    }
    .option-btn:hover {
        background: #F2FAFF;
        border-color: #1DC4DF;
        transform: translateY(-2px);
        box-shadow: 0 7px 0 #99E2F5;
    }
    .option-btn.multi-picked {
        background: linear-gradient(135deg, #EBF5FF 0%, #E0F7FC 100%);
        border-color: #0066D6;
        box-shadow: 0 5px 0 #60A5FA;
        transform: translateY(-2px);
    }
    .option-letter-badge {
        width: 34px;
        height: 34px;
        border-radius: 11px;
        font-size: 14px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .letter-0 { background: #EBF5FF; color: #0066D6; border: 2px solid #93C5FD; }
    .letter-1 { background: #ECFEFF; color: #0E7490; border: 2px solid #67E8F9; }
    .letter-2 { background: #ECFCCB; color: #365314; border: 2px solid #A3E635; }
    .letter-3 { background: #FFF7ED; color: #C2410C; border: 2px solid #FDBA74; }

    .option-btn.correct-choice {
        background: #DCFCE7 !important;
        border-color: #22C55E !important;
        color: #14532D !important;
        box-shadow: 0 5px 0 #16A34A !important;
    }
    .option-btn.wrong-choice {
        background: #FFE4E6 !important;
        border-color: #F43F5E !important;
        color: #881337 !important;
        box-shadow: 0 5px 0 #E11D48 !important;
    }

    /* True / False Battle Cards */
    .tf-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        margin-bottom: 10px;
    }
    .tf-card-btn {
        border-radius: 22px;
        padding: 24px 16px;
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 22px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: transform 0.12s ease;
    }
    .tf-card-btn:hover { transform: translateY(-3px); }
    .tf-true-btn {
        background: linear-gradient(180deg, #F4FCE3 0%, #ECFCCB 100%);
        border: 3px solid #68CC18;
        color: #365314;
        box-shadow: 0 6px 0 #4FA30D;
    }
    .tf-false-btn {
        background: linear-gradient(180deg, #FFF7ED 0%, #FFEDD5 100%);
        border: 3px solid #FF8400;
        color: #9A3412;
        box-shadow: 0 6px 0 #D96B00;
    }

    /* Interactive Matching Tiles */
    .match-board-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 14px;
    }
    .match-tile {
        padding: 12px 14px;
        border-radius: 16px;
        border: 3px solid #B8E2F8;
        background: #FFFFFF;
        font-size: 14px;
        font-weight: 900;
        color: #0D2B4E;
        box-shadow: 0 4px 0 #D4F1FC;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        transition: all 0.15s ease;
    }
    .match-tile:hover { border-color: #1DC4DF; transform: translateY(-1px); }
    .match-tile.active-left {
        border-color: #0066D6;
        background: #E0F7FC;
        box-shadow: 0 4px 0 #38BDF8;
        transform: scale(1.02);
    }

    /* Inline Fill-in-the-Blank Slots */
    .inline-blank-input {
        display: inline-block;
        min-width: 110px;
        max-width: 190px;
        padding: 3px 10px;
        margin: 2px 4px;
        border-radius: 12px;
        border: 3px dashed #FF8400;
        background: #FFF7ED;
        color: #0D2B4E;
        font-weight: 900;
        font-size: 16px;
        text-align: center;
        outline: none;
    }
    .inline-blank-input:focus {
        border-style: solid;
        border-color: #0066D6;
        background: #EBF5FF;
    }

    /* Short Answer & Essay Inputs */
    .kid-typed-input {
        width: 100%;
        padding: 15px 18px;
        font-size: 17px;
        font-weight: 900;
        color: #0D2B4E;
        border: 3px solid #93C5FD;
        border-radius: 18px;
        background: #F4FAFF;
        outline: none;
    }
    .kid-typed-input:focus { border-color: #0066D6; background: #FFF; }

    /* Friendly Feedback Banner */
    .feedback-banner {
        padding: 14px 18px;
        border-radius: 18px;
        font-weight: 900;
        font-size: 16px;
        text-align: center;
        margin-top: 14px;
        animation: popInCard 0.2s ease;
    }
    .feedback-correct {
        background: #DCFCE7;
        color: #14532D;
        border: 3px solid #22C55E;
        box-shadow: 0 4px 0 #86EFAC;
    }
    .feedback-wrong {
        background: #FEF3C7;
        color: #92400E;
        border: 3px solid #F59E0B;
        box-shadow: 0 4px 0 #FCD34D;
    }

    /* Confetti Particles */
    .confetti-piece {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 3px;
        pointer-events: none;
        animation: confettiFall 1.3s ease-out forwards;
    }
    @keyframes confettiFall {
        0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
        100% { transform: translateY(380px) rotate(540deg); opacity: 0; }
    }
</style>
@endpush

@section('content')
@php
    $questionCount = $quiz->questions_count ?? $quiz->questions->count();
    $isPublished = $quiz->isPublished();
@endphp

<div class="mb-5">
    <a href="{{ route('quiz.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl border-2 border-[#B8E2F8] bg-white text-[#0066D6] font-black text-xs hover:bg-[#F2FAFF] transition-all shadow-2xs">
        &larr; Back to Quizzes
    </a>
</div>

@if(Auth::user()->isStudent())
    {{-- =========================================================
         KID-FRIENDLY STUDENT VIEW (GRADES 1 - 6)
    ========================================================= --}}
    @php
        $userAttemptsCount = $attempts->count();
        $canAttempt = !$quiz->max_attempts || $userAttemptsCount < $quiz->max_attempts;
        $bestScore = $attempts->max('score');
    @endphp

    <div class="kid-hero-banner">
        <div class="kid-hero-grid">
            <div style="flex: 1; min-width: 240px;">
                <div class="kid-subject-chip">
                    <span>📚</span> {{ strtoupper($quiz->subject ?? 'Adventure') }}
                </div>
                <h1 class="kid-quiz-title">{{ $quiz->title }}</h1>

                <div class="kid-stat-pills">
                    <div class="kid-stat-pill">
                        <span>❓</span> {{ $questionCount }} {{ Str::plural('Challenge', $questionCount) }}
                    </div>
                    <div class="kid-stat-pill">
                        <span>🔄</span> {{ $quiz->max_attempts ? ($userAttemptsCount . ' / ' . $quiz->max_attempts . ' Tries') : 'Unlimited Tries' }}
                    </div>
                    @if($bestScore !== null)
                        <div class="kid-stat-pill" style="background: #FEF9C3; border-color: #FACC15;">
                            <span>🏆</span> Best: {{ $bestScore }}/{{ $questionCount }}
                        </div>
                    @endif
                    @if($quiz->deadline_at)
                        <div class="kid-stat-pill">
                            <span>📅</span> Due {{ \Carbon\Carbon::parse($quiz->deadline_at)->format('M d') }}
                        </div>
                    @endif
                </div>
            </div>

            <div>
                @if($canAttempt)
                    <button type="button" class="btn-kid-play" onclick="startQuiz({{ $quiz->id }})">
                        <span style="font-size: 24px;">🚀</span> Let's Play!
                    </button>
                @else
                    <div style="background: #F1F5F9; border: 3px solid #CBD5E1; color: #64748B; padding: 16px 24px; border-radius: 20px; font-weight: 900; font-size: 14px; text-align: center;">
                        🎉 All Tries Completed!
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="quiz-detail-card">
        <h3 class="font-display" style="font-size: 20px; font-weight: 700; color: #0D2B4E; margin: 0 0 6px 0;">
            🌟 Your Scoreboard
        </h3>
        <p style="font-size: 13px; font-weight: 700; color: #5A7696; margin: 0;">
            Solve puzzles, match pairs, build combo streaks, and unlock treasure chest medals!
        </p>

        @if($attempts->isEmpty())
            <div style="text-align: center; padding: 36px 16px; background: #F4FAFF; border: 2.5px dashed #38BDF8; border-radius: 20px; margin-top: 16px;">
                <div style="font-size: 44px; margin-bottom: 8px;">🎒</div>
                <div style="font-size: 16px; font-weight: 900; color: #0D2B4E;">Ready for your first quest?</div>
                <div style="font-size: 13px; font-weight: 700; color: #5A7696; margin-top: 4px;">
                    Tap the big green <strong>Let's Play!</strong> button above to launch!
                </div>
            </div>
        @else
            <div class="kid-attempts-grid">
                @foreach($attempts as $index => $att)
                    @php
                        $pct = round(($att->score / max(1, $att->total_questions)) * 100);
                        $medal = $pct >= 90 ? '🥇' : ($pct >= 70 ? '🥈' : '⭐');
                    @endphp
                    <div class="kid-attempt-card">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-size: 11px; font-weight: 900; color: #0066D6; text-transform: uppercase;">
                                    Try #{{ $attempts->count() - $index }}
                                </span>
                                <span style="font-size: 22px;">{{ $medal }}</span>
                            </div>
                            <div style="font-size: 22px; font-weight: 900; color: #0D2B4E;">
                                {{ $att->score }} / {{ $att->total_questions }} <span style="font-size: 14px; color: #16A34A;">({{ $pct }}%)</span>
                            </div>
                            <div style="display: flex; gap: 10px; font-size: 12px; font-weight: 800; color: #475569; margin-top: 8px;">
                                <span>⭐ +{{ $att->total_xp }} XP</span>
                                <span>🔥 {{ $att->max_streak }} Streak</span>
                            </div>
                            <div style="font-size: 11px; font-weight: 700; color: #94A3B8; margin-top: 6px;">
                                {{ $att->created_at->format('M d, Y • h:i A') }}
                            </div>
                        </div>

                        @if(\Illuminate\Support\Facades\Route::has('quiz.review'))
                            <a href="{{ route('quiz.review', $att->id) }}" class="btn-tactile-ghost text-xs py-1.5 px-3 mt-3 w-full">
                                📊 Review Answers
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- GAMIFIED 7-TYPE QUIZ RUNNER MODAL -->
    <div class="quiz-runner-modal" id="quizRunnerModal">
        <div class="quiz-runner-card" id="quizRunnerCard">
            <div id="quizContainer">
                <div style="text-align: center; padding: 36px;">
                    <div style="font-weight: 900; font-size: 18px; color: #0066D6;">Getting your quiz ready...</div>
                </div>
            </div>
        </div>
    </div>

@else
    {{-- =========================================================
         TEACHER / ADMIN VIEW
    ========================================================= --}}
    <div class="quiz-detail-card {{ !$isPublished ? 'is-draft' : '' }}">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-3">
                    <span class="badge-pill">{{ strtoupper($quiz->subject ?? 'General') }}</span>
                    @if($isPublished)
                        <span class="status-pill-published">🟢 Published</span>
                    @else
                        <span class="status-pill-draft">📝 Draft (Hidden from Students)</span>
                    @endif
                </div>
                <h2 style="font-size: 22px; font-weight: 900; color: #0D2B4E; margin-bottom: 6px;">{{ $quiz->title }}</h2>
                <div style="font-size: 13px; font-weight: 700; color: #5A7696; display: flex; gap: 16px; margin-top: 8px; flex-wrap: wrap;">
                    <span>❓ Questions: <strong>{{ $questionCount }}</strong></span>
                    <span>🔄 Max Attempts: <strong>{{ $quiz->max_attempts ?? 'Unlimited' }}</strong></span>
                    @if($quiz->deadline_at)
                        <span>📅 Deadline: <strong>{{ \Carbon\Carbon::parse($quiz->deadline_at)->format('M d, Y h:i A') }}</strong></span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                @if($isPublished)
                    <form action="{{ route('quiz.unpublish', $quiz) }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn-teacher-unpublish">
                            ⏸️ Unpublish
                        </button>
                    </form>
                @else
                    <form action="{{ route('quiz.publish', $quiz) }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn-teacher-publish">
                            🚀 Publish Quiz Now
                        </button>
                    </form>
                @endif

                <a href="{{ route('quiz.edit', $quiz) }}"
                   onclick="{{ $isPublished ? "return confirm('Editing a published quiz will automatically unpublish it and open the quiz editor. Continue?');" : '' }}"
                   class="btn-teacher-edit">
                    ✏️ {{ $isPublished ? 'Unpublish & Edit' : 'Edit Draft' }}
                </a>
            </div>
        </div>
    </div>

    <div class="quiz-detail-card">
        <h3 style="font-size: 16px; font-weight: 900; color: #0D2B4E; margin-bottom: 16px;">Questions List ({{ $questionCount }})</h3>

        @forelse($quiz->questions as $idx => $question)
            @php
                $qType = strtolower((string) ($question->question_type ?? $question->type ?? 'multiple_choice'));
                if ($qType === 'identification') $qType = 'short_answer';
                $typeLabels = [
                    'multiple_choice' => '🔘 Multiple Choice',
                    'multiple_select' => '☑️ Multiple Select',
                    'true_false'      => '⚖️ True / False',
                    'matching'        => '🔗 Matching',
                    'fill_blank'      => '✏️ Fill-in-the-Blank',
                    'short_answer'    => '💬 Short Answer',
                    'essay'           => '📝 Essay',
                ];
                $opts = is_array($question->options) ? $question->options : (json_decode($question->options ?? '[]', true) ?: []);
                $decodedAns = json_decode((string) $question->correct_answer, true);
                $sec = (int) ($question->time_limit_seconds ?? 30);
                $formattedTime = $sec % 3600 === 0
                    ? ($sec / 3600) . 'h'
                    : ($sec % 60 === 0 && $sec >= 60 ? ($sec / 60) . 'm' : $sec . 's');
            @endphp
            <div class="question-list-item">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
                    <div class="flex items-center gap-2">
                        <span style="font-weight: 900; font-size: 13px; color: #0066D6;">Question {{ $idx + 1 }}</span>
                        <span class="px-2.5 py-0.5 rounded-full bg-white border border-[#93C5FD] text-[11px] font-black text-[#0066D6]">
                            {{ $typeLabels[$qType] ?? '🔘 Multiple Choice' }}
                        </span>
                    </div>
                    <span style="font-size: 12px; font-weight: 800; color: #64748B;">⏱️ {{ $formattedTime }} | ⭐ {{ $question->points ?? 100 }} XP</span>
                </div>
                <div style="font-weight: 800; font-size: 14px; color: #0D2B4E; margin-bottom: 8px;">
                    {{ $question->question_text }}
                </div>

                @if(in_array($qType, ['multiple_choice', 'multiple_select'], true) && !empty($opts))
                    @php
                        $rightArr = is_array($decodedAns) ? $decodedAns : [(string) $question->correct_answer];
                    @endphp
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; margin-top: 8px;">
                        @foreach($opts as $opt)
                            @php $isRight = in_array($opt, $rightArr, true); @endphp
                            <div style="font-size: 12px; font-weight: 700; padding: 6px 10px; border-radius: 8px; background: {{ $isRight ? '#DCFCE7' : '#fff' }}; border: 1px solid {{ $isRight ? '#22C55E' : '#B8E2F8' }}; color: {{ $isRight ? '#14532D' : '#475569' }};">
                                {{ $opt }} {{ $isRight ? '✅' : '' }}
                            </div>
                        @endforeach
                    </div>
                @elseif($qType === 'matching')
                    @php
                        $pairs = is_array($question->matching_pairs) && !empty($question->matching_pairs)
                            ? $question->matching_pairs
                            : (is_array($decodedAns) ? $decodedAns : []);
                    @endphp
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($pairs as $p)
                            <span class="px-3 py-1 rounded-xl bg-white border border-[#FDBA74] text-xs font-black text-[#9A3412]">
                                {{ $p['left'] ?? '' }} ↔ {{ $p['right'] ?? '' }}
                            </span>
                        @endforeach
                    </div>
                @elseif($qType === 'fill_blank')
                    @php $blanks = is_array($decodedAns) ? $decodedAns : [(string) $question->correct_answer]; @endphp
                    <div style="font-size: 12px; font-weight: 800; color: #854D0E; margin-top: 4px;">
                        Blanks: <strong>{{ implode(' | ', $blanks) }}</strong>
                    </div>
                @else
                    <div style="font-size: 12px; font-weight: 700; color: #16A34A; margin-top: 4px;">
                        {{ $qType === 'essay' ? 'Rubric / Guide:' : 'Correct Answer:' }}
                        <strong>{{ $question->correct_answer ?: 'Open-ended teacher review' }}</strong>
                    </div>
                @endif
            </div>
        @empty
            <p style="font-size: 13px; color: #888; font-weight: 700;">No questions added to this quiz yet.</p>
        @endforelse
    </div>

    <div class="quiz-detail-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 900; color: #0D2B4E;">Student Attempt Records &amp; Scores</h3>
            @if($attempts->isNotEmpty())
                <button type="button" class="btn-ai-analyze" onclick="analyzeStruggles(null)">
                    🤖 Analyze Class Struggles (Groq AI)
                </button>
            @endif
        </div>

        <div class="ai-analysis-box" id="aiAnalysisBox">
            <div class="ai-analysis-header">
                <span>🧠 Groq AI Diagnostic Insight</span>
                <span id="aiAnalysisTarget" style="font-size: 12px; background: #E2D9FF; padding: 2px 8px; border-radius: 6px;"></span>
            </div>
            <div class="ai-analysis-content" id="aiAnalysisContent"></div>
        </div>

        @if($attempts->isEmpty())
            <p style="font-size: 13px; color: #888; font-weight: 700;">No attempt history recorded yet.</p>
        @else
            <table class="attempts-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Score</th>
                        <th>XP Earned</th>
                        <th>Max Streak</th>
                        <th>Time Spent</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $att)
                        <tr>
                            <td style="font-weight: 800;">{{ $att->student->first_name ?? 'Student' }} {{ $att->student->last_name ?? '' }}</td>
                            <td style="font-weight: 800;">
                                <span style="color: {{ ($att->score / max(1, $att->total_questions)) >= 0.7 ? '#10B981' : '#EF4444' }};">
                                    {{ $att->score }} / {{ $att->total_questions }}
                                </span>
                                ({{ round(($att->score / max(1, $att->total_questions)) * 100) }}%)
                            </td>
                            <td style="font-weight: 800; color: #0066D6;">+{{ $att->total_xp }} XP</td>
                            <td style="font-weight: 800;">🔥 {{ $att->max_streak }}</td>
                            <td>{{ floor(($att->time_taken_seconds ?? 0) / 60) }}m {{ ($att->time_taken_seconds ?? 0) % 60 }}s</td>
                            <td>{{ $att->created_at->format('M d, Y h:i A') }}</td>
                            <td>
                                <button type="button" class="btn-ai-analyze" style="padding: 4px 10px; font-size: 11px;" onclick="analyzeStruggles({{ $att->id }})">
                                    🔍 Analyze Student
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endif
@endsection

@push('scripts')
@if(Auth::user()->isTeacher() || Auth::user()->role === 'admin')
<script>
    function formatAnalysisText(rawText) {
        const escaped = rawText.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return escaped.replace(/\*\*(.+?)\*\*/g, '<strong style="color:#5138EE; display:inline-block; margin-top:6px;">$1</strong>');
    }

    async function analyzeStruggles(attemptId = null) {
        const box = document.getElementById('aiAnalysisBox');
        const content = document.getElementById('aiAnalysisContent');
        const targetBadge = document.getElementById('aiAnalysisTarget');

        box.style.display = 'block';
        content.innerHTML = '⏳ <em>Summarizing missed concepts with Groq AI...</em>';
        targetBadge.textContent = attemptId ? 'Student Attempt' : 'Entire Class';

        try {
            const res = await fetch("{{ route('quiz.analyzeStruggles', $quiz->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ attempt_id: attemptId })
            });
            const data = await res.json();
            if (!res.ok || !data.success) {
                content.innerHTML = `⚠️ <span style="color:#DC2626;">${data.error || 'Failed to analyze student struggles.'}</span>`;
                return;
            }
            if (data.student) targetBadge.textContent = data.student;
            content.innerHTML = formatAnalysisText(data.analysis);
        } catch (e) {
            content.innerHTML = '⚠️ <span style="color:#DC2626;">An error occurred while contacting Groq AI.</span>';
        }
    }
</script>
@endif

@if(Auth::user()->isStudent())
<script>
    let currentQuizId = null;
    let currentQuestionData = null;
    let countdownInterval = null;
    let secondsElapsed = 0;
    let maxTimeAllowed = 30;
    let timeLeft = 30;
    let isSubmitting = false;
    let sharedAudioCtx = null;

    // Interactive state for multi-select & matching
    let selectedMultiIndices = new Set();
    let activeMatchLeftIdx = null;
    let matchedRightByLeft = {}; // { leftIdx: rightText }

    const optionLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
    const matchPalette = [
        { bg: '#EBF5FF', border: '#0066D6', badge: '🔵' },
        { bg: '#ECFCCB', border: '#68CC18', badge: '🟢' },
        { bg: '#FFF7ED', border: '#FF8400', badge: '🟠' },
        { bg: '#ECFEFF', border: '#1DC4DF', badge: '🩵' },
        { bg: '#F3E8FF', border: '#A855F7', badge: '🟣' },
        { bg: '#FEF9C3', border: '#EAB308', badge: '🟡' },
    ];
    const encouragingCheers = ['Awesome job! 🌟', 'Super star! 🚀', 'Way to go! 🎉', 'Spot on! ✨', 'You nailed it! 🏆'];
    const encouragingRetries = ['Good try! Let’s keep going! 💪', 'Almost! You’ll get the next one! 🌈', 'Keep trying, superstar! ⭐'];

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function readAloudQuestion() {
        if (!('speechSynthesis' in window) || !currentQuestionData) return;
        window.speechSynthesis.cancel();

        let speechText = (currentQuestionData.text || '').replace(/_{3,}|\[blank\]/gi, ' blank ');
        if (['multiple_choice', 'multiple_select', 'true_false'].includes(currentQuestionData.type) && Array.isArray(currentQuestionData.options)) {
            currentQuestionData.options.forEach((opt, i) => {
                speechText += `. ${optionLetters[i] || ''}: ${opt}`;
            });
        }

        const utterance = new SpeechSynthesisUtterance(speechText);
        utterance.rate = 0.92;
        utterance.pitch = 1.08;
        window.speechSynthesis.speak(utterance);
    }

    function launchConfetti(count = 24) {
        const card = document.getElementById('quizRunnerCard');
        if (!card) return;
        const colors = ['#0066D6', '#1DC4DF', '#68CC18', '#FF8400', '#FACC15', '#F43F5E'];
        for (let i = 0; i < count; i++) {
            const piece = document.createElement('div');
            piece.className = 'confetti-piece';
            piece.style.left = Math.random() * 100 + '%';
            piece.style.top = '-10px';
            piece.style.backgroundColor = colors[i % colors.length];
            piece.style.animationDelay = (Math.random() * 0.25) + 's';
            card.appendChild(piece);
            setTimeout(() => piece.remove(), 1600);
        }
    }

    function playSound(type) {
        try {
            if (!sharedAudioCtx) sharedAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (sharedAudioCtx.state === 'suspended') sharedAudioCtx.resume();

            const osc = sharedAudioCtx.createOscillator();
            const gain = sharedAudioCtx.createGain();
            osc.connect(gain);
            gain.connect(sharedAudioCtx.destination);

            if (type === 'correct') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(523.25, sharedAudioCtx.currentTime);
                osc.frequency.setValueAtTime(659.25, sharedAudioCtx.currentTime + 0.09);
                osc.frequency.setValueAtTime(783.99, sharedAudioCtx.currentTime + 0.18);
                gain.gain.setValueAtTime(0.22, sharedAudioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, sharedAudioCtx.currentTime + 0.35);
                osc.start();
                osc.stop(sharedAudioCtx.currentTime + 0.35);
            } else if (type === 'pop') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(560, sharedAudioCtx.currentTime);
                gain.gain.setValueAtTime(0.1, sharedAudioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, sharedAudioCtx.currentTime + 0.08);
                osc.start();
                osc.stop(sharedAudioCtx.currentTime + 0.08);
            } else {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(240, sharedAudioCtx.currentTime);
                osc.frequency.setValueAtTime(190, sharedAudioCtx.currentTime + 0.14);
                gain.gain.setValueAtTime(0.18, sharedAudioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, sharedAudioCtx.currentTime + 0.32);
                osc.start();
                osc.stop(sharedAudioCtx.currentTime + 0.32);
            }
        } catch (e) {}
    }

    async function startQuiz(quizId) {
        currentQuizId = quizId;
        const modal = document.getElementById('quizRunnerModal');
        modal.classList.add('active');

        try {
            const res = await fetch("{{ route('quiz.start') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ quiz_id: quizId })
            });
            const data = await res.json();
            if (!res.ok) {
                alert(data.error || 'Failed to start quiz.');
                modal.classList.remove('active');
                return;
            }
            renderQuestion(data);
        } catch (e) {
            alert('An error occurred starting the quiz.');
            modal.classList.remove('active');
        }
    }

    async function exitQuiz() {
        if (!confirm('Stop for now and save your stars?')) return;
        if ('speechSynthesis' in window) window.speechSynthesis.cancel();
        clearInterval(countdownInterval);
        try {
            await fetch("{{ route('quiz.exit') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                }
            });
            window.location.reload();
        } catch (e) {
            window.location.reload();
        }
    }

    function startTimer(durationSeconds) {
        clearInterval(countdownInterval);
        maxTimeAllowed = durationSeconds || 30;
        timeLeft = maxTimeAllowed;
        secondsElapsed = 0;
        isSubmitting = false;

        updateTimerUI();

        countdownInterval = setInterval(() => {
            secondsElapsed++;
            timeLeft--;
            updateTimerUI();

            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                submitAnswer(null, true);
            }
        }, 1000);
    }

    function updateTimerUI() {
        const timerText = document.getElementById('timerText');
        const timerInner = document.getElementById('timerBarInner');
        if (!timerText || !timerInner) return;

        timerText.textContent = `${timeLeft}s`;
        const percentage = Math.max(0, (timeLeft / maxTimeAllowed) * 100);
        timerInner.style.width = `${percentage}%`;

        if (percentage <= 25) timerInner.style.backgroundColor = '#F43F5E';
        else if (percentage <= 50) timerInner.style.backgroundColor = '#FF8400';
        else timerInner.style.backgroundColor = '#68CC18';
    }

    function toggleMultiOption(idx) {
        if (isSubmitting) return;
        const btn = document.getElementById(`opt-btn-${idx}`);
        if (!btn) return;

        if (selectedMultiIndices.has(idx)) {
            selectedMultiIndices.delete(idx);
            btn.classList.remove('multi-picked');
            btn.querySelector('.multi-status').textContent = '⬜';
        } else {
            selectedMultiIndices.add(idx);
            btn.classList.add('multi-picked');
            btn.querySelector('.multi-status').textContent = '☑️';
        }
        playSound('pop');
        const pill = document.getElementById('multiPickedPill');
        if (pill) pill.textContent = `${selectedMultiIndices.size} picked`;
    }

    function pickMatchLeft(leftIdx) {
        if (isSubmitting) return;
        activeMatchLeftIdx = leftIdx;
        document.querySelectorAll('.match-left-node').forEach(el => el.classList.remove('active-left'));
        const el = document.querySelector(`.match-left-node[data-left-idx="${leftIdx}"]`);
        if (el) el.classList.add('active-left');
        playSound('pop');
    }

    function pickMatchRight(rightIdx, rightText) {
        if (isSubmitting || activeMatchLeftIdx === null) return;
        const leftIdx = activeMatchLeftIdx;
        const color = matchPalette[leftIdx % matchPalette.length];

        document.querySelectorAll(`.match-right-node[data-paired-left="${leftIdx}"]`).forEach(prev => {
            prev.removeAttribute('data-paired-left');
            prev.style.background = '#FFFFFF';
            prev.style.borderColor = '#B8E2F8';
            prev.querySelector('.match-icon').textContent = '⚪';
        });

        const leftEl = document.querySelector(`.match-left-node[data-left-idx="${leftIdx}"]`);
        const rightEl = document.querySelector(`.match-right-node[data-right-idx="${rightIdx}"]`);

        [leftEl, rightEl].forEach(node => {
            if (!node) return;
            node.classList.remove('active-left');
            node.style.background = color.bg;
            node.style.borderColor = color.border;
            node.querySelector('.match-icon').textContent = color.badge;
        });

        if (rightEl) rightEl.dataset.pairedLeft = leftIdx;
        matchedRightByLeft[leftIdx] = rightText;
        activeMatchLeftIdx = null;
        playSound('pop');
    }

    function resetMatchPairs() {
        activeMatchLeftIdx = null;
        matchedRightByLeft = {};
        document.querySelectorAll('.match-tile').forEach(node => {
            node.classList.remove('active-left');
            node.removeAttribute('data-paired-left');
            node.style.background = '#FFFFFF';
            node.style.borderColor = '#B8E2F8';
            node.querySelector('.match-icon').textContent = '⚪';
        });
    }

    function updateEssayWords(ta) {
        const count = ta.value.trim().split(/\s+/).filter(Boolean).length;
        const badge = document.getElementById('essayWordBadge');
        if (badge) badge.textContent = `${count} ${count === 1 ? 'word' : 'words'} ✍️`;
    }

    function renderQuestion(data) {
        if ('speechSynthesis' in window) window.speechSynthesis.cancel();

        const container = document.getElementById('quizContainer');
        currentQuestionData = data.question;
        selectedMultiIndices = new Set();
        activeMatchLeftIdx = null;
        matchedRightByLeft = {};

        if (!data.question || !data.question.id) {
            container.innerHTML = `<div style="text-align:center; padding: 20px;"><h3>No questions available.</h3></div>`;
            return;
        }

        const qType = data.question.type || 'multiple_choice';
        const progressPercent = Math.round((data.question_number / data.total_questions) * 100);

        const diffLevel = (data.question.difficulty || data.level || 'easy').toLowerCase();
        let levelClass = 'level-easy';
        let levelLabel = '🌱 Sprout Level';
        if (diffLevel === 'hard') { levelClass = 'level-hard'; levelLabel = '🚀 Super Level'; }
        else if (diffLevel === 'medium') { levelClass = 'level-medium'; levelLabel = '⚡ Explorer Level'; }

        const currentStreak = data.current_streak || 0;
        let streakMultiplierText = '';
        if (currentStreak >= 5) streakMultiplierText = ' • 2x Boost!';
        else if (currentStreak >= 3) streakMultiplierText = ' • 1.5x Boost!';

        let promptHtml = `<h3 class="kid-question-text">${escapeHtml(data.question.text)}</h3>`;
        if (qType === 'fill_blank') {
            const parts = String(data.question.text || '').split(/_{3,}|\[blank\]/gi);
            if (parts.length > 1) {
                promptHtml = `<div class="kid-question-text">` + parts.map((seg, idx) => {
                    return escapeHtml(seg) + (idx < parts.length - 1
                        ? `<input type="text" class="inline-blank-input blank-slot-field" placeholder="type here..." autocomplete="off">`
                        : '');
                }).join('') + `</div>`;
            } else {
                promptHtml = `
                    <div style="width:100%;">
                        <h3 class="kid-question-text mb-3">${escapeHtml(data.question.text)}</h3>
                        <input type="text" class="kid-typed-input blank-slot-field" placeholder="Fill in the missing word..." autocomplete="off">
                    </div>`;
            }
        }

        let html = `
            <div class="kid-hud-bar">
                <div class="kid-hud-pills">
                    <span class="level-badge ${levelClass}">${levelLabel}</span>
                    <span class="streak-badge">🔥 ${currentStreak} in a row${streakMultiplierText}</span>
                    <span class="xp-badge">⭐ ${data.total_xp || 0} Stars</span>
                </div>
                <button type="button" class="btn-exit-quiz" onclick="exitQuiz()">💾 Save &amp; Stop</button>
            </div>

            <div class="kid-progress-wrap">
                <div class="kid-progress-label">
                    <span>🗺️ Challenge ${data.question_number} of ${data.total_questions}</span>
                    <span>${progressPercent}%</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: ${progressPercent}%;"></div>
                </div>
            </div>

            <div class="timer-container">
                <span style="font-size: 16px;">⏱️</span>
                <div class="timer-bar-outer">
                    <div class="timer-bar-inner" id="timerBarInner"></div>
                </div>
                <div class="timer-text" id="timerText">--s</div>
            </div>

            <div class="kid-question-box">
                ${promptHtml}
                <button type="button" class="btn-read-aloud" onclick="readAloudQuestion()" title="Listen to question">🔊 Listen</button>
            </div>
        `;

        if (qType === 'multiple_choice') {
            html += `<div class="kid-options-stack">`;
            (data.question.options || []).forEach((opt, idx) => {
                const letter = optionLetters[idx] || (idx + 1);
                html += `
                    <button type="button" class="option-btn" id="opt-btn-${idx}" onclick="submitAnswer(${idx})">
                        <span class="option-letter-badge letter-${idx % 4}">${letter}</span>
                        <span style="flex: 1;">${escapeHtml(opt)}</span>
                    </button>
                `;
            });
            html += `</div>`;
        } else if (qType === 'multiple_select') {
            html += `
                <div class="mb-2.5 flex items-center justify-between">
                    <span class="text-xs font-black text-[#0E7490] bg-[#ECFEFF] px-3 py-1 rounded-full border border-[#67E8F9]">
                        ☑️ Pick ALL correct choices!
                    </span>
                    <span id="multiPickedPill" class="text-xs font-black text-[#0066D6]">0 picked</span>
                </div>
                <div class="kid-options-stack">
                    ${(data.question.options || []).map((opt, idx) => `
                        <button type="button" class="option-btn" id="opt-btn-${idx}" onclick="toggleMultiOption(${idx})">
                            <span class="option-letter-badge letter-${idx % 4}">${optionLetters[idx] || (idx + 1)}</span>
                            <span style="flex: 1;">${escapeHtml(opt)}</span>
                            <span class="multi-status text-lg">⬜</span>
                        </button>
                    `).join('')}
                </div>
                <button type="button" class="btn-kid-play" style="width:100%; justify-content:center;" onclick="submitAnswer(null)">
                    Lock In My Choices! ✨
                </button>
            `;
        } else if (qType === 'true_false') {
            html += `
                <div class="tf-grid">
                    <button type="button" class="tf-card-btn tf-true-btn" id="opt-btn-0" onclick="submitAnswer(0)">
                        <span style="font-size: 38px;">✅</span>
                        <span>TRUE</span>
                    </button>
                    <button type="button" class="tf-card-btn tf-false-btn" id="opt-btn-1" onclick="submitAnswer(1)">
                        <span style="font-size: 38px;">❌</span>
                        <span>FALSE</span>
                    </button>
                </div>
            `;
        } else if (qType === 'matching') {
            const leftItems = data.question.left_items || [];
            const rightItems = data.question.right_items || [];
            html += `
                <div class="mb-2.5 flex items-center justify-between gap-2">
                    <span class="text-xs font-black text-[#0066D6]">🔗 Tap a Left card, then tap its matching Right card!</span>
                    <button type="button" onclick="resetMatchPairs()" class="text-xs font-black text-[#C2410C] bg-[#FFF7ED] px-2.5 py-1 rounded-xl border border-[#FDBA74] cursor-pointer">🔄 Reset</button>
                </div>
                <div class="match-board-grid">
                    <div class="space-y-2">
                        ${leftItems.map((txt, idx) => `
                            <div class="match-tile match-left-node" data-left-idx="${idx}" onclick="pickMatchLeft(${idx})">
                                <span>${escapeHtml(txt)}</span>
                                <span class="match-icon">⚪</span>
                            </div>
                        `).join('')}
                    </div>
                    <div class="space-y-2">
                        ${rightItems.map((txt, idx) => `
                            <div class="match-tile match-right-node" data-right-idx="${idx}" onclick="pickMatchRight(${idx}, ${JSON.stringify(txt).replace(/"/g, '&quot;')})">
                                <span class="match-icon">⚪</span>
                                <span>${escapeHtml(txt)}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                <button type="button" class="btn-kid-play" style="width:100%; justify-content:center;" onclick="submitAnswer(null)">
                    Check My Matches! 🔗
                </button>
            `;
        } else if (qType === 'fill_blank') {
            html += `
                <button type="button" class="btn-kid-play" style="width:100%; justify-content:center;" onclick="submitAnswer(null)">
                    Check My Answer! ✏️
                </button>
            `;
        } else if (qType === 'essay') {
            html += `
                <div class="mb-3">
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-xs font-black text-[#0066D6]">📝 Write your story or explanation below:</span>
                        <span id="essayWordBadge" class="text-xs font-black text-[#4FA30D]">0 words ✍️</span>
                    </div>
                    <textarea id="typedAnswer" rows="4" oninput="updateEssayWords(this)" class="kid-typed-input" placeholder="Write your thoughts in complete sentences..."></textarea>
                </div>
                <button type="button" class="btn-kid-play" style="width:100%; justify-content:center;" onclick="submitAnswer(null)">
                    Submit My Writing! 🌟
                </button>
            `;
        } else {
            html += `
                <div style="margin-bottom: 14px;">
                    <input type="text" id="typedAnswer" class="kid-typed-input" placeholder="Type your answer here..." autocomplete="off" onkeypress="if(event.key === 'Enter') submitAnswer(null)">
                </div>
                <button type="button" class="btn-kid-play" style="width:100%; justify-content:center;" onclick="submitAnswer(null)">
                    Check My Answer! ✨
                </button>
            `;
        }

        html += `<div id="feedbackContainer"></div>`;
        container.innerHTML = html;
        startTimer(data.question.time_limit || 30);
    }

    async function submitAnswer(selectedIndex, isTimeout = false) {
        if (isSubmitting) return;
        isSubmitting = true;
        clearInterval(countdownInterval);
        if ('speechSynthesis' in window) window.speechSynthesis.cancel();

        const container = document.getElementById('quizContainer');
        const feedbackContainer = document.getElementById('feedbackContainer');
        const typedInput = document.getElementById('typedAnswer');
        const typedAnswer = typedInput ? typedInput.value : null;

        const blankInputs = Array.from(document.querySelectorAll('.blank-slot-field'));
        const blankAnswers = blankInputs.map(inp => inp.value);

        const payload = {
            question_id: currentQuestionData.id,
            selected_index: selectedIndex,
            selected_indices: Array.from(selectedMultiIndices),
            matched_pairs: matchedRightByLeft,
            blank_answers: blankAnswers,
            typed_answer: typedAnswer,
            time_taken_seconds: secondsElapsed
        };

        try {
            const res = await fetch("{{ route('quiz.submitAnswer') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok) {
                alert(data.message || data.error || 'Submission failed.');
                isSubmitting = false;
                return;
            }

            const wasCorrect = Boolean(data.was_correct);

            if (selectedIndex !== null) {
                const btn = document.getElementById(`opt-btn-${selectedIndex}`);
                if (btn) btn.classList.add(wasCorrect ? 'correct-choice' : 'wrong-choice');
            }

            if (feedbackContainer) {
                if (isTimeout) {
                    playSound('wrong');
                    feedbackContainer.innerHTML = `<div class="feedback-banner feedback-wrong">⏰ Time's up! Let's try the next one!</div>`;
                } else if (wasCorrect) {
                    playSound('correct');
                    launchConfetti(20);
                    const cheer = encouragingCheers[Math.floor(Math.random() * encouragingCheers.length)];
                    const multMsg = (data.multiplier && data.multiplier > 1) ? ` (${data.multiplier}x Bonus!)` : '';
                    feedbackContainer.innerHTML = `<div class="feedback-banner feedback-correct">${cheer} +${data.earned_xp || 0} Stars${multMsg}</div>`;
                } else {
                    playSound('wrong');
                    const retryMsg = encouragingRetries[Math.floor(Math.random() * encouragingRetries.length)];
                    feedbackContainer.innerHTML = `<div class="feedback-banner feedback-wrong">💡 ${retryMsg}</div>`;
                }
            }

            setTimeout(() => {
                if (data.finished) {
                    playSound('correct');
                    launchConfetti(42);
                    const acc = Math.round(data.summary.accuracy || 0);
                    const trophyEmoji = acc >= 80 ? '🏆' : (acc >= 50 ? '🌟' : '🎈');
                    const celebrationTitle = acc >= 80 ? 'Amazing Job, Superstar!' : 'Great Effort! Quest Complete!';

                    if (Array.isArray(data.new_badges) && data.new_badges.length > 0 && window.showBrainBalanceToast) {
                        data.new_badges.forEach((badgeAlert, idx) => {
                            window.showBrainBalanceToast(badgeAlert, 3500 + (idx * 300));
                        });
                    }

                    container.innerHTML = `
                        <div style="text-align:center; padding: 20px 10px;">
                            <div style="font-size: 68px; margin-bottom: 10px;">${trophyEmoji}</div>
                            <h2 class="font-display" style="font-size: 28px; font-weight: 700; color:#0D2B4E; margin-bottom: 8px;">${celebrationTitle}</h2>
                            <p style="font-size: 16px; font-weight: 800; color:#475569; margin-bottom: 20px;">
                                You got <strong style="color:#16A34A; font-size: 20px;">${data.summary.score} out of ${data.summary.total}</strong> right! (${acc}%)
                            </p>
                            <div style="background: linear-gradient(135deg, #FFF7ED, #EBF5FF); border: 3px solid #FF8400; border-radius: 22px; padding: 18px; margin-bottom: 24px; box-shadow: 0 5px 0 #FDBA74;">
                                <div style="font-size: 12px; font-weight: 900; color: #C2410C; letter-spacing: 0.6px;">REWARDS EARNED</div>
                                <div style="font-size: 28px; font-weight: 900; color: #0066D6; margin-top: 4px;">
                                    +${data.summary.total_xp} XP ⭐
                                </div>
                                <div style="font-size: 13px; font-weight: 900; color: #3E8708; margin-top: 6px;">
                                    Best Streak: 🔥 ${data.summary.max_streak} in a row!
                                </div>
                            </div>
                            <button type="button" class="btn-kid-play" style="width:100%; justify-content:center;" onclick="window.location.reload()">
                                🎉 See My Scoreboard
                            </button>
                        </div>
                    `;
                } else {
                    renderQuestion(data);
                }
            }, 1350);
        } catch (e) {
            console.error("Quiz submission error:", e);
            alert('An error occurred submitting your answer.');
            isSubmitting = false;
        }
    }
</script>
@endif
@endpush