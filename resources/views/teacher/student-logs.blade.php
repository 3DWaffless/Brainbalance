{{-- FILE: resources/views/teacher/student-logs.blade.php --}}
@extends('layouts.app')

@section('title', 'Student Activity Logs - BrainBalance')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    <!-- Dashboard-Style Header Container -->
    <div class="bg-gradient-to-r from-[#EBF7FF] via-white to-[#F2FAFF] border-4 border-[#B3E5FC] rounded-[28px] p-5 sm:p-6 shadow-[0_8px_0_#D4F1FC] mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-[#E0F7FC] text-[#0066D6] border-2 border-[#B3E5FC] mb-2">
                My Class Monitoring
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-[#0D2B4E]">Student Activity Logs</h1>
            <p class="text-sm font-bold text-[#5A7696] mt-1">
                Showing activity for students enrolled in your classes when opening class files, submitting assignments, or taking class quizzes.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <a href="{{ route('teacher.student-logs.export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-[#68CC18] hover:bg-[#58B312] text-white font-black text-sm border-2 border-[#46910D] shadow-[0_4px_0_#46910D] whitespace-nowrap transition-all">
                ⬇️ Export CSV
            </a>
            <a href="{{ route('subjects.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white hover:bg-[#F2FAFF] text-[#0D2B4E] font-extrabold text-sm border-[3px] border-[#B8E2F8] shadow-[0_4px_0_#D4F1FC] whitespace-nowrap transition-all">
                🏫 My Classrooms
            </a>
        </div>
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white border-[3px] border-[#B3E5FC] rounded-3xl p-4 shadow-[0_6px_0_#D4F1FC]">
            <div class="text-xs font-black uppercase text-[#5A7696]">My Enrolled Students</div>
            <div class="text-2xl font-black text-[#0D2B4E] mt-1">{{ $stats['total_students'] }}</div>
        </div>
        <div class="bg-white border-[3px] border-[#B3E5FC] rounded-3xl p-4 shadow-[0_6px_0_#D4F1FC]">
            <div class="text-xs font-black uppercase text-[#5A7696]">Active Today</div>
            <div class="text-2xl font-black text-[#0066D6] mt-1">{{ $stats['active_today'] }}</div>
        </div>
        <div class="bg-white border-[3px] border-[#B3E5FC] rounded-3xl p-4 shadow-[0_6px_0_#D4F1FC]">
            <div class="text-xs font-black uppercase text-[#5A7696]">Files Opened</div>
            <div class="text-2xl font-black text-[#0284C7] mt-1">{{ $stats['total_files'] }}</div>
        </div>
        <div class="bg-white border-[3px] border-[#B3E5FC] rounded-3xl p-4 shadow-[0_6px_0_#D4F1FC]">
            <div class="text-xs font-black uppercase text-[#5A7696]">Assignments Turned In</div>
            <div class="text-2xl font-black text-[#EA580C] mt-1">{{ $stats['total_assignments'] }}</div>
        </div>
        <div class="bg-white border-[3px] border-[#B3E5FC] rounded-3xl p-4 shadow-[0_6px_0_#D4F1FC] col-span-2 lg:col-span-1">
            <div class="text-xs font-black uppercase text-[#5A7696]">Class Quizzes Completed</div>
            <div class="text-2xl font-black text-[#16A34A] mt-1">{{ $stats['total_quizzes'] }}</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('teacher.student-logs.index') }}"
          class="bg-white border-[3px] border-[#B3E5FC] rounded-3xl p-4 sm:p-5 shadow-[0_6px_0_#D4F1FC] mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
        <div>
            <label class="block text-[11px] font-black uppercase text-[#3E5A7A] mb-1">My Classroom</label>
            <select name="classroom_id" class="w-full h-11 px-3 rounded-xl border-2 border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E]">
                <option value="">All My Classrooms</option>
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected((string) request('classroom_id') === (string) $classroom->id)>
                        {{ $classroom->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[11px] font-black uppercase text-[#3E5A7A] mb-1">Student</label>
            <select name="student_id" class="w-full h-11 px-3 rounded-xl border-2 border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E]">
                <option value="">All My Students</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((string) request('student_id') === (string) $student->id)>
                        {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) ?: $student->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[11px] font-black uppercase text-[#3E5A7A] mb-1">Class Activity</label>
            <select name="category" class="w-full h-11 px-3 rounded-xl border-2 border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E]">
                <option value="all" @selected(request('category', 'all') === 'all')>All Class Activity</option>
                <option value="file" @selected(request('category') === 'file')>Opened Files</option>
                <option value="assignment" @selected(request('category') === 'assignment')>Submitted Assignments</option>
                <option value="quiz" @selected(request('category') === 'quiz')>Quiz Attempts</option>
            </select>
        </div>

        <div>
            <label class="block text-[11px] font-black uppercase text-[#3E5A7A] mb-1">Date</label>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="w-full h-11 px-3 rounded-xl border-2 border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E]">
        </div>

        <div>
            <label class="block text-[11px] font-black uppercase text-[#3E5A7A] mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Student, file, quiz..."
                   class="w-full h-11 px-3 rounded-xl border-2 border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E]">
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="flex-1 h-11 rounded-xl bg-[#0066D6] hover:bg-[#0052AD] text-white font-black text-sm shadow-[0_3px_0_#004FA8] transition-all cursor-pointer">
                Filter
            </button>
            <a href="{{ route('teacher.student-logs.index') }}"
               class="h-11 px-3.5 inline-flex items-center justify-center rounded-xl bg-[#F2FAFF] hover:bg-[#E0F2FE] text-[#3E5A7A] font-extrabold text-sm border-2 border-[#B8E2F8]">
                Reset
            </a>
        </div>
    </form>

    <!-- Activity Logs Table -->
    <div class="bg-white border-4 border-[#B3E5FC] rounded-[28px] overflow-hidden shadow-[0_10px_0_#D4F1FC]">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F2FAFF] border-b-2 border-[#B8E2F8] text-xs font-black uppercase tracking-wider text-[#3E5A7A]">
                        <th class="py-3.5 px-4">When</th>
                        <th class="py-3.5 px-4">Student</th>
                        <th class="py-3.5 px-4">Classroom</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Class Activity</th>
                        <th class="py-3.5 px-4">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E0F2FE] text-sm font-bold text-[#0D2B4E]">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-[#F8FCFF] transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-[#5A7696]">
                                @if ($log['timestamp'])
                                    <div class="font-extrabold text-[#0D2B4E]">{{ $log['timestamp']->format('M d, Y') }}</div>
                                    <div>{{ $log['timestamp']->format('h:i A') }} ({{ $log['timestamp']->diffForHumans() }})</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    @if (!empty($log['avatar']))
                                        <img src="{{ $log['avatar'] }}" alt="{{ $log['student_name'] }}" class="w-9 h-9 rounded-full object-cover border-2 border-[#38BDF8]">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-[#0066D6] text-white font-black text-xs flex items-center justify-center">
                                            {{ strtoupper(substr($log['student_name'], 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-black text-[#0D2B4E]">{{ $log['student_name'] }}</div>
                                        <div class="text-xs text-[#5A7696]">{{ $log['student_email'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-xl bg-[#F2FAFF] border border-[#B8E2F8] text-xs font-black text-[#0D2B4E]">
                                    {{ $log['classroom_name'] ?? 'Class' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @php
                                    $badgeStyles = [
                                        'quiz'       => 'bg-[#ECFDF5] text-[#047857] border-[#6EE7B7]',
                                        'file'       => 'bg-[#EFF6FF] text-[#1D4ED8] border-[#93C5FD]',
                                        'assignment' => 'bg-[#FFF7ED] text-[#C2410C] border-[#FDBA74]',
                                    ];
                                    $categoryLabels = [
                                        'quiz'       => 'Quiz',
                                        'file'       => 'File Opened',
                                        'assignment' => 'Assignment',
                                    ];
                                    $style = $badgeStyles[$log['category']] ?? 'bg-[#F1F5F9] text-[#334155] border-[#CBD5E1]';
                                    $label = $categoryLabels[$log['category']] ?? ucfirst($log['category']);
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-black uppercase border {{ $style }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-black text-[#0D2B4E]">{{ $log['action'] }}</div>
                                <div class="text-xs text-[#5A7696]">{{ $log['description'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    @if (!empty($log['detail_badge']))
                                        <span class="px-2.5 py-1 rounded-xl bg-[#F2FAFF] border border-[#B8E2F8] text-xs font-extrabold text-[#0066D6]">
                                            {{ $log['detail_badge'] }}
                                        </span>
                                    @endif
                                    @if (!empty($log['review_url']))
                                        <a href="{{ $log['review_url'] }}"
                                           class="px-2.5 py-1 rounded-xl bg-[#0066D6] hover:bg-[#0052AD] text-white text-xs font-black">
                                            Review
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-[#5A7696] font-extrabold">
                                No student activity logs found for your classes yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-5 py-4 bg-[#F2FAFF] border-t-2 border-[#B8E2F8]">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection