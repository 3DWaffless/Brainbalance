@extends('layouts.auth')

@section('title', 'Sign up')

@section('content')

    <h1 class="auth-heading">Join BrainBalance</h1>

    <div style="text-align:center; margin-bottom:1.25rem;">
        <span class="xp-badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="#f59e0b" aria-hidden="true">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            +50 XP on first login
        </span>
    </div>

    <form method="POST" action="{{ route('register') }}" id="register-form">
        @csrf

        {{-- Role selector --}}
        <div class="auth-field">
            <span class="auth-label">I am a...</span>
            <div class="role-grid">
                <div class="role-btn selected" id="role-student" onclick="selectRole('student')">
                    <div class="role-emoji">🧠</div>
                    <div class="role-name">Student</div>
                    <div class="role-desc">Learn &amp; earn XP</div>
                </div>
                <div class="role-btn" id="role-teacher" onclick="selectRole('teacher')">
                    <div class="role-emoji">📚</div>
                    <div class="role-name">Teacher</div>
                    <div class="role-desc">Manage classes</div>
                </div>
            </div>
            <input type="hidden" name="role" id="role-input" value="student">
        </div>

        {{-- Name row --}}
        <div class="name-grid" style="margin-bottom:1rem;">
            <div>
                <label for="first_name" class="auth-label">First name</label>
                <div class="auth-input-wrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                    <input
                        id="first_name"
                        name="first_name"
                        type="text"
                        class="auth-input {{ $errors->has('first_name') ? 'is-invalid' : '' }}"
                        value="{{ old('first_name') }}"
                        placeholder="Maria"
                        required
                        autofocus
                        autocomplete="given-name"
                    >
                </div>
                @error('first_name')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="last_name" class="auth-label">Last name</label>
                <div class="auth-input-wrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                    <input
                        id="last_name"
                        name="last_name"
                        type="text"
                        class="auth-input {{ $errors->has('last_name') ? 'is-invalid' : '' }}"
                        value="{{ old('last_name') }}"
                        placeholder="Santos"
                        required
                        autocomplete="family-name"
                    >
                </div>
                @error('last_name')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Email --}}
        <div class="auth-field">
            <label for="email" class="auth-label">Email</label>
            <div class="auth-input-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
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
                    autocomplete="username"
                >
            </div>
            @error('email')
                <span class="auth-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Password --}}
        <div class="auth-field">
            <label for="password" class="auth-label">Password</label>
            <div class="auth-input-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="auth-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="Min. 8 characters"
                    required
                    autocomplete="new-password"
                >
            </div>
            @error('password')
                <span class="auth-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Confirm password --}}
        <div class="auth-field">
            <label for="password_confirmation" class="auth-label">Confirm password</label>
            <div class="auth-input-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    class="auth-input {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                    placeholder="Repeat your password"
                    required
                    autocomplete="new-password"
                >
            </div>
            @error('password_confirmation')
                <span class="auth-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="auth-btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path d="M15 14l5-5-5-5"/>
                <path d="M20 9H9a4 4 0 0 0-4 4v7"/>
            </svg>
            Create account
        </button>
    </form>

    <div class="auth-divider">or</div>

    {{-- Google OAuth --}}
    <a href="{{ route('auth.google') }}" class="auth-btn-google">
        <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Continue with Google
    </a>

    <p class="auth-terms">
        By signing up you agree to our
        <a href="{{ route('terms') }}">Terms of Service</a> and
        <a href="{{ route('privacy') }}">Privacy Policy</a>.
    </p>

    <p class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
    </p>

    <script>
        function selectRole(role) {
            document.getElementById('role-student').classList.toggle('selected', role === 'student');
            document.getElementById('role-teacher').classList.toggle('selected', role === 'teacher');
            document.getElementById('role-input').value = role;
        }
    </script>

@endsection