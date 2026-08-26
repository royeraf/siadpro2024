@extends('adminlte::page')

@section('title', 'Asistencia')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-red card-outline">
                <div class="card-header"><h1 align="center">Crear Nueva Asistencia T茅cnica</h1></div>

                <div class="card-body">
                    <form method="POST" action="/evidencias" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group row">
                            <label for="nombreEvidencia" class="col-md-4 col-form-label text-md-right">{{ __('Nombre de la Asistencia') }}</label>

                            <div class="col-md-6">
                                <input id="nombreEvidencia" type="text" class="form-control @error('nombreEvidencia') is-invalid @enderror" required name="nombreEvidencia" autofocus>

                                @error('nombreEvidencia')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="descripcion" class="col-md-4 col-form-label text-md-right">{{ __('Descripci贸n') }}</label>

                            <div class="col-md-6">
                            <textarea id="descripcion" type="text" class="form-control @error('descripcion') is-invalid @enderror" name="descripcion" required autocomplete="descripcion"></textarea>

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
                                <input id="fecha" type="date" class="form-control @error('fecha') is-invalid @enderror" name="fecha" required>

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
                                <input id="documento" type="file" class="form-control-file @error('documento') is-invalid @enderror" name="documento"  autocomplete="documento" required>

                                <div id="error-message" class="alert alert-danger" style="display: none;">
                                    <strong>Registro no guardado: El archivo es superior a 2MB.</strong>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <a href="/evidencias" class="btn btn-danger" tabindex="4">Cancelar</a>
                                <button type="submit" class="btn btn-primary" id="guardarBtn">
                                    {{ __('Guardar') }}
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
<script>
    
    // Añadir un evento 'click' al botón de guardar
    document.getElementById('guardarBtn').addEventListener('click', function(event) {
        var archivo = document.getElementById('documento').files[0];
        var maxSize = 2 * 1024 * 1024; // 2MB en bytes
        var errorMessage = document.getElementById('error-message');

        // Verificar si el archivo es mayor a 2MB
        if (archivo && archivo.size > maxSize) {
            // Evitar que el formulario se envíe
            event.preventDefault();

            // Mostrar el mensaje de error
            errorMessage.style.display = 'block';

            // Añadir la clase 'is-invalid' al campo de entrada de archivo
            document.getElementById('documento').classList.add('is-invalid');
        } else {
            // Si el archivo es válido, ocultar el mensaje de error
            errorMessage.style.display = 'none';
            document.getElementById('documento').classList.remove('is-invalid');
        }
    });
</script>
<script>
    // Obt茅n el elemento del input de fecha
    var inputFecha = document.getElementById('fecha');
    
    // Obtiene la fecha actual en el formato "yyyy-mm-dd"
    var fechaActual = new Date().toISOString().slice(0, 10);

    // Establece el atributo "min" en el input para bloquear fechas posteriores
    inputFecha.setAttribute('max', fechaActual);
</script>
    
@stop