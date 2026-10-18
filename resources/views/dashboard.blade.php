@extends('layouts.app')

@section('title', 'Dashboard - BrainBalance')

@push('styles')
<style>
    /* Dashboard-specific CSS only */
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
</style>
@endpush

@section('content')
<div class="topbar">
    <div>
        <div class="tgreet">
            @if(Auth::user()->isTeacher()) Teacher Workspace 🏫 @else Welcome back, {{ Auth::user()->first_name }}! 👋 @endif
        </div>
        <div class="tsub">
            @if(Auth::user()->isTeacher()) Create classes, generate AI quizzes, and track student progress. @else Track your upcoming quizzes, class assignments, and active subjects. @endif
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
            <div style="font-size: 28px; font-weight: 900; color: #4ECDC4; margin-top: 5px;">0</div>
        </div>
        <div class="col-4 card" style="text-align: center; margin-bottom: 0;">
            <div class="card-sub">Published Quizzes</div>
            <div style="font-size: 28px; font-weight: 900; color: #FFB347; margin-top: 5px;">{{ isset($quizzes) ? $quizzes->count() : 0 }}</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="col-8 card">
            <h3 style="font-size: 18px; font-weight: 900; color: #2D2D2D; margin-bottom: 15px;">My Classes</h3>
            <div class="empty-state">
                <div class="empty-emoji">🏫</div>
                <div class="empty-title">No classes yet</div>
                <div class="empty-sub">Create your first class and share the code with your students!</div>
            </div>
        </div>
        <div class="col-4 card">
            <div class="card-title"><span>📝 Recent Quizzes</span></div>
            <div class="empty-state" style="padding: 1.5rem">
                <p style="font-size:13px; color:#aaa; font-weight:700;">No quizzes published yet.</p>
            </div>
        </div>
    </div>
@else
    <!-- STUDENT DASHBOARD GRID CONTENT -->
    <div class="dashboard-grid">
        <div class="col-12 card">
            <h3 style="font-size: 18px; font-weight: 900; color: #2D2D2D; margin-bottom: 15px;">My Assignments</h3>
            <p style="font-size:13px; color:#aaa; font-weight:700;">You are all caught up!</p>
        </div>
    </div>
@endif
@endsection