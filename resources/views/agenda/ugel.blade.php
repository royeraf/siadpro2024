@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Asistencia')

@section('content_header')
    <h1>Listado de Agendas de Lectura Para Especialista UGEL</h1>
@stop

@section('content')

<form action="{{route('buscarAgendaUgel')}}" method="get" class="row g-3">
    @csrf
    <!-- Nuevo filtro de año -->
    <div class="form-group col-md-2">
        <div class="col align-self-center">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-calendar-year"></i>
                </span>
                <select class="form-control" id="year" name="year">
                    <option value="2026" {{ !isset($selectedYear) || $selectedYear == '2026' ? 'selected' : '' }}>2026</option>
                    <option value="2025" {{ isset($selectedYear) && $selectedYear == '2025' ? 'selected' : '' }}>2025</option>
                    <option value="2024" {{ isset($selectedYear) && $selectedYear == '2024' ? 'selected' : '' }}>2024</option>
                    <option value="2023" {{ isset($selectedYear) && $selectedYear == '2023' ? 'selected' : '' }}>2023</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="form-group col-md-4">
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
    <div class="form-group col-md-4">
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

<table id="agendas" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Docente</th>
                    <th scope="col">Nombre de la agenda</th>
                    <th scope="col">Descripcion</th>
                    <th scope="col">Fecha Inicio</th>
                    <th scope="col">Fecha Fin</th>
                    <th scope="col">Institución</th> 
                    <th scope="col">Provincia</th>
                    <th scope="col">Distrito</th>
                    <th scope="col">UGEL</th>
                    </tr>
                    </thead>
                    <tbody >
                    @if(count($agendas)<=0)
                    <tr>
                        <td colspan="9">No hay ninguna Agenda</td>
                    </tr>
                    @else
                    @foreach ($agendas as $agenda)
                    <tr>
                        <td>{{$agenda->nomDocente}}</td>
                        <td>{{$agenda->title}}</td>
                        <td>{{$agenda->evento}}</td>
                        <td>{{date('d-m-Y', strtotime($agenda->start))}}</td>
                        <td>{{date('d-m-Y', strtotime($agenda->end))}}</td>
                        <td>{{$agenda->institucion}}</td>
                        <td>{{$agenda->provincia}}</td>
                        <td>{{$agenda->distrito}}</td>
                        <td>{{$agenda->ugel}}</td>
                    </tr>
                    @endforeach
                     @endif
                     </tbody>
                     </table>
                     <div class="form-inline">
                        <p>Total de Evidencias Subidos: {{$agendas->total()}}</p> <br>
                        {{$agendas->appends(request()->only(['year', 'instituciones', 'docentes']))->links()}}
                     </div>
                     
                          
                    
@stop

