{{-- FILE: resources/views/components/classroom-manage-modal.blade.php --}}
@if (auth()->check() && (auth()->user()->role === 'admin' || (auth()->user()->role === 'teacher' && (int) $classroom->teacher_id === (int) auth()->id())))
    @php
        $joinCode = $classroom->class_code ?? $classroom->code ?? 'N/A';
    @endphp

    <div class="inline-flex flex-wrap items-center gap-2.5">
        <button type="button"
                onclick="document.getElementById('edit-classroom-modal-{{ $classroom->id }}').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-white hover:bg-[#F2FAFF] text-[#0066D6] font-black text-xs sm:text-sm border-[3px] border-[#B8E2F8] shadow-[0_4px_0_#D4F1FC] active:translate-y-0.5 transition-all cursor-pointer">
            ✏️ Edit Classroom
        </button>

        <form method="POST" action="{{ route('classes.destroy', $classroom->id) }}" class="inline"
              onsubmit="return confirm('Delete classroom &quot;{{ addslashes($classroom->name) }}&quot;? All student enrollments in this class will be removed.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-[#FFF7ED] hover:bg-[#FFEDD5] text-[#C2410C] font-black text-xs sm:text-sm border-[3px] border-[#FDBA74] shadow-[0_4px_0_#FED7AA] active:translate-y-0.5 transition-all cursor-pointer">
                🗑️ Delete Class
            </button>
        </form>
    </div>

    <div id="edit-classroom-modal-{{ $classroom->id }}"
         class="hidden fixed inset-0 z-50 flex items-center justify-center bg-[#0D2B4E]/50 backdrop-blur-xs p-4">
        <div class="relative w-full max-w-lg bg-white border-4 border-[#B3E5FC] rounded-[30px] p-6 sm:p-7 shadow-[0_14px_0_#D4F1FC,0_25px_50px_rgba(13,43,78,0.25)]">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase bg-[#E0F7FC] text-[#0066D6] border border-[#B3E5FC]">
                        Classroom Settings
                    </span>
                    <h2 class="text-xl sm:text-2xl font-black text-[#0D2B4E] mt-1">Edit Classroom Details</h2>
                </div>
                <button type="button"
                        onclick="document.getElementById('edit-classroom-modal-{{ $classroom->id }}').classList.add('hidden')"
                        class="w-9 h-9 rounded-full bg-[#F2FAFF] hover:bg-[#E0F2FE] text-[#3E5A7A] font-black text-lg flex items-center justify-center border-2 border-[#B8E2F8] cursor-pointer">
                    ✕
                </button>
            </div>

            <form method="POST" action="{{ route('classes.update', $classroom->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-black uppercase tracking-wide text-[#3E5A7A] mb-1">Classroom Name</label>
                    <input type="text" name="name" value="{{ old('name', $classroom->name) }}" required
                           class="w-full h-11 px-4 rounded-2xl border-[3px] border-[#B8E2F8] bg-[#F2FAFF] focus:bg-white focus:border-[#0066D6] font-extrabold text-sm text-[#0D2B4E] outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wide text-[#3E5A7A] mb-1">Subject</label>
                        <select name="subject"
                                class="w-full h-11 px-3.5 rounded-2xl border-[3px] border-[#B8E2F8] bg-[#F2FAFF] focus:bg-white focus:border-[#0066D6] font-extrabold text-sm text-[#0D2B4E] outline-none">
                            @php $currentSubject = strtolower((string) ($classroom->subject ?? '')); @endphp
                            <option value="math" @selected($currentSubject === 'math')>Mathematics</option>
                            <option value="english" @selected($currentSubject === 'english')>English</option>
                            <option value="general" @selected(!in_array($currentSubject, ['math', 'english'], true))>General / Both</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wide text-[#3E5A7A] mb-1">Section</label>
                        <input type="text" name="section" value="{{ old('section', $classroom->section ?? '') }}" placeholder="e.g., Grade 4 - Sapphire"
                               class="w-full h-11 px-4 rounded-2xl border-[3px] border-[#B8E2F8] bg-[#F2FAFF] focus:bg-white focus:border-[#0066D6] font-extrabold text-sm text-[#0D2B4E] outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-wide text-[#3E5A7A] mb-1">Description</label>
                    <textarea name="description" rows="3" placeholder="Class schedule, learning goals, or welcome message..."
                              class="w-full p-3.5 rounded-2xl border-[3px] border-[#B8E2F8] bg-[#F2FAFF] focus:bg-white focus:border-[#0066D6] font-bold text-sm text-[#0D2B4E] outline-none">{{ old('description', $classroom->description ?? '') }}</textarea>
                </div>

                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-[#F2FAFF] border-2 border-[#B8E2F8]">
                    <div>
                        <div class="text-[11px] font-black uppercase text-[#5A7696]">Class Join Code</div>
                        <div class="text-base font-black tracking-widest text-[#0066D6]">{{ $joinCode }}</div>
                    </div>
                    <button type="submit" form="regen-code-form-{{ $classroom->id }}"
                            onclick="return confirm('Generate a new join code? The old code will stop working for new students.');"
                            class="px-3 py-1.5 rounded-xl bg-white hover:bg-[#E0F2FE] text-[#0066D6] font-black text-xs border-2 border-[#B8E2F8] cursor-pointer">
                        🔄 New Code
                    </button>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button type="button"
                            onclick="document.getElementById('edit-classroom-modal-{{ $classroom->id }}').classList.add('hidden')"
                            class="px-4 py-2.5 rounded-2xl bg-[#F2FAFF] hover:bg-[#E0F2FE] text-[#3E5A7A] font-extrabold text-sm border-2 border-[#B8E2F8] cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-[#0066D6] to-[#1DC4DF] text-white font-black text-sm border-2 border-[#004FA8] shadow-[0_4px_0_#004FA8] active:translate-y-0.5 transition-all cursor-pointer">
                        💾 Save Changes
                    </button>
                </div>
            </form>

            <form id="regen-code-form-{{ $classroom->id }}" method="POST" action="{{ route('classes.regenerate-code', $classroom->id) }}" class="hidden">
                @csrf
            </form>
        </div>
    </div>
@endif