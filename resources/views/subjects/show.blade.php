@extends('layouts.app')

@section('title', ($classroom->class_name ?? $classroom->name) . ' - ' . config('app.name', 'BrainBalance'))

@push('styles')
<style>
    .back-link { color: #FF6B6B; font-weight: 900; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 12px; font-size: 13px; }
    .back-link:hover { text-decoration: underline; }
    .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .item-list { display: flex; flex-direction: column; gap: 10px; }
    .item { padding: 12px 14px; background: #FFF8F0; border-radius: 14px; border: 2px solid #FFE4C4; display: flex; justify-content: space-between; align-items: center; }
    .item-title { font-weight: 900; font-size: 14px; color: #2D2D2D; }
    .item-sub { font-size: 11px; font-weight: 700; color: #aaa; margin-top: 2px; }
    .btn-pink { background: #FF6B6B; color: #fff; text-decoration: none; padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 900; }
    .btn-pink:hover { background: #e85555; }
    
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
<a href="{{ route('subjects.index') }}" class="back-link">&larr; Back to Subjects</a>

<div class="topbar">
    <div>
        <div class="tgreet">{{ $classroom->class_name ?? $classroom->name }}</div>
        <div class="tsub">{{ $classroom->subject ?? 'General Subject' }} &bull; Code: {{ $classroom->class_code ?? $classroom->code ?? 'N/A' }}</div>
    </div>
</div>

<div class="content-grid">
    <!-- FILES & LEARNING MODULES -->
    <div class="card">
        <div class="card-title">📂 Class Files & Learning Modules</div>
        <div class="item-list">
            @if(count($modules) === 0)
                <p style="font-size:13px; color:#aaa; font-weight:700;">No learning modules uploaded yet for this class.</p>
            @else
                @foreach($modules as $module)
                    <div class="item">
                        <div>
                            <div class="item-title">{{ $module->title }}</div>
                            <div class="item-sub">
                                {{ strtoupper($module->file_type ?? 'FILE') }} &bull; {{ $module->file_size ?? 0 }} KB
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;align-items:center">
                            <button onclick="openViewer('{{ route('modules.view', $module->id) }}', '{{ addslashes($module->title) }}', {{ $module->id }})" class="btn-teal">
                                View
                            </button>
                            <a href="{{ route('modules.download', $module->id) }}" class="btn-teal">
                                Download
                            </a>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- TEACHER QUIZZES -->
    <div class="card">
        <div class="card-title">📝 Class Quizzes</div>
        <div class="item-list">
            @if(count($quizzes) === 0)
                <p style="font-size:13px; color:#aaa; font-weight:700;">No quizzes created yet for this class.</p>
            @else
                @foreach($quizzes as $quiz)
                    <div class="item">
                        <div>
                            <div class="item-title">{{ $quiz->title }}</div>
                            <div class="item-sub">{{ ucfirst($quiz->subject ?? 'General') }}</div>
                        </div>
                        <a href="{{ route('quiz.show', $quiz->id) }}" class="btn-pink">Start Quiz</a>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

{{-- DOCUMENT VIEWER & AI ASSISTANT MODAL --}}
<div class="viewer-modal" id="viewerModal">
    <div class="viewer-body">
        <div class="viewer-header">
            <div style="font-weight:900;font-size:16px;color:#2D2D2D" id="viewerTitle">Document Viewer</div>
            <button onclick="closeViewer()" style="background:none;border:none;font-size:20px;font-weight:900;cursor:pointer;color:#aaa">✕</button>
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
        aiMsg.innerText = 'Thinking... 🤔';
        chatBox.appendChild(aiMsg);
        chatBox.scrollTop = chatBox.scrollHeight;

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';

            const response = await fetch(`/modules/${currentModuleId}/ai-chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ prompt: text })
            });

            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                const data = await response.json();
                const replyText = data.reply || data.error || (data.message ? `Error: ${data.message}` : "No response text returned.");

                aiMsg.innerHTML = typeof marked !== 'undefined' ? marked.parse(replyText) : replyText;
                if (window.MathJax) {
                    MathJax.typesetPromise([aiMsg]);
                }
            } else {
                const rawText = await response.text();
                console.error("Server HTML Error Response:", rawText);
                aiMsg.innerText = `⚠️ Server returned non-JSON response (${response.status}). Check developer console.`;
            }
        } catch (err) {
            console.error("JS Fetch Exception:", err);
            aiMsg.innerText = `⚠️ Connection error: ${err.message}`;
        }
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
@endpush