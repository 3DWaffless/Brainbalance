{{-- FILE: resources/views/quiz/review.blade.php --}}
@extends('layouts.app')

@section('title', 'Quiz Review - ' . config('app.name', 'BrainBalance'))

@section('content')
@php
    $isStaff = in_array(auth()->user()->role ?? '', ['teacher', 'admin'], true);
    $accuracy = $attempt->total_questions > 0
        ? round(($attempt->score / $attempt->total_questions) * 100)
        : 0;
    $studentFullName = trim(($attempt->student->first_name ?? '') . ' ' . ($attempt->student->last_name ?? ''))
        ?: ($attempt->student->name ?? 'Student');
@endphp

<div class="flex flex-col gap-6 sm:gap-8 w-full">
    <!-- Top Summary Banner -->
    <div class="card-tactile flex flex-col lg:flex-row justify-between items-start lg:items-center gap-5"
         style="background: linear-gradient(135deg, #EBF5FF 0%, #FFFFFF 55%, #ECFCCB 100%) !important;">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white border border-sky-200 text-[10px] font-black text-brand-coral uppercase tracking-wider mb-1.5">
                🔍 Attempt Breakdown
            </div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-brand-text">
                {{ $quiz->title ?? 'Quiz Review' }}
            </h1>
            <p class="text-xs sm:text-sm font-extrabold text-slate-500 mt-1">
                @if($isStaff)
                    <span class="text-brand-text">👤 {{ $studentFullName }}</span> &bull;
                @endif
                🏫 {{ $quiz->classroom->name ?? 'Classroom' }} &bull;
                📅 {{ $attempt->created_at->format('M d, Y g:i A') }} &bull;
                ⏱️ {{ floor(($attempt->time_taken_seconds ?? 0) / 60) }}m {{ ($attempt->time_taken_seconds ?? 0) % 60 }}s
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('quiz.history') }}" class="btn-tactile-teal text-xs py-2.5 px-4">
                &larr; Quiz History
            </a>
            <a href="{{ route('quiz.index') }}" class="btn-tactile-ghost text-xs py-2.5 px-4">
                📋 All Quizzes
            </a>
        </div>
    </div>

    <!-- Score & XP Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card-tactile text-center py-4">
            <div class="text-2xl font-black text-brand-text">{{ $attempt->score }} / {{ $attempt->total_questions }}</div>
            <div class="text-[11px] font-black text-slate-400 uppercase mt-1">Score</div>
        </div>
        <div class="card-tactile text-center py-4">
            <div class="text-2xl font-black text-brand-teal">{{ $accuracy }}%</div>
            <div class="text-[11px] font-black text-slate-400 uppercase mt-1">Accuracy</div>
        </div>
        <div class="card-tactile text-center py-4">
            <div class="text-2xl font-black text-brand-amber">+{{ $attempt->total_xp }} XP</div>
            <div class="text-[11px] font-black text-slate-400 uppercase mt-1">XP Earned</div>
        </div>
        <div class="card-tactile text-center py-4">
            <div class="text-2xl font-black text-purple-600">🔥 {{ $attempt->max_streak ?? 0 }}</div>
            <div class="text-[11px] font-black text-slate-400 uppercase mt-1">Best Streak</div>
        </div>
    </div>

    <!-- Question-by-Question Review List -->
    <div class="card-tactile">
        <div class="flex justify-between items-center mb-6 pb-3 border-b-4 border-dashed border-brand-border">
            <h2 class="font-display text-lg sm:text-xl font-bold text-brand-text flex items-center gap-2">
                📝 Questions &amp; Answers
            </h2>
            <span class="px-3 py-1 rounded-full bg-sky-100 border border-sky-200 text-brand-coral font-black text-xs">
                {{ count($reviewItems) }} Questions
            </span>
        </div>

        @if(count($reviewItems) > 0)
            <div class="flex flex-col gap-5">
                @foreach($reviewItems as $item)
                    @php
                        $isCorrect = $item['is_correct'];
                        $borderClass = $isCorrect === true
                            ? 'border-lime-300 bg-lime-50/40'
                            : ($isCorrect === false ? 'border-amber-300 bg-amber-50/40' : 'border-brand-border bg-white');
                    @endphp
                    <div class="rounded-3xl border-3 {{ $borderClass }} p-5 sm:p-6 shadow-2xs">
                        <!-- Top Question Metadata Bar -->
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-3 py-1 rounded-xl bg-white border-2 border-brand-border text-xs font-black text-brand-text">
                                    Question #{{ $item['number'] }}
                                </span>
                                <span class="px-2.5 py-1 rounded-full bg-sky-100 border border-sky-200 text-[11px] font-black text-brand-coral">
                                    {{ $item['type_label'] }}
                                </span>
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-[11px] font-black text-slate-600 capitalize">
                                    {{ $item['difficulty'] }}
                                </span>
                                @if($isCorrect === true)
                                    <span class="px-3 py-1 rounded-full bg-lime-100 border border-lime-300 text-xs font-black text-lime-800">
                                        ✅ Correct (+{{ $item['xp_earned'] }} XP)
                                    </span>
                                @elseif($isCorrect === false)
                                    <span class="px-3 py-1 rounded-full bg-orange-100 border border-orange-300 text-xs font-black text-orange-800">
                                        ❌ Needs Review
                                    </span>
                                @endif
                            </div>

                            <button type="button"
                                    onclick="speakReviewText({{ Js::from($item['question_text']) }})"
                                    class="btn-tactile-ghost text-xs py-1.5 px-3"
                                    title="Read question aloud">
                                🔊 Listen
                            </button>
                        </div>

                        <!-- Question Prompt -->
                        <p class="text-base sm:text-lg font-black text-brand-text leading-snug mb-4">
                            {{ $item['question_text'] }}
                        </p>

                        <!-- Option-Based Questions (Multiple Choice, True/False, Multiple Select) -->
                        @if(in_array($item['type'], ['multiple_choice', 'true_false', 'multiple_select'], true) && count($item['options']) > 0)
                            @php
                                $normalizedCorrect = array_map(fn ($v) => mb_strtolower(trim((string) $v)), $item['correct_options']);
                            @endphp
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-4">
                                @foreach($item['options'] as $optIdx => $optionText)
                                    @php
                                        $wasPicked = in_array($optIdx, $item['selected_indices'], true);
                                        $isOptionRight = in_array(mb_strtolower(trim((string) $optionText)), $normalizedCorrect, true);

                                        if ($wasPicked && $isOptionRight) {
                                            $optClasses = 'bg-lime-100 border-lime-500 text-lime-950';
                                            $optBadge = '✅ Your Answer';
                                        } elseif ($wasPicked && !$isOptionRight) {
                                            $optClasses = 'bg-orange-100 border-orange-400 text-orange-950';
                                            $optBadge = '❌ Your Answer';
                                        } elseif ($isOptionRight) {
                                            $optClasses = 'bg-emerald-50 border-emerald-400 text-emerald-900';
                                            $optBadge = '✓ Correct Answer';
                                        } else {
                                            $optClasses = 'bg-white border-brand-border text-slate-600';
                                            $optBadge = null;
                                        }
                                    @endphp
                                    <div class="flex items-center justify-between gap-2 px-4 py-3 rounded-2xl border-2 {{ $optClasses }} text-sm font-extrabold">
                                        <span>{{ $optionText }}</span>
                                        @if($optBadge)
                                            <span class="text-[11px] font-black shrink-0">{{ $optBadge }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Matching Questions Breakdown -->
                        @if($item['type'] === 'matching' && count($item['matching_rows']) > 0)
                            <div class="flex flex-col gap-2 mb-4">
                                @foreach($item['matching_rows'] as $row)
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-4 py-2.5 rounded-2xl bg-white border-2 {{ $row['is_match'] ? 'border-lime-300' : 'border-orange-300' }} text-xs sm:text-sm font-extrabold">
                                        <span class="text-brand-text">{{ $row['left'] }}</span>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="{{ $row['is_match'] ? 'text-lime-700' : 'text-orange-700' }}">
                                                {{ $row['is_match'] ? '✅' : '❌' }} Your match: {{ $row['student_right'] }}
                                            </span>
                                            @if(!$row['is_match'])
                                                <span class="text-emerald-700 font-black">
                                                    (Correct: {{ $row['correct_right'] }})
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Summary Answer Boxes (Always Shown for Clarity) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-white border-2 {{ $isCorrect === false ? 'border-orange-300' : 'border-brand-border' }} px-4 py-3">
                                <div class="text-[10px] font-black uppercase tracking-wider {{ $isCorrect === false ? 'text-orange-600' : 'text-slate-400' }}">
                                    {{ $isStaff ? "Student's Answer" : 'Your Answer' }}
                                </div>
                                <div class="text-sm font-black text-brand-text mt-0.5 break-words">
                                    {{ $item['student_answer'] }}
                                </div>
                            </div>

                            <div class="rounded-2xl bg-white border-2 border-lime-300 px-4 py-3">
                                <div class="text-[10px] font-black uppercase tracking-wider text-lime-700">
                                    Correct Answer
                                </div>
                                <div class="text-sm font-black text-lime-900 mt-0.5 break-words">
                                    {{ $item['correct_answer'] }}
                                </div>
                            </div>
                        </div>

                        <!-- Optional Explanation -->
                        @if(!empty($item['explanation']))
                            <div class="mt-3 rounded-2xl bg-sky-50 border-2 border-sky-200 px-4 py-3 text-xs sm:text-sm font-bold text-slate-700">
                                <span class="font-black text-brand-coral">💡 Helpful Tip:</span>
                                {{ $item['explanation'] }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-10">
                <div class="text-4xl mb-2">📭</div>
                <p class="text-sm font-extrabold text-slate-500">
                    No question records were found for this quiz attempt.
                </p>
            </div>
        @endif
    </div>
</div>

<script>
    function speakReviewText(text) {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.95;
        window.speechSynthesis.speak(utterance);
    }
</script>
@endsection