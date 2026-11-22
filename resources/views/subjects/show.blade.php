{{-- FILE: resources/views/subjects/show.blade.php --}}
@extends('layouts.app')

@section('title', ($classroom->name ?? 'Classroom') . ' - Workspace')

@section('content')
@php
    $studentClassBadges = Auth::user()->isStudent()
        ? \App\Models\StudentBadge::with(['badge', 'quiz'])
            ->where('student_id', Auth::id())
            ->where('class_id', $classroom->id)
            ->latest()
            ->get()
        : collect();

    $unreadBadges = $studentClassBadges->where('is_read', false);

    $subjText = strtolower(($classroom->subject ?? '') . ' ' . ($classroom->name ?? ''));
    $isMath    = str_contains($subjText, 'math');
    $isEnglish = str_contains($subjText, 'eng') || str_contains($subjText, 'read');
    $subjectIcon = $isMath ? '🔢' : ($isEnglish ? '📖' : '🏫');
    $heroGradient = $isMath
        ? 'linear-gradient(135deg, #DBEAFE 0%, #FFFFFF 60%, #E0F2FE 100%)'
        : ($isEnglish
            ? 'linear-gradient(135deg, #DCFCE7 0%, #FFFFFF 60%, #FEF9C3 100%)'
            : 'linear-gradient(135deg, #FEF3C7 0%, #FFFFFF 60%, #EEF6FF 100%)');

    // Legacy modules not yet linked to a unified stream post
    $linkedModuleIds = $classroom->announcements->pluck('module_id')->filter()->all();
    $legacyModules = isset($classroom->modules)
        ? $classroom->modules->whereNotIn('id', $linkedModuleIds)
        : collect();
@endphp

<!-- NEW BADGE UNLOCKED NOTIFICATION BANNER -->
@if($unreadBadges->isNotEmpty())
    <div id="badge-notification-banner" class="mb-6 bg-gradient-to-r from-amber-100 via-yellow-50 to-amber-100 border-4 border-amber-400 rounded-3xl p-4 sm:p-5 shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-14 h-14 rounded-2xl bg-white border-3 border-amber-400 flex items-center justify-center shrink-0 shadow-xs">
                <img src="{{ $unreadBadges->first()->badge->image_url }}"
                     alt="{{ $unreadBadges->first()->badge->name }}"
                     style="width: 44px; height: 44px;"
                     class="w-11 h-11 object-contain drop-shadow-sm">
            </div>
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-400 text-amber-950 font-black text-[10px] uppercase tracking-wider mb-1">
                    🎉 New Badge Unlocked!
                </div>
                <h3 class="font-display font-bold text-base sm:text-lg text-brand-text">
                    You earned {{ $unreadBadges->pluck('badge.name')->unique()->join(', ') }}!
                </h3>
                <p class="text-xs font-bold text-slate-600">
                    {{ $unreadBadges->first()->badge->description }} ({{ $unreadBadges->pluck('quiz.title')->filter()->unique()->join(', ') }})
                </p>
            </div>
        </div>
        <button type="button" onclick="dismissBadgeNotification()" class="btn-tactile-primary text-xs py-2 px-4 shrink-0 cursor-pointer">
            Awesome! ✓
        </button>
    </div>

    <script>
        function dismissBadgeNotification() {
            document.getElementById('badge-notification-banner')?.remove();
            fetch("{{ route('badges.markRead') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
        }
    </script>
@endif

<!-- Header Navigation & Code Display -->
<div class="mb-6">
    <a href="{{ Auth::user()->role === 'admin' ? route('admin.subjects.index') : route('subjects.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-white border-2 border-brand-border text-brand-coral font-black text-xs mb-3 hover:-translate-x-1 transition-transform shadow-2xs">
        &larr; Back to Subjects
    </a>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 p-6 rounded-3xl border-4 border-brand-border shadow-tactile-ghost"
         style="background: {{ $heroGradient }};">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white border-3 border-brand-border flex items-center justify-center text-3xl sm:text-4xl shrink-0 shadow-xs">
                {{ $subjectIcon }}
            </div>
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white border border-sky-200 text-[10px] font-black uppercase tracking-wider text-brand-coral mb-1">
                    {{ ucfirst($classroom->subject ?? 'General') }} &bull; Section {{ $classroom->section ?? 'N/A' }}
                </div>
                <h1 class="font-display text-2xl md:text-3xl font-bold text-brand-text leading-tight">
                    {{ $classroom->name }}
                </h1>
                @if($classroom->teacher)
                    <p class="text-xs font-extrabold text-slate-500 mt-1">
                        🧑‍🏫 Teacher: <span class="text-brand-text">{{ $classroom->teacher->first_name }} {{ $classroom->teacher->last_name }}</span>
                    </p>
                @endif
            </div>
        </div>

        @if($classroom->class_code || $classroom->code)
            <div class="flex items-center gap-3 bg-white border-3 border-purple-200 px-4 py-2.5 rounded-2xl self-start sm:self-auto shadow-2xs">
                <div>
                    <div class="text-[10px] font-black text-purple-400 uppercase tracking-widest">Class Code</div>
                    <div id="classCodeText" class="text-lg font-black text-brand-purple tracking-widest">{{ $classroom->class_code ?? $classroom->code }}</div>
                </div>
                <button type="button" onclick="copyClassCode()" class="btn-tactile-ghost text-xs py-1.5 px-2.5 cursor-pointer" title="Copy Class Code">
                    📋 Copy
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Main 2-Column Responsive Workspace Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Left Main Column (8 Cols on Desktop): UNIFIED CLASS STREAM & COMPOSER -->
    <div class="lg:col-span-8 flex flex-col gap-6">

        <div class="card-tactile">
            <div class="flex flex-wrap justify-between items-center gap-2 mb-5 pb-3 border-b-4 border-dashed border-brand-border">
                <h3 class="font-display text-lg font-bold text-brand-text flex items-center gap-2">
                    <span>📣 Class Stream &amp; Materials</span>
                </h3>
                <span class="px-3 py-1 bg-sky-100 border border-sky-200 text-brand-coral font-black text-xs rounded-full">
                    {{ $classroom->announcements->count() + $legacyModules->count() }} Posts
                </span>
            </div>

            @if(Auth::user()->isTeacher() || Auth::user()->role === 'admin')
                <!-- UNIFIED QUIZ-MAKER STYLE COMPOSER -->
                <form action="{{ route('announcements.store', $classroom->id) }}" method="POST" enctype="multipart/form-data"
                      class="mb-6 bg-[#F2FAFF] p-4 sm:p-5 rounded-3xl border-3 border-[#B8E2F8] shadow-[0_5px_0_#D4F1FC]">
                    @csrf
                    <input type="hidden" name="type" id="postTypeInput" value="{{ old('type', 'text') }}">

                    <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                        <span class="text-xs font-black uppercase tracking-wider text-[#0066D6]">
                            ✨ Choose What to Post to Your Class
                        </span>
                        <a href="{{ route('quiz.create', ['class_id' => $classroom->id]) }}" class="text-xs font-black text-[#D96B00] hover:underline">
                            ➕ Build a New Quiz First &rarr;
                        </a>
                    </div>

                    <!-- Type Selector Pills -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mb-4">
                        <button type="button" onclick="selectPostType('text')" data-type-btn="text"
                                class="post-type-pill flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-2xl border-2 font-black text-xs cursor-pointer transition-all">
                            <span>💬</span> Plain Text
                        </button>
                        <button type="button" onclick="selectPostType('file')" data-type-btn="file"
                                class="post-type-pill flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-2xl border-2 font-black text-xs cursor-pointer transition-all">
                            <span>📄</span> File
                        </button>
                        <button type="button" onclick="selectPostType('video')" data-type-btn="video"
                                class="post-type-pill flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-2xl border-2 font-black text-xs cursor-pointer transition-all">
                            <span>🎬</span> Video
                        </button>
                        <button type="button" onclick="selectPostType('quiz')" data-type-btn="quiz"
                                class="post-type-pill flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-2xl border-2 font-black text-xs cursor-pointer transition-all">
                            <span>🧩</span> Quiz
                        </button>
                        <button type="button" onclick="selectPostType('assignment')" data-type-btn="assignment"
                                class="post-type-pill col-span-2 sm:col-span-1 flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-2xl border-2 font-black text-xs cursor-pointer transition-all">
                            <span>📝</span> Assignment
                        </button>
                    </div>

                    @if($errors->any())
                        <div class="mb-3 p-3 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-700 text-xs font-black">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- Dynamic Title Field (Shown for File, Video, Assignment, optional for Text/Quiz) -->
                    <div id="field-title-wrap" class="mb-3">
                        <label id="field-title-label" class="block text-[11px] font-black text-slate-500 uppercase mb-1">Title (Optional)</label>
                        <input type="text" name="title" id="field-title" value="{{ old('title') }}"
                               placeholder="Give your post a friendly title..."
                               class="w-full px-3.5 py-2.5 rounded-xl border-2 border-[#B8E2F8] bg-white font-extrabold text-sm text-brand-text focus:outline-none focus:border-[#0066D6]">
                    </div>

                    <!-- Quiz Picker (Shown when type == quiz) -->
                    <div id="field-quiz-wrap" class="mb-3 hidden">
                        <label class="block text-[11px] font-black text-slate-500 uppercase mb-1">Select Class Quiz to Post</label>
                        @if(isset($classroom->quizzes) && $classroom->quizzes->count() > 0)
                            <select name="quiz_id" id="field-quiz" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-[#B8E2F8] bg-white font-extrabold text-sm text-brand-text focus:outline-none focus:border-[#0066D6]">
                                <option value="">-- Choose a Quiz --</option>
                                @foreach($classroom->quizzes as $qOption)
                                    <option value="{{ $qOption->id }}" {{ old('quiz_id') == $qOption->id ? 'selected' : '' }}>
                                        {{ $qOption->title }} ({{ $qOption->questions->count() }} Qs){{ !$qOption->isPublished() ? ' [Draft - will publish]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="p-3 rounded-xl bg-amber-50 border-2 border-amber-200 text-xs font-extrabold text-amber-800 flex items-center justify-between gap-2">
                                <span>No quizzes in this class yet.</span>
                                <a href="{{ route('quiz.create', ['class_id' => $classroom->id]) }}" class="btn-tactile-primary text-xs py-1.5 px-3">Create Quiz</a>
                            </div>
                        @endif
                    </div>

                    <!-- File Upload Box (Shown for File and optional for Assignment) -->
                    <div id="field-file-wrap" class="mb-3 hidden">
                        <label id="field-file-label" class="block text-[11px] font-black text-slate-500 uppercase mb-1">Attach File (PDF, Doc, Photo, Audio)</label>
                        <input type="file" name="file" id="field-file"
                               class="w-full p-2 rounded-xl border-2 border-dashed border-[#93C5FD] bg-white text-xs font-bold text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-[#EBF5FF] file:text-[#0066D6] hover:file:bg-[#DBEAFE] cursor-pointer">
                    </div>

                    <!-- Video Upload or URL (Shown when type == video) -->
                    <div id="field-video-wrap" class="mb-3 hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-black text-slate-500 uppercase mb-1">Upload Video File (MP4, WebM, MOV)</label>
                                <input type="file" name="video_file" id="field-video-file" accept="video/*"
                                       class="w-full p-2 rounded-xl border-2 border-dashed border-[#93C5FD] bg-white text-xs font-bold text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-[#ECFEFF] file:text-[#0E7490] cursor-pointer">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black text-slate-500 uppercase mb-1">Or Paste YouTube / Video Link</label>
                                <input type="url" name="video_url" id="field-video-url" value="{{ old('video_url') }}"
                                       placeholder="https://www.youtube.com/watch?v=..."
                                       class="w-full px-3.5 py-2.5 rounded-xl border-2 border-[#B8E2F8] bg-white font-extrabold text-xs text-brand-text focus:outline-none focus:border-[#0066D6]">
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Due Date & Points (Shown when type == assignment) -->
                    <div id="field-assignment-wrap" class="mb-3 hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-black text-[#C2410C] uppercase mb-1">📅 Due Date &amp; Time *</label>
                                <input type="datetime-local" name="due_at" id="field-due-at" value="{{ old('due_at') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border-2 border-[#FDBA74] bg-white font-extrabold text-sm text-brand-text focus:outline-none focus:border-[#FF8400]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black text-[#0066D6] uppercase mb-1">⭐ Max Points</label>
                                <input type="number" name="points" id="field-points" value="{{ old('points', 100) }}" min="1" max="1000"
                                       class="w-full px-3.5 py-2.5 rounded-xl border-2 border-[#B8E2F8] bg-white font-extrabold text-sm text-brand-text focus:outline-none focus:border-[#0066D6]">
                            </div>
                        </div>
                    </div>

                    <!-- Message / Instructions Textarea -->
                    <div class="mb-3">
                        <label id="field-content-label" class="block text-[11px] font-black text-slate-500 uppercase mb-1">Announcement Message *</label>
                        <textarea name="content" id="field-content" rows="3"
                                  placeholder="Post a friendly update for your students..."
                                  class="w-full p-3.5 rounded-xl border-2 border-[#B8E2F8] bg-white font-extrabold text-sm text-brand-text focus:outline-none focus:border-[#0066D6] resize-none">{{ old('content') }}</textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" id="composerSubmitBtn" class="btn-tactile-primary">
                            📣 Post Announcement
                        </button>
                    </div>
                </form>
            @endif

            <!-- Stream Type Filter Pills -->
            <div class="flex flex-wrap items-center gap-2 mb-5">
                <button type="button" onclick="filterStream('all', this)" class="stream-filter-btn px-3 py-1.5 rounded-full bg-[#0066D6] text-white font-black text-xs cursor-pointer">
                    All Posts
                </button>
                <button type="button" onclick="filterStream('text', this)" class="stream-filter-btn px-3 py-1.5 rounded-full bg-slate-100 text-slate-600 font-black text-xs hover:bg-sky-50 cursor-pointer">
                    💬 Announcements
                </button>
                <button type="button" onclick="filterStream('file', this)" class="stream-filter-btn px-3 py-1.5 rounded-full bg-slate-100 text-slate-600 font-black text-xs hover:bg-sky-50 cursor-pointer">
                    📄 Files
                </button>
                <button type="button" onclick="filterStream('video', this)" class="stream-filter-btn px-3 py-1.5 rounded-full bg-slate-100 text-slate-600 font-black text-xs hover:bg-sky-50 cursor-pointer">
                    🎬 Videos
                </button>
                <button type="button" onclick="filterStream('quiz', this)" class="stream-filter-btn px-3 py-1.5 rounded-full bg-slate-100 text-slate-600 font-black text-xs hover:bg-sky-50 cursor-pointer">
                    🧩 Quizzes
                </button>
                <button type="button" onclick="filterStream('assignment', this)" class="stream-filter-btn px-3 py-1.5 rounded-full bg-slate-100 text-slate-600 font-black text-xs hover:bg-sky-50 cursor-pointer">
                    📝 Assignments
                </button>
            </div>

            <!-- UNIFIED CLASS STREAM FEED -->
            @if($classroom->announcements->count() > 0 || $legacyModules->count() > 0)
                <div class="flex flex-col gap-4" id="unifiedStreamFeed">
                    @foreach($classroom->announcements->sortByDesc('created_at') as $post)
                        @php
                            $postType = $post->type ?? 'text';
                            $typeBadge = match($postType) {
                                'file'       => ['icon' => '📄', 'label' => 'File Material', 'bg' => 'bg-sky-100 text-[#0066D6] border-sky-200'],
                                'video'      => ['icon' => '🎬', 'label' => 'Video Lesson', 'bg' => 'bg-cyan-100 text-[#0E7490] border-cyan-200'],
                                'quiz'       => ['icon' => '🧩', 'label' => 'Quiz Quest', 'bg' => 'bg-lime-100 text-[#3E8708] border-lime-300'],
                                'assignment' => ['icon' => '📝', 'label' => 'Assignment', 'bg' => 'bg-amber-100 text-[#C2410C] border-amber-300'],
                                default      => ['icon' => '💬', 'label' => 'Announcement', 'bg' => 'bg-slate-100 text-slate-700 border-slate-200'],
                            };
                            $ext = strtolower($post->file_type ?? pathinfo((string) $post->file_name, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']);
                            $isVideoFile = in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv']);
                            $isAudio = in_array($ext, ['mp3', 'wav', 'm4a', 'aac', 'flac']);
                            $isPdf = $ext === 'pdf';
                        @endphp

                        <div class="stream-post-card bg-brand-bg border-3 {{ $postType === 'assignment' ? 'border-[#FDBA74]' : 'border-brand-border' }} rounded-2xl p-4 md:p-5"
                             data-post-type="{{ $postType }}">
                            <!-- Post Header -->
                            <div class="flex justify-between items-start gap-3 mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-brand-coral text-white font-black flex items-center justify-center text-sm border-2 border-brand-border shadow-xs shrink-0">
                                        {{ strtoupper(substr($post->teacher->first_name ?? 'T', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center flex-wrap gap-2">
                                            <span class="font-black text-sm text-brand-text leading-tight">
                                                {{ $post->teacher->first_name ?? 'Teacher' }} {{ $post->teacher->last_name ?? '' }}
                                            </span>
                                            <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-black uppercase {{ $typeBadge['bg'] }}">
                                                {{ $typeBadge['icon'] }} {{ $typeBadge['label'] }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] font-bold text-slate-400 mt-0.5">
                                            {{ $post->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>

                                @if(Auth::user()->isTeacher() || Auth::user()->role === 'admin')
                                    <form action="{{ route('announcements.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Delete this post?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-black text-rose-500 hover:bg-rose-50 px-3 py-1.5 rounded-xl border border-rose-200 transition-colors cursor-pointer">🗑️ Delete</button>
                                    </form>
                                @endif
                            </div>

                            <!-- Post Title -->
                            @if($post->title)
                                <h4 class="font-display font-bold text-base md:text-lg text-brand-text mb-1.5">
                                    {{ $post->title }}
                                </h4>
                            @endif

                            <!-- Post Message / Instructions -->
                            @if(!empty($post->content))
                                <p class="font-bold text-slate-700 text-sm md:text-base leading-relaxed whitespace-pre-line mb-3">
                                    {{ $post->content }}
                                </p>
                            @endif

                            <!-- TYPE: FILE MATERIAL -->
                            @if($postType === 'file' && $post->file_path)
                                <div class="bg-white border-2 border-[#B8E2F8] rounded-2xl p-3.5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl bg-sky-50 border-2 border-sky-200 flex items-center justify-center text-xl shrink-0">
                                            {{ $isImage ? '🖼️' : ($isAudio ? '🎧' : ($isPdf ? '📕' : '📄')) }}
                                        </div>
                                        <div>
                                            <div class="font-black text-xs sm:text-sm text-brand-text">{{ $post->file_name }}</div>
                                            <div class="text-[11px] font-bold text-slate-400">
                                                {{ strtoupper($ext ?: 'FILE') }} @if($post->formatted_size) &bull; {{ $post->formatted_size }} @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-end">
                                        @if($isImage)
                                            <button type="button" onclick="openMediaModal('image', '{{ route('announcements.view', $post->id) }}', '{{ addslashes($post->title ?? $post->file_name) }}')" class="btn-tactile-teal text-xs py-1.5 px-3">
                                                🖼️ View Photo
                                            </button>
                                        @elseif($isAudio)
                                            <button type="button" onclick="openMediaModal('audio', '{{ route('announcements.view', $post->id) }}', '{{ addslashes($post->title ?? $post->file_name) }}')" class="btn-tactile-teal text-xs py-1.5 px-3">
                                                🎧 Listen
                                            </button>
                                        @elseif($isPdf && $post->module_id)
                                            <a href="{{ route('modules.read', $post->module_id) }}" class="btn-tactile-teal text-xs py-1.5 px-3">
                                                📕 Read Lesson
                                            </a>
                                        @endif
                                        <a href="{{ route('announcements.download', $post->id) }}" class="btn-tactile-ghost text-xs py-1.5 px-3">
                                            ⬇️ Download
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- TYPE: VIDEO LESSON -->
                            @if($postType === 'video')
                                @if($post->file_path && $isVideoFile)
                                    <div class="bg-black rounded-2xl overflow-hidden border-2 border-[#99E2F5] mb-2">
                                        <video src="{{ route('announcements.view', $post->id) }}" controls class="w-full max-h-[380px]"></video>
                                    </div>
                                    <div class="flex justify-end">
                                        <a href="{{ route('announcements.download', $post->id) }}" class="btn-tactile-ghost text-xs py-1.5 px-3">
                                            ⬇️ Download Video
                                        </a>
                                    </div>
                                @elseif($post->embed_video_url)
                                    <div class="relative w-full overflow-hidden rounded-2xl border-2 border-[#99E2F5] bg-black" style="padding-top: 56.25%;">
                                        <iframe src="{{ $post->embed_video_url }}" class="absolute inset-0 w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                @elseif($post->video_url)
                                    <a href="{{ $post->video_url }}" target="_blank" rel="noopener" class="btn-tactile-teal text-xs py-2 px-4 inline-flex items-center gap-2">
                                        🎬 Watch Video Link &nearr;
                                    </a>
                                @endif
                            @endif

                            <!-- TYPE: QUIZ QUEST -->
                            @if($postType === 'quiz' && $post->quiz)
                                @php
                                    $linkedQuiz = $post->quiz;
                                    $qAttempts = Auth::user()->isStudent()
                                        ? \App\Models\QuizAttempt::where('quiz_id', $linkedQuiz->id)->where('student_id', Auth::id())->count()
                                        : 0;
                                    $qClosed = Auth::user()->isStudent() && $linkedQuiz->max_attempts && $qAttempts >= $linkedQuiz->max_attempts;
                                @endphp
                                <div class="bg-white border-2 border-[#A3E635] rounded-2xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                                    <div>
                                        <div class="font-black text-sm text-brand-text">{{ $linkedQuiz->title }}</div>
                                        <div class="text-xs font-bold text-slate-500 mt-0.5">
                                            ❓ {{ $linkedQuiz->questions->count() }} Questions &bull; Max Tries: {{ $linkedQuiz->max_attempts ?? 'Unlimited' }}
                                        </div>
                                    </div>
                                    <div>
                                        @if(Auth::user()->isStudent())
                                            @if($qClosed)
                                                <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-500 font-black text-xs">🔒 Completed</span>
                                            @else
                                                <a href="{{ route('quiz.show', $linkedQuiz->id) }}" class="btn-tactile-teal text-xs py-2 px-4">
                                                    🚀 Play Quiz Quest
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('quiz.show', $linkedQuiz->id) }}" class="btn-tactile-primary text-xs py-2 px-4">
                                                View Quiz &rarr;
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- TYPE: ASSIGNMENT -->
                            @if($postType === 'assignment')
                                @php
                                    $dueInfo = $post->due_status;
                                    $duePillClass = match($dueInfo['state']) {
                                        'overdue'  => 'bg-rose-100 text-rose-800 border-rose-300',
                                        'urgent'   => 'bg-amber-100 text-amber-900 border-amber-400',
                                        'upcoming' => 'bg-sky-100 text-[#0066D6] border-sky-300',
                                        default    => 'bg-slate-100 text-slate-600 border-slate-200',
                                    };
                                    $mySubmission = Auth::user()->isStudent()
                                        ? $post->submissions->firstWhere('student_id', Auth::id())
                                        : null;
                                @endphp

                                <div class="bg-white border-2 border-[#FDBA74] rounded-2xl p-4 space-y-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if($post->due_at)
                                                <span class="px-3 py-1 rounded-full border text-xs font-black {{ $duePillClass }}">
                                                    ⏳ {{ $dueInfo['label'] }}
                                                </span>
                                                <span class="text-xs font-extrabold text-slate-500">
                                                    Due {{ $post->due_at->format('M d, Y • h:i A') }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#EBF5FF] border border-[#93C5FD] text-[#0066D6] font-black text-xs">
                                            ⭐ {{ $post->points ?? 100 }} pts
                                        </span>
                                    </div>

                                    @if($post->file_path)
                                        <div>
                                            <a href="{{ route('announcements.download', $post->id) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-sky-50 border border-sky-200 text-[#0066D6] font-black text-xs hover:bg-sky-100">
                                                📎 Download Worksheet: {{ $post->file_name }}
                                            </a>
                                        </div>
                                    @endif

                                    @if(Auth::user()->isStudent())
                                        <div class="pt-2 border-t-2 border-dashed border-amber-200 flex flex-wrap items-center justify-between gap-2">
                                            @if($mySubmission)
                                                <span class="px-3 py-1 rounded-full bg-lime-100 border border-lime-300 text-lime-800 font-black text-xs">
                                                    ✅ Turned In ({{ $mySubmission->submitted_at?->format('M d, h:i A') }})
                                                    @if($mySubmission->score !== null)
                                                        &bull; Score: {{ $mySubmission->score }}/{{ $post->points ?? 100 }}
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-xs font-extrabold text-amber-700">Not turned in yet</span>
                                            @endif

                                            <a href="{{ route('assignments.index') }}" class="btn-tactile-primary text-xs py-1.5 px-3.5">
                                                {{ $mySubmission ? '✏️ View / Edit Submission' : '📤 Turn In Assignment' }}
                                            </a>
                                        </div>
                                    @else
                                        <!-- Teacher Submissions Drawer Toggle -->
                                        <div class="pt-2 border-t-2 border-dashed border-amber-200">
                                            <button type="button" onclick="document.getElementById('subs-drawer-{{ $post->id }}').classList.toggle('hidden')"
                                                    class="text-xs font-black text-[#0066D6] hover:underline cursor-pointer">
                                                📥 Student Turn-Ins ({{ $post->submissions->count() }} / {{ $classroom->students->count() }}) &darr;
                                            </button>

                                            <div id="subs-drawer-{{ $post->id }}" class="hidden mt-3 space-y-2.5">
                                                @forelse($post->submissions as $sub)
                                                    <div class="p-3 rounded-xl bg-[#F4FAFF] border border-[#B8E2F8] text-xs space-y-2">
                                                        <div class="flex justify-between items-center font-black text-brand-text">
                                                            <span>{{ $sub->student->first_name ?? 'Student' }} {{ $sub->student->last_name ?? '' }}</span>
                                                            <span class="text-slate-400">{{ $sub->submitted_at?->format('M d, h:i A') }}</span>
                                                        </div>
                                                        @if($sub->answer_text)
                                                            <p class="font-bold text-slate-700 bg-white p-2 rounded-lg border border-slate-200 whitespace-pre-line">{{ $sub->answer_text }}</p>
                                                        @endif
                                                        @if($sub->file_path)
                                                            <a href="{{ route('assignments.submission.download', $sub->id) }}" class="inline-flex items-center gap-1 text-[#0066D6] font-black hover:underline">
                                                                📎 {{ $sub->file_name }}
                                                            </a>
                                                        @endif
                                                        <form action="{{ route('assignments.grade', $sub->id) }}" method="POST" class="flex flex-wrap items-center gap-2 pt-1">
                                                            @csrf
                                                            <input type="number" name="score" value="{{ $sub->score }}" min="0" max="{{ $post->points ?? 100 }}" placeholder="Score"
                                                                   class="w-20 px-2 py-1 rounded-lg border border-sky-300 font-black text-xs" required>
                                                            <input type="text" name="teacher_feedback" value="{{ $sub->teacher_feedback }}" placeholder="Optional feedback..."
                                                                   class="flex-1 min-w-[140px] px-2.5 py-1 rounded-lg border border-sky-300 font-bold text-xs">
                                                            <button type="submit" class="px-3 py-1 rounded-lg bg-[#68CC18] text-white font-black text-xs cursor-pointer">Save Grade</button>
                                                        </form>
                                                    </div>
                                                @empty
                                                    <p class="text-xs font-bold text-slate-400">No student submissions yet.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <!-- Legacy Modules (uploaded before unified stream) -->
                    @foreach($legacyModules as $module)
                        @php
                            $ext = strtolower($module->file_type ?? pathinfo($module->file_name, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp']);
                            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv']);
                            $isAudio = in_array($ext, ['mp3', 'wav', 'm4a', 'aac', 'flac']);
                            $isPdf   = $ext === 'pdf';
                        @endphp
                        <div class="stream-post-card bg-brand-bg border-3 border-brand-border rounded-2xl p-4 md:p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3"
                             data-post-type="{{ $isVideo ? 'video' : 'file' }}">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-2xl bg-sky-50 border-3 border-sky-200 flex items-center justify-center text-2xl shrink-0">
                                    {{ $isImage ? '🖼️' : ($isVideo ? '🎬' : ($isAudio ? '🎧' : ($isPdf ? '📕' : '📄'))) }}
                                </div>
                                <div>
                                    <span class="px-2 py-0.5 rounded-full bg-sky-100 text-[#0066D6] font-black text-[10px] uppercase">📄 Class File</span>
                                    <h4 class="font-black text-base text-brand-text mt-0.5">{{ $module->title }}</h4>
                                    <span class="text-xs font-bold text-slate-400">{{ strtoupper($ext ?: 'FILE') }} &bull; {{ $module->formatted_size }}</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-end">
                                @if($isImage)
                                    <button type="button" onclick="openMediaModal('image', '{{ route('modules.view', $module->id) }}', '{{ addslashes($module->title) }}')" class="btn-tactile-teal text-xs py-2 px-3.5">🖼️ View Photo</button>
                                @elseif($isVideo)
                                    <button type="button" onclick="openMediaModal('video', '{{ route('modules.view', $module->id) }}', '{{ addslashes($module->title) }}')" class="btn-tactile-teal text-xs py-2 px-3.5">🎬 Watch Video</button>
                                @elseif($isAudio)
                                    <button type="button" onclick="openMediaModal('audio', '{{ route('modules.view', $module->id) }}', '{{ addslashes($module->title) }}')" class="btn-tactile-teal text-xs py-2 px-3.5">🎧 Listen</button>
                                @elseif($isPdf)
                                    <a href="{{ route('modules.read', $module->id) }}" class="btn-tactile-teal text-xs py-2 px-3.5">📕 Read Lesson</a>
                                @endif

                                <a href="{{ route('modules.download', $module->id) }}" class="btn-tactile-ghost text-xs py-2 px-3.5">⬇️ Download</a>

                                @if(Auth::user()->isTeacher() || Auth::user()->role === 'admin')
                                    <form action="{{ route('modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Delete this file?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 font-black p-2 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer" title="Delete File">🗑️</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10 bg-brand-bg/50 rounded-2xl border-2 border-dashed border-brand-border">
                    <div class="text-4xl mb-2">📭</div>
                    <p class="text-xs md:text-sm font-extrabold text-slate-400">No announcements, files, videos, or assignments posted yet.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column (4 Cols on Desktop): Treasure Chest, Quizzes & Student Roster -->
    <div class="lg:col-span-4 flex flex-col gap-6">

        <!-- 🧰 STUDENT MEDAL TREASURE CHEST -->
        @include('partials.medal-box')

        <!-- CLASS QUIZZES -->
        <div class="card-tactile">
            <div class="flex justify-between items-center mb-4 pb-3 border-b-4 border-dashed border-brand-border">
                <h3 class="font-display text-lg font-bold text-brand-text">
                    {{ Auth::user()->isStudent() ? '⚔️ Quiz Quests' : '📝 Class Quizzes' }}
                </h3>
                @if(Auth::user()->isTeacher() || Auth::user()->role === 'admin')
                    <a href="{{ route('quiz.create', ['class_id' => $classroom->id]) }}" class="btn-tactile-primary text-xs py-1.5 px-3">
                        ➕ Create
                    </a>
                @endif
            </div>

            @if(isset($classroom->quizzes) && $classroom->quizzes->count() > 0)
                <div class="flex flex-col gap-3">
                    @foreach($classroom->quizzes as $quiz)
                        @php
                            $userAttempts = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', Auth::id())->count();
                            $latestAttempt = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', Auth::id())->latest()->first();
                            $isClosed = $quiz->max_attempts && $userAttempts >= $quiz->max_attempts;
                        @endphp
                        <div class="bg-brand-bg border-3 border-brand-border rounded-2xl p-4 flex flex-col gap-3 hover:border-sky-300 transition-colors">
                            <div>
                                <div class="flex justify-between items-start gap-2">
                                    <h5 class="font-black text-sm text-brand-text leading-snug">{{ $quiz->title }}</h5>
                                    @if(Auth::user()->isStudent() && $latestAttempt)
                                        <span class="px-2 py-0.5 rounded-full bg-lime-100 border border-lime-300 text-lime-800 font-black text-[10px] shrink-0">
                                            ✓ {{ $latestAttempt->score }}/{{ $latestAttempt->total_questions }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] font-bold text-slate-500 mt-1">
                                    ❓ {{ $quiz->questions->count() }} Questions &bull; Tries: {{ $userAttempts }}/{{ $quiz->max_attempts ?? '∞' }}
                                </p>
                            </div>
                            <div class="flex justify-end gap-2">
                                @if(Auth::user()->isTeacher() || Auth::user()->role === 'admin')
                                    <a href="{{ route('quiz.show', $quiz->id) }}" class="btn-tactile-primary text-xs py-1.5 px-3">View &rarr;</a>
                                @else
                                    @if($isClosed)
                                        @if($latestAttempt)
                                            <a href="{{ route('quiz.review', $latestAttempt->id) }}" class="btn-tactile-ghost text-xs py-1.5 px-3">📊 Review</a>
                                        @else
                                            <button disabled class="bg-gray-200 text-gray-400 font-extrabold text-xs px-3 py-1.5 rounded-xl cursor-not-allowed">🔒 Closed</button>
                                        @endif
                                    @else
                                        @if($latestAttempt)
                                            <a href="{{ route('quiz.review', $latestAttempt->id) }}" class="btn-tactile-ghost text-xs py-1.5 px-3">📊 Review</a>
                                        @endif
                                        <a href="{{ route('quiz.show', $quiz->id) }}" class="btn-tactile-teal text-xs py-1.5 px-3.5">Play Quest 🚀</a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs font-bold text-slate-400 text-center py-4">No quizzes created for this class yet.</p>
            @endif
        </div>

        <!-- ENROLLED STUDENTS ROSTER -->
        <div class="card-tactile">
            <div class="flex justify-between items-center mb-4 pb-3 border-b-4 border-dashed border-brand-border">
                <h3 class="font-display text-lg font-bold text-brand-text">👥 Class Explorers</h3>
                <span class="bg-brand-teal text-white font-black text-xs px-2.5 py-1 rounded-full">
                    {{ $classroom->students->count() }}
                </span>
            </div>

            @if($classroom->students->count() > 0)
                <div class="flex flex-col gap-2.5 max-h-80 overflow-y-auto pr-1">
                    @foreach($classroom->students->sortByDesc('total_xp') as $idx => $student)
                        <div class="flex items-center justify-between p-3 bg-brand-bg border-2 border-brand-border rounded-2xl">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-full bg-brand-coral text-white font-black text-xs flex items-center justify-center border-2 border-white shadow-2xs shrink-0">
                                    {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-black text-xs text-brand-text truncate">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </div>
                                    <div class="text-[10px] font-extrabold text-brand-coral">
                                        ⭐ Level {{ $student->level ?? 1 }} &bull; {{ number_format($student->total_xp ?? 0) }} XP
                                    </div>
                                </div>
                            </div>
                            @if($idx === 0 && ($student->total_xp ?? 0) > 0)
                                <span class="text-base shrink-0" title="Top XP Explorer">👑</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs font-bold text-slate-400 text-center py-4">No students enrolled yet.</p>
            @endif
        </div>
    </div>
</div>

<!-- KID-FRIENDLY MEDIA PLAYER MODAL -->
<div id="media-preview-modal" class="fixed inset-0 bg-black/70 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="card-tactile max-w-3xl w-full bg-white border-4 border-brand-coral shadow-2xl p-5 sm:p-6 relative">
        <div class="flex justify-between items-center mb-4 pb-3 border-b-4 border-dashed border-brand-border">
            <h3 id="media-modal-title" class="font-display font-bold text-base sm:text-lg text-brand-text truncate pr-4">🎬 Media Viewer</h3>
            <button type="button" onclick="closeMediaModal()" class="w-9 h-9 rounded-full bg-brand-coral text-white font-black flex items-center justify-center hover:scale-105 active:scale-95 transition-transform shrink-0 cursor-pointer">✕</button>
        </div>

        <div id="media-modal-body" class="bg-brand-bg border-3 border-brand-border rounded-2xl p-4 flex items-center justify-center min-h-[220px] max-h-[70vh] overflow-hidden"></div>
    </div>
</div>

<script>
    function selectPostType(type) {
        const input = document.getElementById('postTypeInput');
        if (input) input.value = type;

        document.querySelectorAll('.post-type-pill').forEach(btn => {
            const isActive = btn.dataset.typeBtn === type;
            btn.className = 'post-type-pill flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-2xl border-2 font-black text-xs cursor-pointer transition-all ' +
                (btn.dataset.typeBtn === 'assignment' ? 'col-span-2 sm:col-span-1 ' : '') +
                (isActive
                    ? 'bg-[#0066D6] text-white border-[#004FA8] shadow-[0_3px_0_#93C5FD]'
                    : 'bg-white text-slate-600 border-[#B8E2F8] hover:border-[#1DC4DF]');
        });

        const titleWrap      = document.getElementById('field-title-wrap');
        const titleLabel     = document.getElementById('field-title-label');
        const titleInput     = document.getElementById('field-title');
        const quizWrap       = document.getElementById('field-quiz-wrap');
        const fileWrap       = document.getElementById('field-file-wrap');
        const fileLabel      = document.getElementById('field-file-label');
        const fileInput      = document.getElementById('field-file');
        const videoWrap      = document.getElementById('field-video-wrap');
        const assignWrap     = document.getElementById('field-assignment-wrap');
        const dueInput       = document.getElementById('field-due-at');
        const contentLabel   = document.getElementById('field-content-label');
        const contentInput   = document.getElementById('field-content');
        const submitBtn      = document.getElementById('composerSubmitBtn');

        // Reset visibility
        quizWrap?.classList.add('hidden');
        fileWrap?.classList.add('hidden');
        videoWrap?.classList.add('hidden');
        assignWrap?.classList.add('hidden');
        if (titleInput) titleInput.required = false;
        if (fileInput) fileInput.required = false;
        if (dueInput) dueInput.required = false;
        if (contentInput) contentInput.required = false;

        if (type === 'text') {
            titleLabel.textContent = 'Headline (Optional)';
            titleInput.placeholder = 'e.g. Reminder for Friday!';
            contentLabel.textContent = 'Announcement Message *';
            contentInput.placeholder = 'Post a friendly update for your students...';
            contentInput.required = true;
            submitBtn.innerHTML = '📣 Post Announcement';
        } else if (type === 'file') {
            titleLabel.textContent = 'File / Lesson Title *';
            titleInput.placeholder = 'e.g. Lesson 1: Fractions Worksheet';
            titleInput.required = true;
            fileWrap.classList.remove('hidden');
            fileLabel.textContent = 'Choose File (PDF, Doc, Photo, Audio) *';
            fileInput.required = true;
            contentLabel.textContent = 'Notes / Description (Optional)';
            contentInput.placeholder = 'Add helpful instructions for this file...';
            submitBtn.innerHTML = '📄 Post File Material';
        } else if (type === 'video') {
            titleLabel.textContent = 'Video Lesson Title *';
            titleInput.placeholder = 'e.g. Phonics Song or Multiplication Trick';
            titleInput.required = true;
            videoWrap.classList.remove('hidden');
            contentLabel.textContent = 'Video Notes (Optional)';
            contentInput.placeholder = 'What should students watch for in this video?';
            submitBtn.innerHTML = '🎬 Post Video Lesson';
        } else if (type === 'quiz') {
            titleLabel.textContent = 'Custom Post Title (Optional - defaults to Quiz Title)';
            titleInput.placeholder = 'Leave blank to use the quiz title...';
            quizWrap.classList.remove('hidden');
            contentLabel.textContent = 'Instructions for Students (Optional)';
            contentInput.placeholder = 'e.g. Complete this quiz quest before Friday!';
            submitBtn.innerHTML = '🧩 Post Quiz Quest';
        } else if (type === 'assignment') {
            titleLabel.textContent = 'Assignment Title *';
            titleInput.placeholder = 'e.g. Reading Comprehension Essay';
            titleInput.required = true;
            assignWrap.classList.remove('hidden');
            dueInput.required = true;
            fileWrap.classList.remove('hidden');
            fileLabel.textContent = 'Attach Worksheet / Reference File (Optional)';
            contentLabel.textContent = 'Assignment Instructions *';
            contentInput.placeholder = 'Explain what students need to do and turn in...';
            contentInput.required = true;
            submitBtn.innerHTML = '📝 Assign to Students';
        }
    }

    function filterStream(type, clickedBtn) {
        document.querySelectorAll('.stream-filter-btn').forEach(btn => {
            btn.className = 'stream-filter-btn px-3 py-1.5 rounded-full bg-slate-100 text-slate-600 font-black text-xs hover:bg-sky-50 cursor-pointer';
        });
        clickedBtn.className = 'stream-filter-btn px-3 py-1.5 rounded-full bg-[#0066D6] text-white font-black text-xs cursor-pointer';

        document.querySelectorAll('.stream-post-card').forEach(card => {
            if (type === 'all' || card.dataset.postType === type) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const initialType = document.getElementById('postTypeInput')?.value || 'text';
        if (document.getElementById('postTypeInput')) {
            selectPostType(initialType);
        }
    });

    function copyClassCode() {
        const codeText = document.getElementById('classCodeText')?.innerText.trim();
        if (codeText) {
            navigator.clipboard.writeText(codeText).then(() => {
                if (window.showBrainBalanceToast) {
                    window.showBrainBalanceToast({
                        type: 'earned',
                        name: 'Copied!',
                        message: 'Class code ' + codeText + ' copied to clipboard! 📋'
                    }, 2200);
                } else {
                    alert('Class code copied to clipboard! 📋');
                }
            });
        }
    }

    function openMediaModal(type, url, title) {
        const modal = document.getElementById('media-preview-modal');
        const body = document.getElementById('media-modal-body');
        const heading = document.getElementById('media-modal-title');
        body.innerHTML = '';

        if (type === 'image') {
            heading.textContent = '🖼️ ' + title;
            const img = document.createElement('img');
            img.src = url;
            img.alt = title;
            img.className = 'max-h-[62vh] w-auto rounded-xl object-contain shadow-md';
            body.appendChild(img);
        } else if (type === 'video') {
            heading.textContent = '🎬 ' + title;
            const video = document.createElement('video');
            video.src = url;
            video.controls = true;
            video.autoplay = true;
            video.className = 'w-full max-h-[62vh] rounded-xl bg-black shadow-md';
            body.appendChild(video);
        } else if (type === 'audio') {
            heading.textContent = '🎧 ' + title;
            const wrapper = document.createElement('div');
            wrapper.className = 'w-full flex flex-col items-center gap-4 py-6';

            const emoji = document.createElement('div');
            emoji.className = 'text-6xl animate-bounce';
            emoji.textContent = '🎧';

            const label = document.createElement('p');
            label.className = 'font-black text-sm text-brand-text text-center';
            label.textContent = title;

            const audio = document.createElement('audio');
            audio.src = url;
            audio.controls = true;
            audio.autoplay = true;
            audio.className = 'w-full max-w-md';

            wrapper.append(emoji, label, audio);
            body.appendChild(wrapper);
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeMediaModal() {
        const modal = document.getElementById('media-preview-modal');
        const body = document.getElementById('media-modal-body');
        body.innerHTML = '';
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('media-preview-modal')?.addEventListener('click', (e) => {
        if (e.target.id === 'media-preview-modal') closeMediaModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMediaModal();
    });
</script>
@endsection