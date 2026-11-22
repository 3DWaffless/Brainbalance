{{-- FILE: resources/views/quiz/review.blade.php --}}
@extends('layouts.app')

@section('title', 'Attempt Review - ' . config('app.name', 'BrainBalance'))

@section('content')
@php
    $accuracyPct = $attempt->total_questions > 0
        ? round(($attempt->score / $attempt->total_questions) * 100)
        : 0;

    $celebration = match(true) {
        $accuracyPct >= 90 => ['emoji' => '🏆', 'label' => 'Superstar Score!', 'bg' => 'linear-gradient(135deg, #FEF9C3 0%, #FFFFFF 55%, #DCFCE7 100%)'],
        $accuracyPct >= 70 => ['emoji' => '🌟', 'label' => 'Awesome Work!', 'bg' => 'linear-gradient(135deg, #E0F2FE 0%, #FFFFFF 55%, #ECFCCB 100%)'],
        default            => ['emoji' => '💪', 'label' => 'Great Effort!', 'bg' => 'linear-gradient(135deg, #FFF7ED 0%, #FFFFFF 55%, #EEF6FF 100%)'],
    };

    $optionLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
@endphp

<div class="flex flex-col gap-6 sm:gap-8 w-full">
    <div class="card-tactile flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
         style="background: {{ $celebration['bg'] }} !important;">
        <div class="flex items-start sm:items-center gap-4">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white border-3 border-amber-300 flex items-center justify-center text-3xl sm:text-4xl shrink-0 shadow-2xs">
                {{ $celebration['emoji'] }}
            </div>
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white border border-sky-200 text-[10px] font-black text-[#0066D6] uppercase tracking-wider mb-1">
                    {{ $celebration['label'] }} &bull; Quiz Breakdown
                </div>
                <h1 class="font-display text-2xl md:text-3xl font-bold text-brand-text leading-tight">
                    Review: {{ $attempt->quiz->title }} 📝
                </h1>
                <p class="text-xs sm:text-sm font-extrabold text-slate-500 mt-1">
                    Completed on {{ $attempt->created_at->format('M d, Y \a\t g:i A') }} &bull; Class: <span class="text-[#0066D6]">{{ $attempt->quiz->classroom->name ?? 'N/A' }}</span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto justify-end">
            <a href="{{ route('quiz.show', $attempt->quiz_id) }}" class="btn-tactile-ghost text-xs py-2.5 px-4">
                &larr; Back to Quiz
            </a>
            @if(\Illuminate\Support\Facades\Route::has('quiz.history'))
                <a href="{{ route('quiz.history') }}" class="btn-tactile-ghost text-xs py-2.5 px-4">
                    📜 History
                </a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn-tactile-primary text-xs py-2.5 px-4">
                Dashboard &rarr;
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card-tactile text-center" style="background: #EBF5FF !important; border-color: #93C5FD !important;">
            <div class="text-[11px] font-black text-slate-500 uppercase tracking-wider">🎯 Final Score</div>
            <div class="font-display text-2xl sm:text-3xl font-bold text-[#0066D6] mt-1">
                {{ $attempt->score }} / {{ $attempt->total_questions }}
            </div>
        </div>
        <div class="card-tactile text-center" style="background: #ECFCCB !important; border-color: #86EFAC !important;">
            <div class="text-[11px] font-black text-slate-500 uppercase tracking-wider">✨ Accuracy</div>
            <div class="font-display text-2xl sm:text-3xl font-bold text-[#3E8708] mt-1">
                {{ $accuracyPct }}%
            </div>
        </div>
        <div class="card-tactile text-center" style="background: #FFF7ED !important; border-color: #FDBA74 !important;">
            <div class="text-[11px] font-black text-slate-500 uppercase tracking-wider">🔥 Max Streak</div>
            <div class="font-display text-2xl sm:text-3xl font-bold text-[#C2410C] mt-1">
                {{ $attempt->max_streak }}x
            </div>
        </div>
        <div class="card-tactile text-center" style="background: #FEFCE8 !important; border-color: #FDE047 !important;">
            <div class="text-[11px] font-black text-slate-500 uppercase tracking-wider">⭐ XP Earned</div>
            <div class="font-display text-2xl sm:text-3xl font-bold text-amber-600 mt-1">
                +{{ $attempt->total_xp }}
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-xl sm:text-2xl font-bold text-brand-text">🔍 Question-by-Question Check</h2>
            <span class="text-xs font-black text-slate-400">{{ $attempt->responses->count() }} Items</span>
        </div>

        @foreach($attempt->responses as $index => $response)
            @php
                $question = $response->question;
                $qType = strtolower((string) ($question->question_type ?? $question->type ?? 'multiple_choice'));
                if ($qType === 'identification') $qType = 'short_answer';
                $options = is_array($question->options ?? null) ? $question->options : [];
                if ($qType === 'true_false' && empty($options)) $options = ['True', 'False'];

                $selectedIndex = $response->selected_index ?? $response->selected_option;
                $decodedTyped = json_decode((string) ($response->typed_answer ?? ''), true);
                $decodedCorrect = json_decode((string) ($question->correct_answer ?? ''), true);

                $isCorrect = (bool) $response->is_correct;
            @endphp

            <div class="card-tactile border-l-8 {{ $isCorrect ? 'border-l-emerald-500' : 'border-l-rose-500' }}">
                <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="px-3.5 py-1 rounded-full text-xs font-black border-2 {{ $isCorrect ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-rose-100 text-rose-800 border-rose-300' }}">
                            {{ $isCorrect ? '✅ Correct' : '💡 Keep Learning' }}
                        </span>
                        <span class="text-xs font-black text-[#0066D6] uppercase tracking-wider">Question {{ $index + 1 }}</span>
                    </div>

                    <span class="text-xs font-extrabold text-slate-400">
                        ⏱️ {{ $response->time_taken_seconds ?? 0 }}s &bull; ⭐ +{{ $response->xp_earned ?? 0 }} XP
                    </span>
                </div>

                <h3 class="text-base sm:text-lg font-black text-brand-text mb-4 leading-snug">
                    {{ $question->question_text ?? 'Question text unavailable' }}
                </h3>

                @if(in_array($qType, ['multiple_choice', 'multiple_select', 'true_false'], true) && !empty($options))
                    @php
                        $rightList = is_array($decodedCorrect) ? $decodedCorrect : [trim((string) ($question->correct_answer ?? ''))];
                        $chosenList = is_array($decodedTyped)
                            ? $decodedTyped
                            : (is_numeric($selectedIndex) && isset($options[(int) $selectedIndex]) ? [$options[(int) $selectedIndex]] : []);
                    @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($options as $optIdx => $optText)
                            @php
                                $isRightChoice = in_array(trim((string) $optText), array_map('trim', $rightList), true);
                                $isUserChoice = in_array(trim((string) $optText), array_map('trim', $chosenList), true);

                                $cardStyle = 'bg-brand-bg border-brand-border';
                                $badge = null;
                                $badgeStyle = '';

                                if ($isRightChoice) {
                                    $cardStyle = 'bg-emerald-50 border-emerald-400';
                                    $badge = $isUserChoice ? '✅ Your Pick (Correct)' : '✅ Correct Answer';
                                    $badgeStyle = 'text-emerald-700 border border-emerald-200';
                                } elseif ($isUserChoice) {
                                    $cardStyle = 'bg-rose-50 border-rose-400';
                                    $badge = '❌ Your Choice';
                                    $badgeStyle = 'text-rose-700 border border-rose-200';
                                }
                            @endphp
                            <div class="p-3.5 rounded-2xl border-3 {{ $cardStyle }} flex justify-between items-center gap-2 text-xs sm:text-sm font-extrabold text-brand-text">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-7 h-7 rounded-lg bg-white border-2 border-brand-border flex items-center justify-center text-xs font-black text-[#0066D6] shrink-0">
                                        {{ $optionLetters[$optIdx] ?? ($optIdx + 1) }}
                                    </span>
                                    <span class="break-words">{{ $optText }}</span>
                                </div>
                                @if($badge)
                                    <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full bg-white shadow-2xs shrink-0 {{ $badgeStyle }}">
                                        {{ $badge }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif($qType === 'matching')
                    @php
                        $pairs = is_array($question->matching_pairs) && !empty($question->matching_pairs)
                            ? $question->matching_pairs
                            : (is_array($decodedCorrect) ? $decodedCorrect : []);
                    @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach($pairs as $pIdx => $pair)
                            @php $givenRight = is_array($decodedTyped) ? ($decodedTyped[$pIdx] ?? 'Unmatched') : ''; @endphp
                            <div class="p-3 rounded-2xl border-2 {{ $isCorrect ? 'bg-emerald-50 border-emerald-300' : 'bg-sky-50 border-sky-200' }} text-xs font-black">
                                {{ $pair['left'] ?? '' }} ↔ <span class="text-emerald-700">{{ $pair['right'] ?? '' }}</span>
                                @if($givenRight !== '')
                                    <div class="text-[11px] text-slate-500 mt-0.5">Your match: {{ $givenRight }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    @php
                        $studentDisplay = is_array($decodedTyped)
                            ? implode(', ', $decodedTyped)
                            : ($response->typed_answer ?? '');
                        $correctDisplay = is_array($decodedCorrect)
                            ? implode(' | ', $decodedCorrect)
                            : ($question->correct_answer ?? '');
                    @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3.5 rounded-2xl border-3 {{ $isCorrect ? 'bg-emerald-50 border-emerald-400' : 'bg-rose-50 border-rose-400' }}">
                            <div class="text-[10px] font-black uppercase {{ $isCorrect ? 'text-emerald-700' : 'text-rose-700' }}">Your Answer</div>
                            <div class="text-sm font-black text-brand-text mt-0.5">{{ $studentDisplay ?: 'No answer submitted' }}</div>
                        </div>
                        @if($correctDisplay !== '' && $qType !== 'essay')
                            <div class="p-3.5 rounded-2xl border-3 bg-emerald-50 border-emerald-400">
                                <div class="text-[10px] font-black uppercase text-emerald-700">Expected Answer</div>
                                <div class="text-sm font-black text-brand-text mt-0.5">{{ $correctDisplay }}</div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection