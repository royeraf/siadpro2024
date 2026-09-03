@extends('adminlte::page')

@section('title', 'Inicio')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@vite(['resources/css/app.css'])
@endsection

@section('content_header')
    <x-page-header icon="house" title="Inicio" color="blue" />
@stop

@section('content')

<div class="mb-4">
    <p class="text-lg text-gray-800 mb-1">Bienvenido(a), {{ Auth::user()->name }}</p>
    <p class="text-sm text-gray-500 mt-2">Elige un módulo para continuar.</p>
</div>

@forelse ($modulos as $modulo)
    @if($loop->first)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
    @endif

    <x-module-card
        :icon="$modulo['icon']"
        :title="$modulo['text']"
        :href="$modulo['href']"
        :color="$modulo['color']"
        :description="$modulo['description']"
    />

    @if($loop->last)
        </div>
    @endif
@empty
    <div class="alert alert-info">
        No tienes módulos asignados todavía. Comunícate con el administrador del sistema si crees que esto es un error.
    </div>
@endforelse

@stop
