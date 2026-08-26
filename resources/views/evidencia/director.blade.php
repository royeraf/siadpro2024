@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="{{ asset("vendor/datatables/css/jquery.dataTables.css") }}" />
<link href="{{ asset("vendor/datatables/css/dataTables.bootstrap5.min.css") }}" rel="stylesheet">
@endsection

@section('title', 'Evidencia')

@section('content_header')
    <h1>Listado de Evidencias</h1>
@stop

@section('content')

<form action="{{route('buscarEvidenciaDirector')}}" method="get" class="row g-3">
 <div class="form-group col-md-3">
                <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                 <i class="fas fa-file"></i>
                </span>
                    <input type="text" class="form-control" name="texto" Placeholder="Nombre de la Evidencia">
                    
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

<table id="evidencias" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Nombre de Evidencia</th>
                    <th scope="col">Tipo de Evidencia</th>
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
                    @if(count($evidencias)<=0)
                    <tr>
                        <td colspan="8">No hay Evidencia</td>
                    </tr>
                    @else
                    @foreach ($evidencias as $evidencia)
                    <tr>
                        <td>{{$evidencia->nombreEvidencia}}</td>
                        <td>{{$evidencia->tipoevidencia}}</td>
                        <td>{{date('d-m-Y', strtotime($evidencia->updated_at))}}</td>
                        <td align="center"><a href="{{ route('evidencias.download', $evidencia->id) }}" , target="_blank"><i class='{{$evidencia->documento}}' style='font-size:24px;color:{{$evidencia->color}}' ></i></a></td>
                        <td>{{$evidencia->name}}</td>
                        <td>{{$evidencia->cargo}}</td>
                        <td>{{$evidencia->institucion}}</td>
                        <td>{{$evidencia->provincia}}</td>
                        <td>{{$evidencia->distrito}}</td>
                        <td>{{$evidencia->ugel}}</td>
                        <td>{{$evidencia->lugar}}</td>
                    </tr>
                    @endforeach
                     @endif
                     </tbody>
                     </table>
                     <div class="form-inline">
                        <p>Total de Evidencias Subidos: {{$evidencias->total()}}</p> <br>
                        {{$evidencias->links()}}
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

$('#evidencias').DataTable(
{
    scrollX: true,
    "bInfo" : false,
    "bPaginate": false, 
    "bFilter": false 
});
});
</script>
@stop