@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Lectura')

@section('content_header')
    @if(session('mensajeinternet'))
        <div class="alert alert-danger">
            {{ session('mensajeinternet') }}
        </div>
    @endif
    <h1>Listado de Espacio de Lectura en el Hogar</h1>
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
    
    @if(count($plans)<=0)
        <div class="alert alert-info">
            No se encontro Espacio de Lectura en el Hogar!
        </div>
    @endif

<form action="{{route('buscarPlan')}}" method="get" class="row g-3">
 <div class="form-group col-md-3">
                <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                 <i class="fas fa-file"></i>
                </span>
                    <input type="text" class="form-control" name="texto" Placeholder="Nombre de Lectura">
                    
                </div>
                </div>
                </div>
               
                <div class="form-group col-md-1">
                    <input type="submit" class="btn btn-primary" value="Buscar">
                </div>
                
        
        </form>

<a href="plans/create" class="btn btn-primary mb-3">+ NUEVO ESPACIO DE LECTURA</a>

<table id="plans" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Nombre de Espacio de Lectura</th>
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


                     @if(count($plans)<=0)
                    <tr>
                        <td colspan="8">No hay espacio de lectura en el Hogar</td>
                    </tr>

                    @else
                    @foreach ($plans as $plan)
                
                   @php
                         $fecha = date('Y', strtotime($plan->fecha));
                     @endphp
                    @if ($fecha == 2025)

                    
                    <tr>
                        <td>{{$plan->nombrePlan}}</td>
                        <td>{{$plan->descripcion}}</td>
                        <td>{{date('d-m-Y', strtotime($plan->fecha))}}</td>
                        <td align="center"><a href="{{ route('plans.download', $plan->id) }}" , target="_blank"><i class='{{$plan->documento}}' style='font-size:24px;color:{{$plan->color}}' ></i></a></td>
                        <td>{{$plan->getUser->name}}</td>
                        <td>{{$plan->getUser->institucion}}</td>
                        <td>{{$plan->getUser->provincia}}</td>
                        <td>{{$plan->getUser->distrito}}</td>
                        <td>{{$plan->getUser->ugel}}</td>
                        <td>
                            <form action="{{  route ('plans.destroy',$plan->id)}}" method="POST">
                            <a href="/plans/{{ $plan->id}}/edit" class="btn btn-warning"><i class="fas fa-edit"></i></a>
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
                        <p>Total de Plan Subidos: {{$plans->total()}}</p> <br>
                        {{$plans->links()}}
                     </div>
                     
                          
                    
@stop

@section('css')
<style>
    .fade {
        opacity: 0;
        transition: opacity 0.5s ease-out; /* Duraci��n de la transici��n */
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
                setTimeout(() => alert.remove(), 500); // Espera la transici��n y elimina
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
    
    $('#plans').DataTable({
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