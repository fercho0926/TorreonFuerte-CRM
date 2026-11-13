@extends('layouts.admin')

@section('title', 'Solicitudes de clave')

@section('page-title', 'Solicitudes de clave')

@section('content')
<div class="callout callout-info py-2 px-3 mb-3">
    <small>{{ __('interfaz.estos_miembros_pidieron_una_clave_nueva_') }} <strong>{{ __('interfaz.paso_1') }}</strong> {{ __('interfaz.pulsa') }} <strong>{{ __('interfaz.generar_clave') }}</strong>. <strong>{{ __('interfaz.paso_2') }}</strong> {{ __('interfaz.en_la_pantalla_siguiente_pulsa_el_boton_') }} <strong>{{ __('interfaz.enviar_por_whatsapp') }}</strong>{{ __('interfaz.que_abre_el_chat_con_el_mensaje_listo') }} <strong>{{ __('interfaz.paso_3') }}</strong> {{ __('interfaz.en_whatsapp_pulsa_enviar_el_sistema_no_e') }}</small>
</div>

<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Solicitudes" icono="fa-key">
    <x-slot:filtros>
        <x-form.select name="estado" label="Estado" icono="fa-list" :opciones="collect(\App\Grillas\SolicitudesClaveGrilla::FILTROS)->map(fn ($texto, $valor) => ['id' => $valor, 'texto' => $texto])->values()->all()" :value="\App\Grillas\SolicitudesClaveGrilla::filtroDe($estado['estado'] ?? null)" :vacio="null" col="col-md-4" />
    </x-slot:filtros>
</x-ui.grilla>
@endsection
