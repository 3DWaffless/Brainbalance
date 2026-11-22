@extends('layouts.app')

@section('title', 'Learning Modules - ' . config('app.name', 'BrainBalance'))

@section('content')
<style>
    .module-grid { display: grid; grid-template-columns: {{ Auth::user()->isTeacher() ? '1fr 1.5fr' : '1fr' }}; gap: 20px; }
    .module-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; background: var(--color-brand-bg, #F3F9FF); border-radius: 16px; border: 2px solid var(--color-brand-border, #C7E0FF); margin-bottom: 10px; }
    .module-title { font-size: 14px; font-weight: 900; color: var(--color-brand-text, #0F2D52); }
    .module-sub { font-size: 11px; font-weight: 700; color: #888; margin-top: 2px; }

    /* MINI PDF VIEWER MODAL + SIDE-BY-SIDE AI ASSISTANT */
    .viewer-modal { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center; padding: 16px; }
    .viewer-modal.open { display: flex; }
    .viewer-body { background: #fff; border-radius: 24px; width: 95%; max-width: 1320px; height: 86vh; display: flex; flex-direction: column; overflow: hidden; border: 4px solid var(--color-brand-border, #C7E0FF); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
    .viewer-header { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; background: var(--color-brand-bg, #F3F9FF); border-bottom: 3px solid var(--color-brand-border, #C7E0FF); flex-shrink: 0; }
    .viewer-content { flex: 1; display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(340px, 0.9fr); min-height: 0; background: #fff; }

    /* Left: Mini PDF Viewer */
    .mini-pdf-pane { display: flex; flex-direction: column; background: #525659; border-right: 3px solid var(--color-brand-border, #C7E0FF); min-height: 0; }
    .mini-pdf-bar { padding: 8px 14px; background: #f8fafc; border-bottom: 2px solid var(--color-brand-border, #C7E0FF); font-size: 11px; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; flex-shrink: 0; }
    .viewer-frame { flex: 1; border: none; width: 100%; height: 100%; min-height: 0; }

    /* Right: AI Assistant */
    .ai-drawer { display: flex; flex-direction: column; background: #fff; min-height: 0; }
    .ai-header { padding: 10px 16px; background: var(--color-brand-coral, #2488F4); color: #fff; font-weight: 900; font-size: 13px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
    .ai-messages { flex: 1; padding: 14px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; font-size: 13px; background: var(--color-brand-bg, #F4F9FF); min-height: 0; }
    .msg { padding: 10px 14px; border-radius: 14px; max-width: 88%; line-height: 1.45; font-weight: 700; word-break: break-word; }
    .msg.user { background: var(--color-brand-coral, #2488F4); color: #fff; align-self: flex-end; border-bottom-right-radius: 3px; }
    .msg.ai { background: #fff; color: var(--color-brand-text, #1E293B); border: 2px solid var(--color-brand-border, #DCEBFF); align-self: flex-start; border-bottom-left-radius: 3px; }
    .msg.ai p { margin-bottom: 6px; }
    .msg.ai p:last-child { margin-bottom: 0; }
    .msg.ai ul, .msg.ai ol { margin-left: 16px; margin-bottom: 6px; }
    .ai-input-wrap { padding: 12px; border-top: 2px solid var(--color-brand-border, #DCEBFF); display: flex; gap: 8px; background: #fff; flex-shrink: 0; }
    .ai-input { flex: 1; height: 40px; border-radius: 12px; border: 2px solid var(--color-brand-border, #DCEBFF); background: var(--color-brand-bg, #F4F9FF); padding: 0 12px; font-size: 13px; font-weight: 700; outline: none; }
    .ai-input:focus { border-color: var(--color-brand-coral, #2488F4); background: #fff; }
    .ai-send { height: 40px; padding: 0 16px; background: var(--color-brand-coral, #2488F4); color: #fff; border: none; border-radius: 12px; font-weight: 900; font-size: 13px; cursor: pointer; }

    @media (max-width: 900px) {
        .module-grid { grid-template-columns: 1fr; }
        .viewer-content { grid-template-columns: 1fr; grid-template-rows: 45% 55%; }
        .mini-pdf-pane { border-right: none; border-bottom: 3px solid var(--color-brand-border, #C7E0FF); }
    }
</style>

<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-black text-brand-text">Learning Modules 📚</h1>
    <p class="text-sm font-bold text-gray-500 mt-1">
        @if(Auth::user()->isTeacher())
            Upload and manage course resources for your students.
        @else
            View PDF lessons with your side-by-side AI Study Assistant!
        @endif
    </p>
</div>

<div class="module-grid">
    @if(Auth::user()->isTeacher())
    <div class="flex flex-col gap-5">
        {{-- UPLOAD MODULE CARD --}}
        <div class="bg-white border-4 border-brand-border rounded-3xl p-5 shadow-sm">
            <h2 class="text-lg font-black text-brand-text mb-3">Upload Module ⬆️</h2>
            <form action="{{ route('modules.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                @csrf
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1">Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-3.5 py-2 rounded-xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1">Topic (Optional)</label>
                    <select name="topic_id" class="w-full px-3.5 py-2 rounded-xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
                        <option value="">-- Select Topic --</option>
                        @foreach($topics as $topic)
                            <option value="{{ $topic->id }}">{{ $topic->name ?? $topic->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-3.5 py-2 rounded-xl border-2 border-brand-border bg-brand-bg font-bold text-sm">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1">Document File (PDF)</label>
                    <input type="file" name="module_file" accept=".pdf" required class="text-xs font-bold">
                </div>
                <button type="submit" class="btn-tactile-primary justify-center mt-1">
                    Upload Resource
                </button>
            </form>
        </div>

        {{-- GENERATE AI QUIZ CARD --}}
        <div class="bg-white border-4 border-brand-border rounded-3xl p-5 shadow-sm">
            <h2 class="text-lg font-black text-brand-text mb-3">Generate AI Quiz ⚡</h2>
            <form action="{{ route('quiz.generateFromPdf') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                @csrf
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1">Assign to Class *</label>
                    <select name="class_id" required class="w-full px-3.5 py-2 rounded-xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
                        <option value="">-- Select Class --</option>
                        @foreach(Auth::user()->ownedClasses as $class)
                            <option value="{{ $class->id }}">{{ $class->class_name ?? $class->name }} ({{ $class->subject }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1">Subject *</label>
                    <select name="subject" required class="w-full px-3.5 py-2 rounded-xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
                        <option value="math">Mathematics</option>
                        <option value="english">English</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1">Lesson PDF *</label>
                    <input type="file" name="pdf_file" accept=".pdf" required class="text-xs font-bold">
                </div>
                <button type="submit" class="btn-tactile-primary justify-center mt-1">
                    Generate Quiz
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- AVAILABLE MODULES (VIEW PDF & DOWNLOAD ONLY) --}}
    <div class="bg-white border-4 border-brand-border rounded-3xl p-5 shadow-sm">
        <h2 class="text-lg font-black text-brand-text mb-4">Available Resources 📂</h2>
        @if(count($modules) === 0)
            <p class="text-sm text-gray-400 font-bold">No learning modules uploaded yet.</p>
        @else
            @foreach($modules as $module)
                <div class="module-item">
                    <div class="min-w-0">
                        <div class="module-title truncate">{{ $module->title }}</div>
                        <div class="module-sub">
                            {{ strtoupper($module->file_type) }} &bull; {{ $module->file_size }} KB &bull; {{ $module->created_at->format('M d, Y') }}
                        </div>
                        @if($module->description)
                            <p class="text-xs text-gray-600 font-bold mt-1">{{ $module->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button"
                            onclick="openViewer('{{ route('modules.view', $module) }}#toolbar=1&navpanes=0', '{{ addslashes($module->title) }}', {{ $module->id }})"
                            class="btn-tactile-primary text-xs py-2 px-3.5 cursor-pointer">
                            View PDF
                        </button>
                        <a href="{{ route('modules.download', $module) }}"
                           class="px-3.5 py-2 rounded-2xl border-2 border-brand-border bg-white text-brand-text font-black text-xs hover:bg-brand-bg transition-all">
                            Download
                        </a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

{{-- MINI PDF VIEWER + AI ASSISTANT MODAL --}}
<div class="viewer-modal" id="viewerModal">
    <div class="viewer-body">
        <div class="viewer-header">
            <div class="font-black text-base text-brand-text truncate" id="viewerTitle">Document Viewer</div>
            <button type="button" onclick="closeViewer()" class="w-8 h-8 rounded-xl border-2 border-brand-border bg-white font-black text-gray-500 hover:text-brand-coral cursor-pointer">✕</button>
        </div>
        <div class="viewer-content">
            <!-- LEFT: Mini PDF Viewer -->
            <div class="mini-pdf-pane">
                <div class="mini-pdf-bar">📄 Mini PDF Viewer</div>
                <iframe id="viewerFrame" class="viewer-frame" title="Mini PDF Viewer"></iframe>
            </div>

            <!-- RIGHT: AI Assistant -->
            <div class="ai-drawer">
                <div class="ai-header">
                    <span>🤖 AI Study Assistant</span>
                    <span class="text-[11px] opacity-85">Ask about this PDF</span>
                </div>
                <div class="ai-messages" id="aiMessages">
                    <div class="msg ai">👋 Hi! Read the PDF on the left and ask me anything about it!</div>
                </div>
                <div class="ai-input-wrap">
                    <input type="text" id="aiInput" class="ai-input" placeholder="Ask a question..." onkeydown="if(event.key === 'Enter') sendAiMessage()">
                    <button type="button" class="ai-send" onclick="sendAiMessage()">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
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
            const response = await fetch(`/modules/${currentModuleId}/ai-chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: text, prompt: text })
            });
            const data = await response.json();
            const replyText = data.reply || data.error || 'Sorry, no response was returned.';
            aiMsg.innerHTML = typeof marked !== 'undefined' ? marked.parse(replyText) : replyText;
        } catch (err) {
            aiMsg.innerText = 'Sorry, failed to get a response from the AI assistant.';
        }
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>
@endsection