<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings — {{ config('app.name', 'BrainBalance') }}</title>
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
        }

        /* ===================== SIDEBAR ===================== */
        .sidebar {
            width: 210px;
            background: #fff;
            border-right: 2px solid #FFE4C4;
            display: flex;
            flex-direction: column;
            padding: 1rem 0;
            flex-shrink: 0;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
        }

        .sb-logo {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 1rem 1rem;
            border-bottom: 2px solid #FFE4C4;
            margin-bottom: 0.75rem;
        }

        .sb-logo-icon {
            width: 38px; height: 38px;
            background: #FF6B6B;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .sb-logo-name { font-weight: 900; font-size: 14px; color: #2D2D2D; line-height: 1.1; }
        .sb-logo-sub { font-size: 9px; font-weight: 700; color: #bbb; letter-spacing: 0.6px; text-transform: uppercase; }

        .sb-nav { display: flex; flex-direction: column; gap: 3px; padding: 0 0.65rem; flex: 1; }

        .sb-item {
            display: flex; align-items: center; gap: 9px;
            padding: 9px 11px; border-radius: 12px;
            font-size: 13px; font-weight: 800; color: #aaa;
            cursor: pointer; transition: all 0.15s; text-decoration: none;
        }

        .sb-item:hover { background: #FFF0E0; color: #FF6B6B; }
        .sb-item.active { background: #FFE4C4; color: #FF6B6B; }
        .sb-item svg { width: 18px; height: 18px; flex-shrink: 0; }

        .sb-section { font-size: 10px; font-weight: 900; color: #ccc; letter-spacing: 0.8px; text-transform: uppercase; padding: 8px 11px 4px; }

        .sb-bottom { padding: 0.75rem; border-top: 2px solid #FFE4C4; margin-top: 0.5rem; }

        .sb-user {
            display: flex; align-items: center; gap: 9px;
            padding: 9px 11px; border-radius: 12px;
            background: #FFF0E0; cursor: pointer;
        }

        .sb-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: #FF6B6B;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 900; color: #fff; flex-shrink: 0;
        }

        .sb-uname { font-size: 13px; font-weight: 900; color: #2D2D2D; line-height: 1.2; }
        .sb-urole { font-size: 10px; font-weight: 700; color: #aaa; }

        /* ===================== MAIN ===================== */
        .main { margin-left: 210px; flex: 1; padding: 1.75rem; max-width: 720px; }

        .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; }
        .tgreet { font-weight: 900; font-size: 22px; color: #2D2D2D; }
        .tsub { font-size: 13px; color: #aaa; font-weight: 700; margin-top: 3px; }

        /* ===================== ALERTS ===================== */
        .alert {
            padding: 12px 16px; border-radius: 12px;
            font-size: 13px; font-weight: 700;
            margin-bottom: 1.25rem;
            display: flex; align-items: center; gap: 8px;
        }

        .alert-success { background: #D4EDDA; color: #155724; border: 2px solid #C3E6CB; }
        .alert-error   { background: #F8D7DA; color: #721C24; border: 2px solid #F5C6CB; }

        /* ===================== CARDS ===================== */
        .card {
            background: #fff;
            border-radius: 18px;
            padding: 20px;
            border: 2px solid #FFE4C4;
            margin-bottom: 1.25rem;
        }

        .card.danger { border-color: #F5C6CB; }

        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px;
        }

        .card-title { font-size: 15px; font-weight: 900; color: #2D2D2D; }
        .card-title.danger { color: #e74c3c; }
        .card-sub { font-size: 12px; font-weight: 700; color: #aaa; margin-top: 2px; }

        /* ===================== FORM ===================== */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .form-field { margin-bottom: 12px; }

        .form-label { display: block; font-size: 12px; font-weight: 800; color: #555; margin-bottom: 5px; }

        .form-input {
            width: 100%; height: 44px;
            padding: 0 14px;
            border-radius: 12px;
            border: 2px solid #FFE4C4;
            background: #FFF8F0;
            color: #2D2D2D;
            font-size: 14px;
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            outline: none;
            transition: border-color 0.15s;
        }

        .form-input:focus { border-color: #FF6B6B; background: #fff; }
        .form-input.is-invalid { border-color: #e74c3c; }

        .form-error { font-size: 11px; font-weight: 700; color: #e74c3c; margin-top: 4px; display: block; }
        .form-hint { font-size: 11px; font-weight: 700; color: #bbb; margin-top: 4px; display: block; }

        .divider { border: none; border-top: 2px dashed #FFE4C4; margin: 18px 0; }

        /* ===================== BUTTONS ===================== */
        .btn-primary {
            background: #FF6B6B; color: #fff;
            border: none; padding: 10px 20px;
            border-radius: 12px; font-size: 13px; font-weight: 900;
            font-family: 'Nunito', sans-serif; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            transition: background 0.15s;
        }

        .btn-primary:hover { background: #e85555; }

        .btn-ghost {
            background: #fff; color: #FF6B6B;
            border: 2px solid #FFE4C4; padding: 9px 16px;
            border-radius: 12px; font-size: 12px; font-weight: 900;
            font-family: 'Nunito', sans-serif; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            transition: background 0.15s;
        }

        .btn-ghost:hover { background: #FFF0E0; }

        .btn-danger-solid {
            background: #e74c3c; color: #fff;
            border: none; padding: 10px 20px;
            border-radius: 12px; font-size: 13px; font-weight: 900;
            font-family: 'Nunito', sans-serif; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            transition: background 0.15s;
        }

        .btn-danger-solid:hover { background: #c0392b; }
        .btn-danger-solid:disabled { background: #f0aaa2; cursor: not-allowed; }

        /* ===================== MODAL ===================== */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 50;
            align-items: center; justify-content: center;
        }

        .modal-overlay.open { display: flex; }

        .modal {
            background: #fff; border-radius: 20px;
            padding: 24px; width: 100%; max-width: 420px;
            border: 2px solid #FFE4C4;
        }

        .modal-title { font-size: 17px; font-weight: 900; color: #2D2D2D; margin-bottom: 6px; }
        .modal-sub { font-size: 12px; font-weight: 700; color: #999; margin-bottom: 16px; }
        .modal-close { float: right; background: none; border: none; font-size: 20px; cursor: pointer; color: #aaa; }
    </style>
</head>
<body>

{{-- ===================== SIDEBAR ===================== --}}
<div class="sidebar">
    <div class="sb-logo">
        <div class="sb-logo-icon">
            <svg width="20" height="20" viewBox="0 0 22 22" fill="none">
                <circle cx="11" cy="8" r="4" fill="white" opacity="0.95"/>
                <circle cx="7" cy="14" r="3" fill="white" opacity="0.8"/>
                <circle cx="15" cy="14" r="3" fill="white" opacity="0.8"/>
                <line x1="11" y1="8" x2="7" y2="14" stroke="white" stroke-width="1.5" opacity="0.6"/>
                <line x1="11" y1="8" x2="15" y2="14" stroke="white" stroke-width="1.5" opacity="0.6"/>
            </svg>
        </div>
        <div>
            <div class="sb-logo-name">BrainBalance</div>
            <div class="sb-logo-sub">by Ekklessia</div>
        </div>
    </div>

    <nav class="sb-nav">
        <div class="sb-section">Main</div>
        <a href="{{ route('dashboard') }}" class="sb-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <div class="sb-section">Learn</div>
        <a href="{{ route('modules.index') }}" class="sb-item {{ request()->routeIs('modules.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            Learning Modules
        </a>
        <a href="{{ Auth::user()->isTeacher() ? route('quiz.results') : route('quiz.index') }}" class="sb-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Quizzes
        </a>
        <div class="sb-section">Account</div>
        <a href="{{ route('profile.edit') }}" class="sb-item active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Settings
        </a>
    </nav>

    <div class="sb-bottom">
        <div class="sb-user">
            <div class="sb-avatar">{{ Auth::user()->initials }}</div>
            <div>
                <div class="sb-uname">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                <div class="sb-urole">{{ ucfirst(Auth::user()->role) }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:8px">
            @csrf
            <button type="submit" style="width:100%;padding:8px;border-radius:10px;border:2px solid #FFE4C4;background:#fff;color:#FF6B6B;font-size:12px;font-weight:900;font-family:'Nunito',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Log out
            </button>
        </form>
    </div>
</div>

{{-- ===================== MAIN ===================== --}}
<div class="main">

    <div class="topbar">
        <div>
            <div class="tgreet">Settings ⚙️</div>
            <div class="tsub">Manage your account information</div>
        </div>
    </div>

    {{-- ALERTS --}}
    @if (session('success'))
        <div class="alert alert-success">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            <span>❌</span> {{ session('error') }}
        </div>
    @endif

    {{-- ===================== UPDATE PROFILE ===================== --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Your information 👤</div>
                <div class="card-sub">Update your name, email, or password</div>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-field">
                    <label class="form-label">First name</label>
                    <input name="first_name" type="text" class="form-input {{ $errors->has('first_name') ? 'is-invalid' : '' }}" value="{{ old('first_name', $user->first_name) }}" required>
                    @error('first_name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-field">
                    <label class="form-label">Last name</label>
                    <input name="last_name" type="text" class="form-input {{ $errors->has('last_name') ? 'is-invalid' : '' }}" value="{{ old('last_name', $user->last_name) }}" required>
                    @error('last_name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-field">
                <label class="form-label">Email address</label>
                <input name="email" type="email" class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email', $user->email) }}" required>
                @error('email') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <hr class="divider">

            <div class="form-grid">
                <div class="form-field">
                    <label class="form-label">New password</label>
                    <input name="password" type="password" class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Leave blank to keep current password">
                    @error('password') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-field">
                    <label class="form-label">Confirm new password</label>
                    <input name="password_confirmation" type="password" class="form-input" placeholder="Repeat new password">
                </div>
            </div>

            <div class="form-field">
                <label class="form-label">Current password</label>
                <input name="current_password" type="password" class="form-input {{ $errors->has('current_password') ? 'is-invalid' : '' }}" placeholder="Required to change email or password">
                @error('current_password') <span class="form-error">{{ $message }}</span> @enderror
                <span class="form-hint">Only needed if you're changing your email or password.</span>
            </div>

            <div style="display:flex;justify-content:flex-end;margin-top:8px">
                <button type="submit" class="btn-primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                    Save changes
                </button>
            </div>
        </form>
    </div>

    {{-- ===================== DANGER ZONE ===================== --}}
    <div class="card danger">
        <div class="card-header">
            <div>
                <div class="card-title danger">Danger zone ⚠️</div>
                <div class="card-sub">Permanently delete your account and all associated data</div>
            </div>
        </div>

        <button class="btn-danger-solid" onclick="openModal('modal-delete')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
            Delete my account
        </button>
    </div>

</div>

{{-- ===================== DELETE ACCOUNT MODAL ===================== --}}
<div class="modal-overlay" id="modal-delete">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-delete')">✕</button>
        <div class="modal-title">Delete your account?</div>
        <div class="modal-sub">This will permanently remove your account, classes, quiz history, and badges. This action cannot be undone.</div>

        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf
            @method('DELETE')

            <div class="form-field">
                <label class="form-label">Enter your password to confirm</label>
                <input name="current_password" type="password" class="form-input {{ $errors->has('current_password') ? 'is-invalid' : '' }}" placeholder="Your password" required>
                @error('current_password') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
                <button type="button" class="btn-ghost" onclick="closeModal('modal-delete')">Cancel</button>
                <button type="submit" class="btn-danger-solid">🗑 Permanently delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('open');
        });
    });

    @if($errors->has('current_password') && old('_method') === 'DELETE')
        openModal('modal-delete');
    @endif
</script>

</body>
</html>