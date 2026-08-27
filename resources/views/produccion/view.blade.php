@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('title', 'Produccion')

@section('content_header')
    <h1>Listado de Producción de Textos Infantiles</h1>
@stop

@section('content')

<form action="{{route('buscarProduccionGeneral')}}" method="get" class="row g-3">
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
                    <i class="fas fa-calendar-year"></i>
                </span>
                <select class="form-control" id="year" name="year">
                    <option value="2025" {{ request('year') == '2025' || !request('year') ? 'selected' : '' }}>2025</option>
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
        @if (count($buscars) == 1) <!-- Esto es para Ugel -->
        <div class="form-group col-md-3">
            <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                <i class="fas fa-school"></i>
                </span>
                    <div class="col-md-10">
                        <select id="instituciones" name="instituciones" class="form-control">
                            <option value="">----SELECCIONE INSTITUCION-----</option>
                            @foreach ($produccions->unique('institucion') as $produccion)
                                <option value="{{$produccion->institucion}}">{{$produccion->institucion}}</option>                            
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        @else         
        <div class="form-group col-md-3">
            <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                <i class="fas fa-school"></i>
                </span>
                    <div class="col-md-10">
                        <select id="instituciones" name="instituciones" class="form-control">
                            <option value="">----SELECCIONE INSTITUCION-----</option>
                            @foreach ($produccions->unique('institucion') as $produccion)
                                <option value="{{$produccion->institucion}}">{{$produccion->institucion}}</option>                            
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        @endif  
    <div class="form-group col-md-3">
        <div class="col align-self-center">
            <div class="input-group-prepend">
            <span class="input-group-text">
            <i class="	fas fa-user-tie"></i>
            </span>
                <div class="col-md-10">
                    <select id="docentes" name="docentes" class="form-control">
                        <option value="">----SELECCIONE DOCENTE-----</option>
                        @foreach ($produccions->unique('name') as $produccion)
                            <option value="{{$produccion->name}}">{{$produccion->name}}</option>                            
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group col-md-1">
        <input type="submit" class="btn btn-primary" value="Buscar">
    </div>
</form>

<table id="produccions" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Tipo de Producción</th>
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
                   
                        @if(count($produccions)<=0)
                        <tr>
                            <td colspan="11">No hay textos infantiles</td>
                        </tr>
                        @else
                            @foreach ($produccions as $produccion)
                            <tr>
                                <td>{{$produccion->nombreProduccion}}</td>
                                <td>{{$produccion->descripcion}}</td>
                                <td>{{date('d-m-Y', strtotime($produccion->fecha))}}</td>
                                <td align="center"><a href="{{ route('produccions.download', $produccion->id) }}" target="_blank"><i class='{{$produccion->documento}}' style='font-size:24px;color:{{$produccion->color}}' ></i></a></td>
                                <td>{{$produccion->name}}</td>
                                <td>{{$produccion->cargo}}</td>
                                <td>{{$produccion->institucion}}</td>
                                <td>{{$produccion->nivelinstitucion}}</td>
                                <td>{{$produccion->provincia}}</td>
                                <td>{{$produccion->distrito}}</td>
                                <td>{{$produccion->ugel}}</td>
                            </tr>
                            @endforeach
                        @endif
                     </tbody>
                     </table>
                     <div class="form-inline">
                        <p>Total de Producciones Subidas: {{$produccions->total()}}</p> <br>
                        {{$produccions->appends(request()->only(['texto', 'instituciones', 'nivel', 'docentes', 'year', 'ugels']))->links()}}
                     </div>
                     
                          
                    
@stop

@section('css')
 
@stop

