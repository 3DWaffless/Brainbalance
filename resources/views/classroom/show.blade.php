@extends('layouts.app')

@section('title', $classroom->name . ' - Workspace')

@section('content')
<div class="topbar">
    <div>
        <div style="font-size: 11px; font-weight: 800; color: #aaa; text-transform: uppercase;">Class Workspace</div>
        <div class="tgreet" style="color: #FF6B6B;">{{ $classroom->name }}</div>
        <div class="tsub">{{ $classroom->description ?? 'Manage your class materials and students here.' }}</div>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <div style="background: #FFF8F0; border: 2px dashed #FFE4C4; padding: 8px 16px; border-radius: 12px; font-size: 14px; font-weight: 900; color: #2D2D2D;">
            Class Code: <span style="color: #4ECDC4;">{{ $classroom->code }}</span>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-ghost">&larr; Back to Dashboard</a>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Quizzes Section -->
    <div class="col-8 card">
        <div class="card-title" style="margin-bottom: 15px;"><span>📝 Class Quizzes</span></div>
        @if($quizzes->count() > 0)
            <div class="item-list">
                @foreach($quizzes as $quiz)
                    @php
                        $subjectName = ucfirst($quiz->subject ?? 'General');
                        $subjectIcon = match(strtolower($quiz->subject ?? '')) {
                            'math', 'mathematics' => '📐',
                            'science' => '🧪',
                            'english', 'reading' => '📖',
                            default => '💡',
                        };

                        $userAttempts = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', Auth::id())->count();
                        $latestAttempt = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', Auth::id())->latest()->first();
                        $isClosed = $quiz->max_attempts && $userAttempts >= $quiz->max_attempts;
                    @endphp
                    <div class="item-card" style="padding: 16px; border: 2px solid #FFE4C4; border-radius: 12px; background: #FFF8F0; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; opacity: {{ (!Auth::user()->isTeacher() && $isClosed) ? '0.85' : '1' }};">
                        <div>
                            <div style="font-size: 15px; font-weight: 900; color: #2D2D2D; display: flex; align-items: center; gap: 8px;">
                                {{ $quiz->title }}
                                <span style="background: #FFE4C4; color: #FF6B6B; font-weight: 900; font-size: 11px; padding: 3px 10px; border-radius: 50px; border: 1.5px solid #FFD1A4;">
                                    {{ $subjectIcon }} {{ $subjectName }}
                                </span>
                            </div>
                            <div style="font-size: 12px; font-weight: 700; color: #aaa; margin-top: 4px;">
                                Due: {{ \Carbon\Carbon::parse($quiz->deadline_at)->format('M d, Y g:i A') }} &bull; 
                                @if(Auth::user()->isTeacher())
                                    Max Attempts: {{ $quiz->max_attempts ?? '∞' }} &bull; {{ $quiz->type === 'ai_generated' ? 'AI Generated' : 'Manual' }}
                                @else
                                    Attempts: {{ $userAttempts }}/{{ $quiz->max_attempts ?? '∞' }}
                                @endif
                            </div>
                        </div>
                        @if(Auth::user()->isTeacher())
                            <a href="{{ route('quiz.edit', $quiz->id) }}" class="btn-ghost" style="padding: 6px 12px;">✏️ Edit</a>
                        @else
                            <div style="display: flex; gap: 8px;">
                                @if($latestAttempt)
                                    <a href="{{ route('quiz.review', $latestAttempt->id) }}" class="btn-ghost" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">🔍 Review</a>
                                @endif

                                @if($isClosed)
                                    <button class="btn-ghost" disabled style="padding: 6px 12px; font-size: 12px; color: #888; cursor: not-allowed; border-color: #ddd !important; box-shadow: none !important; background: #f0f0f0 !important;">
                                        🔒 Closed
                                    </button>
                                @else
                                    <a href="{{ route('quiz.show', $quiz->id) }}" class="btn-primary" style="padding: 6px 12px; text-decoration: none;">Play &rarr;</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="padding: 2rem 1rem;">
                <p style="font-size:13px; color:#aaa; font-weight:700;">No quizzes published for this class yet.</p>
            </div>
        @endif
    </div>

    <!-- Right Sidebar Column: Leaderboard & Enrolled Students -->
    <div class="col-4" style="display: flex; flex-direction: column; gap: 20px;">
        
        {{-- 🏆 CLASSROOM LEADERBOARD COMPONENT --}}
        <div class="card" style="margin-bottom: 0; background: linear-gradient(180deg, #FFFFFF 0%, #FFF8F0 100%);">
            <div class="card-title" style="margin-bottom: 15px;">
                <span>🏆 Top Students</span>
                <span style="font-size: 11px; font-weight: 800; color: #FF6B6B; background: #FFE4C4; padding: 2px 8px; border-radius: 50px;">Leaderboard</span>
            </div>

            @php
                $leaderboard = $classroom->students->sortByDesc('total_xp')->take(5);
            @endphp

            @if($leaderboard->count() > 0)
                <div class="item-list">
                    @foreach($leaderboard as $rank => $student)
                        @php
                            $medal = match($rank) {
                                0 => '🥇',
                                1 => '🥈',
                                2 => '🥉',
                                default => ($rank + 1) . 'th',
                            };
                            $badgeBg = match($rank) {
                                0 => '#FFD166',
                                1 => '#E0E0E0',
                                2 => '#FFB347',
                                default => '#FFF8F0',
                            };
                            $isCurrentUser = Auth::id() === $student->id;
                        @endphp
                        <div style="padding: 10px 12px; border: 2px solid {{ $isCurrentUser ? '#FF6B6B' : '#FFE4C4' }}; border-radius: 12px; background: {{ $isCurrentUser ? '#FFF0E0' : '#fff' }}; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 28px; height: 28px; background: {{ $badgeBg }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900; color: #2D2D2D; flex-shrink: 0; border: 1px solid #FFE4C4;">
                                    {{ $medal }}
                                </div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 900; color: #2D2D2D;">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                        @if($isCurrentUser) <span style="font-size: 10px; color: #FF6B6B;">(You)</span> @endif
                                    </div>
                                    <div style="font-size: 10px; font-weight: 800; color: #aaa;">Level {{ $student->level ?? 1 }}</div>
                                </div>
                            </div>
                            <div style="font-size: 12px; font-weight: 900; color: #4ECDC4;">
                                {{ $student->total_xp ?? 0 }} XP
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="font-size: 12px; color: #aaa; font-weight: 700; text-align: center;">No student rankings available yet.</p>
            @endif
        </div>

        {{-- Students List Card --}}
        <div class="card" style="margin-bottom: 0;">
            <div class="card-title" style="margin-bottom: 15px;">
                <span>👥 Enrolled Students</span>
                <span style="background: #4ECDC4; color: #fff; padding: 2px 8px; border-radius: 8px; font-size: 12px;">{{ $classroom->students->count() }}</span>
            </div>
            
            @if($classroom->students->count() > 0)
                <div class="item-list">
                    @foreach($classroom->students as $student)
                        <div style="padding: 10px 12px; border: 1px solid #FFE4C4; border-radius: 10px; font-size: 13px; font-weight: 800; color: #2D2D2D; display: flex; align-items: center; gap: 10px;">
                            <div style="width: 30px; height: 30px; background: #FF6B6B; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900;">
                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                            </div>
                            <div>
                                <div>{{ $student->first_name }} {{ $student->last_name }}</div>
                                <div style="font-size: 10px; color: #aaa;">Level {{ $student->level ?? 1 }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state" style="padding: 1.5rem 1rem;">
                    <p style="font-size: 12px; color: #aaa; font-weight: 700;">No students have joined yet.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modules Section -->
    <div class="col-12 card">
        <div class="card-title" style="margin-bottom: 15px;">
            <span>📚 Class Modules & Resources</span>
        </div>

        @if(Auth::user()->isTeacher())
            <!-- Upload Form -->
            <form action="{{ route('modules.store') }}" method="POST" enctype="multipart/form-data" style="background: #FFF8F0; padding: 16px; border-radius: 12px; border: 2px dashed #FFE4C4; margin-bottom: 20px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                @csrf
                <input type="hidden" name="class_id" value="{{ $classroom->id }}">
                
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Module Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. Chapter 1: Fractions" required>
                </div>
                
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">File (PDF, Word, PPT, Image)</label>
                    <input type="file" name="file" class="form-input" style="padding: 9px;" required>
                </div>
                
                <button type="submit" class="btn-teal">📤 Upload Module</button>
            </form>
        @endif

        <!-- List of Modules -->
        @if($modules->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
                @foreach($modules as $module)
                    @php
                        $isImage = in_array(strtolower($module->file_type ?? ''), ['jpg', 'jpeg', 'png', 'webp', 'svg']);
                    @endphp
                    <div class="item-card" style="padding: 14px; border: 2px solid #FFE4C4; border-radius: 16px; background: #fff; display: flex; flex-direction: column; justify-content: space-between;">
                        
                        @if($isImage)
                            <div onclick="openLightbox('{{ asset('storage/' . $module->file_path) }}', '{{ $module->title }}')" 
                                 style="width: 100%; height: 130px; border-radius: 12px; overflow: hidden; background: #FFF8F0; margin-bottom: 10px; border: 2px solid #FFE4C4; cursor: pointer; position: relative;">
                                <img src="{{ asset('storage/' . $module->file_path) }}" alt="{{ $module->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                <div style="position: absolute; bottom: 6px; right: 6px; background: rgba(0,0,0,0.6); color: #fff; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 800;">🔍 Zoom</div>
                            </div>
                        @else
                            <div style="width: 100%; height: 60px; border-radius: 12px; background: #FFF0E0; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 10px; border: 2px solid #FFE4C4;">
                                📄
                            </div>
                        @endif

                        <div>
                            <div style="font-size: 15px; font-weight: 900; color: #2D2D2D;">{{ $module->title }}</div>
                            <div style="font-size: 11px; font-weight: 700; color: #aaa; margin-top: 4px;">{{ $module->file_name }} &bull; Uploaded {{ $module->created_at->format('M d') }}</div>
                        </div>
                        
                        <div style="display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap;">
                            <a href="{{ route('modules.download', $module->id) }}" class="btn-primary" style="padding: 6px 12px; font-size: 12px; flex: 1; justify-content: center; text-decoration: none;">⬇️ Download</a>
                            
                            {{-- 🤖 Module AI Study Drawer Trigger Button --}}
                            <button type="button" class="btn-teal" onclick="openAiChatModal({{ $module->id }}, '{{ addslashes($module->title) }}')" style="padding: 6px 12px; font-size: 12px; flex: 1; justify-content: center;">
                                🤖 Ask AI
                            </button>

                            @if(Auth::user()->isTeacher())
                                <form action="{{ route('modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Delete this module?');" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger">🗑️</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="padding: 3rem 1rem;">
                <div style="font-size: 32px; margin-bottom: 10px;">📄</div>
                <p style="font-size:14px; color:#2D2D2D; font-weight:900;">No modules uploaded.</p>
                <p style="font-size:13px; color:#aaa; font-weight:700;">Upload PDFs and class materials to show them here.</p>
            </div>
        @endif
    </div>
</div>

{{-- 🤖 MODULE AI STUDY ASSISTANT MODAL --}}
<div id="ai-chat-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; padding: 20px; border-radius: 20px; border: 4px solid #4ECDC4; max-width: 550px; width: 100%; display: flex; flex-direction: column; max-height: 80vh;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 2px dashed #FFE4C4; padding-bottom: 10px;">
            <div>
                <h3 id="ai-modal-title" style="font-size: 16px; font-weight: 900; color: #2D2D2D; margin: 0;">🤖 AI Study Assistant</h3>
                <div style="font-size: 11px; font-weight: 700; color: #aaa;">Ask anything about this study module!</div>
            </div>
            <button onclick="closeAiChatModal()" style="background: #FF6B6B; color: #fff; border: none; border-radius: 50%; width: 28px; height: 28px; font-weight: 900; cursor: pointer;">✕</button>
        </div>

        {{-- Chat History Window --}}
        <div id="ai-chat-box" style="flex: 1; overflow-y: auto; background: #FFF8F0; border: 2px solid #FFE4C4; border-radius: 14px; padding: 12px; margin-bottom: 12px; min-height: 250px; display: flex; flex-direction: column; gap: 10px;">
            <div style="background: #E0F7FA; border: 1.5px solid #4ECDC4; padding: 10px; border-radius: 12px; font-size: 13px; font-weight: 800; color: #2D2D2D; max-width: 85%;">
                👋 Hi! I'm BrainBalance AI. What questions do you have about this module?
            </div>
        </div>

        {{-- Input Form --}}
        <form onsubmit="sendAiMessage(event)" style="display: flex; gap: 8px;">
            <input type="hidden" id="current-module-id" value="">
            <input type="text" id="ai-user-input" class="form-input" placeholder="e.g. Explain this in simple words..." style="flex: 1; margin: 0;" required>
            <button type="submit" id="ai-send-btn" class="btn-teal" style="padding: 8px 16px;">Send 🚀</button>
        </form>
    </div>
</div>

{{-- LIGHTBOX PREVIEW MODAL --}}
<div id="image-lightbox" onclick="closeLightbox()" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
    <div onclick="event.stopPropagation()" style="background: #fff; padding: 18px; border-radius: 20px; border: 4px solid #FFE4C4; max-width: 900px; width: 100%; position: relative; text-align: center;">
        <button onclick="closeLightbox()" style="position: absolute; top: 12px; right: 14px; background: #FF6B6B; color: #fff; border: none; border-radius: 50%; width: 32px; height: 32px; font-weight: 900; cursor: pointer;">✕</button>
        <h3 id="lightbox-title" style="font-size: 18px; font-weight: 900; margin-bottom: 12px; color: #2D2D2D;"></h3>
        <img id="lightbox-img" src="" alt="Resource Preview" style="max-width: 100%; max-height: 70vh; border-radius: 12px; object-fit: contain;">
    </div>
</div>

<script>
    function openLightbox(imageSrc, title) {
        document.getElementById('lightbox-img').src = imageSrc;
        document.getElementById('lightbox-title').innerText = title;
        document.getElementById('image-lightbox').style.display = 'flex';
    }

    function closeLightbox() {
        document.getElementById('image-lightbox').style.display = 'none';
    }

    // AI Chat Modal Logic
    function openAiChatModal(moduleId, title) {
        document.getElementById('current-module-id').value = moduleId;
        document.getElementById('ai-modal-title').innerText = "🤖 Study Assistant: " + title;
        document.getElementById('ai-chat-modal').style.display = 'flex';
    }

    function closeAiChatModal() {
        document.getElementById('ai-chat-modal').style.display = 'none';
    }

    function sendAiMessage(e) {
        e.preventDefault();
        const input = document.getElementById('ai-user-input');
        const moduleId = document.getElementById('current-module-id').value;
        const chatBox = document.getElementById('ai-chat-box');
        const message = input.value.trim();

        if (!message) return;

        // Render Student Message
        chatBox.insertAdjacentHTML('beforeend', `
            <div style="background: #FF6B6B; color: #fff; padding: 10px; border-radius: 12px; font-size: 13px; font-weight: 800; align-self: flex-end; max-width: 85%;">
                ${message}
            </div>
        `);

        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        // Fetch AI Response via AJAX
        fetch(`/modules/${moduleId}/ai-chat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            const reply = data.reply || data.error || "I'm ready to help you study!";
            chatBox.insertAdjacentHTML('beforeend', `
                <div style="background: #E0F7FA; border: 1.5px solid #4ECDC4; padding: 10px; border-radius: 12px; font-size: 13px; font-weight: 800; color: #2D2D2D; max-width: 85%;">
                    ${reply}
                </div>
            `);
            chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(() => {
            chatBox.insertAdjacentHTML('beforeend', `
                <div style="background: #FFF3CD; border: 1.5px solid #FFECB5; padding: 10px; border-radius: 12px; font-size: 12px; font-weight: 800; color: #856404; max-width: 85%;">
                    ⚠️ Unable to reach AI study assistant. Please try again in a moment.
                </div>
            `);
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    }
</script>
@endsection