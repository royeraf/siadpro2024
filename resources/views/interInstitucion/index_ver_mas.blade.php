@extends('adminlte::page')

@section('css')
<link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap5.min.css" rel="stylesheet">

{{-- <script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAWr9Iw50KF76coBkfruO2XvN8zEve8j30&callback=initMap&v=weekly"
    defer
></script>  --}}

<style>
    /* Estilos personalizados */
    .map-container {
        height: 550px;
    }
    .custom-title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        text-transform: uppercase;
        text-align: center;
    }
    .custom-link {
        color: #007bff;
        font-weight: bold;
        text-decoration: none;
    }
    .custom-link:hover {
        text-decoration: underline;
    }
    .record-divider {
        margin-top: 20px;
        margin-bottom: 20px;
    }
</style>
<style>
    /* Estilos personalizados solo para los botones con la clase .btn-custom */
    .btn-custom {
        font-size: 18px;
        border-radius: 5px;
        padding: 10px 20px;
        margin: 5px;
        min-width: 450px;
        /* Agrega cualquier otro estilo personalizado que desees */
    }

    #btn1:hover{
        background-color: #0d6e09;
    }
    #btn2:hover{
        background-color: #580505;
    }
</style>
<style>
    .subrayado {
        text-decoration: underline; /* Aplica un subrayado al texto */
        /* Otros estilos opcionales */
        /* color: blue; */ /* Cambiar el color del subrayado */
        /* font-weight: bold; */ /* Cambiar el peso del texto */
        font-weight: bold;
        color: rgb(235, 48, 48);
    }

    .card-title{
        display: flex;
        justify-content: center;
        align-items: center;
    }
    #btn3:hover{
        background-color: #063464;
    }
    #btn4:hover{
        background-color: #063464;
    }
</style>
@endsection

@section('js')
<script>
    let maps = [];

    function initMap() {
        @foreach ($dat_inst as $index => $dat)
            const opcionesMapa{{$index}} = {
                zoom: 16,
                center: { lat: {{$dat->coordenadaX}}, lng: {{$dat->coordenadaY}} },
            };

            const mapa{{$index}} = new google.maps.Map(document.getElementById("map-{{$index}}"), opcionesMapa{{$index}});

            const marcador{{$index}} = new google.maps.Marker({
                position: { lat: {{$dat->coordenadaX}}, lng: {{$dat->coordenadaY}} },
                map: mapa{{$index}},
            });

            maps.push(mapa{{$index}});
        @endforeach
    }

    window.initMap = initMap;
</script>
@endsection

@section('title', 'Internet de Institución')

@section('content_header')
<h1 class="custom-title">Visualizacion de la Institucion</h1>
@stop

