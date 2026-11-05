@extends('layouts.app')

@section('title', 'Edit Quiz - BrainBalance')

@push('styles')
<style>
    .q-block { background: #FFF8F0; border: 2px solid #FFE4C4; border-radius: 14px; padding: 20px; margin-bottom: 16px; position: relative; }
    .q-block-title { font-size: 15px; font-weight: 900; color: #2D2D2D; margin-bottom: 12px; border-bottom: 2px dashed #FFE4C4; padding-bottom: 8px; }
    .remove-q-btn { position: absolute; top: 16px; right: 16px; color: #e74c3c; font-size: 12px; font-weight: 800; cursor: pointer; border: none; background: #F8D7DA; padding: 4px 8px; border-radius: 6px; }
</style>
@endpush

@section('content')
<div class="topbar">
    <div>
        <div class="tgreet">Edit Quiz ✨</div>
        <div class="tsub">Update your quiz details, questions, points, and timers.</div>
    </div>
    <a href="{{ route('dashboard') }}" class="btn-ghost">&larr; Back to Dashboard</a>
</div>

<div class="dashboard-grid">
    <div class="col-8">
        <div class="card">
            <div style="margin-bottom: 20px; padding: 12px 16px; background: #FFF8F0; border: 2px solid #FFE4C4; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="font-size: 11px; font-weight: 800; color: #888; text-transform: uppercase;">Editing Quiz For</span>
                    <div style="font-size: 16px; font-weight: 900; color: #FF6B6B;">{{ $quiz->classroom->name ?? 'Unknown Class' }}</div>
                </div>
                <span class="role-badge" style="background: #FFE4C4; color: #2D2D2D; font-weight: 800; padding: 4px 10px; border-radius: 8px;">Subject: {{ ucfirst($quiz->subject) }}</span>
            </div>

            <form id="edit-form" action="{{ route('quiz.update', $quiz->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-field">
                    <label class="form-label">Quiz Title</label>
                    <input type="text" name="quiz_title" class="form-input" value="{{ $quiz->title }}" required>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                    <div class="form-field" style="flex: 1;">
                        <label class="form-label">Publish Date</label>
                        <input type="datetime-local" name="published_at" class="form-input" value="{{ \Carbon\Carbon::parse($quiz->published_at)->format('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="form-field" style="flex: 1;">
                        <label class="form-label">Deadline</label>
                        <input type="datetime-local" name="deadline_at" class="form-input" value="{{ \Carbon\Carbon::parse($quiz->deadline_at)->format('Y-m-d\TH:i') }}" required>
                    </div>
                </div>

                <div id="manual-questions-container">
                    @foreach($quiz->questions as $index => $q)
                        <div class="q-block" id="mq-{{ $index }}">
                            <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $q->id }}">
                            @if($index > 0)
                                <button type="button" class="remove-q-btn" onclick="document.getElementById('mq-{{ $index }}').remove()">Remove Item</button>
                            @endif
                            <div class="q-block-title">Question Item</div>
                            
                            <div class="form-field">
                                <label class="form-label">Question Text</label>
                                <textarea name="questions[{{ $index }}][text]" class="form-textarea" rows="2" required>{{ $q->question_text }}</textarea>
                            </div>

                            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                                @for($i = 0; $i < 4; $i++)
                                    <div class="form-field" style="margin:0;">
                                        <label class="form-label">Option {{ $i + 1 }}</label>
                                        <input type="text" name="questions[{{ $index }}][options][]" class="form-input" value="{{ $q->options[$i] ?? '' }}" required>
                                    </div>
                                @endfor
                            </div>

                            <div class="form-field">
                                <label class="form-label" style="color: #10B981;">Correct Answer (Must exactly match an option)</label>
                                <input type="text" name="questions[{{ $index }}][answer]" class="form-input" style="border-color: #10B981;" value="{{ $q->correct_answer }}" required>
                            </div>

                            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                                <div class="form-field">
                                    <label class="form-label">Time Limit (Seconds)</label>
                                    <input type="number" name="questions[{{ $index }}][time_limit]" class="form-input" value="{{ $q->time_limit_seconds }}" min="5" required>
                                </div>
                                <div class="form-field">
                                    <label class="form-label">Points (XP)</label>
                                    <input type="number" name="questions[{{ $index }}][points]" class="form-input" value="{{ $q->points }}" min="1" required>
                                </div>
                                <div class="form-field">
                                    <label class="form-label">Difficulty</label>
                                    <select name="questions[{{ $index }}][difficulty]" class="form-input" required>
                                        <option value="easy" {{ $q->difficulty_level == 'easy' ? 'selected' : '' }}>Easy</option>
                                        <option value="medium" {{ $q->difficulty_level == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="hard" {{ $q->difficulty_level == 'hard' ? 'selected' : '' }}>Hard</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn-ghost" onclick="addManualQuestion()" style="width: 100%; justify-content: center; margin-bottom: 20px; border-style: dashed;">
                    + Add New Question
                </button>

                <div style="text-align: right; border-top: 2px solid #FFE4C4; padding-top: 16px;">
                    <button type="submit" class="btn-primary">💾 Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let qIndex = {{ $quiz->questions->count() }};

    function addManualQuestion() {
        const container = document.getElementById('manual-questions-container');
        const html = `
            <div class="q-block" id="mq-${qIndex}">
                <button type="button" class="remove-q-btn" onclick="document.getElementById('mq-${qIndex}').remove()">Remove Item</button>
                <div class="q-block-title">New Question Item</div>
                
                <div class="form-field">
                    <label class="form-label">Question Text</label>
                    <textarea name="questions[${qIndex}][text]" class="form-textarea" rows="2" placeholder="e.g. What is the powerhouse of the cell?" required></textarea>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div class="form-field" style="margin:0;"><label class="form-label">Option 1</label><input type="text" name="questions[${qIndex}][options][]" class="form-input" required></div>
                    <div class="form-field" style="margin:0;"><label class="form-label">Option 2</label><input type="text" name="questions[${qIndex}][options][]" class="form-input" required></div>
                    <div class="form-field" style="margin:0;"><label class="form-label">Option 3</label><input type="text" name="questions[${qIndex}][options][]" class="form-input" required></div>
                    <div class="form-field" style="margin:0;"><label class="form-label">Option 4</label><input type="text" name="questions[${qIndex}][options][]" class="form-input" required></div>
                </div>

                <div class="form-field">
                    <label class="form-label" style="color: #10B981;">Correct Answer (Must exactly match an option)</label>
                    <input type="text" name="questions[${qIndex}][answer]" class="form-input" style="border-color: #10B981;" required>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                    <div class="form-field">
                        <label class="form-label">Time Limit (Seconds)</label>
                        <input type="number" name="questions[${qIndex}][time_limit]" class="form-input" value="30" min="5" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Points (XP)</label>
                        <input type="number" name="questions[${qIndex}][points]" class="form-input" value="100" min="1" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Difficulty</label>
                        <select name="questions[${qIndex}][difficulty]" class="form-input" required>
                            <option value="easy">Easy</option>
                            <option value="medium" selected>Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        qIndex++;
    }
</script>
@endpush