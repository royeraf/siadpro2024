@extends('adminlte::page')

@section('title', 'Usuarios')

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
        <h1 class="m-0 text-dark"><i data-lucide="users" class="w-6 h-6 mr-2 inline-block align-text-bottom"></i>Listado de Usuarios</h1>
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
    $filtrosActuales = request()->only(['texto', 'cargos', 'ugel']);
@endphp

<!-- Tabs Activos / Inhabilitados -->
<div class="flex items-center gap-2 border-b border-gray-200 mb-4">
    @foreach ([
        '1' => [
            'label' => 'Activos',
            'icon' => 'user-check',
            'count' => $conteos['1'] ?? 0,
            'active' => 'border-emerald-600 text-emerald-700 bg-emerald-50 rounded-t-md',
            'badgeActive' => 'bg-emerald-600 text-white',
        ],
        '0' => [
            'label' => 'Inhabilitados',
            'icon' => 'user-x',
            'count' => $conteos['0'] ?? 0,
            'active' => 'border-rose-600 text-rose-700 bg-rose-50 rounded-t-md',
            'badgeActive' => 'bg-rose-600 text-white',
        ],
    ] as $valor => $tab)
        <a href="{{ route('users.index', array_merge($filtrosActuales, ['estado' => $valor])) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition
               {{ $estado === (string) $valor
                   ? $tab['active']
                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <i data-lucide="{{ $tab['icon'] }}" class="w-4 h-4"></i>
            {{ $tab['label'] }}
            <span class="inline-flex items-center justify-center min-w-[1.75rem] px-2 py-0.5 text-xs font-bold rounded-full
                {{ $estado === (string) $valor ? $tab['badgeActive'] : 'bg-gray-100 text-gray-600' }}">
                {{ number_format($tab['count']) }}
            </span>
        </a>
    @endforeach
</div>

<!-- Contador de usuarios -->
<div class="row">
    <div class="col-12">
        <div class="stats-card">
            <div class="stats-icon">
                <i data-lucide="users" class="w-8 h-8"></i>
            </div>
            <div class="stats-info">
                <span class="stats-number">{{ number_format($users->total()) }}</span>
                <span class="stats-title">Total de Usuarios {{ $estado === '1' ? 'Activos' : 'Inhabilitados' }} Encontrados</span>
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
        <form method="GET" action="{{ route('users.index') }}">
            <input type="hidden" name="estado" value="{{ $estado }}">
            <div class="row">
                <!-- Filtro por DNI -->
                <div class="col-md-4 col-sm-6 mb-3">
                    <label for="texto" class="form-label font-weight-normal">
                        <i data-lucide="id-card" class="w-4 h-4 mr-1 inline-block align-text-bottom text-muted"></i> DNI:
                    </label>
                    <input type="text" class="form-control" id="texto" name="texto"
                           placeholder="Ingrese DNI"
                           value="{{ request('texto') }}">
                </div>

                <!-- Filtro por Cargo -->
                <div class="col-md-4 col-sm-6 mb-3">
                    <label for="cargos" class="form-label font-weight-normal">
                        <i data-lucide="briefcase" class="w-4 h-4 mr-1 inline-block align-text-bottom text-muted"></i> Cargo:
                    </label>
                    <input type="text" class="form-control" id="cargos" name="cargos"
                           placeholder="Ingrese el Cargo"
                           value="{{ request('cargos') }}">
                </div>

                <!-- Filtro por UGEL -->
                <div class="col-md-4 col-sm-6 mb-3">
                    <label for="ugel" class="form-label font-weight-normal">
                        <i data-lucide="map-pin" class="w-4 h-4 mr-1 inline-block align-text-bottom text-muted"></i> UGEL:
                    </label>
                    <select class="form-control" id="ugel" name="ugel">
                        <option value="">-- Todas las UGEL --</option>
                        @foreach ($listaUgels as $ugelItem)
                            <option value="{{ $ugelItem }}" {{ request('ugel') == $ugelItem ? 'selected' : '' }}>
                                {{ $ugelItem }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row align-items-center mt-2">
                <div class="col-12 text-right mb-2">
                    <div class="inline-flex items-center gap-3 mr-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-md shadow-sm transition border-0 cursor-pointer mr-2">
                            <i data-lucide="search" class="w-4 h-4 mr-2 inline-block align-text-bottom"></i> Buscar
                        </button>
                        <a href="{{ route('users.index', ['estado' => $estado]) }}" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 active:bg-gray-700 text-white text-sm font-semibold rounded-md shadow-sm transition text-decoration-none">
                            <i data-lucide="eraser" class="w-4 h-4 mr-2 inline-block align-text-bottom"></i> Limpiar Filtros
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla Base Reutilizable con Tailwind CSS y Alpine.js -->
<x-table-base id="tabla-usuarios" :perPage="request('per_page', 10)" :exportable="true" :searchable="true" :exportFilename="$estado === '1' ? 'usuarios_activos' : 'usuarios_inhabilitados'" :serverPaginated="true">
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

    @forelse ($users as $user)
        <tr data-table-row class="hover:bg-blue-50 transition border-b border-gray-100">
            <td class="px-4 py-3 text-center">
                @if($user->estado == 1)
                    <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded">Activo</span>
                @else
                    <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800 rounded">Inactivo</span>
                @endif
            </td>
            <td class="px-4 py-3 text-center font-bold text-gray-900">{{ $user->id }}</td>
            <td class="px-4 py-3">
                <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 rounded">
                    {{ $user->dni }}
                </span>
            </td>
            <td class="px-4 py-3 font-semibold text-blue-600">{{ $user->name }}</td>
            <td class="px-4 py-3">{{ $user->email }}</td>
            <td class="px-4 py-3">{{ $user->cargo ?? '-' }}</td>
            <td class="px-4 py-3">{{ $user->institucion ?? '-' }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $user->ugel ?? '-' }}</td>
            <td class="px-4 py-3">{{ $user->nivelinstitucion ?? '-' }}</td>
            <td class="px-4 py-3">{{ $user->provincia ?? '-' }}</td>
            <td class="px-4 py-3">{{ $user->distrito ?? '-' }}</td>
            <td class="px-4 py-3 text-center no-export whitespace-nowrap">
                <div class="inline-flex items-center justify-center gap-1">
                    <a href="{{ url('/users/' . $user->id . '/edit') }}"
                       class="inline-flex items-center justify-center p-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded shadow-sm transition text-xs"
                       title="Asignar rol">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ url('/usuarios/' . $user->id . '/edit') }}"
                       class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm transition text-xs"
                       title="Editar datos">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </a>
                    @if($user->estado == 1)
                        <a href="{{ route('cambiarEstado', $user->id) }}"
                           onclick="return confirm('¿Está seguro de desactivar a {{ addslashes($user->name) }}?');"
                           class="inline-flex items-center justify-center p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded shadow-sm transition text-xs"
                           title="Desactivar">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </a>
                    @else
                        <a href="{{ route('cambiarEstado', $user->id) }}"
                           class="inline-flex items-center justify-center p-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded shadow-sm transition text-xs"
                           title="Activar">
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        {{-- Fila vacía manejada por el motor o en caso de 0 registros del servidor --}}
    @endforelse
</x-table-base>

@if ($users->hasPages())
    <div class="mt-3 flex justify-end">
        {{ $users->appends(request()->except('page'))->links() }}
    </div>
@endif

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
