<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'BrainBalance'))</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* GLOBAL STYLES MOVED FROM DASHBOARD */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Nunito', sans-serif; background: #FFF8F0; min-height: 100vh; display: flex; flex-direction: column; color: #2D2D2D; }

        .mobile-header { display: none; background: #fff; padding: 12px 20px; border-bottom: 2px solid #FFE4C4; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; }
        .mobile-brand { display: flex; align-items: center; gap: 8px; font-weight: 900; font-size: 16px; }
        .toggle-btn { background: #FFF0E0; border: 2px solid #FFE4C4; border-radius: 10px; padding: 6px 10px; font-size: 18px; cursor: pointer; color: #FF6B6B; }

        .layout-container { display: flex; flex: 1; min-height: 100vh; position: relative; }
        .sidebar { width: 240px; background: #fff; border-right: 2px solid #FFE4C4; display: flex; flex-direction: column; padding: 1.25rem 0.75rem; flex-shrink: 0; position: fixed; top: 0; left: 0; height: 100vh; z-index: 100; transition: transform 0.25s ease-in-out; }
        
        .profile-card { display: flex; flex-direction: column; align-items: center; text-align: center; padding-bottom: 1.25rem; border-bottom: 2px solid #FFE4C4; margin-bottom: 1rem; }
        .profile-avatar { width: 72px; height: 72px; border-radius: 50%; background: #FF6B6B; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 900; border: 3px solid #FFE4C4; margin-bottom: 8px; box-shadow: 0 4px 10px rgba(255, 107, 107, 0.15); }
        .profile-name { font-weight: 900; font-size: 15px; color: #2D2D2D; line-height: 1.2; word-break: break-word; }
        .profile-role { font-size: 11px; font-weight: 800; color: #FF6B6B; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 3px; }

        .sb-nav { display: flex; flex-direction: column; gap: 6px; flex: 1; }
        .sb-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 12px; font-size: 14px; font-weight: 800; color: #888; text-decoration: none; transition: all 0.15s ease; }
        .sb-item:hover { background: #FFF0E0; color: #FF6B6B; }
        .sb-item.active { background: #FFE4C4; color: #FF6B6B; }
        .sb-item svg { width: 20px; height: 20px; flex-shrink: 0; }

        .sb-bottom { border-top: 2px solid #FFE4C4; padding-top: 0.75rem; margin-top: auto; }
        .logout-btn { width: 100%; padding: 10px; border-radius: 12px; border: 2px solid #FFE4C4; background: #fff; color: #FF6B6B; font-size: 13px; font-weight: 900; font-family: 'Nunito', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.15s ease; }
        .logout-btn:hover { background: #FFF0E0; }

        .main-content { margin-left: 240px; flex: 1; padding: 2rem; width: calc(100% - 240px); transition: all 0.25s ease-in-out; }
        .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; gap: 12px; flex-wrap: wrap; }
        .tgreet { font-weight: 900; font-size: 24px; color: #2D2D2D; }
        .tsub { font-size: 14px; color: #888; font-weight: 700; margin-top: 2px; }

        /* Shared UI Elements */
        .dashboard-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 20px; }
        .col-8 { grid-column: span 8; }
        .col-4 { grid-column: span 4; }
        .col-12 { grid-column: span 12; }
        
        .card { background: #fff; border-radius: 18px; padding: 20px; border: 2px solid #FFE4C4; box-shadow: 0 2px 8px rgba(0,0,0,0.02); margin-bottom: 20px; }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .card-title { font-size: 16px; font-weight: 900; color: #2D2D2D; display: flex; align-items: center; justify-content: space-between; width: 100%; }
        .card-sub { font-size: 12px; font-weight: 700; color: #aaa; margin-top: 2px; }

        .form-field { margin-bottom: 12px; }
        .form-label { display: block; font-size: 12px; font-weight: 800; color: #555; margin-bottom: 5px; }
        .form-input { width: 100%; height: 44px; padding: 0 14px; border-radius: 12px; border: 2px solid #FFE4C4; background: #FFF8F0; color: #2D2D2D; font-size: 14px; font-family: 'Nunito', sans-serif; font-weight: 700; outline: none; transition: border-color 0.15s; }
        .form-input:focus { border-color: #FF6B6B; background: #fff; }
        .form-textarea { width: 100%; padding: 12px 14px; border-radius: 12px; border: 2px solid #FFE4C4; background: #FFF8F0; color: #2D2D2D; font-size: 14px; font-family: 'Nunito', sans-serif; font-weight: 700; outline: none; resize: none; transition: border-color 0.15s; }
        .form-textarea:focus { border-color: #FF6B6B; background: #fff; }

        .btn-primary { background: #FF6B6B; color: #fff; border: none; padding: 10px 20px; border-radius: 12px; font-size: 13px; font-weight: 900; font-family: 'Nunito', sans-serif; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: background 0.15s; }
        .btn-primary:hover { background: #e85555; }
        .btn-teal { background: #4ECDC4; color: #fff; border: none; padding: 10px 20px; border-radius: 12px; font-size: 13px; font-weight: 900; font-family: 'Nunito', sans-serif; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: background 0.15s; }
        .btn-teal:hover { background: #3ab8b0; }
        .btn-ghost { background: #fff; color: #FF6B6B; border: 2px solid #FFE4C4; padding: 9px 16px; border-radius: 12px; font-size: 12px; font-weight: 900; font-family: 'Nunito', sans-serif; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: background 0.15s; }
        .btn-ghost:hover { background: #FFF0E0; }
        .btn-danger { background: transparent; color: #e74c3c; border: 2px solid #F8D7DA; padding: 6px 12px; border-radius: 10px; font-size: 11px; font-weight: 900; font-family: 'Nunito', sans-serif; cursor: pointer; transition: background 0.15s; }
        .btn-danger:hover { background: #F8D7DA; }

        .alert { padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #D4EDDA; color: #155724; border: 2px solid #C3E6CB; }
        .alert-error { background: #F8D7DA; color: #721C24; border: 2px solid #F5C6CB; }

        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 90; }

        @media (max-width: 992px) {
            .col-8, .col-4 { grid-column: span 12; }
        }
        @media (max-width: 768px) {
            .mobile-header { display: flex; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; width: 100%; padding: 1.25rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

<header class="mobile-header">
    <div class="mobile-brand">
        <span style="background:#FF6B6B;color:#fff;width:30px;height:30px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:14px">🧠</span>
        BrainBalance
    </div>
    <button class="toggle-btn" onclick="toggleSidebar()" aria-label="Toggle Navigation">☰</button>
</header>

<div class="layout-container">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    @auth
    <aside class="sidebar" id="sidebar">
        <div class="profile-card">
            <div class="profile-avatar">
                {{ Auth::user()->initials ?? strtoupper(substr(Auth::user()->first_name ?? 'U', 0, 1)) }}
            </div>
            <div class="profile-name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
            <div class="profile-role">{{ ucfirst(Auth::user()->role ?? 'User') }}</div>
        </div>

        <nav class="sb-nav">
            <a href="{{ route('dashboard') }}" class="sb-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
            <a href="{{ route('subjects.index') ?? '#' }}" class="sb-item {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                Subjects
            </a>
            <a href="{{ route('quiz.create') ?? '#' }}" class="sb-item {{ request()->routeIs('quiz.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                Quizzes
            </a>
        </nav>

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
    @endauth

    <main class="main-content">
        @if (session('success'))
            <div class="alert alert-success"><span>✅</span> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error"><span>❌</span> {{ session('error') }}</div>
        @endif

        <!-- THIS IS WHERE YOUR PAGE CONTENT GOES -->
        @yield('content')
        
    </main>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
</script>
@stack('scripts')
</body>
</html>