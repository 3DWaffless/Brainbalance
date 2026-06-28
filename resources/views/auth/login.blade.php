{{-- FILE: resources/views/auth/login.blade.php --}}

@extends('layouts.auth')

@section('title', 'Log in')

@section('content')

    @if (session('status'))
        <div class="auth-alert success">{{ session('status') }}</div>
    @endif

    <h1 class="auth-heading">Welcome back!</h1>
    <p class="auth-subheading">Pick up where you left off — your streak's waiting.</p>

    <div class="auth-streak">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="#f59e0b">
            <path d="M12 2c-.5 0-.9.4-.9.9 0 2.8-2.3 4.1-2.3 7.1 0 1.8 1.5 3.3 3.2 3.3s3.2-1.5 3.2-3.3c0-.6-.1-1.1-.4-1.6-.2-.4-.7-.5-1-.2-.1.1-.2.3-.2.5v.3c0 .5-.4.9-.9.9s-.9-.4-.9-.9c0-2 1.6-3 1.6-5.1 0-.5-.4-.9-.9-.9zm-4.5 10.5c-.3.8-.5 1.6-.5 2.5 0 2.8 2.2 5 5 5s5-2.2 5-5c0-.9-.2-1.7-.5-2.5-.7.5-1.6.8-2.5.8-2.4 0-4.4-1.7-4.9-4-.5.3-1.1.7-1.6 1.2z"/>
        </svg>
        <div class="streak-dot lit"></div>
        <div class="streak-dot lit"></div>
        <div class="streak-dot lit"></div>
        <div class="streak-dot"></div>
        <div class="streak-dot"></div>
        <span class="streak-label">3-day streak</span>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="auth-field">
            <label for="email" class="auth-label">Email</label>
            <div class="auth-input-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="M2 7l10 7 10-7"/>
                </svg>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="auth-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="you@school.edu"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>
            @error('email')
                <span class="auth-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <div class="auth-label-row">
                <label for="password" class="auth-label" style="margin-bottom:0">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-forgot">Forgot password?</a>
                @endif
            </div>
            <div class="auth-input-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="auth-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="Your password"
                    required
                    autocomplete="current-password"
                >
            </div>
            @error('password')
                <span class="auth-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="display:flex; align-items:center; gap:8px; margin-bottom:1rem;">
            <input
                type="checkbox"
                id="remember_me"
                name="remember"
                style="accent-color:#FF6B6B; width:15px; height:15px; cursor:pointer;"
            >
            <label for="remember_me" style="font-size:12px; color:#888; cursor:pointer;">
                Keep me logged in
            </label>
        </div>

        <button type="submit" class="auth-btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            Log in
        </button>
    </form>

    <div class="auth-divider">or</div>

    <a href="{{ route('auth.google') }}" class="auth-btn-google">
        <svg width="16" height="16" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Continue with Google
    </a>

    <p class="auth-footer">
        New here? <a href="{{ route('register') }}">Create an account</a>
    </p>

@endsection