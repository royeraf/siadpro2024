@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="{{ asset("vendor/datatables/css/jquery.dataTables.css") }}" />
<link href="{{ asset("vendor/datatables/css/dataTables.bootstrap5.min.css") }}" rel="stylesheet">
@endsection

@section('title', 'Asistencia')

@section('content_header')
    @if(session('mensajeinternet'))
        <div class="alert alert-danger">
            {{ session('mensajeinternet') }}
        </div>
    @endif
    <h1>Listado de Sectores</h1>
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
    
    @if(count($sectores)<=0)
        <div class="alert alert-info">
            No se encontro Sectores!
        </div>
    @endif

<form action="{{route('buscarSector')}}" method="get" class="row g-3">
 <div class="form-group col-md-3">
                <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                 <i class="fas fa-file"></i>
                </span>
                    <input type="text" class="form-control" name="texto" Placeholder="Nombre de la Sector">
                    
                </div>
                </div>
                </div>
                <div class="form-group col-md-3">
                <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                 <i class="fas fa-calendar"></i>
                </span>
                    <input type="date" class="form-control" name="fecha" Placeholder="fecha de publicacion">
                </div>
                </div>
                </div>
                <div class="form-group col-md-1">
                    <input type="submit" class="btn btn-primary" value="Buscar">
                </div>
                
        
        </form>

<a href="sectores/create" class="btn btn-primary mb-3">+ NUEVO REGISTRO</a>

<table id="sectores" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Nombre de la Asistencia</th>
                    <th scope="col">Descripci籀n</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Instituci籀n</th>
                    <th scope="col">Provincia</th>
                    <th scope="col">Distrito</th>
                    <th scope="col">UGEL</th>
                    <th scope="col">Opciones</th>
                    </tr>
                    </thead>
                    <tbody >

                    @if(count($sectores)<=0)
                    <tr>
                        <td colspan="8">NO HAY ASISTENCIA DE SCTORES</td>
                    </tr>
                    @else
                    @foreach ($sectores as $sector)
                
                   @php
                         $fecha = date('Y', strtotime($sector->fecha));
                     @endphp
                    @if ($fecha == 2025)
                    <tr>
                        <td>{{$sector->nombreSector}}</td>
                        <td>{{$sector->descripcion}}</td>
                        <td>{{date('d-m-Y', strtotime($sector->fecha))}}</td>
                        <td align="center"><a href="{{ route('sectores.download', $sector->id) }}" , target="_blank"><i class='{{$sector->documento}}' style='font-size:24px;color:{{$sector->color}}' ></i></a></td>
                        <td>{{$sector->getUser->name}}</td>
                        <td>{{$sector->getUser->institucion}}</td>
                        <td>{{$sector->getUser->provincia}}</td>
                        <td>{{$sector->getUser->distrito}}</td>
                        <td>{{$sector->getUser->ugel}}</td>
                        <td>
                            <form action="{{  route ('sectores.destroy',$sector->id)}}" method="POST">
                            <a href="/sectores/{{ $sector->id}}/edit" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            @csrf
                        
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    @endif
                     </tbody>
                     </table>
                     <div class="form-inline">
                        <p>Total de Sectores Subidos: {{$sectores->total()}}</p> <br>
                        {{$sectores->links()}}
                     </div>
                     
                          
                    
@stop

@section('css')
<style>
    .fade {
        opacity: 0;
        transition: opacity 0.5s ease-out; /* Duraci車n de la transici車n */
    }
</style>
 
@stop

@section('js')
<script src="{{ asset("vendor/datatables/jquery.dataTables.min.js") }}"></script>
<script src="{{ asset("vendor/datatables/dataTables.bootstrap5.min.js") }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            let alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 500); // Espera la transici車n y elimina
            }
        }, 3000); // 3000ms = 3 segundos
    });
</script>

<script>
    $(document).ready(function() {
    // Sobrescribir el mensaje de error de DataTables
    $.fn.dataTable.ext.errMode = 'none';
    
    // Mostrar mensaje personalizado en caso de error
    $(document).on('error.dt', function(e, settings, techNote, message) {
        console.log('Se ha producido un error en DataTables: ', message);
        // Si quieres mostrar un mensaje personalizado, puedes hacerlo as赤:
        // $('.dataTables_wrapper').prepend('<div class="alert alert-info">No se encontr車 acciones de sensibilizaci車n!</div>');
    });
    
    $('#sectores').DataTable({
        scrollX: true,
        "bInfo": false,
        "bPaginate": false, 
        "bFilter": false,
        // Deshabilitar advertencias de consola
        "language": {
            "emptyTable": "No se encontr車 Sectores!"
        }
    });
    
    // Eliminar el modal de error de DataTables si existe
    $('.dt-error').remove();
    
    // Cerrar autom芍ticamente cualquier alerta de DataTables
    setTimeout(function() {
        $('.dt-error').remove();
        $('[id^="DataTables_"]').remove();
    }, 100);
});
</script>
@stop