@extends('layouts.admin')

@section('title', 'Editar usuario')

@section('page-title', 'Editar usuario')

@if(\App\Support\Permisos::puede('gestionarClaves', $user))
    @section('page-actions')
        <x-form.confirm :action="route('users.clave.store', $user)" titulo="¿Enviar una clave nueva?" :mensaje="'La contraseña actual de '.$user->name.' dejará de servir. Se abrirá la clave para enviarla por WhatsApp.'" boton="Generar clave" variante="success" tamano=""><i class="fab fa-whatsapp me-1" aria-hidden="true"></i>Enviar nueva clave</x-form.confirm>
    @endsection
@endif

@section('content')
@include('historial._pestanas', ['url' => route('users.edit', $user), 'pestana' => $pestana ?? 'datos'])

@if(($pestana ?? 'datos') === 'historico')
    @include('historial._lista', ['historico' => $historico])
@else
<x-form.layout :action="route('users.update', $user)" method="PUT" guardar="Actualizar usuario" icono="fa-save" boton="warning" :cancelar="route('users.index')">
    {{-- El nombre y el celular son del miembro dueño de la cuenta: se editan en su ficha (spec 016). --}}
    <x-form.section titulo="Miembro" descripcion="La cuenta pertenece a este miembro. Sus datos personales se editan en su ficha." icono="fa-address-card" id="seccion-miembro">
        <div class="col-12 d-flex flex-wrap align-items-center gap-2">
            <div class="me-auto">
                <p class="mb-1 fs-5"><strong>{{ $user->name }}</strong></p>
                <p class="text-muted mb-0">
                    <i class="fas fa-phone me-1" aria-hidden="true"></i>{{ $user->celular ? \App\Support\Celular::formatoLegible($user->celular) : 'Sin celular' }}
                    @if($user->persona?->sede) · {{ $user->persona->sede->nombre }}@endif
                </p>
            </div>
            @if($user->persona_id)
                <a href="{{ route('personas.show', $user->persona_id) }}" class="btn btn-outline-primary"><i class="fas fa-user-edit me-1" aria-hidden="true"></i>Editar datos personales</a>
            @endif
        </div>
    </x-form.section>
    @if(($candidatos ?? collect())->isNotEmpty())
        <x-form.section titulo="Revisar vínculo" descripcion="Al unificar las cuentas no se pudo saber con certeza a qué miembro pertenece esta. Si es uno de estos, elígelo; si no, déjalo sin marcar." icono="fa-exclamation-triangle" id="seccion-revision">
            <x-form.radio name="vincular_a" label="Miembro correcto"
                :opciones="$candidatos->mapWithKeys(fn ($p) => [$p->id => ['texto' => $p->nombre_completo, 'apoyo' => ($p->celular ? \App\Support\Celular::formatoLegible($p->celular) : 'Sin celular').' · '.($p->sede?->nombre ?? '')]])->all()" />
        </x-form.section>
    @endif
    @include('users._form')
</x-form.layout>
@endif
@endsection
