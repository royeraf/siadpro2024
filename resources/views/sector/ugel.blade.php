@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Asistencia')

@section('content_header')
    <h1>Listado de SECTORES UGEL</h1>
@stop

@section('content')

<form action="{{route('buscarSectorUgel')}}" method="get" class="row g-3">
    @csrf
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
                    <option value="2025" {{ $selectedYear == 2025 ? 'selected' : '' }}>2025</option>
                    <option value="2024" {{ $selectedYear == 2024 ? 'selected' : '' }}>2024</option>
                    <option value="2023" {{ $selectedYear == 2023 ? 'selected' : '' }}>2023</option>
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
                <div class="col-md-4">
                    <input type="text" class="form-control" id="institucion" name="institucion" autocomplete="off">                    
                </div>
                <div class="col-md-6">
                    <select name="nombinstitucion" id="instituciones-similares" class="form-control">
                        <option value="">Selecciona una institución</option>
                    </select>
                </div>
                
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
                    <th scope="col">Nombre de la Asistencia</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Institución</th>
                    <th scope="col">Nivel</th>
                    <th scope="col">Provincia</th>
                    <th scope="col">Distrito</th>
                    <th scope="col">UGEL</th>
                    </tr>
                    </thead>
                    <tbody >
                    @if(count($sectores)<=0)
                    <tr>
                        <td colspan="8">No hay Asistencia Técnica</td>
                    </tr>
                    @else
                    @foreach ($sectores as $sector)
                    <tr>
                        <td>{{$sector->nombreSector}}</td>
                        <td>{{$sector->descripcion}}</td>
                        <td>{{date('d-m-Y', strtotime($sector->fecha))}}</td>
                        <td align="center"><a href="{{ route('sectores.download', $sector->id) }}" , target="_blank"><i class='{{$sector->documento}}' style='font-size:24px;color:{{$sector->color}}' ></i></a></td>
                        <td>{{$sector->name}}</td>
                        <td>{{$sector->cargo}}</td>
                        <td>{{$sector->institucion}}</td>
                        <td>{{$sector->nivelinstitucion}}</td>
                        <td>{{$sector->provincia}}</td>
                        <td>{{$sector->distrito}}</td>
                        <td>{{$sector->ugel}}</td>
                    </tr>
                    @endforeach
                     @endif
                     </tbody>
                     </table>
                     <div class="form-inline">
                        <p>Total de Sectores Subidos: {{$sectores->total()}}</p> <br>
                        {{$sectores->appends(request()->only(['texto', 'nominstitucion', 'nivel', 'year']))->links()}}
                     </div>
                     
                          
                    
@stop

@section('css')
 
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


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

<script>
    $(document).ready(function() {
        // Manejar el evento de escritura en el input
        $('#institucion').on('input', function() {
            var searchTerm = $(this).val();
            var selectedYear = $('#year').val();
            
            console.log("Buscando instituciones con término: " + searchTerm + " para el año: " + selectedYear);

            // Realizar una solicitud AJAX para buscar instituciones similares
            $.ajax({
                url: "{{ route('buscarInstituciones') }}",
                method: "GET",
                data: {
                    term: searchTerm,
                    year: selectedYear,
                    _token: "{{ csrf_token() }}" // Incluye el token CSRF
                },
                success: function(data) {
                    console.log("Instituciones encontradas:", data);
                    
                    // Limpiar y actualizar el select con las instituciones encontradas
                    var select = $('#instituciones-similares');
                    select.empty();
                    select.append('<option value="">Selecciona una institución</option>');

                    // Agregar las instituciones encontradas al select
                    $.each(data, function(index, value) {
                        select.append('<option value="' + value + '">' + value + '</option>');
                    });
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.log("Error en la solicitud AJAX:", textStatus, errorThrown);
                    console.log("Respuesta del servidor:", xhr.responseText);
                }
            });
        });

        // También activar la búsqueda cuando cambie el año
        $('#year').on('change', function() {
            console.log("Año cambiado a: " + $(this).val());
            // Si ya hay un término de búsqueda, volver a realizar la búsqueda
            if ($('#institucion').val().trim() !== '') {
                $('#institucion').trigger('input');
            }
        });
    });
</script>
@stop