@section('js')
<script>
// Definir las variables para las rutas que se usarán en AJAX
var getUgelsProRoute = "{{ route('get-ugels-pro') }}";
var buscarInstitucionporUgelRoute = "{{ route('buscarInstitucionporUgel-pro') }}";
var buscarDocenteporInstitucionProRoute = "{{ route('buscarDocenteporInstitucion-pro') }}";
var exportarProduccionesRoute = "{{ route('exportar.producciones') }}";
var csrfToken = "{{ csrf_token() }}";
</script>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script>
$(document).ready(function() {
    // Función para obtener los parámetros actuales
    function getCurrentParams() {
        return new URLSearchParams(window.location.search).toString();
    }

    $('#produccions').DataTable({
        scrollX: true,
        dom: 'Bfrtip',
        buttons: [
            {
                text: '<i class="fas fa-file-excel"> Excel (Todos)</i>',
                className: 'btn btn-success',
                action: function (e, dt, node, config) {
                    window.location.href = exportarProduccionesRoute + "?format=excel&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-file-csv"> CSV (Todos)</i>',
                className: 'btn btn-info',
                action: function (e, dt, node, config) {
                    window.location.href = exportarProduccionesRoute + "?format=csv&" + getCurrentParams();
                }
            },
            {
                text: '<i class="fas fa-print"> Imprimir (Todos)</i>',
                className: 'btn btn-warning',
                action: function (e, dt, node, config) {
                    window.open(exportarProduccionesRoute + "?format=print&" + getCurrentParams());
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
    // Function to load UGELs based on selected year
    function loadUgels() {
        const selectedYear = $('#year').val();
        
        $.ajax({
            url: getUgelsProRoute,
            method: 'GET',
            data: {
                year: selectedYear
            },
            dataType: 'json',
            success: function(data) {
                var $ugelsSelect = $('#ugels');
                $ugelsSelect.empty(); // Clear existing options

                // Add default option
                $ugelsSelect.append($('<option>', {
                    value: '',
                    text: ' Seleccione la UGEL: '
                }));

                // Iterate through received data and add UGELs to select
                for (var i = 0; i < data.length; i++) {
                    var ugel = data[i].ugel;
                    var docentesCount = data[i].docentes_count;

                    // Create an option for each UGEL with teacher count
                    var $option = $('<option>', {
                        value: ugel,
                        text: ugel + ' (' + docentesCount + ' docente)'
                    });

                    // Add option to select
                    $ugelsSelect.append($option);
                }
            },
            error: function() {
                alert('Error al cargar las UGELs.');
            }
        });
    }

    // Load UGELs when the page loads
    loadUgels();
    
    // Listen for changes in the year selector
    $('#year').on('change', function() {
        // Clear institution and teacher selectors
        $('#instituciones').empty().append($('<option>', {
            value: '',
            text: 'Selecciona una institución'
        }));
        
        $('#docentes').empty().append($('<option>', {
            value: '',
            text: 'Selecciona un Docente'
        }));
        
        // Reload UGELs based on the new year
        loadUgels();
    });

    // Modified AJAX for searching institution by UGEL
    $('#ugels').on('change', function() {
        var selectedUgel = $(this).val();
        var selectedYear = $('#year').val();
        
        $.ajax({
            url: buscarInstitucionporUgelRoute,                
            method: 'GET',
            data: {
                ugel: selectedUgel,
                year: selectedYear,
                _token: csrfToken
            },
            dataType: 'json',
            success: function(data) {
                console.log(data);
                var $institucionesSelect = $('#instituciones');
                $institucionesSelect.empty();

                $institucionesSelect.append($('<option>', {
                    value: '',
                    text: 'Selecciona una institución'
                }));

                for (var i = 0; i < data.length; i++) {
                    var institucion = data[i].nomInstitucion;
                    var docentesCount = data[i].agendas_count;
                    var totalDocentes = data[i].total_docentes;

                    var $option = $('<option>', {
                        value: institucion,
                        text: institucion + ' (' + docentesCount + ' docentes, ' + totalDocentes + ' total)'
                    });

                    $institucionesSelect.append($option);
                }

                $institucionesSelect.prop('disabled', false);
            },
            error: function() {
                alert('Error al cargar las instituciones.');
            }   
        });
    });

    // Búsqueda de docentes por institución
    $('#instituciones').on('change', function() {
        var selectedInstitucion = $(this).val();
        var selectedUgel = $('#ugels').val();
        var selectedYear = $('#year').val();

        $.ajax({
            url: buscarDocenteporInstitucionProRoute,
            method: 'GET',
            data: {
                docente: selectedInstitucion,
                ugel: selectedUgel,
                year: selectedYear,
                _token: csrfToken
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

                    var $option = $('<option>', {
                        value: docente,
                        text: docente + ' (' + agendasCount + ' producciones)'
                    });
                    
                    if (agendasCount > 0) {
                        $option.addClass('docente-con-agendas');
                    }
                    
                    $docentesSelect.append($option);
                }
                
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
});
</script>
@stop