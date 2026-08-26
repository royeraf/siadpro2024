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
    </style>
@endsection

@section('title', 'Institucion')

@section('content_header')
    <h1>Listado de Institucion</h1>
@stop

@section('content')


<a href="tallers/create" class="btn btn-primary mb-3">CREAR</a>

<table id="tallers" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
    <thead class="bg-primary text-white">
        <tr>
            <th scope="col">Taller</th>
            <th scope="col">Plan</th>
            <th scope="col">Informe</th>
            <th scope="col">Evidencia</th>
            <th scope="col">Video</th>
            <th scope="col">Fecha Supervicion</th>
            <th scope="col">Docente</th>
            <th scope="col">Opciones</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($tallers as $taller)
        <tr>
            <td>{{$institucion->nombreTaller}}</td>
            <td>{{$institucion->plan}}</td>
            <td>{{$institucion->informe}}</td>
            <td>{{$institucion->fotoTaller}}</td>
            <td>{{$institucion->enlaceVideo}}</td>
            <td>{{$institucion->fechaSupervicion}}</td>
            <td>{{$institucion->docente}}</td>
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
<script src={{ asset("vendor/datatables/dataTables.buttons.min.js") }}></script>
<script src={{ asset("vendor/jszip/jszip.min.js") }}></script>
<script src={{ asset("vendor/pdfmake/pdfmake.min.js") }}></script>
<script src={{ asset("vendor/pdfmake/vfs_fonts.js") }}></script>
<script src={{ asset("vendor/datatables/buttons.html5.min.js") }}></script>
<script src={{ asset("vendor/datatables/buttons.print.min.js") }}></script>
<script>
    $(document).ready(function() 
   {
    $('#tallers').DataTable(
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

@stop