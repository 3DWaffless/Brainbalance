{{-- FILE: resources/views/quiz/results.blade.php --}}
@extends('layouts.app')

@section('title', 'Class Analytics & Diagnostics - BrainBalance')

@section('content')
<div class="flex flex-col gap-6 sm:gap-8 w-full">
    <div class="card-tactile relative overflow-hidden flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="bb-spectrum-bar absolute top-0 left-0 right-0"></div>
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#ECFCCB] border-2 border-[#A3E635] text-[#3E8708] font-black text-[11px] uppercase tracking-wider mb-1.5">
                🧑‍🏫 Teacher Command Center
            </div>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-[#0D2B4E]">
                Class Performance &amp; Weak Topic Diagnostics 📊
            </h1>
            <p class="text-xs sm:text-sm font-bold text-[#5A7696] mt-1">
                Track student quiz results, identify struggling topics, and measure engagement.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-tactile-ghost shrink-0">&larr; Back to Dashboard</a>
    </div>

    {{-- Spectrum KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
        <div class="card-tactile text-center border-[#93C5FD]" style="background: linear-gradient(135deg, #EBF5FF 0%, #E0F7FC 100%);">
            <div class="text-xs font-black text-[#0066D6] uppercase tracking-wider">Total Submissions</div>
            <div class="font-display text-3xl font-bold text-[#0066D6] mt-1">{{ $totalAttempts }}</div>
        </div>
        <div class="card-tactile text-center border-[#A3E635]" style="background: linear-gradient(135deg, #F4FCE3 0%, #ECFCCB 100%);">
            <div class="text-xs font-black text-[#3E8708] uppercase tracking-wider">Average Score</div>
            <div class="font-display text-3xl font-bold text-[#3E8708] mt-1">{{ $avgScore }} pts</div>
        </div>
        <div class="card-tactile text-center border-[#FDBA74]" style="background: linear-gradient(135deg, #FFF7ED 0%, #FFEDD5 100%);">
            <div class="text-xs font-black text-[#C2410C] uppercase tracking-wider">Overall Accuracy</div>
            <div class="font-display text-3xl font-bold text-[#C2410C] mt-1">{{ $overallAccuracy }}%</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Weak Topic Diagnostics --}}
        <div class="lg:col-span-8 card-tactile">
            <h2 class="font-display text-lg sm:text-xl font-bold text-[#0D2B4E] mb-4 pb-3 border-b-2 border-dashed border-[#B8E2F8]">
                🎯 Weak Topic Diagnostics (Lowest Accuracy Tiers)
            </h2>

            @if(!empty($topicStats))
                <div class="flex flex-col gap-3.5">
                    @foreach($topicStats as $topicName => $stats)
                        @php
                            $topicAccuracy = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100) : 0;
                            $barColor = $topicAccuracy < 50 ? '#FF8400' : ($topicAccuracy < 75 ? '#1DC4DF' : '#68CC18');
                        @endphp
                        <div class="p-4 border-[3px] border-[#B8E2F8] rounded-2xl bg-[#F2FAFF]">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-black text-sm sm:text-base text-[#0D2B4E]">{{ $topicName }}</span>
                                <span class="text-xs sm:text-sm font-black" style="color: {{ $barColor }};">{{ $topicAccuracy }}% Correct</span>
                            </div>
                            <div class="h-3 bg-[#E0F2FE] rounded-full overflow-hidden border border-[#BAE6FD]">
                                <div style="width: {{ $topicAccuracy }}%; height: 100%; background: {{ $barColor }}; border-radius: 999px;"></div>
                            </div>
                            <div class="text-xs font-bold text-[#5A7696] mt-2">
                                Based on {{ $stats['total'] }} answered questions in this topic.
                                @if($topicAccuracy < 50)
                                    <strong class="text-[#C2410C]">Recommendation: Upload a supplementary review module.</strong>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10 bg-[#F2FAFF] rounded-2xl border-2 border-dashed border-[#B8E2F8]">
                    <p class="text-xs sm:text-sm text-[#5A7696] font-extrabold">No diagnostic data collected yet.</p>
                </div>
            @endif
        </div>

        {{-- Recent Submissions --}}
        <div class="lg:col-span-4 card-tactile">
            <h2 class="font-display text-lg sm:text-xl font-bold text-[#0D2B4E] mb-4 pb-3 border-b-2 border-dashed border-[#B8E2F8]">
                🕒 Recent Submissions
            </h2>

            @if($sessions->count() > 0)
                <div class="flex flex-col gap-2.5">
                    @foreach($sessions->take(8) as $s)
                        <div class="p-3 border-2 border-[#B8E2F8] rounded-2xl bg-[#F2FAFF]">
                            <div class="font-black text-xs sm:text-sm text-[#0D2B4E]">
                                {{ $s->student->first_name ?? 'Student' }} {{ $s->student->last_name ?? '' }}
                            </div>
                            <div class="text-[11px] font-bold text-[#5A7696] mt-0.5">
                                {{ $s->quiz->title ?? 'Quiz' }} &bull; Score: <span class="text-[#0066D6] font-black">{{ $s->score }}/{{ $s->total_questions }}</span> (+{{ $s->total_xp }} XP)
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10 bg-[#F2FAFF] rounded-2xl border-2 border-dashed border-[#B8E2F8]">
                    <p class="text-xs sm:text-sm text-[#5A7696] font-extrabold">No quiz attempts logged yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection