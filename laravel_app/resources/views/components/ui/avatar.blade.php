{{-- Avatar redondo con las iniciales del miembro (no hay fotos). tamano: sm | md | lg. El color sale del id para que cada miembro conserve el suyo. --}}
@props(['miembro', 'tamano' => 'md', 'color' => null])
@php($color ??= ['primary', 'success', 'info', 'warning', 'danger', 'secondary'][$miembro->id % 6])
<span {{ $attributes->class(['avatar-iniciales', "avatar-iniciales--{$tamano}", "text-bg-{$color}"]) }} aria-hidden="true">{{ $miembro->iniciales }}</span>
