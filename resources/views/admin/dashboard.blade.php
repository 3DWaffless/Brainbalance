{{-- FILE: resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Students Management - Admin')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-brand-text">Students Directory</h1>
            <p class="text-sm font-bold text-gray-500 mt-1">Create, edit, and manage all registered student accounts.</p>
        </div>
        <button type="button" onclick="openCreateStudentModal()" class="btn-tactile-primary justify-center">
            + Add Student
        </button>
    </div>

    <!-- Quick Overview Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border-4 border-brand-border rounded-3xl p-4 shadow-sm">
            <div class="text-xs font-black text-gray-400 uppercase">Total Students</div>
            <div class="text-2xl sm:text-3xl font-black text-brand-coral mt-1">{{ $stats['students'] }}</div>
        </div>
        <div class="bg-white border-4 border-brand-border rounded-3xl p-4 shadow-sm">
            <div class="text-xs font-black text-gray-400 uppercase">Total Teachers</div>
            <div class="text-2xl sm:text-3xl font-black text-brand-text mt-1">{{ $stats['teachers'] }}</div>
        </div>
        <div class="bg-white border-4 border-brand-border rounded-3xl p-4 shadow-sm">
            <div class="text-xs font-black text-gray-400 uppercase">Active Subjects</div>
            <div class="text-2xl sm:text-3xl font-black text-brand-text mt-1">{{ $stats['classrooms'] }}</div>
        </div>
        <div class="bg-white border-4 border-brand-border rounded-3xl p-4 shadow-sm">
            <div class="text-xs font-black text-gray-400 uppercase">Total Quizzes</div>
            <div class="text-2xl sm:text-3xl font-black text-brand-text mt-1">{{ $stats['quizzes'] }}</div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white border-4 border-brand-border rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-bg border-b-4 border-brand-border text-xs font-black text-gray-500 uppercase tracking-wider">
                        <th class="py-4 px-5">Student Name</th>
                        <th class="py-4 px-5">Email</th>
                        <th class="py-4 px-5">Joined</th>
                        <th class="py-4 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-brand-border text-sm font-bold">
                    @forelse($students as $student)
                        <tr class="hover:bg-sky-50/40 transition-colors">
                            <td class="py-4 px-5 font-black text-brand-text">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </td>
                            <td class="py-4 px-5 text-gray-600">{{ $student->email }}</td>
                            <td class="py-4 px-5 text-gray-400 text-xs">{{ $student->created_at?->format('M d, Y') }}</td>
                            <td class="py-4 px-5 text-right flex items-center justify-end gap-2">
                                <button type="button"
                                    onclick="openEditStudentModal({{ $student->id }}, '{{ addslashes($student->first_name) }}', '{{ addslashes($student->last_name) }}', '{{ addslashes($student->email) }}')"
                                    class="px-3 py-1.5 rounded-xl border-2 border-brand-border bg-white text-brand-text font-black text-xs hover:bg-brand-bg cursor-pointer">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.users.destroy', $student) }}" onsubmit="return confirm('Delete this student account permanently?');">
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
                            <td colspan="4" class="py-8 text-center text-gray-400 font-bold">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create / Edit Student Modal -->
<div id="studentModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white border-4 border-brand-border rounded-3xl max-w-md w-full p-6 shadow-xl">
        <h2 id="studentModalTitle" class="text-xl font-black text-brand-text mb-4">Add New Student</h2>
        <form id="studentForm" method="POST" action="{{ route('admin.students.store') }}" class="flex flex-col gap-3">
            @csrf
            <input type="hidden" name="_method" id="studentFormMethod" value="POST">

            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-1">First Name</label>
                <input type="text" name="first_name" id="studentFirstName" required class="w-full px-4 py-2.5 rounded-2xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
            </div>
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-1">Last Name</label>
                <input type="text" name="last_name" id="studentLastName" required class="w-full px-4 py-2.5 rounded-2xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
            </div>
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-1">Email</label>
                <input type="email" name="email" id="studentEmail" required class="w-full px-4 py-2.5 rounded-2xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
            </div>
            <div>
                <label class="block text-xs font-black text-gray-500 uppercase mb-1">Password <span id="studentPasswordHint" class="text-gray-400 font-normal">(min 8 chars)</span></label>
                <input type="password" name="password" id="studentPassword" class="w-full px-4 py-2.5 rounded-2xl border-2 border-brand-border bg-brand-bg font-bold text-sm">
            </div>

            <div class="flex justify-end gap-2 mt-3">
                <button type="button" onclick="closeStudentModal()" class="px-4 py-2.5 rounded-2xl border-2 border-brand-border font-black text-xs text-gray-600 cursor-pointer">Cancel</button>
                <button type="submit" class="btn-tactile-primary text-xs">Save Student</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateStudentModal() {
        document.getElementById('studentModalTitle').innerText = 'Add New Student';
        document.getElementById('studentForm').action = "{{ route('admin.students.store') }}";
        document.getElementById('studentFormMethod').value = 'POST';
        document.getElementById('studentFirstName').value = '';
        document.getElementById('studentLastName').value = '';
        document.getElementById('studentEmail').value = '';
        document.getElementById('studentPassword').required = true;
        document.getElementById('studentPasswordHint').innerText = '(min 8 chars)';
        document.getElementById('studentModal').classList.remove('hidden');
        document.getElementById('studentModal').classList.add('flex');
    }

    function openEditStudentModal(id, firstName, lastName, email) {
        document.getElementById('studentModalTitle').innerText = 'Edit Student';
        document.getElementById('studentForm').action = `/admin/students/${id}`;
        document.getElementById('studentFormMethod').value = 'PUT';
        document.getElementById('studentFirstName').value = firstName;
        document.getElementById('studentLastName').value = lastName;
        document.getElementById('studentEmail').value = email;
        document.getElementById('studentPassword').required = false;
        document.getElementById('studentPassword').value = '';
        document.getElementById('studentPasswordHint').innerText = '(leave blank to keep current)';
        document.getElementById('studentModal').classList.remove('hidden');
        document.getElementById('studentModal').classList.add('flex');
    }

    function closeStudentModal() {
        document.getElementById('studentModal').classList.add('hidden');
        document.getElementById('studentModal').classList.remove('flex');
    }
</script>
@endsection