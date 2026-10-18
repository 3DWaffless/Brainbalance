@extends('layouts.app')

@section('title', 'Generate AI Quiz - BrainBalance')

@section('content')
<div class="topbar">
    <div>
        <div class="tgreet">Generate AI Quiz ✨</div>
        <div class="tsub">Upload a PDF module and let Gemini automatically create a multiple-choice quiz.</div>
    </div>
    <a href="{{ route('quiz.index') }}" class="btn-ghost">&larr; Back to Workspace</a>
</div>

<div class="dashboard-grid">
    <div class="col-8">
        <div class="card">
            <form action="{{ route('quiz.generate') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-field">
                    <label class="form-label">Quiz Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. Chapter 1: Networking Basics" required>
                </div>

                <div class="form-field">
                    <label class="form-label">Subject Context</label>
                    <input type="text" name="subject" class="form-input" placeholder="e.g. Information Technology, Biology" required>
                </div>

                <div class="form-field">
                    <label class="form-label">Upload Module (PDF only)</label>
                    <div style="border: 2px dashed #FFB347; padding: 2rem; border-radius: 12px; text-align: center; background: #FFF8F0; transition: background 0.2s;">
                        <input type="file" name="module_pdf" accept="application/pdf" required style="width: 100%; cursor: pointer; font-family: 'Nunito', sans-serif; font-size: 14px; font-weight: 700; color: #2D2D2D;">
                        <div style="font-size: 12px; color: #FF6B6B; margin-top: 10px; font-weight: 800;">
                            Max file size: 10MB. Generation may take 1-3 minutes.
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 12px; flex-wrap: wrap;">
                    <div class="form-field" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Publish Date</label>
                        <input type="datetime-local" name="published_at" class="form-input" required>
                    </div>
                    <div class="form-field" style="flex: 1; min-width: 200px;">
                        <label class="form-label">Deadline</label>
                        <input type="datetime-local" name="deadline_at" class="form-input" required>
                    </div>
                </div>

                <div style="text-align: right; border-top: 2px solid #FFE4C4; padding-top: 16px; margin-top: 8px;">
                    <button type="submit" class="btn-primary" onclick="this.innerHTML='Generating... Please wait ⏳'; this.style.opacity='0.7';">
                        ✨ Generate Questions
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Info Panel -->
    <div class="col-4">
        <div class="card" style="background: linear-gradient(135deg, #FF6B6B, #FFB347); color: #fff; border: none;">
            <div style="font-size: 18px; font-weight: 900; margin-bottom: 12px;">How it works 🤖</div>
            <div style="font-size: 13px; font-weight: 700; line-height: 1.6; opacity: 0.95;">
                <p style="margin-bottom: 12px;">1. <strong>Upload</strong> your lesson PDF module.</p>
                <p style="margin-bottom: 12px;">2. <strong>Gemini AI</strong> bypasses local extractors and reads the raw PDF directly.</p>
                <p style="margin-bottom: 12px;">3. The AI natively crafts multiple-choice questions based on your subject context.</p>
                <p>4. <strong>Publish</strong> the quiz to your students instantly!</p>
            </div>
        </div>
    </div>
</div>
@endsection