@extends('adminlte::page')
@section('css')

<link href=https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css>
<link href=https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css>
<link rel="stylesheet" href="/css/admin_custom.css">
<link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .dependencia {
            position: absolute;
            font-size: 20px;
        }
        
        /* Estilos para el contador de instituciones */
        .stats-card {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
        }
        
        .stats-icon {
            float: left;
            font-size: 28px;
            margin-right: 12px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .stats-number {
            font-size: 22px;
            font-weight: 700;
            display: block;
            color: #ffff00;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
        }
        
        .stats-title {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
            margin-top: 3px;
        }
    </style>
@endsection

@section('title', 'Institucion')

@section('content_header')
    <h1>Listado de Institucion</h1>
@stop

@section('content')

<!-- Contador de instituciones -->
<div class="row mb-3">
    <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-school"></i>
            </div>
            <div class="stats-info">
                <span class="stats-number">{{ $total }}</span>
                <span class="stats-title">Total de Instituciones</span>
            </div>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('institucion.index') }}">
    <div class="row justify-content-end align-items-center">
    <div class="col-md-3 d-flex align-items-center">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-calendar"></i>
                </span>
            </div>
            <select class="form-control" id="ugels" name="ugels">
                <option value="">Seleccione la UGEL</option>
                <!-- Opciones -->
            </select>
        </div>
    </div>
    <div class="col-auto d-flex align-items-center">
        <button type="submit" class="btn btn-primary">Buscar</button>
    </div>
</div>

</form>


<a href="institucions/create" class="btn btn-primary mb-3">CREAR</a>

<table id="institucions" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
    <thead class="bg-primary text-white">
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Institución</th>
            <th scope="col">Cod. Modular</th>
            <th scope="col">Nivel</th>
            <th scope="col">Provincia</th>
            <th scope="col">Distrito</th>
            <th scope="col">Centro Poblado</th>
            <th scope="col">Ugel</th>
            <th scope="col">Opciones</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($institucions as $institucion)
        <tr>
            <td>{{$institucion->id}}</td>
            <td>{{$institucion->nomInstitucion}}</td>
            <td>{{$institucion->codModular}}</td>
            <td>{{$institucion->nivel}}</td>
            <td>{{$institucion->provincia}}</td>
            <td>{{$institucion->distrito}}</td>
            <td>{{$institucion->centropoblado}}</td>
            <td>{{$institucion->ugel}}</td>
            <td>
                <form action="{{  route ('institucions.destroy',$institucion->id)}}" method="POST">
                <a href="/institucions/{{ $institucion->id}}/edit" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                @csrf
               
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@stop

@section('css')
    
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap5.min.js"></script>

<!--<script src=https://code.jquery.com/jquery-3.5.1.js></script>-->
<!--<script src=https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js></script>-->
<script src=https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js></script>
<script src=https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js></script>
<script src=https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js></script>
<script>
    $(document).ready(function() 
   {
    $('#institucions').DataTable(
        {
            scrollX: true,
            scrollY: true,
            language: {
                "sSearch": "BUSCAR:",
                "info": "MOSTRANDO DESDE EL INICIO AL FINAL DEL TOTAL DEREGISTROS",
                "infoFiltered": "(Filtrado un total de MAX registros)",
            },
           /* language: {
                "lengthMenu": "Mostrar MENU registros",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando registros del START al END de un total de TOTAL registros",
                "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "infoFiltered": "(filtrado de un total de MAX registros)",
                "sSearch": "Buscar:",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast":"Último",
                    "sNext":"Siguiente",
                    "sPrevious": "Anterior"
			     },
			     "sProcessing":"Procesando...",
            },*/
        //"lengthMenu": [[5,10, 50, -1], [5, 10, 50, "All"]]
        responsive: "true",
        dom: 'Bfrtip',
        buttons: [
            {
				extend:    'print',
				text:      '<i class="fas fa-print"> Imprimir</i> ',
				titleAttr: 'Imprimir',
				className: 'btn btn-warning'
			},
            {
				extend:    'excelHtml5',
				text:      '<i class="fas fa-file-excel"> Excel</i> ',
				titleAttr: 'Exportar a Excel',
				className: 'btn btn-success'
			},
            {
				extend:    'pdfHtml5',
				text:      '<i class="fas fa-file-pdf"> PDF</i> ',
				titleAttr: 'Exportar a PDF',
				className: 'btn btn-danger'
			},
            {
				extend:    'csv',
				text:      '<i class="fas fa-file-csv"> CSV</i> ',
				titleAttr: 'Exportar a CSV',
				className: 'btn btn-info'
			},
            {
				extend:    'copy',
				text:      '<i class="fas fa-copy"> Copiar</i> ',
				titleAttr: 'Copiar Tabla',
				className: 'btn btn-secondary'
			},
        ]
        });
   } );
       
    function imprimir()
        {
       
        window.print();
       
        }
</script>
<script>
    $(document).ready(function() {

        /* Ajax para la busqueda inicial de ugeles con cantidad de docentes que registraron agendas*/
$.ajax({
    url: "{{ route('get-ugels-acc') }}",
    method: 'GET',
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
    /* Ajax para la busqueda de institucion por ugel seleccionada con la informacion de docentes que registraron agendas*/ 
    $.ajax({
        url: "{{ route('buscarInstitucionporUgel-ins') }}", // Reemplaza con la ruta correcta en tu aplicación                
        method: 'GET',
        data: {
        ugel: selectedUgel,
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
          
    });
    
    /* Ajax para la busqueda de institucion por ugel seleccionada con la informacion de docentes que registraron agendas*/
});

$('#institucion').on('input', function() {
    var selectedUgel = $('#ugels').val();
    var searchTerm = $(this).val();
    
   /* Ajax para la busqueda en tiempo real de instituciones por ugel seleccionada con la informacion de docentes que registraron agendas*/
    $.ajax({
    url: "{{ route('buscarInstitucionesAccion') }}",
    method: 'GET',
    data: {
        ugel: selectedUgel,
        term: searchTerm, // Agrega el término de búsqueda
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
    var selectedDocente = $(this).val();

    /* Ajax para la busqueda de docente por institucion seleccionada con la informacion de docentes que registraron agendas*/
    $.ajax({
        url: "{{ route('buscarDocenteporInstitucion-acc') }}", // Reemplaza con la ruta correcta en tu aplicación                
        method: 'GET',
        data: {
        docente: selectedDocente,
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
                    text: docente + ' (' + agendasCount + ' acciones)'
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
    
    /* Ajax para la busqueda de docente por institucion seleccionada con la informacion de docentes que registraron agendas*/
});

$('#docente').on('input', function() {
    var selectedInstitucion = $('#instituciones').val();
    var searchTerm = $(this).val();

    /* Ajax para la busqueda en tiempo real de docentes con la informacion de docentes que registraron agendas*/
    $.ajax({
    url: "{{ route('buscarDocentesAccion') }}",
    method: 'GET',
    data: {
        institucion: selectedInstitucion,
        term: searchTerm, // Agrega el término de búsqueda
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
                value: docentes,
                text: docente + ' (' + agendasCount + ' acciones)'
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