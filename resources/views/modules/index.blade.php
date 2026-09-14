<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Learning Modules — {{ config('app.name', 'BrainBalance') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Markdown Parser -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    
    <!-- MathJax for LaTeX Equations -->
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']]
            },
            svg: { fontCache: 'global' }
        };
    </script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Nunito', sans-serif; background: #FFF8F0; min-height: 100vh; display: flex; }
        .sidebar { width: 210px; background: #fff; border-right: 2px solid #FFE4C4; display: flex; flex-direction: column; padding: 1rem 0; flex-shrink: 0; position: fixed; top: 0; left: 0; height: 100vh; }
        .sb-logo { display: flex; align-items: center; gap: 9px; padding: 0 1rem 1rem; border-bottom: 2px solid #FFE4C4; margin-bottom: 0.75rem; }
        .sb-logo-icon { width: 38px; height: 38px; background: #FF6B6B; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .sb-logo-name { font-weight: 900; font-size: 14px; color: #2D2D2D; line-height: 1.1; }
        .sb-logo-sub { font-size: 9px; font-weight: 700; color: #bbb; letter-spacing: 0.6px; text-transform: uppercase; }
        .sb-nav { display: flex; flex-direction: column; gap: 3px; padding: 0 0.65rem; flex: 1; }
        .sb-item { display: flex; align-items: center; gap: 9px; padding: 9px 11px; border-radius: 12px; font-size: 13px; font-weight: 800; color: #aaa; cursor: pointer; transition: all 0.15s; text-decoration: none; }
        .sb-item:hover { background: #FFF0E0; color: #FF6B6B; }
        .sb-item.active { background: #FFE4C4; color: #FF6B6B; }
        .sb-item svg { width: 18px; height: 18px; flex-shrink: 0; }
        .sb-section { font-size: 10px; font-weight: 900; color: #ccc; letter-spacing: 0.8px; text-transform: uppercase; padding: 8px 11px 4px; }
        .sb-bottom { padding: 0.75rem; border-top: 2px solid #FFE4C4; margin-top: 0.5rem; }
        .sb-user { display: flex; align-items: center; gap: 9px; padding: 9px 11px; border-radius: 12px; background: #FFF0E0; cursor: pointer; }
        .sb-avatar { width: 32px; height: 32px; border-radius: 50%; background: #FF6B6B; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 900; color: #fff; flex-shrink: 0; }
        .sb-uname { font-size: 13px; font-weight: 900; color: #2D2D2D; line-height: 1.2; }
        .sb-urole { font-size: 10px; font-weight: 700; color: #aaa; }
        .main { margin-left: 210px; flex: 1; padding: 1.75rem; }
        .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; }
        .tgreet { font-weight: 900; font-size: 22px; color: #2D2D2D; }
        .tsub { font-size: 13px; color: #aaa; font-weight: 700; margin-top: 3px; }
        .alert { padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #D4EDDA; color: #155724; border: 2px solid #C3E6CB; }
        .alert-error { background: #F8D7DA; color: #721C24; border: 2px solid #F5C6CB; }
        .card { background: #fff; border-radius: 18px; padding: 20px; border: 2px solid #FFE4C4; margin-bottom: 1.25rem; }
        .card-title { font-size: 15px; font-weight: 900; color: #2D2D2D; margin-bottom: 12px; }
        .form-field { margin-bottom: 12px; }
        .form-label { display: block; font-size: 12px; font-weight: 800; color: #555; margin-bottom: 5px; }
        .form-input, .form-select, .form-textarea { width: 100%; padding: 10px 14px; border-radius: 12px; border: 2px solid #FFE4C4; background: #FFF8F0; color: #2D2D2D; font-size: 14px; font-family: 'Nunito', sans-serif; font-weight: 700; outline: none; }
        .btn-primary { background: #FF6B6B; color: #fff; border: none; padding: 10px 20px; border-radius: 12px; font-size: 13px; font-weight: 900; font-family: 'Nunito', sans-serif; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary:hover { background: #e85555; }
        .btn-teal { background: #4ECDC4; color: #fff; border: none; padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 900; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; }
        .btn-teal:hover { background: #3ab8b0; }
        .btn-ghost { background: #fff; color: #FF6B6B; border: 2px solid #FFE4C4; padding: 7px 12px; border-radius: 10px; font-size: 12px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
        .btn-ghost:hover { background: #FFF0E0; }
        .btn-danger { background: transparent; color: #e74c3c; border: 2px solid #F8D7DA; padding: 6px 12px; border-radius: 10px; font-size: 11px; font-weight: 900; cursor: pointer; }
        .btn-danger:hover { background: #F8D7DA; }
        .module-grid { display: grid; grid-template-columns: {{ Auth::user()->isTeacher() ? '1fr 2fr' : '1fr' }}; gap: 20px; }
        .module-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #FFF8F0; border-radius: 14px; border: 2px solid #FFE4C4; margin-bottom: 10px; }
        .module-title { font-size: 14px; font-weight: 900; color: #2D2D2D; }
        .module-sub { font-size: 11px; font-weight: 700; color: #aaa; margin-top: 2px; }

        /* VIEW MODAL & AI CHAT OVERLAY */
        .viewer-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 100; align-items: center; justify-content: center; }
        .viewer-modal.open { display: flex; }
        .viewer-body { background: #fff; border-radius: 20px; width: 95%; height: 90vh; display: flex; flex-direction: column; overflow: hidden; border: 2px solid #FFE4C4; position: relative; }
        .viewer-header { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; background: #FFF8F0; border-bottom: 2px solid #FFE4C4; }
        .viewer-content { flex: 1; display: flex; background: #525659; position: relative; }
        .viewer-frame { flex: 1; border: none; width: 100%; height: 100%; }

        /* AI CHATBOT PANEL */
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
</head>
<body>

{{-- SIDEBAR --}}
<div class="sidebar">
    <div class="sb-logo">
        <div class="sb-logo-icon">
            <svg width="20" height="20" viewBox="0 0 22 22" fill="none">
                <circle cx="11" cy="8" r="4" fill="white" opacity="0.95"/>
                <circle cx="7" cy="14" r="3" fill="white" opacity="0.8"/>
                <circle cx="15" cy="14" r="3" fill="white" opacity="0.8"/>
                <line x1="11" y1="8" x2="7" y2="14" stroke="white" stroke-width="1.5" opacity="0.6"/>
                <line x1="11" y1="8" x2="15" y2="14" stroke="white" stroke-width="1.5" opacity="0.6"/>
            </svg>
        </div>
        <div>
            <div class="sb-logo-name">BrainBalance</div>
            <div class="sb-logo-sub">by Ekklessia</div>
        </div>
    </div>

    <nav class="sb-nav">
        <div class="sb-section">Main</div>
        <a href="{{ route('dashboard') }}" class="sb-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <div class="sb-section">Learn</div>
        <a href="{{ route('modules.index') }}" class="sb-item active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            Learning Modules
        </a>
        <a href="{{ Auth::user()->isTeacher() ? route('quiz.results') : route('quiz.index') }}" class="sb-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Quizzes
        </a>
        <div class="sb-section">Account</div>
        <a href="{{ route('profile.edit') }}" class="sb-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Settings
        </a>
    </nav>

    <div class="sb-bottom">
        <div class="sb-user">
            <div class="sb-avatar">{{ Auth::user()->initials }}</div>
            <div>
                <div class="sb-uname">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                <div class="sb-urole">{{ ucfirst(Auth::user()->role) }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:8px">
            @csrf
            <button type="submit" style="width:100%;padding:8px;border-radius:10px;border:2px solid #FFE4C4;background:#fff;color:#FF6B6B;font-size:12px;font-weight:900;font-family:'Nunito',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Log out
            </button>
        </form>
    </div>
</div>

{{-- MAIN --}}
<div class="main">
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

    @if (session('status') === 'module-uploaded')
        <div class="alert alert-success">✅ Module uploaded successfully!</div>
    @elseif (session('status') === 'module-deleted')
        <div class="alert alert-error">🗑 Module deleted successfully.</div>
    @endif

    <div class="module-grid">
        {{-- TEACHER-ONLY UPLOAD FORM --}}
        @if(Auth::user()->isTeacher())
        <div class="card">
            <div class="card-title">Upload Module 📤</div>
            <form action="{{ route('modules.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-field">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="form-input">
                </div>

                <div class="form-field">
                    <label class="form-label">Topic (Optional)</label>
                    <select name="topic_id" class="form-select">
                        <option value="">-- Select Topic --</option>
                        @foreach($topics as $topic)
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
        @endif

        {{-- AVAILABLE MODULES (VISIBLE TO ALL) --}}
        <div class="card">
            <div class="card-title">Available Resources 📖</div>
            @if($modules->isEmpty())
                <p style="font-size:13px;color:#aaa;font-weight:700">No learning modules uploaded yet.</p>
            @else
                @foreach($modules as $module)
                    <div class="module-item">
                        <div>
                            <div class="module-title">{{ $module->title }}</div>
                            <div class="module-sub">
                                {{ strtoupper($module->file_type) }} &bull; {{ $module->file_size }} KB &bull; {{ $module->created_at->format('M d, Y') }}
                            </div>
                            @if($module->description)
                                <p style="font-size:12px;color:#666;margin-top:4px">{{ $module->description }}</p>
                            @endif
                        </div>
                        <div style="display:flex;gap:8px;align-items:center">
                            {{-- INLINE VIEW BUTTON --}}
                            <button onclick="openViewer('{{ route('modules.view', $module) }}', '{{ addslashes($module->title) }}', {{ $module->id }})" class="btn-teal">
                                👁 View
                            </button>

                            <a href="{{ route('modules.download', $module) }}" class="btn-ghost">
                                ⬇ Download
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

            {{-- STUDENT AI ASSISTANT SIDEBAR --}}
            @if(Auth::user()->isStudent())
            <div class="ai-drawer">
                <div class="ai-header">
                    <span>✨ Gemini Study Assistant</span>
                </div>
                <div class="ai-messages" id="aiMessages">
                    <div class="msg ai">Hi! I'm your Gemini AI Study Assistant. Ask me anything about this document! 📚</div>
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

        // Render user question
        const userMsg = document.createElement('div');
        userMsg.className = 'msg user';
        userMsg.innerText = text;
        chatBox.appendChild(userMsg);

        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        // Render loading placeholder
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ prompt: text })
            });

            const data = await response.json();

            // 1. Render Markdown to HTML
            aiMsg.innerHTML = typeof marked !== 'undefined' ? marked.parse(data.reply) : data.reply;

            // 2. Typeset LaTeX math notation
            if (window.MathJax) {
                MathJax.typesetPromise([aiMsg]);
            }
        } catch (err) {
            aiMsg.innerText = 'Sorry, failed to get a response from Gemini AI.';
        }

        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>

</body>
</html>