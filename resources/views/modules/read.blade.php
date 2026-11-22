{{-- FILE: resources/views/modules/read.blade.php --}}
@extends('layouts.app')

@section('title', $module->title . ' - Study Reader')

@section('content')
<style>
    .reader-grid {
        display: grid;
        grid-template-columns: minmax(340px, 1fr) minmax(360px, 1fr);
        gap: 18px;
        height: calc(100vh - 185px);
        min-height: 520px;
    }
    .reader-pane {
        background: #fff;
        border: 3px solid var(--bb-border, #C7E0FF);
        border-radius: 24px;
        box-shadow: 0 6px 0 var(--bb-border, #C7E0FF);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .pane-header {
        padding: 12px 18px;
        font-size: 13px;
        font-weight: 900;
        color: var(--bb-text, #0F2D52);
        background: var(--bb-blue-soft, #EBF5FF);
        border-bottom: 3px dashed var(--bb-border, #C7E0FF);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .reader-pane.ai .pane-header {
        color: var(--bb-blue, #1D7BF2);
        background: linear-gradient(90deg, #EBF5FF 0%, #ECFCCB 100%);
    }
    .doc-frame {
        flex: 1;
        width: 100%;
        border: 0;
        min-height: 0;
        background: #f8fafc;
    }
    #ai-chat-box {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        background: var(--bb-bg, #F3F9FF);
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-height: 0;
    }
    .msg {
        padding: 12px 15px;
        border-radius: 18px;
        font-size: 13.5px;
        font-weight: 700;
        line-height: 1.55;
        max-width: 90%;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .msg.bot  {
        background: #FFFFFF;
        border: 2.5px solid var(--bb-border, #C7E0FF);
        color: var(--bb-text, #0F2D52);
        align-self: flex-start;
        box-shadow: 0 3px 0 #E2EEFF;
    }
    .msg.user {
        background: var(--bb-blue, #1D7BF2);
        color: #fff;
        align-self: flex-end;
        border: 2px solid var(--bb-blue-dark, #155EC2);
        box-shadow: 0 3px 0 #155EC2;
    }
    .msg.warn {
        background: #FEF3C7;
        border: 2px solid #FCD34D;
        color: #92400E;
        align-self: flex-start;
    }
    .suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 10px 14px 4px;
        background: #fff;
        flex-shrink: 0;
    }
    .suggestions button {
        border: 2px solid var(--bb-border, #C7E0FF);
        background: var(--bb-bg, #F3F9FF);
        color: var(--bb-blue, #1D7BF2);
        font-size: 11px;
        font-weight: 900;
        padding: 6px 12px;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .suggestions button:hover {
        background: #DBEAFE;
        border-color: #93C5FD;
    }
    .chat-form {
        display: flex;
        gap: 8px;
        padding: 12px 14px;
        background: #fff;
        border-top: 3px dashed var(--bb-border, #C7E0FF);
        flex-shrink: 0;
    }
    .chat-input {
        flex: 1;
        padding: 11px 14px;
        border-radius: 14px;
        border: 2.5px solid var(--bb-border, #C7E0FF);
        background: var(--bb-bg, #F3F9FF);
        font-size: 13px;
        font-weight: 800;
        color: var(--bb-text, #0F2D52);
        outline: none;
    }
    .chat-input:focus {
        border-color: var(--bb-blue, #1D7BF2);
        background: #fff;
    }
    @media (max-width: 900px) {
        .reader-grid { grid-template-columns: 1fr; grid-template-rows: 52vh 65vh; height: auto; }
    }
</style>

<!-- Header Bar -->
<div class="card-tactile mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
    <div>
        <a href="{{ route('subjects.show', $module->class_id) }}" class="btn-tactile-ghost text-xs py-1.5 px-3 mb-2">
            &larr; Back to Class
        </a>
        <h1 class="font-display text-xl sm:text-2xl font-bold text-brand-text">📖 {{ $module->title }}</h1>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        @if(Auth::check() && (Auth::user()->isTeacher() || Auth::user()->role === 'admin'))
            <a href="{{ route('quiz.create', ['module_id' => $module->id]) }}" class="btn-tactile-teal text-xs py-2.5 px-4">
                ⚡ Generate Quiz
            </a>
        @endif
        <a href="{{ route('modules.download', $module->id) }}" class="btn-tactile-primary text-xs py-2.5 px-4">
            ⬇️ Download PDF
        </a>
    </div>
</div>

<div class="reader-grid">
    <!-- LEFT: Mini PDF Viewer -->
    <div class="reader-pane">
        <div class="pane-header">
            <span>📄 Lesson Viewer — {{ $module->file_name }}</span>
        </div>
        <iframe class="doc-frame" src="{{ route('modules.view', $module->id) }}#toolbar=1&navpanes=0" title="{{ $module->title }}"></iframe>
    </div>

    <!-- RIGHT: AI Assistant -->
    <div class="reader-pane ai">
        <div class="pane-header">
            <span>🤖 BrainBalance AI Study Buddy</span>
            <span class="px-2.5 py-0.5 rounded-full bg-white border border-sky-200 text-[10px] font-black text-brand-coral">Groq AI</span>
        </div>
        <div id="ai-chat-box">
            <div class="msg bot">
                👋 Hi! I'm your BrainBalance Study Buddy. I've read <strong>{{ $module->title }}</strong>. Ask me anything about this lesson!
            </div>
        </div>
        <div class="suggestions" id="ai-suggestions">
            <button type="button" onclick="useSuggestion(this)">✨ Summarize this</button>
            <button type="button" onclick="useSuggestion(this)">🔤 What are the key words?</button>
            <button type="button" onclick="useSuggestion(this)">🎯 Quiz me with 3 questions</button>
            <button type="button" onclick="useSuggestion(this)">🧒 Explain it simply</button>
        </div>
        <form class="chat-form" onsubmit="sendAiMessage(event)">
            <input type="text" id="ai-user-input" class="chat-input" placeholder="Ask a question about this lesson..." autocomplete="off" required>
            <button type="submit" id="ai-send-btn" class="btn-tactile-primary px-4 py-2 text-xs shrink-0">Send 🚀</button>
        </form>
    </div>
</div>

<script>
    const AI_URL   = @json(route('modules.ai-chat', $module->id));
    const chatBox  = document.getElementById('ai-chat-box');
    const input    = document.getElementById('ai-user-input');
    const sendBtn  = document.getElementById('ai-send-btn');
    const history  = [];

    function speakText(text) {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text.replace(/\*\*/g, ''));
        utterance.rate = 0.94;
        utterance.pitch = 1.05;
        window.speechSynthesis.speak(utterance);
    }

    function addMessage(text, type) {
        const el = document.createElement('div');
        el.className = 'msg ' + type;
        if (type === 'bot') {
            const body = document.createElement('div');
            body.innerHTML = escapeHtml(text).replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            el.appendChild(body);

            if (text !== 'Thinking...') {
                const listenBtn = document.createElement('button');
                listenBtn.type = 'button';
                listenBtn.textContent = '🔊 Listen';
                listenBtn.style.cssText = 'margin-top:8px; display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:999px; background:#EBF5FF; border:1.5px solid #BAE6FD; color:#0284C7; font-size:11px; font-weight:900; cursor:pointer;';
                listenBtn.onclick = () => speakText(text);
                el.appendChild(listenBtn);
            }
        } else {
            el.textContent = text;
        }
        chatBox.appendChild(el);
        chatBox.scrollTop = chatBox.scrollHeight;
        return el;
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function useSuggestion(btn) {
        input.value = btn.textContent.replace(/^[^\w]+/, '').trim();
        document.getElementById('ai-suggestions').style.display = 'none';
        input.form.requestSubmit();
    }

    async function sendAiMessage(e) {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        addMessage(message, 'user');
        input.value = '';
        sendBtn.disabled = true;
        input.disabled = true;

        const typing = addMessage('Thinking...', 'bot');

        try {
            const res = await fetch(AI_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: message, history: history.slice(-6) })
            });
            const data = await res.json();
            typing.remove();

            if (data.reply) {
                addMessage(data.reply, 'bot');
                history.push({ role: 'user', content: message });
                history.push({ role: 'assistant', content: data.reply });
            } else {
                addMessage(data.error || 'Something went wrong. Please try again.', 'warn');
            }
        } catch (err) {
            typing.remove();
            addMessage('⚠️ Unable to connect to the AI assistant. Please try again.', 'warn');
        } finally {
            sendBtn.disabled = false;
            input.disabled = false;
            input.focus();
        }
    }
</script>
@endsection