{{-- FILE: resources/views/subjects/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Subjects - ' . config('app.name', 'BrainBalance'))

@section('content')
@php
    $isTeacherOrAdmin = Auth::user()->isTeacher() || Auth::user()->role === 'admin';
@endphp

<!-- Header Banner -->
<div class="card-tactile mb-6 relative overflow-hidden"
     style="{{ !$isTeacherOrAdmin ? 'background: linear-gradient(135deg, #EBF5FF 0%, #FFFFFF 55%, #ECFCCB 100%) !important;' : '' }}">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border-2 border-sky-200 text-brand-coral font-black text-[11px] uppercase tracking-wider mb-2 shadow-2xs">
                <span>{{ $isTeacherOrAdmin ? 'Class Management' : '🗺️ Explorer Map' }}</span>
            </div>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-brand-text flex items-center gap-2">
                {{ $isTeacherOrAdmin ? 'Manage Subjects & Classes' : 'My Subjects' }}
            </h1>
            <p class="text-xs sm:text-sm font-extrabold text-slate-500 mt-1">
                {{ $isTeacherOrAdmin
                    ? 'Select a classroom to post announcements, upload modules, manage quizzes, and view student rosters.'
                    : 'Choose a subject world below to read lessons, open your treasure chest, and play quizzes!' }}
            </p>
        </div>

        @if(!$isTeacherOrAdmin)
            <a href="{{ route('dashboard') }}" class="btn-tactile-primary text-xs py-2.5 px-4 shrink-0">
                🔑 Join Class Code
            </a>
        @endif
    </div>
</div>

@if(isset($classes) && $classes->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
        @foreach($classes as $class)
            @php
                $subjText = strtolower(($class->subject ?? '') . ' ' . ($class->name ?? ''));
                $isMath    = str_contains($subjText, 'math');
                $isEnglish = str_contains($subjText, 'eng') || str_contains($subjText, 'read');
                $isScience = str_contains($subjText, 'sci');

                $theme = match(true) {
                    $isMath => [
                        'headerBg' => 'linear-gradient(135deg, #DBEAFE 0%, #EFF6FF 100%)',
                        'border'   => 'border-sky-300',
                        'pillBg'   => 'bg-sky-500 text-white border-sky-600',
                        'emoji'    => '🔢',
                        'btn'      => 'btn-tactile-primary',
                    ],
                    $isEnglish => [
                        'headerBg' => 'linear-gradient(135deg, #DCFCE7 0%, #F0FDF4 100%)',
                        'border'   => 'border-lime-300',
                        'pillBg'   => 'bg-lime-600 text-white border-lime-700',
                        'emoji'    => '📖',
                        'btn'      => 'btn-tactile-teal',
                    ],
                    $isScience => [
                        'headerBg' => 'linear-gradient(135deg, #F3E8FF 0%, #FAF5FF 100%)',
                        'border'   => 'border-purple-300',
                        'pillBg'   => 'bg-purple-600 text-white border-purple-700',
                        'emoji'    => '🔬',
                        'btn'      => 'btn-tactile-primary',
                    ],
                    default => [
                        'headerBg' => 'linear-gradient(135deg, #FEF3C7 0%, #FFFBEB 100%)',
                        'border'   => 'border-amber-300',
                        'pillBg'   => 'bg-amber-500 text-amber-950 border-amber-600',
                        'emoji'    => '🎓',
                        'btn'      => 'btn-tactile-orange',
                    ],
                };
            @endphp

            <a href="{{ route('subjects.show', $class->id) }}"
               class="rounded-3xl border-4 {{ $theme['border'] }} bg-white flex flex-col overflow-hidden no-underline group hover:-translate-y-1.5 transition-all duration-200 shadow-sm">
                <!-- Subject Banner Header -->
                <div class="p-5 relative border-b-3 border-dashed border-brand-border" style="background: {{ $theme['headerBg'] }};">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-white border-3 border-brand-border flex items-center justify-center text-2xl shadow-2xs group-hover:scale-110 transition-transform">
                            {{ $theme['emoji'] }}
                        </div>

                        @if($class->class_code || $class->code)
                            <div class="bg-white/95 px-3 py-1 rounded-xl font-black text-xs tracking-widest text-brand-purple border-2 border-brand-border shadow-2xs">
                                {{ $class->class_code ?? $class->code }}
                            </div>
                        @endif
                    </div>

                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $theme['pillBg'] }} mb-1.5">
                        {{ $class->subject ?? 'General' }}
                    </span>

                    <h2 class="font-display text-lg sm:text-xl font-bold text-brand-text tracking-tight leading-snug group-hover:text-brand-coral transition-colors">
                        {{ $class->name }}
                    </h2>

                    <p class="text-xs font-extrabold text-slate-500 mt-1">
                        Section: <span class="text-brand-text">{{ $class->section ?? 'N/A' }}</span>
                    </p>
                </div>

                <!-- Card Footer / Roster Count -->
                <div class="p-4 bg-white flex justify-between items-center mt-auto">
                    <span class="text-xs font-black text-slate-500 flex items-center gap-1.5">
                        👥 {{ $class->students_count ?? $class->students()->count() }} {{ $isTeacherOrAdmin ? 'Enrolled' : 'Classmates' }}
                    </span>
                    <span class="{{ $theme['btn'] }} text-xs py-1.5 px-3.5 group-hover:translate-x-0.5 transition-transform">
                        {{ $isTeacherOrAdmin ? 'Manage' : 'Enter World' }} &rarr;
                    </span>
                </div>
            </a>
        @endforeach
    </div>
@else
    <div class="card-tactile text-center py-14 px-4 bg-white">
        <div class="text-5xl mb-3">🗺️</div>
        <h3 class="font-display text-xl font-bold text-brand-text mb-1">No Subject Worlds Yet</h3>
        <p class="text-xs sm:text-sm font-bold text-slate-400 max-w-md mx-auto mb-5">
            {{ $isTeacherOrAdmin
                ? 'Create a classroom on your dashboard to begin managing subjects and quizzes.'
                : 'Enter your teacher’s class code on the Adventure Hub to unlock your first subject!' }}
        </p>
        <a href="{{ route('dashboard') }}" class="btn-tactile-primary text-xs py-2.5 px-5">
            &larr; Go to Dashboard
        </a>
    </div>
@endif
@endsection