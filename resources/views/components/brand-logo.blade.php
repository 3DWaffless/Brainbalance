{{-- FILE: resources/views/components/brand-logo.blade.php --}}
@props([
    'href' => url('/'),
    'size' => 44,
    'showText' => true,
    'subtitle' => 'by Ekklessia',
    'textSize' => null,
])

@php
    // Keeps the logo icon at its exact $size while scaling the wordmark & subtitle larger
    $computedTitleSize = $textSize ?? ($size >= 50 ? (int) round($size * 0.56) : 24);
    $computedSubSize   = $size >= 50 ? max(12, (int) round($size * 0.24)) : 11.5;
    $borderRadius      = $size >= 56 ? '18px' : '14px';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 no-underline group select-none']) }}>
    {{-- Clear icon container kept at the exact $size --}}
    <div class="relative flex items-center justify-center overflow-hidden transition-transform duration-200 group-hover:scale-105 group-active:scale-95"
         style="width: {{ $size }}px;
                height: {{ $size }}px;
                border-radius: {{ $borderRadius }};
                padding: 3px;
                flex-shrink: 0;">
        <img src="{{ asset('images/BrainBalanceLogo.png') }}"
             alt="BrainBalance Logo"
             style="width: 100%; height: 100%; object-fit: contain; border-radius: calc({{ $borderRadius }} - 4px);">
    </div>

    @if ($showText)
        <div style="display: flex; flex-direction: column; justify-content: center;">
            <div style="font-family: 'Fredoka', 'Nunito', sans-serif;
                        font-weight: 800;
                        font-size: {{ $computedTitleSize }}px;
                        line-height: 1.05;
                        letter-spacing: -0.3px;">
                <span style="color: #1D7BF2;">Brain</span><span style="color: #65C91A;">Bal</span><span style="color: #FF9D00;">ance</span>
            </div>

            @if ($subtitle)
                <div style="font-family: 'Nunito', sans-serif;
                            font-size: {{ $computedSubSize }}px;
                            font-weight: 800;
                            color: #64748B;
                            letter-spacing: 0.6px;
                            text-transform: uppercase;
                            margin-top: 2px;">
                    {{ $subtitle }}
                </div>
            @endif
        </div>
    @endif
</a>