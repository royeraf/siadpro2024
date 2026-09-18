@extends('adminlte::page')

@section('title', 'Accion')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-yellow card-outline">
                <div class="card-header"><h1 align="center">Editar Acción de Sensibilización</h1></div>

                <div class="card-body">
                    <form method="POST" action="{{ route('accions.update', $accion->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group row">
                            <label for="nombreAccion" class="col-md-4 col-form-label text-md-right">{{ __('Nombre de la Acción') }}</label>

                            <div class="col-md-6">
                                <input id="nombreAccion" type="text" class="form-control @error('nombreAccion') is-invalid @enderror" name="nombreAccion" value="{{ $accion->nombreAccion }}" required autocomplete="nombreAccion" autofocus>

                                @error('nombreAccion')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="lugar" class="col-md-4 col-form-label text-md-right">{{ __('Lugar') }}</label>

                            <div class="col-md-6">
                                <input id="lugar" type="text" class="form-control @error('lugar') is-invalid @enderror" name="lugar" value="{{ old('lugar', $accion->lugar) }}" required autocomplete="lugar">

                                @error('lugar')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="descripcion" class="col-md-4 col-form-label text-md-right">{{ __('Descripción') }}</label>

                            <div class="col-md-6">
                                <textarea id="descripcion" class="form-control @error('descripcion') is-invalid @enderror" name="descripcion" required autocomplete="descripcion">{{ old('descripcion', $accion->descripcion) }}</textarea>

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
                                <input id="fecha" type="date" class="form-control @error('fecha') is-invalid @enderror" value="{{ $accion->fecha }}" name="fecha" required>

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
                                @if(!empty($accion->enlace))
                                    <div class="mb-2 p-2 bg-light border rounded d-flex align-items-center justify-content-between">
                                        <div class="text-truncate mr-2">
                                            <i class="{{ $accion->documento }} mr-1" style="font-size: 18px; color: {{ $accion->color }}"></i>
                                            <span class="text-muted small">Actual:</span>
                                            <span class="font-weight-bold small" title="{{ basename($accion->enlace) }}">{{ basename($accion->enlace) }}</span>
                                        </div>
                                        <a href="{{ route('accions.download', $accion->id) }}" class="btn btn-xs btn-outline-primary" title="Descargar actual">
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
                                <a href="/accions" class="btn btn-danger" tabindex="4">Cancelar</a>
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