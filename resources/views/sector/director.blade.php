@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="{{ asset("vendor/datatables/css/jquery.dataTables.css") }}" />
<link href="{{ asset("vendor/datatables/css/dataTables.bootstrap5.min.css") }}" rel="stylesheet">
@endsection

@section('title', 'Sector')

@section('content_header')
    <h1>Listado de Sectores</h1>
@stop

@section('content')

<form action="{{route('buscarSectorDirector')}}" method="get" class="row g-3">
 <div class="form-group col-md-3">
                <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                 <i class="fas fa-file"></i>
                </span>
                    <input type="text" class="form-control" name="texto" Placeholder="Nombre de Sector">
                    
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

<table id="sectores" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Nombre de Sector</th>
                    <th scope="col">Tipo de Sector</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Institución</th>
                    <th scope="col">Provincia</th>
                    <th scope="col">Distrito</th>
                    <th scope="col">Ugel</th>
                    <th scope="col">Lugar</th>
                    </tr>
                    </thead>
                    <tbody >
                    @if(count($sectores)<=0)
                    <tr>
                        <td colspan="8">No hay Sector</td>
                    </tr>
                    @else
                    @foreach ($sectores as $sector)
                    <tr>
                        <td>{{$sector->nombreSector}}</td>
                        <td>{{$sector->tiposector}}</td>
                        <td>{{date('d-m-Y', strtotime($sector->updated_at))}}</td>
                        <td align="center"><a href="{{ route('sectores.download', $sector->id) }}" , target="_blank"><i class='{{$sector->documento}}' style='font-size:24px;color:{{$sector->color}}' ></i></a></td>
                        <td>{{$sector->name}}</td>
                        <td>{{$sector->cargo}}</td>
                        <td>{{$sector->institucion}}</td>
                        <td>{{$sector->provincia}}</td>
                        <td>{{$sector->distrito}}</td>
                        <td>{{$sector->ugel}}</td>
                        <td>{{$sector->lugar}}</td>
                    </tr>
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
 
@stop

@section('js')
<script src="{{ asset("vendor/datatables/jquery.dataTables.min.js") }}"></script>
<script src="{{ asset("vendor/datatables/dataTables.bootstrap5.min.js") }}"></script>

<script>
    $(document).ready(function() 
{

$('#sectores').DataTable(
{
    scrollX: true,
    "bInfo" : false,
    "bPaginate": false, 
    "bFilter": false 
});
});
</script>
@stop