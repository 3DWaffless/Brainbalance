{{-- FILE: resources/views/auth/login.blade.php --}}
@extends('layouts.auth')

@section('title', 'Log in - BrainBalance')

@section('content')
<div class="w-full">
    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-[#ECFCCB] border-2 border-[#84CC16] text-[#365314] px-4 py-3 text-xs font-black">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-2xl bg-[#FFF7ED] border-2 border-[#FB923C] text-[#9A3412] px-4 py-3 text-xs font-black">
            {{ session('error') }}
        </div>
    @endif

    <div class="text-center mb-5">
        <div class="inline-flex items-center gap-1.5 bg-[#E6F7FC] border-2 border-[#99E2F5] text-[#0066D6] font-black text-[11px] uppercase tracking-wider px-3.5 py-1 rounded-full mb-2">
            🎒 Student &amp; Teacher Portal
        </div>
        <h1 class="font-display text-2xl sm:text-3xl font-bold text-[#0D2B4E]">Welcome back! 👋</h1>
        <p class="text-xs sm:text-sm font-bold text-[#5A7696] mt-1">Log in to enter your classroom &amp; medal box</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
        @csrf

        <div>
            <label for="email" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1.5">Email</label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#7FA2C4] pointer-events-none">✉️</span>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    placeholder="you@school.edu"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full h-[52px] pl-10 pr-4 rounded-[18px] border-[3px] {{ $errors->has('email') ? 'border-[#FF8400] bg-[#FFF7ED]' : 'border-[#B8E2F8] bg-[#F2FAFF]' }} font-extrabold text-sm text-[#0D2B4E] placeholder:text-[#8BA8C7] focus:outline-none focus:border-[#0066D6] focus:bg-white focus:ring-4 focus:ring-[#1DC4DF]/20 transition-all"
                >
            </div>
            @error('email')
                <span class="text-xs font-black text-[#D96B00] mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <div class="flex justify-between items-center mb-1.5">
                <label for="password" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-black text-[#0066D6] hover:underline">Forgot password?</a>
                @endif
            </div>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#7FA2C4] pointer-events-none">🔒</span>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Your password"
                    required
                    autocomplete="current-password"
                    class="w-full h-[52px] pl-10 pr-16 rounded-[18px] border-[3px] {{ $errors->has('password') ? 'border-[#FF8400] bg-[#FFF7ED]' : 'border-[#B8E2F8] bg-[#F2FAFF]' }} font-extrabold text-sm text-[#0D2B4E] placeholder:text-[#8BA8C7] focus:outline-none focus:border-[#0066D6] focus:bg-white focus:ring-4 focus:ring-[#1DC4DF]/20 transition-all"
                >
                <button type="button" onclick="togglePassword('password', this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-black text-[#5A7696] hover:text-[#0066D6] cursor-pointer">
                    Show
                </button>
            </div>
            @error('password')
                <span class="text-xs font-black text-[#D96B00] mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" id="remember_me" name="remember" class="w-4 h-4 rounded accent-[#0066D6] cursor-pointer">
            <span class="text-xs font-extrabold text-[#5A7696]">Keep me logged in</span>
        </label>

        <button type="submit" style="background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);" class="w-full h-[54px] px-5 rounded-[20px] text-white font-black text-base border-2 border-[#004FA8] shadow-[0_6px_0_#004FA8] hover:brightness-105 active:translate-y-1 active:shadow-[0_2px_0_#004FA8] transition-all flex items-center justify-center gap-2 cursor-pointer mt-1">
            🚀 Log in
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

    <p class="text-center text-xs sm:text-sm font-extrabold text-[#5A7696] mt-5">
        New here? <a href="{{ route('register') }}" class="font-black text-[#0066D6] hover:underline">Create an account &rarr;</a>
    </p>
</div>

@push('scripts')
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.textContent = isHidden ? 'Hide' : 'Show';
    }
</script>
@endpush
@endsection