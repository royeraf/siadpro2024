@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Accion')

@section('content_header')
    <h1>Listado de Acciones General</h1>
@stop

@section('content')

<form action="{{route('buscarAccionGeneral')}}" method="get" class="row g-3">
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
                    <select class="form-control" name="anio" id="anio">
                        <option value="2025" {{ request('anio') == '2025' || !request('anio') ? 'selected' : '' }}>2025</option>
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
                <i class="fas fa-calendar"></i>
            </span>
                <select class="form-control" id="ugels" name="ugels">
                    <option value=""> Seleccione la UGEL: </option>
                </select>
            </div>
        </div>
    </div>
        @if (count($buscars) == 2) <!-- Esto es para Ugel -->
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
        @else         
        <div class="form-group col-md-3">
            <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                <i class="fas fa-user-tie"></i>
                </span>
                    <div class="col-md-10">
                        <select id="nomdocente" name="nomdocente" class="form-control">
                            <option value="">----SELECCIONE DOCENTE-----</option>
                            @foreach ($accions as $accion)
                                <option value="{{$accion->name}}">{{$accion->name}}</option>                            
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div> 
        @endif  
    
    <div class="form-group col-md-1">
        <input type="submit" class="btn btn-primary" value="Buscar">
    </div>
</form>

<table id="accions" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Nombre de la Acción</th>
                    <th scope="col">Lugar</th>
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
                    @if(count($accions)<=0)
                    <tr>
                        <td colspan="8">No hay Accion de Sensibilización</td>
                    </tr>
                    @else
                        @if (count($rols)) <!-- Esto es para Director -->
                        @foreach ($accions as $accion)
                        <tr>
                            <td>{{$accion->nombreAccion}}</td>
                            <td>{{$accion->lugar}}</td>
                            <td>{{date('d-m-Y', strtotime($accion->fecha))}}</td>
                            <td align="center"><a href="{{ route('accions.download', $accion->id) }}" , target="_blank"><i class='{{$accion->documento}}' style='font-size:24px;color:{{$accion->color}}' ></i></a></td>
                            <td>{{$accion->name}}</td>
                            <td>{{$accion->cargo}}</td>
                            <td>{{$accion->institucion}}</td>
                            <td>{{$accion->nivelinstitucion}}</td>
                            <td>{{$accion->provincia}}</td>
                            <td>{{$accion->distrito}}</td>
                            <td>{{$accion->ugel}}</td>
                        </tr>
                        @endforeach
                        @else  <!-- Esto es para DRE-->
                        @foreach ($accions as $accion)
                        <tr>
                            <td>{{$accion->nombreAccion}}</td>
                            <td>{{$accion->lugar}}</td>
                            <td>{{date('d-m-Y', strtotime($accion->fecha))}}</td>
                            <td align="center"><a href="{{ route('accions.download', $accion->id) }}" , target="_blank"><i class='{{$accion->documento}}' style='font-size:24px;color:{{$accion->color}}' ></i></a></td>
                            <td>{{$accion->getUser->name}}</td>
                            <td>{{$accion->getUser->cargo}}</td>
                            <td>{{$accion->getUser->institucion}}</td>
                            <td>{{$accion->getUser->nivelinstitucion}}</td>
                            <td>{{$accion->getUser->provincia}}</td>
                            <td>{{$accion->getUser->distrito}}</td>
                            <td>{{$accion->getUser->ugel}}</td>
                        </tr>
                        @endforeach
                        @endif
                    @endif
                     </tbody>
                     </table>
                     <div class="form-inline">
                        <p>Total de Acciones Subidos: {{$accions->total()}}</p> <br>
                        {{$accions->appends(request()->only(['texto', 'nominstitucion', 'nivel', 'nomdocente']))->links()}}
                     </div>
                     
                          
                    
@stop

