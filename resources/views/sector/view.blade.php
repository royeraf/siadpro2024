@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Sector')

@section('content_header')
    <h1>Listado de sectores para Especialista DRE</h1>
@stop

@section('content')

<form action="{{route('buscarSectorGeneral')}}" method="get" class="row g-3">
    <div class="form-group col-md-2">
        <div class="col align-self-center">
            <div class="input-group-prepend">
                <span class="input-group-text">
                <i class="fas fa-file"></i>
                </span>
                <input type="text" class="form-control" name="texto" id="dniInput" placeholder="DNI">        
            </div>
        </div>
    </div>
    <div class="form-group col-md-2">
        <div class="col align-self-center">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-calendar-alt"></i>
                </span>
                <select class="form-control" id="year" name="year">
                    <option value="2025" {{ $selectedYear == 2025 ? 'selected' : '' }}>2025</option>
                    <option value="2024" {{ $selectedYear == 2024 ? 'selected' : '' }}>2024</option>
                    <option value="2023" {{ $selectedYear == 2023 ? 'selected' : '' }}>2023</option>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group col-md-3">
        <div class="col-md-15 col align-self-center">
            <div class="input-group-prepend">
            <span class="input-group-text">
                <i class="fas fa-calendar"></i>
            </span>
                <select class="form-control" id="ugels" name="ugels">
                    <option value=""> Seleccione la UGEL: </option>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group col-md-2">
        <div class="col align-self-center">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-building"></i>
                </span>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="institucion" name="" autocomplete="off">                    
                    
                </div>
                <div class="col-md-7">
                    <select name="instituciones" id="instituciones" class="form-control">
                        <option value="">Selecciona una institución</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group col-md-2">
        <!-- Lista desplegable para seleccionar El docente (se habilitará dinámicamente) -->
        <div class="col align-self-center">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-building"></i>
                </span>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="docente" name="" autocomplete="off">                    
                </div>
                <div class="col-md-7">
                    <select name="docentes" id="docentes" class="form-control">
                        <option value="">Selecciona un Docente</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group col-md-1">
        <input type="submit" class="btn btn-primary" value="Buscar">
    </div>    
</form>

<table id="sectores" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Nombre de la Asistencia</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Institución</th>
                    <th scope="col">Tipo de II.EE.</th>
                    <th scope="col">Provincia</th>
                    <th scope="col">Distrito</th>
                    <th scope="col">UGEL</th>
                    </tr>
                    </thead>
                    <tbody >
                    @if(count($sectores)<=0)
                    <tr>
                        <td colspan="8">No hay Asistencia Técnica</td>
                    </tr>
                    @else
                    @foreach ($sectores as $sector)
                    <tr>
                        <td>{{$sector->nombreSector}}</td>
                        <td>{{$sector->descripcion}}</td>
                        <td>{{date('d-m-Y', strtotime($sector->fecha))}}</td>
                        <td align="center"><a href="{{ route('sectores.download', $sector->id) }}" , target="_blank"><i class='{{$sector->documento}}' style='font-size:24px;color:{{$sector->color}}' ></i></a></td>
                        <td>{{$sector->name}}</td>
                        <td>{{$sector->cargo}}</td>
                        <td>{{$sector->institucion}}</td>
                        <td>{{$sector->nivelinstitucion}}</td>
                        <td>{{$sector->provincia}}</td>
                        <td>{{$sector->distrito}}</td>
                        <td>{{$sector->ugel}}</td>
                    </tr>
                    @endforeach
                    @endif
                    
                     </tbody>
                     </table>
                     <div class="form-inline">
                        <p>Total de Sectores Subidos: {{$sectores->total()}}</p> <br>
                        {{$sectores->appends(request()->only(['texto', 'instituciones', 'ugels', 'docentes', 'year']))->links()}}
                     </div>
                     
                          
                    
@stop

