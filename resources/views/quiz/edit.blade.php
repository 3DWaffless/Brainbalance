{{-- FILE: resources/views/quiz/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Quiz - ' . config('app.name', 'BrainBalance'))

@section('content')
<div class="flex flex-col gap-6 w-full">
    <!-- Header -->
    <div class="card-tactile flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Quiz Editor</div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-brand-text flex items-center gap-2 mt-0.5">
                Edit Quiz ✏️
            </h1>
            <p class="text-xs sm:text-sm font-extrabold text-slate-500 mt-1">
                Update your quiz details, questions, points, and timers.
            </p>
        </div>
        <a href="{{ route('quiz.index') }}" class="btn-tactile-ghost text-xs py-2.5 px-4">
            &larr; Back to Quizzes
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Main Edit Form (8 Cols) -->
        <div class="lg:col-span-8 card-tactile">
            <div class="bg-brand-bg border-3 border-brand-border rounded-2xl p-4 flex flex-wrap justify-between items-center gap-3 mb-6">
                <div>
                    <div class="text-[10px] font-black text-slate-400 uppercase">Editing Quiz For</div>
                    <div class="font-display text-lg font-bold text-brand-coral">{{ $quiz->classroom->name ?? 'Unknown Class' }}</div>
                </div>
                <span class="bg-sky-100 text-brand-coral border border-sky-200 font-black text-xs px-3.5 py-1.5 rounded-full">
                    Subject: {{ ucfirst($quiz->subject) }}
                </span>
            </div>

            <form id="edit-form" action="{{ route('quiz.update', $quiz->id) }}" method="POST" class="flex flex-col gap-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    <div class="sm:col-span-8">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1">Quiz Title</label>
                        <input type="text" name="quiz_title" value="{{ $quiz->title }}" class="w-full px-4 py-3 rounded-xl border-3 border-brand-border bg-brand-bg font-extrabold text-sm text-brand-text focus:outline-none focus:border-brand-coral focus:bg-white" required>
                    </div>
                    <div class="sm:col-span-4">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1">Max Attempts</label>
                        <input type="number" name="max_attempts" value="{{ $quiz->max_attempts ?? 1 }}" min="1" class="w-full px-4 py-3 rounded-xl border-3 border-brand-border bg-brand-bg font-extrabold text-sm text-brand-text focus:outline-none focus:border-brand-coral focus:bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1">Publish Date</label>
                        <input type="datetime-local" name="published_at" value="{{ \Carbon\Carbon::parse($quiz->published_at ?? now())->format('Y-m-d\TH:i') }}" class="w-full px-4 py-3 rounded-xl border-3 border-brand-border bg-brand-bg font-extrabold text-sm text-brand-text focus:outline-none focus:border-brand-coral focus:bg-white" required>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1">Deadline</label>
                        <input type="datetime-local" name="deadline_at" value="{{ \Carbon\Carbon::parse($quiz->deadline_at ?? now()->addDays(7))->format('Y-m-d\TH:i') }}" class="w-full px-4 py-3 rounded-xl border-3 border-brand-border bg-brand-bg font-extrabold text-sm text-brand-text focus:outline-none focus:border-brand-coral focus:bg-white" required>
                    </div>
                </div>

                <h3 class="font-display text-lg font-bold text-brand-text pt-3 border-t-4 border-dashed border-brand-border">
                    Quiz Questions ({{ $quiz->questions->count() }})
                </h3>

                <div id="manual-questions-container" class="flex flex-col gap-4">
                    @foreach($quiz->questions as $index => $q)
                        @php
                            $qType = $q->question_type ?? $q->type ?? (!empty($q->options) ? 'multiple_choice' : 'identification');
                        @endphp
                        <div class="q-block bg-brand-bg border-3 border-brand-border rounded-2xl p-4 sm:p-5 flex flex-col gap-3.5" id="mq-{{ $index }}">
                            <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $q->id }}">

                            <div class="flex justify-between items-center pb-2.5 border-b-2 border-dashed border-brand-border">
                                <span class="font-black text-sm text-brand-coral q-number">Question {{ $index + 1 }}</span>
                                @if($index > 0)
                                    <button type="button" onclick="removeQuestionBlock('mq-{{ $index }}')" class="bg-rose-100 text-rose-600 border border-rose-200 rounded-xl px-3 py-1 text-xs font-black hover:bg-rose-200 transition-colors cursor-pointer">
                                        🗑️ Remove Item
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Question Type</label>
                                    <select name="questions[{{ $index }}][type]" onchange="toggleEditOptions('mq-{{ $index }}', this.value)" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs">
                                        <option value="multiple_choice" {{ $qType === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice 🔘</option>
                                        <option value="identification" {{ $qType === 'identification' ? 'selected' : '' }}>Identification ✏️</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Difficulty</label>
                                    <select name="questions[{{ $index }}][difficulty]" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs" required>
                                        <option value="easy" {{ $q->difficulty_level == 'easy' ? 'selected' : '' }}>Easy</option>
                                        <option value="medium" {{ $q->difficulty_level == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="hard" {{ $q->difficulty_level == 'hard' ? 'selected' : '' }}>Hard</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Question Text</label>
                                <textarea name="questions[{{ $index }}][text]" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs sm:text-sm focus:outline-none focus:border-brand-coral resize-none" required>{{ $q->question_text }}</textarea>
                            </div>

                            <div id="edit-opts-mq-{{ $index }}" class="{{ $qType === 'multiple_choice' ? 'block' : 'hidden' }}">
                                <label class="block text-[10px] font-black text-slate-500 uppercase mb-1.5">Multiple Choice Options</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @for($i = 0; $i < 4; $i++)
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-400 mb-0.5">Option {{ ['A','B','C','D'][$i] }}</label>
                                            <input type="text" name="questions[{{ $index }}][options][]" value="{{ $q->options[$i] ?? '' }}" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs opt-field" {{ $qType === 'multiple_choice' ? 'required' : '' }}>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-emerald-600 uppercase mb-1">Correct Answer (Must match an option for Multiple Choice)</label>
                                <input type="text" name="questions[{{ $index }}][answer]" value="{{ $q->correct_answer }}" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-emerald-400 bg-white font-extrabold text-xs sm:text-sm focus:outline-none" required>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Time Limit (Seconds)</label>
                                    <input type="number" name="questions[{{ $index }}][time_limit]" value="{{ $q->time_limit_seconds }}" min="5" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Points (XP)</label>
                                    <input type="number" name="questions[{{ $index }}][points]" value="{{ $q->points }}" min="1" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs" required>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" onclick="addManualQuestion()" class="w-full py-3 px-4 rounded-2xl border-3 border-dashed border-brand-coral bg-brand-bg text-brand-coral font-black text-xs sm:text-sm hover:bg-sky-50 transition-colors cursor-pointer">
                    + Add New Question
                </button>

                <div class="flex justify-end pt-4 border-t-4 border-dashed border-brand-border">
                    <button type="submit" class="btn-tactile-teal py-3 px-6 text-sm">
                        💾 Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Tips Sidebar (4 Cols) -->
        <div class="lg:col-span-4 card-tactile text-white" style="background: linear-gradient(180deg, #8CE022 0%, #65C91A 100%) !important; border-color: #4EA30E !important;">
            <h3 class="font-display text-lg font-bold mb-4 flex items-center gap-2">💡 Editing Tips</h3>
            <div class="flex flex-col gap-3 text-xs sm:text-sm font-bold leading-relaxed">
                <p class="bg-white/15 p-3 rounded-2xl">🎯 <strong>Exact Answer Match:</strong> For multiple-choice items, make sure the Correct Answer matches one of the four options word-for-word.</p>
                <p class="bg-white/15 p-3 rounded-2xl">⏱️ <strong>Kid-Friendly Pacing:</strong> Give Grade 1 learners at least 30–45 seconds per reading question so they don't feel rushed.</p>
                <p class="bg-white/15 p-3 rounded-2xl">⭐ <strong>XP Balance:</strong> Award higher XP (150–200) on Hard questions to reward persistence.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let qIndex = {{ $quiz->questions->count() }};

    function addManualQuestion() {
        const container = document.getElementById('manual-questions-container');
        const blockId = `mq-${qIndex}`;
        const html = `
            <div class="q-block bg-brand-bg border-3 border-brand-border rounded-2xl p-4 sm:p-5 flex flex-col gap-3.5" id="${blockId}">
                <div class="flex justify-between items-center pb-2.5 border-b-2 border-dashed border-brand-border">
                    <span class="font-black text-sm text-brand-coral q-number">New Question</span>
                    <button type="button" onclick="removeQuestionBlock('${blockId}')" class="bg-rose-100 text-rose-600 border border-rose-200 rounded-xl px-3 py-1 text-xs font-black hover:bg-rose-200 transition-colors cursor-pointer">
                        🗑️ Remove Item
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Question Type</label>
                        <select name="questions[${qIndex}][type]" onchange="toggleEditOptions('${blockId}', this.value)" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs">
                            <option value="multiple_choice" selected>Multiple Choice 🔘</option>
                            <option value="identification">Identification ✏️</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Difficulty</label>
                        <select name="questions[${qIndex}][difficulty]" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs" required>
                            <option value="easy">Easy</option>
                            <option value="medium" selected>Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Question Text</label>
                    <textarea name="questions[${qIndex}][text]" rows="2" placeholder="e.g. What is 5 + 3?" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs sm:text-sm focus:outline-none focus:border-brand-coral resize-none" required></textarea>
                </div>
                <div id="edit-opts-${blockId}" class="block">
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1.5">Multiple Choice Options</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div><label class="block text-[10px] font-bold text-slate-400 mb-0.5">Option A</label><input type="text" name="questions[${qIndex}][options][]" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs opt-field" required></div>
                        <div><label class="block text-[10px] font-bold text-slate-400 mb-0.5">Option B</label><input type="text" name="questions[${qIndex}][options][]" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs opt-field" required></div>
                        <div><label class="block text-[10px] font-bold text-slate-400 mb-0.5">Option C</label><input type="text" name="questions[${qIndex}][options][]" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs opt-field" required></div>
                        <div><label class="block text-[10px] font-bold text-slate-400 mb-0.5">Option D</label><input type="text" name="questions[${qIndex}][options][]" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs opt-field" required></div>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-emerald-600 uppercase mb-1">Correct Answer</label>
                    <input type="text" name="questions[${qIndex}][answer]" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-emerald-400 bg-white font-extrabold text-xs sm:text-sm focus:outline-none" required>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Time Limit (Seconds)</label>
                        <input type="number" name="questions[${qIndex}][time_limit]" value="30" min="5" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Points (XP)</label>
                        <input type="number" name="questions[${qIndex}][points]" value="100" min="1" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-border bg-white font-extrabold text-xs" required>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        qIndex++;
        renumberBlocks();
    }

    function removeQuestionBlock(id) {
        document.getElementById(id)?.remove();
        renumberBlocks();
    }

    function renumberBlocks() {
        const blocks = document.querySelectorAll('#manual-questions-container .q-block');
        blocks.forEach((block, idx) => {
            const label = block.querySelector('.q-number');
            if (label) label.textContent = `Question ${idx + 1}`;
        });
    }

    function toggleEditOptions(blockId, type) {
        const wrap = document.getElementById(`edit-opts-${blockId}`);
        if (!wrap) return;
        const fields = wrap.querySelectorAll('.opt-field');
        if (type === 'identification') {
            wrap.classList.add('hidden');
            wrap.classList.remove('block');
            fields.forEach(f => f.removeAttribute('required'));
        } else {
            wrap.classList.remove('hidden');
            wrap.classList.add('block');
            fields.forEach(f => f.setAttribute('required', 'required'));
        }
    }
</script>
@endpush