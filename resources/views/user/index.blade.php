@extends('adminlte::page')

@section('title', 'Usuarios')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@vite(['resources/css/app.css'])
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <x-section-heading icon="users">Listado de Usuarios</x-section-heading>
        <a href="{{ url('users/create') }}" class="btn btn-primary">
            <i data-lucide="user-plus" class="w-4 h-4 mr-1 inline-block align-text-bottom"></i> Nuevo Usuario
        </a>
    </div>
@stop

@section('content')

@if(session('success'))
    <div id="alert-success" class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@php
    $filtrosActuales = request()->only(['texto', 'cargos', 'ugel', 'buscar']);
@endphp

<!-- Tabs Activos / Inhabilitados Destacadas -->
<div class="mb-4">
    <div class="flex flex-col sm:inline-flex sm:flex-row p-1.5 bg-gray-100 border border-gray-300 rounded-xl shadow-sm gap-2">
        <!-- Tab Activos -->
        <a href="{{ route('users.index', array_merge($filtrosActuales, ['estado' => '1'])) }}"
           class="inline-flex items-center justify-center gap-2.5 px-4 py-2 rounded-lg text-sm font-bold transition-all
               {{ $estado === '1'
                   ? 'bg-emerald-600 text-white shadow-md ring-2 ring-emerald-500/20'
                   : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' }}">
            <i data-lucide="user-check" class="w-5 h-5"></i>
            <span>Usuarios Activos</span>
            <span class="inline-flex items-center justify-center min-w-[1.75rem] px-2 py-0.5 text-xs font-black rounded-full
                {{ $estado === '1' ? 'bg-white text-emerald-800 shadow-sm' : 'bg-emerald-200 text-emerald-900' }}">
                {{ number_format($conteos['1'] ?? 0) }}
            </span>
        </a>

        <!-- Tab Inhabilitados -->
        <a href="{{ route('users.index', array_merge($filtrosActuales, ['estado' => '0'])) }}"
           class="inline-flex items-center justify-center gap-2.5 px-4 py-2 rounded-lg text-sm font-bold transition-all
               {{ $estado === '0'
                   ? 'bg-rose-600 text-white shadow-md ring-2 ring-rose-500/20'
                   : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}">
            <i data-lucide="user-x" class="w-5 h-5"></i>
            <span>Usuarios Inhabilitados</span>
            <span class="inline-flex items-center justify-center min-w-[1.75rem] px-2 py-0.5 text-xs font-black rounded-full
                {{ $estado === '0' ? 'bg-white text-rose-800 shadow-sm' : 'bg-rose-200 text-rose-900' }}">
                {{ number_format($conteos['0'] ?? 0) }}
            </span>
        </a>
    </div>
</div>

<x-stats-card icon="users" id="tabla-usuarios" :value="$users->total()"
              :title="'Total de Usuarios ' . ($estado === '1' ? 'Activos' : 'Inhabilitados') . ' Encontrados'" />

<!-- Tabla Base Reutilizable con Tailwind CSS y Alpine.js -->
<x-table-base id="tabla-usuarios"
              :perPage="request('per_page', 10)"
              :exportable="true"
              :searchable="true"
              :exportFilename="$estado === '1' ? 'usuarios_activos' : 'usuarios_inhabilitados'"
              :exportUrl="route('exportUsers', request()->all())"
              :serverPaginated="true"
              :totalServerRecords="$users->total()"
              :fromServer="$users->firstItem() ?? 0"
              :toServer="$users->lastItem() ?? 0"
              :filterAction="route('users.index')"
              :filterParams="['estado' => $estado]">
    <x-slot name="filters">
        <x-table-filter name="texto" label="DNI" icon="id-card" placeholder="Ingrese DNI" />
        <x-table-filter name="cargos" label="Cargo" icon="briefcase" placeholder="Ingrese el Cargo" />
        <x-table-filter name="ugel" label="UGEL" icon="map-pin" :options="$listaUgels" placeholder="-- Todas las UGEL --" />
    </x-slot>
    <x-slot name="header">
        <tr>
            <th @click="sortBy(0)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition" style="width: 90px;">
                <div class="flex items-center justify-between">
                    <span>Estado</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 0 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 0 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(1)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition" style="width: 70px;">
                <div class="flex items-center justify-between">
                    <span>ID</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 1 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 1 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(2)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition" style="width: 120px;">
                <div class="flex items-center justify-between">
                    <span>DNI</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 2 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 2 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(3)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Usuario</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 3 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 3 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(4)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Correo</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 4 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 4 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(5)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Cargo</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 5 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 5 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(6)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Institución</span>
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
            <th @click="sortBy(8)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Tipo de II.EE</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 8 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 8 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(9)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Provincia</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 9 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 9 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(10)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Distrito</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 10 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 10 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th class="px-4 py-3 text-center no-export" style="width: 140px;">
                Opciones
            </th>
        </tr>
    </x-slot>

    @include('user._rows')
</x-table-base>

<x-table-pagination id="tabla-usuarios" :paginator="$users" />

@stop

@section('js')
@vite(['resources/js/app.js'])
<script>
    setTimeout(function() {
        var alertEl = document.getElementById('alert-success');
        if (alertEl) {
            alertEl.classList.remove('show');
        }
    }, 4000);
</script>
@stop
