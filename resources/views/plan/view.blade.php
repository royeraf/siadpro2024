@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="{{ asset("vendor/datatables/css/jquery.dataTables.css") }}" />
<link href="{{ asset("vendor/datatables/css/dataTables.bootstrap5.min.css") }}" rel="stylesheet">
@endsection

@section('title', 'Lectura')

@section('content_header')
    <h1>Listado de Espacio de Lectura en el Hogar para Especialista DRE</h1>
@stop

@section('content')

<form action="{{route('buscarPlanGeneral')}}" method="get" class="row g-3">
    <div class="form-group col-md-2">
        <div class="col align-self-center">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-calendar-alt"></i>
                </span>
                <select class="form-control" id="year" name="year">
                    <option value="2023" {{ isset($selectedYear) && $selectedYear == 2023 ? 'selected' : '' }}>2023</option>
                    <option value="2024" {{ isset($selectedYear) && $selectedYear == 2024 ? 'selected' : '' }}>2024</option>
                    <option value="2025" {{ !isset($selectedYear) || $selectedYear == 2025 ? 'selected' : '' }}>2025</option>
                </select>
            </div>
        </div>
    </div>
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
    <div class="form-group col-md-3">
        <div class="col-md-15 col align-self-center">
            <div class="input-group-prepend">
            <span class="input-group-text">
                <i class="fas fa-building"></i>
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
                    <i class="fas fa-school"></i>
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
                    <i class="fas fa-user"></i>
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

<table id="plans" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
    <thead class="bg-primary text-white">
        <tr>   
            <th scope="col">Nombre de Espacio de Lectura</th>
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
    <tbody>
        @if(count($plans)<=0)
            <tr>
                <td colspan="11">No hay Espacio de Lectura en el Hogar</td>
            </tr>
        @else
            @foreach ($plans as $plan)
                <tr>
                    <td>{{$plan->nombrePlan}}</td>
                    <td>{{$plan->descripcion}}</td>
                    <td>{{date('d-m-Y', strtotime($plan->fecha))}}</td>
                    <td align="center"><a href="{{ route('plans.download', $plan->id) }}" , target="_blank"><i class='{{$plan->documento}}' style='font-size:24px;color:{{$plan->color}}' ></i></a></td>
                    <td>{{$plan->name}}</td>
                    <td>{{$plan->cargo}}</td>
                    <td>{{$plan->institucion}}</td>
                    <td>{{$plan->nivelinstitucion}}</td>
                    <td>{{$plan->provincia}}</td>
                    <td>{{$plan->distrito}}</td>
                    <td>{{$plan->ugel}}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
<div class="form-inline">
    <p>Total de Plan Subidos: {{$plans->total()}}</p> <br>
    {{$plans->appends(request()->only(['texto', 'instituciones', 'docentes', 'ugels', 'year']))->links()}}
</div>
                     
@stop

@section('css')
 
@stop

@section('js')
<script src="{{ asset("vendor/datatables/jquery.dataTables.min.js") }}"></script>
<script src="{{ asset("vendor/datatables/dataTables.bootstrap5.min.js") }}"></script>

