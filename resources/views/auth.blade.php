{{-- FILE: resources/views/auth.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BrainBalance') }} — Grades 1–6 Math & English Learning</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/brainbalance-theme.css') }}">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        :root {
            /* Spectrum Palette from bal hance (6).png */
            --bb-blue: #0066D6;
            --bb-blue-dark: #004FA8;
            --bb-cyan: #1DC4DF;
            --bb-cyan-dark: #0E9EB8;
            --bb-lime: #68CC18;
            --bb-lime-dark: #4FA30D;
            --bb-orange: #FF8400;
            --bb-orange-dark: #D96B00;
            --bb-spectrum: linear-gradient(90deg, #0066D6 0%, #1DC4DF 33%, #68CC18 68%, #FF8400 100%);

            /* Active Theme Tokens (Student Default: Royal Blue + Cyan) */
            --accent: #0066D6;
            --accent-dark: #004FA8;
            --accent-soft: #E0F7FC;
            --accent-ring: rgba(29, 196, 223, 0.22);
            --card-border: #B3E5FC;
            --card-shadow: #D4F1FC;
            --field-bg: #F2FAFF;
            --field-border: #B8E2F8;
            --role-shadow: #7DD3FC;
        }

        body.theme-teacher {
            /* Teacher Theme: Vibrant Lime-Green from Spectrum */
            --accent: #55B310;
            --accent-dark: #3E8708;
            --accent-soft: #ECFCCB;
            --accent-ring: rgba(104, 204, 24, 0.22);
            --card-border: #BEF264;
            --card-shadow: #DCFCE7;
            --field-bg: #F7FEE7;
            --field-border: #BEF264;
            --role-shadow: #A3E635;
        }

        /* Landing Page Background Image (public/images/bglandingpage.png) */
        body {
            font-family: 'Nunito', sans-serif;
            color: #0D2B4E;
            min-height: 100vh;
            background-color: #F5FAFF !important;
            background-image:
                linear-gradient(180deg, rgba(245, 250, 255, 0.28) 0%, rgba(245, 250, 255, 0.38) 100%),
                url('{{ asset('images/bglandingpage.png') }}'),
                url('{{ asset('bglandingpage.png') }}') !important;
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            background-attachment: fixed !important;
            overflow-x: hidden;
        }

        .font-display { font-family: 'Fredoka', sans-serif; }

        .bb-spectrum-bar {
            height: 6px;
            width: 100%;
            background: var(--bb-spectrum);
        }

        .bb-text-spectrum {
            background: linear-gradient(90deg, #0066D6 0%, #1DC4DF 36%, #68CC18 70%, #FF8400 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Sticky Top Navbar */
        .bb-nav {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 3px solid #D4F1FC;
        }

        .bb-nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.8rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .bb-nav-links {
            display: none;
            align-items: center;
            gap: 1.75rem;
            font-size: 14.5px;
            font-weight: 800;
            color: #4A688A;
        }
        @media (min-width: 768px) {
            .bb-nav-links { display: flex; }
        }
        .bb-nav-links a {
            color: inherit;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .bb-nav-links a:hover { color: #0066D6; }

        .bb-cta-spectrum {
            background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);
            color: #FFFFFF;
            font-weight: 900;
            font-size: 14px;
            padding: 0.58rem 1.25rem;
            border-radius: 16px;
            border: 2px solid #004FA8;
            box-shadow: 0 4px 0 #004FA8;
            text-decoration: none;
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }
        .bb-cta-spectrum:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }
        .bb-cta-spectrum:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #004FA8;
        }

        /* Hero Grid */
        .bb-hero {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.75rem 1.25rem 3.5rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
            align-items: center;
        }
        @media (min-width: 1024px) {
            .bb-hero {
                grid-template-columns: 1.12fr 0.88fr;
                padding: 3.5rem 1.5rem 4.25rem;
            }
        }

        /* Portal Card */
        .bb-card {
            position: relative;
            overflow: hidden;
            background: #FFFFFF;
            border: 4px solid var(--card-border);
            border-radius: 32px;
            padding: 2.25rem 2rem 1.85rem;
            box-shadow: 0 12px 0 var(--card-shadow), 0 24px 48px rgba(0, 102, 214, 0.10);
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .bb-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 7px;
            background: var(--bb-spectrum);
        }
        body.theme-teacher .bb-card { border-radius: 26px; }
        @media (max-width: 480px) {
            .bb-card { padding: 1.75rem 1.25rem; border-radius: 24px; }
        }

        .bb-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #E6F7FC;
            border: 2px solid #99E2F5;
            color: #0066D6;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 5px 14px;
            border-radius: 999px;
            margin-bottom: 8px;
        }

        .bb-heading {
            font-weight: 900;
            font-size: 27px;
            color: #0D2B4E;
            text-align: center;
            line-height: 1.25;
        }

        .bb-sub {
            font-size: 14.5px;
            color: #5A7696;
            font-weight: 700;
            text-align: center;
            margin-top: 4px;
            margin-bottom: 1.3rem;
        }

        .bb-field { margin-bottom: 1rem; }
        .bb-label {
            display: block;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #3E5A7A;
            margin-bottom: 6px;
        }
        .bb-lrow {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .bb-iwrap { position: relative; }
        .bb-iwrap svg {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7FA2C4;
            pointer-events: none;
            width: 19px;
            height: 19px;
        }

        .bb-input {
            width: 100%;
            height: 52px;
            padding: 0 16px 0 44px;
            border-radius: 18px;
            border: 3px solid var(--field-border);
            background: var(--field-bg);
            color: #0D2B4E;
            font-size: 15.5px;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        body.theme-teacher .bb-input { border-radius: 13px; }
        .bb-input::placeholder { color: #8BA8C7; font-weight: 700; }
        .bb-input:focus {
            border-color: var(--accent);
            background: #FFFFFF;
            box-shadow: 0 0 0 4px var(--accent-ring);
        }
        .bb-input.is-invalid { border-color: #FF8400; background: #FFF7ED; }

        .bb-toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-family: 'Nunito', sans-serif;
            font-size: 13px;
            font-weight: 900;
            color: #5A7696;
            cursor: pointer;
            padding: 4px 6px;
        }
        .bb-toggle-pw:hover { color: var(--accent); }

        .bb-error {
            font-size: 12.5px;
            font-weight: 800;
            color: #D96B00;
            margin-top: 4px;
            display: block;
        }

        .bb-btn {
            width: 100%;
            height: 54px;
            border-radius: 20px;
            border: 2px solid var(--accent-dark);
            background: linear-gradient(135deg, var(--accent) 0%, #1DC4DF 100%);
            color: #fff;
            font-size: 17px;
            font-weight: 900;
            font-family: 'Nunito', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 0 var(--accent-dark);
            transition: filter 0.2s, transform 0.1s, box-shadow 0.2s;
            margin-top: 0.4rem;
        }
        body.theme-teacher .bb-btn {
            border-radius: 14px;
            background: linear-gradient(135deg, #55B310 0%, #68CC18 100%);
        }
        .bb-btn:hover { filter: brightness(1.05); }
        .bb-btn:active { transform: translateY(3px); box-shadow: 0 2px 0 var(--accent-dark); }

        .bb-google {
            width: 100%;
            height: 50px;
            border-radius: 18px;
            border: 3px solid var(--field-border);
            background: #FFFFFF;
            color: #0D2B4E;
            font-size: 15.5px;
            font-weight: 900;
            font-family: 'Nunito', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 4px 0 var(--card-shadow);
            transition: background 0.15s, transform 0.1s;
            text-decoration: none;
        }
        body.theme-teacher .bb-google { border-radius: 13px; }
        .bb-google:hover { background: var(--field-bg); }
        .bb-google:active { transform: translateY(2px); box-shadow: 0 2px 0 var(--card-shadow); }

        .bb-div {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 1.15rem 0;
            color: #8BA8C7;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .bb-div::before, .bb-div::after {
            content: '';
            flex: 1;
            border-top: 2px dashed var(--field-border);
        }

        .bb-foot {
            text-align: center;
            font-size: 14.5px;
            font-weight: 800;
            color: #5A7696;
            margin-top: 1.15rem;
        }
        .bb-foot a {
            color: var(--accent);
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }
        .bb-foot a:hover { text-decoration: underline; }

        .bb-forgot {
            font-size: 12.5px;
            font-weight: 900;
            color: var(--accent);
            text-decoration: none;
        }
        .bb-forgot:hover { text-decoration: underline; }

        .bb-alert {
            padding: 12px 15px;
            border-radius: 16px;
            font-size: 13.5px;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        .bb-alert.success { background: #ECFCCB; color: #365314; border: 2px solid #84CC16; }
        .bb-alert.error { background: #FFF7ED; color: #9A3412; border: 2px solid #FB923C; }

        .role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .role-btn {
            border: 3px solid var(--field-border);
            border-radius: 20px;
            padding: 12px 10px;
            text-align: center;
            cursor: pointer;
            background: var(--field-bg);
            transition: all 0.2s ease;
        }
        body.theme-teacher .role-btn { border-radius: 14px; }
        .role-btn:hover { border-color: var(--accent); }
        .role-btn.selected {
            border-color: var(--accent);
            background: var(--accent-soft);
            box-shadow: 0 4px 0 var(--role-shadow);
            transform: translateY(-2px);
        }
        .role-emoji { font-size: 26px; margin-bottom: 3px; }
        .role-name { font-size: 15px; font-weight: 900; color: #0D2B4E; }
        .role-desc { font-size: 12px; font-weight: 700; color: #5A7696; }

        .name-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        @media (max-width: 480px) { .name-grid { grid-template-columns: 1fr; } }

        .bb-panel { display: none; width: 100%; }
        .bb-panel.active { display: block; }

        /* Sections & Interactive Curriculum */
        .bb-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3.5rem 1.25rem;
        }

        .grade-pill-btn {
            padding: 0.55rem 1.15rem;
            border-radius: 16px;
            font-weight: 900;
            font-size: 14px;
            border: 3px solid #B8E2F8;
            background: #FFFFFF;
            color: #0D2B4E;
            box-shadow: 0 4px 0 #D4F1FC;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .grade-pill-btn:hover { transform: translateY(-2px); border-color: #1DC4DF; }
        .grade-pill-btn.active {
            background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);
            border-color: #004FA8;
            color: #FFFFFF;
            box-shadow: 0 4px 0 #004FA8;
        }

        .quiz-opt-btn {
            width: 100%;
            text-align: left;
            padding: 0.85rem 1.1rem;
            border-radius: 16px;
            border: 3px solid #B8E2F8;
            background: #F4FAFF;
            font-weight: 800;
            font-size: 15px;
            color: #0D2B4E;
            box-shadow: 0 4px 0 #D4F1FC;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .quiz-opt-btn:hover:not(:disabled) {
            border-color: #0066D6;
            background: #E6F7FC;
            transform: translateY(-2px);
        }
        .quiz-opt-btn.correct {
            background: #ECFCCB;
            border-color: #68CC18;
            color: #274E08;
            box-shadow: 0 4px 0 #4FA30D;
        }
        .quiz-opt-btn.wrong {
            background: #FFF1E0;
            border-color: #FF8400;
            color: #7C2D12;
            box-shadow: 0 4px 0 #D96B00;
        }

        @keyframes chestShake {
            0%, 100% { transform: rotate(0deg) scale(1); }
            25% { transform: rotate(-5deg) scale(1.04); }
            50% { transform: rotate(5deg) scale(1.06); }
            75% { transform: rotate(-3deg) scale(1.03); }
        }
        .chest-shake { animation: chestShake 0.45s ease-in-out; }
    </style>
</head>
<body class="theme-student">
    @php $activeTab = old('active_tab', session('active_tab', 'login')); @endphp

    {{-- =====================================================================
         TOP SPECTRUM BAR & NAVIGATION
         ===================================================================== --}}
    <header class="bb-nav">
        <div class="bb-spectrum-bar"></div>
        <div class="bb-nav-inner">
            <x-brand-logo :href="url('/')" :size="44" />

            <nav class="bb-nav-links">
                <a href="#features">How It Works</a>
                <a href="#curriculum">Grades 1–6 Quests</a>
                <a href="#rewards">Medal Box &amp; Rewards</a>
            </nav>
        </div>
    </header>

    {{-- =====================================================================
         HERO SECTION
         ===================================================================== --}}
    <section class="bb-hero">
        {{-- Left Column: Student-Friendly & Professional Value Pitch --}}
        <div style="display:flex;flex-direction:column;gap:1.35rem;">
            <div style="display:inline-flex;align-items:center;gap:8px;align-self:flex-start;background:#FFFFFF;border:2.5px solid #99E2F5;color:#0066D6;font-size:12.5px;font-weight:900;padding:6px 15px;border-radius:999px;box-shadow:0 4px 0 #D4F1FC;">
                <span>🎓</span>
                <span>ELEMENTARY MATH &amp; ENGLISH PLATFORM • GRADES 1–6</span>
            </div>

            <h1 class="font-display" style="font-size:clamp(34px, 4.4vw, 52px);line-height:1.12;color:#0D2B4E;">
                Where Focused Study Meets <span style="color:#1DC4DF;">Playful</span> <span style="color:#68CC18;">Discovery</span><span style="color:#FF8400;">.</span>
            </h1>

            <p style="font-size:17px;font-weight:700;color:#4A688A;line-height:1.6;max-width:560px;">
                BrainBalance pairs structured Grade 1–6 Math and English modules with smart adaptive quizzes, an AI Study Buddy, and quiz-scoped fastest-time medals stored inside every student’s Treasure Chest.
            </p>

            {{-- Visual Preview Strip Matched to Blue, Lime & Orange --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(165px, 1fr));gap:0.85rem;margin-top:0.25rem;">
                <div class="card-tactile" style="padding:1rem;display:flex;align-items:center;gap:0.75rem;border-color:#93C5FD !important;">
                    <img src="{{ asset('images/1stplace.png') }}" alt="Perfect Score Medal" style="width:46px;height:46px;object-fit:contain;flex-shrink:0;">
                    <div>
                        <div style="font-size:14px;font-weight:900;color:#0066D6;">Mastery Ribbons</div>
                        <div style="font-size:12px;font-weight:700;color:#5A7696;">Score &amp; speed awards</div>
                    </div>
                </div>

                <div class="card-tactile" style="padding:1rem;display:flex;align-items:center;gap:0.75rem;border-color:#A3E635 !important;">
                    <img src="{{ asset('images/fastestbadge.png') }}" alt="Fastest Time Badge" style="width:46px;height:46px;object-fit:contain;flex-shrink:0;">
                    <div>
                        <div style="font-size:14px;font-weight:900;color:#4FA30D;">Speed Records</div>
                        <div style="font-size:12px;font-weight:700;color:#5A7696;">Beat your best time</div>
                    </div>
                </div>

                <div class="card-tactile" style="padding:1rem;display:flex;align-items:center;gap:0.75rem;border-color:#FDBA74 !important;">
                    <img src="{{ asset('images/medalbox.png') }}" alt="Treasure Chest" style="width:46px;height:46px;object-fit:contain;flex-shrink:0;">
                    <div>
                        <div style="font-size:14px;font-weight:900;color:#D96B00;">Medal Box</div>
                        <div style="font-size:12px;font-weight:700;color:#5A7696;">Collectible chest UI</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Student & Teacher Auth Portal Card --}}
        <div id="portal-card" style="width:100%;max-width:490px;margin:0 auto;">
            <div class="bb-card">
                <div style="display:flex;justify-content:center;margin-bottom:1rem;">
                    <x-brand-logo :href="url('/')" :size="54" />
                </div>

                {{-- LOGIN PANEL --}}
                <div class="bb-panel active" id="panel-login">
                    @if (session('status'))
                        <div class="bb-alert success">{{ session('status') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="bb-alert error">{{ session('error') }}</div>
                    @endif
                    @if ($errors->any() && $activeTab === 'login')
                        <div class="bb-alert error">{{ $errors->first() }}</div>
                    @endif

                    <div style="text-align:center;">
                        <span class="bb-pill">🎒 Student &amp; Teacher Portal</span>
                        <div class="bb-heading">Welcome back! 👋</div>
                        <div class="bb-sub">Log in to enter your classroom &amp; medal box</div>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="active_tab" value="login">

                        <div class="bb-field">
                            <label for="login-email" class="bb-label">Email</label>
                            <div class="bb-iwrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
                                <input id="login-email" name="email" type="email" class="bb-input {{ $errors->has('email') && $activeTab === 'login' ? 'is-invalid' : '' }}" value="{{ $activeTab === 'login' ? old('email') : '' }}" placeholder="you@school.edu" required autocomplete="username">
                            </div>
                        </div>

                        <div class="bb-field">
                            <div class="bb-lrow">
                                <label for="login-password" class="bb-label" style="margin-bottom:0">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="bb-forgot">Forgot password?</a>
                                @endif
                            </div>
                            <div class="bb-iwrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <input id="login-password" name="password" type="password" class="bb-input" style="padding-right: 62px;" placeholder="Your password" required autocomplete="current-password">
                                <button type="button" class="bb-toggle-pw" onclick="togglePw('login-password', this)">Show</button>
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;gap:9px;margin-bottom:1rem">
                            <input type="checkbox" id="remember_me" name="remember" style="accent-color:#0066D6;width:18px;height:18px;cursor:pointer">
                            <label for="remember_me" style="font-size:14px;font-weight:800;color:#5A7696;cursor:pointer">Keep me logged in</label>
                        </div>

                        <button type="submit" class="bb-btn">🚀 Log in</button>
                    </form>

                    <div class="bb-div">or</div>

                    <a href="{{ route('auth.google') }}" class="bb-google">
                        <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Continue with Google
                    </a>

                    <p class="bb-foot">New here? <a onclick="switchTab('register')">Create an account &rarr;</a></p>
                </div>

                {{-- REGISTER PANEL --}}
                <div class="bb-panel" id="panel-register">
                    @if ($errors->any() && $activeTab === 'register')
                        <div class="bb-alert error">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div style="text-align:center;margin-bottom:1.1rem;">
                        <span class="bb-pill" id="register-badge">🎒 Student Quest Account</span>
                        <div class="bb-heading" id="register-heading">Join BrainBalance!</div>
                    </div>

                    <form method="POST" action="{{ route('register.store') }}">
                        @csrf
                        <input type="hidden" name="active_tab" value="register">

                        <div class="bb-field">
                            <label class="bb-label">I am a...</label>
                            <div class="role-grid">
                                <div class="role-btn selected" id="role-student" onclick="selectRole('student')">
                                    <div class="role-emoji">🎒</div>
                                    <div class="role-name">Student</div>
                                    <div class="role-desc">Learn &amp; earn medals</div>
                                </div>
                                <div class="role-btn" id="role-teacher" onclick="selectRole('teacher')">
                                    <div class="role-emoji">🧑‍🏫</div>
                                    <div class="role-name">Teacher</div>
                                    <div class="role-desc">Manage classes</div>
                                </div>
                            </div>
                            <input type="hidden" name="role" id="role-input" value="{{ old('role', 'student') }}">
                        </div>

                        <div class="name-grid" style="margin-bottom:1rem">
                            <div>
                                <label for="first_name" class="bb-label">First name</label>
                                <div class="bb-iwrap">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                    <input id="first_name" name="first_name" type="text" class="bb-input {{ $errors->has('first_name') ? 'is-invalid' : '' }}" value="{{ old('first_name') }}" placeholder="Maria" required autocomplete="given-name">
                                </div>
                                @error('first_name') <span class="bb-error">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="last_name" class="bb-label">Last name</label>
                                <div class="bb-iwrap">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                    <input id="last_name" name="last_name" type="text" class="bb-input {{ $errors->has('last_name') ? 'is-invalid' : '' }}" value="{{ old('last_name') }}" placeholder="Santos" required autocomplete="family-name">
                                </div>
                                @error('last_name') <span class="bb-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="bb-field">
                            <label for="reg-email" class="bb-label" id="reg-email-label">Email</label>
                            <div class="bb-iwrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
                                <input id="reg-email" name="email" type="email" class="bb-input {{ $errors->has('email') && $activeTab === 'register' ? 'is-invalid' : '' }}" value="{{ $activeTab === 'register' ? old('email') : '' }}" placeholder="you@school.edu" required autocomplete="username">
                            </div>
                            @error('email') <span class="bb-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="bb-field">
                            <label for="reg-password" class="bb-label">Password</label>
                            <div class="bb-iwrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <input id="reg-password" name="password" type="password" class="bb-input {{ $errors->has('password') ? 'is-invalid' : '' }}" style="padding-right: 62px;" placeholder="Min. 8 characters" required autocomplete="new-password">
                                <button type="button" class="bb-toggle-pw" onclick="togglePw('reg-password', this)">Show</button>
                            </div>
                            @error('password') <span class="bb-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="bb-field">
                            <label for="password_confirmation" class="bb-label">Confirm password</label>
                            <div class="bb-iwrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="bb-input" placeholder="Repeat your password" required autocomplete="new-password">
                            </div>
                        </div>

                        <button type="submit" class="bb-btn" id="register-submit-btn">🚀 Start Learning</button>
                    </form>

                    <div class="bb-div">or</div>

                    <a href="{{ route('auth.google') }}" class="bb-google">
                        <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Continue with Google
                    </a>

                    <p class="bb-foot">Already have an account? <a onclick="switchTab('login')">Log in &rarr;</a></p>
                </div>
            </div>
        </div>
    </section>

    {{-- =====================================================================
         3-PILLAR FEATURES SECTION (Blue, Lime & Orange Pillars)
         ===================================================================== --}}
    <section id="features" class="bb-section">
        <div style="text-align:center;max-width:660px;margin:0 auto 2.5rem;">
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1.5rem;">
            <div class="card-tactile" style="padding:1.75rem;border-color:#93C5FD !important;">
                <div style="width:54px;height:54px;border-radius:16px;background:linear-gradient(135deg, #E0F2FE 0%, #CFFAFE 100%);border:3px solid #1DC4DF;display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:1rem;">
                    🏫
                </div>
                <h3 class="font-display" style="font-size:21px;color:#0066D6;margin-bottom:6px;">Classroom Workspaces</h3>
                <p style="font-size:14.5px;font-weight:700;color:#4A688A;line-height:1.55;">
                    Join Math and English classes with a class code, read teacher announcements, and track assigned quests in one organized hub.
                </p>
            </div>

            <div class="card-tactile" style="padding:1.75rem;border-color:#BEF264 !important;">
                <div style="width:54px;height:54px;border-radius:16px;background:#ECFCCB;border:3px solid #68CC18;display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:1rem;">
                    🤖
                </div>
                <h3 class="font-display" style="font-size:21px;color:#4FA30D;margin-bottom:6px;">AI Study Buddy &amp; Review</h3>
                <p style="font-size:14.5px;font-weight:700;color:#4A688A;line-height:1.55;">
                    Ask questions while reading lesson modules and review every quiz attempt step-by-step to turn mistakes into mastery.
                </p>
            </div>

            <div class="card-tactile" style="padding:1.75rem;border-color:#FDBA74 !important;">
                <div style="width:54px;height:54px;border-radius:16px;background:#FFF7ED;border:3px solid #FF8400;display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:1rem;">
                    ⚡
                </div>
                <h3 class="font-display" style="font-size:21px;color:#D96B00;margin-bottom:6px;">Fastest-Time Medal Box</h3>
                <p style="font-size:14.5px;font-weight:700;color:#4A688A;line-height:1.55;">
                    Every quiz tracks accuracy and completion speed, unlocking ribbons inside your Treasure Chest without stressful public leaderboards.
                </p>
            </div>
        </div>
    </section>

    {{-- =====================================================================
         INTERACTIVE GRADE 1–6 CURRICULUM & MINI-QUEST PREVIEW
         ===================================================================== --}}
    <section id="curriculum" class="bb-section" style="padding-top:1rem;">
        <div class="card-tactile" style="padding:2.25rem 1.75rem;border-color:#99E2F5 !important;">
            <div style="text-align:center;max-width:650px;margin:0 auto 1.75rem;">
                <span class="bb-pill">📚 Grades 1–6 Explorer</span>
                <h2 class="font-display" style="font-size:clamp(26px, 3.6vw, 36px);color:#0D2B4E;">
                    Preview Lessons &amp; Try a Quick Quest!
                </h2>
                <p style="font-size:15px;font-weight:700;color:#4A688A;margin-top:6px;">
                    Select any grade level to inspect Math &amp; English topics and try a timed sample question.
                </p>
            </div>

            <div id="grade-tabs" style="display:flex;flex-wrap:wrap;justify-content:center;gap:0.6rem;margin-bottom:2rem;">
                <button type="button" class="grade-pill-btn active" onclick="selectGrade(1)">Grade 1</button>
                <button type="button" class="grade-pill-btn" onclick="selectGrade(2)">Grade 2</button>
                <button type="button" class="grade-pill-btn" onclick="selectGrade(3)">Grade 3</button>
                <button type="button" class="grade-pill-btn" onclick="selectGrade(4)">Grade 4</button>
                <button type="button" class="grade-pill-btn" onclick="selectGrade(5)">Grade 5</button>
                <button type="button" class="grade-pill-btn" onclick="selectGrade(6)">Grade 6</button>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:1.5rem;">
                <div style="display:flex;flex-direction:column;gap:1rem;">
                    <div style="background:linear-gradient(135deg, #EBF5FF 0%, #E0F7FC 100%);border:3px solid #38BDF8;border-radius:22px;padding:1.35rem;">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:0.75rem;">
                            <span style="font-size:26px;">🔢</span>
                            <div>
                                <div style="font-size:12px;font-weight:900;color:#0066D6;text-transform:uppercase;">Mathematics Track</div>
                                <h3 class="font-display" id="math-grade-title" style="font-size:20px;color:#0D2B4E;">Grade 1 Math Skills</h3>
                            </div>
                        </div>
                        <ul id="math-topics" style="list-style:none;display:flex;flex-direction:column;gap:8px;font-size:14.5px;font-weight:800;color:#163A63;"></ul>
                    </div>

                    <div style="background:linear-gradient(135deg, #F4FCE3 0%, #ECFCCB 100%);border:3px solid #68CC18;border-radius:22px;padding:1.35rem;">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:0.75rem;">
                            <span style="font-size:26px;">📖</span>
                            <div>
                                <div style="font-size:12px;font-weight:900;color:#4FA30D;text-transform:uppercase;">English Track</div>
                                <h3 class="font-display" id="eng-grade-title" style="font-size:20px;color:#0D2B4E;">Grade 1 English Skills</h3>
                            </div>
                        </div>
                        <ul id="eng-topics" style="list-style:none;display:flex;flex-direction:column;gap:8px;font-size:14.5px;font-weight:800;color:#163A63;"></ul>
                    </div>
                </div>

                <div style="background:#FFFFFF;border:3px solid #B8E2F8;border-radius:24px;padding:1.5rem;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 8px 0 #D4F1FC;">
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:1rem;flex-wrap:wrap;">
                            <div style="display:flex;gap:6px;">
                                <button type="button" id="subj-math-btn" onclick="switchSubject('math')" style="padding:6px 14px;font-size:13px;border-radius:14px;border:2px solid #004FA8;background:linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);font-weight:900;color:#FFFFFF;cursor:pointer;">🔢 Math Quest</button>
                                <button type="button" id="subj-eng-btn" onclick="switchSubject('eng')" style="padding:6px 14px;font-size:13px;border-radius:14px;border:2px solid #B8E2F8;background:#F2FAFF;font-weight:900;color:#0D2B4E;cursor:pointer;">📖 English Quest</button>
                            </div>
                            <span id="quest-timer" style="background:#FFF7ED;border:2px solid #FF8400;color:#C2410C;font-size:13px;font-weight:900;padding:4px 12px;border-radius:999px;">
                                ⏱️ 0.0s
                            </span>
                        </div>

                        <div style="background:#F2FAFF;border:2px dashed #1DC4DF;border-radius:18px;padding:1.1rem;margin-bottom:1rem;">
                            <div style="font-size:12px;font-weight:900;color:#0066D6;text-transform:uppercase;margin-bottom:4px;" id="quest-label">Grade 1 • Quick Challenge</div>
                            <p id="quest-question" style="font-size:17.5px;font-weight:900;color:#0D2B4E;line-height:1.4;"></p>
                        </div>

                        <div id="quest-options" style="display:flex;flex-direction:column;gap:0.65rem;"></div>
                    </div>

                    <div id="quest-feedback" style="margin-top:1rem;min-height:48px;display:flex;align-items:center;justify-content:center;text-align:center;font-size:14px;font-weight:900;border-radius:14px;padding:0.65rem 1rem;background:#F4FAFF;color:#5A7696;">
                        Pick the right answer as fast as you can! ⚡
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =====================================================================
         TREASURE CHEST & MEDAL SHOWCASE (Warm Orange + Cyan + Lime Harmony)
         ===================================================================== --}}
    <section id="rewards" class="bb-section" style="padding-top:1rem;padding-bottom:4.5rem;">
        <div class="card-tactile" style="background:linear-gradient(135deg, #FFF5E6 0%, #FFE8CC 42%, #E0F7FC 100%) !important;border-color:#FF8400 !important;box-shadow:0 10px 0 #FED7AA !important;padding:2.5rem 2rem;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(290px, 1fr));gap:2rem;align-items:center;">
                {{-- Interactive Treasure Chest --}}
                <div style="text-align:center;">
                    <div style="display:inline-block;cursor:pointer;" onclick="toggleTreasureChest()">
                        <img id="landing-chest-img"
                             src="{{ asset('images/medalbox.png') }}"
                             data-closed="{{ asset('images/medalbox.png') }}"
                             data-open="{{ asset('images/chestopen.png') }}"
                             alt="BrainBalance Treasure Chest"
                             style="width:185px;height:185px;object-fit:contain;margin:0 auto;filter:drop-shadow(0 12px 18px rgba(217, 107, 0, 0.24));">
                    </div>
                    <div style="margin-top:0.85rem;">
                        <button type="button" onclick="toggleTreasureChest()" style="padding:0.7rem 1.45rem;font-size:15px;font-weight:900;color:#FFFFFF;background:linear-gradient(135deg, #FF8400 0%, #FFA31A 100%);border:2px solid #D96B00;border-radius:18px;box-shadow:0 5px 0 #D96B00;cursor:pointer;" id="chest-toggle-btn">
                            ✨ Tap to Open Treasure Chest!
                        </button>
                    </div>
                    <p id="chest-status-text" style="font-size:13.5px;font-weight:800;color:#9A3412;margin-top:0.65rem;">
                        Click the chest to peek at the ribbons waiting inside!
                    </p>
                </div>

                {{-- All 4 Medal Badges Showcase --}}
                <div>
                    <span style="display:inline-flex;align-items:center;gap:6px;background:#FFFFFF;border:2px solid #FF8400;color:#C2410C;font-size:12px;font-weight:900;padding:4px 14px;border-radius:999px;margin-bottom:10px;">
                        🏅 QUIZ-SCOPED REWARD SYSTEM
                    </span>
                    <h2 class="font-display" style="font-size:clamp(26px, 3.5vw, 36px);color:#0D2B4E;line-height:1.15;margin-bottom:0.75rem;">
                        Collect Ribbons in Your Personal Medal Box
                    </h2>
                    <p style="font-size:15px;font-weight:700;color:#475569;line-height:1.55;margin-bottom:1.35rem;">
                        Students unlock ribbons for accuracy, completion, and setting personal fastest-time records on every quiz.
                    </p>

                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(120px, 1fr));gap:0.75rem;">
                        <div style="background:#FFFFFF;border:3px solid #38BDF8;border-radius:20px;padding:0.85rem 0.5rem;text-align:center;box-shadow:0 5px 0 #BAE6FD;">
                            <img src="{{ asset('images/1stplace.png') }}" alt="Perfect Score Medal" style="width:72px;height:72px;object-fit:contain;margin:0 auto 6px;">
                            <div style="font-size:13px;font-weight:900;color:#0066D6;">Perfect Score!</div>
                            <div style="font-size:11px;font-weight:700;color:#5A7696;">100% Accuracy</div>
                        </div>

                        <div style="background:#FFFFFF;border:3px solid #68CC18;border-radius:20px;padding:0.85rem 0.5rem;text-align:center;box-shadow:0 5px 0 #D9F99D;">
                            <img src="{{ asset('images/fastestbadge.png') }}" alt="You're Fast Medal" style="width:72px;height:72px;object-fit:contain;margin:0 auto 6px;">
                            <div style="font-size:13px;font-weight:900;color:#4FA30D;">You're Fast!</div>
                            <div style="font-size:11px;font-weight:700;color:#5A7696;">Fastest Time</div>
                        </div>

                        <div style="background:#FFFFFF;border:3px solid #FF8400;border-radius:20px;padding:0.85rem 0.5rem;text-align:center;box-shadow:0 5px 0 #FED7AA;">
                            <img src="{{ asset('images/2ndplace.png') }}" alt="Nice Try Medal" style="width:72px;height:72px;object-fit:contain;margin:0 auto 6px;">
                            <div style="font-size:13px;font-weight:900;color:#D96B00;">Nice Try!</div>
                            <div style="font-size:11px;font-weight:700;color:#5A7696;">Keep Practicing</div>
                        </div>

                        <div style="background:#FFFFFF;border:3px solid #1DC4DF;border-radius:20px;padding:0.85rem 0.5rem;text-align:center;box-shadow:0 5px 0 #A5F3FC;">
                            <img src="{{ asset('images/3rdplace.png') }}" alt="Great Work Medal" style="width:72px;height:72px;object-fit:contain;margin:0 auto 6px;">
                            <div style="font-size:13px;font-weight:900;color:#0E9EB8;">Great Work!</div>
                            <div style="font-size:11px;font-weight:700;color:#5A7696;">Quest Completed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =====================================================================
         FOOTER
         ===================================================================== --}}
    <footer style="background:#FFFFFF;border-top:3px solid #B8E2F8;padding:2rem 1.25rem 0;text-align:center;">
        <div style="max-width:1200px;margin:0 auto;display:flex;flex-direction:column;align-items:center;gap:0.75rem;padding-bottom:1.75rem;">
            <x-brand-logo :href="url('/')" :size="40" />
            <p style="font-size:13.5px;font-weight:700;color:#5A7696;">
                Adaptive Math &amp; English Learning Platform for Grades 1–6
            </p>
            <div style="display:flex;gap:1.25rem;font-size:13px;font-weight:800;">
                <a href="{{ route('terms') }}" style="color:#0066D6;text-decoration:none;">Terms of Service</a>
                <a href="{{ route('privacy') }}" style="color:#4FA30D;text-decoration:none;">Privacy Policy</a>
                <a href="#portal-card" style="color:#D96B00;text-decoration:none;">Back to Top ↑</a>
            </div>
        </div>
        <div class="bb-spectrum-bar"></div>
    </footer>

    <script>
        function switchTab(tab) {
            document.getElementById('panel-login').classList.toggle('active', tab === 'login');
            document.getElementById('panel-register').classList.toggle('active', tab === 'register');
            if (tab === 'login') {
                document.body.classList.remove('theme-teacher');
                document.body.classList.add('theme-student');
            } else {
                selectRole(document.getElementById('role-input').value || 'student');
            }
        }

        function selectRole(role) {
            const isTeacher = role === 'teacher';
            document.getElementById('role-student').classList.toggle('selected', !isTeacher);
            document.getElementById('role-teacher').classList.toggle('selected', isTeacher);
            document.getElementById('role-input').value = role;
            document.body.classList.toggle('theme-teacher', isTeacher);
            document.body.classList.toggle('theme-student', !isTeacher);

            const heading = document.getElementById('register-heading');
            const badge = document.getElementById('register-badge');
            const emailLabel = document.getElementById('reg-email-label');
            const emailInput = document.getElementById('reg-email');
            const submitBtn = document.getElementById('register-submit-btn');

            if (isTeacher) {
                if (heading) heading.textContent = 'Educator Setup 🧑‍🏫';
                if (badge) {
                    badge.textContent = '📚 Classroom & AI Quiz Tools';
                    badge.style.background = '#ECFCCB';
                    badge.style.borderColor = '#A3E635';
                    badge.style.color = '#3E8708';
                }
                if (emailLabel) emailLabel.textContent = 'School Email';
                if (emailInput) emailInput.placeholder = 'teacher@school.edu';
                if (submitBtn) submitBtn.textContent = '🎓 Create Teacher Account';
            } else {
                if (heading) heading.textContent = 'Join BrainBalance!';
                if (badge) {
                    badge.textContent = '🎒 Student Quest Account';
                    badge.style.background = '#E6F7FC';
                    badge.style.borderColor = '#99E2F5';
                    badge.style.color = '#0066D6';
                }
                if (emailLabel) emailLabel.textContent = 'Email';
                if (emailInput) emailInput.placeholder = 'student@school.edu';
                if (submitBtn) submitBtn.textContent = '🚀 Start Learning';
            }
        }

        function togglePw(id, btn) {
            const el = document.getElementById(id);
            if (!el) return;
            const show = el.type === 'password';
            el.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Hide' : 'Show';
        }

        const gradeData = {
            1: {
                math: ['Counting & Place Value up to 100', 'Addition & Subtraction Stories', '2D Shapes & Measurement'],
                eng: ['Phonics & Sight Word Builders', 'Nouns, Verbs & Simple Sentences', 'Short Story Comprehension'],
                qMath: { q: 'Mia has 7 red apples and 5 green apples. How many apples does she have in all?', opts: ['11 apples', '12 apples', '13 apples'], ans: 1 },
                qEng:  { q: 'Which word is an action word (verb) in: "The happy puppy jumps high."?', opts: ['happy', 'puppy', 'jumps'], ans: 2 }
            },
            2: {
                math: ['3-Digit Place Value & Skip Counting', '2-Digit Addition & Regrouping', 'Telling Time & Counting Coins'],
                eng: ['Adjectives & Compound Words', 'Reading Fables & Main Idea', 'Capitalization & Punctuation'],
                qMath: { q: 'What is 45 + 28?', opts: ['63', '73', '83'], ans: 1 },
                qEng:  { q: 'Choose the correct plural noun: "Two little ___ ran across the grass."', opts: ['mouses', 'mice', 'mices'], ans: 1 }
            },
            3: {
                math: ['Multiplication & Division Facts', 'Understanding Fractions on a Number Line', 'Area & Perimeter of Rectangles'],
                eng: ['Prefixes, Suffixes & Root Words', 'Character Traits & Cause and Effect', 'Writing Strong Paragraphs'],
                qMath: { q: 'A garden has 4 rows with 6 sunflowers in each row. How many sunflowers are there?', opts: ['20', '24', '28'], ans: 1 },
                qEng:  { q: 'What does the prefix "re-" mean in the word "rewrite"?', opts: ['Again', 'Before', 'Not'], ans: 0 }
            },
            4: {
                math: ['Multi-Digit Multiplication & Long Division', 'Equivalent Fractions & Comparing Decimals', 'Angles, Symmetry & Line Plots'],
                eng: ['Context Clues & Figurative Language', 'Comparing First- & Third-Person Views', 'Opinion & Informational Essays'],
                qMath: { q: 'Which fraction is equivalent to 1/2?', opts: ['2/6', '3/6', '4/6'], ans: 1 },
                qEng:  { q: '"The classroom was a beehive of activity." This sentence uses a:', opts: ['Simile', 'Metaphor', 'Alliteration'], ans: 1 }
            },
            5: {
                math: ['Adding & Subtracting Unlike Fractions', 'Decimal Operations to Thousandths', 'Volume of Rectangular Prisms'],
                eng: ['Summarizing & Theme Analysis', 'Conjunctions & Perfect Verb Tenses', 'Analyzing Multiple Texts'],
                qMath: { q: 'What is 3/4 + 1/8?', opts: ['4/12', '7/8', '5/8'], ans: 1 },
                qEng:  { q: 'Which conjunction best completes: "We packed our umbrellas ___ it looked cloudy."?', opts: ['because', 'unless', 'although'], ans: 0 }
            },
            6: {
                math: ['Ratios, Unit Rates & Percentages', 'Intro to Variables & One-Step Equations', 'Negative Numbers & Coordinate Plane'],
                eng: ['Evaluating Arguments & Evidence', 'Pronoun Case & Sentence Variety', 'Narrative & Research Writing'],
                qMath: { q: 'If 3 notebooks cost $12, how much do 5 notebooks cost at the same rate?', opts: ['$15', '$18', '$20'], ans: 2 },
                qEng:  { q: 'Identify the main purpose of evidence in an argumentative essay:', opts: ['To entertain the reader', 'To support the author’s claim', 'To list random facts'], ans: 1 }
            }
        };

        let currentGrade = 1, currentSubject = 'math', questStartTime = Date.now(), timerInterval = null, questAnswered = false;

        function startQuestTimer() {
            clearInterval(timerInterval);
            questStartTime = Date.now();
            questAnswered = false;
            const timerEl = document.getElementById('quest-timer');
            timerInterval = setInterval(() => {
                if (questAnswered) return;
                timerEl.textContent = `⏱️ ${((Date.now() - questStartTime) / 1000).toFixed(1)}s`;
            }, 100);
        }

        function selectGrade(grade) {
            currentGrade = grade;
            document.querySelectorAll('#grade-tabs .grade-pill-btn').forEach((btn, idx) => btn.classList.toggle('active', idx + 1 === grade));
            const data = gradeData[grade];
            document.getElementById('math-grade-title').textContent = `Grade ${grade} Math Skills`;
            document.getElementById('eng-grade-title').textContent = `Grade ${grade} English Skills`;
            document.getElementById('math-topics').innerHTML = data.math.map(i => `<li>✅ ${i}</li>`).join('');
            document.getElementById('eng-topics').innerHTML = data.eng.map(i => `<li>✅ ${i}</li>`).join('');
            renderMiniQuest();
        }

        function switchSubject(subj) {
            currentSubject = subj;
            const mathBtn = document.getElementById('subj-math-btn');
            const engBtn = document.getElementById('subj-eng-btn');
            if (subj === 'math') {
                mathBtn.style.cssText = 'padding:6px 14px;font-size:13px;border-radius:14px;border:2px solid #004FA8;background:linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);font-weight:900;color:#FFFFFF;cursor:pointer;';
                engBtn.style.cssText = 'padding:6px 14px;font-size:13px;border-radius:14px;border:2px solid #B8E2F8;background:#F2FAFF;font-weight:900;color:#0D2B4E;cursor:pointer;';
            } else {
                engBtn.style.cssText = 'padding:6px 14px;font-size:13px;border-radius:14px;border:2px solid #4FA30D;background:linear-gradient(135deg, #55B310 0%, #68CC18 100%);font-weight:900;color:#FFFFFF;cursor:pointer;';
                mathBtn.style.cssText = 'padding:6px 14px;font-size:13px;border-radius:14px;border:2px solid #B8E2F8;background:#F2FAFF;font-weight:900;color:#0D2B4E;cursor:pointer;';
            }
            renderMiniQuest();
        }

        function renderMiniQuest() {
            const data = gradeData[currentGrade];
            const qObj = currentSubject === 'math' ? data.qMath : data.qEng;
            document.getElementById('quest-label').textContent = `Grade ${currentGrade} • ${currentSubject === 'math' ? 'Math' : 'English'} Quick Challenge`;
            document.getElementById('quest-question').textContent = qObj.q;
            document.getElementById('quest-options').innerHTML = qObj.opts.map((opt, idx) => `
                <button type="button" class="quiz-opt-btn" onclick="checkMiniAnswer(${idx}, this)">
                    <span>${opt}</span>
                    <span style="font-size:12px;color:#7FA2C4;">Tap to answer</span>
                </button>
            `).join('');
            const fb = document.getElementById('quest-feedback');
            fb.style.background = '#F4FAFF';
            fb.style.color = '#5A7696';
            fb.textContent = 'Pick the right answer as fast as you can! ⚡';
            startQuestTimer();
        }

        function checkMiniAnswer(chosenIdx, btnEl) {
            if (questAnswered) return;
            questAnswered = true;
            clearInterval(timerInterval);
            const elapsed = ((Date.now() - questStartTime) / 1000).toFixed(1);
            const qObj = currentSubject === 'math' ? gradeData[currentGrade].qMath : gradeData[currentGrade].qEng;
            const buttons = document.querySelectorAll('#quest-options .quiz-opt-btn');
            const fb = document.getElementById('quest-feedback');
            buttons.forEach((b, i) => { b.disabled = true; if (i === qObj.ans) b.classList.add('correct'); });
            if (chosenIdx === qObj.ans) {
                fb.style.background = '#ECFCCB';
                fb.style.color = '#274E08';
                fb.textContent = `⚡ Solved in ${elapsed}s — "You're Fast!" Medal unlocked!`;
            } else {
                btnEl.classList.add('wrong');
                fb.style.background = '#FFF7ED';
                fb.style.color = '#9A3412';
                fb.textContent = 'Almost! The highlighted answer is correct. Try another grade or subject!';
            }
        }

        let chestIsOpen = false;
        function toggleTreasureChest() {
            const img = document.getElementById('landing-chest-img');
            const btn = document.getElementById('chest-toggle-btn');
            const status = document.getElementById('chest-status-text');
            chestIsOpen = !chestIsOpen;
            img.classList.remove('chest-shake');
            void img.offsetWidth;
            img.classList.add('chest-shake');
            if (chestIsOpen) {
                img.src = img.dataset.open;
                btn.textContent = '🔒 Close Treasure Chest';
                status.textContent = '🎉 Unlocked! Complete quizzes to fill your chest with ribbons!';
            } else {
                img.src = img.dataset.closed;
                btn.textContent = '✨ Tap to Open Treasure Chest!';
                status.textContent = 'Click the chest to peek at the ribbons waiting inside!';
            }
        }

        selectRole(document.getElementById('role-input').value || 'student');
        selectGrade(1);
        @if ($errors->any() && $activeTab === 'register') switchTab('register'); @else switchTab('login'); @endif
    </script>
</body>
</html>