@section('css')
 
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    // Si no hay año seleccionado, seleccionar 2026 por defecto
    if (!$('#year').val()) {
        $('#year').val('2026');
    }
    
    $('#agendas').DataTable({
        scrollX: true,
        dom: 'Bfrtip', // Activa los botones
        buttons: [
            {
                extend: 'print',
                text: '<i class="fas fa-print"> Imprimir</i>',
                className: 'btn btn-warning',
                exportOptions: { modifier: { page: 'all' } }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"> Excel</i>',
                className: 'btn btn-success',
                exportOptions: { modifier: { page: 'all' } }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf"> PDF</i>',
                className: 'btn btn-danger',
                exportOptions: { modifier: { page: 'all' } }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"> CSV</i>',
                className: 'btn btn-info',
                exportOptions: { modifier: { page: 'all' } }
            },
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"> Copiar</i>',
                className: 'btn btn-secondary',
                exportOptions: { modifier: { page: 'all' } }
            },
        ]
    });
});
function imprimir()
{
    window.print();
}
</script>
<script>
    $(document).ready(function() {
        // Función para cargar instituciones según el año seleccionado
        function loadInstituciones() {
            var selectedYear = $('#year').val();
            
            /* Ajax para la busqueda inicial de instituciones con cantidad de docentes que registraron agendas*/
            $.ajax({
                url: "{{ route('get-institucions') }}",
                method: 'GET',
                data: { year: selectedYear },
                dataType: 'json',
                success: function(data) {
                    console.log(data);
                    var $institucionsSelect = $('#instituciones');
                    $institucionsSelect.empty(); // Limpiar opciones existentes

                    // Agregar la opción predeterminada
                    $institucionsSelect.append($('<option>', {
                        value: '',
                        text: ' Seleccione la Institucion: '
                    }));

                    // Iterar sobre los datos recibidos y agregar las UGELs al select
                    for (var i = 0; i < data.length; i++) {
                        var institucion = data[i].nomInstitucion;
                        var agendasCount = data[i].agendas_count;
                        var totalDocentes = data[i].total_docentes;

                        // Crear una opción con el nombre del docente y la cantidad de agendas registradas
                        var $option = $('<option>', {
                            value: institucion,
                            text: institucion + ' (' + agendasCount + ' docente, ' + totalDocentes + ' total)'
                        });
                        // Si el docente tiene al menos una agenda, aplicar una clase CSS
                        if (agendasCount > 0) {
                            $option.addClass('docente-con-agendas');
                        }
                        // Agregar la opción al elemento <select>
                        $institucionsSelect.append($option);
                    }
                    $institucionsSelect.prop('disabled', false);
                    
                    // Limpiar el select de docentes
                    $('#docentes').empty().append($('<option>', {
                        value: '',
                        text: 'Selecciona un Docente'
                    }));
                },
                error: function() {
                    alert('Error al cargar las Instituciones.');
                }
            });
        }
        
        // Cargar instituciones al cargar la página
        loadInstituciones();
        
        // Cargar instituciones cuando cambia el año
        $('#year').on('change', function() {
            loadInstituciones();
        });
        
        /* Ajax para la busqueda en tiempo real de instituciones por ugel seleccionada con la informacion de docentes que registraron agendas*/
        $('#institucion').on('input', function() {
            var searchTerm = $(this).val();
            var selectedYear = $('#year').val();

            $.ajax({
                url: "{{ route('buscarInstitucionesAgenda') }}",  
                method: 'GET',
                data: {
                    term: searchTerm, // Agrega el término de búsqueda
                    year: selectedYear, // Añadir el año seleccionado
                    _token: "{{ csrf_token() }}" // Incluye el token CSRF
                },
                dataType: 'json',
                success: function(data) {
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
                            text: institucion + ' (' + agendasCount + ' docente, ' + totalDocentes + ' total)'
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

        /* Ajax para la busqueda de docente por institucion seleccionada con la informacion de docentes que registraron agendas*/
        $('#instituciones').on('change', function() {
            var selectedDocente = $(this).val();
            var selectedYear = $('#year').val();
            
            $.ajax({
                url: "{{ route('buscarDocenteporInstitucion') }}", // Reemplaza con la ruta correcta en tu aplicación                
                method: 'GET',
                data: {
                    docente: selectedDocente,
                    year: selectedYear, // Añadir el año seleccionado
                    _token: "{{ csrf_token() }}" // Incluye el token CSRF
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
                            text: docente + ' (' + agendasCount + ' agendas)'
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

        /* Ajax para la busqueda en tiempo real de docentes con la informacion de docentes que registraron agendas*/
        $('#docente').on('input', function() {
            var selectedInstitucion = $('#instituciones').val();
            var searchTerm = $(this).val();
            var selectedYear = $('#year').val();

            $.ajax({
                url: "{{ route('buscarDocentesAgenda') }}",
                method: 'GET',
                data: {
                    institucion: selectedInstitucion,
                    term: searchTerm, // Agrega el término de búsqueda
                    year: selectedYear, // Añadir el año seleccionado
                    _token: "{{ csrf_token() }}" // Incluye el token CSRF
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
                            text: docente + ' (' + agendasCount + ' agendas)'
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
@stop