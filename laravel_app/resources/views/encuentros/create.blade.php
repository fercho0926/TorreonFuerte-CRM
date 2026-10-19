@extends('layouts.admin')

@section('title', 'Crear encuentro')

@section('page-title', 'Crear nuevo encuentro')

@section('content')
<x-form.layout :action="route('encuentros.store')" guardar="Guardar encuentro" icono="fa-save" :cancelar="route('encuentros.index')">
    @include('encuentros._form')
</x-form.layout>
@endsection
