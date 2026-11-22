{{-- FILE: resources/views/partials/medal-box.blade.php --}}
@if(Auth::check() && Auth::user()->isStudent())
    @php
        $query = \App\Models\StudentBadge::with(['badge', 'quiz'])
            ->where('student_id', Auth::id());

        // If a specific $classroom is defined in the view, scope badges to that class's quizzes
        if (isset($classroom) && $classroom) {
            $query->whereHas('quiz', function($q) use ($classroom) {
                $q->where('class_id', $classroom->id);
            });
        }

        $myBadges = $query->latest('earned_at')->get();
    @endphp

    <!-- 🧰 MY MEDAL TREASURE BOX CARD -->
    <div class="card-tactile bg-gradient-to-b from-amber-50 to-orange-50/80 border-amber-300 text-center flex flex-col items-center">
        <div class="w-full flex justify-between items-center mb-3 pb-2.5 border-b-4 border-dashed border-amber-200">
            <h3 class="font-display text-base sm:text-lg font-bold text-amber-950 flex items-center gap-2">
                🧰 My Medal Box
            </h3>
            <span class="px-3 py-1 rounded-full bg-amber-400 border-2 border-amber-500 text-amber-950 font-black text-xs shadow-2xs">
                🏅 {{ $myBadges->count() }} {{ \Illuminate\Support\Str::plural('Badge', $myBadges->count()) }}
            </span>
        </div>

        <button type="button"
                onclick="openMedalBox()"
                onmouseenter="this.querySelector('.chest-closed').style.opacity='0'; this.querySelector('.chest-open').style.opacity='1';"
                onmouseleave="this.querySelector('.chest-closed').style.opacity='1'; this.querySelector('.chest-open').style.opacity='0';"
                class="group w-full flex flex-col items-center justify-center py-2 px-3 rounded-2xl hover:bg-amber-100/80 transition-all cursor-pointer focus:outline-none">
            <div style="position: relative; width: 126px; height: 126px; margin: 0 auto;">
                <!-- Closed Chest (Default) -->
                <img src="{{ asset('images/medalbox_2.png') }}"
                     onerror="this.onerror=null;this.src='{{ asset('images/medalbox.png') }}';"
                     alt="My Medal Box"
                     class="chest-closed"
                     style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; opacity: 1; transition: opacity 0.2s ease, transform 0.2s ease; filter: drop-shadow(0 8px 12px rgba(146, 64, 14, 0.2));">
                <!-- Open Chest (On Hover) -->
                <img src="{{ asset('images/chestopen_2.png') }}"
                     onerror="this.onerror=null;this.src='{{ asset('images/chestopen.png') }}';"
                     alt="Open Medal Box"
                     class="chest-open"
                     style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; opacity: 0; transition: opacity 0.2s ease, transform 0.2s ease; filter: drop-shadow(0 8px 12px rgba(146, 64, 14, 0.25));">
            </div>

            <span class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white border-2 border-amber-300 text-amber-900 font-black text-xs group-hover:bg-amber-400 group-hover:border-amber-500 transition-colors">
                ✨ Tap Chest to Inspect
            </span>
        </button>

        @if($myBadges->isNotEmpty())
            <div class="w-full mt-3 pt-3 border-t-2 border-amber-200/80 flex items-center justify-center gap-2">
                @foreach($myBadges->take(4) as $preview)
                    <div title="{{ $preview->badge->name ?? 'Medal' }}" class="w-11 h-11 rounded-xl bg-white border-2 border-amber-300 p-1 flex items-center justify-center shadow-2xs">
                        <img src="{{ $preview->badge->image_url ?? asset('images/1stplace_2.png') }}"
                             alt="{{ $preview->badge->name ?? 'Medal' }}"
                             class="w-full h-full object-contain">
                    </div>
                @endforeach
                @if($myBadges->count() > 4)
                    <button type="button" onclick="openMedalBox()" class="w-11 h-11 rounded-xl bg-amber-200 border-2 border-amber-400 text-amber-950 font-black text-xs cursor-pointer">
                        +{{ $myBadges->count() - 4 }}
                    </button>
                @endif
            </div>
        @endif
    </div>

    <!-- 🧰 CENTERED MEDAL TREASURE BOX MINIVIEW (WITH BLURRED BACKDROP) -->
    <div id="medal-box-modal"
         onclick="if (event.target === this) closeMedalBox();"
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 99999; display: none; align-items: center; justify-content: center; padding: 16px; background-color: rgba(15, 23, 42, 0.55); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);">

        <!-- Centered Chest Window -->
        <div class="bb-pop-in" style="width: 470px; max-width: 100%; max-height: 82vh; display: flex; flex-direction: column; border-radius: 22px; background-color: #72441F; border: 4px solid #F59E0B; box-shadow: 0 20px 45px rgba(0,0,0,0.5), 0 8px 0 #4A2B12; padding: 18px;">

            <!-- Miniview Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; margin-bottom: 12px; border-bottom: 3px solid #F59E0B; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="padding: 5px; border-radius: 12px; background-color: #9A704E; border: 2px solid #F5D013; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset('images/chestopen_2.png') }}"
                             onerror="this.src='{{ asset('images/medalbox_2.png') }}'"
                             alt="Medal Box"
                             style="width: 34px; height: 34px; object-fit: contain;">
                    </div>
                    <div>
                        <h3 style="font-family: 'Fredoka', 'Nunito', sans-serif; font-weight: 700; font-size: 18px; color: #F5D013; line-height: 1.15;">
                            My Treasure Chest
                        </h3>
                        <p style="font-size: 11px; font-weight: 800; color: #FDE68A;">
                            {{ $myBadges->count() }} {{ \Illuminate\Support\Str::plural('medal', $myBadges->count()) }} collected
                        </p>
                    </div>
                </div>

                <!-- Keyhole-Gold Close Button -->
                <button type="button"
                        onclick="closeMedalBox()"
                        style="width: 34px; height: 34px; border-radius: 10px; font-weight: 900; font-size: 14px; background-color: #F5D013; color: #000000; border: 2px solid #F59E0B; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    ✕
                </button>
            </div>

            <!-- Miniview Body (Scrollable Square Badge Slots with 68px Medals) -->
            <div style="overflow-y: auto; flex: 1; padding-right: 2px;">
                @if($myBadges->isNotEmpty())
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        @foreach($myBadges as $earned)
                            <div title="{{ $earned->badge->name ?? 'Medal' }} — {{ $earned->quiz->title ?? 'Quiz' }}"
                                 style="aspect-ratio: 1 / 1; border-radius: 12px; background-color: #875329; border: 3px solid #F59E0B; box-shadow: inset 0 0 0 1px #9A704E; padding: 8px 6px; display: flex; flex-direction: column; align-items: center; justify-content: space-between; text-align: center;">
                                <img src="{{ $earned->badge->image_url ?? asset('images/1stplace_2.png') }}"
                                     alt="{{ $earned->badge->name ?? 'Badge' }}"
                                     style="width: 68px; height: 68px; object-fit: contain; margin: auto 0;">

                                <div style="width: 100%;">
                                    <div style="font-weight: 900; font-size: 11px; color: #FFFFFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $earned->badge->name ?? 'Medal' }}
                                    </div>
                                    <div style="margin-top: 3px; padding: 2px 6px; border-radius: 6px; background-color: #F5D013; color: #000000; font-weight: 900; font-size: 9px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $earned->quiz->title ?? 'Quiz' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 28px 14px; border-radius: 14px; background-color: #875329; border: 2px dashed #F59E0B;">
                        <img src="{{ asset('images/medalbox_2.png') }}"
                             onerror="this.src='{{ asset('images/medalbox.png') }}'"
                             alt="Empty Box"
                             style="width: 74px; height: 74px; object-fit: contain; margin: 0 auto 8px; opacity: 0.9;">
                        <div style="font-weight: 900; font-size: 14px; color: #F5D013;">
                            Your treasure box is empty!
                        </div>
                        <p style="font-size: 11px; font-weight: 700; color: #FDE68A; margin-top: 4px;">
                            Earn top scores or the fastest time to unlock medals here.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function openMedalBox() {
            const modal = document.getElementById('medal-box-modal');
            if (modal) {
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.style.display = 'flex';
            }
        }
        function closeMedalBox() {
            const modal = document.getElementById('medal-box-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeMedalBox();
        });
    </script>
@endif