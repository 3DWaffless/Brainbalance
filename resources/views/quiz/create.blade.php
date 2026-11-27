{{-- FILE: resources/views/quiz/create.blade.php --}}
@extends('layouts.app')

@section('title', (isset($editingQuiz) ? 'Edit Quiz' : 'Create Quiz') . ' - ' . config('app.name', 'BrainBalance'))

@push('styles')
<style>
    :root {
        --bb-blue: #0066D6;
        --bb-blue-deep: #004FA8;
        --bb-cyan: #1DC4DF;
        --bb-green: #68CC18;
        --bb-green-deep: #3E8708;
        --bb-orange: #FF8400;
        --bb-orange-deep: #D96B00;
        --bb-ink: #0D2B4E;
        --bb-muted: #4A688A;
        --bb-surface: #F5FAFF;
        --bb-border: #C7E0FF;
        --bb-spectrum: linear-gradient(90deg, #0066D6 0%, #1DC4DF 33%, #68CC18 68%, #FF8400 100%);
    }

    .bb-builder-page {
        position: relative;
    }

    .bb-spectrum-strip {
        height: 6px;
        width: 100%;
        background: var(--bb-spectrum);
    }

    /* Hero / Header Banner matching Landing Page & Dashboard */
    .bb-hero-banner {
        background: #FFFFFF;
        border: 3px solid var(--bb-border);
        border-radius: 26px;
        box-shadow: 0 6px 0 var(--bb-border);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .bb-hero-inner {
        padding: 22px 26px;
        background:
            radial-gradient(circle at 95% 15%, rgba(29, 196, 223, 0.12), transparent 40%),
            radial-gradient(circle at 5% 90%, rgba(104, 204, 24, 0.10), transparent 40%),
            #FFFFFF;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .bb-hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 13px;
        border-radius: 9999px;
        background: #EBF5FF;
        border: 2px solid #93C5FD;
        color: var(--bb-blue);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 6px;
    }

    .bb-hero-pill.draft-pill {
        background: #FFF7ED;
        border-color: #FDBA74;
        color: #C2410C;
    }

    .autosave-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 900;
        border: 2px solid #BAE6FD;
        background: #F0F9FF;
        color: #0369A1;
        margin-bottom: 6px;
        transition: all 0.2s ease;
    }

    .autosave-badge.saving {
        border-color: #93C5FD;
        background: #EBF5FF;
        color: #0066D6;
    }

    .autosave-badge.saved {
        border-color: #86EFAC;
        background: #F0FDF4;
        color: #15803D;
    }

    .autosave-badge.dirty {
        border-color: #FDE047;
        background: #FEFCE8;
        color: #854D0E;
    }

    .autosave-badge.error {
        border-color: #FECACA;
        background: #FEF2F2;
        color: #DC2626;
    }

    .create-quiz-wrapper {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .create-quiz-wrapper {
            grid-template-columns: 1fr;
        }
    }

    .card-box {
        background: #FFFFFF;
        border-radius: 26px;
        border: 3px solid var(--bb-border);
        box-shadow: 0 6px 0 var(--bb-border);
        overflow: hidden;
    }

    .card-box-body {
        padding: 26px;
    }

    .class-banner {
        background: linear-gradient(135deg, #EBF5FF 0%, #F5FAFF 100%);
        border: 2.5px solid var(--bb-border);
        border-radius: 20px;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 22px;
        box-shadow: 0 4px 0 #E0EEFF;
    }

    .class-title {
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: var(--bb-ink);
    }

    .class-code-badge {
        background: #FFFFFF;
        color: var(--bb-blue);
        font-weight: 900;
        font-size: 12px;
        padding: 6px 14px;
        border-radius: 9999px;
        border: 2px solid #93C5FD;
        box-shadow: 0 3px 0 #DBEAFE;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    @media (max-width: 640px) {
        .form-grid-2 {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 16px;
    }

    .form-label {
        font-size: 11px;
        font-weight: 900;
        color: var(--bb-muted);
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border-radius: 16px;
        border: 2.5px solid var(--bb-border);
        background: var(--bb-surface);
        font-size: 13.5px;
        font-weight: 800;
        color: var(--bb-ink);
        outline: none;
        box-sizing: border-box;
        transition: all 0.18s ease;
    }

    .form-control:focus {
        border-color: var(--bb-blue);
        background: #FFFFFF;
        box-shadow: 0 0 0 4px rgba(0, 102, 214, 0.14);
    }

    .file-dropzone {
        border: 3px dashed #38BDF8;
        background: linear-gradient(180deg, #F5FAFF 0%, #EBF5FF 100%);
        border-radius: 20px;
        padding: 22px 18px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.18s ease, transform 0.18s ease;
    }

    .file-dropzone:hover {
        border-color: var(--bb-blue);
        transform: translateY(-1px);
    }

    .error-banner {
        background: #FEF2F2;
        color: #DC2626;
        border: 2.5px solid #FECACA;
        border-radius: 16px;
        padding: 12px 16px;
        font-size: 12.5px;
        font-weight: 800;
        margin-top: 6px;
        box-shadow: 0 4px 0 #FEE2E2;
    }

    .notice-banner {
        background: #FFF7ED;
        color: #9A3412;
        border: 2.5px solid #FDBA74;
        border-radius: 16px;
        padding: 12px 16px;
        font-size: 12.5px;
        font-weight: 800;
        margin-bottom: 18px;
        box-shadow: 0 4px 0 #FED7AA;
    }

    /* Tactile 3D Push Buttons matching Landing Page & Dashboard */
    .btn-submit {
        background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);
        color: #FFFFFF;
        border: 2px solid var(--bb-blue-deep);
        padding: 13px 24px;
        border-radius: 18px;
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 5px 0 var(--bb-blue-deep);
        transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
    }

    .btn-submit:hover {
        filter: brightness(1.04);
        transform: translateY(-2px);
    }

    .btn-submit:active {
        transform: translateY(3px);
        box-shadow: 0 2px 0 var(--bb-blue-deep);
    }

    .btn-submit:disabled {
        background: #CBD5E1;
        border-color: #94A3B8;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    .btn-publish-green {
        background: linear-gradient(135deg, #68CC18 0%, #52B00E 100%);
        border-color: var(--bb-green-deep);
        box-shadow: 0 5px 0 var(--bb-green-deep);
    }

    .btn-publish-green:active {
        box-shadow: 0 2px 0 var(--bb-green-deep);
    }

    .btn-draft-tactile {
        background: #FFFFFF;
        color: var(--bb-blue);
        border: 2.5px solid #93C5FD;
        padding: 13px 22px;
        border-radius: 18px;
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 5px 0 #BFDBFE;
        transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.12s ease;
    }

    .btn-draft-tactile:hover {
        background: #EBF5FF;
        transform: translateY(-2px);
    }

    .btn-draft-tactile:active {
        transform: translateY(3px);
        box-shadow: 0 2px 0 #BFDBFE;
    }

    .btn-ai-cyan {
        background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);
        color: #FFFFFF;
        border: 2.5px solid var(--bb-blue-deep);
        padding: 10px 18px;
        border-radius: 16px;
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 0 var(--bb-blue-deep);
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }

    .btn-ai-cyan:hover {
        transform: translateY(-2px);
    }

    .btn-ai-cyan:active {
        transform: translateY(2px);
        box-shadow: 0 2px 0 var(--bb-blue-deep);
    }

    .btn-bank-orange {
        background: linear-gradient(135deg, #FF8400 0%, #FFA31A 100%);
        color: #FFFFFF;
        border: 2.5px solid var(--bb-orange-deep);
        padding: 10px 18px;
        border-radius: 16px;
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 0 var(--bb-orange-deep);
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }

    .btn-bank-orange:hover {
        transform: translateY(-2px);
    }

    .btn-bank-orange:active {
        transform: translateY(2px);
        box-shadow: 0 2px 0 var(--bb-orange-deep);
    }

    .btn-back-tactile {
        background: #FFFFFF;
        color: var(--bb-ink);
        border: 2.5px solid var(--bb-border);
        padding: 10px 16px;
        border-radius: 16px;
        font-size: 12.5px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 0 var(--bb-border);
        transition: transform 0.12s ease, border-color 0.12s ease;
    }

    .btn-back-tactile:hover {
        border-color: var(--bb-blue);
        color: var(--bb-blue);
        transform: translateY(-2px);
    }

    .type-pill-btn {
        padding: 7px 12px;
        border-radius: 13px;
        font-size: 11.5px;
        font-weight: 900;
        border: 2px solid var(--bb-border);
        background: #FFFFFF;
        color: var(--bb-muted);
        cursor: pointer;
        transition: all 0.14s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 3px 0 #E0EEFF;
    }

    .type-pill-btn:hover {
        border-color: var(--bb-cyan);
        color: var(--bb-blue);
        transform: translateY(-1px);
    }

    .type-pill-btn.active {
        background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);
        border-color: var(--bb-blue-deep);
        color: #FFFFFF;
        box-shadow: 0 3px 0 var(--bb-blue-deep);
    }

    .question-card {
        background: #FFFFFF;
        border: 3px solid var(--bb-border);
        border-radius: 24px;
        padding: 22px;
        margin-bottom: 20px;
        position: relative;
        box-shadow: 0 6px 0 var(--bb-border);
        transition: border-color 0.18s ease;
    }

    .question-card:focus-within {
        border-color: #60A5FA;
    }

    .btn-remove-question {
        background: #FEF2F2;
        color: #DC2626;
        border: 2px solid #FECACA;
        border-radius: 12px;
        padding: 5px 12px;
        font-size: 11.5px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 2px 0 #FECACA;
    }

    .btn-remove-question:hover {
        background: #FEE2E2;
    }

    /* Dashboard-style Guide Sidebar Card */
    .sidebar-card {
        background: #FFFFFF;
        color: var(--bb-ink);
        border-radius: 26px;
        border: 3px solid var(--bb-border);
        box-shadow: 0 6px 0 var(--bb-border);
        overflow: hidden;
    }

    .sidebar-header {
        background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);
        color: #FFFFFF;
        padding: 18px 22px;
        border-bottom: 3px solid var(--bb-blue-deep);
    }

    .sidebar-title {
        font-family: 'Fredoka', 'Nunito', sans-serif;
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }

    .sidebar-body {
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .sidebar-item {
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.55;
        color: var(--bb-ink);
        background: var(--bb-surface);
        border: 2px solid var(--bb-border);
        padding: 12px 14px;
        border-radius: 16px;
    }

    .sidebar-item.accent-blue {
        background: #EBF5FF;
        border-color: #93C5FD;
    }

    .sidebar-item.accent-orange {
        background: #FFF7ED;
        border-color: #FED7AA;
    }

    .sidebar-item.accent-green {
        background: #F4FCEB;
        border-color: #BEF264;
    }

    .sidebar-item.accent-amber {
        background: #FEFCE8;
        border-color: #FDE047;
    }

    /* Slide-over Drawers (Question Bank & AI Generator share identical formatting) */
    .bank-overlay {
        position: fixed;
        inset: 0;
        background: rgba(13, 43, 78, 0.52);
        backdrop-filter: blur(4px);
        z-index: 70;
        display: none;
        justify-content: flex-end;
    }

    .bank-overlay.open {
        display: flex;
    }

    .bank-drawer {
        width: 100%;
        max-width: 720px;
        height: 100%;
        background: var(--bb-surface);
        border-left: 4px solid var(--bb-cyan);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .ai-stepper-btn {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        border: 2px solid var(--bb-border);
        background: #FFFFFF;
        color: var(--bb-ink);
        font-weight: 900;
        font-size: 15px;
        line-height: 1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.12s ease;
    }

    .ai-stepper-btn:hover {
        border-color: var(--bb-blue);
        color: var(--bb-blue);
        background: #EBF5FF;
    }
</style>
@endpush

@section('content')
@php
    $isEditing = isset($editingQuiz) && $editingQuiz;
    $bankTotalQuestions = collect($questionBank ?? [])->sum(fn ($q) => count($q['questions'] ?? []));
    $publishedModulesCount = collect($classModules ?? [])->count();

    $defaultPublishedAt = old(
        'published_at',
        isset($published_at)
            ? \Carbon\Carbon::parse($published_at)->format('Y-m-d\TH:i')
            : ($isEditing && $editingQuiz->published_at
                ? \Carbon\Carbon::parse($editingQuiz->published_at)->format('Y-m-d\TH:i')
                : now()->format('Y-m-d\TH:i'))
    );

    $defaultDeadlineAt = old(
        'deadline_at',
        isset($deadline_at)
            ? \Carbon\Carbon::parse($deadline_at)->format('Y-m-d\TH:i')
            : ($isEditing && $editingQuiz->deadline_at
                ? \Carbon\Carbon::parse($editingQuiz->deadline_at)->format('Y-m-d\TH:i')
                : now()->addDays(7)->format('Y-m-d\TH:i'))
    );
@endphp

<div class="bb-builder-page">

    {{-- Top Hero Banner matching Landing Page & Teacher Dashboard --}}
    <div class="bb-hero-banner">
        <div class="bb-spectrum-strip"></div>
        <div class="bb-hero-inner">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="bb-hero-pill {{ $isEditing ? 'draft-pill' : '' }}">
                        <span>
                            {{ $isEditing ? '📝 Quiz Editor • Draft Mode' : '✨ Quiz Builder • 7 Question Types' }}
                        </span>
                    </div>
                    <div id="autoSaveStatusBadge" class="autosave-badge">
                        <span id="autoSaveStatusText">🛡️ Auto-draft protection active</span>
                    </div>
                </div>
                <h1 id="heroQuizHeading" class="font-display text-2xl sm:text-3xl font-bold text-[#0D2B4E]">
                    {{ $isEditing ? 'Edit Quiz: ' . ($editingQuiz->title ?? 'Untitled Draft') : 'Create New Quiz 📝' }}
                </h1>
                <p class="text-xs sm:text-sm font-extrabold text-[#4A688A] mt-1">
                    Build from scratch with 7 interactive types, generate questions with Groq AI, reuse from your Question Bank, or save as a draft.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="openAiGenerator()" class="btn-ai-cyan">
                    <span>🤖 AI Generate Questions</span>
                </button>
                <button type="button" onclick="openQuestionBank()" class="btn-bank-orange">
                    <span>🏦 Question Bank</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-white text-[#D96B00] text-xs font-black">{{ $bankTotalQuestions }}</span>
                </button>
                <a href="{{ route('quiz.index') }}" id="btnBackToQuizzes" onclick="handleExitToQuizzes(event)" class="btn-back-tactile">
                    &larr; Back to Quizzes
                </a>
            </div>
        </div>
    </div>

    <div id="restoredDraftNotice" class="notice-banner" style="display: none;">
        ✨ Your unsaved quiz edits were automatically restored after refresh and synced to this draft.
    </div>

    @if (session('info'))
        <div class="notice-banner">
            ℹ️ {{ session('info') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="error-banner mb-5">
            ⚠️ Please fix the following before saving:
            <ul class="list-disc pl-5 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="create-quiz-wrapper">
        <div class="card-box">
            <div class="bb-spectrum-strip"></div>
            <div class="card-box-body">
                @if(isset($classes) && $classes->count() > 1)
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Select Target Class</label>
                        <select id="classSelector" class="form-control" onchange="updateSelectedClass(this.value)">
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ (isset($selectedClass) && $selectedClass->id == $c->id) ? 'selected' : '' }}>
                                    {{ $c->class_name ?? $c->name }} (Code: {{ $c->class_code ?? $c->code ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="class-banner">
                        <div>
                            <div style="font-size: 10px; font-weight: 900; color: #4A688A; text-transform: uppercase; letter-spacing: 0.5px;">Assigned Class</div>
                            <div class="class-title">{{ $selectedClass->class_name ?? $selectedClass->name ?? 'Default Class' }}</div>
                        </div>
                        <div class="class-code-badge">
                            Code: {{ $selectedClass->class_code ?? $selectedClass->code ?? 'N/A' }}
                        </div>
                    </div>
                @endif

                <!-- UNIFIED QUIZ BUILDER / EDITOR FORM -->
                <form id="manualForm"
                      action="{{ $isEditing ? route('quiz.update', $editingQuiz) : route('quiz.store') }}"
                      method="POST">
                    @csrf
                    <div id="spoofMethodContainer">
                        @if($isEditing)
                            @method('PUT')
                        @endif
                    </div>

                    <input type="hidden" name="quiz_id" id="quizIdInput" value="{{ $isEditing ? $editingQuiz->id : '' }}">
                    <input type="hidden" name="draft_token" id="draftTokenInput" value="">
                    <input type="hidden" name="class_id" id="manualClassId" value="{{ $selectedClass->id ?? ($classes->first()->id ?? '') }}">
                    <input type="hidden" name="save_action" id="saveActionInput" value="publish">

                    <div class="form-group">
                        <label class="form-label">Quiz Title</label>
                        <input type="text"
                               id="quizTitleInput"
                               name="quiz_title"
                               class="form-control"
                               value="{{ old('quiz_title', $quiz_title ?? ($editingQuiz->title ?? ($preselectedModule->title ?? ''))) }}"
                               placeholder="e.g. Grade 3 Science & Math Quest"
                               required>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <input type="text"
                                   id="quizSubjectInput"
                                   name="subject"
                                   class="form-control"
                                   value="{{ old('subject', $subject ?? ($editingQuiz->subject ?? 'General')) }}"
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Max Attempts</label>
                            <input type="number"
                                   id="quizMaxAttemptsInput"
                                   name="max_attempts"
                                   class="form-control"
                                   value="{{ old('max_attempts', $max_attempts ?? ($editingQuiz->max_attempts ?? 1)) }}"
                                   min="1">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Publish Date</label>
                            <input type="datetime-local" id="quizPublishedAtInput" name="published_at" class="form-control" value="{{ $defaultPublishedAt }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deadline Date</label>
                            <input type="datetime-local" id="quizDeadlineAtInput" name="deadline_at" class="form-control" value="{{ $defaultDeadlineAt }}" required>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 my-5 pt-4 border-t-2 border-dashed border-[#C7E0FF]">
                        <h3 class="font-display text-lg font-bold text-[#0D2B4E]">
                            Quiz Questions (<span id="totalQuestionsBadge" class="text-[#0066D6]">0</span>)
                        </h3>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" onclick="openAiGenerator()" class="btn-ai-cyan text-xs py-2 px-3.5">
                                🤖 Generate with AI
                            </button>
                            <button type="button" onclick="openQuestionBank()" class="btn-bank-orange text-xs py-2 px-3.5">
                                🏦 Import from Published Quizzes
                            </button>
                        </div>
                    </div>
                    <br>

                    <div id="questionsContainer"></div>

                    <!-- Add Question Type Selector Bar -->
                    <div class="p-5 rounded-3xl border-3 border-dashed border-[#38BDF8] bg-[#F5FAFF] text-center mb-5">
                        <div class="text-[11px] font-black uppercase tracking-wider text-[#0066D6] mb-3">
                            ➕ Add a New Question by Type
                        </div>
                        <div class="flex flex-wrap justify-center gap-2">
                            <button type="button" class="type-pill-btn" onclick="addQuestionCard({ type: 'multiple_choice' })">🔘 Multiple Choice</button>
                            <button type="button" class="type-pill-btn" onclick="addQuestionCard({ type: 'multiple_select' })">☑️ Multiple Select</button>
                            <button type="button" class="type-pill-btn" onclick="addQuestionCard({ type: 'true_false' })">⚖️ True / False</button>
                            <button type="button" class="type-pill-btn" onclick="addQuestionCard({ type: 'matching' })">🔗 Matching</button>
                            <button type="button" class="type-pill-btn" onclick="addQuestionCard({ type: 'fill_blank' })">✏️ Fill-in-the-Blank</button>
                            <button type="button" class="type-pill-btn" onclick="addQuestionCard({ type: 'short_answer' })">💬 Short Answer</button>
                            <button type="button" class="type-pill-btn" onclick="addQuestionCard({ type: 'essay' })">📝 Essay</button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-3 mt-6 pt-4 border-t-2 border-[#E0EEFF]">
                        <button type="button" onclick="submitQuizForm('draft')" class="btn-draft-tactile">
                            💾 Save as Draft
                        </button>
                        <button type="button" onclick="submitQuizForm('publish')" class="btn-submit btn-publish-green">
                            🚀 Publish Quiz Now
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

{{-- =========================================================================
     AI QUESTION GENERATOR DRAWER (MATCHES QUESTION BANK FORMATTING)
     ========================================================================= --}}
<div id="aiGeneratorOverlay" class="bank-overlay" onclick="if(event.target===this) closeAiGenerator()">
    <div class="bank-drawer">
        <div class="bb-spectrum-strip"></div>

        <div class="p-5 bg-white border-b-2 border-[#C7E0FF]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-[11px] font-black uppercase tracking-wider text-[#0066D6]">🤖 Groq AI Lesson Analyzer</div>
                    <h2 class="font-display text-2xl font-bold text-[#0D2B4E]">AI Question Generator</h2>
                </div>
                <button type="button" onclick="closeAiGenerator()" class="w-10 h-10 rounded-2xl bg-[#F5FAFF] border-2 border-[#C7E0FF] font-black text-lg text-[#0D2B4E] hover:bg-[#EBF5FF] hover:border-[#0066D6] cursor-pointer">
                    ✕
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-5 space-y-5">
            {{-- 1. Source File Selection (Computer Upload OR Published Class Files) --}}
            <div class="bg-white rounded-3xl border-2.5 border-[#C7E0FF] p-4 shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <span class="form-label text-[#0D2B4E]">1. Choose Lesson Source File</span>
                    <div class="flex gap-1.5">
                        <button type="button" id="aiSrcComputerBtn" class="type-pill-btn active" onclick="switchAiSourceTab('computer')">
                            💻 From Computer
                        </button>
                        <button type="button" id="aiSrcClassBtn" class="type-pill-btn" onclick="switchAiSourceTab('class')">
                            📚 Published Class Files ({{ $publishedModulesCount }})
                        </button>
                    </div>
                </div>

                {{-- Source Tab A: Computer Upload --}}
                <div id="aiSourceComputerPane">
                    <label class="file-dropzone block">
                        <div class="w-11 h-11 mx-auto mb-2 rounded-2xl bg-white border-2 border-[#C7E0FF] shadow-xs flex items-center justify-center text-xl">
                            📄
                        </div>
                        <div class="text-xs font-black text-[#0066D6] mb-1" id="aiComputerFileLabel">
                            Click to choose a lesson file from your computer
                        </div>
                        <input type="file"
                               id="aiModuleFileInput"
                               onchange="onAiComputerFileSelected(this)"
                               accept=".pdf,.txt,.md,.docx,.pptx,.jpg,.jpeg,.png,.webp,.mp3,.wav,.m4a,.ogg,.flac,.mp4,.webm"
                               class="hidden">
                        <div class="text-[11px] font-bold text-[#4A688A]">
                            Supports PDF, Word (.docx), PowerPoint (.pptx), Images, Audio, and Video (up to 25MB)
                        </div>
                    </label>
                </div>

                {{-- Source Tab B: Published Class Files --}}
                <div id="aiSourceClassPane" style="display: none;">
                    <input type="text"
                           id="aiModuleSearchInput"
                           oninput="renderAiClassModules()"
                           placeholder="🔍 Search published class files by title, filename, or class..."
                           class="form-control mb-2.5">
                    <div id="aiClassModulesList" class="max-h-48 overflow-y-auto space-y-2 pr-1"></div>
                </div>
            </div>

            {{-- 2. Question Type Counts Selector --}}
            <div class="bg-white rounded-3xl border-2.5 border-[#C7E0FF] p-4 shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <div>
                        <span class="form-label text-[#0D2B4E] block">2. Choose Question Types &amp; Counts</span>
                        <span class="text-[11px] font-bold text-[#4A688A]">Set how many questions AI should generate for each type</span>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-[#EBF5FF] border-2 border-[#93C5FD] text-xs font-black text-[#0066D6]">
                        Total: <span id="aiRequestedTotalCount">5</span> question(s)
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-3" id="aiTypeCountersGrid"></div>

                <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-dashed border-[#C7E0FF]">
                    <div class="flex items-center gap-2">
                        <span class="form-label">Difficulty:</span>
                        <select id="aiDifficultySelect" class="form-control py-1.5 px-3 text-xs" style="width: auto;">
                            <option value="mixed">🎯 Mixed</option>
                            <option value="easy">🌱 Easy</option>
                            <option value="medium">⚡ Medium</option>
                            <option value="hard">🚀 Hard</option>
                        </select>
                    </div>
                    <button type="button" id="btnRunAiGenerate" onclick="runAiGeneration()" class="btn-submit py-2.5 px-5 text-xs">
                        ⚡ Generate Questions
                    </button>
                </div>

                <div id="aiErrorBox" class="error-banner mt-3" style="display: none;"></div>
            </div>

            {{-- 3. Generated Questions Preview (Identical card formatting to Question Bank) --}}
            <div id="aiResultsSection" style="display: none;">
                <div class="flex items-center justify-between gap-2 mb-2.5">
                    <span class="font-display text-base font-bold text-[#0D2B4E]">
                        ✨ Generated Questions Preview (<span id="aiGeneratedResultCount">0</span>)
                    </span>
                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border-2 border-[#93C5FD] text-xs font-black text-[#0066D6] cursor-pointer">
                        <input type="checkbox" id="aiSelectAllCheckbox" checked onchange="toggleAllAiGenerated(this.checked)" class="w-4 h-4 accent-[#0066D6]">
                        <span>Select All</span>
                    </label>
                </div>

                <div id="aiGeneratedList" class="space-y-2.5"></div>
            </div>
        </div>

        <div class="p-4 bg-white border-t-2 border-[#C7E0FF] flex items-center justify-between gap-3">
            <div class="text-sm font-black text-[#0D2B4E]">
                <span id="aiSelectedCount" class="text-[#0066D6]">0</span> question(s) ready to paste
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeAiGenerator()" class="px-3.5 py-2.5 rounded-xl font-black text-xs text-[#4A688A] bg-[#F5FAFF] border-2 border-[#C7E0FF] cursor-pointer">
                    Close
                </button>
                <button type="button" id="btnPasteAiQuestions" onclick="pasteSelectedAiQuestions()" class="btn-submit btn-publish-green py-2.5 px-5 text-xs" disabled>
                    📋 Paste Selected to Questionnaire
                </button>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================================
     PER-QUIZ SEARCHABLE QUESTION BANK DRAWER
     ========================================================================= --}}
<div id="questionBankOverlay" class="bank-overlay" onclick="if(event.target===this) closeQuestionBank()">
    <div class="bank-drawer">
        <div class="bb-spectrum-strip"></div>

        <div class="p-5 bg-white border-b-2 border-[#C7E0FF]">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div>
                    <div class="text-[11px] font-black uppercase tracking-wider text-[#FF8400]">🏦 Published Quizzes Library</div>
                    <h2 class="font-display text-2xl font-bold text-[#0D2B4E]">Teacher Question Bank</h2>
                </div>
                <button type="button" onclick="closeQuestionBank()" class="w-10 h-10 rounded-2xl bg-[#F5FAFF] border-2 border-[#C7E0FF] font-black text-lg text-[#0D2B4E] hover:bg-[#FFF7ED] hover:border-[#FF8400] cursor-pointer">
                    ✕
                </button>
            </div>

            <input type="text" id="bankSearchInput" oninput="renderQuestionBank()"
                   placeholder="🔍 Search by question text, keyword, or published quiz title..."
                   class="form-control mb-3">

            <div id="bankTypeFilters" class="flex flex-wrap gap-1.5">
                <button type="button" class="type-pill-btn active" onclick="setBankFilter('all', this)">All Types</button>
                <button type="button" class="type-pill-btn" onclick="setBankFilter('multiple_choice', this)">🔘 Multiple Choice</button>
                <button type="button" class="type-pill-btn" onclick="setBankFilter('multiple_select', this)">☑️ Multi-Select</button>
                <button type="button" class="type-pill-btn" onclick="setBankFilter('true_false', this)">⚖️ True/False</button>
                <button type="button" class="type-pill-btn" onclick="setBankFilter('matching', this)">🔗 Matching</button>
                <button type="button" class="type-pill-btn" onclick="setBankFilter('fill_blank', this)">✏️ Fill-Blank</button>
                <button type="button" class="type-pill-btn" onclick="setBankFilter('short_answer', this)">💬 Short Answer</button>
                <button type="button" class="type-pill-btn" onclick="setBankFilter('essay', this)">📝 Essay</button>
            </div>
        </div>

        <div id="bankQuizzesList" class="flex-1 overflow-y-auto p-5 space-y-4"></div>

        <div class="p-4 bg-white border-t-2 border-[#C7E0FF] flex items-center justify-between gap-3">
            <div class="text-sm font-black text-[#0D2B4E]">
                <span id="bankSelectedCount" class="text-[#0066D6]">0</span> question(s) selected
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="clearBankSelection()" class="px-3.5 py-2.5 rounded-xl font-black text-xs text-[#4A688A] bg-[#F5FAFF] border-2 border-[#C7E0FF] cursor-pointer">
                    Clear
                </button>
                <button type="button" onclick="importSelectedFromBank()" class="btn-submit py-2.5 px-5 text-xs">
                    ➕ Extract &amp; Add Selected
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const QUESTION_BANK = @json($questionBank ?? []);
    const CLASS_MODULES = @json($classModules ?? []);
    const PRESELECTED_MODULE_ID = @json(isset($preselectedModule) && $preselectedModule ? $preselectedModule->id : null);
    const INITIAL_QUESTIONS = @json($generatedQuestions ?? []);
    const GENERATE_URL = @json(route('quiz.generate'));
    const STORE_URL = @json(route('quiz.store'));
    const CSRF_TOKEN = @json(csrf_token());

    const USER_STORAGE_PREFIX = 'bb_quiz_draft_u' + @json(Auth::id() ?? 'guest');
    const LOCKED_EDIT_QUIZ_ID = @json($isEditing ? $editingQuiz->id : null);
    const LOCKED_EDIT_UPDATE_URL = @json($isEditing ? route('quiz.update', $editingQuiz) : null);

    let activeQuizId = LOCKED_EDIT_QUIZ_ID;
    let activeUpdateUrl = LOCKED_EDIT_UPDATE_URL;
    let activeDraftToken = @json($isEditing ? ($editingQuiz->config['draft_token'] ?? '') : '');

    let isExplicitSubmit = false;
    let isDirty = false;
    let isAutoSaving = false;
    let exitKeepaliveSent = false;
    let autoSaveDebounceTimer = null;
    let lastServerSyncMs = 0;

    const selectedBankIds = new Set();
    let activeBankFilter = 'all';

    // AI Generator Drawer State
    let aiSourceMode = PRESELECTED_MODULE_ID ? 'class' : 'computer';
    let selectedClassModuleId = PRESELECTED_MODULE_ID;
    let aiGeneratedPool = [];
    const selectedAiIndices = new Set();
    const aiTypeCounts = {
        multiple_choice: 5,
        multiple_select: 0,
        true_false: 0,
        matching: 0,
        fill_blank: 0,
        short_answer: 0,
        essay: 0,
    };

    const TYPE_CONFIG = {
        multiple_choice: { label: 'Multiple Choice', icon: '🔘', bg: '#EBF5FF', color: '#0066D6' },
        multiple_select: { label: 'Multiple Select', icon: '☑️', bg: '#ECFEFF', color: '#0E7490' },
        true_false:      { label: 'True / False',    icon: '⚖️', bg: '#ECFCCB', color: '#3E8708' },
        matching:        { label: 'Matching',        icon: '🔗', bg: '#FFF7ED', color: '#C2410C' },
        fill_blank:      { label: 'Fill-in-the-Blank', icon: '✏️', bg: '#FEF9C3', color: '#854D0E' },
        short_answer:    { label: 'Short Answer',    icon: '💬', bg: '#F3E8FF', color: '#6B21A8' },
        essay:           { label: 'Essay',           icon: '📝', bg: '#FCE7F3', color: '#9D174D' },
    };

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function generateDraftToken() {
        return 'bb_draft_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);
    }

    function ensureDraftToken() {
        if (!activeDraftToken) {
            activeDraftToken = generateDraftToken();
        }
        const tokenInput = document.getElementById('draftTokenInput');
        if (tokenInput) {
            tokenInput.value = activeDraftToken;
        }
        return activeDraftToken;
    }

    function getDraftStorageKey(quizId = activeQuizId) {
        return quizId ? `${USER_STORAGE_PREFIX}_edit_${quizId}` : `${USER_STORAGE_PREFIX}_new`;
    }

    function setAutoSaveStatus(state, text) {
        const badge = document.getElementById('autoSaveStatusBadge');
        const label = document.getElementById('autoSaveStatusText');
        if (!badge || !label) return;
        badge.className = 'autosave-badge ' + state;
        label.textContent = text;
    }

    /* =========================================================================
       SERIALIZE / RESTORE / AUTO-SAVE DRAFT STATE
       ========================================================================= */
    function serializeQuestionCard(card) {
        const type = card.dataset.type || 'multiple_choice';
        const rawId = card.querySelector('.q-id-field')?.value || '';
        const prompt = card.querySelector('.q-text-field')?.value || '';
        const difficulty = card.querySelector('.q-diff-field')?.value || 'easy';
        const points = parseInt(card.querySelector('.q-points-field')?.value, 10) || 100;
        const timeLimit = parseInt(card.querySelector('.q-time-field')?.value, 10) || 30;

        const base = {
            id: rawId !== '' ? parseInt(rawId, 10) : null,
            type,
            prompt,
            difficulty,
            points,
            time_limit: timeLimit,
            options: [],
            correct_answer: '',
            correct_answers: [],
            matching_pairs: [],
        };

        if (type === 'multiple_choice' || type === 'multiple_select') {
            const rows = Array.from(card.querySelectorAll('.q-opt-row'));
            const options = [];
            const correctAnswers = [];

            rows.forEach(row => {
                const text = row.querySelector('.q-opt-input')?.value ?? '';
                const checked = row.querySelector('.q-opt-correct')?.checked ?? false;
                options.push(text);
                if (checked && text.trim() !== '') {
                    correctAnswers.push(text.trim());
                }
            });

            base.options = options;
            base.correct_answers = correctAnswers;
            base.correct_answer = correctAnswers[0] || '';
        } else if (type === 'true_false') {
            const checkedRadio = card.querySelector('.q-tf-radio:checked');
            const val = checkedRadio ? checkedRadio.value : 'True';
            base.options = ['True', 'False'];
            base.correct_answer = val;
            base.correct_answers = [val];
        } else if (type === 'matching') {
            base.matching_pairs = Array.from(card.querySelectorAll('.q-pair-row')).map(row => ({
                left: row.querySelector('.q-pair-left')?.value ?? '',
                right: row.querySelector('.q-pair-right')?.value ?? '',
            }));
        } else if (type === 'fill_blank') {
            base.correct_answers = Array.from(card.querySelectorAll('.q-blank-field')).map(inp => inp.value ?? '');
        } else if (type === 'short_answer') {
            base.correct_answer = card.querySelector('.q-short-field')?.value ?? '';
        } else if (type === 'essay') {
            base.correct_answer = card.querySelector('.q-essay-field')?.value ?? '';
        }

        return base;
    }

    function captureCurrentDraftSnapshot(syncedAt = 0) {
        const cards = Array.from(document.querySelectorAll('#questionsContainer .question-card'));
        return {
            quizId: activeQuizId,
            updateUrl: activeUpdateUrl,
            draftToken: ensureDraftToken(),
            classId: document.getElementById('manualClassId')?.value || '',
            quizTitle: document.getElementById('quizTitleInput')?.value || '',
            subject: document.getElementById('quizSubjectInput')?.value || 'General',
            maxAttempts: document.getElementById('quizMaxAttemptsInput')?.value || '1',
            publishedAt: document.getElementById('quizPublishedAtInput')?.value || '',
            deadlineAt: document.getElementById('quizDeadlineAtInput')?.value || '',
            questions: cards.map(serializeQuestionCard),
            updatedAt: Date.now(),
            syncedAt: syncedAt,
        };
    }

    function persistLocalSnapshot(syncedAt = 0) {
        if (isExplicitSubmit) return;
        try {
            const snapshot = captureCurrentDraftSnapshot(syncedAt);
            localStorage.setItem(getDraftStorageKey(activeQuizId), JSON.stringify(snapshot));
        } catch (e) {
            // Ignore storage quota errors
        }
    }

    function clearLocalDraft() {
        try {
            localStorage.removeItem(getDraftStorageKey(activeQuizId));
            localStorage.removeItem(`${USER_STORAGE_PREFIX}_new`);
        } catch (e) {
            // Ignore storage errors
        }
    }

    function switchFormToEditMode(quizId, updateUrl) {
        if (!quizId || !updateUrl) return;

        // If this page was opened specifically to edit a locked quiz ID, never switch to another ID
        if (LOCKED_EDIT_QUIZ_ID && Number(quizId) !== Number(LOCKED_EDIT_QUIZ_ID)) {
            return;
        }

        const wasNew = !activeQuizId;
        activeQuizId = quizId;
        activeUpdateUrl = updateUrl;

        const quizIdInput = document.getElementById('quizIdInput');
        if (quizIdInput) {
            quizIdInput.value = quizId;
        }

        const form = document.getElementById('manualForm');
        if (form) {
            form.action = updateUrl;
        }

        const spoofContainer = document.getElementById('spoofMethodContainer');
        if (spoofContainer && !spoofContainer.querySelector('input[name="_method"]')) {
            spoofContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        }

        if (wasNew) {
            try {
                localStorage.removeItem(`${USER_STORAGE_PREFIX}_new`);
            } catch (e) {}

            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', `/quiz/${quizId}/edit`);
            }
        }
    }

    function buildAutoSaveFormData() {
        reindexAllCards();
        ensureDraftToken();

        const form = document.getElementById('manualForm');
        const formData = new FormData(form);

        formData.set('auto_save', '1');
        formData.set('save_action', 'draft');
        formData.set('draft_token', activeDraftToken);

        if (activeQuizId) {
            formData.set('quiz_id', String(activeQuizId));
        }

        if (activeUpdateUrl) {
            formData.set('_method', 'PUT');
        } else {
            formData.delete('_method');
        }

        const maxAttempts = parseInt(formData.get('max_attempts'), 10);
        if (!maxAttempts || maxAttempts < 1) {
            formData.set('max_attempts', '1');
        }

        return formData;
    }

    function hasMeaningfulDraftContent() {
        const title = (document.getElementById('quizTitleInput')?.value || '').trim();
        if (title !== '') return true;

        const texts = Array.from(document.querySelectorAll('#questionsContainer .q-text-field'));
        return texts.some(t => (t.value || '').trim() !== '');
    }

    async function saveDraftToServer({ useKeepalive = false, force = false } = {}) {
        if (isExplicitSubmit) return false;
        if (!force && !isDirty) return false;
        if (!activeQuizId && !hasMeaningfulDraftContent()) return false;

        if (useKeepalive) {
            // Prevent duplicate requests when both visibilitychange and pagehide fire on tab close
            if (exitKeepaliveSent) return false;
            exitKeepaliveSent = true;

            try {
                const targetUrl = activeUpdateUrl || STORE_URL;
                const formData = buildAutoSaveFormData();
                persistLocalSnapshot(0);

                fetch(targetUrl, {
                    method: 'POST',
                    body: formData,
                    keepalive: true,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                return true;
            } catch (e) {
                return false;
            }
        }

        if (isAutoSaving) {
            return false;
        }

        isAutoSaving = true;
        setAutoSaveStatus('saving', '⏳ Saving draft...');

        try {
            const targetUrl = activeUpdateUrl || STORE_URL;
            const formData = buildAutoSaveFormData();

            const response = await fetch(targetUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Auto-save failed');
            }

            if (data.skipped) {
                isAutoSaving = false;
                return false;
            }

            if (data.quiz_id && data.update_url) {
                switchFormToEditMode(data.quiz_id, data.update_url);
            }

            if (Array.isArray(data.question_ids)) {
                const cards = document.querySelectorAll('#questionsContainer .question-card');
                data.question_ids.forEach((qId, idx) => {
                    if (cards[idx]) {
                        const idInput = cards[idx].querySelector('.q-id-field');
                        if (idInput) idInput.value = qId;
                    }
                });
                reindexAllCards();
            }

            isDirty = false;
            exitKeepaliveSent = false;
            lastServerSyncMs = Date.now();
            persistLocalSnapshot(lastServerSyncMs);
            setAutoSaveStatus('saved', `💾 Draft saved • ${data.saved_at || 'just now'}`);
            return true;
        } catch (err) {
            setAutoSaveStatus('error', '⚠️ Offline backup saved locally');
            return false;
        } finally {
            isAutoSaving = false;
        }
    }

    function markDraftDirty(immediateSync = false) {
        if (isExplicitSubmit) return;
        isDirty = true;
        exitKeepaliveSent = false;
        persistLocalSnapshot(0);
        setAutoSaveStatus('dirty', '📝 Unsaved changes...');

        clearTimeout(autoSaveDebounceTimer);
        if (immediateSync) {
            saveDraftToServer({ force: true });
        } else {
            autoSaveDebounceTimer = setTimeout(() => {
                saveDraftToServer();
            }, 900);
        }
    }

    async function handleExitToQuizzes(event) {
        if (isExplicitSubmit) return;
        const link = event.currentTarget;
        const destination = link.href;

        if (isDirty || (!activeQuizId && hasMeaningfulDraftContent())) {
            event.preventDefault();
            clearTimeout(autoSaveDebounceTimer);
            link.innerHTML = '💾 Saving Draft...';
            link.style.pointerEvents = 'none';

            await saveDraftToServer({ force: true });
            isExplicitSubmit = true;
            window.location.href = destination;
        }
    }

    function restoreDraftSnapshotIfAvailable() {
        try {
            const raw = localStorage.getItem(getDraftStorageKey(activeQuizId));
            if (!raw) return false;

            const saved = JSON.parse(raw);
            if (!saved || !Array.isArray(saved.questions) || saved.questions.length === 0) {
                return false;
            }

            // If we are on /quiz/{id}/edit, reject any snapshot that does not belong to this quiz ID
            if (LOCKED_EDIT_QUIZ_ID && saved.quizId && Number(saved.quizId) !== Number(LOCKED_EDIT_QUIZ_ID)) {
                return false;
            }

            if (saved.draftToken && !activeDraftToken) {
                activeDraftToken = saved.draftToken;
                ensureDraftToken();
            }

            // Only restore if there were edits made after the last confirmed server sync
            const hasUnsyncedEdits = !saved.syncedAt || saved.updatedAt > saved.syncedAt;
            if (!hasUnsyncedEdits && activeQuizId) {
                return false;
            }

            if (!LOCKED_EDIT_QUIZ_ID && saved.quizId && saved.updateUrl) {
                switchFormToEditMode(saved.quizId, saved.updateUrl);
            }

            if (saved.classId) {
                const classSelect = document.getElementById('classSelector');
                if (classSelect) classSelect.value = saved.classId;
                const manualClassInput = document.getElementById('manualClassId');
                if (manualClassInput) manualClassInput.value = saved.classId;
            }

            if (typeof saved.quizTitle === 'string') {
                document.getElementById('quizTitleInput').value = saved.quizTitle;
            }
            if (typeof saved.subject === 'string') {
                document.getElementById('quizSubjectInput').value = saved.subject;
            }
            if (saved.maxAttempts) {
                document.getElementById('quizMaxAttemptsInput').value = saved.maxAttempts;
            }
            if (saved.publishedAt) {
                document.getElementById('quizPublishedAtInput').value = saved.publishedAt;
            }
            if (saved.deadlineAt) {
                document.getElementById('quizDeadlineAtInput').value = saved.deadlineAt;
            }

            const container = document.getElementById('questionsContainer');
            container.innerHTML = '';
            saved.questions.forEach(q => addQuestionCard(q, true));

            const notice = document.getElementById('restoredDraftNotice');
            if (notice) notice.style.display = 'block';

            return true;
        } catch (e) {
            return false;
        }
    }

    function updateSelectedClass(classId) {
        const manualClassInput = document.getElementById('manualClassId');
        if (manualClassInput) manualClassInput.value = classId;
        renderAiClassModules();
        markDraftDirty(true);
    }

    function submitQuizForm(actionType) {
        const form = document.getElementById('manualForm');
        document.getElementById('saveActionInput').value = actionType;
        reindexAllCards();

        if (form.reportValidity()) {
            isExplicitSubmit = true;
            clearTimeout(autoSaveDebounceTimer);
            clearLocalDraft();
            form.submit();
        }
    }

    /* =========================================================================
       TIME UNIT CONVERSION HELPER (SECONDS / MINUTES / HOURS)
       ========================================================================= */
    function resolveInitialTimeParts(rawSeconds) {
        const sec = Math.max(5, parseInt(rawSeconds, 10) || 30);
        if (sec % 3600 === 0) {
            return { value: sec / 3600, unit: 'hours', totalSeconds: sec };
        }
        if (sec % 60 === 0 && sec >= 60) {
            return { value: sec / 60, unit: 'minutes', totalSeconds: sec };
        }
        return { value: sec, unit: 'seconds', totalSeconds: sec };
    }

    function syncQuestionTimeSeconds(el) {
        const card = el.closest('.question-card');
        if (!card) return;
        const valInput = card.querySelector('.q-time-val-field');
        const unitSelect = card.querySelector('.q-time-unit-field');
        const hiddenSec = card.querySelector('.q-time-field');

        const rawVal = Math.max(1, parseInt(valInput.value, 10) || 1);
        const unit = unitSelect.value;
        const multiplier = unit === 'hours' ? 3600 : (unit === 'minutes' ? 60 : 1);
        hiddenSec.value = Math.max(5, rawVal * multiplier);
        markDraftDirty();
    }

    /* =========================================================================
       QUESTIONNAIRE BUILDER CARDS
       ========================================================================= */
    function addQuestionCard(preset = {}, skipDirty = false) {
        const container = document.getElementById('questionsContainer');
        const type = TYPE_CONFIG[preset.type] ? preset.type : 'multiple_choice';
        const prompt = preset.prompt || preset.question || '';
        const difficulty = preset.difficulty || 'easy';
        const points = preset.points || 100;
        const questionId = preset.id || '';
        const timeParts = resolveInitialTimeParts(preset.time_limit || preset.time_limit_seconds || 30);

        const card = document.createElement('div');
        card.className = 'question-card';
        card.dataset.type = type;

        card.innerHTML = `
            <input type="hidden" class="q-id-field" value="${escapeHtml(questionId)}">

            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom: 14px;">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="qcard-number px-3 py-1 rounded-full bg-[#EBF5FF] border-2 border-[#93C5FD] font-display font-bold text-xs text-[#0066D6]">Question 1</span>
                    ${preset.fromQuiz ? `<span class="px-2.5 py-0.5 rounded-full bg-[#FFF7ED] border border-[#FF8400] text-[#C2410C] text-[11px] font-black">🏦 From: ${escapeHtml(preset.fromQuiz)}</span>` : ''}
                    ${preset.fromAi ? `<span class="px-2.5 py-0.5 rounded-full bg-[#EBF5FF] border border-[#1DC4DF] text-[#0066D6] text-[11px] font-black">🤖 AI Generated</span>` : ''}
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="moveQuestionCard(this, -1)" class="px-2.5 py-1 rounded-xl bg-[#F5FAFF] border-2 border-[#C7E0FF] font-black text-xs text-[#0D2B4E] hover:border-[#0066D6] cursor-pointer" title="Move Up">↑</button>
                    <button type="button" onclick="moveQuestionCard(this, 1)" class="px-2.5 py-1 rounded-xl bg-[#F5FAFF] border-2 border-[#C7E0FF] font-black text-xs text-[#0D2B4E] hover:border-[#0066D6] cursor-pointer" title="Move Down">↓</button>
                    <button type="button" class="btn-remove-question" onclick="removeQuestionCard(this)">🗑️ Remove</button>
                </div>
            </div>

            <div class="mb-3.5">
                <label class="form-label mb-1.5 block">Question Type</label>
                <div class="flex flex-wrap gap-1.5 qcard-type-bar">
                    ${Object.entries(TYPE_CONFIG).map(([key, meta]) => `
                        <button type="button" class="type-pill-btn ${key === type ? 'active' : ''}" onclick="switchCardType(this, '${key}')">
                            ${meta.icon} ${meta.label}
                        </button>
                    `).join('')}
                </div>
                <input type="hidden" class="q-type-field" value="${type}">
            </div>
            <br>
            <div class="form-group">
                <div class="flex items-center justify-between">
                    <label class="form-label">Question Text *</label>
                    <button type="button" onclick="insertBlankIntoPrompt(this)"
                            class="btn-insert-blank ${type === 'fill_blank' ? '' : 'hidden'} px-2.5 py-1 rounded-xl bg-[#FEF9C3] border-2 border-[#FACC15] text-[#854D0E] text-[11px] font-black cursor-pointer">
                        + Insert [blank] (___)
                    </button>
                </div>
                <textarea rows="2" class="form-control q-text-field" oninput="syncFillBlankSlots(this)" placeholder="Type your question prompt..." required>${escapeHtml(prompt)}</textarea>
            </div>

            <div class="qcard-dynamic-body mb-4"></div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t-2 border-dashed border-[#C7E0FF]">
                <div>
                    <label class="form-label">Difficulty</label>
                    <select class="form-control q-diff-field">
                        <option value="easy" ${difficulty === 'easy' ? 'selected' : ''}>🌱 Easy</option>
                        <option value="medium" ${difficulty === 'medium' ? 'selected' : ''}>⚡ Medium</option>
                        <option value="hard" ${difficulty === 'hard' ? 'selected' : ''}>🚀 Hard</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">XP Stars ⭐</label>
                    <input type="number" class="form-control q-points-field" value="${points}" min="10" max="1000" step="10" required>
                </div>
                <div>
                    <label class="form-label">Time Limit ⏱️</label>
                    <div class="flex items-center gap-1.5">
                        <input type="number"
                               class="form-control q-time-val-field"
                               value="${timeParts.value}"
                               min="1"
                               max="999"
                               oninput="syncQuestionTimeSeconds(this)"
                               style="flex:1; min-width:64px;"
                               required>
                        <select class="form-control q-time-unit-field"
                                onchange="syncQuestionTimeSeconds(this)"
                                style="width:auto; min-width:105px; padding-left:10px; padding-right:10px;">
                            <option value="seconds" ${timeParts.unit === 'seconds' ? 'selected' : ''}>Seconds</option>
                            <option value="minutes" ${timeParts.unit === 'minutes' ? 'selected' : ''}>Minutes</option>
                            <option value="hours" ${timeParts.unit === 'hours' ? 'selected' : ''}>Hours</option>
                        </select>
                        <input type="hidden" class="q-time-field" value="${timeParts.totalSeconds}">
                    </div>
                </div>
            </div>
        `;

        container.appendChild(card);
        renderCardDynamicBody(card, type, preset);
        reindexAllCards();

        if (!skipDirty) {
            markDraftDirty();
        }
    }

    function switchCardType(btn, newType) {
        const card = btn.closest('.question-card');
        card.dataset.type = newType;
        card.querySelector('.q-type-field').value = newType;
        card.querySelectorAll('.qcard-type-bar .type-pill-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const blankBtn = card.querySelector('.btn-insert-blank');
        if (blankBtn) blankBtn.classList.toggle('hidden', newType !== 'fill_blank');

        renderCardDynamicBody(card, newType, {});
        reindexAllCards();
        markDraftDirty(true);
    }

    function renderCardDynamicBody(card, type, preset = {}) {
        const body = card.querySelector('.qcard-dynamic-body');

        if (type === 'multiple_choice' || type === 'multiple_select') {
            const isMulti = type === 'multiple_select';
            const opts = (preset.options && preset.options.length) ? preset.options : ['', '', '', ''];
            const correctList = Array.isArray(preset.correct_answers) && preset.correct_answers.length
                ? preset.correct_answers.map(v => String(v).trim())
                : (preset.correct_answer ? [String(preset.correct_answer).trim()] : []);

            body.innerHTML = `
                <label class="form-label mb-1.5 block">
                    ${isMulti ? '☑️ Check ALL Correct Options:' : '🔘 Select the ONE Correct Option:'}
                </label>
                <div class="space-y-2 q-opts-list">
                    ${opts.map((optVal, i) => {
                        const isChecked = correctList.includes(String(optVal).trim()) && String(optVal).trim() !== ''
                            || (correctList.length === 0 && !isMulti && i === 0);
                        return `
                            <div class="flex items-center gap-2 q-opt-row">
                                <input type="${isMulti ? 'checkbox' : 'radio'}" class="q-opt-correct w-5 h-5 accent-[#68CC18] cursor-pointer shrink-0" value="${i}" ${isChecked ? 'checked' : ''}>
                                <input type="text" class="form-control q-opt-input bg-[#F5FAFF]" value="${escapeHtml(optVal)}" placeholder="Option ${i + 1}" required>
                                <button type="button" onclick="removeOptRow(this)" class="px-2.5 py-2 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] font-black text-xs cursor-pointer">✕</button>
                            </div>
                        `;
                    }).join('')}
                </div>
                <br>
                <button type="button" onclick="addOptRow(this, ${isMulti})" class="mt-2.5 px-3.5 py-1.5 rounded-xl bg-[#EBF5FF] border-2 border-[#93C5FD] text-[#0066D6] font-black text-xs cursor-pointer">
                    + Add Choice
                </button>
            `;
        } else if (type === 'true_false') {
            const rawTf = String(preset.correct_answer || (preset.correct_answers && preset.correct_answers[0]) || 'True');
            const isFalse = rawTf.toLowerCase() === 'false';
            body.innerHTML = `
                <label class="form-label mb-1.5 block">⚖️ Select Correct Answer:</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center justify-center gap-2 p-3.5 rounded-2xl border-2.5 border-[#84CC16] bg-[#F4FCEB] text-[#365314] font-black text-sm cursor-pointer shadow-xs">
                        <input type="radio" class="q-tf-radio w-5 h-5 accent-[#68CC18]" value="True" ${!isFalse ? 'checked' : ''}>
                        <span>✅ TRUE</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 p-3.5 rounded-2xl border-2.5 border-[#FB923C] bg-[#FFF7ED] text-[#9A3412] font-black text-sm cursor-pointer shadow-xs">
                        <input type="radio" class="q-tf-radio w-5 h-5 accent-[#FF8400]" value="False" ${isFalse ? 'checked' : ''}>
                        <span>❌ FALSE</span>
                    </label>
                </div>
            `;
        } else if (type === 'matching') {
            const pairs = (preset.matching_pairs && preset.matching_pairs.length)
                ? preset.matching_pairs
                : [{ left: '', right: '' }, { left: '', right: '' }, { left: '', right: '' }];
            body.innerHTML = `
                <label class="form-label mb-1.5 block">🔗 Matching Pairs (Left Item ↔ Right Match):</label>
                <div class="space-y-2 q-pairs-list">
                    ${pairs.map((p, i) => `
                        <div class="grid grid-cols-12 gap-2 items-center q-pair-row">
                            <input type="text" class="col-span-5 form-control q-pair-left bg-[#F5FAFF]" value="${escapeHtml(p.left)}" placeholder="Left item ${i + 1}" required>
                            <div class="col-span-1 text-center font-black text-[#1DC4DF]">↔</div>
                            <input type="text" class="col-span-5 form-control q-pair-right bg-[#F5FAFF]" value="${escapeHtml(p.right)}" placeholder="Right match ${i + 1}" required>
                            <button type="button" onclick="removePairRow(this)" class="col-span-1 py-2 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] font-black text-xs cursor-pointer">✕</button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" onclick="addPairRow(this)" class="mt-2.5 px-3.5 py-1.5 rounded-xl bg-[#FFF7ED] border-2 border-[#FF8400] text-[#C2410C] font-black text-xs cursor-pointer">
                    + Add Pair
                </button>
            `;
        } else if (type === 'fill_blank') {
            const blanks = (preset.correct_answers && preset.correct_answers.length) ? preset.correct_answers : [''];
            body.innerHTML = `
                <div class="p-4 rounded-2xl bg-[#FEFCE8] border-2.5 border-[#FDE047]">
                    <div class="text-[11px] font-black text-[#854D0E] mb-2.5">
                        ✏️ Put <code class="bg-white px-1.5 py-0.5 rounded border border-[#FDE047]">___</code> in Question Text for each blank. Separate synonyms with <code class="bg-white px-1.5 py-0.5 rounded border border-[#FDE047]">|</code>.
                    </div>
                    <div class="space-y-2 q-blanks-list">
                        ${blanks.map((val, idx) => `
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-xl bg-[#FACC15] text-[#713F12] font-black text-xs shrink-0">Blank #${idx + 1}</span>
                                <input type="text" class="form-control q-blank-field bg-white" value="${escapeHtml(val)}" placeholder="Answer for blank #${idx + 1}" required>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        } else if (type === 'short_answer') {
            const ansVal = preset.correct_answer || (preset.correct_answers ? preset.correct_answers.join(', ') : '');
            body.innerHTML = `
                <div class="p-4 rounded-2xl bg-[#FAF5FF] border-2.5 border-[#D8B4FE]">
                    <label class="form-label text-[#6B21A8] mb-1.5 block">💬 Accepted Short Answers (Comma-separated, or leave blank for open teacher check)</label>
                    <input type="text" class="form-control q-short-field bg-white" value="${escapeHtml(ansVal)}" placeholder="e.g. Photosynthesis, plant food">
                </div>
            `;
        } else if (type === 'essay') {
            const rubricVal = preset.correct_answer || '';
            body.innerHTML = `
                <div class="p-4 rounded-2xl bg-[#FDF2F8] border-2.5 border-[#F9A8D4]">
                    <label class="form-label text-[#9D174D] mb-1.5 block">📝 Essay Writing Prompt Guide / Rubric Notes (Optional)</label>
                    <input type="text" class="form-control q-essay-field bg-white" value="${escapeHtml(rubricVal)}" placeholder="e.g. Look for 3 complete sentences explaining the water cycle...">
                </div>
            `;
        }
    }

    function insertBlankIntoPrompt(btn) {
        const card = btn.closest('.question-card');
        const textarea = card.querySelector('.q-text-field');
        const pos = textarea.selectionStart ?? textarea.value.length;
        textarea.value = textarea.value.slice(0, pos) + ' ___ ' + textarea.value.slice(pos);
        textarea.focus();
        syncFillBlankSlots(textarea);
    }

    function syncFillBlankSlots(textarea) {
        const card = textarea.closest('.question-card');
        if (card.dataset.type !== 'fill_blank') return;
        const matches = textarea.value.match(/_{3,}|\[blank\]/gi) || [];
        const count = Math.max(1, matches.length);
        const list = card.querySelector('.q-blanks-list');
        const existing = Array.from(list.querySelectorAll('.q-blank-field')).map(i => i.value);

        list.innerHTML = Array.from({ length: count }).map((_, idx) => `
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-xl bg-[#FACC15] text-[#713F12] font-black text-xs shrink-0">Blank #${idx + 1}</span>
                <input type="text" class="form-control q-blank-field bg-white" value="${escapeHtml(existing[idx] || '')}" placeholder="Answer for blank #${idx + 1}" required>
            </div>
        `).join('');
        reindexAllCards();
    }

    function addOptRow(btn, isMulti) {
        const list = btn.closest('.qcard-dynamic-body').querySelector('.q-opts-list');
        const idx = list.children.length;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2 q-opt-row';
        row.innerHTML = `
            <input type="${isMulti ? 'checkbox' : 'radio'}" class="q-opt-correct w-5 h-5 accent-[#68CC18] cursor-pointer shrink-0" value="${idx}">
            <input type="text" class="form-control q-opt-input bg-[#F5FAFF]" placeholder="Option ${idx + 1}" required>
            <button type="button" onclick="removeOptRow(this)" class="px-2.5 py-2 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] font-black text-xs cursor-pointer">✕</button>
        `;
        list.appendChild(row);
        reindexAllCards();
        markDraftDirty();
    }

    function removeOptRow(btn) {
        const list = btn.closest('.q-opts-list');
        if (list.children.length <= 2) return;
        btn.closest('.q-opt-row').remove();
        reindexAllCards();
        markDraftDirty(true);
    }

    function addPairRow(btn) {
        const list = btn.closest('.qcard-dynamic-body').querySelector('.q-pairs-list');
        const idx = list.children.length;
        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-2 items-center q-pair-row';
        row.innerHTML = `
            <input type="text" class="col-span-5 form-control q-pair-left bg-[#F5FAFF]" placeholder="Left item ${idx + 1}" required>
            <div class="col-span-1 text-center font-black text-[#1DC4DF]">↔</div>
            <input type="text" class="col-span-5 form-control q-pair-right bg-[#F5FAFF]" placeholder="Right match ${idx + 1}" required>
            <button type="button" onclick="removePairRow(this)" class="col-span-1 py-2 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] font-black text-xs cursor-pointer">✕</button>
        `;
        list.appendChild(row);
        reindexAllCards();
        markDraftDirty();
    }

    function removePairRow(btn) {
        const list = btn.closest('.q-pairs-list');
        if (list.children.length <= 2) return;
        btn.closest('.q-pair-row').remove();
        reindexAllCards();
        markDraftDirty(true);
    }

    function moveQuestionCard(btn, dir) {
        const card = btn.closest('.question-card');
        const container = document.getElementById('questionsContainer');
        if (dir === -1 && card.previousElementSibling) {
            container.insertBefore(card, card.previousElementSibling);
        } else if (dir === 1 && card.nextElementSibling) {
            container.insertBefore(card.nextElementSibling, card);
        }
        reindexAllCards();
        markDraftDirty(true);
    }

    function removeQuestionCard(btn) {
        const container = document.getElementById('questionsContainer');
        if (container.children.length <= 1) {
            alert('Your quiz needs at least 1 question!');
            return;
        }
        btn.closest('.question-card').remove();
        reindexAllCards();
        markDraftDirty(true);
    }

    function reindexAllCards() {
        const cards = document.querySelectorAll('#questionsContainer .question-card');
        document.getElementById('totalQuestionsBadge').textContent = cards.length;

        cards.forEach((card, qIdx) => {
            const type = card.dataset.type;
            card.querySelector('.qcard-number').textContent = `Question ${qIdx + 1}`;

            const idField = card.querySelector('.q-id-field');
            if (idField) {
                idField.name = `questions[${qIdx}][id]`;
            }

            card.querySelector('.q-type-field').name = `questions[${qIdx}][type]`;
            card.querySelector('.q-text-field').name = `questions[${qIdx}][text]`;
            card.querySelector('.q-diff-field').name = `questions[${qIdx}][difficulty]`;
            card.querySelector('.q-points-field').name = `questions[${qIdx}][points]`;
            card.querySelector('.q-time-field').name = `questions[${qIdx}][time_limit]`;
            card.querySelector('.q-time-val-field').name = `questions[${qIdx}][time_value]`;
            card.querySelector('.q-time-unit-field').name = `questions[${qIdx}][time_unit]`;

            if (type === 'multiple_choice' || type === 'multiple_select') {
                card.querySelectorAll('.q-opt-row').forEach((row, oIdx) => {
                    const chk = row.querySelector('.q-opt-correct');
                    chk.value = oIdx;
                    chk.name = type === 'multiple_select'
                        ? `questions[${qIdx}][correct_indices][]`
                        : `questions[${qIdx}][correct_index]`;
                    row.querySelector('.q-opt-input').name = `questions[${qIdx}][options][${oIdx}]`;
                });
            } else if (type === 'true_false') {
                card.querySelectorAll('.q-tf-radio').forEach(r => {
                    r.name = `questions[${qIdx}][answer]`;
                });
            } else if (type === 'matching') {
                card.querySelectorAll('.q-pair-row').forEach((row, pIdx) => {
                    row.querySelector('.q-pair-left').name = `questions[${qIdx}][pairs][${pIdx}][left]`;
                    row.querySelector('.q-pair-right').name = `questions[${qIdx}][pairs][${pIdx}][right]`;
                });
            } else if (type === 'fill_blank') {
                card.querySelectorAll('.q-blank-field').forEach((inp, bIdx) => {
                    inp.name = `questions[${qIdx}][blanks][${bIdx}]`;
                });
            } else if (type === 'short_answer') {
                const inp = card.querySelector('.q-short-field');
                if (inp) inp.name = `questions[${qIdx}][answer]`;
            } else if (type === 'essay') {
                const inp = card.querySelector('.q-essay-field');
                if (inp) inp.name = `questions[${qIdx}][answer]`;
            }
        });
    }

    /* =========================================================================
       SHARED PREVIEW CARD HELPER (USED BY BOTH QUESTION BANK & AI DRAWER)
       ========================================================================= */
    function buildQuestionPreviewHtml(q) {
        if (q.type === 'matching' && Array.isArray(q.matching_pairs) && q.matching_pairs.length) {
            return `<div class="mt-1.5 flex flex-wrap gap-1.5">` +
                q.matching_pairs.map(p => `<span class="px-2 py-0.5 rounded-lg bg-[#FFF7ED] border border-[#FED7AA] text-[11px] font-bold text-[#9A3412]">${escapeHtml(p.left)} ↔ ${escapeHtml(p.right)}</span>`).join('') +
                `</div>`;
        }
        if (Array.isArray(q.options) && q.options.length) {
            const rightSet = (q.correct_answers || (q.correct_answer ? [q.correct_answer] : [])).map(v => String(v).trim());
            return `<div class="mt-1.5 flex flex-wrap gap-1.5">` +
                q.options.map(opt => {
                    const isRight = rightSet.includes(String(opt).trim());
                    return `<span class="px-2 py-0.5 rounded-lg text-[11px] font-bold ${isRight ? 'bg-[#ECFCCB] border border-[#84CC16] text-[#365314]' : 'bg-[#F8FAFC] border border-[#E2E8F0] text-[#64748B]'}">${isRight ? '✓ ' : ''}${escapeHtml(opt)}</span>`;
                }).join('') +
                `</div>`;
        }
        if (q.type === 'true_false') {
            const ans = q.correct_answer || (q.correct_answers && q.correct_answers[0]) || 'True';
            return `<div class="mt-1.5"><span class="px-2 py-0.5 rounded-lg bg-[#ECFCCB] border border-[#84CC16] text-[11px] font-bold text-[#365314]">✓ ${escapeHtml(ans)}</span></div>`;
        }
        if (q.type === 'fill_blank' && Array.isArray(q.correct_answers) && q.correct_answers.length) {
            return `<div class="mt-1.5 flex flex-wrap gap-1.5">` +
                q.correct_answers.map((b, i) => `<span class="px-2 py-0.5 rounded-lg bg-[#FEF9C3] border border-[#FDE047] text-[11px] font-bold text-[#854D0E]">Blank #${i + 1}: ${escapeHtml(b)}</span>`).join('') +
                `</div>`;
        }
        return '';
    }

    /* =========================================================================
       AI QUESTION GENERATOR DRAWER
       ========================================================================= */
    function openAiGenerator() {
        document.getElementById('aiGeneratorOverlay').classList.add('open');
        renderAiTypeCounters();
        switchAiSourceTab(aiSourceMode);
        renderAiClassModules();
    }

    function closeAiGenerator() {
        document.getElementById('aiGeneratorOverlay').classList.remove('open');
    }

    function switchAiSourceTab(mode) {
        aiSourceMode = mode;
        const compBtn = document.getElementById('aiSrcComputerBtn');
        const clsBtn = document.getElementById('aiSrcClassBtn');
        const compPane = document.getElementById('aiSourceComputerPane');
        const clsPane = document.getElementById('aiSourceClassPane');

        if (mode === 'computer') {
            compBtn.classList.add('active');
            clsBtn.classList.remove('active');
            compPane.style.display = 'block';
            clsPane.style.display = 'none';
        } else {
            clsBtn.classList.add('active');
            compBtn.classList.remove('active');
            clsPane.style.display = 'block';
            compPane.style.display = 'none';
            renderAiClassModules();
        }
    }

    function onAiComputerFileSelected(input) {
        const label = document.getElementById('aiComputerFileLabel');
        if (input.files && input.files[0]) {
            label.textContent = `✅ Selected: ${input.files[0].name}`;
        } else {
            label.textContent = 'Click to choose a lesson file from your computer';
        }
    }

    function selectClassModuleForAi(moduleId) {
        selectedClassModuleId = moduleId;
        renderAiClassModules();
    }

    function renderAiClassModules() {
        const container = document.getElementById('aiClassModulesList');
        if (!container) return;
        const keyword = (document.getElementById('aiModuleSearchInput')?.value || '').trim().toLowerCase();

        if (!CLASS_MODULES.length) {
            container.innerHTML = `
                <div class="p-4 text-center rounded-2xl bg-[#F5FAFF] border-2 border-dashed border-[#C7E0FF]">
                    <div class="text-xs font-extrabold text-[#4A688A]">No published files found in your classes yet. Switch to "From Computer" to upload a file!</div>
                </div>`;
            return;
        }

        const filtered = CLASS_MODULES.filter(m => {
            if (!keyword) return true;
            return (m.title || '').toLowerCase().includes(keyword)
                || (m.file_name || '').toLowerCase().includes(keyword)
                || (m.class_name || '').toLowerCase().includes(keyword);
        });

        if (!filtered.length) {
            container.innerHTML = `<div class="p-3 text-center text-xs font-bold text-[#4A688A]">No class files matched your search.</div>`;
            return;
        }

        container.innerHTML = filtered.map(m => {
            const isSelected = Number(selectedClassModuleId) === Number(m.id);
            return `
                <div onclick="selectClassModuleForAi(${m.id})"
                     class="flex items-center justify-between gap-3 p-3 rounded-2xl border-2 ${isSelected ? 'border-[#0066D6] bg-[#EBF5FF]' : 'border-[#E2E8F0] bg-white hover:border-[#38BDF8]'} cursor-pointer">
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-black text-[#0D2B4E] truncate">📚 ${escapeHtml(m.title)}</div>
                        <div class="text-[11px] font-bold text-[#4A688A] truncate">${escapeHtml(m.file_name || 'Attached Module')} • ${escapeHtml(m.class_name || 'Class')}</div>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl text-[11px] font-black shrink-0 ${isSelected ? 'bg-[#0066D6] text-white' : 'bg-[#F5FAFF] text-[#0066D6] border border-[#C7E0FF]'}">
                        ${isSelected ? '✓ Selected' : 'Use File'}
                    </span>
                </div>
            `;
        }).join('');
    }

    function renderAiTypeCounters() {
        const grid = document.getElementById('aiTypeCountersGrid');
        grid.innerHTML = Object.entries(TYPE_CONFIG).map(([key, meta]) => {
            const count = aiTypeCounts[key] || 0;
            return `
                <div class="flex items-center justify-between gap-2 p-2.5 rounded-2xl border-2 ${count > 0 ? 'border-[#60A5FA] bg-[#F5FAFF]' : 'border-[#E2E8F0] bg-white'}">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="px-2 py-0.5 rounded-lg text-[11px] font-black shrink-0" style="background:${meta.bg};color:${meta.color};">
                            ${meta.icon} ${meta.label}
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" class="ai-stepper-btn" onclick="stepAiTypeCount('${key}', -1)">−</button>
                        <input type="number"
                               min="0"
                               max="30"
                               value="${count}"
                               onchange="setAiTypeCount('${key}', this.value)"
                               class="w-12 text-center font-black text-xs text-[#0D2B4E] bg-white border-2 border-[#C7E0FF] rounded-xl py-1">
                        <button type="button" class="ai-stepper-btn" onclick="stepAiTypeCount('${key}', 1)">+</button>
                    </div>
                </div>
            `;
        }).join('');

        const total = Object.values(aiTypeCounts).reduce((sum, n) => sum + (parseInt(n, 10) || 0), 0);
        document.getElementById('aiRequestedTotalCount').textContent = total;
    }

    function stepAiTypeCount(typeKey, delta) {
        const next = Math.max(0, Math.min(30, (aiTypeCounts[typeKey] || 0) + delta));
        aiTypeCounts[typeKey] = next;
        renderAiTypeCounters();
    }

    function setAiTypeCount(typeKey, val) {
        aiTypeCounts[typeKey] = Math.max(0, Math.min(30, parseInt(val, 10) || 0));
        renderAiTypeCounters();
    }

    async function runAiGeneration() {
        const errBox = document.getElementById('aiErrorBox');
        errBox.style.display = 'none';

        const totalRequested = Object.values(aiTypeCounts).reduce((sum, n) => sum + (parseInt(n, 10) || 0), 0);
        if (totalRequested < 1) {
            errBox.textContent = '⚠️ Please choose at least 1 question across any question type.';
            errBox.style.display = 'block';
            return;
        }

        const fileInput = document.getElementById('aiModuleFileInput');
        if (aiSourceMode === 'computer' && (!fileInput.files || !fileInput.files[0])) {
            errBox.textContent = '⚠️ Please select a lesson file from your computer first.';
            errBox.style.display = 'block';
            return;
        }
        if (aiSourceMode === 'class' && !selectedClassModuleId) {
            errBox.textContent = '⚠️ Please select one of your published class files first.';
            errBox.style.display = 'block';
            return;
        }

        const btn = document.getElementById('btnRunAiGenerate');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Generating with Groq AI...';

        try {
            const formData = new FormData();
            formData.append('_token', CSRF_TOKEN);
            formData.append('class_id', document.getElementById('manualClassId')?.value || '');
            formData.append('quiz_title', document.getElementById('quizTitleInput')?.value || 'AI Generated Quiz');
            formData.append('subject', document.getElementById('quizSubjectInput')?.value || 'General');
            formData.append('difficulty', document.getElementById('aiDifficultySelect')?.value || 'mixed');
            formData.append('question_count', totalRequested);

            Object.entries(aiTypeCounts).forEach(([k, v]) => {
                formData.append(`type_counts[${k}]`, v);
            });

            if (aiSourceMode === 'computer' && fileInput.files[0]) {
                formData.append('module_pdf', fileInput.files[0]);
            } else if (aiSourceMode === 'class' && selectedClassModuleId) {
                formData.append('module_id', selectedClassModuleId);
            }

            const response = await fetch(GENERATE_URL, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await response.json();
            if (!response.ok || !Array.isArray(data.questions)) {
                throw new Error(data.message || 'Failed to generate questions from the selected file.');
            }

            aiGeneratedPool = data.questions;
            selectedAiIndices.clear();
            aiGeneratedPool.forEach((_, idx) => selectedAiIndices.add(idx));
            renderAiGeneratedPreview();
        } catch (err) {
            errBox.textContent = '⚠️ ' + (err.message || 'An error occurred while generating questions.');
            errBox.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }

    function toggleAiItem(idx) {
        if (selectedAiIndices.has(idx)) selectedAiIndices.delete(idx);
        else selectedAiIndices.add(idx);
        updateAiSelectionFooter();
    }

    function toggleAllAiGenerated(checked) {
        selectedAiIndices.clear();
        if (checked) {
            aiGeneratedPool.forEach((_, idx) => selectedAiIndices.add(idx));
        }
        renderAiGeneratedPreview();
    }

    function updateAiSelectionFooter() {
        const count = selectedAiIndices.size;
        document.getElementById('aiSelectedCount').textContent = count;
        document.getElementById('btnPasteAiQuestions').disabled = count === 0;
        const selectAllChk = document.getElementById('aiSelectAllCheckbox');
        if (selectAllChk) {
            selectAllChk.checked = aiGeneratedPool.length > 0 && count === aiGeneratedPool.length;
        }
    }

    function renderAiGeneratedPreview() {
        const section = document.getElementById('aiResultsSection');
        const list = document.getElementById('aiGeneratedList');
        section.style.display = 'block';
        document.getElementById('aiGeneratedResultCount').textContent = aiGeneratedPool.length;

        list.innerHTML = aiGeneratedPool.map((q, idx) => {
            const meta = TYPE_CONFIG[q.type] || TYPE_CONFIG.multiple_choice;
            const isChecked = selectedAiIndices.has(idx);
            const preview = buildQuestionPreviewHtml(q);

            return `
                <label class="flex items-start gap-3 p-3 rounded-2xl border-2 ${isChecked ? 'border-[#0066D6] bg-[#EBF5FF]' : 'border-[#E2E8F0] bg-white hover:border-[#38BDF8]'} cursor-pointer">
                    <input type="checkbox" ${isChecked ? 'checked' : ''} onchange="toggleAiItem(${idx})" class="mt-1 w-4 h-4 accent-[#0066D6] shrink-0">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded-lg text-[11px] font-black" style="background:${meta.bg};color:${meta.color};">
                                ${meta.icon} ${meta.label}
                            </span>
                            <span class="text-[11px] font-black text-[#4A688A]">⭐ ${q.points || 100} XP</span>
                        </div>
                        <div class="text-sm font-extrabold text-[#0D2B4E]">${escapeHtml(q.prompt || q.question)}</div>
                        ${preview}
                    </div>
                </label>
            `;
        }).join('');

        updateAiSelectionFooter();
    }

    function pasteSelectedAiQuestions() {
        if (!selectedAiIndices.size) return;

        const container = document.getElementById('questionsContainer');
        if (container.children.length === 1) {
            const firstText = container.querySelector('.q-text-field')?.value.trim();
            if (!firstText) container.innerHTML = '';
        }

        aiGeneratedPool.forEach((q, idx) => {
            if (selectedAiIndices.has(idx)) {
                addQuestionCard({
                    ...q,
                    id: null,
                    fromAi: true,
                }, true);
            }
        });

        closeAiGenerator();
        markDraftDirty(true);
    }

    /* =========================================================================
       PER-QUIZ SEARCHABLE QUESTION BANK DRAWER
       ========================================================================= */
    function openQuestionBank() {
        document.getElementById('questionBankOverlay').classList.add('open');
        renderQuestionBank();
    }

    function closeQuestionBank() {
        document.getElementById('questionBankOverlay').classList.remove('open');
    }

    function setBankFilter(type, btn) {
        activeBankFilter = type;
        document.querySelectorAll('#bankTypeFilters .type-pill-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderQuestionBank();
    }

    function toggleBankItem(qId) {
        if (selectedBankIds.has(qId)) selectedBankIds.delete(qId);
        else selectedBankIds.add(qId);
        document.getElementById('bankSelectedCount').textContent = selectedBankIds.size;
    }

    function toggleEntireQuizBank(quizId, checked) {
        const quiz = QUESTION_BANK.find(q => q.id === quizId);
        if (!quiz) return;
        quiz.questions.forEach(q => {
            if (checked) selectedBankIds.add(q.id);
            else selectedBankIds.delete(q.id);
        });
        document.getElementById('bankSelectedCount').textContent = selectedBankIds.size;
        renderQuestionBank();
    }

    function clearBankSelection() {
        selectedBankIds.clear();
        document.getElementById('bankSelectedCount').textContent = '0';
        renderQuestionBank();
    }

    function renderQuestionBank() {
        const container = document.getElementById('bankQuizzesList');
        const keyword = (document.getElementById('bankSearchInput').value || '').trim().toLowerCase();

        if (!QUESTION_BANK.length) {
            container.innerHTML = `
                <div class="p-8 text-center bg-white rounded-3xl border-3 border-dashed border-[#C7E0FF]">
                    <div class="text-4xl mb-2">📭</div>
                    <h3 class="font-display text-lg text-[#0D2B4E]">No Published Quizzes Found</h3>
                    <p class="text-xs font-bold text-[#5A7696] mt-1">Publish a quiz first and its questions will appear here separated by quiz.</p>
                </div>`;
            return;
        }

        let html = '';
        QUESTION_BANK.forEach(quiz => {
            const quizMatchesKeyword = quiz.title.toLowerCase().includes(keyword)
                || (quiz.subject || '').toLowerCase().includes(keyword)
                || (quiz.class_name || '').toLowerCase().includes(keyword);

            const filteredQuestions = quiz.questions.filter(q => {
                const typeOk = activeBankFilter === 'all' || q.type === activeBankFilter;
                if (!typeOk) return false;
                if (!keyword || quizMatchesKeyword) return true;

                const inPrompt = (q.prompt || '').toLowerCase().includes(keyword);
                const inOptions = (q.options || []).some(o => String(o).toLowerCase().includes(keyword));
                const inPairs = (q.matching_pairs || []).some(p =>
                    String(p.left).toLowerCase().includes(keyword) || String(p.right).toLowerCase().includes(keyword)
                );
                return inPrompt || inOptions || inPairs;
            });

            if (!filteredQuestions.length) return;

            const allQuizChecked = filteredQuestions.every(q => selectedBankIds.has(q.id));

            html += `
                <details open class="bg-white rounded-3xl border-2.5 border-[#C7E0FF] shadow-xs overflow-hidden">
                    <summary class="p-4 bg-[#EBF5FF] cursor-pointer flex items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-display text-base font-bold text-[#0D2B4E]">📘 ${escapeHtml(quiz.title)}</span>
                                <span class="px-2.5 py-0.5 rounded-full bg-white border border-[#93C5FD] text-[#0066D6] text-[11px] font-black">${escapeHtml(quiz.subject)}</span>
                                <span class="px-2.5 py-0.5 rounded-full bg-[#ECFCCB] border border-[#BEF264] text-[#3E8708] text-[11px] font-black">${escapeHtml(quiz.class_name)}</span>
                            </div>
                            <div class="text-[11px] font-bold text-[#4A688A] mt-0.5">
                                ${filteredQuestions.length} question(s) • Published ${escapeHtml(quiz.published_at)}
                            </div>
                        </div>

                        <label onclick="event.stopPropagation()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border-2 border-[#93C5FD] text-xs font-black text-[#0066D6] cursor-pointer shrink-0">
                            <input type="checkbox" ${allQuizChecked ? 'checked' : ''} onchange="toggleEntireQuizBank(${quiz.id}, this.checked)" class="w-4 h-4 accent-[#0066D6]">
                            <span>Select All</span>
                        </label>
                    </summary>

                    <div class="p-4 space-y-2.5 border-t border-[#C7E0FF]">
                        ${filteredQuestions.map(q => {
                            const meta = TYPE_CONFIG[q.type] || TYPE_CONFIG.multiple_choice;
                            const isChecked = selectedBankIds.has(q.id);
                            const preview = buildQuestionPreviewHtml(q);

                            return `
                                <label class="flex items-start gap-3 p-3 rounded-2xl border-2 ${isChecked ? 'border-[#0066D6] bg-[#EBF5FF]' : 'border-[#E2E8F0] bg-white hover:border-[#38BDF8]'} cursor-pointer">
                                    <input type="checkbox" ${isChecked ? 'checked' : ''} onchange="toggleBankItem(${q.id})" class="mt-1 w-4 h-4 accent-[#0066D6] shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 rounded-lg text-[11px] font-black" style="background:${meta.bg};color:${meta.color};">
                                                ${meta.icon} ${meta.label}
                                            </span>
                                            <span class="text-[11px] font-black text-[#4A688A]">⭐ ${q.points} XP</span>
                                        </div>
                                        <div class="text-sm font-extrabold text-[#0D2B4E]">${escapeHtml(q.prompt)}</div>
                                        ${preview}
                                    </div>
                                </label>
                            `;
                        }).join('')}
                    </div>
                </details>
            `;
        });

        container.innerHTML = html || `
            <div class="p-8 text-center bg-white rounded-3xl border-3 border-dashed border-[#C7E0FF]">
                <div class="text-3xl mb-2">🔎</div>
                <p class="text-sm font-black text-[#0D2B4E]">No questions matched your search filter.</p>
            </div>`;
    }

    function importSelectedFromBank() {
        if (!selectedBankIds.size) {
            alert('Check at least one question from the Question Bank first.');
            return;
        }

        const container = document.getElementById('questionsContainer');
        if (container.children.length === 1) {
            const firstText = container.querySelector('.q-text-field')?.value.trim();
            if (!firstText) container.innerHTML = '';
        }

        QUESTION_BANK.forEach(quiz => {
            quiz.questions.forEach(q => {
                if (selectedBankIds.has(q.id)) {
                    addQuestionCard({
                        id: null,
                        type: q.type,
                        prompt: q.prompt,
                        options: q.options,
                        correct_answer: q.correct_answer,
                        correct_answers: q.correct_answers,
                        matching_pairs: q.matching_pairs,
                        difficulty: q.difficulty,
                        points: q.points,
                        time_limit: q.time_limit,
                        fromQuiz: quiz.title,
                    }, true);
                }
            });
        });

        clearBankSelection();
        closeQuestionBank();
        markDraftDirty(true);
    }

    document.addEventListener('DOMContentLoaded', function() {
        ensureDraftToken();
        const wasRestored = restoreDraftSnapshotIfAvailable();

        if (!wasRestored) {
            if (Array.isArray(INITIAL_QUESTIONS) && INITIAL_QUESTIONS.length > 0) {
                INITIAL_QUESTIONS.forEach(q => addQuestionCard(q, true));
            } else {
                addQuestionCard({ type: 'multiple_choice' }, true);
            }
            // Record baseline state so closing without making edits does not create duplicate requests
            persistLocalSnapshot(Date.now());
        } else {
            isDirty = true;
            saveDraftToServer({ force: true });
        }

        const manualForm = document.getElementById('manualForm');
        if (manualForm) {
            manualForm.addEventListener('input', function(e) {
                if (e.target.id === 'quizTitleInput' && activeQuizId) {
                    const heading = document.getElementById('heroQuizHeading');
                    if (heading) {
                        heading.textContent = 'Edit Quiz: ' + (e.target.value.trim() || 'Untitled Draft');
                    }
                }
                markDraftDirty(false);
            });

            manualForm.addEventListener('change', function() {
                markDraftDirty(true);
            });
        }

        // Deduplicated exit save: fires at most once per dirty edit cycle when closing or hiding tab
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden' && !isExplicitSubmit) {
                clearTimeout(autoSaveDebounceTimer);
                saveDraftToServer({ useKeepalive: true, force: false });
            }
        });

        window.addEventListener('pagehide', function() {
            if (!isExplicitSubmit) {
                clearTimeout(autoSaveDebounceTimer);
                saveDraftToServer({ useKeepalive: true, force: false });
            }
        });

        if (PRESELECTED_MODULE_ID && !wasRestored) {
            openAiGenerator();
        }
    });
</script>
@endpush