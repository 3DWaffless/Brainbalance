{{-- FILE: resources/views/auth.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BrainBalance') }}</title>
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
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .bb-wrap { width: 100%; max-width: 420px; }

        .bb-tabs {
            display: flex;
            background: #fff;
            border: 2px solid #FFE4C4;
            border-radius: 16px;
            padding: 4px;
            margin-bottom: 1.25rem;
        }

        .bb-tab {
            flex: 1;
            padding: 9px 0;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 800;
            text-align: center;
            cursor: pointer;
            border: none;
            background: transparent;
            color: #bbb;
            transition: all 0.18s;
            font-family: 'Nunito', sans-serif;
        }

        .bb-tab.active { background: #FF6B6B; color: #fff; }
        .bb-tab:hover:not(.active) { color: #FF6B6B; }

        .bb-card {
            background: #fff;
            border: 2px solid #FFE4C4;
            border-radius: 24px;
            padding: 1.75rem 1.75rem 1.5rem;
        }

        .bb-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 1.25rem;
        }

        .bb-logo-icon {
            width: 44px;
            height: 44px;
            background: #FF6B6B;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .bb-logo-main { font-weight: 900; font-size: 18px; color: #2D2D2D; line-height: 1.1; }
        .bb-logo-sub { font-size: 10px; font-weight: 700; color: #bbb; letter-spacing: 0.6px; text-transform: uppercase; }

        .bb-heading { font-weight: 900; font-size: 22px; color: #2D2D2D; text-align: center; margin-bottom: 4px; }
        .bb-sub { font-size: 13px; color: #aaa; font-weight: 600; text-align: center; margin-bottom: 1.25rem; line-height: 1.5; }

        .bb-field { margin-bottom: 0.9rem; }
        .bb-label { display: block; font-size: 12px; font-weight: 800; color: #555; margin-bottom: 5px; }
        .bb-lrow { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }

        .bb-iwrap { position: relative; }
        .bb-iwrap svg { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #ccc; pointer-events: none; }

        .bb-input {
            width: 100%;
            height: 44px;
            padding: 0 14px 0 38px;
            border-radius: 12px;
            border: 2px solid #FFE4C4;
            background: #FFF8F0;
            color: #2D2D2D;
            font-size: 14px;
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            outline: none;
            transition: border-color 0.15s, background 0.15s;
        }

        .bb-input:focus { border-color: #FF6B6B; background: #fff; }
        .bb-input.is-invalid { border-color: #e74c3c; }

        .bb-error { font-size: 11px; font-weight: 700; color: #e74c3c; margin-top: 4px; display: block; }

        .bb-btn {
            width: 100%;
            height: 46px;
            border-radius: 14px;
            border: none;
            background: #FF6B6B;
            color: #fff;
            font-size: 15px;
            font-weight: 900;
            font-family: 'Nunito', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.15s, transform 0.1s;
            margin-top: 0.25rem;
        }

        .bb-btn:hover { background: #e85555; }
        .bb-btn:active { transform: scale(0.98); }

        .bb-google {
            width: 100%;
            height: 44px;
            border-radius: 14px;
            border: 2px solid #FFE4C4;
            background: #fff;
            color: #2D2D2D;
            font-size: 13px;
            font-weight: 800;
            font-family: 'Nunito', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.15s;
            text-decoration: none;
        }

        .bb-google:hover { background: #FFF8F0; }

        .bb-div {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 1rem 0;
            color: #ccc;
            font-size: 12px;
            font-weight: 700;
        }

        .bb-div::before, .bb-div::after { content: ''; flex: 1; height: 2px; background: #FFE4C4; }

        .bb-foot { text-align: center; font-size: 12px; font-weight: 700; color: #aaa; margin-top: 1.1rem; }
        .bb-foot a { color: #FF6B6B; font-weight: 900; text-decoration: none; cursor: pointer; }

        .bb-forgot { font-size: 11px; font-weight: 800; color: #FF6B6B; text-decoration: none; }

        .bb-alert { padding: 10px 14px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 1rem; }
        .bb-alert.success { background: #d1fae5; color: #065f46; border: 2px solid #6ee7b7; }
        .bb-alert.error { background: #fee2e2; color: #991b1b; border: 2px solid #fca5a5; }

        .xp-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #FFF3CD;
            color: #856404;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 1.1rem;
        }

        .role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 0.9rem; }

        .role-btn {
            border: 2px solid #FFE4C4;
            border-radius: 14px;
            padding: 10px 8px;
            text-align: center;
            cursor: pointer;
            background: #FFF8F0;
            transition: all 0.15s;
        }

        .role-btn:hover { border-color: #FF6B6B; }
        .role-btn.selected { border-color: #FF6B6B; background: #FFF0E8; }

        .role-emoji { font-size: 24px; margin-bottom: 4px; }
        .role-name { font-size: 13px; font-weight: 900; color: #2D2D2D; }
        .role-desc { font-size: 10px; font-weight: 700; color: #aaa; }

        .name-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .streak-row { display: flex; align-items: center; gap: 5px; justify-content: center; margin-bottom: 1rem; }
        .sdot { width: 9px; height: 9px; border-radius: 50%; background: #FFE4C4; }
        .sdot.lit { background: #FF6B6B; }
        .slbl { font-size: 11px; font-weight: 700; color: #aaa; margin-left: 3px; }

        .bb-terms { font-size: 11px; font-weight: 700; color: #bbb; text-align: center; margin-top: 0.9rem; line-height: 1.6; }
        .bb-terms a { color: #FF6B6B; text-decoration: none; }

        .bb-panel { display: none; }
        .bb-panel.active { display: block; }
    </style>
</head>
<body>
    <div class="bb-wrap">
        <div class="bb-tabs">
            <button class="bb-tab active" id="tab-login" onclick="switchTab('login')">Log in</button>
            <button class="bb-tab" id="tab-register" onclick="switchTab('register')">Sign up</button>
        </div>

        <div class="bb-card">
            <div class="bb-logo">
                <div class="bb-logo-icon">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                        <circle cx="11" cy="8" r="4" fill="white" opacity="0.95"/>
                        <circle cx="7" cy="14" r="3" fill="white" opacity="0.8"/>
                        <circle cx="15" cy="14" r="3" fill="white" opacity="0.8"/>
                        <line x1="11" y1="8" x2="7" y2="14" stroke="white" stroke-width="1.5" opacity="0.6"/>
                        <line x1="11" y1="8" x2="15" y2="14" stroke="white" stroke-width="1.5" opacity="0.6"/>
                    </svg>
                </div>
                <div>
                    <div class="bb-logo-main">BrainBalance</div>
                    <div class="bb-logo-sub">by Ekklessia</div>
                </div>
            </div>

            {{-- LOGIN PANEL --}}
            <div class="bb-panel active" id="panel-login">
                @if (session('status'))
                    <div class="bb-alert success">{{ session('status') }}</div>
                @endif

                @if ($errors->any() && session('active_tab') === 'login')
                    <div class="bb-alert error">{{ $errors->first() }}</div>
                @endif

                <div class="bb-heading">Welcome back! 👋</div>
                <div class="bb-sub">Your streak is waiting for you!</div>

                <div class="streak-row">
                    <span style="font-size:15px">🔥</span>
                    <div class="sdot lit"></div>
                    <div class="sdot lit"></div>
                    <div class="sdot lit"></div>
                    <div class="sdot"></div>
                    <div class="sdot"></div>
                    <span class="slbl">3-day streak</span>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="active_tab" value="login">

                    <div class="bb-field">
                        <label for="login-email" class="bb-label">Email</label>
                        <div class="bb-iwrap">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
                            <input id="login-email" name="email" type="email" class="bb-input {{ $errors->has('email') && session('active_tab') === 'login' ? 'is-invalid' : '' }}" value="{{ old('email') }}" placeholder="you@school.edu" required autocomplete="username">
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
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input id="login-password" name="password" type="password" class="bb-input" placeholder="Your password" required autocomplete="current-password">
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:0.9rem">
                        <input type="checkbox" id="remember_me" name="remember" style="accent-color:#FF6B6B;width:15px;height:15px;cursor:pointer">
                        <label for="remember_me" style="font-size:12px;font-weight:700;color:#aaa;cursor:pointer">Keep me logged in</label>
                    </div>

                    <button type="submit" class="bb-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        Log in
                    </button>
                </form>

                <div class="bb-div">or</div>

                <a href="{{ route('auth.google') }}" class="bb-google">
                    <svg width="16" height="16" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Continue with Google
                </a>

                <p class="bb-foot">New here? <a onclick="switchTab('register')">Create an account</a></p>
            </div>

            {{-- REGISTER PANEL --}}
            <div class="bb-panel" id="panel-register">
                @if ($errors->any() && session('active_tab') === 'register')
                    <div class="bb-alert error">{{ $errors->first() }}</div>
                @endif

                <div class="bb-heading">Join BrainBalance! 🚀</div>
                <div style="text-align:center;margin-bottom:1.1rem">
                    <span class="xp-badge">⭐ +50 XP on first login</span>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <input type="hidden" name="active_tab" value="register">

                    <div class="bb-field">
                        <label class="bb-label">I am a...</label>
                        <div class="role-grid">
                            <div class="role-btn selected" id="role-student" onclick="selectRole('student')">
                                <div class="role-emoji">🧠</div>
                                <div class="role-name">Student</div>
                                <div class="role-desc">Learn and earn XP</div>
                            </div>
                            <div class="role-btn" id="role-teacher" onclick="selectRole('teacher')">
                                <div class="role-emoji">📚</div>
                                <div class="role-name">Teacher</div>
                                <div class="role-desc">Manage classes</div>
                            </div>
                        </div>
                        <input type="hidden" name="role" id="role-input" value="student">
                    </div>

                    <div class="name-grid" style="margin-bottom:0.9rem">
                        <div>
                            <label for="first_name" class="bb-label">First name</label>
                            <div class="bb-iwrap">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                <input id="first_name" name="first_name" type="text" class="bb-input {{ $errors->has('first_name') ? 'is-invalid' : '' }}" value="{{ old('first_name') }}" placeholder="Maria" required autocomplete="given-name">
                            </div>
                            @error('first_name') <span class="bb-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="last_name" class="bb-label">Last name</label>
                            <div class="bb-iwrap">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                <input id="last_name" name="last_name" type="text" class="bb-input {{ $errors->has('last_name') ? 'is-invalid' : '' }}" value="{{ old('last_name') }}" placeholder="Santos" required autocomplete="family-name">
                            </div>
                            @error('last_name') <span class="bb-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="bb-field">
                        <label for="reg-email" class="bb-label">Email</label>
                        <div class="bb-iwrap">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
                            <input id="reg-email" name="email" type="email" class="bb-input {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}" placeholder="you@school.edu" required autocomplete="username">
                        </div>
                        @error('email') <span class="bb-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="bb-field">
                        <label for="reg-password" class="bb-label">Password</label>
                        <div class="bb-iwrap">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input id="reg-password" name="password" type="password" class="bb-input {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Min. 8 characters" required autocomplete="new-password">
                        </div>
                        @error('password') <span class="bb-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="bb-field">
                        <label for="password_confirmation" class="bb-label">Confirm password</label>
                        <div class="bb-iwrap">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="bb-input" placeholder="Repeat your password" required autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="bb-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 14l5-5-5-5"/><path d="M20 9H9a4 4 0 0 0-4 4v7"/></svg>
                        Create account
                    </button>
                </form>

                <div class="bb-div">or</div>

                <a href="{{ route('auth.google') }}" class="bb-google">
                    <svg width="16" height="16" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Continue with Google
                </a>

                <p class="bb-terms">By signing up you agree to our <a href="{{ route('terms') }}">Terms of Service</a> and <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>
                <p class="bb-foot">Already have an account? <a onclick="switchTab('login')">Log in</a></p>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.getElementById('panel-login').classList.toggle('active', tab === 'login');
            document.getElementById('panel-register').classList.toggle('active', tab === 'register');
            document.getElementById('tab-login').classList.toggle('active', tab === 'login');
            document.getElementById('tab-register').classList.toggle('active', tab === 'register');
        }

        function selectRole(role) {
            document.getElementById('role-student').classList.toggle('selected', role === 'student');
            document.getElementById('role-teacher').classList.toggle('selected', role === 'teacher');
            document.getElementById('role-input').value = role;
        }

        @if ($errors->any() && session('active_tab') === 'register')
            switchTab('register');
        @endif
    </script>
</body>
</html>