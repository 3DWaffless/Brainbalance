{{-- FILE: resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard - BrainBalance')

@section('content')
<div class="flex flex-col gap-6 sm:gap-8 w-full">

    @if(Auth::user()->isTeacher())
        <!-- TEACHER WORKSPACE HEADER -->
        <div class="card-tactile flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white w-full">
            <div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-brand-text flex items-center gap-2">
                    Teacher Workspace
                </h1>
                <p class="text-xs sm:text-sm font-extrabold text-gray-400 mt-1">
                    Create classes, Formulate quizzes, and track student progress.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
                <a href="{{ route('quiz.create') }}" class="btn-tactile-teal text-xs py-2.5 px-4">
                    Create Quiz
                </a>
                <button type="button" onclick="document.getElementById('create-class-modal')?.classList.remove('hidden')" class="btn-tactile-primary text-xs py-2.5 px-4 cursor-pointer">
                    Create Class
                </button>
            </div>
        </div>
    @else
        <!-- STUDENT GAMIFIED ADVENTURE HERO BANNER -->
        @php
            $xp = Auth::user()->total_xp ?? 0;
            $lvl = Auth::user()->level ?? 1;
            $xpProgress = min(100, max(10, round((($xp % 500) / 500) * 100)));
            $xpToNext = 500 - ($xp % 500);
        @endphp
        <div class="card-tactile relative overflow-hidden w-full" style="background: linear-gradient(135deg, #EBF5FF 0%, #FFFFFF 52%, #ECFCCB 100%) !important;">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-5 relative z-10">
                <div class="flex-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border-2 border-sky-200 text-brand-coral font-black text-xs mb-2 shadow-2xs">
                        <span>🚀</span>
                        <span>EXPLORER RANK • LEVEL {{ $lvl }}</span>
                    </div>
                    <h1 class="font-display text-2xl sm:text-3xl md:text-4xl font-bold text-brand-text leading-tight">
                        Welcome back, <span class="text-brand-coral">{{ Auth::user()->first_name ?? 'Explorer' }}</span>! 👋
                    </h1>
                    <p class="text-xs sm:text-sm font-extrabold text-slate-500 mt-1">
                        Complete Math &amp; English quizzes to earn XP and unlock fastest-time medals in your chest!
                    </p>

                    <!-- XP Progress Track -->
                    <div class="mt-4 max-w-md bg-white/90 border-2 border-sky-200 rounded-2xl p-3">
                        <div class="flex justify-between items-center text-xs font-black mb-1.5">
                            <span class="text-brand-coral">⚡ {{ number_format($xp) }} Total XP</span>
                            <span class="text-slate-400">{{ $xpToNext }} XP to Level {{ $lvl + 1 }}</span>
                        </div>
                        <div class="w-full h-3 bg-sky-100 rounded-full overflow-hidden border border-sky-200">
                            <div class="h-full bb-xp-fill rounded-full transition-all duration-500" style="width: {{ $xpProgress }}%;"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
                    <a href="{{ route('quiz.index') }}" class="btn-tactile-orange text-xs sm:text-sm py-3 px-5">
                        Take a Quiz
                    </a>
                    <button type="button" onclick="document.getElementById('join-class-modal')?.classList.remove('hidden')" class="btn-tactile-primary text-xs sm:text-sm py-3 px-5 cursor-pointer">
                        Join New Class
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Stat Counter Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 w-full">
        @if(Auth::user()->isTeacher())
            <div class="card-tactile flex flex-col items-center justify-center text-center py-5 sm:py-6 bg-sky-50 border-sky-200">
                <span class="text-2xl sm:text-3xl font-black text-brand-coral mb-1">{{ $activeClassesCount ?? (isset($ownedClasses) ? $ownedClasses->count() : 0) }}</span>
                <span class="text-[10px] sm:text-xs font-black text-gray-500 uppercase tracking-wider">Active Classes</span>
            </div>
            <div class="card-tactile flex flex-col items-center justify-center text-center py-5 sm:py-6 bg-teal-50 border-teal-200">
                <span class="text-2xl sm:text-3xl font-black text-brand-teal mb-1">{{ $totalStudentsCount ?? 0 }}</span>
                <span class="text-[10px] sm:text-xs font-black text-gray-500 uppercase tracking-wider">Total Students</span>
            </div>
            <div class="card-tactile flex flex-col items-center justify-center text-center py-5 sm:py-6 bg-amber-50 border-amber-200 sm:col-span-2 lg:col-span-1">
                <span class="text-2xl sm:text-3xl font-black text-brand-amber mb-1">{{ $publishedQuizzesCount ?? 0 }}</span>
                <span class="text-[10px] sm:text-xs font-black text-gray-500 uppercase tracking-wider">Published Quizzes</span>
            </div>
        @else
            <div class="card-tactile flex items-center gap-4 py-4 px-5 bg-gradient-to-br from-lime-50 to-emerald-50 border-lime-300">
                <div class="w-14 h-14 rounded-2xl bg-white border-3 border-lime-300 flex items-center justify-center text-3xl shrink-0 shadow-2xs">
                    ⭐
                </div>
                <div>
                    <span class="text-[11px] font-black text-lime-700 uppercase tracking-wider block">Current Rank</span>
                    <span class="font-display text-2xl sm:text-3xl font-bold text-brand-text">Level {{ Auth::user()->level ?? 1 }}</span>
                </div>
            </div>

            <div class="card-tactile flex items-center gap-4 py-4 px-5 bg-gradient-to-br from-sky-50 to-cyan-50 border-sky-300">
                <div class="w-14 h-14 rounded-2xl bg-white border-3 border-sky-300 flex items-center justify-center text-3xl shrink-0 shadow-2xs">
                    ⚡
                </div>
                <div>
                    <span class="text-[11px] font-black text-sky-700 uppercase tracking-wider block">Brainpower XP</span>
                    <span class="font-display text-2xl sm:text-3xl font-bold text-brand-coral">{{ number_format(Auth::user()->total_xp ?? 0) }} XP</span>
                </div>
            </div>

            <div class="card-tactile flex items-center gap-4 py-4 px-5 bg-gradient-to-br from-amber-50 to-orange-50 border-amber-300 sm:col-span-2 lg:col-span-1">
                <div class="w-14 h-14 rounded-2xl bg-white border-3 border-amber-300 flex items-center justify-center text-3xl shrink-0 shadow-2xs">
                    🏫
                </div>
                <div>
                    <span class="text-[11px] font-black text-amber-700 uppercase tracking-wider block">Enrolled Worlds</span>
                    <span class="font-display text-2xl sm:text-3xl font-bold text-amber-600">{{ isset($joinedClasses) ? $joinedClasses->count() : 0 }} {{ \Illuminate\Support\Str::plural('Class', isset($joinedClasses) ? $joinedClasses->count() : 0) }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Dynamic Widescreen Grid (7-5 Split on Desktop for balanced layout) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 w-full">

        <!-- Left Main Column (Managed / Enrolled Classes Showcase) -->
        <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-6">
            <div class="card-tactile h-full">
                <div class="flex justify-between items-center mb-4 sm:mb-6 pb-3 border-b-4 border-dashed border-brand-border">
                    <h3 class="font-display text-lg sm:text-xl font-bold text-brand-text flex items-center gap-2">
                        🏫 @if(Auth::user()->isTeacher()) My Managed Classes @else My Subject Worlds @endif
                    </h3>
                    <a href="{{ route('subjects.index') }}" class="text-xs font-black text-brand-coral hover:underline">
                        View All &rarr;
                    </a>
                </div>

                @php
                    $classesList = Auth::user()->isTeacher()
                        ? ($ownedClasses ?? Auth::user()->ownedClasses ?? collect())
                        : ($joinedClasses ?? Auth::user()->joinedClasses ?? collect());
                @endphp

                @if(count($classesList) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($classesList as $class)
                            @php
                                $subjLower = strtolower(($class->subject ?? '') . ' ' . ($class->name ?? ''));
                                $isMath = str_contains($subjLower, 'math');
                                $isEng  = str_contains($subjLower, 'eng') || str_contains($subjLower, 'read');
                                $cardBg = $isMath ? 'bg-sky-50/70 border-sky-200' : ($isEng ? 'bg-lime-50/70 border-lime-200' : 'bg-amber-50/60 border-amber-200');
                                $badgeEmoji = $isMath ? '🔢' : ($isEng ? '📖' : '🎓');
                            @endphp
                            <div class="rounded-3xl border-3 {{ $cardBg }} p-4 flex flex-col justify-between h-full group hover:-translate-y-1 transition-all shadow-xs">
                                <div class="mb-4">
                                    <div class="flex justify-between items-start gap-2 mb-2.5">
                                        <span class="w-10 h-10 rounded-xl bg-white border-2 border-brand-border flex items-center justify-center text-xl shadow-2xs">
                                            {{ $badgeEmoji }}
                                        </span>
                                        @if($class->class_code || $class->code)
                                            <span class="bg-white text-brand-coral font-black text-[10px] px-2.5 py-1 rounded-lg border-2 border-sky-200 shrink-0">
                                                {{ $class->class_code ?? $class->code }}
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="font-black text-sm sm:text-base text-brand-text group-hover:text-brand-coral transition-colors leading-snug">
                                        {{ $class->name }}
                                    </h4>
                                    <p class="text-xs font-bold text-slate-500 mt-1">
                                        {{ ucfirst($class->subject ?? 'General') }} &bull; Sec. {{ $class->section ?? 'A' }}
                                    </p>
                                </div>

                                <div class="pt-3 border-t-2 border-dashed border-brand-border flex justify-between items-center mt-auto">
                                    <span class="text-[11px] font-extrabold text-slate-500">
                                        👥 {{ $class->students_count ?? 0 }} Learners
                                    </span>
                                    <a href="{{ route('subjects.show', $class->id) }}" class="btn-tactile-primary text-xs py-1.5 px-3">
                                        Enter &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 bg-brand-bg/60 rounded-2xl border-2 border-dashed border-brand-border">
                        <p class="text-xs sm:text-sm font-extrabold text-slate-500 mb-3">
                            @if(Auth::user()->isTeacher())
                                No classrooms created yet.
                            @else
                                You haven't joined any subject worlds yet!
                            @endif
                        </p>
                        @if(!Auth::user()->isTeacher())
                            <button type="button" onclick="document.getElementById('join-class-modal')?.classList.remove('hidden')" class="btn-tactile-primary text-xs py-2 px-4 cursor-pointer">
                                🔑 Enter Class Code
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side Column (Medal Treasure Box for Students + Recent Quizzes Feed) -->
        <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-6">

            @include('partials.medal-box')

            <div class="card-tactile flex-1">
                <div class="flex justify-between items-center mb-4 sm:mb-6 pb-3 border-b-4 border-dashed border-brand-border">
                    <h3 class="font-display text-lg sm:text-xl font-bold text-brand-text flex items-center gap-2">
                        @if(Auth::user()->isTeacher()) Recent Quizzes @else Latest Quizzes @endif
                    </h3>
                    <a href="{{ route('quiz.index') }}" class="text-xs font-black text-brand-coral hover:underline">
                        All Quizzes &rarr;
                    </a>
                </div>

                @if(isset($recentQuizzes) && count($recentQuizzes) > 0)
                    <div class="flex flex-col gap-3.5">
                        @foreach($recentQuizzes as $quiz)
                            <div class="bg-brand-bg border-3 border-brand-border rounded-2xl p-3.5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 hover:border-sky-300 transition-colors">
                                <div>
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-white border border-sky-200 text-[10px] font-black text-brand-coral uppercase mb-1">
                                        {{ $quiz->classroom->name ?? 'General' }}
                                    </span>
                                    <h5 class="font-black text-xs sm:text-sm text-brand-text leading-snug">{{ $quiz->title }}</h5>
                                </div>

                                <div class="flex justify-end gap-2 w-full sm:w-auto shrink-0">
                                    @if(Auth::user()->isTeacher())
                                        <a href="{{ route('quiz.show', $quiz->id) }}" class="btn-tactile-primary text-xs py-1.5 px-3 w-full sm:w-auto">
                                            View &rarr;
                                        </a>
                                    @else
                                        <a href="{{ route('quiz.show', $quiz->id) }}" class="btn-tactile-teal text-xs py-2 px-3.5 w-full sm:w-auto">
                                            Take Quiz
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 bg-brand-bg/60 rounded-2xl border-2 border-dashed border-brand-border">
                        <p class="text-xs font-bold text-slate-400">No quizzes available right now.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@if(Auth::user()->isTeacher())
    <!-- ➕ CREATE CLASS MODAL -->
    <div id="create-class-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-3 sm:p-4">
        <div class="card-tactile max-w-md w-full bg-white border-4 border-brand-coral shadow-2xl p-5 sm:p-6 relative max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4 pb-3 border-b-4 border-dashed border-brand-border">
                <h3 class="font-black text-base sm:text-lg text-brand-text">🏫 Create New Classroom</h3>
                <button type="button" onclick="document.getElementById('create-class-modal')?.classList.add('hidden')" class="w-8 h-8 rounded-full bg-brand-coral text-white font-black flex items-center justify-center hover:scale-105 active:scale-95 transition-transform cursor-pointer">✕</button>
            </div>

            <form action="{{ route('classes.store') }}" method="POST" class="flex flex-col gap-3.5">
                @csrf
                <div>
                    <label class="block text-[11px] font-black text-gray-500 uppercase mb-1">Classroom Name</label>
                    <input type="text" name="name" placeholder="e.g. Grade 1 - Diamond" class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border-3 border-brand-border bg-brand-bg font-extrabold text-xs sm:text-sm focus:outline-none focus:border-brand-coral" required>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-gray-500 uppercase mb-1">Subject</label>
                    <input type="text" name="subject" placeholder="e.g. Mathematics" class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border-3 border-brand-border bg-brand-bg font-extrabold text-xs sm:text-sm focus:outline-none focus:border-brand-coral" required>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-gray-500 uppercase mb-1">Section (Optional)</label>
                    <input type="text" name="section" placeholder="e.g. Section A" class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border-3 border-brand-border bg-brand-bg font-extrabold text-xs sm:text-sm focus:outline-none focus:border-brand-coral">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('create-class-modal')?.classList.add('hidden')" class="btn-tactile-ghost text-xs py-2 px-4 cursor-pointer">Cancel</button>
                    <button type="submit" class="btn-tactile-primary text-xs py-2 px-5 cursor-pointer">Create Class</button>
                </div>
            </form>
        </div>
    </div>
@else
    <!-- 🔑 JOIN CLASS MODAL -->
    <div id="join-class-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-3 sm:p-4">
        <div class="card-tactile max-w-md w-full bg-white border-4 border-brand-teal shadow-2xl p-5 sm:p-6 relative max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4 pb-3 border-b-4 border-dashed border-brand-border">
                <h3 class="font-display font-bold text-base sm:text-lg text-brand-text">🔑 Join a Subject World</h3>
                <button type="button" onclick="document.getElementById('join-class-modal')?.classList.add('hidden')" class="w-8 h-8 rounded-full bg-brand-coral text-white font-black flex items-center justify-center hover:scale-105 active:scale-95 transition-transform cursor-pointer">✕</button>
            </div>

            <form action="{{ route('classes.join') }}" method="POST" class="flex flex-col gap-3.5">
                @csrf
                <div>
                    <label class="block text-[11px] font-black text-gray-500 uppercase mb-1">Enter Teacher's Class Code</label>
                    <input type="text" name="class_code" placeholder="e.g. ABC123" class="w-full px-3.5 py-2.5 sm:py-3 rounded-xl border-3 border-brand-border bg-brand-bg font-extrabold text-sm sm:text-base tracking-widest text-center uppercase focus:outline-none focus:border-brand-teal" required>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="submit" class="btn-tactile-teal text-xs py-2.5 px-5 w-full cursor-pointer">Unlock Classroom</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection