@extends('adminlte::page')

@section('title', 'Asistencia Técnica')

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
        <h1 class="m-0 text-dark"><i data-lucide="file-text" class="w-6 h-6 mr-2 inline-block align-text-bottom"></i>Listado de Asistencia Técnica</h1>
        <a href="{{ route('evidencias.create') }}" class="btn btn-primary">
            <i data-lucide="circle-plus" class="w-4 h-4 mr-1 inline-block align-text-bottom"></i> Nueva Asistencia Técnica
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

<!-- Contador de asistencias técnicas -->
<div class="row">
    <div class="col-12">
        <div class="stats-card">
            <div class="stats-icon">
                <i data-lucide="file-text" class="w-8 h-8"></i>
            </div>
            <div class="stats-info">
                <span class="stats-number" id="tabla-evidencias-total">{{ number_format($evidencias->total()) }}</span>
                <span class="stats-title">Total de Asistencia Técnica Subida</span>
            </div>
        </div>
    </div>
</div>

<!-- Tabla Base Reutilizable con Tailwind CSS y Alpine.js -->
<x-table-base id="tabla-evidencias"
              :perPage="10"
              :exportable="true"
              :searchable="true"
              exportFilename="asistencia_tecnica"
              :serverPaginated="true"
              :totalServerRecords="$evidencias->total()"
              :fromServer="$evidencias->firstItem() ?? 0"
              :toServer="$evidencias->lastItem() ?? 0"
              :filterAction="route('evidencias.index')">
    <x-slot name="filters">
        <x-table-filter name="texto" label="Nombre de la Evidencia" icon="file-text" placeholder="Ej. Visita de acompañamiento" />
        <x-table-filter name="fecha" label="Fecha" icon="calendar" type="date" />
    </x-slot>
    <x-slot name="header">
        <tr>
            <th @click="sortBy(0)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Nombre de la Asistencia</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 0 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 0 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(1)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Descripción</span>
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

    @include('evidencia._rows')
</x-table-base>

<div id="tabla-evidencias-pagination" class="mt-3 flex justify-center sm:justify-end">
    @if ($evidencias->hasPages())
        {{ $evidencias->appends(request()->except('page'))->links('vendor.pagination.table-tailwind') }}
    @endif
</div>

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