@section('css')
 
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() 
{
    

$('#accions').DataTable(
{
    scrollX: true,
    "bInfo" : false,
    "bPaginate": false, 
    "bFilter": false 
});
});
</script>
<script>
$(document).ready(function() {
    // Cargar UGELs con el año seleccionado por defecto al cargar la página
    var selectedYear = $('#anio').val();
    loadUgels(selectedYear);
    
    // Manejar cambio de año
    $('#anio').on('change', function() {
        var year = $(this).val();
        loadUgels(year);
        
        // Limpiar los otros selectores
        $('#instituciones').empty().append($('<option>', {
            value: '',
            text: 'Selecciona una institución'
        })).prop('disabled', true);
        
        $('#docentes').empty().append($('<option>', {
            value: '',
            text: 'Selecciona un Docente'
        })).prop('disabled', true);
    });
    
    function loadUgels(year) {
        $.ajax({
            url: "{{ route('get-ugels-acc') }}",
            method: 'GET',
            data: {
                anio: year,
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
    }
    
    // También actualiza los otros eventos Ajax para incluir el año
    $('#ugels').on('change', function() {
        var selectedUgel = $(this).val();
        var selectedYear = $('#anio').val();  // Obtener el año seleccionado
        
        $.ajax({
            url: "{{ route('buscarInstitucionporUgel-acc') }}",
            method: 'GET',
            data: {
                ugel: selectedUgel,
                anio: selectedYear,  // Incluir el año
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
        var selectedYear = $('#anio').val();  // Obtener el año seleccionado
        var searchTerm = $(this).val();
        
        $.ajax({
            url: "{{ route('buscarInstitucionesAccion') }}",
            method: 'GET',
            data: {
                ugel: selectedUgel,
                anio: selectedYear,  // Incluir el año
                term: searchTerm,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(data) {
                // El código existente para procesar los resultados
                var $institucionesSelect = $('#instituciones');
                $institucionesSelect.empty();
                
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
                    
                    $institucionesSelect.append($option);
                }
                
                $institucionesSelect.prop('disabled', false);
            },
            error: function() {
                alert('Error al cargar las instituciones.');
            }
        });
    });
});

$(document).ready(function() {
    $('#instituciones').on('change', function() {
        var selectedInstitucion = $(this).val();
        var selectedYear = $('#anio').val();  // Obtener el año seleccionado

        $.ajax({
            url: "{{ route('buscarDocenteporInstitucion-acc') }}",
            method: 'GET',
            data: {
                docente: selectedInstitucion,
                ugel: $('#ugels').val(),
                anio: selectedYear,  // Incluir el año
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(data) {
                // El código existente para procesar los resultados
                var $docentesSelect = $('#docentes');
                $docentesSelect.empty();
                
                $docentesSelect.append($('<option>', {
                    value: '',
                    text: 'Selecciona un Docente'
                }));
                
                for (var i = 0; i < data.length; i++) {
                    var docente = data[i].name;
                    var agendasCount = data[i].agendas_count;
                    
                    var $option = $('<option>', {
                        value: docente,
                        text: docente + ' (' + agendasCount + ' acciones)'
                    });
                    
                    if (agendasCount > 0) {
                        $option.addClass('docente-con-agendas');
                    }
                    
                    $docentesSelect.append($option);
                }
                
                $docentesSelect.prop('disabled', false);
            },
            error: function() {
                alert('Error al cargar los docentes.');
            }
        });
    });

    $('#docente').on('input', function() {
        var selectedInstitucion = $('#instituciones').val();
        var selectedYear = $('#anio').val();  // Obtener el año seleccionado
        var searchTerm = $(this).val();

        $.ajax({
            url: "{{ route('buscarDocentesAccion') }}",
            method: 'GET',
            data: {
                institucion: selectedInstitucion,
                anio: selectedYear,  // Incluir el año
                term: searchTerm,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(data) {
                // El código existente para procesar los resultados
                var $docentesSelect = $('#docentes');
                $docentesSelect.empty();
                
                $docentesSelect.append($('<option>', {
                    value: '',
                    text: 'Selecciona un Docente'
                }));
                
                for (var i = 0; i < data.length; i++) {
                    var docente = data[i].name;
                    var agendasCount = data[i].agendas_count;
                    
                    var $option = $('<option>', {
                        value: docente,
                        text: docente + ' (' + agendasCount + ' acciones)'
                    });
                    
                    if (agendasCount > 0) {
                        $option.addClass('docente-con-agendas');
                    }
                    
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