@extends('adminlte::page')

@section('title', 'Instituciones')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
<style>
    /* Estilos para el contador de instituciones */
    .stats-card {
        background: linear-gradient(135deg, #007bff, #0056b3);
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
        color: rgba(255, 255, 255, 0.85);
    }
    .stats-number {
        font-size: 24px;
        font-weight: 700;
        display: block;
        color: #ffff00;
        line-height: 1.1;
    }
    .stats-title {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.95;
    }
    .filter-card {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .pagination {
        margin-bottom: 0;
    }
    .table-hover tbody tr:hover {
        background-color: #f1f7ff;
    }
</style>
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="m-0 text-dark"><i class="fas fa-university mr-2"></i>Listado de Instituciones</h1>
        <a href="{{ url('institucions/create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-1"></i> Nueva Institución
        </a>
    </div>
@stop

@section('content')

<!-- Contador de instituciones -->
<div class="row">
    <div class="col-12">
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-school"></i>
            </div>
            <div class="stats-info">
                <span class="stats-number">{{ number_format($total) }}</span>
                <span class="stats-title">Total de Instituciones Encontradas</span>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de búsqueda -->
<div class="card card-outline card-primary mb-4">
    <div class="card-header py-2">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-filter mr-1"></i> Filtros de Búsqueda
        </h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('institucion.index') }}">
            <div class="row">
                <!-- Filtro por Nombre / Nro de Institución -->
                <div class="col-md-4 col-sm-6 mb-3">
                    <label for="institucion" class="form-label font-weight-normal">
                        <i class="fas fa-school mr-1 text-muted"></i> Institución o Nro:
                    </label>
                    <input type="text" class="form-control" id="institucion" name="institucion" 
                           placeholder="Ej. Illathupa, 32004, etc." 
                           value="{{ request('institucion') }}">
                </div>

                <!-- Filtro por Código Modular -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <label for="codModular" class="form-label font-weight-normal">
                        <i class="fas fa-barcode mr-1 text-muted"></i> Cód. Modular:
                    </label>
                    <input type="text" class="form-control" id="codModular" name="codModular" 
                           placeholder="Ej. 0234567" 
                           value="{{ request('codModular') }}">
                </div>

                <!-- Filtro por UGEL -->
                <div class="col-md-3 col-sm-6 mb-3">
                    <label for="ugels" class="form-label font-weight-normal">
                        <i class="fas fa-map-marker-alt mr-1 text-muted"></i> UGEL:
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
                        <i class="fas fa-layer-group mr-1 text-muted"></i> Nivel:
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

            <div class="row align-items-center">
                <!-- Registros por página -->
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="d-flex align-items-center">
                        <label for="per_page" class="mr-2 mb-0 font-weight-normal text-muted">Mostrar:</label>
                        <select class="form-control form-control-sm" style="width: 80px;" id="per_page" name="per_page" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="ml-2 text-muted">por pág.</span>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="col-md-9 col-sm-6 text-md-right mb-2">
                    <button type="submit" class="btn btn-primary mr-1">
                        <i class="fas fa-search mr-1"></i> Buscar
                    </button>
                    <a href="{{ route('institucion.index') }}" class="btn btn-secondary">
                        <i class="fas fa-eraser mr-1"></i> Limpiar Filtros
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Instituciones -->
<div class="card shadow-sm">
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover table-bordered mb-0">
            <thead class="bg-primary text-white">
                <tr>
                    <th style="width: 60px;" class="text-center">ID</th>
                    <th>Institución</th>
                    <th style="width: 140px;" class="text-center">Cód. Modular</th>
                    <th>Nivel</th>
                    <th>Provincia</th>
                    <th>Distrito</th>
                    <th>Centro Poblado</th>
                    <th>UGEL</th>
                    <th style="width: 110px;" class="text-center">Opciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($institucions as $institucion)
                    <tr>
                        <td class="text-center font-weight-bold">{{ $institucion->id }}</td>
                        <td class="font-weight-bold text-primary">{{ $institucion->nomInstitucion }}</td>
                        <td class="text-center"><span class="badge badge-info">{{ $institucion->codModular }}</span></td>
                        <td>{{ $institucion->nivel ?? '-' }}</td>
                        <td>{{ $institucion->provincia ?? '-' }}</td>
                        <td>{{ $institucion->distrito ?? '-' }}</td>
                        <td>{{ $institucion->centropoblado ?? '-' }}</td>
                        <td><span class="badge badge-secondary">{{ $institucion->ugel ?? '-' }}</span></td>
                        <td class="text-center">
                            <form action="{{ route('institucions.destroy', $institucion->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta institución?');">
                                <a href="{{ url('/institucions/' . $institucion->id . '/edit') }}" class="btn btn-warning btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                            No se encontraron instituciones con los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginador Footer -->
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap py-3">
        <div class="text-muted mb-2 mb-md-0">
            @if ($institucions->total() > 0)
                Mostrando del <strong>{{ $institucions->firstItem() }}</strong> al <strong>{{ $institucions->lastItem() }}</strong> de un total de <strong>{{ number_format($institucions->total()) }}</strong> instituciones
            @else
                Mostrando 0 registros
            @endif
        </div>
        <div>
            {{ $institucions->links() }}
        </div>
    </div>
</div>

@stop
