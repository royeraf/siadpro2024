@extends('adminlte::page')

@section('css')
<link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap5.min.css" rel="stylesheet">

@endsection

@section('title', 'Internet de Institucion')

@section('content_header')
<style>
    .custom-title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        text-transform: uppercase;
        text-align: center;
    }
</style>
<style>
    /* Estilos personalizados solo para los botones con la clase .btn-custom */
    .btn-custom {
        font-size: 18px;
        border-radius: 5px;
        padding: 10px 20px;
        margin: 5px;
        min-width: 200px;
        /* Agrega cualquier otro estilo personalizado que desees */
    }

</style>
<h1 class="custom-title">Actualizacion de Registro</h1>
@stop

@section('content')
<style>
    .custom-link {
        color: #007bff;
        font-weight: bold;
        text-decoration: none;
    }
    .custom-link:hover {
        text-decoration: underline;
    }
</style>

<a href="/interInstitucion" class="btn btn-primary">Listado de Instituciones/Internet</a>
<br><br>
@if (Session::has('success_message'))
    <div class="alert alert-success">
        {{ Session::get('success_message') }}
    </div>
@endif

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informacion de la Institucion</h3>
                    </div>

                @isset($info)
                                @foreach ($info as $Informacion)


                                <div class="card-body">
                                    <form action="{{ url("/interInstitucion/".$Informacion->id) }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        {{ method_field('PATCH') }}
                                        <div class="form-group">
                                            <label for="Codigo_Modular_Data">Código Modular:</label>
                                            <input type="text" id="Codigo_Modular_Data" name="Codigo_Modular_Data" value="{{ $Informacion->codigoModular }}" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="NombreInstitucion">Nombre de la Institución:</label>
                                            <input type="text" id="NombreInstitucion" name="NombreInstitucion" value="{{ $Informacion->nombreInstitucion }}" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="Nivel_Modalidad">Nivel/Modalidad:</label>
                                            <input type="text" id="Nivel_Modalidad" name="Nivel_Modalidad" value="{{ $Informacion->nivelModalidad }}" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Departamento/Provincia/Distrito:</label>
                                            <input type="text" id="departamento" name="departamento" value="{{ $Informacion->departamento }}" class="form-control" readonly>
                                            <input type="text" id="provincia" name="provincia" value="{{ $Informacion->provincia }}" class="form-control" readonly>
                                            <input type="text" id="distrito" name="distrito" value="{{ $Informacion->distrito }}" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="centroPoblado">Centro Poblado:</label>
                                            <input type="text" id="centroPoblado" name="centroPoblado" value="{{ $Informacion->centroPoblado }}" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="ugel">UGEL:</label>
                                            <input type="text" id="ugel" name="ugel" value="{{ $Informacion->ugel }}" class="form-control" readonly>
                                        </div>
                            </div>
                        </div>
                    </div>


            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Registro de Internet</h3>
                    </div>
                    <div class="card-body">
                            <div class="form-group">
                                <label for="Proveedor">Proveedor de Internet:</label>
                                <input type="text" id="Proveedor" name="Proveedor" class="form-control" value="{{ $Informacion->proveedorServicio }}"  required>
                            </div>
                            <div class="form-group">
                                <label for="tipo_linea">Tipo de Línea:</label>
                                <select id="tipo_linea" name="tipo_linea" class="form-control" value="{{ $Informacion->tipoLinea }}" required onchange="habilitarCampoOtros()">
                                    <option value="{{ $Informacion->tipoLinea }}">{{ $Informacion->tipoLinea }}</option>
                                    <option value="Domestica">Domestica</option>
                                    <option value="Satelital">Satelital</option>
                                    <option value="Comercial">Comercial</option>
                                    <option value="Dedicada">Dedicada</option>
                                    <option value="Domentica">Domentica</option>
                                    <option value="Wifi">Wifi</option>
                                    <option value="FibraOptica">Fibra Óptica</option>
                                    <option value="ConexionDirecta">Conexión Directa</option>
                                    <option value="Otros">Otros</option>
                                </select>
                            </div>

                            <div class="form-group" id="otrosCampo" style="display: none;">
                                <label for="otros">Especificar:</label>
                                <input type="text" id="otros" name="otros" class="form-control" placeholder="Especificar">
                            </div>

                            <script>
                                function habilitarCampoOtros() {
                                    var tipoLinea = document.getElementById('tipo_linea').value;
                                    var otrosCampo = document.getElementById('otrosCampo');

                                    if (tipoLinea === 'Otros') {
                                        otrosCampo.style.display = 'block'; // Muestra el campo de entrada
                                    } else {
                                        otrosCampo.style.display = 'none'; // Oculta el campo de entrada
                                    }
                                }
                            </script>
                            <div class="form-group">
                                <label for="Megas_Contratadas">Megas Contratadas:</label>
                                <div class="input-group">
                                    <input type="number" id="Megas_Contratadas" name="Megas_Contratadas" class="form-control" value="{{ $Informacion->megasContratadas }}" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">mb</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="costoMensual">Costo Mensual:</label>
                                <div class="input-group">
                                    <input type="number" id="costoMensual" name="costoMensual" class="form-control" value="{{ $Informacion->costoMensual }}" oninput="calcularCostoAnual()" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">S/.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="costoAnual">Costo Anual:</label>
                                <div class="input-group">
                                    <input type="number" id="costoAnual" name="costoAnual" class="form-control" value="{{ $Informacion->costoAnual }}" disabled>
                                    <div class="input-group-append">
                                        <span class="input-group-text">S/.</span>
                                    </div>
                                </div>
                            </div>

                            <script>
                                function calcularCostoAnual() {
                                    var costoMensual = parseFloat(document.getElementById('costoMensual').value);
                                    var costoAnual = costoMensual * 12;
                                    document.getElementById('costoAnual').value = costoAnual.toFixed(2); // Asegura que el valor tenga 2 decimales
                                }
                            </script>
                            <div class="form-group">
                                <label for="Coordenada_x">Coordenadas:</label>
                                <div class="input-group">
                                    <label for="Coordenada_x">Latitud:</label>
                                        <input type="text" id="Coordenada_x" name="Coordenada_x" class="form-control" value="{{ $Informacion->coordenadaX }}" required>
                                    <label for="Coordenada_y">Longitud:</label>
                                        <input type="text" id="Coordenada_y" name="Coordenada_y" class="form-control" value="{{ $Informacion->coordenadaY }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Documentación</h3>
                        </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="tipoDocumento">Tipo de Documento:</label>
                                    <select id="tipoDocumento" name="tipoDocumento" class="form-control" value="{{ $Informacion->tipoDocumento }}" required>
                                        <option value="{{ $Informacion->tipoDocumento }}">{{ $Informacion->tipoDocumento }}</option>
                                        <option value="contrato">Contrato</option>
                                        <option value="resolucion">Resolución</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="fechaInstalacion">Fecha Instalación:</label>
                                    <input type="date" id="fechaInstalacion" name="fechaInstalacion" class="form-control" value="{{ $Informacion->fechaInstalacion }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="inicioContrato">Inicio de Contrato:</label>
                                    <input type="date" id="inicioContrato" name="inicioContrato" class="form-control" value="{{ $Informacion->inicioContrato }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="finalContrato">Final de Contrato:</label>
                                    <input type="date" id="finalContrato" name="finalContrato" class="form-control" value="{{ $Informacion->finalContrato }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="nmrNombreResolucion">Nmr/Nombre:</label>
                                    <input type="text" id="nmrNombreResolucion" name="nmrNombreResolucion" class="form-control" value="{{ $Informacion->nmrNombreResolucion }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="descripcion">Descripción:</label>
                                    <textarea type="text" id="descripcion" name="descripcion" class="form-control"  cols="30" rows="3" required>{{ $Informacion->descripcionResolucion }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="archivoDocumento">Archivo de Documento (PDF):</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="archivoDocumento" name="archivoDocumento" accept=".pdf" value="{{ $Informacion->archivoPDF }}">
                                            <label class="custom-file-label" for="archivoDocumento">{{ $Informacion->archivoPDF }}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="far fa-file-pdf"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    document.addEventListener("DOMContentLoaded", function () {
                                        const inputFile = document.querySelector(".custom-file-input");
                                        const fileLabel = document.querySelector(".custom-file-label");

                                        inputFile.addEventListener("change", function () {
                                            const fileName = this.files[0].name;
                                            fileLabel.innerText = fileName;
                                        });
                                    });
                                </script>
                                <div class="d-flex justify-content-center mt-2">
                                    <div class="d-flex align-items-center">
                                        <button type="submit" class="btn btn-primary btn-custom mr-2">Guardar</button>
                                        <a href="{{ url('/interInstitucion') }}" id="btn1" class="btn btn-danger btn-custom mr-2" onclick="return confirm('¿Esta seguro de Cancelar?')">Cancelar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endisset
@stop


