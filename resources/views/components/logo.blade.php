{{--
    Logo Component — centralized dark/light mode logo logic
    
    Props:
    - $size      : 'sm' (30px), 'md' (40px), 'lg' (50px), 'xl' (80px) — default 'md'
    - $variant   : 'icon' (icon only), 'full' (icon + text), 'landing' (uses Logos/ folder with dark mode switch) — default 'full'
    - $href      : link URL — optional, wraps in <a> if provided
    - $textClass : extra classes for the HelpDesk text — optional
    - $darkOnly  : if true, always show dark mode logo (for dark auth pages) — default false
--}}

@props([
    'size' => 'md',
    'variant' => 'full',
    'href' => null,
    'textClass' => '',
    'darkOnly' => false,
])

@php
    $sizes = [
        'sm' => ['img' => 'width:30px; height:30px;', 'imgClass' => 'w-[30px] h-[30px]', 'text' => 'text-xs'],
        'md' => ['img' => 'width:40px; height:40px;', 'imgClass' => 'w-10 h-10', 'text' => 'text-sm'],
        'lg' => ['img' => 'height:50px; width:auto;', 'imgClass' => '', 'text' => 'text-lg'],
        'xl' => ['img' => 'height:80px; width:auto;', 'imgClass' => '', 'text' => 'text-xl'],
    ];
    $s = $sizes[$size] ?? $sizes['md'];
@endphp

@if ($variant === 'landing')
    {{-- Landing page variant — uses Logos/ folder, Alpine dark mode switch --}}
    @if ($href)
        <a href="{{ $href }}" class="flex items-center gap-2 shrink-0">
            <div class="relative" style="{{ $s['img'] }}">
                @if ($darkOnly)
                    <img src="{{ asset('images/Logos/Logo with text DM.png') }}" alt="HelpDesk Logo"
                        style="{{ $s['img'] }}" class="object-contain transition-opacity duration-300" />
                @else
                    <img x-show="!$store.ui.darkMode" src="{{ asset('images/Logos/logo with text LM.png') }}"
                        alt="HelpDesk Logo" style="{{ $s['img'] }}"
                        class="object-contain transition-opacity duration-300" />
                    <img x-show="$store.ui.darkMode" src="{{ asset('images/Logos/Logo with text DM.png') }}"
                        alt="HelpDesk Logo" style="{{ $s['img'] }} display: none;"
                        class="object-contain transition-opacity duration-300" />
                @endif
            </div>
        </a>
    @else
        <div class="flex items-center gap-2 shrink-0">
            <div class="relative" style="{{ $s['img'] }}">
                @if ($darkOnly)
                    <img src="{{ asset('images/Logos/Logo with text DM.png') }}" alt="HelpDesk Logo"
                        style="{{ $s['img'] }}" class="object-contain transition-opacity duration-300" />
                @else
                    <img x-show="!$store.ui.darkMode" src="{{ asset('images/Logos/logo with text LM.png') }}"
                        alt="HelpDesk Logo" style="{{ $s['img'] }}"
                        class="object-contain transition-opacity duration-300" />
                    <img x-show="$store.ui.darkMode" src="{{ asset('images/Logos/Logo with text DM.png') }}"
                        alt="HelpDesk Logo" style="{{ $s['img'] }} display: none;"
                        class="object-contain transition-opacity duration-300" />
                @endif
            </div>
        </div>
    @endif
@elseif($variant === 'full')
    {{-- Full variant — icon + HelpDesk text (for sidebar, auth headers) --}}
    @if ($href)
        <a href="{{ $href }}"
            class="flex flex-col items-center justify-center gap-1 transition hover:opacity-80 {{ $attributes->get('class', '') }}"
            wire:navigate>
            @if ($darkOnly)
                <img src="{{ asset('images/logodm.png') }}" alt="HelpDesk Logo"
                    class="{{ $s['imgClass'] }} object-contain" style="{{ $s['img'] }}">
            @else
                <img x-show="$store.ui.darkMode" src="{{ asset('images/logodm.png') }}" alt="HelpDesk Logo"
                    class="{{ $s['imgClass'] }} object-contain" style="{{ $s['img'] }} display:none;">
                <img x-show="!$store.ui.darkMode" src="{{ asset('images/logolm.png') }}" alt="HelpDesk Logo"
                    class="{{ $s['imgClass'] }} object-contain" style="{{ $s['img'] }}">
            @endif
            <span class="{{ $s['text'] }} font-semibold {{ $textClass }}">HelpDesk</span>
        </a>
    @else
        <div class="flex flex-col items-center justify-center gap-1 {{ $attributes->get('class', '') }}">
            @if ($darkOnly)
                <img src="{{ asset('images/logodm.png') }}" alt="HelpDesk Logo"
                    class="{{ $s['imgClass'] }} object-contain" style="{{ $s['img'] }}">
            @else
                <img x-show="$store.ui.darkMode" src="{{ asset('images/logodm.png') }}" alt="HelpDesk Logo"
                    class="{{ $s['imgClass'] }} object-contain" style="{{ $s['img'] }} display:none;">
                <img x-show="!$store.ui.darkMode" src="{{ asset('images/logolm.png') }}" alt="HelpDesk Logo"
                    class="{{ $s['imgClass'] }} object-contain" style="{{ $s['img'] }}">
            @endif
            <span class="{{ $s['text'] }} font-semibold {{ $textClass }}">HelpDesk</span>
        </div>
    @endif
@elseif($variant === 'icon')
    {{-- Icon only — no text --}}
    @if ($darkOnly)
        <img src="{{ asset('images/logodm.png') }}" alt="HelpDesk Logo" class="{{ $s['imgClass'] }} object-contain"
            style="{{ $s['img'] }}">
    @else
        <img x-show="$store.ui.darkMode" src="{{ asset('images/logodm.png') }}" alt="HelpDesk Logo"
            class="{{ $s['imgClass'] }} object-contain" style="{{ $s['img'] }} display:none;">
        <img x-show="!$store.ui.darkMode" src="{{ asset('images/logolm.png') }}" alt="HelpDesk Logo"
            class="{{ $s['imgClass'] }} object-contain" style="{{ $s['img'] }}">
    @endif
@endif
