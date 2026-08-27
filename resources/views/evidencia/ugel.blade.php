@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('title', 'Asistencia')

@section('content_header')
    <h1>Listado de Asistencia Técnica Para Especialista UGEL</h1>
@stop

@section('content')

<form action="{{route('buscarEvidenciaUgel')}}" method="get" class="row g-3">
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
                <i class="fas fa-calendar"></i>
                </span>
                <select class="form-control" id="anio" name="anio">
                    <option value="" disabled>Seleccione Año</option>
                    <option value="2026" {{ request('anio') == '2026' || !request('anio') ? 'selected' : '' }}>2026</option>
                    <option value="2025" {{ request('anio') == '2025' ? 'selected' : '' }}>2025</option>
                    <option value="2024" {{ request('anio') == '2024' ? 'selected' : '' }}>2024</option>
                    <option value="2023" {{ request('anio') == '2023' ? 'selected' : '' }}>2023</option>
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
            <i class="fas fa-building"></i>
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

<table id="evidencias" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>   
                    <th scope="col">Nombre de la Asistencia</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Documento</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Institución</th>
                    <th scope="col">Tipo de II.EE.</th>
                    <th scope="col">Provincia</th>
                    <th scope="col">Distrito</th>
                    <th scope="col">UGEL</th>
                    </tr>
                    </thead>
                    <tbody >
                    @if(count($evidencias)<=0)
                    <tr>
                        <td colspan="11">No hay Asistencia Técnica</td>
                    </tr>
                    @else
                    @foreach ($evidencias as $evidencia)
                    <tr>
                        <td>{{$evidencia->nombreEvidencia}}</td>
                        <td>{{$evidencia->descripcion}}</td>
                        <td>{{date('d-m-Y', strtotime($evidencia->fecha))}}</td>
                        <td align="center"><a href="{{ route('evidencias.download', $evidencia->id) }}" , target="_blank"><i class='{{$evidencia->documento}}' style='font-size:24px;color:{{$evidencia->color}}' ></i></a></td>
                        <td>{{$evidencia->name}}</td>
                        <td>{{$evidencia->cargo}}</td>
                        <td>{{$evidencia->institucion}}</td>
                        <td>{{$evidencia->nivelinstitucion}}</td>
                        <td>{{$evidencia->provincia}}</td>
                        <td>{{$evidencia->distrito}}</td>
                        <td>{{$evidencia->ugel}}</td>
                    </tr>
                    @endforeach
                     @endif
                     </tbody>
                     </table>
                     <div class="form-inline">
                        <p>Total de Evidencias Subidos: {{$evidencias->total()}}</p> <br>
                        {{$evidencias->appends(request()->only(['texto', 'nombinstitucion', 'nivel', 'anio']))->links()}}
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

$('#evidencias').DataTable(
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
        // Evento de cambio para el año 
        $('#anio').on('change', function() {
            // Si cambia el año y hay texto en el campo de institución, actualizar las sugerencias
            if ($('#institucion').val().length > 0) {
                buscarInstituciones();
            }
        });

        // Manejar el evento de escritura en el input
        $('#institucion').on('input', function() {
            buscarInstituciones();
        });

        // Función para buscar instituciones
        function buscarInstituciones() {
            var searchTerm = $('#institucion').val();
            var selectedYear = $('#anio').val();

            // Realizar una solicitud AJAX para buscar instituciones similares
            $.ajax({
                url: "{{ route('buscarInstituciones') }}",
                method: "GET",
                data: {
                    term: searchTerm,
                    anio: selectedYear,
                    _token: "{{ csrf_token() }}" // Incluye el token CSRF
                },
                success: function(data) {
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
                }
            });
        }
    });
</script>
@stop