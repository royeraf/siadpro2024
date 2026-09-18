@extends('adminlte::page')

@section('title', 'Lectura')

@section('content_header')
    
@stop

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-cyan card-outline">
                <div class="card-header"><h1 align="center">Editar Espacio de Lectura en el Hogar</h1></div>

                <div class="card-body">
                    <form method="POST" action="{{ route('plans.update', $plan->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group row">
                            <label for="nombrePlan" class="col-md-4 col-form-label text-md-right">{{ __('Nombre del plan') }}</label>

                            <div class="col-md-6">
                                <input id="nombrePlan" type="text" class="form-control @error('nombrePlan') is-invalid @enderror" name="nombrePlan" value="{{ $plan->nombrePlan }}" required autocomplete="nombrePlan" autofocus>

                                @error('nombrePlan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="descripcion" class="col-md-4 col-form-label text-md-right">{{ __('Descripción') }}</label>

                            <div class="col-md-6">
                            <textarea id="descripcion" type="text" class="form-control @error('descripcion') is-invalid @enderror" name="descripcion" required autocomplete="descripcion" required>{{ $plan->descripcion }}</textarea>

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
                                <input id="fecha" type="date" class="form-control @error('fecha') is-invalid @enderror" value="{{ $plan->fecha }}" name="fecha" required>

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
                                @if(!empty($plan->enlace))
                                    <div class="mb-2 p-2 bg-light border rounded d-flex align-items-center justify-content-between">
                                        <div class="text-truncate mr-2">
                                            <i class="{{ $plan->documento }} mr-1" style="font-size: 18px; color: {{ $plan->color }}"></i>
                                            <span class="text-muted small">Actual:</span>
                                            <span class="font-weight-bold small" title="{{ basename($plan->enlace) }}">{{ basename($plan->enlace) }}</span>
                                        </div>
                                        <a href="{{ route('plans.download', $plan->id) }}" class="btn btn-xs btn-outline-primary" title="Descargar actual">
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
                                <a href="/plans" class="btn btn-danger" tabindex="4">Cancelar</a>
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
