<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'BrainBalance') }} — @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Space+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Vite: Tailwind + app assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background: #f5f4ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
        }

        /* Tab switcher */
        .auth-tabs {
            display: flex;
            background: #fff;
            border: 1px solid #e5e3ff;
            border-radius: 14px;
            padding: 4px;
            margin-bottom: 1.5rem;
        }

        .auth-tab {
            flex: 1;
            padding: 9px 0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            border: none;
            background: transparent;
            color: #888;
            transition: all 0.18s;
            font-family: 'Space Grotesk', sans-serif;
            text-decoration: none;
            display: block;
        }

        .auth-tab.active,
        .auth-tab:hover.active {
            background: #FF6B6B;
            color: #fff;
        }

        .auth-tab:hover:not(.active) {
            color: #FF6B6B;
        }

        /* Card */
        .auth-card {
            background: #fff;
            border: 1px solid #e5e3ff;
            border-radius: 20px;
            padding: 2rem 2rem 1.75rem;
            box-shadow: 0 4px 24px rgba(108, 92, 231, 0.07);
        }

        /* Logo */
        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 1.5rem;
        }

        .auth-logo-icon {
            width: 42px;
            height: 42px;
            background: #FF6B6B;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .auth-logo-text-main {
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 18px;
            color: #1a1a2e;
            letter-spacing: -0.3px;
            line-height: 1.1;
        }

        .auth-logo-text-sub {
            font-size: 10px;
            font-weight: 500;
            color: #aaa;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        /* Headings */
        .auth-heading {
            font-family: 'Nunito', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #1a1a2e;
            text-align: center;
            margin-bottom: 4px;
        }

        .auth-subheading {
            font-size: 13px;
            color: #999;
            text-align: center;
            margin-bottom: 1.4rem;
            line-height: 1.5;
        }

        /* Fields */
        .auth-field {
            margin-bottom: 1rem;
        }

        .auth-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
            letter-spacing: 0.2px;
        }

        .auth-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .auth-input-wrap {
            position: relative;
        }

        .auth-input-wrap svg {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #bbb;
            pointer-events: none;
        }

        .auth-input {
            width: 100%;
            height: 42px;
            padding: 0 14px 0 38px;
            border-radius: 8px;
            border: 1px solid #e0deff;
            background: #fafafa;
            color: #1a1a2e;
            font-size: 14px;
            font-family: 'Space Grotesk', sans-serif;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .auth-input:focus {
            border-color: #FF6B6B;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.12);
            background: #fff;
        }

        .auth-input.is-invalid {
            border-color: #e74c3c;
        }

        .auth-error {
            font-size: 11px;
            color: #e74c3c;
            margin-top: 4px;
            display: block;
        }

        /* Buttons */
        .auth-btn-primary {
            width: 100%;
            height: 44px;
            border-radius: 8px;
            border: none;
            background: #FF6B6B;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Nunito', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.15s, transform 0.1s;
            letter-spacing: 0.2px;
            margin-top: 0.25rem;
        }

        .auth-btn-primary:hover { background: #d13939; }
        .auth-btn-primary:active { transform: scale(0.98); }

        .auth-btn-google {
            width: 100%;
            height: 42px;
            border-radius: 8px;
            border: 1px solid #e0deff;
            background: #fff;
            color: #1a1a2e;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Space Grotesk', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.15s;
            text-decoration: none;
        }

        .auth-btn-google:hover { background: #f8f8ff; }

        /* Divider */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 1.1rem 0;
            color: #bbb;
            font-size: 12px;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f0eeff;
        }

        /* Footer link */
        .auth-footer {
            text-align: center;
            font-size: 12px;
            color: #aaa;
            margin-top: 1.25rem;
        }

        .auth-footer a {
            color: #FF6B6B;
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover { text-decoration: underline; }

        /* Forgot password */
        .auth-forgot {
            font-size: 11px;
            color: #FF6B6B;
            font-weight: 600;
            text-decoration: none;
        }

        .auth-forgot:hover { text-decoration: underline; }

        /* Terms */
        .auth-terms {
            font-size: 11px;
            color: #bbb;
            text-align: center;
            margin-top: 1rem;
            line-height: 1.6;
        }

        .auth-terms a {
            color: #FF6B6B;
            text-decoration: none;
            font-weight: 500;
        }

        /* Streak dots */
        .auth-streak {
            display: flex;
            align-items: center;
            gap: 5px;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .streak-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #e0deff;
        }

        .streak-dot.lit { background: #FF6B6B; }

        .streak-label {
            font-size: 11px;
            color: #aaa;
            margin-left: 2px;
        }

        /* XP badge */
        .xp-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #fef3c7;
            color: #92400e;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 1.25rem;
        }

        /* Role selector */
        .role-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 1rem;
        }

        .role-btn {
            border: 1.5px solid #e0deff;
            border-radius: 8px;
            padding: 10px 8px;
            text-align: center;
            cursor: pointer;
            background: #fafafa;
            transition: all 0.15s;
        }

        .role-btn:hover { border-color: #FF6B6B; }

        .role-btn.selected {
            border-color: #FF6B6B;
            background: rgba(108, 92, 231, 0.06);
        }

        .role-emoji { font-size: 22px; margin-bottom: 4px; }

        .role-name {
            font-size: 12px;
            font-weight: 600;
            color: #1a1a2e;
        }

        .role-desc {
            font-size: 10px;
            color: #aaa;
        }

        /* Two-column name row */
        .name-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        /* Alert for session messages */
        .auth-alert {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 1rem;
        }

        .auth-alert.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .auth-alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">

        {{-- Tab switcher --}}
        <div class="auth-tabs">
            <a href="{{ route('login') }}"
               class="auth-tab {{ request()->routeIs('login') ? 'active' : '' }}">
                Log in
            </a>
            <a href="{{ route('register') }}"
               class="auth-tab {{ request()->routeIs('register') ? 'active' : '' }}">
                Sign up
            </a>
        </div>

        {{-- Card --}}
        <div class="auth-card">

            {{-- Logo --}}
            <div class="auth-logo">
                <div class="auth-logo-icon">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
                        <circle cx="11" cy="8" r="4" fill="white" opacity="0.9"/>
                        <circle cx="7" cy="14" r="3" fill="white" opacity="0.7"/>
                        <circle cx="15" cy="14" r="3" fill="white" opacity="0.7"/>
                        <line x1="11" y1="8" x2="7" y2="14" stroke="white" stroke-width="1.5" opacity="0.5"/>
                        <line x1="11" y1="8" x2="15" y2="14" stroke="white" stroke-width="1.5" opacity="0.5"/>
                    </svg>
                </div>
                <div>
                    <div class="auth-logo-text-main">BrainBalance</div>
                    <div class="auth-logo-text-sub">by Ekklessia</div>
                </div>
            </div>

            {{-- Page content (login or register) --}}
            @yield('content')

        </div>
    </div>
</body>
</html>