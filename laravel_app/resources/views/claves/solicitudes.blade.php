@extends('layouts.admin')

@section('title', 'Solicitudes de clave')

@section('page-title', 'Solicitudes de clave')

@section('content')
<div class="callout callout-info py-2 px-3 mb-3">
    <small>Estos miembros pidieron una clave nueva desde el inicio de sesión. <strong>Paso 1:</strong> pulsa <strong>Generar clave</strong>. <strong>Paso 2:</strong> en la pantalla siguiente pulsa el botón verde <strong>Enviar por WhatsApp</strong>, que abre el chat con el mensaje listo. <strong>Paso 3:</strong> en WhatsApp pulsa enviar. El sistema no envía el mensaje por sí solo.</small>
</div>

<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Solicitudes" icono="fa-key">
    <x-slot:filtros>
        <x-form.select name="estado" label="Estado" icono="fa-list" :opciones="collect(\App\Grillas\SolicitudesClaveGrilla::FILTROS)->map(fn ($texto, $valor) => ['id' => $valor, 'texto' => $texto])->values()->all()" :value="\App\Grillas\SolicitudesClaveGrilla::filtroDe($estado['estado'] ?? null)" :vacio="null" col="col-md-4" />
    </x-slot:filtros>
</x-ui.grilla>
@endsection
