@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Asistencia')

@section('content_header')
    <h1>Listado de Asistencia Técnica Para Especialista DRE</h1>
@stop

@section('content')

<form action="{{route('buscarEvidenciaGeneral')}}" method="get" class="row g-3">
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
                <i class="fas fa-calendar"></i>
                </span>
                    <select class="form-control" id="anio" name="anio">
                        <option value="" disabled>Seleccione Año</option>
                        <option value="2026" {{ request('anio') == '2026' || !request('anio') ? 'selected' : '' }}>2026</option>
                        <option value="2025" {{ request('anio') == '2025' ? 'selected' : '' }}>2025</option>
                        <option value="2024" {{ request('anio') == '2024' ? 'selected' : '' }}>2024</option>
                        <option value="2023" {{ request('anio') == '2023' ? 'selected' : '' }}>2023</option>
                    </select>    
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
    <div class="form-group col-md-3">
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
    <div class="form-group col-md-3">
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

<table id="evidencias" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
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
                    @if(count($evidencias)<=0)
                    <tr>
                        <td colspan="11">No hay Asistencia Técnica</td>
                    </tr>
                    @else
                    @foreach ($evidencias as $evidencia)
                    <tr>
                        <td>{{$evidencia->nombreEvidencia}}</td>
                        <td>{{$evidencia->descripcion}}</td>
                        <td>{{date('d-m-Y', strtotime($evidencia->fecha))}}</td>
                        <td align="center"><a href="{{ route('evidencias.download', $evidencia->id) }}" , target="_blank"><i class='{{$evidencia->documento}}' style='font-size:24px;color:{{$evidencia->color}}' ></i></a></td>
                        <td>{{$evidencia->name}}</td>
                        <td>{{$evidencia->cargo}}</td>
                        <td>{{$evidencia->institucion}}</td>
                        <td>{{$evidencia->nivelinstitucion}}</td>
                        <td>{{$evidencia->provincia}}</td>
                        <td>{{$evidencia->distrito}}</td>
                        <td>{{$evidencia->ugel}}</td>
                
                    </tr>
                    @endforeach
                     @endif
                     </tbody>
                     </table>
                     <div class="form-inline">
                        <p>Total de Evidencias Subidos: {{$evidencias->total()}}</p> <br>
                        {{$evidencias->appends(request()->only(['texto', 'instituciones', 'ugels', 'docentes', 'anio']))->links()}}
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
    // Crear función para obtener parámetros actuales de filtro
    function getCurrentParams() {
        return new URLSearchParams(window.location.search).toString();
    }

    // Configurar DataTables con botones personalizados
    $('#evidencias').DataTable({
        scrollX: true,
        dom: 'Bfrtip',
        buttons: [
            {
                text: '<i class="fas fa-file-excel"> Excel (Todos)</i>',
                className: 'btn btn-success',
                action: function (e, dt, node, config) {
                    window.location.href = "{{ route('exportar.evidencias') }}?format=excel&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-file-csv"> CSV (Todos)</i>',
                className: 'btn btn-info',
                action: function (e, dt, node, config) {
                    window.location.href = "{{ route('exportar.evidencias') }}?format=csv&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-file-pdf"> PDF (Todos)</i>',
                className: 'btn btn-danger',
                action: function (e, dt, node, config) {
                    window.location.href = "{{ route('exportar.evidencias') }}?format=pdf&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-print"> Imprimir (Todos)</i>',
                className: 'btn btn-warning',
                action: function (e, dt, node, config) {
                    window.open("{{ route('exportar.evidencias') }}?format=excel&" + getCurrentParams());
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
    /* Evento de cambio para el selector de año */
    $('#anio').on('change', function() {
        // Recargar las UGELs al cambiar el año
        cargarUgels();
    });

    // Función para cargar UGELs según el año seleccionado
    function cargarUgels() {
        var selectedYear = $('#anio').val();
        
        $.ajax({
            url: "{{ route('get-ugels-evi') }}",
            method: 'GET',
            data: {
                anio: selectedYear
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

    // Cargar UGELs al iniciar la página
    cargarUgels();

    // Manejar el cambio en la lista de UGEL
    $('#ugels').on('change', function() {
        var selectedUgel = $(this).val();
        var selectedYear = $('#anio').val();
        
        /* Ajax para la busqueda de institucion por ugel seleccionada con la informacion de docentes que registraron agendas */ 
        $.ajax({
            url: "{{ route('buscarInstitucionporUgel-evi') }}", 
            method: 'GET',
            data: {
                ugel: selectedUgel,
                anio: selectedYear,
                _token: "{{ csrf_token() }}" // Incluye el token CSRF
            },
            dataType: 'json',
            success: function(data) {
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
        var selectedYear = $('#anio').val();
        var searchTerm = $(this).val();
        
        /* Ajax para la busqueda en tiempo real de instituciones */
        $.ajax({
            url: "{{ route('buscarInstitucionesEvidencia') }}",
            method: 'GET',
            data: {
                ugel: selectedUgel,
                anio: selectedYear,
                term: searchTerm,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(data) {
                var $institucionesSelect = $('#instituciones');
                $institucionesSelect.empty();

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
        var selectedDocente = $(this).val();
        var selectedYear = $('#anio').val();

        /* Ajax para la busqueda de docente por institucion seleccionada */
        $.ajax({
            url: "{{ route('buscarDocenteporInstitucion-evi') }}",
            method: 'GET',
            data: {
                docente: selectedDocente,
                anio: selectedYear,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(data) {
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
        var selectedYear = $('#anio').val();
        var searchTerm = $(this).val();

        /* Ajax para la busqueda en tiempo real de docentes */
        $.ajax({
            url: "{{ route('buscarDocentesEvidencia') }}",
            method: 'GET',
            data: {
                institucion: selectedInstitucion,
                anio: selectedYear,
                term: searchTerm,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(data) {
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
@stop