{{-- FILE: resources/views/quiz/results.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quiz Results — {{ config('app.name', 'BrainBalance') }}</title>
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
            padding: 2.5rem 1.5rem;
        }

        .wrap { max-width: 900px; margin: 0 auto; }

        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 800; color: #aaa;
            text-decoration: none; margin-bottom: 1.25rem;
        }
        .back-link:hover { color: #FF6B6B; }

        h1 { font-size: 22px; font-weight: 900; color: #2D2D2D; margin-bottom: 4px; }
        .sub { font-size: 13px; font-weight: 700; color: #aaa; margin-bottom: 1.75rem; }

        .card {
            background: #fff;
            border-radius: 18px;
            border: 2px solid #FFE4C4;
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            text-align: left;
            font-size: 11px; font-weight: 900; color: #aaa;
            text-transform: uppercase; letter-spacing: 0.5px;
            padding: 14px 16px;
            background: #FFF8F0;
            border-bottom: 2px solid #FFE4C4;
        }

        tbody td {
            padding: 14px 16px;
            font-size: 13px; font-weight: 700; color: #2D2D2D;
            border-bottom: 1px solid #FFE4C4;
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #FFF8F0; }

        .subject-tag {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 11px; font-weight: 900; padding: 3px 10px;
            border-radius: 999px;
        }
        .subject-math { background: #FFE4E1; color: #FF6B6B; }
        .subject-english { background: #DFF7F5; color: #4ECDC4; }

        .level-pill {
            font-size: 11px; font-weight: 900; padding: 3px 10px;
            border-radius: 999px; background: #FFE4C4; color: #2D2D2D;
            text-transform: capitalize;
        }

        .accuracy-bar-track {
            width: 80px; height: 6px; background: #FFE4C4;
            border-radius: 999px; overflow: hidden;
        }
        .accuracy-bar-fill {
            height: 100%; border-radius: 999px;
            background: linear-gradient(90deg, #FF6B6B, #4ECDC4);
        }

        .empty-state { text-align: center; padding: 3rem 1rem; }
        .empty-emoji { font-size: 44px; margin-bottom: 10px; }
        .empty-title { font-size: 15px; font-weight: 900; color: #2D2D2D; margin-bottom: 4px; }
        .empty-sub { font-size: 13px; font-weight: 700; color: #aaa; }
    </style>
</head>
<body>

<div class="wrap">
    <a href="{{ route('dashboard') }}" class="back-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to dashboard
    </a>

    <h1>Quiz Results 📊</h1>
    <p class="sub">Every completed quiz attempt across all students</p>

    <div class="card">
        @if($sessions->isEmpty())
            <div class="empty-state">
                <div class="empty-emoji">📭</div>
                <div class="empty-title">No quiz attempts yet</div>
                <div class="empty-sub">Results will appear here once students complete a quiz.</div>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Score</th>
                        <th>Accuracy</th>
                        <th>Final level</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                        <tr>
                            @php($subject = strtolower($session->quiz->topic->subject ?? ''))
                            <td>{{ $session->student->first_name }} {{ $session->student->last_name }}</td>
                            <td>
                                <span class="subject-tag {{ $subject === 'math' ? 'subject-math' : 'subject-english' }}">
                                    {{ $subject === 'math' ? '🔢 Math' : '📖 English' }}
                                </span>
                            </td>
                            <td>{{ $session->score }}/{{ $session->total_questions }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div class="accuracy-bar-track">
                                        <div class="accuracy-bar-fill" style="width:{{ $session->accuracy }}%"></div>
                                    </div>
                                    <span>{{ $session->accuracy }}%</span>
                                </div>
                            </td>
                            <td><span class="level-pill">{{ $session->weakestTopic ? 'weak spot' : 'solid' }}</span></td>
                            <td>{{ $session->created_at?->format('M j, Y g:i A') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

</body>
</html>