@section('css')
 
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script src=https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js></script>
<script src=https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js></script>
<script src=https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js></script>
<script>
$(document).ready(function() {
    // Función para obtener los parámetros actuales
    function getCurrentParams() {
        return new URLSearchParams(window.location.search).toString();
    }

    $('#sectores').DataTable({
        scrollX: true,
        dom: 'Bfrtip',
        buttons: [
            {
                text: '<i class="fas fa-file-excel"> Excel (Todos)</i>',
                className: 'btn btn-success',
                action: function (e, dt, node, config) {
                    window.location.href = "{{ route('exportar.sectores') }}?format=excel&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-file-csv"> CSV (Todos)</i>',
                className: 'btn btn-info',
                action: function (e, dt, node, config) {
                    window.location.href = "{{ route('exportar.sectores') }}?format=csv&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-print"> Imprimir (Todos)</i>',
                className: 'btn btn-warning',
                action: function (e, dt, node, config) {
                    window.open("{{ route('exportar.sectores') }}?format=excel&" + getCurrentParams());
                }
            },
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"> Copiar (Actual)</i>',
                className: 'btn btn-secondary'
            }
        ]
    });
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dniInput = document.getElementById('dniInput');

        dniInput.addEventListener('input', function() {
            const inputValue = dniInput.value.trim();
            const numericValue = inputValue.replace(/[^\d]/g, ''); // Elimina caracteres no numéricos

            if (numericValue.length > 8) {
                dniInput.value = numericValue.slice(0, 8); // Limita a 8 caracteres
            } else {
                dniInput.value = numericValue;
            }
        });
    });
</script>

<script>
    $(document).ready(function() {

    /* Ajax para la busqueda inicial de ugeles con cantidad de docentes que registraron agendas*/
    function cargarUgels() {
        var selectedYear = $('#year').val();
        
        $.ajax({
            url: "{{ route('get-ugels-sec') }}",
            method: 'GET',
            data: {
                year: selectedYear
            },
            dataType: 'json',
            success: function(data) {
                var $ugelsSelect = $('#ugels');
                $ugelsSelect.empty(); // Limpiar opciones existentes

                // Agregar la opción predeterminada
                $ugelsSelect.append($('<option>', {
                    value: '',
                    text: ' Seleccione la UGEL: '
                }));

                // Iterar sobre los datos recibidos y agregar las UGELs al select
                for (var i = 0; i < data.length; i++) {
                    var ugel = data[i].ugel;
                    var docentesCount = data[i].docentes_count;

                    // Crear una opción para cada UGEL con la cantidad de docentes
                    var $option = $('<option>', {
                        value: ugel,
                        text: ugel + ' (' + docentesCount + ' docente)'
                    });

                    // Agregar la opción al select
                    $ugelsSelect.append($option);
                }
            },
            error: function() {
                alert('Error al cargar las UGELs.');
            }
        });
    }
    
    // Cargar UGELs al inicio
    cargarUgels();
    
    // Recargar UGELs cuando cambia el año
    $('#year').on('change', function() {
        cargarUgels();
        // Limpiar los otros selectores
        $('#instituciones').empty().append('<option value="">Selecciona una institución</option>');
        $('#docentes').empty().append('<option value="">Selecciona un Docente</option>');
    });

    // Manejar el cambio en la lista de UGEL
    $('#ugels').on('change', function() {
        var selectedUgel = $(this).val();
        var selectedYear = $('#year').val();
        
        /* Ajax para la busqueda de institucion por ugel seleccionada con la informacion de docentes que registraron agendas*/ 
        $.ajax({
            url: "{{ route('buscarInstitucionporUgel-sec') }}", // Reemplaza con la ruta correcta en tu aplicación                
            method: 'GET',
            data: {
                ugel: selectedUgel,
                year: selectedYear,
                _token: "{{ csrf_token() }}" // Incluye el token CSRF
            },
            dataType: 'json',
            success: function(data) {
                console.log(data);
                var $institucionesSelect = $('#instituciones');
                $institucionesSelect.empty(); // Limpia las opciones existentes

                // Agrega la opción predeterminada
                $institucionesSelect.append($('<option>', {
                    value: '',
                    text: 'Selecciona una institución'
                }));

                // Itera a través de los datos y agrega las instituciones con sus cantidades
                for (var i = 0; i < data.length; i++) {
                    var institucion = data[i].nomInstitucion;
                    var docentesCount = data[i].agendas_count;
                    var totalDocentes = data[i].total_docentes;

                    // Crea una opción con el nombre de la institución y las cantidades
                    var $option = $('<option>', {
                        value: institucion,
                        text: institucion + ' (' + docentesCount + ' docentes, ' + totalDocentes + ' total)'
                    });

                    // Agrega la opción a la lista desplegable
                    $institucionesSelect.append($option);
                }

                $institucionesSelect.prop('disabled', false); // Habilita la lista desplegable
            },
            error: function() {
                alert('Error al cargar las instituciones.');
            }   
        });
    });


    $('#institucion').on('input', function() {
        var selectedUgel = $('#ugels').val();
        var searchTerm = $(this).val();
        var selectedYear = $('#year').val();
        
    /* Ajax para la busqueda en tiempo real de instituciones por ugel seleccionada con la informacion de docentes que registraron agendas*/
        $.ajax({
        url: "{{ route('buscarInstitucionesSector') }}",
        method: 'GET',
        data: {
            ugel: selectedUgel,
            term: searchTerm,
            year: selectedYear,
            _token: "{{ csrf_token() }}"
        },
        dataType: 'json',
        success: function(data) {
            console.log(data);
            var $institucionesSelect = $('#instituciones');
            $institucionesSelect.empty(); // Limpia las opciones existentes

            // Agregar la opción predeterminada
            $institucionesSelect.append($('<option>', {
                value: '',
                text: 'Selecciona una institución'
            }));

            for (var i = 0; i < data.length; i++) {
                var institucion = data[i].nomInstitucion;
                var agendasCount = data[i].agendas_count;
                var totalDocentes = data[i].total_docentes;

                // Crear una opción con el nombre del docente y la cantidad de agendas registradas
                var $option = $('<option>', {
                    value: institucion,
                    text: institucion + ' (' + agendasCount + ' docentes, ' + totalDocentes + ' total)'
                });
                // Si el docente tiene al menos una agenda, aplicar una clase CSS
                if (agendasCount > 0) {
                    $option.addClass('docente-con-agendas');
                }
                // Agregar la opción al elemento <select>
                $institucionesSelect.append($option);
            }
            // Habilitar el elemento <select>
            $institucionesSelect.prop('disabled', false);
        },
        error: function() {
            alert('Error al cargar las instituciones.');
            }
        });
    });
    
    $('#instituciones').on('change', function() {
        var selectedDocente = $(this).val();
        var selectedYear = $('#year').val();

        /* Ajax para la busqueda de docente por institucion seleccionada con la informacion de docentes que registraron agendas*/
        $.ajax({
            url: "{{ route('buscarDocenteporInstitucion-sec') }}",             
            method: 'GET',
            data: {
                docente: selectedDocente,
                year: selectedYear,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(data) {
                console.log(data);
                var $docentesSelect = $('#docentes');
                $docentesSelect.empty(); // Limpiar opciones existentes

                // Agregar la opción predeterminada
                $docentesSelect.append($('<option>', {
                    value: '',
                    text: 'Selecciona un Docente'
                }));

                for (var i = 0; i < data.length; i++) {
                    var docente = data[i].name;
                    var agendasCount = data[i].agendas_count;

                    // Crear una opción con el nombre del docente y la cantidad de agendas registradas
                    var $option = $('<option>', {
                        value: docente,
                        text: docente + ' (' + agendasCount + ' sectores)'
                    });
                    // Si el docente tiene al menos una agenda, aplicar una clase CSS
                    if (agendasCount > 0) {
                        $option.addClass('docente-con-agendas');
                    }
                    // Agregar la opción al elemento <select>
                    $docentesSelect.append($option);
                }
                // Habilitar el elemento <select>
                $docentesSelect.prop('disabled', false);
            },
            error: function() {
                alert('Error al cargar los docentes.');
            }
        });
    });

    $('#docente').on('input', function() {
        var selectedInstitucion = $('#instituciones').val();
        var searchTerm = $(this).val();
        var selectedYear = $('#year').val();

        /* Ajax para la busqueda en tiempo real de docentes con la informacion de docentes que registraron agendas*/
        $.ajax({
        url: "{{ route('buscarDocentesSector') }}",
        method: 'GET',
        data: {
            institucion: selectedInstitucion,
            term: searchTerm,
            year: selectedYear,
            _token: "{{ csrf_token() }}"
        },
        dataType: 'json',
        success: function(data) {
            console.log(data);
            var $docentesSelect = $('#docentes');
            $docentesSelect.empty(); // Limpiar opciones existentes

            // Agregar la opción predeterminada
            $docentesSelect.append($('<option>', {
                value: '',
                text: 'Selecciona un Docente'
            }));
            for (var i = 0; i < data.length; i++) {
                var docente = data[i].name;
                var agendasCount = data[i].agendas_count;

                // Crear una opción con el nombre del docente y la cantidad de agendas registradas
                var $option = $('<option>', {
                    value: docente,
                    text: docente + ' (' + agendasCount + ' sectores)'
                });
                // Si el docente tiene al menos una agenda, aplicar una clase CSS
                if (agendasCount > 0) {
                    $option.addClass('docente-con-agendas');
                }
                // Agregar la opción al elemento <select>
                $docentesSelect.append($option);
            }
            $('#docentes').prop('disabled', false);
        },
        error: function() {
            alert('Error al cargar las docentes.');
            }
        });
    });
    /* Ajax para la busqueda en tiempo real de docentes con la informacion de docentes que registraron agendas*/
});
</script>
@stop