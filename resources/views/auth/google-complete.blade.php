{{-- FILE: resources/views/auth/google-complete.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complete Your Profile - {{ config('app.name', 'BrainBalance') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Nunito', sans-serif;
            color: #0D2B4E;
            background-color: #F5FAFF;
            background-image:
                radial-gradient(at 6% 8%, rgba(0, 102, 214, 0.16) 0px, transparent 42%),
                radial-gradient(at 45% 4%, rgba(29, 196, 223, 0.18) 0px, transparent 42%),
                radial-gradient(at 92% 14%, rgba(104, 204, 24, 0.17) 0px, transparent 44%),
                radial-gradient(at 86% 86%, rgba(255, 132, 0, 0.16) 0px, transparent 46%),
                radial-gradient(#CBE6FF 2px, transparent 2px);
            background-size: 100% 100%, 100% 100%, 100% 100%, 100% 100%, 34px 34px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .bb-wrap { width: 100%; max-width: 470px; }
        .bb-card {
            position: relative;
            overflow: hidden;
            background: #FFFFFF;
            border: 4px solid #B3E5FC;
            border-radius: 32px;
            padding: 2.15rem 2rem 1.75rem;
            box-shadow: 0 12px 0 #D4F1FC, 0 24px 48px rgba(0, 102, 214, 0.10);
        }
        .bb-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 7px;
            background: linear-gradient(90deg, #0066D6 0%, #1DC4DF 33%, #68CC18 68%, #FF8400 100%);
        }
        .bb-logo { display: flex; align-items: center; justify-content: center; margin-bottom: 1.15rem; }
        .bb-heading { font-family: 'Fredoka', 'Nunito', sans-serif; font-weight: 700; font-size: 26px; color: #0D2B4E; text-align: center; margin-bottom: 4px; }
        .bb-sub { font-size: 14px; color: #5A7696; font-weight: 700; text-align: center; margin-bottom: 1.2rem; line-height: 1.5; }
        .avatar-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #F2FAFF;
            border: 3px solid #B8E2F8;
            border-radius: 20px;
            padding: 12px 14px;
            margin-bottom: 1rem;
        }
        .avatar-preview {
            width: 54px; height: 54px; border-radius: 50%; object-fit: cover;
            border: 3px solid #38BDF8; background: #E0F2FE; flex-shrink: 0;
        }
        .avatar-fallback {
            width: 54px; height: 54px; border-radius: 50%; border: 3px solid #BAE6FD;
            background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);
            color: #fff; font-weight: 900; font-size: 18px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .avatar-meta { flex: 1; min-width: 0; }
        .avatar-title { font-size: 13px; font-weight: 900; color: #0D2B4E; }
        .avatar-hint { font-size: 11.5px; font-weight: 700; color: #5A7696; margin-bottom: 4px; }
        .avatar-upload-btn { display: inline-block; font-size: 12px; font-weight: 900; color: #0066D6; cursor: pointer; text-decoration: underline; }
        .bb-field { margin-bottom: 0.95rem; }
        .bb-label { display: block; font-size: 11.5px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; color: #3E5A7A; margin-bottom: 5px; }
        .bb-iwrap { position: relative; }
        .bb-iwrap svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #7FA2C4; pointer-events: none; }
        .bb-input {
            width: 100%; height: 48px; padding: 0 14px 0 40px;
            border-radius: 16px; border: 3px solid #B8E2F8; background: #F2FAFF;
            color: #0D2B4E; font-size: 14.5px; font-family: 'Nunito', sans-serif; font-weight: 800;
            outline: none; transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
        }
        .bb-input:focus { border-color: #0066D6; background: #fff; box-shadow: 0 0 0 4px rgba(29, 196, 223, 0.22); }
        .bb-input[readonly] { background: #EBF5FF; color: #5A7696; cursor: not-allowed; }
        .bb-input.is-invalid { border-color: #FF8400; background: #FFF7ED; }
        .bb-error { font-size: 12px; font-weight: 800; color: #D96B00; margin-top: 4px; display: block; }
        .role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .role-btn {
            border: 3px solid #B8E2F8; border-radius: 20px; padding: 12px 10px;
            text-align: center; cursor: pointer; background: #F2FAFF; transition: all 0.18s;
        }
        .role-btn:hover { border-color: #0066D6; }
        .role-btn.selected {
            border-color: #0066D6; background: #E0F7FC;
            box-shadow: 0 4px 0 #7DD3FC; transform: translateY(-2px);
        }
        .role-emoji { font-size: 25px; margin-bottom: 3px; }
        .role-name { font-size: 14px; font-weight: 900; color: #0D2B4E; }
        .role-desc { font-size: 11.5px; font-weight: 700; color: #5A7696; }
        .name-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .bb-btn {
            width: 100%; height: 52px; border-radius: 20px; border: 2px solid #004FA8;
            background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);
            color: #fff; font-size: 16px; font-weight: 900; font-family: 'Nunito', sans-serif;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 6px 0 #004FA8; transition: filter 0.15s, transform 0.1s; margin-top: 0.4rem;
        }
        .bb-btn:hover { filter: brightness(1.05); }
        .bb-btn:active { transform: translateY(3px); box-shadow: 0 2px 0 #004FA8; }
        .bb-alert.error { padding: 12px 15px; border-radius: 16px; font-size: 13px; font-weight: 800; margin-bottom: 1rem; background: #FFF7ED; color: #9A3412; border: 2px solid #FB923C; }
        .bb-foot { text-align: center; font-size: 13.5px; font-weight: 800; color: #5A7696; margin-top: 1.15rem; }
        .bb-foot a { color: #0066D6; font-weight: 900; text-decoration: none; }
    </style>
</head>
<body>
    <div class="bb-wrap">
        <div class="bb-card">
            <div class="bb-logo">
                <x-brand-logo :href="url('/')" :size="48" />
            </div>

            <div class="bb-heading">Complete Your Profile</div>
            <div class="bb-sub">Your account is not created yet—choose your role, set a password, and click below to finish.</div>

            @if ($errors->any())
                <div class="bb-alert error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form id="google-complete-form" method="POST" action="{{ route('auth.google.complete.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="google_payload" value="{{ old('google_payload', $encryptedGoogleData ?? '') }}">

                <div class="avatar-box">
                    @if (!empty($googleData['avatar']))
                        <img id="avatar-img" src="{{ $googleData['avatar'] }}" alt="Profile photo" class="avatar-preview">
                    @else
                        <div id="avatar-fallback" class="avatar-fallback">
                            {{ strtoupper(substr($googleData['first_name'] ?? 'U', 0, 1)) }}
                        </div>
                        <img id="avatar-img" src="" alt="Profile photo" class="avatar-preview" style="display:none">
                    @endif
                    <div class="avatar-meta">
                        <div class="avatar-title">Profile Photo</div>
                        <div class="avatar-hint">Using your Google photo (optional custom upload, max 2MB)</div>
                        <label for="avatar" class="avatar-upload-btn">Change photo</label>
                        <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none" onchange="previewAvatar(event)">
                        @error('avatar') <span class="bb-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="bb-field">
                    <label class="bb-label">I am a...</label>
                    <div class="role-grid">
                        <div class="role-btn selected" id="role-student" onclick="selectRole('student')">
                            <div class="role-emoji">🎒</div>
                            <div class="role-name">Student</div>
                            <div class="role-desc">Learn &amp; earn medals</div>
                        </div>
                        <div class="role-btn" id="role-teacher" onclick="selectRole('teacher')">
                            <div class="role-emoji">👩‍🏫</div>
                            <div class="role-name">Teacher</div>
                            <div class="role-desc">Manage classes</div>
                        </div>
                    </div>
                    <input type="hidden" name="role" id="role-input" value="{{ old('role', 'student') }}">
                    @error('role') <span class="bb-error">{{ $message }}</span> @enderror
                </div>

                <div class="name-grid" style="margin-bottom:0.95rem">
                    <div>
                        <label for="first_name" class="bb-label">First name</label>
                        <div class="bb-iwrap">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                            <input id="first_name" name="first_name" type="text" class="bb-input {{ $errors->has('first_name') ? 'is-invalid' : '' }}" value="{{ old('first_name', $googleData['first_name'] ?? '') }}" required autocomplete="given-name">
                        </div>
                        @error('first_name') <span class="bb-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="last_name" class="bb-label">Last name</label>
                        <div class="bb-iwrap">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                            <input id="last_name" name="last_name" type="text" class="bb-input {{ $errors->has('last_name') ? 'is-invalid' : '' }}" value="{{ old('last_name', $googleData['last_name'] ?? '') }}" required autocomplete="family-name">
                        </div>
                        @error('last_name') <span class="bb-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="bb-field">
                    <label for="email" class="bb-label">Google Email</label>
                    <div class="bb-iwrap">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
                        <input id="email" name="email" type="email" class="bb-input {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ $googleData['email'] ?? '' }}" readonly required>
                    </div>
                    @error('email') <span class="bb-error">{{ $message }}</span> @enderror
                </div>

                <div class="bb-field">
                    <label for="password" class="bb-label">Create a password</label>
                    <div class="bb-iwrap">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input id="password" name="password" type="password" class="bb-input {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Min. 8 characters" required autocomplete="new-password">
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

                <button type="submit" name="finish_signup" value="1" id="finish-signup-btn" class="bb-btn">
                    🚀 Finish &amp; Enter BrainBalance
                </button>
            </form>

            <p class="bb-foot">Wrong Google account? <a href="{{ route('login') }}">Back to login</a></p>
        </div>
    </div>

    <script>
        function selectRole(role) {
            document.getElementById('role-student').classList.toggle('selected', role === 'student');
            document.getElementById('role-teacher').classList.toggle('selected', role === 'teacher');
            document.getElementById('role-input').value = role;
        }

        function previewAvatar(event) {
            const file = event.target.files[0];
            if (!file) return;
            const img = document.getElementById('avatar-img');
            const fallback = document.getElementById('avatar-fallback');
            img.src = URL.createObjectURL(file);
            img.style.display = 'block';
            if (fallback) fallback.style.display = 'none';
        }

        const completeForm = document.getElementById('google-complete-form');
        const finishBtn = document.getElementById('finish-signup-btn');
        let isSubmitting = false;

        completeForm.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && event.target.tagName === 'INPUT') {
                event.preventDefault();
            }
        });

        completeForm.addEventListener('submit', function (event) {
            if (isSubmitting) {
                event.preventDefault();
                return;
            }
            isSubmitting = true;
            finishBtn.style.pointerEvents = 'none';
            finishBtn.style.opacity = '0.85';
            finishBtn.textContent = 'Creating your account...';
        });

        selectRole(document.getElementById('role-input').value || 'student');
    </script>
</body>
</html>