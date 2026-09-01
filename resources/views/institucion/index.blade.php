@extends('adminlte::page')

@section('title', 'Instituciones')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@vite(['resources/css/app.css'])
@endsection

@section('content_header')
    <x-page-header icon="landmark" title="Listado de Instituciones" />
@stop

@section('content')

<x-stats-card icon="school" id="tabla-instituciones" :value="$total"
              title="Total de Instituciones Encontradas" />

<div class="flex justify-start mb-3">
    <x-create-button :href="route('institucions.create')">Nueva Institución</x-create-button>
</div>

<!-- Tabla Base Reutilizable con Tailwind CSS y Alpine.js -->
<x-table-base id="tabla-instituciones"
              :perPage="request('per_page', 15)"
              :exportable="true"
              :searchable="true"
              exportFilename="instituciones"
              :exportUrl="route('exportInstituciones', request()->all())"
              :serverPaginated="true"
              :totalServerRecords="$institucions->total()"
              :fromServer="$institucions->firstItem() ?? 0"
              :toServer="$institucions->lastItem() ?? 0"
              :filterAction="route('institucion.index')">
    <x-slot name="filters">
        <x-table-filter name="institucion" label="Institución o Nro" icon="school" placeholder="Ej. Illathupa, 32004, etc." />
        <x-table-filter name="codModular" label="Cód. Modular" icon="barcode" placeholder="Ej. 0234567" />
        <x-table-filter name="ugels" label="UGEL" icon="map-pin" :options="$listaUgels" placeholder="-- Todas las UGEL --" />
        <x-table-filter name="nivel" label="Nivel" icon="layers" :options="$listaNiveles" placeholder="-- Todos --" />
    </x-slot>
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

    @include('institucion._rows')
</x-table-base>

<x-table-pagination id="tabla-instituciones" :paginator="$institucions" />

@stop

@section('js')
@vite(['resources/js/app.js'])
@stop
