@extends('layouts.app')

@section('title', 'Select Class - ' . config('app.name', 'BrainBalance'))

@section('content')
<div class="topbar">
    <div>
        <div class="tgreet">Create Quiz 📝</div>
        <div class="tsub">Select the class you want to generate a quiz for</div>
    </div>
</div>

<div class="card" style="max-width: 720px;">
    @if($classes->isEmpty())
        <div class="empty-state" style="text-align: center; padding: 2rem;">
            <div style="font-size: 40px; margin-bottom: 8px;">🏫</div>
            <div style="font-weight: 900; font-size: 16px; color: #2D2D2D;">No Active Classes</div>
            <div style="font-size: 13px; color: #aaa; font-weight: 700; margin-top: 4px;">You need to create a class before generating quizzes.</div>
            <div style="margin-top: 16px;">
                <a href="{{ route('dashboard') }}" class="btn-primary">Return to Dashboard</a>
            </div>
        </div>
    @else
        <h3 style="font-size: 15px; font-weight: 900; color: #2D2D2D; margin-bottom: 14px;">Select Target Class:</h3>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($classes as $class)
                <a href="{{ route('quiz.create', ['class_id' => $class->id]) }}" 
                   style="text-decoration: none; display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border: 2px solid #FFE4C4; border-radius: 14px; background: #FFF8F0; transition: border-color 0.15s;">
                    <div>
                        <div style="font-weight: 900; font-size: 15px; color: #2D2D2D;">{{ $class->name }}</div>
                        <div style="font-size: 12px; color: #888; font-weight: 700;">Class Code: {{ $class->code }}</div>
                    </div>
                    <span class="btn-teal" style="font-size: 13px; font-weight: 800;">Select Class →</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection