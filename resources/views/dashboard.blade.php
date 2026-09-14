<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard — {{ config('app.name', 'BrainBalance') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', sans-serif;
            background: #FFF8F0;
            min-height: 100vh;
            display: flex;
        }

        /* ===================== SIDEBAR ===================== */
        .sidebar {
            width: 210px;
            background: #fff;
            border-right: 2px solid #FFE4C4;
            display: flex;
            flex-direction: column;
            padding: 1rem 0;
            flex-shrink: 0;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
        }

        .sb-logo {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 1rem 1rem;
            border-bottom: 2px solid #FFE4C4;
            margin-bottom: 0.75rem;
        }

        .sb-logo-icon {
            width: 38px; height: 38px;
            background: #FF6B6B;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .sb-logo-name { font-weight: 900; font-size: 14px; color: #2D2D2D; line-height: 1.1; }
        .sb-logo-sub { font-size: 9px; font-weight: 700; color: #bbb; letter-spacing: 0.6px; text-transform: uppercase; }

        .sb-nav { display: flex; flex-direction: column; gap: 3px; padding: 0 0.65rem; flex: 1; }

        .sb-item {
            display: flex; align-items: center; gap: 9px;
            padding: 9px 11px; border-radius: 12px;
            font-size: 13px; font-weight: 800; color: #aaa;
            cursor: pointer; transition: all 0.15s; text-decoration: none;
        }

        .sb-item:hover { background: #FFF0E0; color: #FF6B6B; }
        .sb-item.active { background: #FFE4C4; color: #FF6B6B; }
        .sb-item svg { width: 18px; height: 18px; flex-shrink: 0; }

        .sb-section { font-size: 10px; font-weight: 900; color: #ccc; letter-spacing: 0.8px; text-transform: uppercase; padding: 8px 11px 4px; }

        .sb-bottom { padding: 0.75rem; border-top: 2px solid #FFE4C4; margin-top: 0.5rem; }

        .sb-user {
            display: flex; align-items: center; gap: 9px;
            padding: 9px 11px; border-radius: 12px;
            background: #FFF0E0; cursor: pointer;
        }

        .sb-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: #FF6B6B;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 900; color: #fff; flex-shrink: 0;
        }

        .sb-uname { font-size: 13px; font-weight: 900; color: #2D2D2D; line-height: 1.2; }
        .sb-urole { font-size: 10px; font-weight: 700; color: #aaa; }

        /* ===================== MAIN ===================== */
        .main { margin-left: 210px; flex: 1; padding: 1.75rem; }

        .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; }
        .tgreet { font-weight: 900; font-size: 22px; color: #2D2D2D; }
        .tsub { font-size: 13px; color: #aaa; font-weight: 700; margin-top: 3px; }

        /* ===================== ALERTS ===================== */
        .alert {
            padding: 12px 16px; border-radius: 12px;
            font-size: 13px; font-weight: 700;
            margin-bottom: 1.25rem;
            display: flex; align-items: center; gap: 8px;
        }

        .alert-success { background: #D4EDDA; color: #155724; border: 2px solid #C3E6CB; }
        .alert-error   { background: #F8D7DA; color: #721C24; border: 2px solid #F5C6CB; }

        /* ===================== CARDS ===================== */
        .card {
            background: #fff;
            border-radius: 18px;
            padding: 20px;
            border: 2px solid #FFE4C4;
            margin-bottom: 1.25rem;
        }

        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px;
        }

        .card-title { font-size: 15px; font-weight: 900; color: #2D2D2D; }
        .card-sub { font-size: 12px; font-weight: 700; color: #aaa; margin-top: 2px; }

        /* ===================== FORM ===================== */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .form-field { margin-bottom: 12px; }

        .form-label { display: block; font-size: 12px; font-weight: 800; color: #555; margin-bottom: 5px; }

        .form-input {
            width: 100%; height: 44px;
            padding: 0 14px;
            border-radius: 12px;
            border: 2px solid #FFE4C4;
            background: #FFF8F0;
            color: #2D2D2D;
            font-size: 14px;
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            outline: none;
            transition: border-color 0.15s;
        }

        .form-input:focus { border-color: #FF6B6B; background: #fff; }
        .form-input.is-invalid { border-color: #e74c3c; }

        .form-error { font-size: 11px; font-weight: 700; color: #e74c3c; margin-top: 4px; display: block; }

        .form-textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 2px solid #FFE4C4;
            background: #FFF8F0;
            color: #2D2D2D;
            font-size: 14px;
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            outline: none;
            resize: none;
            transition: border-color 0.15s;
        }

        .form-textarea:focus { border-color: #FF6B6B; background: #fff; }

        /* ===================== BUTTONS ===================== */
        .btn-primary {
            background: #FF6B6B; color: #fff;
            border: none; padding: 10px 20px;
            border-radius: 12px; font-size: 13px; font-weight: 900;
            font-family: 'Nunito', sans-serif; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            transition: background 0.15s;
        }

        .btn-primary:hover { background: #e85555; }

        .btn-teal {
            background: #4ECDC4; color: #fff;
            border: none; padding: 10px 20px;
            border-radius: 12px; font-size: 13px; font-weight: 900;
            font-family: 'Nunito', sans-serif; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            transition: background 0.15s;
        }

        .btn-teal:hover { background: #3ab8b0; }

        .btn-ghost {
            background: #fff; color: #FF6B6B;
            border: 2px solid #FFE4C4; padding: 9px 16px;
            border-radius: 12px; font-size: 12px; font-weight: 900;
            font-family: 'Nunito', sans-serif; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            transition: background 0.15s;
        }

        .btn-ghost:hover { background: #FFF0E0; }

        .btn-danger {
            background: transparent; color: #e74c3c;
            border: 2px solid #F8D7DA; padding: 6px 12px;
            border-radius: 10px; font-size: 11px; font-weight: 900;
            font-family: 'Nunito', sans-serif; cursor: pointer;
            transition: background 0.15s;
        }

        .btn-danger:hover { background: #F8D7DA; }

        /* ===================== CLASS CARDS ===================== */
        .class-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }

        .class-card {
            background: #fff;
            border-radius: 16px;
            border: 2px solid #FFE4C4;
            overflow: hidden;
            transition: box-shadow 0.15s;
        }

        .class-card:hover { box-shadow: 0 4px 20px rgba(255,107,107,0.12); }

        .class-card-top {
            padding: 16px;
            background: linear-gradient(135deg, #FF6B6B, #FFB347);
            position: relative;
        }

        .class-card-top.teal { background: linear-gradient(135deg, #4ECDC4, #45B7D1); }
        .class-card-top.blue { background: linear-gradient(135deg, #45B7D1, #667eea); }
        .class-card-top.green { background: linear-gradient(135deg, #96CEB4, #4ECDC4); }

        .class-card-name { font-size: 16px; font-weight: 900; color: #fff; margin-bottom: 4px; }
        .class-card-desc { font-size: 12px; font-weight: 700; color: rgba(255,255,255,0.8); }

        .class-card-code {
            position: absolute; top: 14px; right: 14px;
            background: rgba(255,255,255,0.25);
            padding: 4px 10px; border-radius: 8px;
            font-size: 12px; font-weight: 900; color: #fff;
            letter-spacing: 1px;
        }

        .class-card-body { padding: 14px 16px; }

        .class-card-meta {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 700; color: #aaa;
            margin-bottom: 12px;
        }

        .class-card-meta svg { width: 14px; height: 14px; }

        /* ===================== STUDENT LIST ===================== */
        .student-list { display: flex; flex-direction: column; gap: 8px; }

        .student-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; background: #FFF8F0;
            border-radius: 12px; border: 2px solid #FFE4C4;
        }

        .student-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 900; color: #fff; flex-shrink: 0;
        }

        .student-name { font-size: 13px; font-weight: 800; color: #2D2D2D; flex: 1; }
        .student-email { font-size: 11px; font-weight: 700; color: #aaa; }

        /* ===================== CODE DISPLAY ===================== */
        .code-box {
            display: inline-flex; align-items: center; gap: 10px;
            background: #FFF8F0; border: 2px dashed #FFB347;
            border-radius: 12px; padding: 10px 16px;
            margin-bottom: 14px;
        }

        .code-text { font-size: 20px; font-weight: 900; color: #FF6B6B; letter-spacing: 2px; }
        .code-copy { font-size: 11px; font-weight: 800; color: #FFB347; cursor: pointer; background: none; border: none; font-family: 'Nunito', sans-serif; }

        /* ===================== EMPTY STATE ===================== */
        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-emoji { font-size: 48px; margin-bottom: 12px; }
        .empty-title { font-size: 16px; font-weight: 900; color: #2D2D2D; margin-bottom: 6px; }
        .empty-sub { font-size: 13px; font-weight: 700; color: #aaa; }

        /* ===================== JOIN BOX ===================== */
        .join-box {
            display: flex; gap: 10px; align-items: flex-end;
        }

        .join-box .form-field { flex: 1; margin-bottom: 0; }

        /* ===================== MODAL ===================== */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 50;
            align-items: center; justify-content: center;
        }

        .modal-overlay.open { display: flex; }

        .modal {
            background: #fff; border-radius: 20px;
            padding: 24px; width: 100%; max-width: 480px;
            border: 2px solid #FFE4C4;
            max-height: 80vh; overflow-y: auto;
        }

        .modal-title { font-size: 17px; font-weight: 900; color: #2D2D2D; margin-bottom: 16px; }
        .modal-close { float: right; background: none; border: none; font-size: 20px; cursor: pointer; color: #aaa; }

        /* Avatar colors */
        .av1{background:#FF6B6B} .av2{background:#4ECDC4} .av3{background:#45B7D1}
        .av4{background:#96CEB4} .av5{background:#FFB347} .av6{background:#667eea}
    </style>
</head>
<body>

{{-- ===================== SIDEBAR ===================== --}}
<div class="sidebar">
    <div class="sb-logo">
        <div class="sb-logo-icon">
            <svg width="20" height="20" viewBox="0 0 22 22" fill="none">
                <circle cx="11" cy="8" r="4" fill="white" opacity="0.95"/>
                <circle cx="7" cy="14" r="3" fill="white" opacity="0.8"/>
                <circle cx="15" cy="14" r="3" fill="white" opacity="0.8"/>
                <line x1="11" y1="8" x2="7" y2="14" stroke="white" stroke-width="1.5" opacity="0.6"/>
                <line x1="11" y1="8" x2="15" y2="14" stroke="white" stroke-width="1.5" opacity="0.6"/>
            </svg>
        </div>
        <div>
            <div class="sb-logo-name">BrainBalance</div>
            <div class="sb-logo-sub">by Ekklessia</div>
        </div>
    </div>

    <nav class="sb-nav">
        <div class="sb-section">Main</div>
        <a href="{{ route('dashboard') }}" class="sb-item active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <div class="sb-section">Learn</div>
        <a href="{{ route('modules.index') }}" class="sb-item {{ request()->routeIs('modules.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            Learning Modules
        </a>
        <a href="{{ Auth::user()->isTeacher() ? route('quiz.results') : route('quiz.index') }}" class="sb-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Quizzes
        </a>
        <div class="sb-section">Account</div>
        <a href="{{ route('profile.edit') }}" class="sb-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Settings
        </a>
    </nav>

    <div class="sb-bottom">
        <div class="sb-user">
            <div class="sb-avatar">{{ Auth::user()->initials }}</div>
            <div>
                <div class="sb-uname">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                <div class="sb-urole">{{ ucfirst(Auth::user()->role) }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:8px">
            @csrf
            <button type="submit" style="width:100%;padding:8px;border-radius:10px;border:2px solid #FFE4C4;background:#fff;color:#FF6B6B;font-size:12px;font-weight:900;font-family:'Nunito',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Log out
            </button>
        </form>
    </div>
</div>

{{-- ===================== MAIN ===================== --}}
<div class="main">

    {{-- TOPBAR --}}
    <div class="topbar">
        <div>
            <div class="tgreet">
                @if(Auth::user()->isTeacher())
                    Your Classes 🏫
                @else
                    My Classes 🎒
                @endif
            </div>
            <div class="tsub">
                @if(Auth::user()->isTeacher())
                    Create and manage your classes below.
                @else
                    Join a class using a code from your teacher.
                @endif
            </div>
        </div>

        @if(Auth::user()->isTeacher())
            <button class="btn-primary" onclick="openModal('modal-create')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create class
            </button>
        @endif
    </div>

    {{-- ALERTS --}}
    @if (session('success'))
        <div class="alert alert-success">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            <span>❌</span> {{ session('error') }}
        </div>
    @endif

    {{-- ===================== TEACHER VIEW ===================== --}}
    @if(Auth::user()->isTeacher())

        @if($classes->isEmpty())
            <div class="card">
                <div class="empty-state">
                    <div class="empty-emoji">🏫</div>
                    <div class="empty-title">No classes yet</div>
                    <div class="empty-sub">Create your first class and share the code with your students!</div>
                    <br>
                    <button class="btn-primary" onclick="openModal('modal-create')" style="margin-top:8px">
                        + Create my first class
                    </button>
                </div>
            </div>
        @else
            <div class="class-grid">
                @php $colors = ['', 'teal', 'blue', 'green']; @endphp
                @foreach($classes as $index => $class)
                    <div class="class-card">
                        <div class="class-card-top {{ $colors[$index % 4] }}">
                            <div class="class-card-name">{{ $class->name }}</div>
                            <div class="class-card-desc">{{ $class->description ?? 'No description' }}</div>
                            <div class="class-card-code">{{ $class->code }}</div>
                        </div>
                        <div class="class-card-body">
                            <div class="class-card-meta">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                {{ $class->students_count }} {{ Str::plural('student', $class->students_count) }}
                            </div>

                            <div style="display:flex;gap:8px;flex-wrap:wrap">
                                <button class="btn-ghost" onclick="openModal('modal-students-{{ $class->id }}')">
                                    👥 View students
                                </button>
                                <form method="POST" action="{{ route('classes.destroy', $class) }}" onsubmit="return confirm('Delete this class?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger">🗑 Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- STUDENTS MODAL --}}
                    <div class="modal-overlay" id="modal-students-{{ $class->id }}">
                        <div class="modal">
                            <button class="modal-close" onclick="closeModal('modal-students-{{ $class->id }}')">✕</button>
                            <div class="modal-title">{{ $class->name }} — Students</div>

                            <div class="code-box">
                                <span class="code-text">{{ $class->code }}</span>
                                <button class="code-copy" onclick="copyCode('{{ $class->code }}')">📋 Copy code</button>
                            </div>

                            @php $students = $class->students()->get(); @endphp

                            @if($students->isEmpty())
                                <div class="empty-state" style="padding:1.5rem">
                                    <div class="empty-emoji">👋</div>
                                    <div class="empty-title">No students yet</div>
                                    <div class="empty-sub">Share the class code above with your students!</div>
                                </div>
                            @else
                                <div class="student-list">
                                    @php $avColors = ['av1','av2','av3','av4','av5','av6']; @endphp
                                    @foreach($students as $i => $student)
                                        <div class="student-item">
                                            <div class="student-avatar {{ $avColors[$i % 6] }}">{{ $student->initials }}</div>
                                            <div>
                                                <div class="student-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                                                <div class="student-email">{{ $student->email }}</div>
                                            </div>
                                            <form method="POST" action="{{ route('classes.removeStudent', [$class, $student->id]) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-danger">Remove</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    @endif

    {{-- ===================== STUDENT VIEW ===================== --}}
    @if(Auth::user()->isStudent())

        {{-- JOIN CLASS --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Join a Class 🔑</div>
                    <div class="card-sub">Enter the class code your teacher gave you</div>
                </div>
            </div>

            <form method="POST" action="{{ route('classes.join') }}">
                @csrf
                <div class="join-box">
                    <div class="form-field">
                        <label class="form-label">Class code</label>
                        <input
                            name="code"
                            type="text"
                            class="form-input {{ $errors->has('code') ? 'is-invalid' : '' }}"
                            placeholder="e.g. BB-A1X9"
                            value="{{ old('code') }}"
                            style="text-transform:uppercase;letter-spacing:2px;font-size:16px"
                            maxlength="10"
                        >
                        @error('code') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn-teal" style="height:44px;margin-bottom:{{ $errors->has('code') ? '22px' : '0' }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="9" y2="12"/></svg>
                        Join class
                    </button>
                </div>
            </form>
        </div>

        {{-- JOINED CLASSES --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">My Classes 📚</div>
                    <div class="card-sub">Classes you are currently enrolled in</div>
                </div>
            </div>

            @if($classes->isEmpty())
                <div class="empty-state">
                    <div class="empty-emoji">🎒</div>
                    <div class="empty-title">No classes yet</div>
                    <div class="empty-sub">Ask your teacher for a class code and enter it above!</div>
                </div>
            @else
                @php $colors = ['', 'teal', 'blue', 'green']; @endphp
                <div class="class-grid">
                    @foreach($classes as $index => $class)
                        <div class="class-card">
                            <div class="class-card-top {{ $colors[$index % 4] }}">
                                <div class="class-card-name">{{ $class->name }}</div>
                                <div class="class-card-desc">{{ $class->description ?? 'No description' }}</div>
                            </div>
                            <div class="class-card-body">
                                <div class="class-card-meta">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    Teacher: {{ $class->teacher->first_name }} {{ $class->teacher->last_name }}
                                </div>
                                <div style="display:flex;gap:8px">
                                    <form method="POST" action="{{ route('classes.leave', $class) }}" onsubmit="return confirm('Leave this class?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger">🚪 Leave class</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    @endif

</div>

{{-- ===================== CREATE CLASS MODAL (Teacher only) ===================== --}}
@if(Auth::user()->isTeacher())
    <div class="modal-overlay" id="modal-create">
        <div class="modal">
            <button class="modal-close" onclick="closeModal('modal-create')">✕</button>
            <div class="modal-title">Create a new class 🏫</div>

            <form method="POST" action="{{ route('classes.store') }}">
                @csrf

                <div class="form-field">
                    <label class="form-label">Class name *</label>
                    <input name="name" type="text" class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="e.g. Grade 3 - Section A" value="{{ old('name') }}" required>
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-field">
                    <label class="form-label">Description (optional)</label>
                    <textarea name="description" class="form-textarea" rows="3" placeholder="What will students learn in this class?">{{ old('description') }}</textarea>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
                    <button type="button" class="btn-ghost" onclick="closeModal('modal-create')">Cancel</button>
                    <button type="submit" class="btn-primary">
                        ✨ Create class
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('open');
        });
    });

    function copyCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            alert('Code ' + code + ' copied to clipboard!');
        });
    }

    @if($errors->has('name') || $errors->has('description'))
        openModal('modal-create');
    @endif
</script>

</body>
</html>