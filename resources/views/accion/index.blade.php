@extends('adminlte::page')

@section('title', 'Acción de Sensibilización')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@vite(['resources/css/app.css'])
<style>
    .stats-card {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        border-radius: 8px;
        padding: 12px 18px;
        margin-bottom: 15px;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        display: inline-flex;
        align-items: center;
        gap: 15px;
    }
    .stats-icon {
        font-size: 32px;
        color: rgba(255, 255, 255, 0.9);
    }
    .stats-number {
        font-size: 24px;
        font-weight: 700;
        display: block;
        color: #facc15;
        line-height: 1.1;
    }
    .stats-title {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.95;
    }
    @media (max-width: 575px) {
        .stats-card {
            display: flex;
            width: 100%;
        }
    }
</style>
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <x-section-heading icon="megaphone">Listado de Acciones de Sensibilización</x-section-heading>
        <a href="{{ route('accions.create') }}" class="btn btn-primary">
            <i data-lucide="circle-plus" class="w-4 h-4 mr-1 inline-block align-text-bottom"></i> Nueva Acción
        </a>
    </div>
@stop

@section('content')

@if(session('mensajeinternet'))
    <div class="alert alert-danger">
        {{ session('mensajeinternet') }}
    </div>
@endif

@if(session('success'))
    <div id="alert-success" class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if ($errors->has('documento'))
    <div class="alert alert-danger">
        {{ $errors->first('documento') }}
    </div>
@endif

<!-- Contador de acciones -->
<div class="row">
    <div class="col-12">
        <div class="stats-card">
            <div class="stats-icon">
                <i data-lucide="megaphone" class="w-8 h-8"></i>
            </div>
            <div class="stats-info">
                <span class="stats-number" id="tabla-acciones-total">{{ number_format($accions->total()) }}</span>
                <span class="stats-title">Total de Acciones de Sensibilización Subidas</span>
            </div>
        </div>
    </div>
</div>

<!-- Tabla Base Reutilizable con Tailwind CSS y Alpine.js -->
<x-table-base id="tabla-acciones"
              :perPage="10"
              :exportable="true"
              :searchable="true"
              exportFilename="acciones_sensibilizacion"
              :serverPaginated="true"
              :totalServerRecords="$accions->total()"
              :fromServer="$accions->firstItem() ?? 0"
              :toServer="$accions->lastItem() ?? 0"
              :filterAction="route('accions.index')">
    <x-slot name="filters">
        <x-table-filter name="texto" label="Nombre de la Acción" icon="file-text" placeholder="Ej. Taller de sensibilización" />
        <x-table-filter name="fecha" label="Fecha" icon="calendar" type="date" />
    </x-slot>
    <x-slot name="header">
        <tr>
            <th @click="sortBy(0)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Nombre de la Acción</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 0 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 0 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(1)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Lugar</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 1 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 1 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(2)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition" style="width: 120px;">
                <div class="flex items-center justify-between">
                    <span>Fecha</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 2 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 2 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th class="px-4 py-3 text-center no-export" style="width: 100px;">
                Documento
            </th>
            <th @click="sortBy(4)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Usuario</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 4 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 4 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(5)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Institución</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 5 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 5 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(6)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Provincia</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 6 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 6 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(7)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Distrito</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 7 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 7 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(8)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>UGEL</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 8 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 8 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th class="px-4 py-3 text-center no-export" style="width: 100px;">
                Opciones
            </th>
        </tr>
    </x-slot>

    @include('accion._rows')
</x-table-base>

<x-table-pagination id="tabla-acciones" :paginator="$accions" />

@stop

@section('js')
@vite(['resources/js/app.js'])
<x-sweet-alert />
<script>
    setTimeout(function() {
        var alertEl = document.getElementById('alert-success');
        if (alertEl) {
            alertEl.classList.remove('show');
        }
    }, 4000);
</script>
@stop
