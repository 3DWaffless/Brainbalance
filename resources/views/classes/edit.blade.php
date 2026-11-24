{{-- FILE: resources/views/classes/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8">
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <a href="{{ route('subjects.show', $classroom->id) }}" class="text-xs font-black text-[#0066D6] hover:underline">
                ← Back to Classroom
            </a>
            <h1 class="text-2xl sm:text-3xl font-black text-[#0D2B4E] mt-1">Edit Classroom</h1>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-5 px-4 py-3.5 rounded-2xl bg-[#FFF7ED] border-2 border-[#FB923C] text-[#9A3412] font-extrabold text-sm">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white border-4 border-[#B3E5FC] rounded-[28px] p-6 sm:p-8 shadow-[0_10px_0_#D4F1FC] mb-6">
        <form method="POST" action="{{ route('classes.update', $classroom->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-black uppercase tracking-wide text-[#3E5A7A] mb-1.5">Classroom Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $classroom->name) }}" required
                       class="w-full h-12 px-4 rounded-2xl border-[3px] border-[#B8E2F8] bg-[#F2FAFF] focus:bg-white focus:border-[#0066D6] font-extrabold text-[#0D2B4E] outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="subject" class="block text-xs font-black uppercase tracking-wide text-[#3E5A7A] mb-1.5">Subject</label>
                    <select id="subject" name="subject"
                            class="w-full h-12 px-4 rounded-2xl border-[3px] border-[#B8E2F8] bg-[#F2FAFF] focus:bg-white focus:border-[#0066D6] font-extrabold text-[#0D2B4E] outline-none">
                        @php $currentSubject = strtolower((string) ($classroom->subject ?? '')); @endphp
                        <option value="math" @selected($currentSubject === 'math')>Mathematics</option>
                        <option value="english" @selected($currentSubject === 'english')>English</option>
                        <option value="general" @selected(!in_array($currentSubject, ['math', 'english'], true))>General / Both</option>
                    </select>
                </div>

                <div>
                    <label for="section" class="block text-xs font-black uppercase tracking-wide text-[#3E5A7A] mb-1.5">Section</label>
                    <input id="section" name="section" type="text" value="{{ old('section', $classroom->section ?? '') }}" placeholder="e.g., Grade 3 - Diamond"
                           class="w-full h-12 px-4 rounded-2xl border-[3px] border-[#B8E2F8] bg-[#F2FAFF] focus:bg-white focus:border-[#0066D6] font-extrabold text-[#0D2B4E] outline-none">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-black uppercase tracking-wide text-[#3E5A7A] mb-1.5">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full p-4 rounded-2xl border-[3px] border-[#B8E2F8] bg-[#F2FAFF] focus:bg-white focus:border-[#0066D6] font-bold text-[#0D2B4E] outline-none">{{ old('description', $classroom->description ?? '') }}</textarea>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
                <a href="{{ route('subjects.show', $classroom->id) }}"
                   class="px-5 py-3 rounded-2xl bg-[#F2FAFF] hover:bg-[#E0F2FE] text-[#3E5A7A] font-extrabold text-sm border-2 border-[#B8E2F8]">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-3 rounded-2xl bg-gradient-to-r from-[#0066D6] to-[#1DC4DF] text-white font-black text-sm border-2 border-[#004FA8] shadow-[0_5px_0_#004FA8] active:translate-y-0.5 transition-all cursor-pointer">
                    💾 Save Classroom Details
                </button>
            </div>
        </form>
    </div>

    <div class="bg-[#FFF7ED] border-4 border-[#FDBA74] rounded-[28px] p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-black text-[#9A3412]">Delete This Classroom</h2>
            <p class="text-xs font-bold text-[#C2410C] mt-1">Permanently removes this classroom and unlinks enrolled students.</p>
        </div>
        <form method="POST" action="{{ route('classes.destroy', $classroom->id) }}"
              onsubmit="return confirm('Permanently delete {{ addslashes($classroom->name) }}?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="px-5 py-3 rounded-2xl bg-[#EA580C] hover:bg-[#C2410C] text-white font-black text-sm border-2 border-[#9A3412] shadow-[0_4px_0_#9A3412] whitespace-nowrap cursor-pointer">
                🗑️ Delete Classroom
            </button>
        </form>
    </div>
</div>
@endsection