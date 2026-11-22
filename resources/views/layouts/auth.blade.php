{{-- FILE: resources/views/layouts/auth.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            --bb-lime: #68CC18;
            --bb-orange: #FF8400;
            --bb-spectrum: linear-gradient(90deg, #0066D6 0%, #1DC4DF 33%, #68CC18 68%, #FF8400 100%);
        }

        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            color: #0D2B4E;
            background-color: #F5FAFF;
            background-image:
                radial-gradient(at 6% 8%, rgba(0, 102, 214, 0.16) 0px, transparent 42%),
                radial-gradient(at 45% 4%, rgba(29, 196, 223, 0.18) 0px, transparent 42%),
                radial-gradient(at 92% 14%, rgba(104, 204, 24, 0.17) 0px, transparent 44%),
                radial-gradient(at 86% 86%, rgba(255, 132, 0, 0.16) 0px, transparent 46%),
                radial-gradient(#CBE6FF 2px, transparent 2px);
            background-size: 100% 100%, 100% 100%, 100% 100%, 100% 100%, 34px 34px;
        }

        .font-display { font-family: 'Fredoka', 'Nunito', sans-serif; }

        .auth-wrapper {
            width: 100%;
            max-width: 480px;
        }

        .auth-card {
            position: relative;
            overflow: hidden;
            background: #FFFFFF;
            border: 4px solid #B3E5FC;
            border-radius: 32px;
            padding: 2.25rem 2rem 1.85rem;
            box-shadow: 0 12px 0 #D4F1FC, 0 24px 48px rgba(0, 102, 214, 0.10);
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 7px;
            background: var(--bb-spectrum);
        }

        body.theme-teacher .auth-card {
            border-color: #BEF264;
            box-shadow: 0 12px 0 #DCFCE7, 0 24px 48px rgba(104, 204, 24, 0.10);
            border-radius: 26px;
        }

        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }
    </style>
    @stack('styles')
</head>

<body class="theme-student">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <x-brand-logo :href="url('/')" :size="52" />
            </div>

            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>