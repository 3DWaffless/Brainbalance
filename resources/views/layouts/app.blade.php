{{-- FILE: resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'BrainBalance'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/brainbalance-theme.css') }}">
    <style>
        :root {
            --bb-blue: #0066D6;
            --bb-blue-dark: #004FA8;
            --bb-cyan: #1DC4DF;
            --bb-cyan-dark: #0E9EB8;
            --bb-lime: #68CC18;
            --bb-lime-dark: #4FA30D;
            --bb-orange: #FF8400;
            --bb-orange-dark: #D96B00;
            --bb-spectrum: linear-gradient(90deg, #0066D6 0%, #1DC4DF 33%, #68CC18 68%, #FF8400 100%);
        }

        body {
            font-family: 'Nunito', sans-serif;
            color: #0D2B4E;
            background-color: #F5FAFF;
            background-image:
                radial-gradient(at 6% 8%, rgba(0, 102, 214, 0.14) 0px, transparent 42%),
                radial-gradient(at 45% 4%, rgba(29, 196, 223, 0.16) 0px, transparent 42%),
                radial-gradient(at 92% 14%, rgba(104, 204, 24, 0.15) 0px, transparent 44%),
                radial-gradient(at 86% 86%, rgba(255, 132, 0, 0.14) 0px, transparent 46%),
                radial-gradient(#CBE6FF 2px, transparent 2px);
            background-size: 100% 100%, 100% 100%, 100% 100%, 100% 100%, 34px 34px;
        }

        .font-display { font-family: 'Fredoka', 'Nunito', sans-serif; }

        .bb-spectrum-bar {
            height: 6px;
            width: 100%;
            background: var(--bb-spectrum);
            flex-shrink: 0;
        }

        @keyframes xpShimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .bb-xp-fill {
            background: linear-gradient(90deg, #0066D6 0%, #1DC4DF 35%, #68CC18 70%, #FF8400 100%);
            background-size: 200% 100%;
            animation: xpShimmer 4s linear infinite;
        }

        .bb-sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 18px;
            font-weight: 800;
            font-size: 14.5px;
            color: #4A688A;
            border: 2.5px solid transparent;
            transition: all 0.16s ease;
            text-decoration: none;
        }
        .bb-sidebar-link:hover {
            background-color: #F2FAFF;
            color: #0066D6;
            border-color: #D4F1FC;
            transform: translateY(-1px);
        }

        .bb-nav-active-blue {
            background: linear-gradient(135deg, #EBF5FF 0%, #E0F7FC 100%);
            color: #0066D6;
            border-color: #38BDF8;
            box-shadow: 0 4px 0 #BAE6FD;
            font-weight: 900;
        }
        .bb-nav-active-lime {
            background: linear-gradient(135deg, #F4FCE3 0%, #ECFCCB 100%);
            color: #3E8708;
            border-color: #68CC18;
            box-shadow: 0 4px 0 #D9F99D;
            font-weight: 900;
        }
        .bb-nav-active-orange {
            background: linear-gradient(135deg, #FFF7ED 0%, #FFEDD5 100%);
            color: #C2410C;
            border-color: #FF8400;
            box-shadow: 0 4px 0 #FED7AA;
            font-weight: 900;
        }
        .bb-nav-active-cyan {
            background: linear-gradient(135deg, #ECFEFF 0%, #CFFAFE 100%);
            color: #0E7490;
            border-color: #1DC4DF;
            box-shadow: 0 4px 0 #A5F3FC;
            font-weight: 900;
        }
        .bb-nav-active-purple {
            background: linear-gradient(135deg, #F5F3FF 0%, #EDE9FE 100%);
            color: #5B21B6;
            border-color: #8B5CF6;
            box-shadow: 0 4px 0 #DDD6FE;
            font-weight: 900;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased selection:bg-[#1DC4DF] selection:text-white flex flex-col min-h-[100dvh] {{ (Auth::check() && Auth::user()->isStudent()) ? 'theme-student' : 'theme-teacher' }}">
    @php
        $isStudent = Auth::check() && Auth::user()->isStudent();
        $userXp = Auth::check() ? (Auth::user()->total_xp ?? 0) : 0;
        $userLevel = Auth::check() ? (Auth::user()->level ?? ((int) floor($userXp / 1000) + 1)) : 1;
        $xpInLevel = $userXp % 1000;
        $xpPercent = min(100, max(12, round(($xpInLevel / 1000) * 100)));

        $pendingAssignmentsCount = 0;
        if ($isStudent && \Illuminate\Support\Facades\Schema::hasColumn('announcements', 'type') && \Illuminate\Support\Facades\Schema::hasTable('assignment_submissions')) {
            $joinedIds = Auth::user()->joinedClasses()->pluck('classes.id');
            if ($joinedIds->isNotEmpty()) {
                $submittedIds = \App\Models\AssignmentSubmission::where('student_id', Auth::id())->pluck('announcement_id');
                $pendingAssignmentsCount = \App\Models\Announcement::whereIn('class_id', $joinedIds)
                    ->where('type', 'assignment')
                    ->whereNotIn('id', $submittedIds)
                    ->count();
            }
        }
    @endphp

    <!-- Mobile Navigation Header -->
    <header class="lg:hidden sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b-[3px] border-[#D4F1FC] shadow-xs">
        <div class="bb-spectrum-bar"></div>
        <div class="px-4 py-3 flex justify-between items-center">
            <x-brand-logo :href="Auth::check() ? route('dashboard') : url('/')" :size="38" :text-size="20" />
            @auth
                <div class="flex items-center gap-2">
                    @if($isStudent)
                        <span class="px-2.5 py-1 rounded-full bg-[#FFF7ED] border-2 border-[#FF8400] text-[#C2410C] font-black text-xs">
                            ⭐ Lv.{{ $userLevel }}
                        </span>
                    @endif
                    <button type="button" onclick="toggleSidebar()" aria-label="Toggle Navigation" class="w-11 h-11 bg-[#E6F7FC] border-2 border-[#99E2F5] rounded-2xl text-[#0066D6] flex items-center justify-center active:scale-95 transition-transform cursor-pointer">
                        <svg class="w-6 h-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round">
                            <line x1="4" y1="7" x2="20" y2="7" />
                            <line x1="4" y1="12" x2="20" y2="12" />
                            <line x1="4" y1="17" x2="20" y2="17" />
                        </svg>
                    </button>
                </div>
            @endauth
        </div>
    </header>

    <div class="flex flex-1 min-h-screen relative">
        <!-- Mobile/Tablet Drawer Backdrop Overlay -->
        <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-[#0D2B4E]/40 backdrop-blur-xs z-40 hidden lg:hidden transition-opacity"></div>

        <!-- Adaptive Navigation Sidebar -->
        @auth
        <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white border-r-[3px] border-[#B8E2F8] shadow-[6px_0_24px_rgba(0,102,214,0.06)] flex flex-col z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out shrink-0 overflow-y-auto">
            <div class="bb-spectrum-bar"></div>

            <div class="p-5 flex flex-col flex-1">
                <!-- Brand Logo -->
                <div class="pt-1 pb-4 flex items-center justify-center">
                    <x-brand-logo :href="route('dashboard')" :size="40" :text-size="20" />
                </div>

                <!-- User Profile Card -->
                <div class="flex flex-col items-center text-center pb-5">
                    <div class="relative mb-2.5">
                        @if(Auth::user()->avatar_url)
                            <img src="{{ Auth::user()->avatar_url }}"
                                 class="w-16 h-16 sm:w-20 sm:h-20 rounded-full border-4 {{ $isStudent ? 'border-[#38BDF8]' : 'border-[#68CC18]' }} object-cover shadow-md"
                                 alt="Profile Picture">
                        @else
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full text-white border-4 {{ $isStudent ? 'border-[#BAE6FD]' : 'border-[#D9F99D]' }} flex items-center justify-center text-xl sm:text-2xl font-black shadow-md"
                                 style="background: {{ $isStudent ? 'linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%)' : 'linear-gradient(135deg, #55B310 0%, #68CC18 100%)' }};">
                                {{ strtoupper(substr(Auth::user()->first_name ?? Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(Auth::user()->last_name ?? '', 0, 1)) }}
                            </div>
                        @endif

                        @if($isStudent)
                            <span class="absolute -bottom-1 -right-1 px-2 py-0.5 rounded-full bg-[#FF8400] border-2 border-white text-white font-black text-[11px] shadow-xs">
                                Lv.{{ $userLevel }}
                            </span>
                        @endif
                    </div>

                    <h2 class="font-black text-sm sm:text-base text-[#0D2B4E] leading-tight w-full truncate">
                        {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                    </h2>

                    @if($isStudent)
                        <div class="w-full mt-3 bg-[#F2FAFF] border-2 border-[#B8E2F8] rounded-2xl p-2.5 shadow-[0_3px_0_#D4F1FC]">
                            <div class="flex justify-between items-center text-[11px] font-black mb-1">
                                <span class="text-[#0066D6]">⚡ {{ number_format($userXp) }} XP</span>
                                <span class="text-[#D96B00]">Level {{ $userLevel }}</span>
                            </div>
                            <div class="w-full h-2.5 bg-[#E0F2FE] rounded-full overflow-hidden border border-[#BAE6FD]">
                                <div class="h-full bb-xp-fill rounded-full transition-all duration-500" style="width: {{ $xpPercent }}%;"></div>
                            </div>
                        </div>
                    @else
                        <span class="mt-1.5 px-3.5 py-0.5 bg-[#ECFCCB] text-[#3E8708] text-[11px] font-black uppercase tracking-wider rounded-full border-2 border-[#A3E635]">
                            {{ ucfirst(Auth::user()->role ?? 'Teacher') }}
                        </span>
                    @endif
                </div>

                <!-- Navigation Links -->
                <nav class="flex flex-col gap-2 flex-1">
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="bb-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'bb-nav-active-blue' : '' }}">
                            <svg class="w-5 h-5 stroke-current shrink-0" viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Students
                        </a>
                        <a href="{{ route('admin.subjects.index') }}" class="bb-sidebar-link {{ request()->routeIs('admin.subjects.*') ? 'bb-nav-active-lime' : '' }}">
                            <svg class="w-5 h-5 stroke-current shrink-0" viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            Subjects
                        </a>
                        <a href="{{ route('admin.quizzes.index') }}" class="bb-sidebar-link {{ request()->routeIs('admin.quizzes.*') ? 'bb-nav-active-orange' : '' }}">
                            <svg class="w-5 h-5 stroke-current shrink-0" viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            Quizzes
                        </a>
                    @elseif($isStudent)
                        <a href="{{ route('dashboard') }}" class="bb-sidebar-link {{ request()->routeIs('dashboard') ? 'bb-nav-active-blue' : '' }}">
                            <span class="text-lg">🏫</span>
                            Dashboard
                        </a>
                        <a href="{{ route('subjects.index') }}" class="bb-sidebar-link {{ request()->routeIs('subjects.*') ? 'bb-nav-active-lime' : '' }}">
                            <span class="text-lg">📚</span>
                            My Subjects
                        </a>
                        <a href="{{ route('assignments.index') }}" class="bb-sidebar-link {{ request()->routeIs('assignments.*') ? 'bb-nav-active-purple' : '' }}">
                            <span class="text-lg">📝</span>
                            <span class="flex-1">Assignments</span>
                            @if($pendingAssignmentsCount > 0)
                                <span class="px-2 py-0.5 rounded-full bg-[#FF8400] text-white text-[11px] font-black shadow-2xs">
                                    {{ $pendingAssignmentsCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('quiz.index') }}" class="bb-sidebar-link {{ request()->routeIs('quiz.index') || request()->routeIs('quiz.show') ? 'bb-nav-active-orange' : '' }}">
                            <span class="text-lg">📓</span>
                            Quizzes
                        </a>
                        @if(\Illuminate\Support\Facades\Route::has('quiz.history'))
                            <a href="{{ route('quiz.history') }}" class="bb-sidebar-link {{ request()->routeIs('quiz.history') || request()->routeIs('quiz.review') ? 'bb-nav-active-cyan' : '' }}">
                                <span class="text-lg">📜</span>
                                Quiz History
                            </a>
                        @endif
                    @else
                        <a href="{{ route('dashboard') }}" class="bb-sidebar-link {{ request()->routeIs('dashboard') ? 'bb-nav-active-blue' : '' }}">
                            <svg class="w-5 h-5 stroke-current shrink-0" viewBox="0 0 24 24" fill="none" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>
                            Dashboard
                        </a>
                        <a href="{{ route('subjects.index') }}" class="bb-sidebar-link {{ request()->routeIs('subjects.*') ? 'bb-nav-active-lime' : '' }}">
                            <svg class="w-5 h-5 stroke-current shrink-0" viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            Subjects
                        </a>
                        <a href="{{ route('quiz.index') }}" class="bb-sidebar-link {{ request()->routeIs('quiz.*') ? 'bb-nav-active-orange' : '' }}">
                            <svg class="w-5 h-5 stroke-current shrink-0" viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            Quizzes
                        </a>
                    @endif

                    <a href="{{ route('profile.edit') }}" class="bb-sidebar-link {{ request()->routeIs('profile.*') || request()->routeIs('settings') ? 'bb-nav-active-cyan' : '' }}">
                        @if($isStudent)
                            <span class="text-lg">⚙️</span>
                        @else
                            <svg class="w-5 h-5 stroke-current shrink-0" viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        @endif
                        Settings
                    </a>
                </nav>

                <!-- Bottom Logout Action -->
                <div class="pt-4 mt-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-3 rounded-2xl border-[3px] border-[#B8E2F8] bg-[#F2FAFF] text-[#0066D6] font-black text-sm flex items-center justify-center gap-2 shadow-[0_4px_0_#D4F1FC] hover:bg-[#FFF7ED] hover:border-[#FF8400] hover:text-[#D96B00] hover:shadow-[0_4px_0_#FED7AA] active:translate-y-0.5 transition-all cursor-pointer">
                            <svg class="w-5 h-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        @endauth

        <!-- Fluid Full Width Workspace Container -->
        <main class="flex-1 {{ Auth::check() ? 'lg:ml-64' : '' }} p-4 sm:p-6 lg:p-8 w-full">
            @yield('content')
        </main>
    </div>

    <!-- Global Auto-Fading Toast Notifications (3 Seconds) -->
    @php
        $unreadBadgeAlerts = collect();
        if (Auth::check() && Auth::user()->isStudent() && \Illuminate\Support\Facades\Schema::hasTable('badge_notifications')) {
            $unreadBadgeAlerts = \App\Models\BadgeNotification::with('badge')
                ->where('student_id', Auth::id())
                ->where('is_read', false)
                ->orderBy('id')
                ->get();

            if ($unreadBadgeAlerts->isNotEmpty()) {
                \App\Models\BadgeNotification::whereIn('id', $unreadBadgeAlerts->pluck('id'))
                    ->update(['is_read' => true]);
            }
        }
    @endphp

    <div id="global-toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2.5 max-w-sm sm:max-w-md w-[calc(100%-2rem)] pointer-events-none">
        @if(session('success'))
            <div class="auto-fade-toast pointer-events-auto bg-[#68CC18] text-white font-black p-3.5 sm:p-4 rounded-2xl shadow-xl flex items-center gap-3 border-4 border-[#BEF264] text-xs sm:text-sm transition-all duration-500 ease-out">
                <span class="text-xl sm:text-2xl">✅</span> <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="auto-fade-toast pointer-events-auto bg-[#FF8400] text-white font-black p-3.5 sm:p-4 rounded-2xl shadow-xl flex items-center gap-3 border-4 border-[#FDBA74] text-xs sm:text-sm transition-all duration-500 ease-out">
                <span class="text-xl sm:text-2xl">⚠️</span> <span>{{ session('error') }}</span>
            </div>
        @endif

        @foreach($unreadBadgeAlerts as $alert)
            @php
                $isEarned = $alert->type === 'earned';
            @endphp
            <div class="auto-fade-toast pointer-events-auto {{ $isEarned ? 'bg-amber-400 text-amber-950 border-amber-200' : 'bg-rose-500 text-white border-rose-300' }} font-black p-3.5 sm:p-4 rounded-2xl shadow-xl flex items-center gap-3 border-4 text-xs sm:text-sm transition-all duration-500 ease-out">
                @if($alert->badge)
                    <img src="{{ $alert->badge->image_url }}" alt="{{ $alert->badge->name }}" class="w-10 h-10 object-contain shrink-0 bg-white/80 rounded-xl p-1">
                @else
                    <span class="text-2xl shrink-0">{{ $isEarned ? '🏅' : '💔' }}</span>
                @endif
                <div>
                    <p class="text-[10px] uppercase tracking-wider opacity-80">
                        {{ $isEarned ? '🎉 New Badge Unlocked!' : '⚠️ Badge Taken Away' }}
                    </p>
                    <p class="leading-snug">{{ $alert->message }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        function scheduleToastFade(toastEl, delayMs = 3000) {
            setTimeout(() => {
                toastEl.style.opacity = '0';
                toastEl.style.transform = 'translateY(-8px)';
                setTimeout(() => toastEl.remove(), 500);
            }, delayMs);
        }

        window.showBrainBalanceToast = function (alertObj, delayMs = 3000) {
            const container = document.getElementById('global-toast-container');
            if (!container) return;

            const isEarned = (alertObj.type || 'earned') === 'earned';
            const toast = document.createElement('div');
            toast.className = `auto-fade-toast pointer-events-auto ${
                isEarned
                    ? 'bg-amber-400 text-amber-950 border-amber-200'
                    : 'bg-rose-500 text-white border-rose-300'
            } font-black p-3.5 sm:p-4 rounded-2xl shadow-xl flex items-center gap-3 border-4 text-xs sm:text-sm transition-all duration-500 ease-out`;

            const imgHtml = alertObj.image
                ? `<img src="${alertObj.image}" alt="${alertObj.name || 'Badge'}" class="w-10 h-10 object-contain shrink-0 bg-white/80 rounded-xl p-1">`
                : `<span class="text-2xl shrink-0">${isEarned ? '🏅' : '💔'}</span>`;

            toast.innerHTML = `
                ${imgHtml}
                <div>
                    <p class="text-[10px] uppercase tracking-wider opacity-80">
                        ${isEarned ? '🎉 New Badge Unlocked!' : '⚠️ Badge Taken Away'}
                    </p>
                    <p class="leading-snug">${alertObj.message || ('You earned ' + alertObj.name)}</p>
                </div>
            `;

            container.appendChild(toast);
            scheduleToastFade(toast, delayMs);
        };

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.auto-fade-toast').forEach((toast, idx) => {
                scheduleToastFade(toast, 3000 + (idx * 250));
            });
        });

        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sb && overlay) {
                sb.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>