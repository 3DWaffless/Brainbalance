{{-- FILE: resources/views/quiz/play.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quiz — {{ config('app.name', 'BrainBalance') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', sans-serif;
            background: #FFF8F0;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2.5rem 1rem;
        }

        .wrap { width: 100%; max-width: 560px; }

        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 800; color: #aaa;
            text-decoration: none; margin-bottom: 1.25rem;
        }
        .back-link:hover { color: #FF6B6B; }

        .card {
            background: #fff;
            border-radius: 20px;
            padding: 28px;
            border: 2px solid #FFE4C4;
        }

        /* progress */
        .progress-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .progress-label { font-size: 12px; font-weight: 900; color: #aaa; }
        .level-pill {
            font-size: 11px; font-weight: 900; padding: 3px 10px;
            border-radius: 999px; background: #FFE4C4; color: #2D2D2D;
            text-transform: capitalize;
        }
        .beam-track { position: relative; height: 8px; background: #FFE4C4; border-radius: 999px; margin-bottom: 1.25rem; overflow: hidden; }
        .beam-fill { position: absolute; top:0; left:0; height:100%; background: linear-gradient(90deg, #FF6B6B, #4ECDC4); border-radius: 999px; transition: width 0.4s ease; }

        /* gamification bar */
        .game-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #FFF8F0;
            border: 2px solid #FFE4C4;
            border-radius: 16px;
            padding: 10px 16px;
            margin-bottom: 1.25rem;
        }
        .game-stat { display: flex; align-items: center; gap: 6px; font-weight: 900; font-size: 14px; color: #2D2D2D; }
        .streak-pill {
            font-size: 10px; font-weight: 900; padding: 2px 8px;
            border-radius: 999px; background: #FFD166; color: #7A4B00;
            display: inline-block; animation: bounce 1s infinite;
        }
        .hidden { display: none !important; }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        /* timer bar */
        .timer-track { position: relative; height: 6px; background: #FFE4C4; border-radius: 999px; margin-bottom: 1rem; overflow: hidden; }
        .timer-fill { position: absolute; top:0; left:0; height:100%; background: #FF6B6B; border-radius: 999px; transition: width 1s linear, background 0.3s ease; }

        /* start screen */
        .subject-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin: 1.5rem 0; }
        .subject-btn {
            border: 2px solid #FFE4C4; border-radius: 16px; padding: 20px;
            background: #FFF8F0; cursor: pointer; text-align: center;
            font-family: 'Nunito', sans-serif; transition: border-color 0.15s;
        }
        .subject-btn:hover { border-color: #FF6B6B; }
        .subject-btn .emoji { font-size: 30px; margin-bottom: 8px; }
        .subject-btn .label { font-size: 14px; font-weight: 900; color: #2D2D2D; }

        h1 { font-size: 20px; font-weight: 900; color: #2D2D2D; margin-bottom: 6px; }
        .sub { font-size: 13px; font-weight: 700; color: #aaa; margin-bottom: 4px; }

        .question-text { font-size: 18px; font-weight: 800; color: #2D2D2D; margin: 1rem 0 1.25rem; line-height: 1.4; }

        .option-btn {
            width: 100%; text-align: left;
            border: 2px solid #FFE4C4; border-radius: 14px;
            padding: 14px 16px; margin-bottom: 10px;
            background: #FFF8F0; font-family: 'Nunito', sans-serif;
            font-size: 14px; font-weight: 700; color: #2D2D2D;
            cursor: pointer; transition: border-color 0.15s, background 0.15s;
        }
        .option-btn:hover:not(:disabled) { border-color: #FF6B6B; background: #fff; }
        .option-btn.correct { background: #D4EDDA; border-color: #34A853; }
        .option-btn.incorrect { background: #F8D7DA; border-color: #EA4335; }
        .option-btn:disabled { cursor: default; }

        .btn-primary {
            background: #FF6B6B; color: #fff; border: none;
            padding: 12px 20px; border-radius: 12px; font-size: 14px; font-weight: 900;
            font-family: 'Nunito', sans-serif; cursor: pointer; width: 100%;
            transition: background 0.15s;
        }
        .btn-primary:hover { background: #e85555; }

        .feedback-icon { font-size: 44px; text-align: center; margin: 0.75rem 0; }
        .feedback-title { font-size: 20px; font-weight: 900; text-align: center; margin-bottom: 6px; }
        .feedback-msg { font-size: 13px; font-weight: 700; color: #aaa; text-align: center; margin-bottom: 1.5rem; }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 1.25rem 0 1.75rem; }
        .stat-box { background: #FFF8F0; border-radius: 14px; padding: 14px; text-align: center; border: 1px solid #FFE4C4; }
        .stat-value { font-size: 20px; font-weight: 900; color: #2D2D2D; }
        .stat-label { font-size: 11px; font-weight: 700; color: #aaa; margin-top: 2px; }

        .loading { text-align: center; padding: 2rem 0; font-size: 13px; font-weight: 700; color: #aaa; }

        .screen { display: none; }
        .screen.active { display: block; }
    </style>
</head>
<body>

<div class="wrap">
    <a href="{{ route('dashboard') }}" class="back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to dashboard
    </a>

    {{-- START SCREEN --}}
    <div class="screen active" id="screen-start">
        <div class="card">
            <h1>Ready for today's challenge? 🧠</h1>
            <p class="sub">Answer 5 questions. Build up streaks and earn bonus XP as you go!</p>

            <div class="subject-grid">
                <button class="subject-btn" onclick="startQuiz('math')">
                    <div class="emoji">🔢</div>
                    <div class="label" style="color:#FF6B6B">Math</div>
                </button>
                <button class="subject-btn" onclick="startQuiz('english')">
                    <div class="emoji">📖</div>
                    <div class="label" style="color:#4ECDC4">English</div>
                </button>
            </div>
        </div>
    </div>

    {{-- LOADING --}}
    <div class="screen" id="screen-loading">
        <div class="card">
            <div class="loading">Thinking of a question for you... 🤔</div>
        </div>
    </div>

    {{-- QUESTION SCREEN --}}
    <div class="screen" id="screen-question">
        <div class="progress-row">
            <span class="progress-label" id="question-counter">Question 1 of 5</span>
            <span class="level-pill" id="level-pill">easy</span>
        </div>
        <div class="beam-track"><div class="beam-fill" id="beam-fill" style="width:20%"></div></div>

        <!-- Gamification Header Bar -->
        <div class="game-bar">
            <div class="game-stat">
                ⏱️ <span id="timer-display">15</span>s
            </div>
            <div class="game-stat">
                🔥 <span id="streak-count">0</span>x
                <span id="multiplier-tag" class="streak-pill hidden">1.5x XP</span>
            </div>
            <div class="game-stat">
                ⭐ <span id="total-xp">0</span> XP
            </div>
        </div>

        <div class="timer-track"><div class="timer-fill" id="timer-fill" style="width:100%"></div></div>

        <div class="card">
            <div class="question-text" id="question-text"></div>
            <div id="options-container"></div>
        </div>
    </div>

    {{-- FEEDBACK SCREEN --}}
    <div class="screen" id="screen-feedback">
        <div class="card">
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

            <button class="btn-primary" onclick="loadNext()">Continue</button>
        </div>
    </div>

    {{-- SUMMARY SCREEN --}}
    <div class="screen" id="screen-summary">
        <div class="card" style="text-align:center">
            <div style="font-size:56px;line-height:1">🏆</div>
            <h1 style="text-align:center; margin-top: 10px;">Quiz Completed!</h1>
            <p class="sub" style="text-align:center">Awesome job! Here is how you did:</p>

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

            <a href="{{ route('dashboard') }}" class="btn-primary" style="display:block;text-align:center;text-decoration:none;">Back to dashboard</a>
        </div>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let pendingQuestion = null;

    // Gamification & Timer state
    const timePerQuestion = 15;
    let timer = timePerQuestion;
    let timerInterval = null;
    let currentStreak = 0;
    let totalXp = 0;

    function showScreen(id) {
        document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
        document.getElementById(id).classList.add('active');
    }

    async function startQuiz(subject) {
        showScreen('screen-loading');

        const res = await fetch('{{ route('quiz.start') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ subject }),
        });

        const data = await res.json();
        currentStreak = data.current_streak || 0;
        totalXp = data.total_xp || 0;
        
        renderQuestion(data);
    }

    function startTimer() {
        clearInterval(timerInterval);
        timer = timePerQuestion;
        
        const timerDisplay = document.getElementById('timer-display');
        const timerFill = document.getElementById('timer-fill');

        timerDisplay.textContent = timer;
        timerFill.style.width = '100%';
        timerFill.style.background = '#FF6B6B';

        timerInterval = setInterval(() => {
            timer--;
            timerDisplay.textContent = timer;
            
            const pct = (timer / timePerQuestion) * 100;
            timerFill.style.width = pct + '%';

            if (timer <= 5) {
                timerFill.style.background = '#EA4335';
            }

            if (timer <= 0) {
                clearInterval(timerInterval);
                // Automatically submit timeout (-1 indicates no selection)
                selectAnswer(-1, null, true);
            }
        }, 1000);
    }

    function renderQuestion(data) {
        pendingQuestion = data.question;

        document.getElementById('question-counter').textContent =
            `Question ${data.question_number} of ${data.total_questions}`;
        document.getElementById('level-pill').textContent = data.level;
        document.getElementById('beam-fill').style.width =
            `${(data.question_number / data.total_questions) * 100}%`;

        // Update gamification bar
        document.getElementById('streak-count').textContent = currentStreak;
        document.getElementById('total-xp').textContent = totalXp;

        const multTag = document.getElementById('multiplier-tag');
        if (currentStreak >= 5) {
            multTag.textContent = '2.0x XP';
            multTag.classList.remove('hidden');
        } else if (currentStreak >= 3) {
            multTag.textContent = '1.5x XP';
            multTag.classList.remove('hidden');
        } else {
            multTag.classList.add('hidden');
        }

        document.getElementById('question-text').textContent = data.question.text;

        const container = document.getElementById('options-container');
        container.innerHTML = '';
        data.question.options.forEach((opt, idx) => {
            const btn = document.createElement('button');
            btn.className = 'option-btn';
            btn.textContent = opt;
            btn.onclick = () => selectAnswer(idx, btn, false);
            container.appendChild(btn);
        });

        showScreen('screen-question');
        startTimer();
    }

    async function selectAnswer(selectedIndex, btnEl, isTimeout = false) {
        clearInterval(timerInterval);
        document.querySelectorAll('.option-btn').forEach(b => b.disabled = true);

        const timeTaken = timePerQuestion - timer;
        const isCorrect = !isTimeout && selectedIndex === pendingQuestion.correct_index;

        document.querySelectorAll('.option-btn').forEach((b, idx) => {
            if (idx === pendingQuestion.correct_index) b.classList.add('correct');
            else if (idx === selectedIndex && !isCorrect) b.classList.add('incorrect');
        });

        const res = await fetch('{{ route('quiz.answer') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                question_id: pendingQuestion.id,
                selected_index: selectedIndex,
                question_text: pendingQuestion.text,
                correct_index: pendingQuestion.correct_index,
                options: pendingQuestion.options,
                time_taken_seconds: timeTaken,
            }),
        });

        if (!res.ok) {
            const errBody = await res.text();
            console.error('Quiz answer request failed:', res.status, errBody);
            alert('Something went wrong submitting your answer (status ' + res.status + '). Check the console for details.');
            return;
        }

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
            icon.textContent = data.multiplier > 1.0 ? '🔥' : '🎉';
            title.textContent = data.multiplier > 1.0 ? `Correct! (${data.multiplier}x Multiplier!)` : 'Correct!';
            title.style.color = '#34A853';
            msg.textContent = "Great job! Let's try something a little trickier.";
        } else if (isTimeout) {
            icon.textContent = '⏰';
            title.textContent = "Time's up!";
            title.style.color = '#EA4335';
            msg.textContent = "Speed is key! Let's try an easier one next.";
        } else {
            icon.textContent = '💡';
            title.textContent = 'Not quite';
            title.style.color = '#EA4335';
            msg.textContent = "That's okay — streak reset, let's try an easier one next.";
        }

        showScreen('screen-feedback');
    }

    function loadNext() {
        if (lastResponse.finished) {
            document.getElementById('sum-score').textContent = `${lastResponse.summary.score}/${lastResponse.summary.total}`;
            document.getElementById('sum-streak').textContent = `${lastResponse.summary.max_streak || 0}x`;
            document.getElementById('sum-xp').textContent = lastResponse.summary.total_xp || 0;

            showScreen('screen-summary');
        } else {
            renderQuestion(lastResponse);
        }
    }
</script>

</body>
</html>