@section('content')
<div class="container">


    @switch($tipo_rol)
        @case('Admin')

        <div class="d-flex justify-content-center mt-2">
            <div class="d-flex align-items-center">
                <a href="{{ url('interInstitucion/buscar') }}" id="btn3" class="btn btn-primary btn-custom mr-2">Crear</a>
                <a href="{{ url('interInstitucion') }}" id="btn4" class="btn btn-primary btn-custom">Volver</a><br>
            </div>
        </div>

        @foreach ($dat_inst as $index => $datos)
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row" style="display: flex; justify-content: center; align-items: center;">
                        <h4 class="subrayado">{{ $datos->nombreInstitucion }}</h4>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div id="map-{{$index}}" class="map-container"></div>
                        </div>
                        <div class="col-md-6">
                            <div style="display: flex; justify-content: center; align-items: center; text-decoration: underline;">
                                <h5 class="card-title">DATOS GENERALES</h3><br><br>
                            </div>
                            <div class="form-group">
                                <label><strong>Código Modular:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->codigoModular }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Departamento:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->departamento }}" readonly>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label><strong>Provincia:</strong></label>
                                        <input type="text" class="form-control" value="{{ $datos->provincia }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label><strong>Distrito:</strong></label>
                                        <input type="text" class="form-control" value="{{ $datos->distrito }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><strong>Centro Poblado:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->centroPoblado }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>UGEL:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->ugel }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Usuario Creador de los Datos Registrados:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->usuario }}" readonly>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div style="display: flex; justify-content: center; align-items: center;">
                                <h3 class="card-title">INFORMACION DEL SERVICIO</h3><br><br>
                            </div>
                            <div class="form-group">
                                <label><strong>Proveedor de Servicio:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->proveedorServicio }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Megas Contratadas:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->megasContratadas }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Costo Mensual:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->costoMensual }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Costo Anual:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->costoAnual }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Tipo de Línea:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->tipoLinea }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Latitud:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->coordenadaX }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Longitud:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->coordenadaY }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="display: flex; justify-content: center; align-items: center; ">
                                <h3 class="card-title">DOCUMENTACIÓN</h3><br><br>
                            </div>
                            <div class="form-group">
                                <label><strong>Fecha de Instalación:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->fechaInstalacion }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Inicio de Contrato:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->inicioContrato }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Final de Contrato:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->finalContrato }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Tipo de Documento:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->tipoDocumento }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Número de Resolución:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->nmrNombreResolucion }}" readonly>
                            </div>
                            <div class="form-group">
                                <label><strong>Descripción de Resolución:</strong></label>
                                <input type="text" class="form-control" value="{{ $datos->descripcionResolucion }}" readonly>
                            </div>

                            <div class="form-group">
                                <label><strong>Archivo PDF:</strong></label>

                                @if ($datos->archivoPDF)
                                    <br>
                                    <div style="display: flex; align-items: center;">
                                        <div>
                                            <a href="{{ asset('archivos_pdf/'.$dat->archivoPDF) }}" target="_blank">
                                                Ver PDF
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    Sin PDF
                                @endif
                            </div>

                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-center mt-2">
                        <div class="d-flex align-items-center">
                            <a href="{{ url('/interInstitucion/'.$datos->id.'/edit') }}" id="btn1" class="btn btn-success btn-custom mr-2">Editar</a>
                            <form action="{{  route ('interInstitucion.destroy',$datos->id)}}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input class="btn btn-danger btn-custom mr-2" type="submit" onclick="return confirm('¿Quieres Eliminar esta Informacion?')" value="Borrar">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            @break
        @case('EspecDRE')

            <div class="d-flex justify-content-center mt-2">
                <div class="d-flex align-items-center">
                    <a href="{{ url('interInstitucion/buscar') }}" id="btn3" class="btn btn-primary btn-custom mr-2">Crear</a>
                    <a href="{{ url('interInstitucion') }}" id="btn4" class="btn btn-primary btn-custom">Volver</a><br>
                </div>
            </div>

            @foreach ($dat_inst as $index => $datos)
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row" style="display: flex; justify-content: center; align-items: center;">
                            <h4 class="subrayado">{{ $datos->nombreInstitucion }}</h4>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div id="map-{{$index}}" class="map-container"></div>
                            </div>
                            <div class="col-md-6">
                                <div style="display: flex; justify-content: center; align-items: center; text-decoration: underline;">
                                    <h5 class="card-title">DATOS GENERALES</h3><br><br>
                                </div>
                                <div class="form-group">
                                    <label><strong>Código Modular:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->codigoModular }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Departamento:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->departamento }}" readonly>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label><strong>Provincia:</strong></label>
                                            <input type="text" class="form-control" value="{{ $datos->provincia }}" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label><strong>Distrito:</strong></label>
                                            <input type="text" class="form-control" value="{{ $datos->distrito }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><strong>Centro Poblado:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->centroPoblado }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>UGEL:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->ugel }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Usuario Creador de los Datos Registrados:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->usuario }}" readonly>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div style="display: flex; justify-content: center; align-items: center;">
                                    <h3 class="card-title">INFORMACION DEL SERVICIO</h3><br><br>
                                </div>
                                <div class="form-group">
                                    <label><strong>Proveedor de Servicio:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->proveedorServicio }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Megas Contratadas:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->megasContratadas }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Costo Mensual:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->costoMensual }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Costo Anual:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->costoAnual }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Tipo de Línea:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->tipoLinea }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Latitud:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->coordenadaX }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Longitud:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->coordenadaY }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="display: flex; justify-content: center; align-items: center; ">
                                    <h3 class="card-title">DOCUMENTACIÓN</h3><br><br>
                                </div>
                                <div class="form-group">
                                    <label><strong>Fecha de Instalación:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->fechaInstalacion }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Inicio de Contrato:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->inicioContrato }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Final de Contrato:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->finalContrato }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Tipo de Documento:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->tipoDocumento }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Número de Resolución:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->nmrNombreResolucion }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Descripción de Resolución:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->descripcionResolucion }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Archivo PDF:</strong></label>
                                    @if ($datos->archivoPDF)
                                        <br>
                                        <div style="display: flex; align-items: center;">
                                            <div style="margin-right: 10px;">
                                                <a href="{{ asset('archivos_pdf/'.$datos->archivoPDF) }}" target="_blank">
                                                    <img src="{{ asset('archivos_pdf/'.$datos->archivoPDF.'.png') }}" alt="Vista previa del PDF" style="max-width: 100px; height: auto;">
                                                </a>
                                            </div>
                                            <div>
                                                <a href="{{ asset('archivos_pdf/'.$datos->archivoPDF) }}" target="_blank">Ver PDF</a>
                                            </div>
                                        </div>
                                    @else
                                        Sin PDF
                                    @endif
                                </div>

                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-center mt-2">
                            <div class="d-flex align-items-center">
                                <a href="{{ url('/interInstitucion/'.$datos->id.'/edit') }}" id="btn1" class="btn btn-success btn-custom mr-2">Editar</a>
                                <form action="{{  route ('interInstitucion.destroy',$datos->id)}}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input class="btn btn-danger btn-custom mr-2" type="submit" onclick="return confirm('¿Quieres Eliminar esta Informacion?')" value="Borrar">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

            @break

        @case('EspecUGEL')

            <div class="d-flex justify-content-center mt-2">
                <div class="d-flex align-items-center">
                    <a href="{{ url('interInstitucion/buscar') }}" id="btn3" class="btn btn-primary btn-custom mr-2">Crear</a>
                    <a href="{{ url('interInstitucion') }}" id="btn4" class="btn btn-primary btn-custom">Volver</a><br>
                </div>
            </div>

            @foreach ($dat_inst as $index => $datos)
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row" style="display: flex; justify-content: center; align-items: center;">
                            <h4 class="subrayado">{{ $datos->nombreInstitucion }}</h4>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div id="map-{{$index}}" class="map-container"></div>
                            </div>
                            <div class="col-md-6">
                                <div style="display: flex; justify-content: center; align-items: center; text-decoration: underline;">
                                    <h5 class="card-title">DATOS GENERALES</h3><br><br>
                                </div>
                                <div class="form-group">
                                    <label><strong>Código Modular:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->codigoModular }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Departamento:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->departamento }}" readonly>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label><strong>Provincia:</strong></label>
                                            <input type="text" class="form-control" value="{{ $datos->provincia }}" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label><strong>Distrito:</strong></label>
                                            <input type="text" class="form-control" value="{{ $datos->distrito }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><strong>Centro Poblado:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->centroPoblado }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>UGEL:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->ugel }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Usuario Creador de los Datos Registrados:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->usuario }}" readonly>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div style="display: flex; justify-content: center; align-items: center;">
                                    <h3 class="card-title">INFORMACION DEL SERVICIO</h3><br><br>
                                </div>
                                <div class="form-group">
                                    <label><strong>Proveedor de Servicio:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->proveedorServicio }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Megas Contratadas:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->megasContratadas }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Costo Mensual:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->costoMensual }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Costo Anual:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->costoAnual }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Tipo de Línea:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->tipoLinea }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Latitud:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->coordenadaX }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Longitud:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->coordenadaY }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="display: flex; justify-content: center; align-items: center; ">
                                    <h3 class="card-title">DOCUMENTACIÓN</h3><br><br>
                                </div>
                                <div class="form-group">
                                    <label><strong>Fecha de Instalación:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->fechaInstalacion }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Inicio de Contrato:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->inicioContrato }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Final de Contrato:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->finalContrato }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Tipo de Documento:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->tipoDocumento }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Número de Resolución:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->nmrNombreResolucion }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Descripción de Resolución:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->descripcionResolucion }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Archivo PDF:</strong></label>
                                    @if ($datos->archivoPDF)
                                        <br>
                                        <div style="display: flex; align-items: center;">
                                            <div style="margin-right: 10px;">
                                                <a href="{{ asset('archivos_pdf/'.$datos->archivoPDF) }}" target="_blank">
                                                    <img src="{{ asset('archivos_pdf/'.$datos->archivoPDF.'.png') }}" alt="Vista previa del PDF" style="max-width: 100px; height: auto;">
                                                </a>
                                            </div>
                                            <div>
                                                <a href="{{ asset('archivos_pdf/'.$datos->archivoPDF) }}" target="_blank">Ver PDF</a>
                                            </div>
                                        </div>
                                    @else
                                        Sin PDF
                                    @endif
                                </div>

                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-center mt-2">
                            <div class="d-flex align-items-center">
                                <a href="{{ url('/interInstitucion/'.$datos->id.'/edit') }}" id="btn1" class="btn btn-success btn-custom mr-2">Editar</a>
                                <form action="{{  route ('interInstitucion.destroy',$datos->id)}}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input class="btn btn-danger btn-custom mr-2" type="submit" onclick="return confirm('¿Quieres Eliminar esta Informacion?')" value="Borrar">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

            @break

        @case('Director')

            <div class="d-flex justify-content-center mt-2">
                <div class="d-flex align-items-center">
                    <a href="{{ url('interInstitucion/buscar') }}" id="btn3" class="btn btn-primary btn-custom mr-2">Crear</a>
                    <a href="{{ url('interInstitucion') }}" id="btn4" class="btn btn-primary btn-custom">Volver</a><br>
                </div>
            </div>

            @foreach ($dat_inst as $index => $datos)
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row" style="display: flex; justify-content: center; align-items: center;">
                            <h4 class="subrayado">{{ $datos->nombreInstitucion }}</h4>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div id="map-{{$index}}" class="map-container"></div>
                            </div>
                            <div class="col-md-6">
                                <div style="display: flex; justify-content: center; align-items: center; text-decoration: underline;">
                                    <h5 class="card-title">DATOS GENERALES</h3><br><br>
                                </div>
                                <div class="form-group">
                                    <label><strong>Código Modular:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->codigoModular }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Departamento:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->departamento }}" readonly>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label><strong>Provincia:</strong></label>
                                            <input type="text" class="form-control" value="{{ $datos->provincia }}" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label><strong>Distrito:</strong></label>
                                            <input type="text" class="form-control" value="{{ $datos->distrito }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><strong>Centro Poblado:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->centroPoblado }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>UGEL:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->ugel }}" readonly>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div style="display: flex; justify-content: center; align-items: center;">
                                    <h3 class="card-title">INFORMACION DEL SERVICIO</h3><br><br>
                                </div>
                                <div class="form-group">
                                    <label><strong>Proveedor de Servicio:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->proveedorServicio }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Megas Contratadas:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->megasContratadas }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Costo Mensual:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->costoMensual }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Costo Anual:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->costoAnual }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Tipo de Línea:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->tipoLinea }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Latitud:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->coordenadaX }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Longitud:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->coordenadaY }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="display: flex; justify-content: center; align-items: center; ">
                                    <h3 class="card-title">DOCUMENTACIÓN</h3><br><br>
                                </div>
                                <div class="form-group">
                                    <label><strong>Fecha de Instalación:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->fechaInstalacion }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Inicio de Contrato:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->inicioContrato }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Final de Contrato:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->finalContrato }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Tipo de Documento:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->tipoDocumento }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Número de Resolución:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->nmrNombreResolucion }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Descripción de Resolución:</strong></label>
                                    <input type="text" class="form-control" value="{{ $datos->descripcionResolucion }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label><strong>Archivo PDF:</strong></label>
                                    @if ($datos->archivoPDF)
                                        <br>
                                        <div style="display: flex; align-items: center;">
                                            <div>
                                                <a href="{{ asset('archivos_pdf/'.$datos->archivoPDF) }}" target="_blank">Ver PDF</a>
                                            </div>
                                        </div>
                                    @else
                                        Sin PDF
                                    @endif
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-center mt-2">
                            <div class="d-flex align-items-center">
                                <a href="{{ url('/interInstitucion/'.$datos->id.'/edit') }}" id="btn1" class="btn btn-success btn-custom mr-2">Editar</a>
                                <form action="{{  route ('interInstitucion.destroy',$datos->id)}}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input class="btn btn-danger btn-custom mr-2" type="submit" onclick="return confirm('¿Quieres Eliminar esta Informacion?')" value="Borrar">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

            @break

        @default

    @endswitch
</div>
@endsection


