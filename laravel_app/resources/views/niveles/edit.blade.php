@extends('layouts.admin')

@section('title', 'Editar nivel')

@section('page-title', 'Editar nivel')

@section('content')
@include('historial._pestanas', ['url' => route('niveles.edit', $nivel), 'pestana' => $pestana ?? 'datos'])

@if(($pestana ?? 'datos') === 'historico')
    @include('historial._lista', ['historico' => $historico])
@else
<x-form.layout :action="route('niveles.update', $nivel)" method="PUT" guardar="Actualizar nivel" icono="fa-save" boton="warning" :cancelar="route('niveles.index')">
    @include('niveles._form')
</x-form.layout>
@endif
@endsection
