{{-- Insignia genérica (contadores, roles dentro de una célula...). Para estados use <x-ui.estado>. --}}
@props(['color' => 'secondary'])
<span {{ $attributes->class(['badge', 'text-bg-'.$color]) }}>{{ $slot }}</span>
