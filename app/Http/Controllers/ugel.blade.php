@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Asistencia')

@section('content_header')
    <h1>Listado de Agendas de Lectura Para Especialista UGEL</h1>
@stop

@section('content')

<form action="{{route('buscarAgendaUgel')}}" method="get" class="row g-3">
 <div class="form-group col-md-3">
                <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                 <i class="fas fa-file"></i>
                </span>
                    <input type="text" class="form-control" name="texto" Placeholder="Nombre de la Institucion">
                </div>
                </div>
                </div>
           
                <div class="form-group col-md-1">
                    <input type="submit" class="btn btn-primary" value="Buscar">
                </div>
                
        
        </form>

<table id="agendas" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Docente</th>
                    <th scope="col">Nombre de la agenda</th>
                    <th scope="col">Descripcion</th>
                    <th scope="col">Fecha Inicio</th>
                    <th scope="col">Fecha Fin</th>
                    <th scope="col">Institución</th> 
                    <th scope="col">Provincia</th>
                    <th scope="col">Distrito</th>
                    <th scope="col">Ugel</th>
                    </tr>
                    </thead>
                    <tbody >
                    @if(count($agendas)<=0)
                    <tr>
                        <td colspan="8">No hay ninguna Agenda</td>
                    </tr>
                    @else
                    @foreach ($agendas as $agenda)
                    <tr>
                        <td>{{$agenda->nomDocente}}</td>
                        <td>{{$agenda->title}}</td>
                        <td>{{$agenda->evento}}</td>
                        <td>{{date('d-m-Y', strtotime($agenda->start))}}</td>
                        <td>{{date('d-m-Y', strtotime($agenda->end))}}</td>
                        <td>{{$agenda->institucion}}</td>
                        <td>{{$agenda->provincia}}</td>
                        <td>{{$agenda->distrito}}</td>
                        <td>{{$agenda->ugel}}</td>
                    </tr>
                    @endforeach
                     @endif
                     </tbody>
                     </table>
                     <div class="form-inline">
                        <p>Total de Evidencias Subidos: {{$agendas->total()}}</p> <br>
                        {{$agendas->links()}}
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

$('#agendas').DataTable(
{
    scrollX: true,
    "bInfo" : false,
    "bPaginate": false, 
    "bFilter": false 
});
});
</script>
@stop