{{-- FILE: resources/views/modules/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Learning Modules - ' . config('app.name', 'BrainBalance'))

@push('styles')
<style>
    .module-grid { display: grid; grid-template-columns: {{ Auth::user()->isTeacher() ? '1fr 1.5fr' : '1fr' }}; gap: 20px; }
    .module-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #FFF8F0; border-radius: 14px; border: 2px solid #FFE4C4; margin-bottom: 10px; }
    .module-title { font-size: 14px; font-weight: 900; color: #2D2D2D; }
    .module-sub { font-size: 11px; font-weight: 700; color: #aaa; margin-top: 2px; }
    
    /* VIEWER MODAL & AI PANEL */
    .viewer-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 100; align-items: center; justify-content: center; }
    .viewer-modal.open { display: flex; }
    .viewer-body { background: #fff; border-radius: 20px; width: 95%; height: 90vh; display: flex; flex-direction: column; overflow: hidden; border: 2px solid #FFE4C4; position: relative; }
    .viewer-header { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; background: #FFF8F0; border-bottom: 2px solid #FFE4C4; }
    .viewer-content { flex: 1; display: flex; background: #525659; position: relative; }
    .viewer-frame { flex: 1; border: none; width: 100%; height: 100%; }
    .ai-drawer { width: 340px; background: #FFF; border-left: 2px solid #FFE4C4; display: flex; flex-direction: column; }
    .ai-header { padding: 12px 16px; background: #FF6B6B; color: #fff; font-weight: 900; font-size: 14px; display: flex; align-items: center; gap: 8px; }
    .ai-messages { flex: 1; padding: 12px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; font-size: 12px; }
    .msg { padding: 8px 12px; border-radius: 12px; max-width: 85%; line-height: 1.4; font-weight: 700; word-break: break-word; }
    .msg.user { background: #FFE4C4; color: #2D2D2D; align-self: flex-end; border-bottom-right-radius: 2px; }
    .msg.ai { background: #FFF8F0; color: #2D2D2D; border: 2px solid #FFE4C4; align-self: flex-start; border-bottom-left-radius: 2px; }
    .msg.ai p { margin-bottom: 6px; }
    .msg.ai p:last-child { margin-bottom: 0; }
    .msg.ai ul, .msg.ai ol { margin-left: 16px; margin-bottom: 6px; }
    .ai-input-wrap { padding: 10px; border-top: 2px solid #FFE4C4; display: flex; gap: 6px; background: #fff; }
    .ai-input { flex: 1; height: 38px; border-radius: 10px; border: 2px solid #FFE4C4; padding: 0 10px; font-size: 12px; font-family: 'Nunito', sans-serif; font-weight: 700; outline: none; }
    .ai-send { height: 38px; padding: 0 12px; background: #FF6B6B; color: #fff; border: none; border-radius: 10px; font-weight: 900; cursor: pointer; }
</style>
@endpush

@section('content')
<div class="topbar">
    <div>
        <div class="tgreet">Learning Modules 📚</div>
        <div class="tsub">
            @if(Auth::user()->isTeacher())
                Upload and manage course resources for your students.
            @else
                Read and view educational materials with your AI Assistant!
            @endif
        </div>
    </div>
</div>

<div class="module-grid">
    @if(Auth::user()->isTeacher())
    <div>
        {{-- UPLOAD MODULE CARD --}}
        <div class="card">
            <div class="card-title">Upload Module 📄</div>
            <form action="{{ route('modules.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-field">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label">Topic (Optional)</label>
                    <select name="topic_id" class="form-input">
                        <option value="">-- Select Topic --</option>
                        @foreach($topics as$topic)
                            <option value="{{ $topic->id }}">{{ $topic->name ?? $topic->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-textarea">{{ old('description') }}</textarea>
                </div>
                <div class="form-field">
                    <label class="form-label">Document File (PDF, DOCX, PPTX, ZIP)</label>
                    <input type="file" name="module_file" required style="font-size:12px">
                </div>
                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:6px">
                    Upload Resource
                </button>
            </form>
        </div>

        {{-- GENERATE AI QUIZ CARD --}}
        <div class="card">
            <div class="card-title">Generate AI Quiz ⚡</div>
            <form action="{{ route('quiz.generateFromPdf') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-field">
                    <label class="form-label">Assign to Class *</label>
                    <select name="class_id" required class="form-input">
                        <option value="">-- Select Class --</option>
                        @foreach(Auth::user()->ownedClasses as $class)
                            <option value="{{ $class->id }}">{{ $class->class_name }} ({{$class->subject }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label">Subject *</label>
                    <select name="subject" required class="form-input">
                        <option value="math">Mathematics</option>
                        <option value="english">English</option>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label">Lesson PDF *</label>
                    <input type="file" name="pdf_file" accept=".pdf" required style="font-size:12px">
                </div>
                <button type="submit" class="btn-teal" style="width:100%;justify-content:center;margin-top:6px;padding:10px 20px">
                    Generate Quiz with Gemini
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- AVAILABLE MODULES --}}
    <div class="card">
        <div class="card-title">Available Resources 📖</div>
        @if(count($modules) === 0)
            <p style="font-size:13px;color:#aaa;font-weight:700">No learning modules uploaded yet.</p>
        @else
            @foreach($modules as$module)
                <div class="module-item">
                    <div>
                        <div class="module-title">{{ $module->title }}</div>
                        <div class="module-sub">
                            {{ strtoupper($module->file_type) }} &bull; {{ $module->file_size }} KB &bull; {{$module->created_at->format('M d, Y') }}
                        </div>
                        @if($module->description)
                            <p style="font-size:12px;color:#666;margin-top:4px">{{ $module->description }}</p>
                        @endif
                    </div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <button onclick="openViewer('{{ route('modules.view', $module) }}', '{{ addslashes($module->title) }}', {{$module->id }})" class="btn-teal">
                            View
                        </button>
                        <a href="{{ route('modules.download', $module) }}" class="btn-ghost">
                            Download
                        </a>
                        @if(Auth::user()->isTeacher())
                            <form action="{{ route('modules.destroy', $module) }}" method="POST" onsubmit="return confirm('Delete this module?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

{{-- DOCUMENT VIEWER MODAL --}}
<div class="viewer-modal" id="viewerModal">
    <div class="viewer-body">
        <div class="viewer-header">
            <div style="font-weight:900;font-size:16px;color:#2D2D2D" id="viewerTitle">Document Viewer</div>
            <button onclick="closeViewer()" style="background:none;border:none;font-size:20px;font-weight:900;cursor:pointer;color:#aaa">×</button>
        </div>
        <div class="viewer-content">
            <iframe id="viewerFrame" class="viewer-frame"></iframe>
            @if(Auth::user()->isStudent())
            <div class="ai-drawer">
                <div class="ai-header">
                    <span>✨ Gemini Study Assistant</span>
                </div>
                <div class="ai-messages" id="aiMessages">
                    <div class="msg ai">Hi! I'm your Gemini AI Study Assistant. Ask me anything about this document! 🤖</div>
                </div>
                <div class="ai-input-wrap">
                    <input type="text" id="aiInput" class="ai-input" placeholder="Ask a question..." onkeydown="if(event.key === 'Enter') sendAiMessage()">
                    <button class="ai-send" onclick="sendAiMessage()">Send</button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    window.MathJax = {
        tex: { inlineMath: [['$', '$'], ['\\(', '\\)']], displayMath: [['$$', '$$'], ['[', ']']] },
        svg: { fontCache: 'global' }
    };
</script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
<script>
    let currentModuleId = null;
    function openViewer(url, title, moduleId) {
        document.getElementById('viewerTitle').innerText = title;
        document.getElementById('viewerFrame').src = url;
        currentModuleId = moduleId;
        document.getElementById('viewerModal').classList.add('open');
    }
    function closeViewer() {
        document.getElementById('viewerModal').classList.remove('open');
        document.getElementById('viewerFrame').src = '';
    }
    async function sendAiMessage() {
        const input = document.getElementById('aiInput');
        const text = input.value.trim();
        if (!text || !currentModuleId) return;

        const chatBox = document.getElementById('aiMessages');
        const userMsg = document.createElement('div');
        userMsg.className = 'msg user';
        userMsg.innerText = text;
        chatBox.appendChild(userMsg);
        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        const aiMsg = document.createElement('div');
        aiMsg.className = 'msg ai';
        aiMsg.innerText = 'Thinking... 💭';
        chatBox.appendChild(aiMsg);
        chatBox.scrollTop = chatBox.scrollHeight;

        try {
            const response = await fetch(`/modules/${currentModuleId}/ai-chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ prompt: text })
            });
            const data = await response.json();
            aiMsg.innerHTML = typeof marked !== 'undefined' ? marked.parse(data.reply) : data.reply;
            if (window.MathJax) {
                MathJax.typesetPromise([aiMsg]);
            }
        } catch (err) {
            aiMsg.innerText = 'Sorry, failed to get a response from Gemini AI.';
        }
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
@endpush