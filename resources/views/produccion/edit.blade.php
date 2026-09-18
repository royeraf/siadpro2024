@extends('adminlte::page')

@section('title', 'Evidencia')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-green card-outline">
                <div class="card-header"><h1 align="center">Editar Producción de Textos Infantiles</h1></div>

                <div class="card-body">
                    <form method="POST" action="{{ route('produccions.update', $produccion->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group row">
                            <label for="nombreProduccion" class="col-md-4 col-form-label text-md-right">{{ __('Nombre de la Producción') }}</label>

                            <div class="col-md-6">
                                <input id="nombreProduccion" type="text" class="form-control @error('nombreProduccion') is-invalid @enderror" name="nombreProduccion" value="{{ $produccion->nombreProduccion }}" required autocomplete="nombreProduccion" autofocus>

                                @error('nombreProduccion')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="descripcion" class="col-md-4 col-form-label text-md-right">{{ __('Descripción') }}</label>

                            <div class="col-md-6">
                            <textarea id="descripcion" type="text" class="form-control @error('descripcion') is-invalid @enderror" name="descripcion" required autocomplete="descripcion" required>{{ $produccion->descripcion }}</textarea>

                                @error('descripcion')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="fecha" class="col-md-4 col-form-label text-md-right">{{ __('Fecha') }}</label>

                            <div class="col-md-6">
                                <input id="fecha" type="date" class="form-control @error('fecha') is-invalid @enderror" value="{{ $produccion->fecha }}" name="fecha" required>

                                @error('fecha')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>










                        <div class="form-group row">
                            <label for="documento" class="col-md-4 col-form-label text-md-right">{{ __('Documento') }}</label>

                            <div class="col-md-6">
                                @if(!empty($produccion->enlace))
                                    <div class="mb-2 p-2 bg-light border rounded d-flex align-items-center justify-content-between">
                                        <div class="text-truncate mr-2">
                                            <i class="{{ $produccion->documento }} mr-1" style="font-size: 18px; color: {{ $produccion->color }}"></i>
                                            <span class="text-muted small">Actual:</span>
                                            <span class="font-weight-bold small" title="{{ basename($produccion->enlace) }}">{{ basename($produccion->enlace) }}</span>
                                        </div>
                                        <a href="{{ route('produccions.download', $produccion->id) }}" class="btn btn-xs btn-outline-primary" title="Descargar actual">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                @endif
                                <input id="documento" type="file" class="form-control-file @error('documento') is-invalid @enderror" name="documento" autocomplete="documento">
                                <small class="form-text text-muted">Dejar vacío para conservar el archivo actual (máx. 10MB).</small>

                                @error('documento')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <a href="/produccions" class="btn btn-danger" tabindex="4">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Guardar cambios') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    
@stop

@section('js')
    
@stop