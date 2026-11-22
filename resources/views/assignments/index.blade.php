{{-- FILE: resources/views/assignments/index.blade.php --}}
@extends('layouts.app')

@section('title', 'My Assignments - ' . config('app.name', 'BrainBalance'))

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Hero Banner -->
    <div class="p-6 rounded-3xl border-4 border-[#8B5CF6] shadow-[0_8px_0_#DDD6FE] mb-6"
         style="background: linear-gradient(135deg, #F5F3FF 0%, #EBF5FF 55%, #FFF7ED 100%);">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#8B5CF6] text-white font-black text-xs uppercase tracking-wider mb-2">
                    📝 Quest Board
                </span>
                <h1 class="font-display text-2xl sm:text-3xl font-bold text-[#0D2B4E]">
                    My Assignments
                </h1>
                <p class="text-xs sm:text-sm font-extrabold text-slate-600 mt-1">
                    Sorted by earliest due date first so you always know what to finish next!
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('assignments.index', ['filter' => 'todo']) }}"
                   class="px-3.5 py-2 rounded-2xl border-2 font-black text-xs transition-all {{ $filter === 'todo' ? 'bg-[#0066D6] text-white border-[#004FA8] shadow-xs' : 'bg-white text-slate-600 border-[#B8E2F8]' }}">
                    📋 To-Do ({{ $counts['todo'] }})
                </a>
                <a href="{{ route('assignments.index', ['filter' => 'overdue']) }}"
                   class="px-3.5 py-2 rounded-2xl border-2 font-black text-xs transition-all {{ $filter === 'overdue' ? 'bg-rose-500 text-white border-rose-700 shadow-xs' : 'bg-white text-rose-600 border-rose-200' }}">
                    ⚠️ Overdue ({{ $counts['overdue'] }})
                </a>
                <a href="{{ route('assignments.index', ['filter' => 'completed']) }}"
                   class="px-3.5 py-2 rounded-2xl border-2 font-black text-xs transition-all {{ $filter === 'completed' ? 'bg-[#68CC18] text-white border-[#4FA30D] shadow-xs' : 'bg-white text-[#3E8708] border-lime-300' }}">
                    ✅ Turned In ({{ $counts['completed'] }})
                </a>
                <a href="{{ route('assignments.index', ['filter' => 'all']) }}"
                   class="px-3.5 py-2 rounded-2xl border-2 font-black text-xs transition-all {{ $filter === 'all' ? 'bg-[#8B5CF6] text-white border-[#6D28D9] shadow-xs' : 'bg-white text-purple-700 border-purple-200' }}">
                    All ({{ $counts['all'] }})
                </a>
            </div>
        </div>
    </div>

    @if($assignments->isEmpty())
        <div class="card-tactile text-center py-12">
            <div class="text-5xl mb-3">🎉</div>
            <h3 class="font-display text-xl font-bold text-[#0D2B4E]">
                {{ $filter === 'todo' ? 'All caught up! No pending assignments.' : 'No assignments in this tab.' }}
            </h3>
            <p class="text-xs sm:text-sm font-bold text-slate-500 mt-1">
                Check back later or explore your subjects for new lessons and quiz quests!
            </p>
        </div>
    @else
        <div class="flex flex-col gap-5">
            @foreach($assignments as $assignment)
                @php
                    $dueInfo = $assignment->due_status;
                    $submission = Auth::user()->isStudent()
                        ? $assignment->submissions->first()
                        : null;

                    $countdownStyle = match(true) {
                        (bool) $submission               => 'bg-lime-100 text-lime-800 border-lime-300',
                        $dueInfo['state'] === 'overdue'  => 'bg-rose-100 text-rose-800 border-rose-300',
                        $dueInfo['state'] === 'urgent'   => 'bg-amber-100 text-amber-900 border-amber-400',
                        $dueInfo['state'] === 'upcoming' => 'bg-sky-100 text-[#0066D6] border-sky-300',
                        default                          => 'bg-slate-100 text-slate-600 border-slate-200',
                    };
                @endphp

                <div class="card-tactile border-3 {{ $submission ? 'border-lime-300' : ($dueInfo['state'] === 'overdue' ? 'border-rose-300' : 'border-[#B8E2F8]') }}">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                        <div class="flex-1">
                            <!-- Subject & Class Badges -->
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <a href="{{ route('subjects.show', $assignment->class_id) }}"
                                   class="px-3 py-0.5 rounded-full bg-[#EBF5FF] border border-[#93C5FD] text-[#0066D6] font-black text-[11px] uppercase hover:bg-sky-100">
                                    📚 {{ $assignment->classroom->name ?? 'Class' }}
                                </a>
                                <span class="px-2.5 py-0.5 rounded-full bg-[#FFF7ED] border border-[#FDBA74] text-[#C2410C] font-black text-[11px]">
                                    ⭐ {{ $assignment->points ?? 100 }} pts
                                </span>
                                <span class="px-3 py-0.5 rounded-full border text-xs font-black {{ $countdownStyle }}">
                                    @if($submission)
                                        ✅ Turned In
                                    @else
                                        ⏳ {{ $dueInfo['label'] }}
                                    @endif
                                </span>
                            </div>

                            <h2 class="font-display text-xl font-bold text-[#0D2B4E]">
                                {{ $assignment->title }}
                            </h2>

                            @if($assignment->due_at)
                                <p class="text-xs font-extrabold text-slate-500 mt-1">
                                    📅 Due Date: <span class="text-[#0D2B4E]">{{ $assignment->due_at->format('l, M d, Y • h:i A') }}</span>
                                </p>
                            @endif

                            <p class="font-bold text-slate-700 text-sm leading-relaxed whitespace-pre-line mt-3 bg-[#F4FAFF] p-3.5 rounded-2xl border-2 border-[#D4F1FC]">
                                {{ $assignment->content }}
                            </p>

                            @if($assignment->file_path)
                                <div class="mt-3">
                                    <a href="{{ route('announcements.download', $assignment->id) }}"
                                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border-2 border-[#B8E2F8] text-[#0066D6] font-black text-xs hover:bg-sky-50">
                                        📎 Download Reference File: {{ $assignment->file_name }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if(Auth::user()->isStudent())
                            <!-- Student Turn-In Panel -->
                            <div class="w-full md:w-80 bg-[#F2FAFF] border-2 border-[#B8E2F8] rounded-2xl p-4 shrink-0">
                                @if($submission)
                                    <div class="mb-3 pb-2 border-b-2 border-dashed border-sky-200">
                                        <div class="text-xs font-black text-[#3E8708]">
                                            ✅ Submitted {{ $submission->submitted_at?->diffForHumans() }}
                                        </div>
                                        @if($submission->score !== null)
                                            <div class="mt-1.5 px-3 py-1.5 rounded-xl bg-amber-100 border border-amber-300 text-amber-950 font-black text-xs">
                                                🏆 Grade: {{ $submission->score }} / {{ $assignment->points ?? 100 }}
                                            </div>
                                        @endif
                                        @if($submission->teacher_feedback)
                                            <div class="mt-1.5 text-xs font-bold text-slate-600 bg-white p-2 rounded-xl border border-slate-200">
                                                💬 Teacher: "{{ $submission->teacher_feedback }}"
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <form action="{{ route('assignments.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-2.5">
                                    @csrf
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Your Answer</label>
                                        <textarea name="answer_text" rows="3" placeholder="Write your answer here..."
                                                  class="w-full p-2.5 rounded-xl border-2 border-[#B8E2F8] bg-white font-bold text-xs text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] resize-none">{{ $submission?->answer_text }}</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">
                                            {{ $submission?->file_name ? 'Replace File (' . $submission->file_name . ')' : 'Attach File (Optional)' }}
                                        </label>
                                        <input type="file" name="file"
                                               class="w-full text-[11px] font-bold text-slate-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-black file:bg-sky-100 file:text-[#0066D6] cursor-pointer">
                                    </div>

                                    <button type="submit" class="btn-tactile-primary w-full text-xs py-2">
                                        {{ $submission ? '🔄 Update Submission' : '📤 Turn In Assignment' }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection