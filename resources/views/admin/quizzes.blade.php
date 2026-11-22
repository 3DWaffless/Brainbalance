{{-- FILE: resources/views/admin/quizzes.blade.php --}}
@extends('layouts.app')

@section('title', 'Quizzes Management - Admin')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-brand-text">All Quizzes</h1>
            <p class="text-sm font-bold text-gray-500 mt-1">Create, inspect, edit, or remove any quiz across all subjects.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('quiz.create') }}" class="px-4 py-2.5 rounded-2xl border-2 border-brand-border bg-white text-brand-text font-black text-xs hover:bg-brand-bg">
                AI Quiz Wizard
            </a>
            <button type="button" onclick="openCreateQuizModal()" class="btn-tactile-primary justify-center">
                + Quick Create Quiz
            </button>
        </div>
    </div>

    <div class="bg-white border-4 border-brand-border rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-bg border-b-4 border-brand-border text-xs font-black text-gray-500 uppercase tracking-wider">
                        <th class="py-4 px-5">Quiz Title</th>
                        <th class="py-4 px-5">Subject / Class</th>
                        <th class="py-4 px-5">Created</th>
                        <th class="py-4 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-brand-border text-sm font-bold">
                    @forelse($quizzes as $quiz)
                        <tr class="hover:bg-sky-50/40 transition-colors">
                            <td class="py-4 px-5 font-black text-brand-text">{{ $quiz->title }}</td>
                            <td class="py-4 px-5 text-gray-600">{{ $quiz->classroom->name ?? 'Unassigned' }}</td>
                            <td class="py-4 px-5 text-gray-400 text-xs">{{ $quiz->created_at?->format('M d, Y') }}</td>
                            <td class="py-4 px-5 text-right flex items-center justify-end gap-2">
                                <a href="{{ route('quiz.edit', $quiz) }}" class="px-3 py-1.5 rounded-xl border-2 border-brand-border bg-brand-bg text-brand-text font-black text-xs hover:bg-white">
                                    Questions
                                </a>
                                <button type="button"
                                    onclick="openEditQuizModal({{ $quiz->id }}, '{{ addslashes($quiz->title) }}', '{{ $quiz->class_id ?? $quiz->classroom_id }}', '{{ addslashes($quiz->description ?? '') }}')"
                                    class="px-3 py-1.5 rounded-xl border-2 border-brand-border bg-white text-brand-text font-black text-xs hover:bg-brand-bg cursor-pointer">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.quizzes.destroy', $quiz) }}" onsubmit="return confirm('Delete this quiz permanently?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-500 text-white font-black text-xs hover:bg-rose-600 cursor-pointer">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-400 font-bold">No quizzes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create / Edit Quiz Modal -->
<div id="quizModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white border-4 border-brand-border rounded-3xl max-w-md w-full p-6 shadow-xl">
        <h2 id="quizModalTitle" class="text-xl font-black text-brand-text mb-4">Create Quiz</h2>
        <form id="quizForm" method="POST" action="{{ route('admin.quizzes.store') }}" class="flex flex-col gap-3">
            @csrf
            <input type="hidden" name="_method" id="quizFormMethod" value="POST">

            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-1">Quiz Title</label>
                <input type="text" name="title" id="quizTitle" required placeholder="e.g., Addition & Subtraction Basics" class="w-full px-4 py-2.5 rounded-2xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
            </div>
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-1">Subject / Classroom</label>
                <select name="classroom_id" id="quizClassroom" required class="w-full px-4 py-2.5 rounded-2xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-1">Description</label>
                <textarea name="description" id="quizDescription" rows="3" class="w-full px-4 py-2.5 rounded-2xl border-2 border-brand-border bg-brand-bg font-bold text-sm"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-3">
                <button type="button" onclick="closeQuizModal()" class="px-4 py-2.5 rounded-2xl border-2 border-brand-border font-black text-xs text-gray-600 cursor-pointer">Cancel</button>
                <button type="submit" class="btn-tactile-primary text-xs">Save Quiz</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateQuizModal() {
        document.getElementById('quizModalTitle').innerText = 'Create Quiz';
        document.getElementById('quizForm').action = "{{ route('admin.quizzes.store') }}";
        document.getElementById('quizFormMethod').value = 'POST';
        document.getElementById('quizTitle').value = '';
        document.getElementById('quizDescription').value = '';
        document.getElementById('quizModal').classList.remove('hidden');
        document.getElementById('quizModal').classList.add('flex');
    }

    function openEditQuizModal(id, title, classroomId, description) {
        document.getElementById('quizModalTitle').innerText = 'Edit Quiz';
        document.getElementById('quizForm').action = `/admin/quizzes/${id}`;
        document.getElementById('quizFormMethod').value = 'PUT';
        document.getElementById('quizTitle').value = title;
        document.getElementById('quizClassroom').value = classroomId;
        document.getElementById('quizDescription').value = description;
        document.getElementById('quizModal').classList.remove('hidden');
        document.getElementById('quizModal').classList.add('flex');
    }

    function closeQuizModal() {
        document.getElementById('quizModal').classList.add('hidden');
        document.getElementById('quizModal').classList.remove('flex');
    }
</script>
@endsection