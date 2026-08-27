@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Biblioteca')

@section('content_header')
    <h1>Listado de Biblioteca en el Aula para Especialista DRE</h1>
@stop

@section('content')

 <form action="{{route('buscarInformeGeneral')}}" method="get" class="row g-3">
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
                    <option value="2026" {{ request('year') == '2026' || !request('year') ? 'selected' : '' }}>2026</option>
                    <option value="2025" {{ request('year') == '2025' ? 'selected' : '' }}>2025</option>
                    <option value="2024" {{ request('year') == '2024' ? 'selected' : '' }}>2024</option>
                    <option value="2023" {{ request('year') == '2023' ? 'selected' : '' }}>2023</option>
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

<table id="informes" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Nombre del Biblioteca</th>
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
                    @if(count($informes)<=0)
                    <tr>
                        <td colspan="11">No hay Biblioteca en el Aula para el año seleccionado</td>
                    </tr>
                    @else
                    @foreach ($informes as $informe)
                    <tr>
                        <td>{{$informe->nombreInforme}}</td>
                        <td>{{$informe->descripcion}}</td>
                        
                        <td>{{date('d-m-Y', strtotime($informe->fecha))}}</td>
                        <td align="center"><a href="{{ route('informes.download', $informe->id) }}" , target="_blank"><i class='{{$informe->documento}}' style='font-size:24px;color:{{$informe->color}}' ></i></a></td>
                        <td>{{$informe->name}}</td>
                        <td>{{$informe->cargo}}</td>
                        <td>{{$informe->institucion}}</td>
                        <td>{{$informe->nivelinstitucion}}</td>
                        <td>{{$informe->provincia}}</td>
                        <td>{{$informe->distrito}}</td>
                        <td>{{$informe->ugel}}</td>
                    </tr>
                    @endforeach
                    @endif
                     </tbody>
                     </table>
                     <div class="form-inline">
                        <p>Total de Informes Subidos: {{$informes->total()}}</p> <br>
                        {{$informes->appends(request()->only(['texto','year','fecha', 'instituciones', 'docentes', 'ugels']))->links()}}
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
        const params = new URLSearchParams(window.location.search);
        const year = params.get('year') || '2026'; // Asegurar que el año siempre esté incluido
        params.set('year', year);
        return params.toString();
    }

    $('#informes').DataTable({
        scrollX: true,
        dom: 'Bfrtip',
        buttons: [
            {
                text: '<i class="fas fa-file-excel"> Excel (Todos)</i>',
                className: 'btn btn-success',
                action: function (e, dt, node, config) {
                    window.location.href = "{{ route('exportar.biblioteca') }}?format=excel&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-file-csv"> CSV (Todos)</i>',
                className: 'btn btn-info',
                action: function (e, dt, node, config) {
                    window.location.href = "{{ route('exportar.biblioteca') }}?format=csv&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-print"> Imprimir (Todos)</i>',
                className: 'btn btn-warning',
                action: function (e, dt, node, config) {
                    window.open("{{ route('exportar.biblioteca') }}?format=excel&" + getCurrentParams());
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
    // Asegurarse de que el filtro de año se incluya en las solicitudes AJAX
    $('#year').on('change', function() {
        // Opcionalmente, puedes hacer que el formulario se envíe automáticamente al cambiar el año
        // $('form').submit();
    });

    /* Ajax para la busqueda inicial de ugeles con cantidad de docentes que registraron agendas*/
    $.ajax({
        url: "{{ route('get-ugels-inf') }}",
        method: 'GET',
        data: {
            year: $('#year').val() || '2026'
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
    /* Ajax para la busqueda inicial de ugeles con cantidad de docentes que registraron agendas*/ 

    // Manejar el cambio en la lista de UGEL
    $('#ugels').on('change', function() {
        var selectedUgel = $(this).val();
        var selectedYear = $('#year').val() || '2026';
        
        /* Ajax para la busqueda de institucion por ugel seleccionada con la informacion de docentes que registraron agendas*/ 
        $.ajax({
            url: "{{ route('buscarInstitucionporUgel-inf') }}", // Reemplaza con la ruta correcta en tu aplicación                
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
        
        /* Ajax para la busqueda de institucion por ugel seleccionada con la informacion de docentes que registraron agendas*/
    });


    $('#institucion').on('input', function() {
        var selectedUgel = $('#ugels').val();
        var searchTerm = $(this).val();
        var selectedYear = $('#year').val() || '2026';
        
    /* Ajax para la busqueda en tiempo real de instituciones por ugel seleccionada con la informacion de docentes que registraron agendas*/
        $.ajax({
        url: "{{ route('buscarInstitucionesInforme') }}",
        method: 'GET',
        data: {
            ugel: selectedUgel,
            term: searchTerm, // Agrega el término de búsqueda
            year: selectedYear,
            _token: "{{ csrf_token() }}" // Incluye el token CSRF
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
    /* Ajax para la busqueda en tiempo real de instituciones por ugel seleccionada con la informacion de docentes que registraron agendas*/
    });


    $(document).ready(function() {
    // Manejar el cambio en la lista de UGEL
    $('#instituciones').on('change', function() {
    var selectedInstitucion = $(this).val();
    console.log("Institución seleccionada:", selectedInstitucion);
    
    // Obtener el valor de UGEL seleccionado
    var selectedUgel = $('#ugels').val();
    console.log("UGEL seleccionada:", selectedUgel);
    var selectedYear = $('#year').val() || '2026';

    /* Ajax para la busqueda de docente por institucion seleccionada */
    $.ajax({
        url: "{{ route('buscarDocenteporInstitucion-inf') }}",                
        method: 'GET',
        data: {
            docente: selectedInstitucion, // El nombre que espera el controlador
            ugel: selectedUgel,          // Añade el parámetro ugel explícitamente
            year: selectedYear,
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
                    text: docente + ' (' + agendasCount + ' evidencias)'
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
    $('#docente').on('input', function() {
        var selectedInstitucion = $('#instituciones').val();
        var searchTerm = $(this).val();
        var selectedYear = $('#year').val() || '2026';

        /* Ajax para la busqueda en tiempo real de docentes con la informacion de docentes que registraron agendas*/
        $.ajax({
        url: "{{ route('buscarDocentesInforme') }}",
        method: 'GET',
        data: {
            institucion: selectedInstitucion,
            term: searchTerm, // Agrega el término de búsqueda
            year: selectedYear,
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
    /* Ajax para la busqueda en tiempo real de docentes con la informacion de docentes que registraron agendas*/
});
</script>
@stop