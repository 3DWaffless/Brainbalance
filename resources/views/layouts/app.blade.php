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
        /* --- KID-FRIENDLY TACTILE UI OVERRIDES --- */

        /* 1. Playful Chunky Buttons with 3D Depth */
        .btn-primary {
            background: #FF6B6B !important;
            color: #fff !important;
            border: none !important;
            border-radius: 16px !important;
            padding: 12px 22px !important;
            font-size: 14px !important;
            font-weight: 900 !important;
            box-shadow: 0 5px 0 #D94848 !important; /* Thick bottom lip */
            transition: all 0.1s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        }
        .btn-primary:hover {
            background: #ff5252 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 7px 0 #D94848 !important;
        }
        .btn-primary:active {
            transform: translateY(4px) !important;
            box-shadow: 0 1px 0 #D94848 !important;
        }

        .btn-teal {
            background: #4ECDC4 !important;
            color: #fff !important;
            border: none !important;
            border-radius: 16px !important;
            padding: 12px 22px !important;
            font-size: 14px !important;
            font-weight: 900 !important;
            box-shadow: 0 5px 0 #32B3A9 !important;
            transition: all 0.1s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        }
        .btn-teal:hover {
            background: #3dbcb3 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 7px 0 #32B3A9 !important;
        }
        .btn-teal:active {
            transform: translateY(4px) !important;
            box-shadow: 0 1px 0 #32B3A9 !important;
        }

        .btn-ghost {
            background: #fff !important;
            color: #FF6B6B !important;
            border-radius: 16px !important;
            border: 3px solid #FFE4C4 !important;
            box-shadow: 0 4px 0 #FFE4C4 !important;
            font-weight: 900 !important;
            transition: all 0.1s ease !important;
        }
        .btn-ghost:hover {
            background: #FFF0E0 !important;
        }
        .btn-ghost:active {
            transform: translateY(3px) !important;
            box-shadow: 0 1px 0 #FFE4C4 !important;
        }

        .btn-danger {
            background: transparent !important;
            color: #e74c3c !important;
            border: 2px solid #F8D7DA !important;
            padding: 6px 12px !important;
            border-radius: 12px !important;
            font-size: 11px !important;
            font-weight: 900 !important;
            font-family: 'Nunito', sans-serif !important;
            cursor: pointer !important;
            transition: background 0.15s !important;
        }
        .btn-danger:hover {
            background: #F8D7DA !important;
        }

        /* 2. Toy-Like Playful Cards & Classroom Cards */
        .card, .item-card, .class-card {
            border-radius: 22px !important;
            border: 3px solid #FFE4C4 !important;
            box-shadow: 0 8px 0 #FFE4C4 !important; /* Thick bottom edge */
            transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.2s ease !important;
        }

        .card:hover, .item-card:hover, .class-card:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 11px 0 #FFD1A4 !important;
        }

        .card:active, .item-card:active, .class-card:active {
            transform: translateY(4px) !important;
            box-shadow: 0 2px 0 #FFD1A4 !important;
        }

        /* 3. Chunky Form Inputs */
        .form-input, .form-textarea {
            border-radius: 16px !important;
            border: 3px solid #FFE4C4 !important;
            background: #FFF8F0 !important;
            font-weight: 800 !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.03) !important;
        }
        .form-input:focus, .form-textarea:focus {
            border-color: #FF6B6B !important;
            background: #fff !important;
            box-shadow: 0 0 0 4px rgba(255, 107, 107, 0.15) !important;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Nunito', sans-serif; background: #FFF8F0; min-height: 100vh; display: flex; flex-direction: column; color: #2D2D2D; }

        .mobile-header { display: none; background: #fff; padding: 12px 20px; border-bottom: 2px solid #FFE4C4; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; }
        .mobile-brand { display: flex; align-items: center; gap: 8px; font-weight: 900; font-size: 16px; }
        .toggle-btn { background: #FFF0E0; border: 2px solid #FFE4C4; border-radius: 10px; padding: 6px 10px; font-size: 18px; cursor: pointer; color: #FF6B6B; }

        .layout-container { display: flex; flex: 1; min-height: 100vh; position: relative; }
        .sidebar { width: 240px; background: #fff; border-right: 2px solid #FFE4C4; display: flex; flex-direction: column; padding: 1.25rem 0.75rem; flex-shrink: 0; position: fixed; top: 0; left: 0; height: 100vh; z-index: 100; transition: transform 0.25s ease-in-out; }
        
        .profile-card { display: flex; flex-direction: column; align-items: center; text-align: center; padding-bottom: 1.25rem; border-bottom: 2px solid #FFE4C4; margin-bottom: 1rem; }
        .profile-avatar { width: 72px; height: 72px; border-radius: 50%; background: #FF6B6B; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 900; border: 3px solid #FFE4C4; margin-bottom: 8px; box-shadow: 0 4px 10px rgba(255, 107, 107, 0.15); object-fit: cover; }
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
            @if(Auth::user()->avatar_url)
                <img src="{{ Auth::user()->avatar_url }}" class="profile-avatar" alt="Profile Picture">
            @else
                <div class="profile-avatar">
                    {{ Auth::user()->initials }}
                </div>
            @endif
            <div class="profile-name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
            <div class="profile-role">{{ ucfirst(Auth::user()->role ?? 'User') }}</div>
        </div>

        <nav class="sb-nav">
            <a href="{{ route('dashboard') }}" class="sb-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
            <a href="{{ route('subjects.index') }}" class="sb-item {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                Subjects
            </a>
            <a href="{{ route('quiz.create') }}" class="sb-item {{ request()->routeIs('quiz.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                Quizzes
            </a>
            <a href="{{ route('profile.edit') }}" class="sb-item {{ request()->routeIs('profile.*') || request()->routeIs('settings') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Settings
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
        @yield('content')
    </main>
</div>

{{-- FLOATING NOTIFICATIONS --}}
@if(session('success') || session('error'))
    <div id="floating-toast-container">
        @if(session('success'))
            <div class="toast toast-success">
                <span style="font-size: 18px;">✅</span> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="toast toast-error">
                <span style="font-size: 18px;">🚨</span> {{ session('error') }}
            </div>
        @endif
    </div>

    <style>
        #floating-toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }
        
        .toast {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 24px;
            border-radius: 14px;
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            font-weight: 800;
            color: #fff;
            animation: toastSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            transition: opacity 0.4s ease, transform 0.4s ease;
        }
        
        .toast-success {
            background: #10B981;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }
        
        .toast-error {
            background: #EF4444;
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
        }
        
        .toast.hiding {
            opacity: 0;
            transform: translateX(100%);
        }
        
        @keyframes toastSlideIn {
            0% { transform: translateX(120%); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                setTimeout(() => {
                    toast.classList.add('hiding');
                    setTimeout(() => toast.remove(), 400); 
                }, 3000);
            });
        });
    </script>
@endif

<!-- AUDIO FEEDBACK POP SOUND SCRIPT WITH EVENT DELEGATION -->
<script>
    function playPopSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            
            osc.type = 'sine';
            osc.frequency.setValueAtTime(400, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(800, ctx.currentTime + 0.08);
            
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.08);
            
            osc.connect(gain);
            gain.connect(ctx.destination);
            
            osc.start();
            osc.stop(ctx.currentTime + 0.08);
        } catch (e) {}
    }

    // Event Delegation: Automatically captures clicks on buttons, item cards, and classroom cards
    document.addEventListener('click', (e) => {
        if (e.target.closest('.btn-primary, .btn-teal, .btn-ghost, .card, .item-card, .class-card, .sb-item')) {
            playPopSound();
        }
    });

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
</script>
@stack('scripts')
</body>
</html>