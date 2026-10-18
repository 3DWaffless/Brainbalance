@extends('layouts.app')

@section('title', 'Class Workspace - BrainBalance')

@section('content')
<div class="topbar">
    <div>
        <div class="tgreet">Class Workspace 📝</div>
        <div class="tsub">Manage your published quizzes and view student performance.</div>
    </div>
    <a href="{{ route('quiz.create') }}" class="btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        + Create AI Quiz
    </a>
</div>

<div class="card">
    @if(isset($quizzes) && $quizzes->isEmpty())
        <div class="empty-state">
            <div class="empty-emoji">📄</div>
            <div class="empty-title">No quizzes published</div>
            <div class="empty-sub">Get started by generating your first quiz from a PDF module.</div>
        </div>
    @elseif(isset($quizzes))
        <div class="item-list">
            @foreach($quizzes as $quiz)
                <div class="item-card" style="display: flex; align-items: center; flex-wrap: wrap;">
                    <div class="item-info" style="flex: 1; min-width: 250px;">
                        <div class="item-title" style="display: flex; align-items: center; gap: 8px;">
                            {{ $quiz->title }}
                            @if($quiz->type === 'ai_generated')
                                <span style="font-size: 10px; background: #E0E7FF; color: #4338CA; padding: 2px 6px; border-radius: 4px; font-weight: 900;">AI Generated</span>
                            @endif
                        </div>
                        <div class="item-meta" style="margin-top: 4px;">
                            Subject: {{ ucfirst($quiz->subject) }} &bull; Published: {{ \Carbon\Carbon::parse($quiz->published_at)->format('M d, Y') }}
                        </div>
                    </div>
                    
                    <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                        @php $isExpired = now()->greaterThan($quiz->deadline_at); @endphp
                        <div style="font-size: 12px; font-weight: 800; color: {{ $isExpired ? '#e74c3c' : '#4ECDC4' }};">
                            Deadline: {{ \Carbon\Carbon::parse($quiz->deadline_at)->format('M d, Y h:i A') }} {{ $isExpired ? '(Closed)' : '' }}
                        </div>
                        <a href="{{ route('quiz.results', $quiz->id) ?? '#' }}" class="btn-ghost">View Results</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection