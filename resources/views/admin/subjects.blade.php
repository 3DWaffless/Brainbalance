{{-- FILE: resources/views/admin/subjects.blade.php --}}
@extends('layouts.app')

@section('title', 'Subjects Management - Admin')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-brand-text">All Subjects & Classrooms</h1>
            <p class="text-sm font-bold text-gray-500 mt-1">Create, update, assign teachers, or delete any subject across the platform.</p>
        </div>
        <button type="button" onclick="openCreateSubjectModal()" class="btn-tactile-primary justify-center">
            + Create Subject
        </button>
    </div>

    <div class="bg-white border-4 border-brand-border rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-bg border-b-4 border-brand-border text-xs font-black text-gray-500 uppercase tracking-wider">
                        <th class="py-4 px-5">Subject / Class Name</th>
                        <th class="py-4 px-5">Assigned Teacher</th>
                        <th class="py-4 px-5">Class Code</th>
                        <th class="py-4 px-5">Students</th>
                        <th class="py-4 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-brand-border text-sm font-bold">
                    @forelse($classrooms as $classroom)
                        <tr class="hover:bg-sky-50/40 transition-colors">
                            <td class="py-4 px-5">
                                <div class="font-black text-brand-text">{{ $classroom->name }}</div>
                                @if(!empty($classroom->description))
                                    <div class="text-xs text-gray-400 truncate max-w-xs">{{ $classroom->description }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-gray-600">
                                {{ $classroom->teacher ? $classroom->teacher->first_name . ' ' . $classroom->teacher->last_name : 'Unassigned' }}
                            </td>
                                <td class="py-4 px-5">
                                    <span class="px-2.5 py-1 rounded-xl bg-brand-bg border-2 border-brand-border text-xs font-black text-brand-coral tracking-wider">
                                        {{ $classroom->class_code ?? $classroom->code ?? $classroom->join_code ?? 'N/A' }}
                                    </span>
                                </td>
                            <td class="py-4 px-5 text-gray-600">{{ $classroom->students->count() }}</td>
                            <td class="py-4 px-5 text-right flex items-center justify-end gap-2">
                                <a href="{{ route('subjects.show', $classroom) }}" class="px-3 py-1.5 rounded-xl border-2 border-brand-border bg-brand-bg text-brand-text font-black text-xs hover:bg-white">
                                    Open
                                </a>
                                <button type="button"
                                    onclick="openEditSubjectModal({{ $classroom->id }}, '{{ addslashes($classroom->name) }}', '{{ $classroom->teacher_id }}', '{{ addslashes($classroom->description ?? '') }}')"
                                    class="px-3 py-1.5 rounded-xl border-2 border-brand-border bg-white text-brand-text font-black text-xs hover:bg-brand-bg cursor-pointer">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.classrooms.destroy', $classroom) }}" onsubmit="return confirm('Delete this subject and all its contents?');">
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
                            <td colspan="5" class="py-8 text-center text-gray-400 font-bold">No subjects created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create / Edit Subject Modal -->
<div id="subjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white border-4 border-brand-border rounded-3xl max-w-md w-full p-6 shadow-xl">
        <h2 id="subjectModalTitle" class="text-xl font-black text-brand-text mb-4">Create Subject</h2>
        <form id="subjectForm" method="POST" action="{{ route('admin.subjects.store') }}" class="flex flex-col gap-3">
            @csrf
            <input type="hidden" name="_method" id="subjectFormMethod" value="POST">

            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-1">Subject / Class Name</label>
                <input type="text" name="name" id="subjectName" required placeholder="e.g., Grade 1 Mathematics" class="w-full px-4 py-2.5 rounded-2xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
            </div>
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-1">Assigned Teacher</label>
                <select name="teacher_id" id="subjectTeacher" required class="w-full px-4 py-2.5 rounded-2xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-1">Description</label>
                <textarea name="description" id="subjectDescription" rows="3" class="w-full px-4 py-2.5 rounded-2xl border-2 border-brand-border bg-brand-bg font-bold text-sm"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-3">
                <button type="button" onclick="closeSubjectModal()" class="px-4 py-2.5 rounded-2xl border-2 border-brand-border font-black text-xs text-gray-600 cursor-pointer">Cancel</button>
                <button type="submit" class="btn-tactile-primary text-xs">Save Subject</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateSubjectModal() {
        document.getElementById('subjectModalTitle').innerText = 'Create Subject';
        document.getElementById('subjectForm').action = "{{ route('admin.subjects.store') }}";
        document.getElementById('subjectFormMethod').value = 'POST';
        document.getElementById('subjectName').value = '';
        document.getElementById('subjectDescription').value = '';
        document.getElementById('subjectModal').classList.remove('hidden');
        document.getElementById('subjectModal').classList.add('flex');
    }

    function openEditSubjectModal(id, name, teacherId, description) {
        document.getElementById('subjectModalTitle').innerText = 'Edit Subject';
        document.getElementById('subjectForm').action = `/admin/subjects/${id}`;
        document.getElementById('subjectFormMethod').value = 'PUT';
        document.getElementById('subjectName').value = name;
        document.getElementById('subjectTeacher').value = teacherId;
        document.getElementById('subjectDescription').value = description;
        document.getElementById('subjectModal').classList.remove('hidden');
        document.getElementById('subjectModal').classList.add('flex');
    }

    function closeSubjectModal() {
        document.getElementById('subjectModal').classList.add('hidden');
        document.getElementById('subjectModal').classList.remove('flex');
    }
</script>
@endsection