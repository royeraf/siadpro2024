@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'DifGeneralDRE')

@section('content_header')
    <h1>Listado de Acciones de Difusión</h1>
@stop

@section('content')

<form action="{{route('buscarDifusionGeneral')}}" method="get" class="row g-3">
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
                <select id="anio" name="anio" class="form-control">
                    <option value="">----Año-----</option>
                    <option value="2026" {{ request()->get('anio') == '2026' || !request()->get('anio') ? 'selected' : '' }}>2026</option>
                    <option value="2025" {{ request()->get('anio') == '2025' ? 'selected' : '' }}>2025</option>
                    <option value="2024" {{ request()->get('anio') == '2024' ? 'selected' : '' }}>2024</option>
                    <option value="2023" {{ request()->get('anio') == '2023' ? 'selected' : '' }}>2023</option>
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
                        <select id="nominstitucion" name="nominstitucion" class="form-control">
                            <option value="">----SELECCIONE INSTITUCION-----</option>
                            @foreach ($accions as $accion)
                                <option value="{{$accion->institucion}}">{{$accion->institucion}}</option>                            
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        @else         
        <span></span>  
        @endif 
    <div class="form-group col-md-3">
        <div class="col align-self-center">
            <div class="input-group-prepend">
            <span class="input-group-text">
            <i class="	fas fa-user-tie"></i>
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
        
    
    <div class="form-group col-md-1">
        <input type="submit" class="btn btn-primary" value="Buscar">
    </div>
    
    
</form>

