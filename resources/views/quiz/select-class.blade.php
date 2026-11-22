{{-- FILE: resources/views/quiz/select-class.blade.php --}}
@extends('layouts.app')

@section('title', 'Select Class - ' . config('app.name', 'BrainBalance'))

@section('content')
<div class="max-w-3xl mx-auto flex flex-col gap-6">
    <div class="card-tactile relative overflow-hidden">
        <div class="bb-spectrum-bar absolute top-0 left-0 right-0"></div>
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#E6F7FC] border-2 border-[#99E2F5] text-[#0066D6] font-black text-[11px] uppercase tracking-wider mb-1.5">
            ⚡ Quiz Builder
        </div>
        <h1 class="font-display text-2xl sm:text-3xl font-bold text-[#0D2B4E]">Create Quiz 📝</h1>
        <p class="text-xs sm:text-sm font-bold text-[#5A7696] mt-1">Select the classroom you want to build or generate a quiz for.</p>
    </div>

    <div class="card-tactile">
        @if($classes->isEmpty())
            <div class="text-center py-10">
                <div class="text-5xl mb-3">🏫</div>
                <div class="font-display text-xl font-bold text-[#0D2B4E]">No Active Classes</div>
                <p class="text-xs sm:text-sm text-[#5A7696] font-bold mt-1 mb-5">You need to create a class before generating quizzes.</p>
                <a href="{{ route('dashboard') }}" class="btn-tactile-primary">Return to Dashboard</a>
            </div>
        @else
            <h3 class="font-display text-base sm:text-lg font-bold text-[#0D2B4E] mb-4 pb-2.5 border-b-2 border-dashed border-[#B8E2F8]">
                Select Target Class
            </h3>
            <div class="flex flex-col gap-3">
                @foreach($classes as $class)
                    <a href="{{ route('quiz.create', ['class_id' => $class->id]) }}"
                       class="card-tactile-interactive flex items-center justify-between gap-4 no-underline">
                        <div>
                            <div class="font-black text-base text-[#0D2B4E]">{{ $class->name }}</div>
                            <div class="text-xs text-[#5A7696] font-extrabold mt-0.5">
                                Class Code: <span class="text-[#0066D6]">{{ $class->class_code ?? $class->code }}</span>
                            </div>
                        </div>
                        <span class="btn-tactile-teal text-xs py-2 px-4 shrink-0">Select Class &rarr;</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection