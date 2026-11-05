@extends('layouts.app')

@section('title', 'Create Quiz - BrainBalance')

@push('styles')
<style>
    .mode-tabs { 
        display: flex; 
        gap: 10px; 
        margin-bottom: 20px; 
        background: #FFF8F0; 
        padding: 6px; 
        border-radius: 12px; 
        border: 2px solid #FFE4C4; 
    }
    .mode-btn { 
        flex: 1; 
        padding: 12px; 
        text-align: center; 
        font-weight: 900; 
        font-size: 14px; 
        border-radius: 8px; 
        cursor: pointer; 
        transition: all 0.2s; 
        color: #888; 
    }
    .mode-btn.active { 
        background: #FF6B6B; 
        color: #fff; 
        box-shadow: 0 4px 10px rgba(255,107,107,0.2); 
    }
    .q-block { 
        background: #FFF8F0; 
        border: 2px solid #FFE4C4; 
        border-radius: 14px; 
        padding: 20px; 
        margin-bottom: 16px; 
        position: relative; 
    }
    .q-block-title { 
        font-size: 15px; 
        font-weight: 900; 
        color: #2D2D2D; 
        margin-bottom: 12px; 
        border-bottom: 2px dashed #FFE4C4; 
        padding-bottom: 8px; 
    }
    .remove-q-btn { 
        position: absolute; 
        top: 16px; 
        right: 16px; 
        color: #e74c3c; 
        font-size: 12px; 
        font-weight: 800; 
        cursor: pointer; 
        border: none; 
        background: #F8D7DA; 
        padding: 4px 8px; 
        border-radius: 6px; 
    }
</style>
@endpush

@section('content')
<div class="topbar">
    <div>
        <div class="tgreet">Create Quiz ✨</div>
        <div class="tsub">
            {{ isset($generatedQuestions) ? 'Review, edit, and publish your AI-generated quiz.' : 'Choose between AI generation or build your quiz manually from scratch.' }}
        </div>
    </div>
    <a href="{{ route('quiz.create') }}" class="btn-ghost">&larr; Change Selected Class</a>
</div>

<div class="dashboard-grid">
    <div class="col-8">
        <div class="card">
            {{-- Target Class Badge --}}
            <div style="margin-bottom: 20px; padding: 12px 16px; background: #FFF8F0; border: 2px solid #FFE4C4; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="font-size: 11px; font-weight: 800; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">Assigned Class</span>
                    <div style="font-size: 16px; font-weight: 900; color: #FF6B6B;">{{ $selectedClass->name }}</div>
                </div>
                <span class="role-badge" style="background: #FFE4C4; color: #2D2D2D; font-weight: 800; padding: 4px 10px; border-radius: 8px;">
                    Code: {{ $selectedClass->code }}
                </span>
            </div>

            @if(isset($generatedQuestions))
                {{-- ============================================================== --}}
                {{-- PHASE 2: EDIT & PUBLISH AI GENERATED QUESTIONS                 --}}
                {{-- ============================================================== --}}
                <form action="{{ route('quiz.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                    <input type="hidden" name="quiz_mode" value="ai">
                    <input type="hidden" name="quiz_title" value="{{ $quiz_title }}">
                    <input type="hidden" name="subject" value="{{ $subject }}">
                    <input type="hidden" name="published_at" value="{{ $published_at }}">
                    <input type="hidden" name="deadline_at" value="{{ $deadline_at }}">
                    <input type="hidden" name="max_attempts" value="{{ $max_attempts ?? 1 }}">
                    
                    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 16px;">
                        <h3 style="font-size: 16px; font-weight: 900; color: #2D2D2D;">
                            Edit Generated Questions for "{{ $quiz_title }}"
                        </h3>
                    </div>

                    @foreach($generatedQuestions as $index => $q)
                        <div class="q-block">
                            <div class="q-block-title">Question {{ $index + 1 }}</div>
                            
                            <div class="form-field">
                                <label class="form-label">Question Text</label>
                                <textarea name="questions[{{ $index }}][text]" class="form-textarea" rows="2" required>{{ $q['question'] }}</textarea>
                            </div>

                            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                                @for($i = 0; $i < 4; $i++)
                                    <div class="form-field" style="margin:0;">
                                        <label class="form-label">Option {{ $i + 1 }}</label>
                                        <input type="text" name="questions[{{ $index }}][options][]" class="form-input" value="{{ $q['options'][$i] ?? '' }}" required>
                                    </div>
                                @endfor
                            </div>

                            <div class="form-field">
                                <label class="form-label" style="color: #10B981;">Correct Answer (Must exactly match one of the options above)</label>
                                <input type="text" name="questions[{{ $index }}][answer]" class="form-input" style="border-color: #10B981;" value="{{ $q['answer'] }}" required>
                            </div>

                            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                                <div class="form-field">
                                    <label class="form-label">Time Limit (Seconds)</label>
                                    <input type="number" name="questions[{{ $index }}][time_limit]" class="form-input" value="30" min="5" required>
                                </div>
                                <div class="form-field">
                                    <label class="form-label">Points (XP)</label>
                                    <input type="number" name="questions[{{ $index }}][points]" class="form-input" value="100" min="1" required>
                                </div>
                                <div class="form-field">
                                    <label class="form-label">Difficulty</label>
                                    <select name="questions[{{ $index }}][difficulty]" class="form-input" required>
                                        <option value="easy" {{ ($q['difficulty'] ?? '') == 'easy' ? 'selected' : '' }}>Easy</option>
                                        <option value="medium" {{ ($q['difficulty'] ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="hard" {{ ($q['difficulty'] ?? '') == 'hard' ? 'selected' : '' }}>Hard</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div style="text-align: right; border-top: 2px solid #FFE4C4; padding-top: 16px; margin-top: 8px;">
                        <button type="submit" class="btn-primary">
                            🚀 Publish AI Quiz to Class
                        </button>
                    </div>
                </form>

            @else
                {{-- ============================================================== --}}
                {{-- PHASE 1: MODE SELECTION (AI GENERATION VS MANUAL BUILD)        --}}
                {{-- ============================================================== --}}
                
                <div class="mode-tabs">
                    <div id="ai-mode-btn" class="mode-btn active" onclick="switchMode('ai')">🤖 AI Generated</div>
                    <div id="manual-mode-btn" class="mode-btn" onclick="switchMode('manual')">✍️ Manual Creation</div>
                </div>

                {{-- AI FORM --}}
                <form id="ai-form" action="{{ route('quiz.generate') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">

                    <div class="form-field">
                        <label class="form-label">Quiz Title</label>
                        <input type="text" name="quiz_title" class="form-input" value="{{ old('quiz_title') }}" placeholder="e.g. Chapter 1: Networking Basics" required>
                    </div>

                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                        <div class="form-field">
                            <label class="form-label">Subject Context</label>
                            <input type="text" name="subject" class="form-input" value="{{ old('subject', 'General') }}" placeholder="e.g. Information Technology" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Number of Questions</label>
                            <input type="number" name="question_count" class="form-input" value="{{ old('question_count', 5) }}" min="1" max="50" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Max Attempts per Student</label>
                            <input type="number" name="max_attempts" class="form-input" value="{{ old('max_attempts', 1) }}" min="1" placeholder="e.g. 1" required>
                        </div>
                    </div>

                    <div class="form-field">
                        <label class="form-label">Upload Module (PDF only)</label>
                        <div style="border: 2px dashed #FFB347; padding: 2rem; border-radius: 12px; text-align: center; background: #FFF8F0;">
                            <input type="file" name="module_pdf" accept="application/pdf" required style="width: 100%; cursor: pointer;">
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; margin-bottom: 12px;">
                        <div class="form-field" style="flex: 1;">
                            <label class="form-label">Publish Date</label>
                            <input type="datetime-local" name="published_at" class="form-input" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="form-field" style="flex: 1;">
                            <label class="form-label">Deadline</label>
                            <input type="datetime-local" name="deadline_at" class="form-input" value="{{ old('deadline_at', now()->addDays(7)->format('Y-m-d\TH:i')) }}" required>
                        </div>
                    </div>

                    <div style="text-align: right; border-top: 2px solid #FFE4C4; padding-top: 16px;">
                        <button type="submit" class="btn-primary" onclick="this.innerHTML='Generating... ⏳'; this.style.opacity='0.7';">
                            ✨ Generate Questions
                        </button>
                    </div>
                </form>

                {{-- MANUAL FORM --}}
                <form id="manual-form" action="{{ route('quiz.store') }}" method="POST" style="display: none;">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                    <input type="hidden" name="quiz_mode" value="manual">

                    <div class="form-field">
                        <label class="form-label">Quiz Title</label>
                        <input type="text" name="quiz_title" class="form-input" placeholder="e.g. Midterm Examination" required>
                    </div>

                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                        <div class="form-field">
                            <label class="form-label">Subject Context</label>
                            <input type="text" name="subject" class="form-input" value="General" placeholder="e.g. Science" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Max Attempts per Student</label>
                            <input type="number" name="max_attempts" class="form-input" value="1" min="1" placeholder="e.g. 1" required>
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <div class="form-field" style="flex: 1;">
                            <label class="form-label">Publish Date</label>
                            <input type="datetime-local" name="published_at" class="form-input" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="form-field" style="flex: 1;">
                            <label class="form-label">Deadline</label>
                            <input type="datetime-local" name="deadline_at" class="form-input" value="{{ now()->addDays(7)->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>

                    <div id="manual-questions-container">
                        <!-- Dynamic Manual Question Blocks will render here -->
                    </div>

                    <button type="button" class="btn-ghost" onclick="addManualQuestion()" style="width: 100%; justify-content: center; margin-bottom: 20px; border-style: dashed;">
                        + Add Question Item
                    </button>

                    <div style="text-align: right; border-top: 2px solid #FFE4C4; padding-top: 16px;">
                        <button type="submit" class="btn-primary">
                            🚀 Publish Manual Quiz
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
    
    <!-- Info Panel -->
    <div class="col-4">
        <div class="card" style="background: linear-gradient(135deg, #FF6B6B, #FFB347); color: #fff; border: none;">
            <div style="font-size: 18px; font-weight: 900; margin-bottom: 12px;">Quiz Creation Modes 🤖</div>
            <div style="font-size: 13px; font-weight: 700; line-height: 1.6; opacity: 0.95;">
                <p style="margin-bottom: 12px;"><strong>🤖 AI Generated:</strong> Upload a PDF module, let Gemini craft multiple-choice questions, and fine-tune questions, time limits, XP, difficulty levels, and attempt limits before publishing.</p>
                <p><strong>✍️ Manual Creation:</strong> Directly construct custom questions, answer options, time limits, reward XP points, difficulty tiers, and attempt limits item by item.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let qIndex = 0;

    // Switch between AI and Manual creation mode tabs
    function switchMode(mode) {
        document.getElementById('ai-mode-btn').classList.toggle('active', mode === 'ai');
        document.getElementById('manual-mode-btn').classList.toggle('active', mode === 'manual');
        document.getElementById('ai-form').style.display = mode === 'ai' ? 'block' : 'none';
        document.getElementById('manual-form').style.display = mode === 'manual' ? 'block' : 'none';
        
        // Add default initial question if switching to manual for the first time
        if (mode === 'manual' && qIndex === 0) {
            addManualQuestion();
        }
    }

    // Dynamic Manual Question Builder
    function addManualQuestion() {
        const container = document.getElementById('manual-questions-container');
        const html = `
            <div class="q-block" id="mq-${qIndex}">
                ${qIndex > 0 ? `<button type="button" class="remove-q-btn" onclick="document.getElementById('mq-${qIndex}').remove()">Remove Item</button>` : ''}
                <div class="q-block-title">Question Item #${qIndex + 1}</div>
                
                <div class="form-field">
                    <label class="form-label">Question Text</label>
                    <textarea name="questions[${qIndex}][text]" class="form-textarea" rows="2" placeholder="e.g. What is the powerhouse of the cell?" required></textarea>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div class="form-field" style="margin:0;">
                        <label class="form-label">Option 1</label>
                        <input type="text" name="questions[${qIndex}][options][]" class="form-input" required>
                    </div>
                    <div class="form-field" style="margin:0;">
                        <label class="form-label">Option 2</label>
                        <input type="text" name="questions[${qIndex}][options][]" class="form-input" required>
                    </div>
                    <div class="form-field" style="margin:0;">
                        <label class="form-label">Option 3</label>
                        <input type="text" name="questions[${qIndex}][options][]" class="form-input" required>
                    </div>
                    <div class="form-field" style="margin:0;">
                        <label class="form-label">Option 4</label>
                        <input type="text" name="questions[${qIndex}][options][]" class="form-input" required>
                    </div>
                </div>

                <div class="form-field">
                    <label class="form-label" style="color: #10B981;">Correct Answer (Must exactly match one of the options above)</label>
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