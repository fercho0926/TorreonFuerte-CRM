@extends('layouts.admin')

@section('title', 'Clave generada')

@section('page-title', 'Clave generada')

@section('page-actions')
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1" aria-hidden="true"></i>Volver a usuarios</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle me-1" aria-hidden="true"></i>
            <strong>La clave aún NO se ha enviado.</strong> Pulsa el botón verde <strong>Enviar por WhatsApp</strong> (abre el chat con el mensaje listo) y, en WhatsApp, pulsa enviar. Esta clave se muestra una sola vez; si la pierdes, genera una nueva.
        </div>

        <div class="card card-success card-outline mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-key me-1" aria-hidden="true"></i>{{ $usuario->name }}</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-3">
                    <dt class="col-sm-4">Correo</dt>
                    <dd class="col-sm-8">{{ $usuario->email }}</dd>

                    <dt class="col-sm-4">Rol</dt>
                    <dd class="col-sm-8">{{ \App\Models\User::ROLE_LABELS[$usuario->role] ?? $usuario->role }}</dd>

                    <dt class="col-sm-4">WhatsApp</dt>
                    <dd class="col-sm-8"><i class="fab fa-whatsapp text-success me-1" aria-hidden="true"></i>{{ $celularLegible }}
                        <small class="text-muted d-block">Revisa que sea el número correcto antes de enviar.</small></dd>

                    <dt class="col-sm-4">Vigencia</dt>
                    <dd class="col-sm-8">{{ config('claves.vigencia_horas') }} horas (hasta {{ $registro->vence_en->locale('es')->isoFormat('D [de] MMMM, h:mm a') }})</dd>
                </dl>

                <div class="text-center my-4">
                    <div class="text-muted small mb-1">Clave temporal</div>
                    <span class="font-monospace fs-2 fw-bold user-select-all" id="clave-temporal">{{ $clave }}</span>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="{{ $enlace }}" target="_blank" rel="noopener" class="btn btn-success btn-lg">
                        <i class="fab fa-whatsapp me-1" aria-hidden="true"></i>Enviar por WhatsApp
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-lg" data-copiar="{{ $mensaje }}">
                        <i class="fas fa-copy me-1" aria-hidden="true"></i>Copiar mensaje
                    </button>
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted small">Cuando la envíes, márcala como enviada para llevar el control.</span>
                <x-form.accion :action="route('claves.enviada', $registro)" boton="Ya la envié" variante="primary" icono="fa-check" />
            </div>
        </div>

        <details class="mb-4">
            <summary class="text-muted">Ver el mensaje que se enviará</summary>
            <pre class="mt-2 p-3 bg-body-tertiary border rounded" style="white-space: pre-wrap;">{{ $mensaje }}</pre>
        </details>
    </div>
</div>
@endsection
