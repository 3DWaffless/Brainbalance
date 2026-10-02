<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Quiz — BrainBalance</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Nunito', sans-serif; background: #FFF8F0; min-height: 100vh; display: flex; color: #2D2D2D; }
        
        .sidebar { width: 240px; background: #fff; border-right: 2px solid #FFE4C4; padding: 1.25rem 0.75rem; position: fixed; height: 100vh; }
        .main-content { margin-left: 240px; flex: 1; padding: 2rem; }
        
        .card { background: #fff; border-radius: 18px; padding: 24px; border: 2px solid #FFE4C4; margin-bottom: 20px; max-width: 800px; }
        .form-label { font-size: 13px; font-weight: 800; color: #555; display: block; margin-bottom: 6px; }
        .form-input, .form-select { width: 100%; height: 44px; padding: 0 14px; border-radius: 12px; border: 2px solid #FFE4C4; background: #FFF8F0; font-family: 'Nunito'; font-weight: 700; font-size: 14px; margin-bottom: 16px; outline: none; }
        .btn-primary { background: #FF6B6B; color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 900; cursor: pointer; }
        .btn-teal { background: #4ECDC4; color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 900; cursor: pointer; }
        .question-box { background: #FFF8F0; padding: 16px; border-radius: 12px; border: 2px solid #FFE4C4; margin-bottom: 12px; }
        .date-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    </style>
</head>
<body>

<!-- PASTE YOUR SIDEBAR HTML HERE -->

<main class="main-content">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-weight: 900; font-size: 24px;">AI Quiz Generator 🤖</h1>
        <p style="color: #888; font-weight: 700;">Upload a PDF module and let AI generate the questions.</p>
    </div>

    <!-- Display error alerts if generation fails -->
    @if (session('error'))
        <div class="card" style="background: #F8D7DA; color: #721C24; border-color: #F5C6CB; padding: 16px; margin-bottom: 20px; font-weight: 800;">
            ❌ {{ session('error') }}
        </div>
    @endif

    @if(!isset($generatedQuestions) || empty($generatedQuestions))
        <!-- STEP 1: UPLOAD PDF -->
        <div class="card">
            <form action="{{ route('quiz.generate') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="form-label">Quiz Title</label>
                <input type="text" name="quiz_title" class="form-input" required>

                <label class="form-label">Assign to Class</label>
                <select name="class_id" class="form-select" required>
                    <option value="">-- Choose a Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name ?? $class->class_name }}</option>
                    @endforeach
                </select>

                <label class="form-label">Upload Module (PDF)</label>
                <input type="file" name="module_pdf" accept="application/pdf" class="form-input" required style="padding-top: 10px;">

                <div class="date-grid">
                    <div>
                        <label class="form-label">Number of Questions</label>
                        <input type="number" name="question_count" class="form-input" min="1" max="50" value="10" required>
                    </div>
                    <div>
                        <label class="form-label">Question Type</label>
                        <select name="question_type" class="form-select" required>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True or False</option>
                            <option value="identification">Identification</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Generate Questions ✨</button>
            </form>
        </div>
    @else
        <!-- STEP 2: REVIEW & PUBLISH -->
        <form action="{{ route('quiz.store') }}" method="POST">
            @csrf
            <input type="hidden" name="class_id" value="{{ $class_id }}">
            <input type="hidden" name="quiz_title" value="{{ $quiz_title }}">

            <div class="card">
                <h3 style="font-weight: 900; margin-bottom: 16px;">Publish Settings</h3>
                <div class="date-grid">
                    <div>
                        <label class="form-label">Publish Date</label>
                        <input type="datetime-local" name="published_at" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Deadline (Closes access)</label>
                        <input type="datetime-local" name="deadline_at" class="form-input" required>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3 style="font-weight: 900; margin-bottom: 16px;">Review & Edit Questions</h3>
                @foreach($generatedQuestions as $index => $q)
                    <div class="question-box">
                        <label class="form-label">Question {{ $index + 1 }}</label>
                        <input type="text" name="questions[{{ $index }}][text]" class="form-input" value="{{ $q['question'] }}">
                        
                        <label class="form-label">Correct Answer</label>
                        <input type="text" name="questions[{{ $index }}][answer]" class="form-input" value="{{ $q['answer'] }}" style="border-color:#4ECDC4;">
                    </div>
                @endforeach

                <button type="submit" class="btn-teal" style="width: 100%;">Publish Quiz Now 🚀</button>
            </div>
        </form>
    @endif
</main>
</body>
</html>