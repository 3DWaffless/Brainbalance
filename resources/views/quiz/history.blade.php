@extends('layouts.app')

@section('title', 'Quiz History - BrainBalance')

@section('content')
<div class="topbar">
    <div>
        <div style="font-size: 11px; font-weight: 800; color: #aaa; text-transform: uppercase;">Learner Records</div>
        <div class="tgreet" style="color: #FF6B6B;">📜 My Quiz History</div>
        <div class="tsub">Review past attempts, track your XP earnings, and learn from mistakes.</div>
    </div>
    <a href="{{ route('dashboard') }}" class="btn-ghost">&larr; Back to Dashboard</a>
</div>

<div class="dashboard-grid">
    <div class="col-12 card">
        <div class="card-title" style="margin-bottom: 20px;">
            <span>🏆 Completed Assignments ({{ $attempts->total() }})</span>
        </div>

        @if($attempts->count() > 0)
            <div class="item-list">
                @foreach($attempts as $attempt)
                    @php
                        $quiz = $attempt->quiz;
                        $subjectName = ucfirst($quiz->subject ?? 'General');
                        $subjectIcon = match(strtolower($quiz->subject ?? '')) {
                            'math', 'mathematics' => '📐',
                            'science' => '🧪',
                            'english', 'reading' => '📖',
                            default => '💡',
                        };
                        $accuracy = $attempt->total_questions > 0 ? round(($attempt->score / $attempt->total_questions) * 100) : 0;
                    @endphp

                    <div class="item-card" style="padding: 16px; border: 2px solid #FFE4C4; border-radius: 16px; background: #FFF8F0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <div style="font-size: 16px; font-weight: 900; color: #2D2D2D; display: flex; align-items: center; gap: 8px;">
                                {{ $quiz->title ?? 'Untitled Quiz' }}
                                <span style="background: #FFE4C4; color: #FF6B6B; font-weight: 900; font-size: 11px; padding: 3px 10px; border-radius: 50px; border: 1.5px solid #FFD1A4;">
                                    {{ $subjectIcon }} {{ $subjectName }}
                                </span>
                            </div>
                            <div style="font-size: 12px; font-weight: 700; color: #aaa; margin-top: 4px;">
                                Class: {{ $quiz->classroom->name ?? 'Unknown' }} &bull; 
                                Completed: {{ $attempt->created_at->format('M d, Y g:i A') }} &bull; 
                                Time: {{ floor($attempt->time_taken_seconds / 60) }}m {{ $attempt->time_taken_seconds % 60 }}s
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 16px;">
                            {{-- Stats Pill --}}
                            <div style="display: flex; gap: 12px; background: #fff; padding: 8px 14px; border-radius: 12px; border: 2px solid #FFE4C4; text-align: center;">
                                <div>
                                    <div style="font-size: 14px; font-weight: 900; color: #2D2D2D;">{{ $attempt->score }}/{{ $attempt->total_questions }}</div>
                                    <div style="font-size: 10px; font-weight: 800; color: #aaa;">Score</div>
                                </div>
                                <div style="border-left: 1px solid #FFE4C4;"></div>
                                <div>
                                    <div style="font-size: 14px; font-weight: 900; color: #4ECDC4;">{{ $accuracy }}%</div>
                                    <div style="font-size: 10px; font-weight: 800; color: #aaa;">Accuracy</div>
                                </div>
                                <div style="border-left: 1px solid #FFE4C4;"></div>
                                <div>
                                    <div style="font-size: 14px; font-weight: 900; color: #FFD166;">+{{ $attempt->total_xp }}</div>
                                    <div style="font-size: 10px; font-weight: 800; color: #aaa;">XP</div>
                                </div>
                            </div>

                            <a href="{{ route('quiz.review', $attempt->id) }}" class="btn-teal" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">
                                🔍 Review Answers
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top: 20px;">
                {{ $attempts->links() }}
            </div>
        @else
            <div class="empty-state" style="padding: 3rem 1rem; text-align: center;">
                <div style="font-size: 48px; margin-bottom: 10px;">📜</div>
                <div style="font-size: 16px; font-weight: 900; color: #2D2D2D;">No Quiz History Yet</div>
                <p style="font-size: 13px; color: #aaa; font-weight: 700; margin-top: 4px;">Complete your assigned quizzes to see your scores and answer breakdowns here!</p>
            </div>
        @endif
    </div>
</div>
@endsection