<script src={{ asset("vendor/datatables/dataTables.buttons.min.js") }}></script>
<script src={{ asset("vendor/jszip/jszip.min.js") }}></script>
<script src={{ asset("vendor/pdfmake/pdfmake.min.js") }}></script>
<script src={{ asset("vendor/pdfmake/vfs_fonts.js") }}></script>
<script src={{ asset("vendor/datatables/buttons.html5.min.js") }}></script>
<script src={{ asset("vendor/datatables/buttons.print.min.js") }}></script>
<script>
$(document).ready(function() {
    // Función para obtener los parámetros actuales incluyendo el año
    function getCurrentParams() {
        const params = new URLSearchParams(window.location.search);
        if (!params.has('year')) {
            params.append('year', '2025'); // Asegurarse de que year=2025 esté siempre presente por defecto
        }
        return params.toString();
    }

    // Función para obtener el año seleccionado
    function getSelectedYear() {
        return $('#year').val() || '2025';
    }

    // Configuración de DataTables con botones de exportación
    $('#plans').DataTable({
        scrollX: true,
        dom: 'Bfrtip',
        buttons: [
            {
                text: '<i class="fas fa-file-excel"> Excel (Todos)</i>',
                className: 'btn btn-success',
                action: function (e, dt, node, config) {
                    window.location.href = "{{ route('exportar.planes') }}?format=excel&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-file-csv"> CSV (Todos)</i>',
                className: 'btn btn-info',
                action: function (e, dt, node, config) {
                    window.location.href = "{{ route('exportar.planes') }}?format=csv&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-print"> Imprimir (Todos)</i>',
                className: 'btn btn-warning',
                action: function (e, dt, node, config) {
                    window.open("{{ route('exportar.planes') }}?format=excel&" + getCurrentParams());
                }
            },
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"> Copiar (Actual)</i>',
                className: 'btn btn-secondary'
            }
        ]
    });

    // Cuando cambia el año, recargar la página
    $('#year').on('change', function() {
        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);
        params.set('year', $(this).val());
        
        // Mantener otros filtros si existen
        window.location.href = `${currentUrl.pathname}?${params.toString()}`;
    });

    // Ajax para la búsqueda inicial de UGELs con cantidad de docentes que registraron agendas
    $.ajax({
        url: "{{ route('get-ugels-plan') }}",
        method: 'GET',
        data: {
            year: getSelectedYear(),
            _token: "{{ csrf_token() }}"
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

    // Manejar el cambio en la lista de UGEL
    $('#ugels').on('change', function() {
        var selectedUgel = $(this).val();
        
        // Ajax para la búsqueda de institución por UGEL seleccionada con la información de docentes que registraron agendas
        $.ajax({
            url: "{{ route('buscarInstitucionporUgel-plan') }}",
            method: 'GET',
            data: {
                ugel: selectedUgel,
                year: getSelectedYear(),
                _token: "{{ csrf_token() }}"
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

    // Gestión de la búsqueda de instituciones en tiempo real
    $('#institucion').on('input', function() {
        var selectedUgel = $('#ugels').val();
        var searchTerm = $(this).val();
        
        // Ajax para la búsqueda en tiempo real de instituciones por UGEL seleccionada
        $.ajax({
            url: "{{ route('buscarInstitucionesPlan') }}",
            method: 'GET',
            data: {
                ugel: selectedUgel,
                term: searchTerm,
                year: getSelectedYear(),
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

    // Manejar el cambio en la lista de instituciones
    $('#instituciones').on('change', function() {
        var selectedInstitucion = $(this).val();
        console.log("Institución seleccionada:", selectedInstitucion);
        
        // Obtener el valor de UGEL
        var selectedUgel = $('#ugels').val();
        console.log("UGEL seleccionada:", selectedUgel);

        // Ajax para la búsqueda de docente por institución seleccionada
        $.ajax({
            url: "{{ route('buscarDocenteporInstitucion-pla') }}",
            method: 'GET',
            data: {
                docente: selectedInstitucion,
                ugel: selectedUgel,
                year: getSelectedYear(),
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(data) {
                console.log("Datos recibidos:", data);
                
                var $docentesSelect = $('#docentes');
                $docentesSelect.empty();

                // Agregar la opción predeterminada
                $docentesSelect.append($('<option>', {
                    value: '',
                    text: 'Selecciona un Docente'
                }));

                for (var i = 0; i < data.length; i++) {
                    var docente = data[i].name;
                    var agendasCount = data[i].agendas_count;

                    // Crear una opción con el nombre del docente y la cantidad de registros
                    var $option = $('<option>', {
                        value: docente,
                        text: docente + ' (' + agendasCount + ' planes)'
                    });
                    
                    // Si el docente tiene al menos un registro, aplicar una clase CSS
                    if (agendasCount > 0) {
                        $option.addClass('docente-con-agendas');
                    }
                    
                    // Agregar la opción al elemento <select>
                    $docentesSelect.append($option);
                }
                
                // Habilitar el elemento <select>
                $docentesSelect.prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", error);
                console.error("Estado:", status);
                console.error("Respuesta:", xhr.responseText);
                alert('Error al cargar los docentes: ' + error);
            }
        });
    });

    // Gestión de la búsqueda de docentes en tiempo real
    $('#docente').on('input', function() {
        var selectedInstitucion = $('#instituciones').val();
        var searchTerm = $(this).val();

        // Ajax para la búsqueda en tiempo real de docentes
        $.ajax({
            url: "{{ route('buscarDocentesPlan') }}",
            method: 'GET',
            data: {
                institucion: selectedInstitucion,
                term: searchTerm,
                year: getSelectedYear(),
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
                        text: docente + ' (' + agendasCount + ' evidencias)'
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
@stop