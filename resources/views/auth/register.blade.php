{{-- FILE: resources/views/auth/register.blade.php --}}
@extends('layouts.auth')

@section('title', 'Sign up - BrainBalance')

@section('content')
<div class="w-full">
    <div class="text-center mb-5">
        <span id="register-badge" class="inline-flex items-center gap-1.5 bg-[#E6F7FC] border-2 border-[#99E2F5] text-[#0066D6] font-black text-[11px] uppercase tracking-wider px-3.5 py-1 rounded-full mb-2">
            🎒 Student Quest Account
        </span>
        <h1 id="register-heading" class="font-display text-2xl sm:text-3xl font-bold text-[#0D2B4E]">Join BrainBalance!</h1>
    </div>

    <form method="POST" action="{{ route('register.store') }}" id="register-form" class="flex flex-col gap-3.5">
        @csrf

        <div>
            <span class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1.5">I am a...</span>
            @php $selectedRole = old('role', 'student'); @endphp
            <div class="grid grid-cols-2 gap-3">
                <button type="button" id="role-student" onclick="selectRole('student')"
                    class="p-3 rounded-[20px] border-[3px] text-center transition-all cursor-pointer {{ $selectedRole === 'student' ? 'border-[#0066D6] bg-[#E0F7FC] shadow-[0_4px_0_#7DD3FC] -translate-y-0.5' : 'border-[#B8E2F8] bg-[#F2FAFF]' }}">
                    <div class="text-2xl mb-0.5">🎒</div>
                    <div class="font-black text-sm text-[#0D2B4E]">Student</div>
                    <div class="text-[11px] font-bold text-[#5A7696]">Learn &amp; earn medals</div>
                </button>
                <button type="button" id="role-teacher" onclick="selectRole('teacher')"
                    class="p-3 rounded-[20px] border-[3px] text-center transition-all cursor-pointer {{ $selectedRole === 'teacher' ? 'border-[#55B310] bg-[#ECFCCB] shadow-[0_4px_0_#A3E635] -translate-y-0.5' : 'border-[#B8E2F8] bg-[#F2FAFF]' }}">
                    <div class="text-2xl mb-0.5">🧑‍🏫</div>
                    <div class="font-black text-sm text-[#0D2B4E]">Teacher</div>
                    <div class="text-[11px] font-bold text-[#5A7696]">Manage classes</div>
                </button>
            </div>
            <input type="hidden" name="role" id="role-input" value="{{ $selectedRole }}">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="first_name" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1">First name</label>
                <input
                    id="first_name"
                    name="first_name"
                    type="text"
                    value="{{ old('first_name') }}"
                    placeholder="Maria"
                    required
                    autofocus
                    autocomplete="given-name"
                    class="w-full h-[48px] px-4 rounded-[16px] border-[3px] {{ $errors->has('first_name') ? 'border-[#FF8400]' : 'border-[#B8E2F8]' }} bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] focus:bg-white transition-colors"
                >
                @error('first_name')
                    <span class="text-xs font-black text-[#D96B00] mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="last_name" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1">Last name</label>
                <input
                    id="last_name"
                    name="last_name"
                    type="text"
                    value="{{ old('last_name') }}"
                    placeholder="Santos"
                    required
                    autocomplete="family-name"
                    class="w-full h-[48px] px-4 rounded-[16px] border-[3px] {{ $errors->has('last_name') ? 'border-[#FF8400]' : 'border-[#B8E2F8]' }} bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] focus:bg-white transition-colors"
                >
                @error('last_name')
                    <span class="text-xs font-black text-[#D96B00] mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label for="email" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                placeholder="you@school.edu"
                required
                autocomplete="username"
                class="w-full h-[48px] px-4 rounded-[16px] border-[3px] {{ $errors->has('email') ? 'border-[#FF8400]' : 'border-[#B8E2F8]' }} bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] focus:bg-white transition-colors"
            >
            @error('email')
                <span class="text-xs font-black text-[#D96B00] mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Min. 8 characters"
                    required
                    autocomplete="new-password"
                    class="w-full h-[48px] px-4 rounded-[16px] border-[3px] {{ $errors->has('password') ? 'border-[#FF8400]' : 'border-[#B8E2F8]' }} bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] focus:bg-white transition-colors"
                >
                @error('password')
                    <span class="text-xs font-black text-[#D96B00] mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1">Confirm password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    placeholder="Repeat password"
                    required
                    autocomplete="new-password"
                    class="w-full h-[48px] px-4 rounded-[16px] border-[3px] border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] focus:bg-white transition-colors"
                >
            </div>
        </div>

        <button type="submit" id="register-submit-btn" style="background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);" class="w-full h-[54px] px-5 rounded-[20px] text-white font-black text-base border-2 border-[#004FA8] shadow-[0_6px_0_#004FA8] hover:brightness-105 active:translate-y-1 transition-all flex items-center justify-center gap-2 cursor-pointer mt-1">
            🚀 Start Learning
        </button>
    </form>

    <div class="relative flex py-4 items-center">
        <div class="grow border-t-2 border-dashed border-[#B8E2F8]"></div>
        <span class="shrink mx-3 text-xs font-black text-[#8BA8C7] uppercase tracking-wider">or</span>
        <div class="grow border-t-2 border-dashed border-[#B8E2F8]"></div>
    </div>

    <a href="{{ route('auth.google') }}" class="w-full h-[50px] px-5 rounded-[18px] bg-white hover:bg-[#F2FAFF] text-[#0D2B4E] font-black text-sm border-[3px] border-[#B8E2F8] shadow-[0_4px_0_#D4F1FC] active:translate-y-0.5 transition-all flex items-center justify-center gap-2.5 no-underline">
        <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Continue with Google
    </a>

    <p class="text-center text-xs sm:text-sm font-extrabold text-[#5A7696] mt-4">
        Already have an account? <a href="{{ route('login') }}" class="font-black text-[#0066D6] hover:underline">Log in &rarr;</a>
    </p>
