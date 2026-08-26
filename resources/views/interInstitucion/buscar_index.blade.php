

@extends('adminlte::page')
@section('css')

<link href=https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css>
<link href=https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css>
<link rel="stylesheet" href="/css/admin_custom.css">
<link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap5.min.css" rel="stylesheet">
{{-- <script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAWr9Iw50KF76coBkfruO2XvN8zEve8j30&callback=initMap&v=weekly"
    defer
></script>  --}}

@endsection

@section('title', 'internet de Institucion')

@section('content_header')
    <center><h1>Registro de Intituciones/Internet</h1></center>
@stop
@section('content')
<style>
    #map {
    height: 100%;
}

    #name{
        width: 100%;
        margin: 0 auto;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
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
        background-color: #063464;
    }
    #btn2:hover{
        background-color: #09460e;
    }
</style>


@switch($dat_user)

@case('Admin')

<div class="d-flex justify-content-center mt-2">
    <div class="d-flex align-items-center">
        <a href="{{ url('interInstitucion/buscar') }}" id="btn1" class="btn btn-primary btn-custom mr-2">Crear</a>
        <a href="{{ url('interInstitucion') }}" id="btn2" class="btn btn-success btn-custom">Listado Instituciones</a>
    </div>
</div>

<br>
<br>


<!-- Formulario de búsqueda -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Buscar Institución por Código Modular</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('interInstitucion.buscar_index') }}" method="post">
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
<div class="container" style="justify-content: center">
    <div class="row">
        @foreach ($datos_gen as $index => $dat)
            <div class="col-4" style="margin-top:17px">
                <div class="card" style="width: 20rem;">
                    <div id="map-{{$index}}" style="width: 320px; height: 200px;"></div>
                    <button class="btn btn-primary btn-sm" onclick="showMapModal({{$dat->coordenadaX}}, {{$dat->coordenadaY}}, {{$index}})">Click Aqui Para Abrir el Mapa Ampliado</button>
                    <div class="card-body">
                        <div>
                            <h5 class="card-title" id="name">{{$dat->nombreInstitucion}}</h5>
                        </div>
                        <br><br><p class="card-text">Codigo Modular: {{$dat->codigoModular}}</p>
                        <p class="card-text">Nivel  : {{$dat->nivelModalidad}}</p>
                        <p class="card-text">Ugel  : {{$dat->ugel}}</p>
                        <p class="card-text">Provincia  : {{$dat->provincia}}</p>
                        <p class="card-text">Distrito  : {{$dat->distrito}}</p>
                        <em><center><a href="{{ url('/interInstitucion/'.$dat->codigoModular.'/ver_mas') }}"><h5>Ver mas --></h5></a></center></em>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ url('/interInstitucion/'.$dat->codigoModular.'/edit') }}" class="btn btn-primary btn-lg">Editar</a>
                                </div>
                                <div class="col-6">
                                    <form action="{{ url('/interInstitucion/'.$dat->codigoModular) }}" method="post">
                                        @csrf
                                        {{ method_field('DELETE') }}
                                        <input class="btn btn-danger btn-lg" type="button" onclick="return confirm('¿Quieres Eliminar esta Informacion?')" value="Borrar">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
<script>
    let maps = []; // Array para almacenar los objetos de mapa
    let mapClickListeners = []; // Array para almacenar los controladores de eventos de clic en el mapa

    function initMap() {
        @foreach ($datos_gen as $index => $dat)
            const mapOptions{{$index}} = {
                zoom: 16,
                center: { lat: {{$dat->coordenadaX}}, lng: {{$dat->coordenadaY}} },
            };

            const map{{$index}} = new google.maps.Map(document.getElementById("map-{{$index}}"), mapOptions{{$index}});

            const marker{{$index}} = new google.maps.Marker({
                position: { lat: {{$dat->coordenadaX}}, lng: {{$dat->coordenadaY}} },
                map: map{{$index}},
            });

            maps.push(map{{$index}}); // Agregar el mapa al arreglo
        @endforeach
    }

    function showMapModal(lat, lng, index) {
        const modal = document.getElementById('mapModal');
        const modalBody = document.getElementById('mapModalBody');
        modalBody.innerHTML = ''; // Limpiar el contenido anterior del modal

        const mapOptions = {
            zoom: 16,
            center: { lat: lat, lng: lng },
        };
        const map = new google.maps.Map(modalBody, mapOptions);
        const marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map,
        });
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        initMapModal(map, lat, lng);
    }

    function initMapModal(map, lat, lng) {
        const marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map,
        });
        const infowindow = new google.maps.InfoWindow({
            content: "<p>Ubicación del marcador: " + marker.getPosition() + "</p>",
        });
        google.maps.event.addListener(marker, "click", () => {
            infowindow.open(map, marker);
        });
    }

    window.initMap = initMap;
