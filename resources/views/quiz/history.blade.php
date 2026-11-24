{{-- FILE: resources/views/quiz/history.blade.php --}}
@extends('layouts.app')

@section('title', 'Quiz History - ' . config('app.name', 'BrainBalance'))

@section('content')
@php
    $isStaff = in_array(auth()->user()->role ?? '', ['teacher', 'admin'], true);
@endphp
<div class="flex flex-col gap-6 sm:gap-8 w-full">
    <!-- Header -->
    <div class="card-tactile flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
         style="background: linear-gradient(135deg, #EBF5FF 0%, #FFFFFF 55%, #FEF9C3 100%) !important;">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white border border-sky-200 text-[10px] font-black text-brand-coral uppercase tracking-wider mb-1">
                📜 {{ $isStaff ? 'Class Assessment Log' : 'Explorer Logbook' }}
            </div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-brand-text flex items-center gap-2">
                {{ $isStaff ? 'Student Quiz History 🏆' : 'My Quiz History 🏆' }}
            </h1>
            <p class="text-xs sm:text-sm font-extrabold text-slate-500 mt-1">
                {{ $isStaff ? 'Inspect student quiz submissions, accuracy scores, and question-by-question answers.' : 'Review past quests, check your XP stars, and learn from every question.' }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('quiz.index') }}" class="btn-tactile-teal text-xs py-2.5 px-4">
                {{ $isStaff ? '📋 Manage Quizzes' : '⚔️ Play New Quest' }}
            </a>
            <a href="{{ route('dashboard') }}" class="btn-tactile-ghost text-xs py-2.5 px-4">
                &larr; Dashboard
            </a>
        </div>
    </div>

    <!-- Completed Assignments Card -->
    <div class="card-tactile">
        <div class="flex justify-between items-center mb-5 pb-3 border-b-4 border-dashed border-brand-border">
            <h2 class="font-display text-lg sm:text-xl font-bold text-brand-text flex items-center gap-2">
                ✅ {{ $isStaff ? 'Submitted Attempts' : 'Completed Quests' }}
            </h2>
            <span class="px-3 py-1 rounded-full bg-lime-100 border border-lime-300 text-lime-800 font-black text-xs">
                {{ $attempts->total() }} Total
            </span>
        </div>

        @if($attempts->count() > 0)
            <div class="flex flex-col gap-4">
                @foreach($attempts as $attempt)
                    @php
                        $quiz = $attempt->quiz;
                        $subjKey = strtolower($quiz->subject ?? '');
                        $subjectName = ucfirst($quiz->subject ?? 'General');

                        $subjectBadge = match(true) {
                            str_contains($subjKey, 'math') => ['icon' => '🔢', 'classes' => 'bg-sky-100 text-brand-coral border-sky-200'],
                            str_contains($subjKey, 'eng') || str_contains($subjKey, 'read') => ['icon' => '📖', 'classes' => 'bg-lime-100 text-lime-800 border-lime-300'],
                            str_contains($subjKey, 'sci') => ['icon' => '🔬', 'classes' => 'bg-purple-100 text-purple-800 border-purple-200'],
                            default => ['icon' => '📝', 'classes' => 'bg-amber-100 text-amber-900 border-amber-200'],
                        };

                        $accuracy = $attempt->total_questions > 0 ? round(($attempt->score / $attempt->total_questions) * 100) : 0;
                        $rankIcon = $accuracy >= 90 ? '🥇' : ($accuracy >= 70 ? '🥈' : '⭐');
                        $studentFullName = trim(($attempt->student->first_name ?? '') . ' ' . ($attempt->student->last_name ?? ''))
                            ?: ($attempt->student->name ?? 'Student');
                    @endphp
                    <div class="card-tactile-interactive flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-white border-2 border-brand-border flex items-center justify-center text-2xl shrink-0 shadow-2xs">
                                {{ $rankIcon }}
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base sm:text-lg font-black text-brand-text">
                                        {{ $quiz->title ?? 'Untitled Quiz' }}
                                    </h3>
                                    <span class="{{ $subjectBadge['classes'] }} border font-black text-xs px-3 py-0.5 rounded-full">
                                        {{ $subjectBadge['icon'] }} {{ $subjectName }}
                                    </span>
                                </div>
                                <p class="text-xs font-bold text-slate-500 mt-1">
                                    @if($isStaff)
                                        <span class="text-brand-text font-extrabold">👤 {{ $studentFullName }}</span> &bull;
                                    @endif
                                    🏫 {{ $quiz->classroom->name ?? 'Unknown' }} &bull;
                                    📅 {{ $attempt->created_at->format('M d, Y g:i A') }} &bull;
                                    ⏱️ {{ floor(($attempt->time_taken_seconds ?? 0) / 60) }}m {{ ($attempt->time_taken_seconds ?? 0) % 60 }}s
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto justify-between lg:justify-end">
                            <!-- Stats Pill -->
                            <div class="flex items-center gap-4 bg-white px-4 py-2.5 rounded-2xl border-3 border-brand-border text-center">
                                <div>
                                    <div class="text-sm font-black text-brand-text">{{ $attempt->score }}/{{ $attempt->total_questions }}</div>
                                    <div class="text-[10px] font-black text-slate-400 uppercase">Score</div>
                                </div>
                                <div class="h-7 w-0.5 bg-brand-border"></div>
                                <div>
                                    <div class="text-sm font-black text-brand-teal">{{ $accuracy }}%</div>
                                    <div class="text-[10px] font-black text-slate-400 uppercase">Accuracy</div>
                                </div>
                                <div class="h-7 w-0.5 bg-brand-border"></div>
                                <div>
                                    <div class="text-sm font-black text-brand-amber">+{{ $attempt->total_xp }}</div>
                                    <div class="text-[10px] font-black text-slate-400 uppercase">XP</div>
                                </div>
                            </div>

                            <a href="{{ route('quiz.review', $attempt->id) }}" class="btn-tactile-teal text-xs py-2.5 px-4">
                                🔍 Review Answers
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($attempts->hasPages())
                @php
                    $currentPage = $attempts->currentPage();
                    $lastPage = $attempts->lastPage();
                    $startPage = max(1, $currentPage - 1);
                    $endPage = min($lastPage, $currentPage + 1);
                @endphp
                <div class="mt-6 pt-3 flex flex-col sm:flex-row justify-between items-center gap-4"
                     style="border-top: 3px dashed #CBD5E1;">
                    <div class="text-xs font-extrabold text-slate-500">
                        Showing <span class="font-black text-brand-text">{{ $attempts->firstItem() }}</span>
                        to <span class="font-black text-brand-text">{{ $attempts->lastItem() }}</span>
                        of <span class="font-black text-brand-text">{{ $attempts->total() }}</span> results
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        @if ($attempts->onFirstPage())
                            <span class="btn-tactile-ghost text-xs py-2.5 px-4"
                                  style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">
                                &larr; Previous
                            </span>
                        @else
                            <a href="{{ $attempts->previousPageUrl() }}" class="btn-tactile-ghost text-xs py-2.5 px-4">
                                &larr; Previous
                            </a>
                        @endif

                        @if ($startPage > 1)
                            <a href="{{ $attempts->url(1) }}" class="btn-tactile-ghost text-xs py-2.5 px-4">1</a>
                            @if ($startPage > 2)
                                <span class="text-xs font-black text-slate-500">...</span>
                            @endif
                        @endif

                        @for ($page = $startPage; $page <= $endPage; $page++)
                            @if ($page === $currentPage)
                                <span class="btn-tactile-primary text-xs py-2.5 px-4" style="pointer-events: none;">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $attempts->url($page) }}" class="btn-tactile-ghost text-xs py-2.5 px-4">
                                    {{ $page }}
                                </a>
                            @endif
                        @endfor

                        @if ($endPage < $lastPage)
                            @if ($endPage < $lastPage - 1)
                                <span class="text-xs font-black text-slate-500">...</span>
                            @endif
                            <a href="{{ $attempts->url($lastPage) }}" class="btn-tactile-ghost text-xs py-2.5 px-4">{{ $lastPage }}</a>
                        @endif

                        @if ($attempts->hasMorePages())
                            <a href="{{ $attempts->nextPageUrl() }}" class="btn-tactile-teal text-xs py-2.5 px-4">
                                Next &rarr;
                            </a>
                        @else
                            <span class="btn-tactile-ghost text-xs py-2.5 px-4"
                                  style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">
                                Next &rarr;
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <div class="text-5xl mb-3">📭</div>
                <h3 class="font-display text-xl font-bold text-brand-text">No Quiz History Yet</h3>
                <p class="text-xs sm:text-sm font-bold text-slate-400 mt-1 mb-5">
                    {{ $isStaff ? 'No students have completed a quiz in your classes yet.' : 'Complete your assigned quizzes to see your scores and answer breakdowns here!' }}
                </p>
                <a href="{{ route('quiz.index') }}" class="btn-tactile-primary text-xs py-2.5 px-5">
                    Browse Quizzes 🚀
                </a>
            </div>
        @endif
    </div>
</div>
@endsection