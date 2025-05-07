@props([
    'heading' => null,
    'logo' => true,
    'subheading' => null,
])

<header class="fi-simple-header flex flex-col items-center">
    @php
        $customLogoPath = asset('images/logo-agostini-full_color-1-horizontal.png');
        $customLogoAlt = 'Logo Agostini';
    @endphp
    <img src="{{ $customLogoPath }}" alt="{{ $customLogoAlt }}" class="h-9 w-auto">
</header>
