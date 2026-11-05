@extends('layouts.app')

@section('title', 'My Subjects - ' . config('app.name', 'BrainBalance'))

@push('styles')
<style>
    .subject-section { margin-bottom: 2rem; }
    .subject-title { font-size: 18px; font-weight: 900; color: #FF6B6B; margin-bottom: 1rem; border-bottom: 2px solid #FFE4C4; padding-bottom: 8px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
    .class-card { background: #fff; border-radius: 18px; padding: 20px; border: 2px solid #FFE4C4; text-decoration: none; color: inherit; display: block; transition: transform 0.15s ease, box-shadow 0.15s ease; }
    .class-card:hover { transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0,0,0,0.05); }
    .class-name { font-size: 16px; font-weight: 900; color: #2D2D2D; }
    .class-code { font-size: 12px; font-weight: 800; color: #4ECDC4; margin-top: 6px; }
    .class-meta { font-size: 11px; font-weight: 700; color: #aaa; margin-top: 4px; }
    .empty-state { text-align: center; padding: 3rem 1rem; background: #fff; border-radius: 18px; border: 2px solid #FFE4C4; }
    .empty-emoji { font-size: 48px; margin-bottom: 12px; }
    .empty-title { font-size: 16px; font-weight: 900; color: #2D2D2D; margin-bottom: 6px; }
    .empty-sub { font-size: 13px; font-weight: 700; color: #aaa; }
</style>
@endpush

@section('content')
<div class="topbar">
    <div>
        <h1 class="tgreet">My Subjects & Classes 📚</h1>
        <p class="tsub">
            @if(Auth::user()->isTeacher())
                Select a class to manage its files, learning modules, and quizzes.
            @else
                Browse your enrolled classes to access learning materials and quizzes.
            @endif
        </p>
    </div>
</div>

@if(!isset($subjects) || count($subjects) === 0)
    <div class="empty-state">
        <div class="empty-emoji">🏫</div>
        <div class="empty-title">No classes enrolled</div>
        <div class="empty-sub">You are not currently enrolled in any active classes or subjects.</div>
    </div>
@else
    @foreach($subjects as $subjectName => $classList)
        <div class="subject-section">
            <div class="subject-title">📖 {{ ucfirst($subjectName) }}</div>
            <div class="grid">
                @foreach($classList as $class)
                    <a href="{{ route('subjects.show', $class->id) }}" class="class-card">
                        <div class="class-name">{{ $class->class_name ?? $class->name }}</div>
                        @if(!empty($class->class_code ?? $class->code))
                            <div class="class-code">Code: {{ $class->class_code ?? $class->code }}</div>
                        @endif
                        @if(!empty($class->teacher))
                            <div class="class-meta">Teacher: {{ $class->teacher->first_name }} {{ $class->teacher->last_name }}</div>
                        @elseif(!empty($class->grade_level))
                            <div class="class-meta">Grade Level: {{ $class->grade_level }}</div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
@endif
@endsection