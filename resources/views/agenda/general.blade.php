@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Asistencia')

@section('content_header')
    <h1>Listado de Agendas de Lectura Para Especialista GENERAL DRE</h1>
@stop

@section('content')

<form action="{{route('buscarAgendaGeneral')}}" method="get" class="row g-4">
    @csrf
    <!-- Nuevo filtro de año -->
    <div class="form-group col-md-2">
        <div class="col align-self-center">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-calendar-year"></i>
                </span>
                <select class="form-control" id="year" name="year">
                    <option value="2023" {{ isset($selectedYear) && $selectedYear == '2023' ? 'selected' : '' }}>2023</option>
                    <option value="2024" {{ isset($selectedYear) && $selectedYear == '2024' ? 'selected' : '' }}>2024</option>
                    <option value="2025" {{ isset($selectedYear) && $selectedYear == '2025' ? 'selected' : '' }}>2025</option>
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
                    <!-- Si hay un valor seleccionado, mantenerlo con una opción temporal -->
                    @if(isset($selectedUgel) && !empty($selectedUgel))
                        <option value="{{ $selectedUgel }}" selected>{{ $selectedUgel }} (cargando...)</option>
                    @endif
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
                        <!-- Si hay una institución seleccionada, mantenerla con una opción temporal -->
                        @if(isset($selectedInstitucion) && !empty($selectedInstitucion))
                            <option value="{{ $selectedInstitucion }}" selected>{{ $selectedInstitucion }} (cargando...)</option>
                        @endif
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
                    <i class="fas fa-building"></i>
                </span>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="docente" name="" autocomplete="off">                    
                </div>
                <div class="col-md-7">
                    <select name="docentes" id="docentes" class="form-control">
                        <option value="">Selecciona un Docente</option>
                        <!-- Si hay un docente seleccionado, mantenerlo con una opción temporal -->
                        @if(isset($selectedDocente) && !empty($selectedDocente))
                            <option value="{{ $selectedDocente }}" selected>{{ $selectedDocente }} (cargando...)</option>
                        @endif
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
    {{$agendas->appends(request()->only(['instituciones', 'ugels', 'docentes', 'year']))->links()}}
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
    // Guardar valores seleccionados
    var selectedUgel = "{{ isset($selectedUgel) ? $selectedUgel : '' }}";
    var selectedInstitucion = "{{ isset($selectedInstitucion) ? $selectedInstitucion : '' }}";
    var selectedDocente = "{{ isset($selectedDocente) ? $selectedDocente : '' }}";
    
    // Si no hay año seleccionado, seleccionar 2025 por defecto
    if (!$('#year').val()) {
        $('#year').val('2025');
    }

    // Función para obtener los parámetros actuales
    function getCurrentParams() {
        var params = new URLSearchParams();
        
        // Añadir todos los parámetros del formulario
        if ($('#ugels').val()) params.append('ugels', $('#ugels').val());
        if ($('#instituciones').val()) params.append('instituciones', $('#instituciones').val());
        if ($('#docentes').val()) params.append('docentes', $('#docentes').val());
        if ($('#nivel').val()) params.append('nivel', $('#nivel').val());
        if ($('#year').val()) params.append('year', $('#year').val());
        
        return params.toString();
    }

    $('#agendas').DataTable({
        scrollX: true,
        dom: 'Bfrtip',
        buttons: [
            {
                text: '<i class="fas fa-print"> Imprimir (Todos)</i>',
                className: 'btn btn-warning',
                action: function (e, dt, node, config) {
                    window.open("{{ route('exportar.agendas') }}?format=print&" + getCurrentParams());
                }
            },
            {
                text: '<i class="fas fa-file-excel"> Excel (Todos)</i>',
                className: 'btn btn-success',
                action: function (e, dt, node, config) {
                    window.location.href = "{{ route('exportar.agendas') }}?format=excel&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-file-csv"> CSV (Todos)</i>',
                className: 'btn btn-info',
                action: function (e, dt, node, config) {
                    window.location.href = "{{ route('exportar.agendas') }}?format=csv&" + getCurrentParams();
                }
            },
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"> Copiar (Actual)</i>',
                className: 'btn btn-secondary'
            }
        ]
    });
    
    // Cargar UGEL cuando cambia el año
    function loadUgels() {
        var selectedYear = $('#year').val();
        
        $.ajax({
            url: "{{ route('get-ugels-ag') }}",
            method: 'GET',
            data: { year: selectedYear },
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
                    
                    // Seleccionar la opción si coincide con la UGEL seleccionada anteriormente
                    if (ugel === selectedUgel) {
                        $option.prop('selected', true);
                    }

                    // Agregar la opción al select
                    $ugelsSelect.append($option);
                }
                
                // Si hay una UGEL seleccionada, cargar las instituciones correspondientes
                if (selectedUgel) {
                    loadInstituciones(selectedUgel);
                } else {
                    // Limpiar otros selects dependientes
                    $('#instituciones').empty().append($('<option>', {
                        value: '',
                        text: 'Selecciona una institución'
                    }));
                    
                    $('#docentes').empty().append($('<option>', {
                        value: '',
                        text: 'Selecciona un Docente'
                    }));
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar UGELs:", error);
                alert('Error al cargar las UGELs.');
            }
        });
    }
    
    // Función para cargar instituciones basadas en UGEL seleccionada
    function loadInstituciones(ugel) {
        var selectedYear = $('#year').val();
        
        $.ajax({
            url: "{{ route('buscarInstitucionporUgel-ag') }}", 
            method: 'GET',
            data: {
                ugel: ugel,
                year: selectedYear,
                _token: "{{ csrf_token() }}"
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

                // Itera a través de los datos y agrega las instituciones
                for (var i = 0; i < data.length; i++) {
                    var institucion = data[i].nomInstitucion;
                    var docentesCount = data[i].agendas_count;
                    var totalDocentes = data[i].total_docentes;

                    // Crea una opción con el nombre de la institución y las cantidades
                    var $option = $('<option>', {
                        value: institucion,
                        text: institucion + ' (' + docentesCount + ' docentes, ' + totalDocentes + ' total)'
                    });
                    
                    // Seleccionar la opción si coincide con la institución seleccionada anteriormente
                    if (institucion === selectedInstitucion) {
                        $option.prop('selected', true);
                    }

                    // Agrega la opción a la lista desplegable
                    $institucionesSelect.append($option);
                }

                $institucionesSelect.prop('disabled', false); // Habilita la lista desplegable
                
                // Si hay una institución seleccionada, cargar los docentes correspondientes
                if (selectedInstitucion) {
                    loadDocentes(selectedInstitucion, ugel);
                } else {
                    // Limpiar el select de docentes
                    $('#docentes').empty().append($('<option>', {
                        value: '',
                        text: 'Selecciona un Docente'
                    }));
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar instituciones:", error);
                alert('Error al cargar las instituciones.');
            }   
        });
    }
    
    // Función para cargar docentes basados en la institución seleccionada
    function loadDocentes(institucion, ugel) {
        var selectedYear = $('#year').val();
        
        $.ajax({
            url: "{{ route('buscarDocenteporInstitucion-ag') }}",
            method: 'GET',
            data: {
                docente: institucion, 
                ugel: ugel,
                year: selectedYear,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(data) {
                var $docentesSelect = $('#docentes');
                $docentesSelect.empty(); // Limpiar opciones existentes

                // Agregar la opción predeterminada
                $docentesSelect.append($('<option>', {
                    value: '',
                    text: 'Selecciona un Docente'
                }));

                // Si recibimos datos y hay elementos, los agregamos
                if (data && data.length > 0) {
                    for (var i = 0; i < data.length; i++) {
                        var docente = data[i].name;
                        var agendasCount = data[i].agendas_count || 0;

                        // Crear una opción con el nombre del docente y la cantidad de agendas registradas
                        var $option = $('<option>', {
                            value: docente,
                            text: docente + ' (' + agendasCount + ' agendas)'
                        });
                        
                        // Seleccionar la opción si coincide con el docente seleccionado anteriormente
                        if (docente === selectedDocente) {
                            $option.prop('selected', true);
                        }
                        
                        // Si el docente tiene al menos una agenda, aplicar una clase CSS
                        if (agendasCount > 0) {
                            $option.addClass('docente-con-agendas');
                        }
                        
                        // Agregar la opción al elemento <select>
                        $docentesSelect.append($option);
                    }
                } else {
                    // Si no hay docentes, mostramos un mensaje
                    $docentesSelect.append($('<option>', {
                        value: '',
                        text: 'No hay docentes disponibles'
                    }));
                }
                
                // Habilitar el elemento <select>
                $docentesSelect.prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", error);
                console.error("Estado:", status);
                console.error("Respuesta:", xhr.responseText);
                
                var $docentesSelect = $('#docentes');
                $docentesSelect.empty();
                $docentesSelect.append($('<option>', {
                    value: '',
                    text: 'Error al cargar docentes'
                }));
                
                alert('Error al cargar los docentes: ' + error);
            }
        });
    }
    
    // Cargar UGELs al iniciar la página
    loadUgels();
    
    // Cargar UGELs cuando cambia el año
    $('#year').on('change', function() {
        // Resetear valores seleccionados cuando cambia el año
        selectedUgel = '';
        selectedInstitucion = '';
        selectedDocente = '';
        loadUgels();
    });
    
    // Manejar el cambio en la lista de UGEL
    $('#ugels').on('change', function() {
        var selUgel = $(this).val();
        selectedUgel = selUgel;
        selectedInstitucion = '';
        selectedDocente = '';
        
        if (selUgel) {
            loadInstituciones(selUgel);
        } else {
            // Limpiar selects dependientes
            $('#instituciones').empty().append($('<option>', {
                value: '',
                text: 'Selecciona una institución'
            }));
            
            $('#docentes').empty().append($('<option>', {
                value: '',
                text: 'Selecciona un Docente'
            }));
        }
    });

    // Manejar el cambio en la lista de instituciones
    $('#instituciones').on('change', function() {
        var selInstitucion = $(this).val();
        selectedInstitucion = selInstitucion;
        selectedDocente = '';
        
        if (selInstitucion && selectedUgel) {
            loadDocentes(selInstitucion, selectedUgel);
        } else {
            // Limpiar select de docentes
            $('#docentes').empty().append($('<option>', {
                value: '',
                text: 'Selecciona un Docente'
            }));
        }
    });
});
</script>
<script>
    $(document).ready(function() {
        // Manejar la búsqueda de instituciones en tiempo real
        $('#institucion').on('input', function() {
            var selectedUgel = $('#ugels').val();
            var selectedYear = $('#year').val();
            var searchTerm = $(this).val();
            
            /* Ajax para la busqueda en tiempo real de instituciones */
            $.ajax({
                url: "{{ route('buscarInstitucionesAgenda') }}",
                method: 'GET',
                data: {
                    ugel: selectedUgel,
                    year: selectedYear,
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

                        var $option = $('<option>', {
                            value: institucion,
                            text: institucion + ' (' + agendasCount + ' docentes, ' + totalDocentes + ' total)'
                        });
                        
                        if (agendasCount > 0) {
                            $option.addClass('docente-con-agendas');
                        }
                        
                        // Si coincide con la institución previamente seleccionada, marcarla
                        if (institucion === "{{ isset($selectedInstitucion) ? $selectedInstitucion : '' }}") {
                            $option.prop('selected', true);
                        }
                        
                        $institucionesSelect.append($option);
                    }
                    
                    $institucionesSelect.prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error("Error al buscar instituciones:", error);
                    alert('Error al cargar las instituciones.');
                }
            });
        });
        
        // Manejar la búsqueda de docentes en tiempo real
        $('#docente').on('input', function() {
            var institucion = $('#instituciones').val();
            var year = $('#year').val();
            var searchTerm = $(this).val();
            
            if (!institucion) {
                alert('Por favor, selecciona primero una institución');
                return;
            }
            
            $.ajax({
                url: "{{ route('buscarDocentesAgenda') }}",
                method: 'GET',
                data: {
                    institucion: institucion,
                    year: year,
                    term: searchTerm,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(data) {
                    var $docentesSelect = $('#docentes');
                    $docentesSelect.empty();
                    
                    $docentesSelect.append($('<option>', {
                        value: '',
                        text: 'Selecciona un Docente'
                    }));
                    
                    for (var i = 0; i < data.length; i++) {
                        var docente = data[i];
                        
                        var $option = $('<option>', {
                            value: docente,
                            text: docente
                        });
                        
                        // Si coincide con el docente previamente seleccionado, marcarlo
                        if (docente === "{{ isset($selectedDocente) ? $selectedDocente : '' }}") {
                            $option.prop('selected', true);
                        }
                        
                        $docentesSelect.append($option);
                    }
                    
                    $docentesSelect.prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error("Error al buscar docentes:", error);
                    alert('Error al cargar los docentes.');
                }
            });
        });
    });
</script>
@stop