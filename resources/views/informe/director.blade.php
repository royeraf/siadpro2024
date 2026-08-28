@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Biblioteca')

@section('content_header')
    @if(session('mensajeinternet'))
        <div class="alert alert-danger">
            {{ session('mensajeinternet') }}
        </div>
    @endif
    <h1>Listado de Biblioteca en el Aula Director</h1>
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

 <form action="{{route('buscarInformeDirector')}}" method="get" class="row g-3">
 <div class="form-group col-md-3">
                <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                 <i class="fas fa-file"></i>
                </span>
                    <input type="text" class="form-control" name="texto" Placeholder="Nombre de la Biblioteca">
                    
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
                    <th scope="col">Nombre de la Biblioteca</th>
                    <th scope="col">Descripcion</th>
                    <th scope="col">Fechas</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Instituci��n</th>
                    <th scope="col">Provincia</th>
                    <th scope="col">Distrito</th>
                    <th scope="col">UGEL</th>
                    </tr>
                    </thead>
                    <tbody >
                    @if(count($informes)<=0)
                    <tr>
                        <td >No hay Biblioteca en el Aula</td>
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
                        {{$informes->links()}}
                     </div>
                     
                          
                    
@stop

@section('css')
 
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
    // Sobrescribir el mensaje de error de DataTables
    $.fn.dataTable.ext.errMode = 'none';
    
    // Mostrar mensaje personalizado en caso de error
    $(document).on('error.dt', function(e, settings, techNote, message) {
        console.log('Se ha producido un error en DataTables: ', message);
        // Si quieres mostrar un mensaje personalizado, puedes hacerlo as��:
        // $('.dataTables_wrapper').prepend('<div class="alert alert-info">No se encontr�� acciones de sensibilizaci��n!</div>');
    });
    
    $('#informes').DataTable({
        scrollX: true,
        "bInfo": false,
        "bPaginate": false, 
        "bFilter": false,
        // Deshabilitar advertencias de consola
        "language": {
            "emptyTable": "No se encontr�� acciones de difusion!"
        }
    });
    
    // Eliminar el modal de error de DataTables si existe
    $('.dt-error').remove();
    
    // Cerrar autom��ticamente cualquier alerta de DataTables
    setTimeout(function() {
        $('.dt-error').remove();
        $('[id^="DataTables_"]').remove();
    }, 100);
});
</script>
@stop