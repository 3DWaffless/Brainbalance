@extends('layouts.app')

@section('title', 'Class Analytics & Diagnostics - BrainBalance')

@section('content')
<div class="topbar">
    <div>
        <div style="font-size: 11px; font-weight: 800; color: #aaa; text-transform: uppercase;">Teacher Command Center</div>
        <div class="tgreet" style="color: #FF6B6B;">📊 Class Performance & Weak Topic Diagnostics</div>
        <div class="tsub">Track student quiz results, identify struggling topics, and measure engagement.</div>
    </div>
    <a href="{{ route('dashboard') }}" class="btn-ghost">&larr; Back to Dashboard</a>
</div>

<div class="dashboard-grid">
    {{-- KPI Cards --}}
    <div class="col-12" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 10px;">
        <div class="card" style="text-align: center; margin-bottom: 0;">
            <div class="card-sub">Total Submissions</div>
            <div style="font-size: 28px; font-weight: 900; color: #FF6B6B; margin-top: 4px;">{{ $totalAttempts }}</div>
        </div>
        <div class="card" style="text-align: center; margin-bottom: 0;">
            <div class="card-sub">Average Score</div>
            <div style="font-size: 28px; font-weight: 900; color: #4ECDC4; margin-top: 4px;">{{ $avgScore }} pts</div>
        </div>
        <div class="card" style="text-align: center; margin-bottom: 0;">
            <div class="card-sub">Overall Accuracy</div>
            <div style="font-size: 28px; font-weight: 900; color: #FFB347; margin-top: 4px;">{{ $overallAccuracy }}%</div>
        </div>
    </div>

    {{-- Weak Topic Diagnostics --}}
    <div class="col-8 card">
        <div class="card-title" style="margin-bottom: 15px;">
            <span>⚠️ Weak Topic Diagnostics (Lowest Accuracy Tiers)</span>
        </div>

        @if(!empty($topicStats))
            <div class="item-list">
                @foreach($topicStats as $topicName => $stats)
                    @php
                        $topicAccuracy = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100) : 0;
                        $color = $topicAccuracy < 50 ? '#E74C3C' : ($topicAccuracy < 75 ? '#FFB347' : '#2ECC71');
                    @endphp
                    <div style="padding: 14px; border: 2px solid #FFE4C4; border-radius: 12px; background: #FFF8F0; margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <span style="font-size: 15px; font-weight: 900; color: #2D2D2D;">{{ $topicName }}</span>
                            <span style="font-size: 13px; font-weight: 900; color: {{ $color }};">{{ $topicAccuracy }}% Correct</span>
                        </div>

                        {{-- Progress Bar --}}
                        <div style="height: 10px; background: #FFE4C4; border-radius: 50px; overflow: hidden;">
                            <div style="width: {{ $topicAccuracy }}%; height: 100%; background: {{ $color }}; border-radius: 50px;"></div>
                        </div>

                        <div style="font-size: 11px; font-weight: 700; color: #aaa; margin-top: 6px;">
                            Based on {{ $stats['total'] }} answered questions in this topic.
                            @if($topicAccuracy < 50)
                                <strong style="color: #E74C3C;">Recommendation: Upload a supplementary review module.</strong>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="padding: 2rem 1rem;">
                <p style="font-size:13px; color:#aaa; font-weight:700;">No diagnostic data collected yet.</p>
            </div>
        @endif
    </div>

    {{-- Recent Submissions --}}
    <div class="col-4 card">
        <div class="card-title" style="margin-bottom: 15px;"><span>📜 Recent Submissions</span></div>

        @if($sessions->count() > 0)
            <div class="item-list">
                @foreach($sessions->take(8) as $s)
                    <div style="padding: 10px 12px; border: 1px solid #FFE4C4; border-radius: 10px; font-size: 13px; background: #fff; margin-bottom: 8px;">
                        <div style="font-weight: 900; color: #2D2D2D;">
                            {{ $s->student->first_name ?? 'Student' }} {{ $s->student->last_name ?? '' }}
                        </div>
                        <div style="font-size: 11px; font-weight: 700; color: #aaa; margin-top: 2px;">
                            {{ $s->quiz->title ?? 'Quiz' }} &bull; Score: {{ $s->score }}/{{ $s->total_questions }} (+{{ $s->total_xp }} XP)
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="padding: 2rem 1rem;">
                <p style="font-size:13px; color:#aaa; font-weight:700;">No quiz attempts logged yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection