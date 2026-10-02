<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Subjects — {{ config('app.name', 'BrainBalance') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Nunito', sans-serif; background: #FFF8F0; min-height: 100vh; display: flex; flex-direction: column; color: #2D2D2D; }

        /* MOBILE TOPBAR TOGGLE */
        .mobile-header { display: none; background: #fff; padding: 12px 20px; border-bottom: 2px solid #FFE4C4; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; }
        .mobile-brand { display: flex; align-items: center; gap: 8px; font-weight: 900; font-size: 16px; }
        .toggle-btn { background: #FFF0E0; border: 2px solid #FFE4C4; border-radius: 10px; padding: 6px 10px; font-size: 18px; cursor: pointer; color: #FF6B6B; }

        /* SIDEBAR STYLES */
        .layout-container { display: flex; flex: 1; min-height: 100vh; position: relative; }
        .sidebar { width: 240px; background: #fff; border-right: 2px solid #FFE4C4; display: flex; flex-direction: column; padding: 1.25rem 0.75rem; flex-shrink: 0; position: fixed; top: 0; left: 0; height: 100vh; z-index: 100; transition: transform 0.25s ease-in-out; }
        
        /* TOP PROFILE SECTION */
        .profile-card { display: flex; flex-direction: column; align-items: center; text-align: center; padding-bottom: 1.25rem; border-bottom: 2px solid #FFE4C4; margin-bottom: 1rem; }
        .profile-avatar { width: 72px; height: 72px; border-radius: 50%; background: #FF6B6B; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 900; border: 3px solid #FFE4C4; margin-bottom: 8px; box-shadow: 0 4px 10px rgba(255, 107, 107, 0.15); }
        .profile-name { font-weight: 900; font-size: 15px; color: #2D2D2D; line-height: 1.2; word-break: break-word; }
        .profile-role { font-size: 11px; font-weight: 800; color: #FF6B6B; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }

        /* NAVIGATION MENU */
        .sb-nav { display: flex; flex-direction: column; gap: 6px; flex: 1; }
        .sb-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 12px; font-size: 14px; font-weight: 800; color: #888; text-decoration: none; transition: all 0.15s ease; }
        .sb-item:hover { background: #FFF0E0; color: #FF6B6B; }
        .sb-item.active { background: #FFE4C4; color: #FF6B6B; }
        .sb-item svg { width: 20px; height: 20px; flex-shrink: 0; }

        /* BOTTOM LOGOUT BUTTON */
        .sb-bottom { border-top: 2px solid #FFE4C4; padding-top: 0.75rem; margin-top: auto; }
        .logout-btn { width: 100%; padding: 10px; border-radius: 12px; border: 2px solid #FFE4C4; background: #fff; color: #FF6B6B; font-size: 13px; font-weight: 900; font-family: 'Nunito', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.15s ease; }
        .logout-btn:hover { background: #FFF0E0; }

        /* MAIN CONTENT AREA */
        .main-content { margin-left: 240px; flex: 1; padding: 2rem; width: calc(100% - 240px); transition: all 0.25s ease-in-out; }
        .topbar { margin-bottom: 1.75rem; }
        .tgreet { font-weight: 900; font-size: 24px; color: #2D2D2D; }
        .tsub { font-size: 14px; color: #888; font-weight: 700; margin-top: 2px; }

        /* SUBJECT SECTION & GRID */
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

        /* OVERLAY FOR MOBILE SIDEBAR */
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 90; }

        @media (max-width: 768px) {
            .mobile-header { display: flex; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; width: 100%; padding: 1.25rem; }
        }
    </style>
</head>
<body>

<!-- MOBILE HEADER -->
<header class="mobile-header">
    <div class="mobile-brand">
        <span style="background:#FF6B6B;color:#fff;width:30px;height:30px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:14px">🧠</span>
        BrainBalance
    </div>
    <button class="toggle-btn" onclick="toggleSidebar()" aria-label="Toggle Navigation">☰</button>
</header>

<div class="layout-container">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- STUDENT SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <!-- TOP PROFILE AREA -->
        <div class="profile-card">
            <div class="profile-avatar">
                {{ Auth::user()->initials ?? strtoupper(substr(Auth::user()->first_name ?? 'S', 0, 1)) }}
            </div>
            <div class="profile-name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
            <div class="profile-role">{{ ucfirst(Auth::user()->role ?? 'Student') }}</div>
        </div>

        <!-- MENU BUTTONS -->
        <nav class="sb-nav">
            <a href="{{ route('dashboard') }}" class="sb-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>

            <a href="{{ route('subjects.index') }}" class="sb-item {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                Subjects
            </a>

            <a href="{{ route('profile.edit') }}" class="sb-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Settings
            </a>
        </nav>

        <!-- BOTTOM LOGOUT -->
        <div class="sb-bottom">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Log out
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
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
                <div class="empty-emoji">🎒</div>
                <div class="empty-title">No classes enrolled</div>
                <div class="empty-sub">You are not currently enrolled in any active classes or subjects.</div>
            </div>
        @else
            @foreach($subjects as $subjectName => $classList)
                <div class="subject-section">
                    <div class="subject-title">📚 {{ ucfirst($subjectName) }}</div>
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
    </main>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
</script>

</body>
</html>