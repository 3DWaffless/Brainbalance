@extends('layouts.app')

@section('title', 'Dashboard - BrainBalance')

@push('styles')
<style>
    /* Dashboard-specific CSS only */
    .user-greeting-card { display: flex; align-items: center; gap: 16px; }
    .dash-avatar { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 3px solid #FFE4C4; background: #FF6B6B; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; box-shadow: 0 4px 10px rgba(255, 107, 107, 0.15); flex-shrink: 0; }
    
    .class-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
    .class-card { background: #fff; border-radius: 16px; border: 2px solid #FFE4C4; overflow: hidden; transition: box-shadow 0.15s; }
    .class-card:hover { box-shadow: 0 4px 20px rgba(255,107,107,0.12); }
    .class-card-top { padding: 16px; background: linear-gradient(135deg, #FF6B6B, #FFB347); position: relative; }
    .class-card-top.teal { background: linear-gradient(135deg, #4ECDC4, #45B7D1); }
    .class-card-top.blue { background: linear-gradient(135deg, #45B7D1, #667eea); }
    .class-card-top.green { background: linear-gradient(135deg, #96CEB4, #4ECDC4); }
    .class-card-name { font-size: 16px; font-weight: 900; color: #fff; margin-bottom: 4px; }
    .class-card-desc { font-size: 12px; font-weight: 700; color: rgba(255,255,255,0.8); }
    .class-card-code { position: absolute; top: 14px; right: 14px; background: rgba(255,255,255,0.25); padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 900; color: #fff; letter-spacing: 1px; }
    .class-card-body { padding: 14px 16px; }
    .class-card-meta { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #aaa; margin-bottom: 12px; }
    
    .item-list { display: flex; flex-direction: column; gap: 10px; }
    .item-card { padding: 12px 16px; background: #FFF8F0; border-radius: 14px; border: 2px solid #FFE4C4; display: flex; justify-content: space-between; align-items: center; gap: 12px; }
    .item-info { display: flex; flex-direction: column; gap: 2px; }
    .item-title { font-weight: 900; font-size: 14px; color: #2D2D2D; }
    .item-meta { font-size: 12px; font-weight: 700; color: #aaa; }
    
    .empty-state { text-align: center; padding: 3rem 1rem; }
    .empty-emoji { font-size: 48px; margin-bottom: 12px; }
    .empty-title { font-size: 16px; font-weight: 900; color: #2D2D2D; margin-bottom: 6px; }
    .empty-sub { font-size: 13px; font-weight: 700; color: #aaa; }

    /* XP Progress Bar */
    .xp-container { background: #FFF8F0; border: 3px solid #FFE4C4; padding: 5px; border-radius: 50px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }
    .xp-bar { height: 16px; background: linear-gradient(90deg, #4ECDC4, #2ECC71); border-radius: 50px; position: relative; transition: width 0.4s ease; }
    .xp-bar-shine { position: absolute; top: 2px; left: 8px; right: 8px; height: 4px; background: rgba(255,255,255,0.4); border-radius: 10px; }
</style>
@endpush

@section('content')
<div class="topbar">
    <div class="user-greeting-card">
        @if(Auth::user()->avatar_url)
            <img src="{{ Auth::user()->avatar_url }}" class="dash-avatar" alt="Profile Picture">
        @else
            <div class="dash-avatar">
                {{ Auth::user()->initials }}
            </div>
        @endif

        <div>
            <div class="tgreet">
                @if(Auth::user()->isTeacher()) Teacher Workspace 🏫 @else Welcome back, {{ Auth::user()->first_name }}! 👋 @endif
            </div>
            <div class="tsub">
                @if(Auth::user()->isTeacher()) Create classes, generate AI quizzes, and track student progress. @else Track your upcoming quizzes, class assignments, and active subjects. @endif
            </div>
        </div>
    </div>

    @if(Auth::user()->isTeacher())
        <div class="btn-group" style="display: flex; gap: 10px;">
            <a href="{{ route('quiz.create') }}" class="btn-teal">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Generate AI Quiz
            </a>
            <button class="btn-primary" onclick="alert('Class creation modal coming soon!')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create Class
            </button>
        </div>
    @else
        <!-- Student Controls & XP Bar -->
        <div style="display: flex; align-items: center; gap: 16px;">
            <a href="{{ route('quiz.history') }}" class="btn-ghost" style="text-decoration: none;">📜 Quiz History</a>

            <div style="min-width: 200px;">
                <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 900; color: #2D2D2D; margin-bottom: 4px;">
                    <span>⭐ Level {{ Auth::user()->level ?? 1 }}</span>
                    <span style="color: #4ECDC4;">{{ Auth::user()->total_xp ?? 0 }} XP</span>
                </div>
                <div class="xp-container">
                    <div class="xp-bar" style="width: {{ min(100, (((Auth::user()->total_xp ?? 0) % 1000) / 1000) * 100) }}%;">
                        <div class="xp-bar-shine"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@if(Auth::user()->isTeacher())
    <!-- TEACHER DASHBOARD GRID CONTENT -->
    <div class="dashboard-grid" style="margin-bottom: 20px;">
        <div class="col-4 card" style="text-align: center; margin-bottom: 0;">
            <div class="card-sub">Active Classes</div>
            <div style="font-size: 28px; font-weight: 900; color: #FF6B6B; margin-top: 5px;">{{ isset($classes) ? $classes->count() : 0 }}</div>
        </div>
        <div class="col-4 card" style="text-align: center; margin-bottom: 0;">
            <div class="card-sub">Total Students</div>
            <div style="font-size: 28px; font-weight: 900; color: #4ECDC4; margin-top: 5px;">{{ $totalStudents ?? 0 }}</div>
        </div>
        <div class="col-4 card" style="text-align: center; margin-bottom: 0;">
            <div class="card-sub">Published Quizzes</div>
            <div style="font-size: 28px; font-weight: 900; color: #FFB347; margin-top: 5px;">{{ isset($quizzes) ? $quizzes->count() : 0 }}</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="col-8 card">
            <h3 style="font-size: 18px; font-weight: 900; color: #2D2D2D; margin-bottom: 15px;">My Classes</h3>
            
            @if(isset($classes) && $classes->count() > 0)
                <div class="class-grid">
                    @foreach($classes as $class)
                        <a href="{{ route('classes.show', $class->id) }}" class="class-card" style="text-decoration: none; display: block;">
                            <div class="class-card-top">
                                <div class="class-card-name">{{ $class->name }}</div>
                                <div class="class-card-desc" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $class->description ?? 'No description provided.' }}
                                </div>
                                <div class="class-card-code">{{ $class->code }}</div>
                            </div>
                            <div class="class-card-body">
                                <div class="class-card-meta">
                                    <span>👥 {{ $class->students_count ?? $class->students()->count() }} Students Enrolled</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-emoji">🏫</div>
                    <div class="empty-title">No classes yet</div>
                    <div class="empty-sub">Create your first class and share the code with your students!</div>
                </div>
            @endif
        </div>
        <div class="col-4 card">
            <div class="card-title"><span>📝 Recent Quizzes</span></div>
            
            @if(isset($quizzes) && $quizzes->count() > 0)
                <div class="item-list" style="margin-top: 15px;">
                    @foreach($quizzes->take(5) as $quiz)
                        @php
                            $subjectName = ucfirst($quiz->subject ?? 'General');
                            $subjectIcon = match(strtolower($quiz->subject ?? '')) {
                                'math', 'mathematics' => '📐',
                                'science' => '🧪',
                                'english', 'reading' => '📖',
                                default => '💡',
                            };
                        @endphp
                        <div class="item-card" style="padding: 12px; border: 2px solid #FFE4C4; border-radius: 12px; margin-bottom: 10px; background: #FFF8F0;">
                            <div>
                                <div style="font-size: 14px; font-weight: 900; color: #2D2D2D; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                    <span style="display: flex; align-items: center; gap: 6px;">
                                        {{ $quiz->title }}
                                        <span style="background: #FFE4C4; color: #FF6B6B; font-weight: 900; font-size: 10px; padding: 2px 8px; border-radius: 50px; border: 1px solid #FFD1A4;">
                                            {{ $subjectIcon }} {{ $subjectName }}
                                        </span>
                                    </span>
                                    
                                    {{-- Action Buttons --}}
                                    <div style="display: flex; gap: 12px; align-items: center; flex-shrink: 0;">
                                        <a href="{{ route('quiz.edit', $quiz->id) }}" style="font-size: 11px; color: #FF6B6B; text-decoration: none; font-weight: 800;">✏️ Edit</a>
                                        
                                        <form action="{{ route('quiz.destroy', $quiz->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this quiz? This cannot be undone.');" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="font-size: 11px; color: #e74c3c; background: none; border: none; font-weight: 800; cursor: pointer; padding: 0;">🗑️ Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <div style="font-size: 11px; font-weight: 700; color: #aaa; margin-top: 4px;">
                                    Class: {{ $quiz->classroom->name ?? 'Unknown' }} &bull; 
                                    Max Attempts: {{ $quiz->max_attempts ?? '∞' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state" style="padding: 1.5rem">
                    <p style="font-size:13px; color:#aaa; font-weight:700;">No quizzes published yet.</p>
                </div>
            @endif
        </div>
    </div>
@else
    <!-- STUDENT DASHBOARD GRID CONTENT -->
    <div class="dashboard-grid">
        <div class="col-12 card">
            <h3 style="font-size: 18px; font-weight: 900; color: #2D2D2D; margin-bottom: 15px;">My Assignments</h3>
            
            @if(isset($quizzes) && $quizzes->count() > 0)
                <div class="item-list">
                    @foreach($quizzes as $quiz)
                        @php
                            $subjectName = ucfirst($quiz->subject ?? 'General');
                            $subjectIcon = match(strtolower($quiz->subject ?? '')) {
                                'math', 'mathematics' => '📐',
                                'science' => '🧪',
                                'english', 'reading' => '📖',
                                default => '💡',
                            };

                            $userAttempts = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', Auth::id())->count();
                            $latestAttempt = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', Auth::id())->latest()->first();
                            $isClosed = $quiz->max_attempts && $userAttempts >= $quiz->max_attempts;
                        @endphp
                        <div class="item-card" style="padding: 16px; border: 2px solid #FFE4C4; border-radius: 12px; display: flex; justify-content: space-between; background: #FFF8F0; align-items: center; opacity: {{ $isClosed ? '0.85' : '1' }};">
                            <div>
                                <div style="font-size: 16px; font-weight: 900; color: #2D2D2D; display: flex; align-items: center; gap: 8px;">
                                    {{ $quiz->title }}
                                    <span style="background: #FFE4C4; color: #FF6B6B; font-weight: 900; font-size: 11px; padding: 3px 10px; border-radius: 50px; border: 1.5px solid #FFD1A4;">
                                        {{ $subjectIcon }} {{ $subjectName }}
                                    </span>
                                </div>
                                <div style="font-size: 12px; font-weight: 700; color: #aaa; margin-top: 4px;">
                                    Class: {{ $quiz->classroom->name ?? 'Unknown' }} &bull; 
                                    Due: {{ \Carbon\Carbon::parse($quiz->deadline_at)->format('M d, Y') }} &bull; 
                                    Attempts: {{ $userAttempts }}/{{ $quiz->max_attempts ?? '∞' }}
                                </div>
                            </div>

                            <div style="display: flex; gap: 8px; align-items: center;">
                                @if($latestAttempt)
                                    <a href="{{ route('quiz.review', $latestAttempt->id) }}" class="btn-ghost" style="padding: 8px 14px; font-size: 13px; text-decoration: none;">
                                        🔍 Review
                                    </a>
                                @endif

                                @if($isClosed)
                                    <button class="btn-ghost" disabled style="padding: 8px 16px; font-size: 13px; color: #888; cursor: not-allowed; border-color: #ddd !important; box-shadow: none !important; background: #f0f0f0 !important;">
                                        🔒 Closed
                                    </button>
                                @else
                                    <a href="{{ route('quiz.show', $quiz->id) }}" class="btn-primary" style="padding: 8px 16px; border-radius: 8px; width: auto; font-size: 13px; text-decoration: none;">
                                        Play Quiz &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="font-size:13px; color:#aaa; font-weight:700;">You are all caught up!</p>
            @endif
        </div>
    </div>
@endif
@endsection