</script>

<!-- Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">Mapa en Mayor Tamaño</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="mapModalBody" style="height: 400px;"></div>
        </div>
    </div>
</div>
@stop

@break



@case("Director")

        <div class="d-flex justify-content-center mt-2">
            <div class="d-flex align-items-center">
                <a href="{{ url('interInstitucion/buscar') }}" id="btn1" class="btn btn-primary btn-custom mr-2">Crear</a>
                <a href="{{ url('interInstitucion') }}" id="btn2" class="btn btn-success btn-custom">Listado Instituciones</a>
            </div>
        </div>

        <br>
        <br>


        <!-- Formulario de búsqueda -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Buscar Institución por Código Modular</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('interInstitucion.buscar_index') }}" method="post">
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
        <div class="container" style="justify-content: center">
            <div class="row">
                @foreach ($datos_gen as $index => $dat)
                    <div class="col-4" style="margin-top:17px">
                        <div class="card" style="width: 20rem;">
                            <div id="map-{{$index}}" style="width: 320px; height: 200px;"></div>
                            <button class="btn btn-primary btn-sm" onclick="showMapModal({{$dat->coordenadaX}}, {{$dat->coordenadaY}}, {{$index}})">Click Aqui Para Abrir el Mapa Ampliado</button>
                            <div class="card-body">
                                <div>
                                    <h5 class="card-title" id="name">{{$dat->nombreInstitucion}}</h5>
                                </div>
                                <br><br><p class="card-text">Codigo Modular: {{$dat->codigoModular}}</p>
                                <p class="card-text">Nivel  : {{$dat->nivelModalidad}}</p>
                                <p class="card-text">Ugel  : {{$dat->ugel}}</p>
                                <p class="card-text">Provincia  : {{$dat->provincia}}</p>
                                <p class="card-text">Distrito  : {{$dat->distrito}}</p>
                                <em><center><a href="{{ url('/interInstitucion/'.$dat->codigoModular.'/ver_mas') }}"><h5>Ver mas --></h5></a></center></em>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <script>
            let maps = []; // Array para almacenar los objetos de mapa
            let mapClickListeners = []; // Array para almacenar los controladores de eventos de clic en el mapa

            function initMap() {
                @foreach ($datos_gen as $index => $dat)
                    const mapOptions{{$index}} = {
                        zoom: 16,
                        center: { lat: {{$dat->coordenadaX}}, lng: {{$dat->coordenadaY}} },
                    };

                    const map{{$index}} = new google.maps.Map(document.getElementById("map-{{$index}}"), mapOptions{{$index}});

                    const marker{{$index}} = new google.maps.Marker({
                        position: { lat: {{$dat->coordenadaX}}, lng: {{$dat->coordenadaY}} },
                        map: map{{$index}},
                    });

                    maps.push(map{{$index}}); // Agregar el mapa al arreglo
                @endforeach
            }

            function showMapModal(lat, lng, index) {
                const modal = document.getElementById('mapModal');
                const modalBody = document.getElementById('mapModalBody');
                modalBody.innerHTML = ''; // Limpiar el contenido anterior del modal

                const mapOptions = {
                    zoom: 16,
                    center: { lat: lat, lng: lng },
                };
                const map = new google.maps.Map(modalBody, mapOptions);
                const marker = new google.maps.Marker({
                    position: { lat: lat, lng: lng },
                    map: map,
                });
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();

                initMapModal(map, lat, lng);
            }

            function initMapModal(map, lat, lng) {
                const marker = new google.maps.Marker({
                    position: { lat: lat, lng: lng },
                    map: map,
                });
                const infowindow = new google.maps.InfoWindow({
                    content: "<p>Ubicación del marcador: " + marker.getPosition() + "</p>",
                });
                google.maps.event.addListener(marker, "click", () => {
                    infowindow.open(map, marker);
                });
            }

            window.initMap = initMap;
        </script>

        <!-- Modal -->
        <div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mapModalLabel">Mapa en Mayor Tamaño</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="mapModalBody" style="height: 400px;"></div>
                </div>
            </div>
        </div>
        @stop

    @break

    @case('EspecDRE')

            <div class="d-flex justify-content-center mt-2">
                <div class="d-flex align-items-center">
                    <a href="{{ url('interInstitucion/buscar') }}" id="btn1" class="btn btn-primary btn-custom mr-2">Crear</a>
                    <a href="{{ url('interInstitucion') }}" id="btn2" class="btn btn-success btn-custom">Listado Instituciones</a>
                </div>
            </div>

            <br>
            <br>


            <!-- Formulario de búsqueda -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Buscar Institución por Código Modular</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('interInstitucion.buscar_index') }}" method="post">
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
            <div class="container" style="justify-content: center">
                <div class="row">
                    @foreach ($datos_gen as $index => $dat)
                        <div class="col-4" style="margin-top:17px">
                            <div class="card" style="width: 20rem;">
                                <div id="map-{{$index}}" style="width: 320px; height: 200px;"></div>
                                <button class="btn btn-primary btn-sm" onclick="showMapModal({{$dat->coordenadaX}}, {{$dat->coordenadaY}}, {{$index}})">Click Aqui Para Abrir el Mapa Ampliado</button>
                                <div class="card-body">
                                    <div>
                                        <h5 class="card-title" id="name">{{$dat->nombreInstitucion}}</h5>
                                    </div>
                                    <br><br><p class="card-text">Codigo Modular: {{$dat->codigoModular}}</p>
                                    <p class="card-text">Nivel  : {{$dat->nivelModalidad}}</p>
                                    <p class="card-text">Ugel  : {{$dat->ugel}}</p>
                                    <p class="card-text">Provincia  : {{$dat->provincia}}</p>
                                    <p class="card-text">Distrito  : {{$dat->distrito}}</p>
                                    <em><center><a href="{{ url('/interInstitucion/'.$dat->codigoModular.'/ver_mas') }}"><h5>Ver mas --></h5></a></center></em>
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-6">
                                                <a href="{{ url('/interInstitucion/'.$dat->codigoModular.'/edit') }}" class="btn btn-primary btn-lg">Editar</a>
                                            </div>
                                            <div class="col-6">
                                                <form action="{{ url('/interInstitucion/'.$dat->codigoModular) }}" method="post">
                                                    @csrf
                                                    {{ method_field('DELETE') }}
                                                    <input class="btn btn-danger btn-lg" type="button" onclick="return confirm('¿Quieres Eliminar esta Informacion?')" value="Borrar">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <script>
                let maps = []; // Array para almacenar los objetos de mapa
                let mapClickListeners = []; // Array para almacenar los controladores de eventos de clic en el mapa

                function initMap() {
                    @foreach ($datos_gen as $index => $dat)
                        const mapOptions{{$index}} = {
                            zoom: 16,
                            center: { lat: {{$dat->coordenadaX}}, lng: {{$dat->coordenadaY}} },
                        };

                        const map{{$index}} = new google.maps.Map(document.getElementById("map-{{$index}}"), mapOptions{{$index}});

                        const marker{{$index}} = new google.maps.Marker({
                            position: { lat: {{$dat->coordenadaX}}, lng: {{$dat->coordenadaY}} },
                            map: map{{$index}},
                        });

                        maps.push(map{{$index}}); // Agregar el mapa al arreglo
                    @endforeach
                }

                function showMapModal(lat, lng, index) {
                    const modal = document.getElementById('mapModal');
                    const modalBody = document.getElementById('mapModalBody');
                    modalBody.innerHTML = ''; // Limpiar el contenido anterior del modal

                    const mapOptions = {
                        zoom: 16,
                        center: { lat: lat, lng: lng },
                    };
                    const map = new google.maps.Map(modalBody, mapOptions);
                    const marker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: map,
                    });
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();

                    initMapModal(map, lat, lng);
                }

                function initMapModal(map, lat, lng) {
                    const marker = new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: map,
                    });
                    const infowindow = new google.maps.InfoWindow({
                        content: "<p>Ubicación del marcador: " + marker.getPosition() + "</p>",
                    });
                    google.maps.event.addListener(marker, "click", () => {
                        infowindow.open(map, marker);
                    });
                }

                window.initMap = initMap;
            </script>

            <!-- Modal -->
            <div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="mapModalLabel">Mapa en Mayor Tamaño</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="mapModalBody" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
            @stop

        @break

        @case('EspecUGEL')

                    <div class="d-flex justify-content-center mt-2">
                    <div class="d-flex align-items-center">
                        <a href="{{ url('interInstitucion/buscar') }}" id="btn1" class="btn btn-primary btn-custom mr-2">Crear</a>
                        <a href="{{ url('interInstitucion') }}" id="btn2" class="btn btn-success btn-custom">Listado Instituciones</a>
                    </div>
                </div>

                <br>
                <br>


                <!-- Formulario de búsqueda -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Buscar Institución por Código Modular</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('interInstitucion.buscar_index') }}" method="post">
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
                <div class="container" style="justify-content: center">
                    <div class="row">
                        @foreach ($datos_gen as $index => $dat)
                            <div class="col-4" style="margin-top:17px">
                                <div class="card" style="width: 20rem;">
                                    <div id="map-{{$index}}" style="width: 320px; height: 200px;"></div>
                                    <button class="btn btn-primary btn-sm" onclick="showMapModal({{$dat->coordenadaX}}, {{$dat->coordenadaY}}, {{$index}})">Click Aqui Para Abrir el Mapa Ampliado</button>
                                    <div class="card-body">
                                        <div>
                                            <h5 class="card-title" id="name">{{$dat->nombreInstitucion}}</h5>
                                        </div>
                                        <br><br><p class="card-text">Codigo Modular: {{$dat->codigoModular}}</p>
                                        <p class="card-text">Nivel  : {{$dat->nivelModalidad}}</p>
                                        <p class="card-text">Ugel  : {{$dat->ugel}}</p>
                                        <p class="card-text">Provincia  : {{$dat->provincia}}</p>
                                        <p class="card-text">Distrito  : {{$dat->distrito}}</p>
                                        <em><center><a href="{{ url('/interInstitucion/'.$dat->codigoModular.'/ver_mas') }}"><h5>Ver mas --></h5></a></center></em>
                                        <div class="container-fluid">
                                            <div class="row">
                                                <div class="col-6">
                                                    <a href="{{ url('/interInstitucion/'.$dat->codigoModular.'/edit') }}" class="btn btn-primary btn-lg">Editar</a>
                                                </div>
                                                <div class="col-6">
                                                    <form action="{{ url('/interInstitucion/'.$dat->codigoModular) }}" method="post">
                                                        @csrf
                                                        {{ method_field('DELETE') }}
                                                        <input class="btn btn-danger btn-lg" type="button" onclick="return confirm('¿Quieres Eliminar esta Informacion?')" value="Borrar">
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <script>
                    let maps = []; // Array para almacenar los objetos de mapa
                    let mapClickListeners = []; // Array para almacenar los controladores de eventos de clic en el mapa

                    function initMap() {
                        @foreach ($datos_gen as $index => $dat)
                            const mapOptions{{$index}} = {
                                zoom: 16,
                                center: { lat: {{$dat->coordenadaX}}, lng: {{$dat->coordenadaY}} },
                            };

                            const map{{$index}} = new google.maps.Map(document.getElementById("map-{{$index}}"), mapOptions{{$index}});

                            const marker{{$index}} = new google.maps.Marker({
                                position: { lat: {{$dat->coordenadaX}}, lng: {{$dat->coordenadaY}} },
                                map: map{{$index}},
                            });

                            maps.push(map{{$index}}); // Agregar el mapa al arreglo
                        @endforeach
                    }

                    function showMapModal(lat, lng, index) {
                        const modal = document.getElementById('mapModal');
                        const modalBody = document.getElementById('mapModalBody');
                        modalBody.innerHTML = ''; // Limpiar el contenido anterior del modal

                        const mapOptions = {
                            zoom: 16,
                            center: { lat: lat, lng: lng },
                        };
                        const map = new google.maps.Map(modalBody, mapOptions);
                        const marker = new google.maps.Marker({
                            position: { lat: lat, lng: lng },
                            map: map,
                        });
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();

                        initMapModal(map, lat, lng);
                    }

                    function initMapModal(map, lat, lng) {
                        const marker = new google.maps.Marker({
                            position: { lat: lat, lng: lng },
                            map: map,
                        });
                        const infowindow = new google.maps.InfoWindow({
                            content: "<p>Ubicación del marcador: " + marker.getPosition() + "</p>",
                        });
                        google.maps.event.addListener(marker, "click", () => {
                            infowindow.open(map, marker);
                        });
                    }

                    window.initMap = initMap;
                </script>

                <!-- Modal -->
                <div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="mapModalLabel">Mapa en Mayor Tamaño</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="mapModalBody" style="height: 400px;"></div>
                        </div>
                    </div>
                </div>
                @stop
            @break

@default
    <!-- Agrega el mensaje "Usuario no autorizado" para otros roles -->
        @php
            $mensajeNoAutorizado = "Usuario no autorizado para este proceso";
        @endphp
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <p>{{ $mensajeNoAutorizado }}</p>
                </div>
            </div>
        </div>
    @stop
@endswitch
