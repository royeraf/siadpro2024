@extends('adminlte::page')

@section('css')
<link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAWr9Iw50KF76coBkfruO2XvN8zEve8j30&callback=initMap&v=weekly"
    defer
></script>

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

<h1 class="custom-title">Registro de Instituciones/Internet</h1>
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

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Buscar Institución por Código Modular</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('interInstitucion.buscar') }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="Codigo_Modular">Código Modular:</label>
                                <div class="input-group">
                                    <input type="number" name="Codigo_Modular" id="Codigo_Modular" class="form-control" placeholder="Código Modular">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">Buscar</button>

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        {{--@isset($rev)
            @if (count($rev) > 0)
                <div class="card mt-6">
                    <div class="card-body">
                        <h4 class="card-title">La Institucion ya se encuentra Registrada</h4>
                    </div>
                </div>
            @elseif (count($rev) == 0)--}}
                @isset($datos)
                    @if(count($datos) == 0)
                        <div class="card mt-6">
                            <div class="card-body">
                                <h4 class="card-title">No se encuentran resultados con los criterios de búsqueda.</h4>
                            </div>
                        </div>
                    @elseif(count($datos) > 0)
                        <div class="card mt-6">
                                <div class="card-header">
                                    <h5 class="card-title">Resultados de búsqueda:</h5>
                                </div>
                                @foreach ($datos as $result)
                                <div class="card-body">
                                    <form action="{{ url('interInstitucion') }}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group">
                                            <label for="Codigo_Modular_Data">Código Modular:</label>
                                            <input type="text" id="Codigo_Modular_Data" name="Codigo_Modular_Data" value="{{ $result->codModular }}" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="NombreInstitucion">Nombre de la Institución:</label>
                                            <input type="text" id="NombreInstitucion" name="NombreInstitucion" value="{{ $result->nomInstitucion }}" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="Nivel_Modalidad">Nivel/Modalidad:</label>
                                            <input type="text" id="Nivel_Modalidad" name="Nivel_Modalidad" value="{{ $result->nivel }}" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Departamento/Provincia/Distrito:</label>
                                            <input type="text" id="departamento" name="departamento" value="Huánuco" class="form-control" readonly>
                                            <input type="text" id="provincia" name="provincia" value="{{ $result->provincia }}" class="form-control" readonly>
                                            <input type="text" id="distrito" name="distrito" value="{{ $result->distrito }}" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="centroPoblado">Centro Poblado:</label>
                                            <input type="text" id="centroPoblado" name="centroPoblado" value="{{ $result->centropoblado }}" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="ugel">UGEL:</label>
                                            <input type="text" id="ugel" name="ugel" value="{{ $result->ugel }}" class="form-control" readonly>
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
                                <input type="text" id="Proveedor" name="Proveedor" class="form-control" placeholder="Proveedor" required>
                            </div>
                            <div class="form-group">
                                <label for="tipo_linea">Tipo de Línea:</label>
                                <select id="tipo_linea" name="tipo_linea" class="form-control" required onchange="habilitarCampoOtros()">
                                    <option>Seleccionar</option>
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
                                    <input type="number" id="Megas_Contratadas" name="Megas_Contratadas" class="form-control" placeholder="Megas Contratadas" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">mb</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="costoMensual">Costo Mensual:</label>
                                <div class="input-group">
                                    <input type="number" id="costoMensual" name="costoMensual" class="form-control" placeholder="Costo Mensual" oninput="calcularCostoAnual()" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">S/.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="costoAnual">Costo Anual:</label>
                                <div class="input-group">
                                    <input type="number" id="costoAnual" name="costoAnual" class="form-control" placeholder="Costo Anual" disabled>
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
                                <label for="Coordenada_x">Coordenadas de Ubicacion de la Institucion:</label>
                                <div class="input-group">
                                    <input type="text" id="latitude" name="latitude" class="form-control" placeholder="Latitud" required>
                                    <input type="text" id="longitude" name="longitude" class="form-control" placeholder="Longitud" required>
                                    <input type="button" id="getLocationBtn" value="Obtener Ubicacion">
                                </div>
                            </div>
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                const getLocationBtn = document.getElementById("getLocationBtn");
                                const latitudeOutput = document.getElementById("latitude");
                                const longitudeOutput = document.getElementById("longitude");
                                const addressOutput = document.getElementById("address");


                                getLocationBtn.addEventListener("click", function() {
                                    if ("geolocation" in navigator) {
                                        navigator.geolocation.getCurrentPosition(function(position) {
                                            const latitude = position.coords.latitude;
                                            const longitude = position.coords.longitude;

                                            latitudeOutput.value = latitude;
                                            longitudeOutput.value = longitude;

                                            const apiKey = "AIzaSyAWr9Iw50KF76coBkfruO2XvN8zEve8j30"; // Reemplaza esto con tu clave de API
                                            const apiUrl = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${apiKey}`;


                                            fetch(apiUrl)
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.results.length > 0) {
                                                        addressOutput.textContent = data.results[0].formatted_address;
                                                    } else {
                                                        addressOutput.textContent = "No se encontró la dirección";
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error("Error:", error);
                                                });
                                        });
                                    } else {
                                        alert("La geolocalización no está disponible en este navegador.");
                                    }
                                });
                            });
                            </script>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Documentación</h3>
                        </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="tipoDocumento">Tipo de Documento:</label>
                                    <select id="tipoDocumento" name="tipoDocumento" class="form-control" required>
                                        <option>Seleccionar</option>
                                        <option value="contrato">Contrato</option>
                                        <option value="resolucion">Resolución</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="inicioContrato">Inicio de Contrato:</label>
                                    <input type="date" id="inicioContrato" name="inicioContrato" class="form-control" placeholder="Inicio de Contrato" required>
                                </div>
                                <div class="form-group">
                                    <label for="finalContrato">Final de Contrato:</label>
                                    <input type="date" id="finalContrato" name="finalContrato" class="form-control" placeholder="Final de Contrato" required>
                                </div>
                                <div class="form-group">
                                    <label for="fechaInstalacion">Fecha Instalación:</label>
                                    <input type="date" id="fechaInstalacion" name="fechaInstalacion" class="form-control" placeholder="Fecha Instalación" required>
                                </div>
                                <div class="form-group">
                                    <label for="nmrNombreResolucion">Nmr/Nombre del Documento:</label>
                                    <input type="text" id="nmrNombreResolucion" name="nmrNombreResolucion" class="form-control" placeholder="Nmr/Nombre" required>
                                </div>
                                <div class="form-group">
                                    <label for="descripcion">Descripción:</label>
                                    <textarea type="text" id="descripcion" name="descripcion" class="form-control" placeholder="Descripción" cols="30" rows="3" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="archivoDocumento">Archivo de Documento (PDF):</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="archivoDocumento" name="archivoDocumento" accept=".pdf" required>
                                            <label class="custom-file-label" for="archivoDocumento">Arrastra aquí un archivo o haz clic para examinar</label>
                                        </div>
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="far fa-file-pdf"></i></span>
                                            <span class="input-group-text" onclick="borrarPDF()"><i class="fa fa-minus"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    function borrarPDF() {
                                                const inputFile = document.querySelector(".custom-file-input");
                                                const fileLabel = document.querySelector(".custom-file-label");

                                                inputFile.value = null;
                                                fileLabel.innerText = "Arrastra aquí un archivo o haz clic para examinar";
                                                        }

                                    document.addEventListener("DOMContentLoaded", function () {
                                        const inputFile = document.querySelector(".custom-file-input");
                                        const fileLabel = document.querySelector(".custom-file-label");

                                        inputFile.addEventListener("change", function () {
                                            const fileName = this.files[0].name;
                                            fileLabel.innerText = fileName;
                                        });
                                    });
                                </script>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Registrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
@endforeach
@endif
@endisset
{{-- @endif
@endisset --}}
@stop


