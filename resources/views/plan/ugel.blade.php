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
    <h1>Listado de espacio de lectura en el hogar Ugel</h1>
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

<form action="{{route('buscarPlanUgel')}}" method="get" class="row g-3">
    <div class="form-group col-md-2">
        <div class="col align-self-center">
            <div class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-calendar-alt"></i>
                </span>
                <select class="form-control" id="year" name="year">
                    <option value="2023" {{ isset($selectedYear) && $selectedYear == 2023 ? 'selected' : '' }}>2023</option>
                    <option value="2024" {{ isset($selectedYear) && $selectedYear == 2024 ? 'selected' : '' }}>2024</option>
                    <option value="2025" {{ !isset($selectedYear) || $selectedYear == 2025 ? 'selected' : '' }}>2025</option>
                </select>
            </div>
        </div>
    </div>
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
            <i class="fas fa-school"></i>
            </span>
                <div class="col-md-10">
                    <select id="nominstitucion" name="nominstitucion" class="form-control">
                        <option value="">----SELECCIONE INSTITUCION-----</option>
                        @foreach ($plans as $plan)
                            <option value="{{$plan->institucion}}">{{$plan->institucion}}</option>                            
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

<table id="plans" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
    <thead class="bg-primary text-white">
    <tr>   
    <th scope="col">Nombre de Espacio de Lectura</th>
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
    @if(count($plans)<=0)
    <tr>
        <td colspan="11">No hay Espacio de Lectura en el Hogar</td>
    </tr>
    @else
    @foreach ($plans as $plan)
    <tr>
        <td>{{$plan->nombrePlan}}</td>
        <td>{{$plan->descripcion}}</td>
        <td>{{date('d-m-Y', strtotime($plan->fecha))}}</td>
        <td align="center"><a href="{{ route('plans.download', $plan->id) }}" , target="_blank"><i class='{{$plan->documento}}' style='font-size:24px;color:{{$plan->color}}' ></i></a></td>
        <td>{{$plan->name}}</td>
        <td>{{$plan->cargo}}</td>
        <td>{{$plan->institucion}}</td>
        <td>{{$plan->nivelinstitucion}}</td>
        <td>{{$plan->provincia}}</td>
        <td>{{$plan->distrito}}</td>
        <td>{{$plan->ugel}}</td>
    </tr>
    @endforeach
    @endif
    </tbody>
</table>
<div class="form-inline">
    <p>Total de Plan Subidos: {{$plans->total()}}</p> <br>
    {{$plans->appends(request()->only(['texto', 'nominstitucion', 'nivel', 'year']))->links()}}
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

<script src=https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js></script>
<script src=https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js></script>
<script src=https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js></script>
<script src=https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js></script>
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
    // Función para obtener los parámetros actuales incluyendo el año
    function getCurrentParams() {
        const params = new URLSearchParams(window.location.search);
        if (!params.has('year')) {
            params.append('year', '2025'); // Asegurarse de que year=2025 esté siempre presente por defecto
        }
        return params.toString();
    }

    // Cuando cambia el año, recargar la página
    $('#year').on('change', function() {
        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);
        params.set('year', $(this).val());
        
        // Mantener otros filtros si existen
        window.location.href = `${currentUrl.pathname}?${params.toString()}`;
    });

    // Sobrescribir el mensaje de error de DataTables
    $.fn.dataTable.ext.errMode = 'none';
    
    // IMPORTANTE: Solo una inicialización de DataTable
    $('#plans').DataTable({
        scrollX: true,
        dom: 'Bfrtip', // Activa los botones
        "bInfo": false,
        "bPaginate": false, 
        "bFilter": false,
        buttons: [
            {
                extend: 'print',
                text: '<i class="fas fa-print"> Imprimir</i>',
                className: 'btn btn-warning',
                exportOptions: { modifier: { page: 'all' } },
                action: function (e, dt, node, config) {
                    $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, node, config);
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
            }
        ],
        "language": {
            "emptyTable": "No se encontró espacio de lectura en el hogar!",
            "zeroRecords": "No se encontró espacio de lectura en el hogar!"
        }
    });
    
    // Eliminar el modal de error de DataTables si existe
    $('.dt-error').remove();
    
    // Ocultar mensajes de error de DataTables
    $('.dataTables_empty').parent().parent().hide();
    
    // Si no hay datos, ocultamos la tabla pero mantenemos los botones
    if ($('#plans tbody tr').length === 1 && $('#plans tbody tr td').length === 1) {
        $('#plans tbody').hide();
        $('.dt-buttons').css('margin-bottom', '20px');
    }
});

function imprimir() {
    window.print();
}

// Limitar el DNI a 8 caracteres numéricos
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
@stop