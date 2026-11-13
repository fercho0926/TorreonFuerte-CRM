{{--
    Bandera de un país en SVG (sin dependencias externas ni emoji: Windows no dibuja los emoji de
    bandera como imagen, solo las letras del código). Props: $pais ('es' o 'gb').
--}}
@if($pais === 'es')
    <svg viewBox="0 0 750 500" width="20" height="14" aria-hidden="true" focusable="false">
        <rect width="750" height="500" fill="#c60b1e" />
        <rect y="125" width="750" height="250" fill="#ffc400" />
    </svg>
@elseif($pais === 'gb')
    <svg viewBox="0 0 60 30" width="20" height="14" aria-hidden="true" focusable="false">
        <rect width="60" height="30" fill="#012169" />
        <path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6" />
        <path d="M0,0 L60,30 M60,0 L0,30" stroke="#c8102e" stroke-width="2" />
        <path d="M30,0 V30 M0,15 H60" stroke="#fff" stroke-width="10" />
        <path d="M30,0 V30 M0,15 H60" stroke="#c8102e" stroke-width="6" />
    </svg>
@endif