</div>

@push('scripts')
<script>
    function selectRole(role) {
        const isTeacher = role === 'teacher';
        document.body.classList.toggle('theme-teacher', isTeacher);
        document.body.classList.toggle('theme-student', !isTeacher);

        const studentBtn = document.getElementById('role-student');
        const teacherBtn = document.getElementById('role-teacher');
        const badge = document.getElementById('register-badge');
        const submitBtn = document.getElementById('register-submit-btn');

        studentBtn.className = !isTeacher
            ? 'p-3 rounded-[20px] border-[3px] text-center transition-all cursor-pointer border-[#0066D6] bg-[#E0F7FC] shadow-[0_4px_0_#7DD3FC] -translate-y-0.5'
            : 'p-3 rounded-[20px] border-[3px] text-center transition-all cursor-pointer border-[#BEF264] bg-[#F7FEE7]';

        teacherBtn.className = isTeacher
            ? 'p-3 rounded-[20px] border-[3px] text-center transition-all cursor-pointer border-[#55B310] bg-[#ECFCCB] shadow-[0_4px_0_#A3E635] -translate-y-0.5'
            : 'p-3 rounded-[20px] border-[3px] text-center transition-all cursor-pointer border-[#B8E2F8] bg-[#F2FAFF]';

        if (isTeacher) {
            badge.textContent = '🧑‍🏫 Teacher Classroom Account';
            badge.style.background = '#ECFCCB';
            badge.style.borderColor = '#A3E635';
            badge.style.color = '#3E8708';
            submitBtn.style.background = 'linear-gradient(135deg, #55B310 0%, #68CC18 100%)';
            submitBtn.style.borderColor = '#3E8708';
            submitBtn.style.boxShadow = '0 6px 0 #3E8708';
            submitBtn.textContent = '🏫 Create Teacher Account';
        } else {
            badge.textContent = '🎒 Student Quest Account';
            badge.style.background = '#E6F7FC';
            badge.style.borderColor = '#99E2F5';
            badge.style.color = '#0066D6';
            submitBtn.style.background = 'linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%)';
            submitBtn.style.borderColor = '#004FA8';
            submitBtn.style.boxShadow = '0 6px 0 #004FA8';
            submitBtn.textContent = '🚀 Start Learning';
        }

        document.getElementById('role-input').value = role;
    }

    selectRole(document.getElementById('role-input').value || 'student');
</script>
@endpush
@endsection