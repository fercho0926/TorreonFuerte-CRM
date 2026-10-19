@extends('layouts.admin')

@section('title', 'Crear evento')

@section('page-title', 'Crear nuevo evento')

@section('content')
<x-form.layout :action="route('eventos.store')" guardar="Guardar evento" icono="fa-save" :cancelar="route('eventos.index')">
    @include('eventos._form')
</x-form.layout>
@endsection
