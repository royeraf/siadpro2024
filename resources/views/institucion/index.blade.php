@extends('adminlte::page')

@section('title', 'Instituciones')

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
</style>
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="m-0 text-dark"><i data-lucide="landmark" class="w-6 h-6 mr-2 inline-block align-text-bottom"></i>Listado de Instituciones</h1>
        <a href="{{ url('institucions/create') }}" class="btn btn-primary">
            <i data-lucide="circle-plus" class="w-4 h-4 mr-1 inline-block align-text-bottom"></i> Nueva Institución
        </a>
    </div>
@stop

@section('content')

<!-- Contador de instituciones -->
<div class="row">
    <div class="col-12">
        <div class="stats-card">
            <div class="stats-icon">
                <i data-lucide="school" class="w-8 h-8"></i>
            </div>
            <div class="stats-info">
                <span class="stats-number">{{ number_format($total) }}</span>
                <span class="stats-title">Total de Instituciones Encontradas</span>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de búsqueda Backend -->
<div class="card card-outline card-primary mb-4">
    <div class="card-header py-2">
        <h3 class="card-title font-weight-bold">
            <i data-lucide="filter" class="w-4 h-4 mr-1 inline-block align-text-bottom"></i> Filtros de Búsqueda
        </h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('institucion.index') }}">
            <div class="row">
                <!-- Filtro por Nombre / Nro de Institución -->
                <div class="col-md-4 col-sm-6 mb-3">
                    <label for="institucion" class="form-label font-weight-normal">
                        <i data-lucide="school" class="w-4 h-4 mr-1 inline-block align-text-bottom text-muted"></i> Institución o Nro:
                    </label>
                    <input type="text" class="form-control" id="institucion" name="institucion" 
                           placeholder="Ej. Illathupa, 32004, etc." 
                           value="{{ request('institucion') }}">
                </div>

                <!-- Filtro por Código Modular -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <label for="codModular" class="form-label font-weight-normal">
                        <i data-lucide="barcode" class="w-4 h-4 mr-1 inline-block align-text-bottom text-muted"></i> Cód. Modular:
                    </label>
                    <input type="text" class="form-control" id="codModular" name="codModular" 
                           placeholder="Ej. 0234567" 
                           value="{{ request('codModular') }}">
                </div>

                <!-- Filtro por UGEL -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <label for="ugels" class="form-label font-weight-normal">
                        <i data-lucide="map-pin" class="w-4 h-4 mr-1 inline-block align-text-bottom text-muted"></i> UGEL:
                    </label>
                    <select class="form-control" id="ugels" name="ugels">
                        <option value="">-- Todas las UGEL --</option>
                        @foreach ($listaUgels as $ugelItem)
                            <option value="{{ $ugelItem }}" {{ request('ugels') == $ugelItem ? 'selected' : '' }}>
                                {{ $ugelItem }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Nivel -->
                <div class="col-md-2 col-sm-6 mb-3">
                    <label for="nivel" class="form-label font-weight-normal">
                        <i data-lucide="layers" class="w-4 h-4 mr-1 inline-block align-text-bottom text-muted"></i> Nivel:
                    </label>
                    <select class="form-control" id="nivel" name="nivel">
                        <option value="">-- Todos --</option>
                        @foreach ($listaNiveles as $nivelItem)
                            <option value="{{ $nivelItem }}" {{ request('nivel') == $nivelItem ? 'selected' : '' }}>
                                {{ $nivelItem }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row align-items-center mt-2">
                <!-- Botones de Acción -->
                <div class="col-12 text-right mb-2">
                    <div class="inline-flex items-center gap-3 mr-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-md shadow-sm transition border-0 cursor-pointer mr-2">
                            <i data-lucide="search" class="w-4 h-4 mr-2 inline-block align-text-bottom"></i> Buscar
                        </button>
                        <a href="{{ route('institucion.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 active:bg-gray-700 text-white text-sm font-semibold rounded-md shadow-sm transition text-decoration-none">
                            <i data-lucide="eraser" class="w-4 h-4 mr-2 inline-block align-text-bottom"></i> Limpiar Filtros
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla Base Reutilizable con Tailwind CSS y Alpine.js -->
<x-table-base id="tabla-instituciones" :perPage="request('per_page', 15)" :exportable="true" :searchable="true" exportFilename="instituciones" :exportUrl="route('exportInstituciones', request()->all())" :serverPaginated="true">
    <x-slot name="header">
        <tr>
            <th @click="sortBy(0)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition" style="width: 70px;">
                <div class="flex items-center justify-between">
                    <span>ID</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 0 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 0 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(1)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Institución</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 1 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 1 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(2)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition" style="width: 140px;">
                <div class="flex items-center justify-between">
                    <span>Cód. Modular</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 2 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 2 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(3)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Nivel</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 3 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 3 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(4)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Provincia</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 4 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 4 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(5)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Distrito</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 5 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 5 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(6)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Centro Poblado</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 6 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 6 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(7)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>UGEL</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 7 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 7 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th class="px-4 py-3 text-center no-export" style="width: 110px;">
                Opciones
            </th>
        </tr>
    </x-slot>

    @forelse ($institucions as $institucion)
        <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
            <td class="px-4 py-3 text-center font-bold text-gray-900">{{ $institucion->id }}</td>
            <td class="px-4 py-3 font-semibold text-blue-600">{{ $institucion->nomInstitucion }}</td>
            <td class="px-4 py-3 text-center">
                <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 rounded">
                    {{ $institucion->codModular }}
                </span>
            </td>
            <td class="px-4 py-3">{{ $institucion->nivel ?? '-' }}</td>
            <td class="px-4 py-3">{{ $institucion->provincia ?? '-' }}</td>
            <td class="px-4 py-3">{{ $institucion->distrito ?? '-' }}</td>
            <td class="px-4 py-3">{{ $institucion->centropoblado ?? '-' }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $institucion->ugel ?? '-' }}</td>
            <td class="px-4 py-3 text-center no-export whitespace-nowrap">
                <form action="{{ route('institucions.destroy', $institucion->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta institución?');" class="inline-flex items-center justify-center gap-1 m-0">
                    <a href="{{ url('/institucions/' . $institucion->id . '/edit') }}" 
                       class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm transition text-xs" 
                       title="Editar">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </a>
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center justify-center p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded shadow-sm transition text-xs border-0 cursor-pointer" 
                            title="Eliminar">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </td>
        </tr>
    @empty
        {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
    @endforelse
</x-table-base>

@if ($institucions->hasPages())
    <div class="mt-3 flex justify-end">
        {{ $institucions->appends(request()->except('page'))->links() }}
    </div>
@endif

@stop

@section('js')
@vite(['resources/js/app.js'])
@stop
