@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Asistencia')

@section('content_header')
    @if(session('mensajeinternet'))
        <div class="alert alert-danger">
            {{ session('mensajeinternet') }}
        </div>
    @endif
    <h1>Listado de Asistencia Técnica</h1>
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
    
    @if(count($evidencias)<=0)
        <div class="alert alert-info">
            No se encontró asistencia!
        </div>
    @endif

<form action="{{route('buscarEvidencia')}}" method="get" class="row g-3">
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

<a href="evidencias/create" class="btn btn-primary mb-3">+ NUEVA ASISTENCIA TÉCNICA</a>

<table id="evidencias" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Nombre de la Asistencia</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Institución</th>
                    <th scope="col">Provincia</th>
                    <th scope="col">Distrito</th>
                    <th scope="col">UGEL</th>
                    <th scope="col">Opciones</th>
                    </tr>
                    </thead>
                    <tbody >
                        @if(count($evidencias)<=0)
                        <tr>
                            <td colspan="8">No hay Asistencia Técnica</td>
                        </tr>
                        @else
                        @foreach ($evidencias as $evidencia)
                    <tr>
                        <td>{{$evidencia->nombreEvidencia}}</td>
                        <td>{{$evidencia->descripcion}}</td>
                        <td>{{date('d-m-Y', strtotime($evidencia->fecha))}}</td>
                        <td align="center"><a href="{{ route('evidencias.download', $evidencia->id) }}" , target="_blank"><i class='{{$evidencia->documento}}' style='font-size:24px;color:{{$evidencia->color}}' ></i></a></td>
                        <td>{{$evidencia->getUser->name}}</td>
                        <td>{{$evidencia->getUser->institucion}}</td>
                        <td>{{$evidencia->getUser->provincia}}</td>
                        <td>{{$evidencia->getUser->distrito}}</td>
                        <td>{{$evidencia->getUser->ugel}}</td>
                        <td>
                            <form action="{{  route ('evidencias.destroy',$evidencia->id)}}" method="POST">
                            <a href="/evidencias/{{ $evidencia->id}}/edit" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            @csrf
                        
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
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
<style>
    .fade {
        opacity: 0;
        transition: opacity 0.5s ease-out; /* Duración de la transición */
    }
</style>
 
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            let alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 500); // Espera la transición y elimina
            }
        }, 3000); // 3000ms = 3 segundos
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            let alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 500); // Espera la transición y elimina
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
        // Si quieres mostrar un mensaje personalizado, puedes hacerlo así:
        // $('.dataTables_wrapper').prepend('<div class="alert alert-info">No se encontró acciones de sensibilización!</div>');
    });
    
    $('#evidencias').DataTable({
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25" integrity="sha384-nLoOnA/BDh8A/jxqtckg4DumuCGOBYUnNJLZdQz/zfYNp3wcjGSoWTAzgko06G/2" crossorigin="anonymous"></script>
<script>
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Registro guardado!',
            text: @json(session('success')),
            timer: 2500,
            showConfirmButton: false
        });
    @endif
</script>
@stop