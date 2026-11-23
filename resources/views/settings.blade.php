{{-- FILE: resources/views/settings.blade.php --}}
@extends('layouts.app')

@section('title', 'Settings - ' . config('app.name', 'BrainBalance'))

@section('content')
<div class="flex flex-col gap-6 sm:gap-8 w-full">

    <!-- Page Header -->
    <div class="card-tactile relative overflow-hidden flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="bb-spectrum-bar absolute top-0 left-0 right-0"></div>
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#E6F7FC] border-2 border-[#99E2F5] text-[#0066D6] font-black text-[11px] uppercase tracking-wider mb-1.5">
                ⚙️ Account Preferences
            </div>
            <h1 class="font-display text-2xl md:text-3xl font-bold text-[#0D2B4E] flex items-center gap-2 mt-0.5">
                Settings
            </h1>
            <p class="text-xs sm:text-sm font-extrabold text-[#5A7696] mt-1">
                Manage your profile photo, name, email, and security preferences.
            </p>
        </div>
        <span class="bg-[#ECFCCB] text-[#3E8708] border-2 border-[#A3E635] text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-wider">
            {{ ucfirst(Auth::user()->role ?? 'Student') }} Account
        </span>
    </div>

    @if ($errors->any())
        <div class="card-tactile bg-[#FFF7ED] border-[#FB923C]">
            <ul class="list-disc list-inside text-xs sm:text-sm font-black text-[#9A3412] space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Main Information Form (8 Cols) -->
        <div class="lg:col-span-8 card-tactile">
            <div class="mb-6 pb-3 border-b-2 border-dashed border-[#B8E2F8]">
                <h2 class="font-display text-lg font-bold text-[#0D2B4E]">Your Information 👤</h2>
                <p class="text-xs font-bold text-[#5A7696] mt-0.5">Update your profile picture, name, email, or password</p>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-5">
                @csrf
                @method('PUT')

                <!-- Profile Picture -->
                <div>
                    <label class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-2">Profile Picture</label>
                    <div class="flex flex-wrap items-center gap-5 bg-[#F2FAFF] border-[3px] border-[#B8E2F8] rounded-2xl p-4">
                        @if(Auth::user()->avatar_url)
                            <img src="{{ Auth::user()->avatar_url }}" class="w-20 h-20 rounded-full border-4 border-[#38BDF8] object-cover shadow-md shrink-0" alt="Profile Avatar">
                        @else
                            <div class="w-20 h-20 rounded-full text-white border-4 border-[#BAE6FD] flex items-center justify-center text-2xl font-black shadow-md shrink-0"
                                 style="background: linear-gradient(135deg, #0066D6 0%, #1DC4DF 100%);">
                                {{ strtoupper(substr(Auth::user()->first_name ?? Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(Auth::user()->last_name ?? '', 0, 1)) }}
                            </div>
                        @endif

                        <div class="flex-1 min-w-[200px]">
                            <input type="file" name="avatar" accept="image/*" class="w-full text-xs font-bold text-[#5A7696] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-2 file:border-[#99E2F5] file:text-xs file:font-black file:bg-[#E6F7FC] file:text-[#0066D6] hover:file:bg-[#CFFAFE] cursor-pointer">
                            <p class="text-[11px] font-bold text-[#5A7696] mt-1.5">JPG, PNG, GIF, or WEBP up to 5MB.</p>
                        </div>
                    </div>
                </div>

                <!-- Name Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1">First name</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', Auth::user()->first_name ?? Auth::user()->name) }}" class="w-full px-4 py-3 rounded-[16px] border-[3px] border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] focus:bg-white transition-colors" required>
                    </div>

                    <div>
                        <label for="last_name" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1">Last name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', Auth::user()->last_name) }}" class="w-full px-4 py-3 rounded-[16px] border-[3px] border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] focus:bg-white transition-colors">
                    </div>
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" class="w-full px-4 py-3 rounded-[16px] border-[3px] border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] focus:bg-white transition-colors" required>
                </div>

                <hr class="border-t-2 border-dashed border-[#B8E2F8] my-1">

                <!-- Password Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1">New password</label>
                        <input type="password" id="password" name="password" placeholder="Leave blank to keep current" class="w-full px-4 py-3 rounded-[16px] border-[3px] border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] focus:bg-white transition-colors">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1">Confirm new password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat new password" class="w-full px-4 py-3 rounded-[16px] border-[3px] border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] focus:bg-white transition-colors">
                    </div>
                </div>

                <!-- Current Password Field -->
                <div>
                    <label for="current_password" class="block text-[11px] font-black text-[#3E5A7A] uppercase tracking-wider mb-1">Current password</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Required to change email or password" class="w-full px-4 py-3 rounded-[16px] border-[3px] border-[#B8E2F8] bg-[#F2FAFF] font-extrabold text-sm text-[#0D2B4E] focus:outline-none focus:border-[#0066D6] focus:bg-white transition-colors">
                    <p class="text-[11px] font-bold text-[#5A7696] mt-1">Only required if you are updating your email address or password.</p>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="btn-tactile-primary">
                        ✓ Save changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Danger Zone Column (4 Cols) -->
        <div class="lg:col-span-4 card-tactile border-[#FDBA74] bg-[#FFF7ED]/70 shadow-[0_8px_0_#FED7AA]">
            <h3 class="font-display text-lg font-bold text-[#C2410C] mb-2">Danger zone ⚠️</h3>
            <p class="text-xs font-bold text-[#5A7696] leading-relaxed mb-5">
                Permanently delete your account and all associated data. This action cannot be undone.
            </p>

            <form action="{{ route('profile.destroy') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-[#FF8400] hover:bg-[#D96B00] text-white font-black text-xs sm:text-sm py-3 px-4 rounded-2xl border-2 border-[#D96B00] shadow-[0_4px_0_#D96B00] active:translate-y-0.5 transition-all cursor-pointer">
                    🗑️ Delete my account
                </button>
            </form>
        </div>
    </div>
</div>
@endsection