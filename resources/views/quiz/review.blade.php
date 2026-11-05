@extends('layouts.app')

@section('title', 'Attempt Review - BrainBalance')

@section('content')
<div class="topbar">
    <div>
        <div style="font-size: 11px; font-weight: 800; color: #aaa; text-transform: uppercase;">Quiz Performance Breakdown</div>
        <div class="tgreet" style="color: #FF6B6B;">Review: {{ $attempt->quiz->title }} 🧠</div>
        <div class="tsub">
            Completed on {{ $attempt->created_at->format('M d, Y \a\t g:i A') }} &bull; Class: {{ $attempt->quiz->classroom->name ?? 'N/A' }}
        </div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('quiz.history') }}" class="btn-ghost">&larr; Back to History</a>
        <a href="{{ route('dashboard') }}" class="btn-primary">Dashboard &rarr;</a>
    </div>
</div>

<div class="dashboard-grid">
    {{-- Summary Cards --}}
    <div class="col-12" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 10px;">
        <div class="card" style="text-align: center; margin-bottom: 0;">
            <div class="card-sub">Final Score</div>
            <div style="font-size: 28px; font-weight: 900; color: #FF6B6B; margin-top: 4px;">
                {{ $attempt->score }} / {{ $attempt->total_questions }}
            </div>
        </div>
        <div class="card" style="text-align: center; margin-bottom: 0;">
            <div class="card-sub">Accuracy Rate</div>
            <div style="font-size: 28px; font-weight: 900; color: #4ECDC4; margin-top: 4px;">
                {{ $attempt->total_questions > 0 ? round(($attempt->score / $attempt->total_questions) * 100) : 0 }}%
            </div>
        </div>
        <div class="card" style="text-align: center; margin-bottom: 0;">
            <div class="card-sub">Max Streak</div>
            <div style="font-size: 28px; font-weight: 900; color: #FFB347; margin-top: 4px;">
                🔥 {{ $attempt->max_streak }}x
            </div>
        </div>
        <div class="card" style="text-align: center; margin-bottom: 0;">
            <div class="card-sub">Total XP Earned</div>
            <div style="font-size: 28px; font-weight: 900; color: #2ECC71; margin-top: 4px;">
                ⭐ +{{ $attempt->total_xp }}
            </div>
        </div>
    </div>

    {{-- Question-by-Question Review --}}
    <div class="col-12">
        <h3 style="font-size: 18px; font-weight: 900; color: #2D2D2D; margin-bottom: 16px;">Question Analysis</h3>

        @foreach($attempt->responses as $index => $response)
            @php
                $question = $response->question;
                $options = $question->options ?? [];
                $selectedIndex = $response->selected_index ?? $response->selected_option;
                $selectedText = is_numeric($selectedIndex) && isset($options[$selectedIndex]) ? $options[$selectedIndex] : ($selectedIndex ?? 'No answer selected');
                $correctText = $question->correct_answer ?? ($options[$question->correct_index ?? 0] ?? 'N/A');
                $isCorrect = $response->is_correct;
            @endphp

            <div class="card" style="border-left: 6px solid {{ $isCorrect ? '#2ECC71' : '#E74C3C' }} !important; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="background: {{ $isCorrect ? '#D4EDDA' : '#F8D7DA' }}; color: {{ $isCorrect ? '#28A745' : '#DC3545' }}; font-size: 14px; font-weight: 900; padding: 4px 12px; border-radius: 50px; border: 1.5px solid {{ $isCorrect ? '#28A745' : '#DC3545' }};">
                            {{ $isCorrect ? '✅ Correct' : '❌ Incorrect' }}
                        </span>
                        <span style="font-size: 13px; font-weight: 900; color: #aaa;">Question {{ $index + 1 }}</span>
                    </div>

                    <div style="font-size: 12px; font-weight: 800; color: #aaa;">
                        ⏱️ Time Spent: {{ $response->time_taken_seconds ?? 0 }}s &bull; XP: +{{ $response->xp_earned ?? 0 }}
                    </div>
                </div>

                <div style="font-size: 16px; font-weight: 900; color: #2D2D2D; margin-bottom: 16px; line-height: 1.4;">
                    {{ $question->question_text ?? 'Question text unavailable' }}
                </div>

                {{-- Options Display --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px;">
                    @foreach($options as $optIdx => $optText)
                        @php
                            $isUserChoice = (string)$optIdx === (string)$selectedIndex || $optText === $selectedText;
                            $isRightChoice = $optText === $correctText || (string)$optIdx === (string)($question->correct_index ?? -1);
                            
                            $bg = '#FFF8F0';
                            $border = '#FFE4C4';
                            $badge = null;

                            if ($isRightChoice) {
                                $bg = '#D4EDDA';
                                $border = '#28A745';
                                $badge = '✅ Correct Answer';
                            } elseif ($isUserChoice && !$isCorrect) {
                                $bg = '#F8D7DA';
                                $border = '#DC3545';
                                $badge = '❌ Your Choice';
                            }
                        @endphp

                        <div style="padding: 12px 14px; border-radius: 14px; border: 2px solid {{ $border }}; background: {{ $bg }}; display: flex; justify-content: space-between; align-items: center; font-size: 14px; font-weight: 800; color: #2D2D2D;">
                            <span>{{ $optText }}</span>
                            @if($badge)
                                <span style="font-size: 10px; font-weight: 900; padding: 2px 8px; border-radius: 20px; background: #fff; color: {{ $isRightChoice ? '#28A745' : '#DC3545' }};">
                                    {{ $badge }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection