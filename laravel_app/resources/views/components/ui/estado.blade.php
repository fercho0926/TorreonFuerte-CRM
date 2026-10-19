{{-- Insignia de estado con el esquema único de colores (App\Support\Estados). --}}
@props(['valor', 'etiqueta' => null])
<span {{ $attributes->class(['badge', 'text-bg-'.\App\Support\Estados::color($valor)]) }}>{{ $etiqueta ?? \App\Support\Estados::etiqueta($valor) }}</span>
