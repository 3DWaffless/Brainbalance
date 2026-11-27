{{-- FILE: resources/views/quiz/Play.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quiz — {{ config('app.name', 'BrainBalance') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Nunito', sans-serif;
            color: #0D2B4E;
            background-color: #F5FAFF;
            background-image:
                radial-gradient(at 6% 8%, rgba(0, 102, 214, 0.16) 0px, transparent 42%),
                radial-gradient(at 45% 4%, rgba(29, 196, 223, 0.18) 0px, transparent 42%),
                radial-gradient(at 92% 14%, rgba(104, 204, 24, 0.17) 0px, transparent 44%),
                radial-gradient(at 86% 86%, rgba(255, 132, 0, 0.16) 0px, transparent 46%),
                radial-gradient(#CBE6FF 2px, transparent 2px);
            background-size: 100% 100%, 100% 100%, 100% 100%, 100% 100%, 34px 34px;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .wrap { width: 100%; max-width: 620px; }
        .top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 900; color: #0066D6;
            background: #FFFFFF; border: 2.5px solid #B8E2F8;
            padding: 9px 15px; border-radius: 16px;
            text-decoration: none;
            box-shadow: 0 4px 0 #D4F1FC;
            transition: transform 0.12s ease;
        }
        .back-link:hover { transform: translateY(-2px); background: #F2FAFF; }
        .card {
            background: #FFFFFF;
            border-radius: 32px;
            padding: 30px 28px;
            border: 4px solid #B3E5FC;
            box-shadow: 0 12px 0 #D4F1FC, 0 24px 48px rgba(0, 102, 214, 0.10);
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 7px;
            background: linear-gradient(90deg, #0066D6 0%, #1DC4DF 33%, #68CC18 68%, #FF8400 100%);
        }
        .progress-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .progress-label { font-size: 13.5px; font-weight: 900; color: #0066D6; }
        .level-pill {
            font-size: 11px; font-weight: 900; padding: 4px 12px;
            border-radius: 999px; background: #ECFCCB; color: #3E8708;
            border: 2px solid #A3E635;
            text-transform: capitalize;
        }
        .beam-track { position: relative; height: 12px; background: #E0F2FE; border-radius: 999px; margin-bottom: 1.25rem; overflow: hidden; border: 2px solid #BAE6FD; }
        .beam-fill { position: absolute; top:0; left:0; height:100%; background: linear-gradient(90deg, #0066D6, #1DC4DF, #68CC18, #FF8400); border-radius: 999px; transition: width 0.4s ease; }

        .game-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #FFF7ED;
            border: 3px solid #FDBA74;
            border-radius: 20px;
            padding: 10px 16px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 0 #FED7AA;
        }
        .game-stat { display: flex; align-items: center; gap: 6px; font-weight: 900; font-size: 14px; color: #0D2B4E; }
        .streak-pill {
            font-size: 10px; font-weight: 900; padding: 2px 8px;
            border-radius: 999px; background: #FF8400; color: #fff;
            display: inline-block; animation: bounce 1s infinite;
        }
        .hidden { display: none !important; }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        .timer-track { position: relative; height: 10px; background: #E0F2FE; border-radius: 999px; margin-bottom: 1.15rem; overflow: hidden; }
        .timer-fill { position: absolute; top:0; left:0; height:100%; background: #68CC18; border-radius: 999px; transition: width 1s linear, background 0.3s ease; }

        h1 { font-family: 'Fredoka', 'Nunito', sans-serif; font-size: 26px; font-weight: 700; color: #0D2B4E; margin-bottom: 6px; }
        .sub { font-size: 14.5px; font-weight: 700; color: #5A7696; margin-bottom: 4px; }

        .question-bubble {
            background: #F2FAFF;
            border: 3px solid #99E2F5;
            border-radius: 22px;
            padding: 16px 18px;
            margin: 0.75rem 0 1.25rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
        .question-text { font-size: 19px; font-weight: 900; color: #0D2B4E; line-height: 1.4; }
        .btn-listen {
            flex-shrink: 0;
            background: #fff;
            border: 2px solid #1DC4DF;
            color: #0066D6;
            border-radius: 14px;
            padding: 7px 11px;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 3px 0 #BAE6FD;
        }

        .option-btn {
            width: 100%; text-align: left;
            border: 3px solid #B8E2F8; border-radius: 18px;
            padding: 14px 16px; margin-bottom: 11px;
            background: #FFFFFF; font-family: 'Nunito', sans-serif;
            font-size: 15px; font-weight: 900; color: #0D2B4E;
            cursor: pointer; box-shadow: 0 5px 0 #D4F1FC;
            display: flex; align-items: center; gap: 12px;
            transition: all 0.12s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .option-btn:hover:not(:disabled) { border-color: #0066D6; background: #F2FAFF; transform: translateY(-2px); box-shadow: 0 7px 0 #99E2F5; }
        .option-btn:active:not(:disabled) { transform: translateY(3px); box-shadow: 0 2px 0 #99E2F5; }
        .option-btn.correct { background: #ECFCCB; border-color: #68CC18; color: #274E08; box-shadow: 0 5px 0 #4FA30D; }
        .option-btn.incorrect { background: #FFF1E0; border-color: #FF8400; color: #7C2D12; box-shadow: 0 5px 0 #D96B00; }
        .option-btn:disabled { cursor: default; }

        .opt-letter {
            width: 34px; height: 34px; border-radius: 11px;
            font-size: 14px; font-weight: 900;
            display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .opt-0 { background: #EBF5FF; color: #0066D6; border: 2px solid #93C5FD; }
        .opt-1 { background: #E0F7FC; color: #0E7490; border: 2px solid #67E8F9; }
        .opt-2 { background: #ECFCCB; color: #3E8708; border: 2px solid #A3E635; }
        .opt-3 { background: #FFF7ED; color: #C2410C; border: 2px solid #FDBA74; }

        .btn-primary {
            background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);
            color: #fff; border: 2px solid #004FA8;
            padding: 15px 20px; border-radius: 20px; font-size: 16px; font-weight: 900;
            font-family: 'Nunito', sans-serif; cursor: pointer; width: 100%;
            box-shadow: 0 6px 0 #004FA8;
            transition: all 0.1s ease;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .btn-primary:active { transform: translateY(3px); box-shadow: 0 2px 0 #004FA8; }

        .feedback-icon { font-size: 52px; text-align: center; margin: 0.75rem 0; }
        .feedback-title { font-family: 'Fredoka', 'Nunito', sans-serif; font-size: 24px; font-weight: 700; text-align: center; margin-bottom: 6px; }
        .feedback-msg { font-size: 14px; font-weight: 700; color: #5A7696; text-align: center; margin-bottom: 1.5rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 1.25rem 0 1.75rem; }
        .stat-box { background: #F2FAFF; border-radius: 18px; padding: 14px; text-align: center; border: 2.5px solid #B8E2F8; }
        .stat-value { font-size: 20px; font-weight: 900; color: #0D2B4E; }
        .stat-label { font-size: 11px; font-weight: 800; color: #5A7696; margin-top: 2px; }
        .loading { text-align: center; padding: 2rem 0; font-size: 15px; font-weight: 800; color: #0066D6; }
        .screen { display: none; }
        .screen.active { display: block; }

        .confetti-piece {
            position: absolute;
            width: 10px; height: 10px; border-radius: 3px;
            pointer-events: none;
            animation: confettiFall 1.3s ease-out forwards;
        }
        @keyframes confettiFall {
            0% { transform: translateY(-20px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(380px) rotate(540deg); opacity: 0; }
        }
    </style>
</head>
<body>
<div id="play-toast-container" style="position:fixed; top:16px; right:16px; z-index:9999; display:flex; flex-direction:column; gap:10px; max-width:360px; width:calc(100% - 32px); pointer-events:none;"></div>

<div class="wrap">
    <div class="top-nav">
        <a href="{{ route('dashboard') }}" class="back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to Hub
        </a>
        <x-brand-logo :size="42" />
    </div>

    {{-- START SCREEN --}}
    <div class="screen active" id="screen-start">
        <div class="card">
            <div style="display:inline-block; padding: 5px 14px; border-radius: 999px; background: #FFF7ED; border: 2px solid #FF8400; font-size: 11.5px; font-weight: 900; color: #C2410C; text-transform: uppercase;">
                ⚔️ Quiz Quest
            </div>
            <h1 style="margin-top: 10px;">{{ $quiz->title }} 🎯</h1>
            <p class="sub">Answer {{ $quiz->questions->count() }} questions, build up answer streaks, and earn bonus XP stars!</p>
            <div style="margin-top: 1.5rem;">
                <button class="btn-primary" style="padding: 16px; font-size: 17px;" onclick="startQuiz({{ $quiz->id }})">
                    🚀 Start Quiz Now
                </button>
            </div>
        </div>
    </div>

    {{-- LOADING --}}
    <div class="screen" id="screen-loading">
        <div class="card">
            <div class="loading">Getting your next question ready... 🤔</div>
        </div>
    </div>

    {{-- QUESTION SCREEN --}}
    <div class="screen" id="screen-question">
        <div class="progress-row">
            <span class="progress-label" id="question-counter">Question 1 of 5</span>
            <span class="level-pill" id="level-pill">easy</span>
        </div>
        <div class="beam-track"><div class="beam-fill" id="beam-fill" style="width:20%"></div></div>

        <div class="game-bar">
            <div class="game-stat">⏱️ <span id="timer-display">30</span>s</div>
            <div class="game-stat">🔥 <span id="streak-count">0</span>x <span id="multiplier-tag" class="streak-pill hidden">1.5x XP</span></div>
            <div class="game-stat">⭐ <span id="total-xp">0</span> XP</div>
        </div>

        <div class="timer-track"><div class="timer-fill" id="timer-fill" style="width:100%"></div></div>
        <div class="card" id="question-card">
            <div class="question-bubble">
                <div class="question-text" id="question-text"></div>
                <button type="button" class="btn-listen" onclick="readAloudPlay()">🔊 Listen</button>
            </div>
            <div id="options-container"></div>
        </div>
    </div>

    {{-- FEEDBACK SCREEN --}}
    <div class="screen" id="screen-feedback">
        <div class="card" id="feedback-card">
            <div class="feedback-icon" id="feedback-icon"></div>
            <div class="feedback-title" id="feedback-title"></div>
            <div class="feedback-msg" id="feedback-msg"></div>
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value" id="fb-xp">+0</div>
                    <div class="stat-label">XP Earned</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="fb-streak">0x</div>
                    <div class="stat-label">Current Streak</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="fb-time">0s</div>
                    <div class="stat-label">Time Taken</div>
                </div>
            </div>
            <button class="btn-primary" onclick="loadNext()">Continue &rarr;</button>
        </div>
    </div>

    {{-- SUMMARY SCREEN --}}
    <div class="screen" id="screen-summary">
        <div class="card" id="summary-card" style="text-align:center">
            <div style="font-size:62px;line-height:1">🏆</div>
            <h1 style="text-align:center; margin-top: 10px;">Quiz Completed!</h1>
            <p class="sub" style="text-align:center">Awesome job, superstar! Here is how you did:</p>
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value" id="sum-score">0/5</div>
                    <div class="stat-label">Final Score</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="sum-streak">0x</div>
                    <div class="stat-label">Max Streak</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="sum-xp">0</div>
                    <div class="stat-label">Total XP</div>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="btn-primary" style="display:block;text-align:center;text-decoration:none;">Back to Adventure Hub 🚀</a>
        </div>
    </div>
</div>

<script>
    let audioCtx = null;
    function getAudioContext() {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') audioCtx.resume();
        return audioCtx;
    }
    function playPopSound() {
        try {
            const ctx = getAudioContext(); const osc = ctx.createOscillator(); const gain = ctx.createGain();
            osc.type = 'sine'; osc.frequency.setValueAtTime(400, ctx.currentTime); osc.frequency.exponentialRampToValueAtTime(800, ctx.currentTime + 0.08);
            gain.gain.setValueAtTime(0.15, ctx.currentTime); gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.08);
            osc.connect(gain); gain.connect(ctx.destination); osc.start(); osc.stop(ctx.currentTime + 0.08);
        } catch (e) {}
    }
    function playCorrectSound() {
        try {
            const ctx = getAudioContext(); const now = ctx.currentTime;
            [523.25, 659.25, 783.99].forEach((freq, idx) => {
                const osc = ctx.createOscillator(); const gain = ctx.createGain();
                osc.type = 'triangle'; osc.frequency.setValueAtTime(freq, now + idx * 0.08);
                gain.gain.setValueAtTime(0.2, now + idx * 0.08); gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.08 + 0.2);
                osc.connect(gain); gain.connect(ctx.destination); osc.start(now + idx * 0.08); osc.stop(now + idx * 0.08 + 0.2);
            });
        } catch (e) {}
    }
    function playIncorrectSound() {
        try {
            const ctx = getAudioContext(); const now = ctx.currentTime; const osc = ctx.createOscillator(); const gain = ctx.createGain();
            osc.type = 'sawtooth'; osc.frequency.setValueAtTime(220, now); osc.frequency.linearRampToValueAtTime(140, now + 0.25);
            gain.gain.setValueAtTime(0.15, now); gain.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
            osc.connect(gain); gain.connect(ctx.destination); osc.start(now); osc.stop(now + 0.25);
        } catch (e) {}
    }
    function playFanfareSound() {
        try {
            const ctx = getAudioContext(); const now = ctx.currentTime;
            [523.25, 659.25, 783.99, 1046.50].forEach((freq, idx) => {
                const osc = ctx.createOscillator(); const gain = ctx.createGain();
                osc.type = 'sine'; osc.frequency.setValueAtTime(freq, now + idx * 0.1);
                gain.gain.setValueAtTime(0.2, now + idx * 0.1); gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.1 + 0.3);
                osc.connect(gain); gain.connect(ctx.destination); osc.start(now + idx * 0.1); osc.stop(now + idx * 0.1 + 0.3);
            });
        } catch (e) {}
    }
    function launchPlayConfetti(targetEl, count = 26) {
        if (!targetEl) return;
        const colors = ['#0066D6', '#1DC4DF', '#68CC18', '#FF8400'];
        for (let i = 0; i < count; i++) {
            const piece = document.createElement('div');
            piece.className = 'confetti-piece';
            piece.style.left = Math.random() * 100 + '%';
            piece.style.top = '-10px';
            piece.style.backgroundColor = colors[i % colors.length];
            piece.style.animationDelay = (Math.random() * 0.25) + 's';
            targetEl.appendChild(piece);
            setTimeout(() => piece.remove(), 1600);
        }
    }
    function showPlayBadgeToast(alertObj, delayMs = 3000) {
        const container = document.getElementById('play-toast-container');
        if (!container) return;
        const isEarned = (alertObj.type || 'earned') === 'earned';
        const toast = document.createElement('div');
        toast.style.cssText = `pointer-events:auto;display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:18px;font-family:'Nunito',sans-serif;font-weight:900;font-size:13px;box-shadow:0 10px 25px rgba(0,0,0,0.15);border:4px solid ${isEarned ? '#FDE68A' : '#FDA4AF'};background:${isEarned ? '#FBBF24' : '#F43F5E'};color:${isEarned ? '#451A03' : '#FFFFFF'};transition:opacity 0.5s ease,transform 0.5s ease;`;
        const imgHtml = alertObj.image
            ? `<img src="${alertObj.image}" alt="${alertObj.name || 'Badge'}" style="width:40px;height:40px;object-fit:contain;flex-shrink:0;background:rgba(255,255,255,0.85);border-radius:12px;padding:4px;">`
            : `<span style="font-size:24px;flex-shrink:0;">${isEarned ? '🏅' : '💔'}</span>`;
        toast.innerHTML = `${imgHtml}<div><div style="font-size:10px;text-transform:uppercase;letter-spacing:0.05em;opacity:0.8;">${isEarned ? '🎉 New Badge Unlocked!' : '⚠️ Badge Taken Away'}</div><div style="line-height:1.3;">${alertObj.message || ('You earned ' + alertObj.name)}</div></div>`;
        container.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(-8px)'; setTimeout(() => toast.remove(), 500); }, delayMs);
    }

    document.addEventListener('click', (e) => { if (e.target.closest('.btn-primary, .option-btn')) playPopSound(); });

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const optionLetters = ['A', 'B', 'C', 'D'];
    let pendingQuestion = null;
    let currentQuestionTimeLimit = 30;
    let timer = currentQuestionTimeLimit;
    let timerInterval = null;
    let currentStreak = 0;
    let totalXp = 0;

    function readAloudPlay() {
        if (!('speechSynthesis' in window) || !pendingQuestion) return;
        window.speechSynthesis.cancel();
        let text = pendingQuestion.text || '';
        if (Array.isArray(pendingQuestion.options)) {
            pendingQuestion.options.forEach((opt, i) => { text += `. ${optionLetters[i] || ''}: ${opt}`; });
        }
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.92; utterance.pitch = 1.08;
        window.speechSynthesis.speak(utterance);
    }

    function showScreen(id) {
        if ('speechSynthesis' in window) window.speechSynthesis.cancel();
        document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
        document.getElementById(id).classList.add('active');
    }

    async function startQuiz(quizId) {
        showScreen('screen-loading');
        const res = await fetch('{{ route('quiz.start') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ quiz_id: quizId }),
        });
        const data = await res.json();
        currentStreak = data.current_streak || 0;
        totalXp = data.total_xp || 0;
        renderQuestion(data);
    }

    function startTimer(timeLimit) {
        clearInterval(timerInterval);
        currentQuestionTimeLimit = timeLimit || 30;
        timer = currentQuestionTimeLimit;
        const timerDisplay = document.getElementById('timer-display');
        const timerFill = document.getElementById('timer-fill');
        timerDisplay.textContent = timer;
        timerFill.style.width = '100%';
        timerFill.style.background = '#68CC18';
        timerInterval = setInterval(() => {
            timer--;
            timerDisplay.textContent = timer;
            const pct = (timer / currentQuestionTimeLimit) * 100;
            timerFill.style.width = pct + '%';
            if (timer <= 5) timerFill.style.background = '#FF8400';
            if (timer <= 0) { clearInterval(timerInterval); selectAnswer(-1, null, true); }
        }, 1000);
    }

    function renderQuestion(data) {
        pendingQuestion = data.question;
        document.getElementById('question-counter').textContent = ` Question ${data.question_number} of ${data.total_questions}`;
        document.getElementById('level-pill').textContent = data.level;
        document.getElementById('beam-fill').style.width = `${(data.question_number / data.total_questions) * 100}%`;
        document.getElementById('streak-count').textContent = currentStreak;
        document.getElementById('total-xp').textContent = totalXp;

        const multTag = document.getElementById('multiplier-tag');
        if (currentStreak >= 5) { multTag.textContent = '2.0x XP'; multTag.classList.remove('hidden'); }
        else if (currentStreak >= 3) { multTag.textContent = '1.5x XP'; multTag.classList.remove('hidden'); }
        else { multTag.classList.add('hidden'); }

        document.getElementById('question-text').textContent = data.question.text;
        const container = document.getElementById('options-container');
        container.innerHTML = '';
        data.question.options.forEach((opt, idx) => {
            const btn = document.createElement('button');
            btn.className = 'option-btn';
            const badge = document.createElement('span');
            badge.className = `opt-letter opt-${idx % 4}`;
            badge.textContent = optionLetters[idx] || (idx + 1);
            const label = document.createElement('span');
            label.style.flex = '1'; label.textContent = opt;
            btn.append(badge, label);
            btn.onclick = () => selectAnswer(idx, btn, false);
            container.appendChild(btn);
        });
        showScreen('screen-question');
        startTimer(data.question.time_limit);
    }

    async function selectAnswer(selectedIndex, btnEl, isTimeout = false) {
        clearInterval(timerInterval);
        document.querySelectorAll('.option-btn').forEach(b => b.disabled = true);
        const timeTaken = currentQuestionTimeLimit - timer;
        const isCorrect = !isTimeout && selectedIndex === pendingQuestion.correct_index;

        document.querySelectorAll('.option-btn').forEach((b, idx) => {
            if (idx === pendingQuestion.correct_index) b.classList.add('correct');
            else if (idx === selectedIndex && !isCorrect) b.classList.add('incorrect');
        });

        const res = await fetch('{{ route('quiz.answer') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                question_id: pendingQuestion.id,
                selected_index: selectedIndex,
                question_text: pendingQuestion.text,
                correct_index: pendingQuestion.correct_index,
                options: pendingQuestion.options,
                time_taken_seconds: timeTaken,
            }),
        });
        if (!res.ok) return;
        const data = await res.json();
        currentStreak = data.current_streak;
        totalXp = data.total_xp;
        setTimeout(() => showFeedback(isCorrect, isTimeout, timeTaken, data), 500);
    }

    let lastResponse = null;
    function showFeedback(isCorrect, isTimeout, timeTaken, data) {
        lastResponse = data;
        const icon = document.getElementById('feedback-icon');
        const title = document.getElementById('feedback-title');
        const msg = document.getElementById('feedback-msg');
        document.getElementById('fb-xp').textContent = `+${data.earned_xp || 0}`;
        document.getElementById('fb-streak').textContent = `${data.current_streak || 0}x`;
        document.getElementById('fb-time').textContent = `${timeTaken}s`;

        if (isCorrect) {
            playCorrectSound();
            icon.textContent = data.multiplier > 1.0 ? '🔥' : '🎉';
            title.textContent = data.multiplier > 1.0 ? `Awesome! (${data.multiplier}x Multiplier!)` : 'Awesome Job!';
            title.style.color = '#4FA30D';
            msg.textContent = "Way to go! Let's try something a little trickier.";
        } else if (isTimeout) {
            playIncorrectSound();
            icon.textContent = '⏰';
            title.textContent = "Time's up!";
            title.style.color = '#D96B00';
            msg.textContent = "Speed is key! Let's try an easier one next.";
        } else {
            playIncorrectSound();
            icon.textContent = '💡';
            title.textContent = 'Good Try!';
            title.style.color = '#0066D6';
            msg.textContent = "That's okay — streak reset, let's try an easier one next.";
        }
        showScreen('screen-feedback');
        if (isCorrect) launchPlayConfetti(document.getElementById('feedback-card'), 22);
    }

    function loadNext() {
        if (lastResponse.finished) {
            playFanfareSound();
            document.getElementById('sum-score').textContent = `${lastResponse.summary.score}/${lastResponse.summary.total}`;
            document.getElementById('sum-streak').textContent = `${lastResponse.summary.max_streak || 0}x`;
            document.getElementById('sum-xp').textContent = lastResponse.summary.total_xp || 0;
            showScreen('screen-summary');
            launchPlayConfetti(document.getElementById('summary-card'), 40);
            if (Array.isArray(lastResponse.new_badges)) {
                lastResponse.new_badges.forEach((badgeAlert, idx) => showPlayBadgeToast(badgeAlert, 3000 + (idx * 250)));
            }
        } else {
            renderQuestion(lastResponse);
        }
    }
</script>
</body>
</html>