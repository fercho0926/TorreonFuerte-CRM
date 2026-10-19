@extends('layouts.admin')

@section('title', 'Crear ministerio')

@section('page-title', 'Crear nuevo ministerio')

@section('content')
<x-form.layout :action="route('ministerios.store')" guardar="Guardar ministerio" icono="fa-save" :cancelar="route('ministerios.index')">
    @include('ministerios._form')
</x-form.layout>
@endsection