<table id="accions" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Nombre de la Acción</th>
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
                            @if(count($accions)<=0)
                            <tr>
                                <td colspan="11">No hay Acciones de Difusión para el año seleccionado</td>
                            </tr>
                            @else
                                @if (count($rols) == 2) <!-- Esto es para Director -->
                                @foreach ($accions as $accion)
                                <tr>
                                    <td>{{$accion->nombreAccion}}</td>
                                    <td>{{$accion->descripcion}}</td>
                                    <td>{{date('d-m-Y', strtotime($accion->fecha))}}</td>
                                    <td align="center"><a href="#" data-file-viewer data-src="{{ route('visor.stream', ['path' => $accion->enlace]) }}" data-name="{{ basename($accion->enlace) }}" data-download="{{ route('accions.download', $accion->id) }}" title="Ver documento"><i class='{{$accion->documento}}' style='font-size:24px;color:{{$accion->color}}' ></i></a></td>
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
                                    <td>{{$accion->descripcion}}</td>
                                    <td>{{date('d-m-Y', strtotime($accion->fecha))}}</td>
                                    <td align="center"><a href="#" data-file-viewer data-src="{{ route('visor.stream', ['path' => $accion->enlace]) }}" data-name="{{ basename($accion->enlace) }}" data-download="{{ route('accions.download', $accion->id) }}" title="Ver documento"><i class='{{$accion->documento}}' style='font-size:24px;color:{{$accion->color}}' ></i></a></td>
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
                        {{$accions->appends(request()->only(['texto', 'nominstitucion', 'nivel', 'nomdocente', 'nivel', 'anio']))->links()}}
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
    // Obtener valores de la URL para inicializar los selectores
    const urlParams = new URLSearchParams(window.location.search);
    const selectedUgel = urlParams.get('ugels') || '';
    const selectedInstitucion = urlParams.get('instituciones') || '';
    const selectedDocente = urlParams.get('docentes') || '';
    const selectedYear = urlParams.get('anio') || $('#anio').val() || '2026';
    
    console.log("Valores iniciales:", {
        selectedYear,
        selectedUgel,
        selectedInstitucion,
        selectedDocente
    });
    
    // Inicializar con el año seleccionado
    $('#anio').val(selectedYear);
    
    // Cargar UGELs y preseleccionar si hay un valor
    loadUgels(selectedYear, function() {
        if (selectedUgel) {
            $('#ugels').val(selectedUgel);
            
            // Cargar instituciones basadas en la UGEL seleccionada
            loadInstituciones(selectedUgel, selectedYear, function() {
                if (selectedInstitucion) {
                    $('#instituciones').val(selectedInstitucion);
                    
                    // Cargar docentes basados en la institución seleccionada
                    loadDocentes(selectedInstitucion, selectedYear, function() {
                        if (selectedDocente) {
                            $('#docentes').val(selectedDocente);
                        }
                    });
                }
            });
        }
    });
    
    // Manejar cambio de año
    $('#anio').on('change', function() {
        var year = $(this).val() || '2026';
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
    
    function loadUgels(year, callback) {
        console.log("Cargando UGELs para el año:", year);
        $.ajax({
            url: "{{ route('get-ugels-dif') }}",
            method: 'GET',
            data: {
                anio: year
            },
            dataType: 'json',
            success: function(data) {
                console.log("Datos recibidos para UGELs:", data);
                var $ugelsSelect = $('#ugels');
                $ugelsSelect.empty(); // Limpiar opciones existentes

                // Agregar la opción predeterminada
                $ugelsSelect.append($('<option>', {
                    value: '',
                    text: ' Seleccione la UGEL: '
                }));

                // Verificar si hay datos
                if (data && data.length > 0) {
                    // Iterar sobre los datos recibidos y agregar las UGELs al select
                    for (var i = 0; i < data.length; i++) {
                        var ugel = data[i].ugel;
                        var docentesCount = data[i].docentes_count || 0;

                        // Crear una opción para cada UGEL con la cantidad de docentes
                        var $option = $('<option>', {
                            value: ugel,
                            text: ugel + ' (' + docentesCount + ' docente)'
                        });

                        // Agregar la opción al select
                        $ugelsSelect.append($option);
                    }
                } else {
                    console.log("No se encontraron UGELs para el año:", year);
                    $ugelsSelect.append($('<option>', {
                        value: '',
                        text: 'No hay UGELs disponibles'
                    }));
                }
                
                // Llamar al callback si se proporciona
                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar UGELs. Status:", status);
                console.error("Error:", error);
                console.error("Respuesta:", xhr.responseText);
                alert('Error al cargar las UGELs. Consulta la consola para más detalles.');
                
                // Llamar al callback incluso si hay error
                if (typeof callback === 'function') {
                    callback();
                }
            }
        });
    }
    
    function loadInstituciones(ugel, year, callback) {
        console.log("Cargando instituciones para UGEL:", ugel, "y año:", year);
        $.ajax({
            url: "{{ route('buscarInstitucionporUgel-dif') }}",
            method: 'GET',
            data: {
                ugel: ugel,
                anio: year
            },
            dataType: 'json',
            success: function(data) {
                console.log("Datos de instituciones:", data);
                var $institucionesSelect = $('#instituciones');
                $institucionesSelect.empty(); // Limpia las opciones existentes

                // Agrega la opción predeterminada
                $institucionesSelect.append($('<option>', {
                    value: '',
                    text: 'Selecciona una institución'
                }));

                // Verificar si hay datos
                if (data && data.length > 0) {
                    // Itera a través de los datos y agrega las instituciones con sus cantidades
                    for (var i = 0; i < data.length; i++) {
                        var institucion = data[i].nomInstitucion;
                        var docentesCount = data[i].agendas_count || 0;
                        var totalDocentes = data[i].total_docentes || 0;

                        // Crea una opción con el nombre de la institución y las cantidades
                        var $option = $('<option>', {
                            value: institucion,
                            text: institucion + ' (' + docentesCount + ' docentes, ' + totalDocentes + ' total)'
                        });

                        // Agrega la opción a la lista desplegable
                        $institucionesSelect.append($option);
                    }
                } else {
                    console.log("No se encontraron instituciones para la UGEL:", ugel);
                    $institucionesSelect.append($('<option>', {
                        value: '',
                        text: 'No hay instituciones disponibles'
                    }));
                }

                $institucionesSelect.prop('disabled', false); // Habilita la lista desplegable
                
                // Llamar al callback si se proporciona
                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar instituciones. Status:", status);
                console.error("Error:", error);
                console.error("Respuesta:", xhr.responseText);
                alert('Error al cargar las instituciones. Consulta la consola para más detalles.');
                
                // Llamar al callback incluso si hay error
                if (typeof callback === 'function') {
                    callback();
                }
            }   
        });
    }
    
    function loadDocentes(institucion, year, callback) {
        console.log("Cargando docentes para institución:", institucion, "y año:", year);
        $.ajax({
            url: "{{ route('buscarDocenteporInstitucion-dif') }}",
            method: 'GET',
            data: {
                docente: institucion,
                ugel: $('#ugels').val(),
                anio: year
            },
            dataType: 'json',
            success: function(data) {
                console.log("Datos de docentes:", data);
                var $docentesSelect = $('#docentes');
                $docentesSelect.empty(); // Limpiar opciones existentes

                // Agregar la opción predeterminada
                $docentesSelect.append($('<option>', {
                    value: '',
                    text: 'Selecciona un Docente'
                }));

                // Verificar si hay datos
                if (data && data.length > 0) {
                    for (var i = 0; i < data.length; i++) {
                        var docente = data[i].name;
                        var agendasCount = data[i].agendas_count || 0;

                        // Crear una opción con el nombre del docente y la cantidad de agendas registradas
                        var $option = $('<option>', {
                            value: docente,
                            text: docente + ' (' + agendasCount + ' acciones)'
                        });
                        
                        // Si el docente tiene al menos una agenda, aplicar una clase CSS
                        if (agendasCount > 0) {
                            $option.addClass('docente-con-agendas');
                        }
                        
                        // Agregar la opción al elemento <select>
                        $docentesSelect.append($option);
                    }
                } else {
                    console.log("No se encontraron docentes para la institución:", institucion);
                    $docentesSelect.append($('<option>', {
                        value: '',
                        text: 'No hay docentes disponibles'
                    }));
                }
                
                // Habilitar el elemento <select>
                $docentesSelect.prop('disabled', false);
                
                // Llamar al callback si se proporciona
                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar docentes. Status:", status);
                console.error("Error:", error);
                console.error("Respuesta:", xhr.responseText);
                alert('Error al cargar los docentes. Consulta la consola para más detalles.');
                
                // Llamar al callback incluso si hay error
                if (typeof callback === 'function') {
                    callback();
                }
            }
        });
    }
    
    // Eventos de cambio
    $('#ugels').on('change', function() {
        var selectedUgel = $(this).val();
        var selectedYear = $('#anio').val() || '2026';
        
        if (selectedUgel) {
            loadInstituciones(selectedUgel, selectedYear);
        } else {
            // Limpiar y deshabilitar selectores dependientes
            $('#instituciones').empty().append($('<option>', {
                value: '',
                text: 'Selecciona una institución'
            })).prop('disabled', true);
            
            $('#docentes').empty().append($('<option>', {
                value: '',
                text: 'Selecciona un Docente'
            })).prop('disabled', true);
        }
    });
    
    $('#instituciones').on('change', function() {
        var selectedInstitucion = $(this).val();
        var selectedYear = $('#anio').val() || '2026';
        
        if (selectedInstitucion) {
            loadDocentes(selectedInstitucion, selectedYear);
        } else {
            // Limpiar y deshabilitar selector de docentes
            $('#docentes').empty().append($('<option>', {
                value: '',
                text: 'Selecciona un Docente'
            })).prop('disabled', true);
        }
    });
    
    // Los otros eventos de búsqueda en tiempo real
    $('#institucion').on('input', function() {
        var selectedUgel = $('#ugels').val();
        var searchTerm = $(this).val();
        var selectedYear = $('#anio').val() || '2026';
        
        if (selectedUgel && searchTerm.length >= 2) {
            $.ajax({
                url: "{{ route('buscarInstitucionesDifusion') }}",
                method: 'GET',
                data: {
                    ugel: selectedUgel,
                    term: searchTerm,
                    anio: selectedYear
                },
                dataType: 'json',
                success: function(data) {
                    console.log("Búsqueda de instituciones:", data);
                    var $institucionesSelect = $('#instituciones');
                    $institucionesSelect.empty();
                    
                    $institucionesSelect.append($('<option>', {
                        value: '',
                        text: 'Selecciona una institución'
                    }));
                    
                    if (data && data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            var institucion = data[i].nomInstitucion;
                            var agendasCount = data[i].agendas_count || 0;
                            var totalDocentes = data[i].total_docentes || 0;
                            
                            var $option = $('<option>', {
                                value: institucion,
                                text: institucion + ' (' + agendasCount + ' docentes, ' + totalDocentes + ' total)'
                            });
                            
                            if (agendasCount > 0) {
                                $option.addClass('docente-con-agendas');
                            }
                            
                            $institucionesSelect.append($option);
                        }
                    } else {
                        $institucionesSelect.append($('<option>', {
                            value: '',
                            text: 'No se encontraron instituciones'
                        }));
                    }
                    
                    $institucionesSelect.prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error("Error en búsqueda de instituciones:", error);
                    console.error("Respuesta:", xhr.responseText);
                }
            });
        }
    });
    
    $('#docente').on('input', function() {
        var selectedInstitucion = $('#instituciones').val();
        var searchTerm = $(this).val();
        var selectedYear = $('#anio').val() || '2026';
        
        if (selectedInstitucion && searchTerm.length >= 2) {
            $.ajax({
                url: "{{ route('buscarDocentesDifusion') }}",
                method: 'GET',
                data: {
                    institucion: selectedInstitucion,
                    term: searchTerm,
                    anio: selectedYear
                },
                dataType: 'json',
                success: function(data) {
                    console.log("Búsqueda de docentes:", data);
                    var $docentesSelect = $('#docentes');
                    $docentesSelect.empty();
                    
                    $docentesSelect.append($('<option>', {
                        value: '',
                        text: 'Selecciona un Docente'
                    }));
                    
                    if (data && data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            var docente = data[i].name;
                            var agendasCount = data[i].agendas_count || 0;
                            
                            var $option = $('<option>', {
                                value: docente,
                                text: docente + ' (' + agendasCount + ' acciones)'
                            });
                            
                            if (agendasCount > 0) {
                                $option.addClass('docente-con-agendas');
                            }
                            
                            $docentesSelect.append($option);
                        }
                    } else {
                        $docentesSelect.append($('<option>', {
                            value: '',
                            text: 'No se encontraron docentes'
                        }));
                    }
                    
                    $('#docentes').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error("Error en búsqueda de docentes:", error);
                    console.error("Respuesta:", xhr.responseText);
                }
            });
        }
    });
});
</script>
@stop