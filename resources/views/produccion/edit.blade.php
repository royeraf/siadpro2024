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
                                <input id="documento" type="file" class="form-control-file @error('documento') is-invalid @enderror" name="documento" value="{{ old('documento') }}" autocomplete="documento" required>

                                @error('documento')
                                    <span class="invalid-feedback" role="alert">
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