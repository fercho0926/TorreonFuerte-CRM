@extends('layouts.admin')

@section('title', 'Crear usuario')

@section('page-title', 'Crear nuevo usuario')

@section('content')
@php
    // Spec 016: todo usuario es un miembro. Paso 1: el celular; paso 2: el miembro encontrado o sus datos.
    $estado = $busqueda['estado'] ?? null;
    $personas = $busqueda['personas'] ?? collect();
    $esAdmin = $sedes->isNotEmpty();
    $celularLegible = $celular !== '' ? \App\Support\Celular::formatoLegible($celular) : '';
    $conFormulario = in_array($estado, ['encontrado', 'varios', 'nuevo'], true);
@endphp

@if($estado === null)
    <x-form.layout :action="route('users.create')" method="GET" guardar="Buscar miembro" icono="fa-search" :cancelar="route('users.index')">
        <x-form.section titulo="Celular del miembro" descripcion="Todo usuario es un miembro de la iglesia. Escribe su celular: si ya está registrado se vincula; si no, lo registras aquí mismo." icono="fa-mobile-alt" id="seccion-celular">
            <x-form.input name="celular" label="Celular de WhatsApp" type="celular" icono="fa-phone" :required="true" :value="$celular" mascara="celular" maxlength="20" ejemplo="310 555 9999" />
            @if($esAdmin)
                <x-form.select name="sede_id" label="Sede del miembro" icono="fa-church" :required="true" :opciones="$sedes->map(fn ($s) => ['id' => $s->id, 'texto' => $s->nombre])->all()" :value="$sedeElegidaId"
                    vacio="Elige una sede" ayuda="El administrador ve todas las sedes, pero como miembro pertenece a una." />
            @endif
        </x-form.section>
    </x-form.layout>
@else
    <div class="card card-outline card-primary mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-2">
            <span><i class="fas fa-phone me-1 text-muted" aria-hidden="true"></i>Celular: <strong>{{ $celularLegible }}</strong></span>
            <a href="{{ route('users.create', $esAdmin && $sedeElegidaId ? ['sede_id' => $sedeElegidaId] : []) }}" class="btn btn-outline-secondary ms-auto">Cambiar celular</a>
        </div>
    </div>

    @switch($estado)
        @case('con_cuenta')
            @php($existente = $personas->first())
            <div class="alert alert-warning" role="alert">
                <strong>{{ $existente->nombre_completo }}</strong> ya tiene una cuenta. Cada miembro tiene una sola cuenta.
                <a href="{{ route('users.edit', $existente->cuenta) }}" class="alert-link">Ver su cuenta</a>
            </div>
            @break
        @case('inactivo')
            @php($existente = $personas->first())
            <div class="alert alert-warning" role="alert">
                <strong>{{ $existente->nombre_completo }}</strong> está inactivo. Reactívalo desde su ficha y vuelve a crear la cuenta.
                <a href="{{ route('personas.show', $existente) }}" class="alert-link">Abrir su ficha</a>
            </div>
            @break
        @case('otra_sede')
            <div class="alert alert-info" role="alert">Ese celular pertenece a un miembro de otra sede; solicita su traslado.</div>
            @break
        @case('no_visible')
            <div class="alert alert-info" role="alert">Ese celular pertenece a un miembro fuera de tu alcance; pide a tu pastor que cree la cuenta.</div>
            @break
    @endswitch

    @if($conFormulario)
        <x-form.layout :action="route('users.store')" guardar="Crear y generar clave" icono="fa-key" :cancelar="route('users.index')">
            <input type="hidden" name="celular" value="{{ $celular }}">
            @if($esAdmin)
                <input type="hidden" name="sede_id" value="{{ $sedeElegidaId }}">
            @endif

            @if($estado === 'encontrado')
                @php($miembro = $personas->first())
                <x-form.section titulo="Miembro" descripcion="La cuenta queda vinculada a este miembro: su nombre y su celular se toman de su ficha." icono="fa-address-card" id="seccion-miembro">
                    <div class="col-12">
                        <input type="hidden" name="persona_id" value="{{ $miembro->id }}">
                        <p class="mb-1 fs-5"><i class="fas fa-user-check text-success me-1" aria-hidden="true"></i><strong>{{ $miembro->nombre_completo }}</strong></p>
                        <p class="text-muted mb-0">{{ $miembro->estado_etiqueta }}@if($miembro->numero_documento) · Doc. {{ $miembro->numero_documento }}@endif</p>
                    </div>
                </x-form.section>
            @elseif($estado === 'varios')
                <x-form.section titulo="Miembro" descripcion="Varios miembros tienen ese celular. Elige a quién pertenece la cuenta." icono="fa-address-card" id="seccion-miembro">
                    <x-form.radio name="persona_id" label="Miembro" :required="true"
                        :opciones="$personas->mapWithKeys(fn ($p) => [$p->id => ['texto' => $p->nombre_completo, 'apoyo' => $p->estado_etiqueta.($p->numero_documento ? ' · Doc. '.$p->numero_documento : '')]])->all()" />
                </x-form.section>
            @else
                <x-form.section titulo="Datos del miembro" descripcion="Ese celular no es de ningún miembro: se registra como miembro activo junto con su cuenta." icono="fa-user-plus" id="seccion-miembro">
                    <x-form.input name="miembro[nombres]" label="Nombres" icono="fa-user" :required="true" maxlength="255" />
                    <x-form.input name="miembro[apellidos]" label="Apellidos" icono="fa-user" :required="true" maxlength="255" />
                    <x-form.date name="miembro[fecha_nacimiento]" label="Fecha de nacimiento" />
                    <x-form.input name="miembro[direccion]" label="Dirección" icono="fa-map-marker-alt" maxlength="255" />
                </x-form.section>
            @endif

            @include('users._form')
        </x-form.layout>
    @endif
@endif
@endsection
