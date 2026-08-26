@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="{{ asset("vendor/datatables/css/jquery.dataTables.css") }}" />
<link href="{{ asset("vendor/datatables/css/dataTables.bootstrap5.min.css") }}" rel="stylesheet">
@endsection

@section('title', 'Informe')

@section('content_header')
    @if(session('mensajeinternet'))
        <div class="alert alert-danger">
            {{ session('mensajeinternet') }}
        </div>
    @endif
    <h1>Listado de Biblioteca en el Aula Ugel</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if ($errors->has('documento'))
    <div class="alert alert-danger">
        {{ $errors->first('documento') }}
    </div>
    @endif
    
    @if(count($informes)<=0)
        <div class="alert alert-info">
            No se encontro bibliotecas en el aula!
        </div>
    @endif

<form action="{{route('buscarInformeUgel')}}" method="get" class="row g-3">
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
                    <option value="2025" {{ request('year') == '2025' || !request('year') ? 'selected' : '' }}>2025</option>
                    <option value="2024" {{ request('year') == '2024' ? 'selected' : '' }}>2024</option>
                    <option value="2023" {{ request('year') == '2023' ? 'selected' : '' }}>2023</option>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group col-md-3">
        <div class="col align-self-center">
            <div class="input-group-prepend">
            <span class="input-group-text">
            <i class="fas fa-award"></i>
            </span>
                <div class="col-md-9">
                    <select id="nivel" name="nivel" class="form-control">
                        <option value="">----SELECCIONE NIVEL-----</option>
                            <option value="Escolarizado">Escolarizado</option>
                            <option value="No escolarizado - PRONOEI">No escolarizado - PRONOEI</option>      
                    </select>
                </div>
            </div>
        </div>
    </div> 
    <div class="form-group col-md-3">
        <div class="col align-self-center">
            <div class="input-group-prepend">
            <span class="input-group-text">
            <i class="fas fa-calendar"></i>
            </span>
                <div class="col-md-10">
                    <select id="nominstitucion" name="nominstitucion" class="form-control">
                        <option value="">----SELECCIONE INSTITUCION-----</option>
                        @foreach ($informes as $informe)
                            <option value="{{$informe->institucion}}">{{$informe->institucion}}</option>                            
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

<table id="informes" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Nombre Nombre de la Biblioteca</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Institución</th>
                    <th scope="col">TIpo de II.EE.</th>
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
                        {{$informes->appends(request()->only(['texto', 'nominstitucion', 'nivel', 'year']))->links()}}
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
    // Función para obtener los parámetros actuales
    function getCurrentParams() {
        const params = new URLSearchParams(window.location.search);
        const year = params.get('year') || '2025'; // Asegurar que el año siempre esté incluido
        params.set('year', year);
        return params.toString();
    }

    $('#informes').DataTable({
        scrollX: true,
        dom: 'Bfrtip', // Activa los botones
        buttons: [
            {
                extend: 'print',
                text: '<i class="fas fa-print"> Imprimir</i>',
                className: 'btn btn-warning',
                exportOptions: { modifier: { page: 'all' } },
                action: function (e, dt, node, config) {
                    // Incluir el parámetro year en la URL
                    var url = dt.ajax.url() || window.location.href;
                    var params = getCurrentParams();
                    if (url.indexOf('?') > -1) {
                        url = url + '&' + params;
                    } else {
                        url = url + '?' + params;
                    }
                    window.print();
                }
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

// Script para limitar la entrada del DNI a 8 dígitos
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
    // Sobrescribir el mensaje de error de DataTables
    $.fn.dataTable.ext.errMode = 'none';
    
    // Mostrar mensaje personalizado en caso de error
    $(document).on('error.dt', function(e, settings, techNote, message) {
        console.log('Se ha producido un error en DataTables: ', message);
        // Si quieres mostrar un mensaje personalizado, puedes hacerlo así:
        // $('.dataTables_wrapper').prepend('<div class="alert alert-info">No se encontró acciones de sensibilización!</div>');
    });
    
    $('#informes').DataTable({
        scrollX: true,
        "bInfo": false,
        "bPaginate": false, 
        "bFilter": false,
        // Deshabilitar advertencias de consola
        "language": {
            "emptyTable": "No se encontró acciones de difusion!"
        }
    });
    
    // Eliminar el modal de error de DataTables si existe
    $('.dt-error').remove();
    
    // Cerrar automáticamente cualquier alerta de DataTables
    setTimeout(function() {
        $('.dt-error').remove();
        $('[id^="DataTables_"]').remove();
    }, 100);
});
</script>
@stop