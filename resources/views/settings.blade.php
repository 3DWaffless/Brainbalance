{{-- FILE: resources/views/settings.blade.php --}}
@extends('layouts.app')

@section('title', 'Settings - ' . config('app.name', 'BrainBalance'))

@push('styles')
<style>
    .card.danger { border-color: #F5C6CB; }
    .card-title.danger { color: #e74c3c; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-error { font-size: 11px; font-weight: 700; color: #e74c3c; margin-top: 4px; display: block; }
    .form-hint { font-size: 11px; font-weight: 700; color: #bbb; margin-top: 4px; display: block; }
    .divider { border: none; border-top: 2px dashed #FFE4C4; margin: 18px 0; }
    .role-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        background: #FFE4C4;
        color: #FF6B6B;
        margin-left: 6px;
    }
    .avatar-upload-wrap {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 2px dashed #FFE4C4;
    }
    .avatar-preview {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #FF6B6B;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 900;
        border: 3px solid #FFE4C4;
        object-fit: cover;
        box-shadow: 0 4px 10px rgba(255, 107, 107, 0.15);
        flex-shrink: 0;
    }
    .btn-danger-solid {
        background: #e74c3c; color: #fff;
        border: none; padding: 10px 20px;
        border-radius: 12px; font-size: 13px; font-weight: 900;
        font-family: 'Nunito', sans-serif; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: background 0.15s;
    }
    .btn-danger-solid:hover { background: #c0392b; }
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.4); z-index: 100;
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
@endpush

@section('content')
<div class="topbar">
    <div>
        <div class="tgreet">Settings ⚙️</div>
        <div class="tsub">Manage your account information and preferences</div>
    </div>
</div>

{{-- UPDATE PROFILE --}}
<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <div>
            <div class="card-title">
                <span>Your Information 👤</span>
                <span class="role-badge">{{ ucfirst(Auth::user()->role) }} Account</span>
            </div>
            <div class="card-sub">Update your profile picture, name, email, or password</div>
        </div>
    </div>
    
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- AVATAR UPLOAD --}}
        <div class="avatar-upload-wrap">
            @if(Auth::user()->avatar_url)
                <img src="{{ Auth::user()->avatar_url }}" id="avatarPreview" class="avatar-preview" alt="Profile Picture">
            @else
                <div id="avatarInitials" class="avatar-preview">
                    {{ Auth::user()->initials }}
                </div>
                <img id="avatarPreview" class="avatar-preview" style="display:none;" alt="Profile Picture">
            @endif

            <div style="flex:1">
                <label class="form-label">Profile Picture</label>
                <input type="file" name="avatar" id="avatarInput" accept="image/*" style="font-size:12px">
                @error('avatar') <span class="form-error">{{ $message }}</span> @enderror
                <span class="form-hint">JPG, PNG, or GIF up to 2MB.</span>
            </div>
        </div>

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
            <span class="form-hint">Only required if you are updating your email address or password.</span>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:8px">
            <button type="submit" class="btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                Save changes
            </button>
        </div>
    </form>
</div>

{{-- DANGER ZONE --}}
<div class="card danger" style="max-width: 720px;">
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

{{-- DELETE ACCOUNT MODAL --}}
<div class="modal-overlay" id="modal-delete">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('modal-delete')">×</button>
        <div class="modal-title">Delete your account?</div>
        <div class="modal-sub">
            @if(Auth::user()->isTeacher())
                This will permanently remove your teacher profile, created classes, uploaded modules, and quizzes. This action cannot be undone.
            @else
                This will permanently remove your student account, class enrollments, quiz history, and badges. This action cannot be undone.
            @endif
        </div>
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
                <button type="submit" class="btn-danger-solid">Permanently delete</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('open');
        });
    });

    // Live Avatar Preview
    document.getElementById('avatarInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgPreview = document.getElementById('avatarPreview');
                const initialsDiv = document.getElementById('avatarInitials');
                
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block';
                if (initialsDiv) initialsDiv.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    });

    @if($errors->has('current_password') && old('_method') === 'DELETE')
        openModal('modal-delete');
    @endif
</script>
@endpush