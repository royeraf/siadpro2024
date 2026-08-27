@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    /* Estilos para el contador de usuarios */
    .stats-card {
        background: rgb(0, 123, 255); /* Azul más claro */
        color: white;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 15px;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-3px);
    }
    
    .stats-icon {
        float: left;
        font-size: 28px;
        margin-right: 12px;
        color: rgba(255, 255, 255, 0.8);
    }
    
    .stats-number {
        font-size: 22px;
        font-weight: 700;
        display: block;
        color: #ffff00;
        text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
    }
    
    .stats-title {
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.9;
        margin-top: 3px;
    }
</style>


@endsection

@section('title', 'Usuario')

@section('content_header')
    <h1>Listado de Usuarios</h1>
@stop

@section('content')
<main>
<!-- Contador de usuarios -->
<div class="row mb-3">
    <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="stats-card">
            <div class="stats-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stats-info">
                <span class="stats-number">{{ $users->total() }}</span>
                <span class="stats-title">Total de Usuarios</span>
            </div>
        </div>
    </div>
</div>

<form action="{{route('buscarUser')}}" method="get" class="row g-3">
 <div class="form-group col-md-3">
                <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                 <i class="fas fa-file"></i>
                </span>
                
                    <input type="text" class="form-control" id="texto" name="texto" Placeholder="Ingrese DNI">
                    
                </div>
                </div>
                </div>
                <div class="form-group col-md-3">
                <div class="col align-self-center">
                <div class="input-group-prepend">
                <span class="input-group-text">
                <i class="fas fa-user-tie"></i>
                </span>
                    <input type="text" class="form-control" id="cargos" name="cargos" Placeholder="Ingrese el Cargo">
                </div>
                </div>
                </div>

                <div class="form-group col-md-3">
                    <div class="col-md-15 col align-self-center">
                        <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-calendar"></i>
                        </span>
                            <select class="form-control" id="ugel" name="ugel">
                                <option value=""> Seleccione la UGEL: </option>
                            </select>
                        </div>
                    </div>
                </div>



                <div class="form-group col-md-1">
                    <input type="submit" class="btn btn-primary" value="Buscar">
                </div>
                
        
        </form>

<a href="users/create" class="btn btn-primary mb-3">+ NUEVO USUARIO</a>

<!-- Añadir los datos completos para DataTables en un formato oculto -->
<div style="display:none;">
    <table id="full-users-data">
        <thead>
            <tr>
                <th>ESTADO</th>
                <th>ID</th>
                <th>DNI</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Cargo</th>
                <th>Institución</th>
                <th>UGEL</th>
                <th>Tipo de II.EE</th>
                <th>Provincia</th>
                <th>Distrito</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allUsers as $user)
            <tr>
                <td>{{$user->estado}}</td>
                <td>{{$user->id}}</td>
                <td>{{$user->dni}}</td>
                <td>{{$user->name}}</td>
                <td>{{$user->email}}</td>
                <td>{{$user->cargo}}</td>
                <td>{{$user->institucion}}</td>
                <td>{{$user->ugel}}</td>
                <td>{{$user->nivelinstitucion}}</td>
                <td>{{$user->provincia}}</td>
                <td>{{$user->distrito}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<table id="users" class="table table-striped table-bordered shadow-lg mt-4 display nowrap" style="width:100%">
                    <thead class="bg-primary text-white">
                    <tr>  
                    <th scope="col">ESTADO</th> 
                    <th scope="col">ID</th> 
                    <th scope="col">DNI</th>
                    <th scope="col">Usuario</th>
                    <th scope="col">Correo</th>
                    <th scope="col">Cargo</th>
                    <th scope="col">Institución</th>
                    <th scope="col">UGEL</th>
                    <th scope="col">Tipo de II.EE</th>
                    <th scope="col">Provincia</th>
                    <th scope="col">Distrito</th>
                    <th scope="col">Opciones</th>
                    </tr>
                    </thead>
                    <tbody >
                    @if(count($users)<=0)
                    <tr>
                        <td colspan="8">No hay Usuario</td>
                    </tr>
                    @else
                    @foreach ($users as $user)
                    <tr>
                        <td>{{$user->estado}}</td>
                        <td>{{$user->id}}</td>
                        <td>{{$user->dni}}</td>
                        <td>{{$user->name}}</td>
                        <td>{{$user->email}}</td>
                        <td>{{$user->cargo}}</td>
                        <td>{{$user->institucion}}</td>
                        <td>{{$user->ugel}}</td>
                        <td>{{$user->nivelinstitucion}}</td>
                        <td>{{$user->provincia}}</td>
                        <td>{{$user->distrito}}</td>
                        <td>
                        <a href="/users/{{ $user->id}}/edit" class="btn btn-info"><i class="fas fa-user-tag"></i></a>
                        <a href="/usuarios/{{ $user->id}}/edit" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                       {{-- Agregar el botón para cambiar estado --}}
                @if($user->estado == 1)
                <a href="{{ route('cambiarEstado', $user->id) }}" class="btn btn-danger"><i class="fas fa-times"></i> eliminar</a>
                @else
                <a href="{{ route('cambiarEstado', $user->id) }}" class="btn btn-success"><i class="fas fa-check"></i> Activar</a>
                @endif
                        </td>
                    </tr>
                    @endforeach
                     @endif
                     </tbody>
                     </table>
                     <div class="form-inline">
                        {{$users->appends(request()->only(['texto', 'cargos', 'ugel']))->links()}}
                     </div>
</main>
    <div id="subir__arriba">
        <i class="fas fa-angle-up"></i>
    </div>                    
                          
                    
@stop

@section('css')
<style>
        #subir__arriba{
    width: 70px;
    height: 70px;
    background: rgb(61, 68, 89);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 30px;
    position: fixed;
    bottom: 20px;
    cursor: pointer;
    display: none;
}
    </style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script>
$(document).ready(function() {
    // Obtener y establecer parámetros de la URL
    const urlSearchParams = new URLSearchParams(window.location.search);
    $('#texto').val(urlSearchParams.get('texto') || '');
    $('#cargos').val(urlSearchParams.get('cargos') || '');

    // Cargar las UGEL mediante AJAX
    $.ajax({
        url: "{{ route('get-ugels-users') }}",
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log("UGELs cargadas:", data);

            var $ugelSelect = $('#ugel');
            $ugelSelect.empty();

            // Agregar la opción predeterminada
            $ugelSelect.append($('<option>', {
                value: '',
                text: 'Seleccione la UGEL:'
            }));

            // Iterar sobre los datos recibidos y agregar las UGELs
            for (var i = 0; i < data.length; i++) {
                var ugel = data[i].ugel;
                $ugelSelect.append($('<option>', {
                    value: ugel,
                    text: ugel
                }));
            }

            // Establecer el valor seleccionado
            var selectedUgel = urlSearchParams.get('ugel') || '';
            if (selectedUgel) {
                $('#ugel').val(selectedUgel);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error cargando UGELs:", error);
            alert('Error al cargar las UGEL: ' + error);
        }
    });

    // Manejar envío del formulario
    $('form').on('submit', function(e) {
        e.preventDefault();
        const texto = $('#texto').val();
        const cargos = $('#cargos').val();
        const ugel = $('#ugel').val();

        const searchParams = new URLSearchParams();
        if (texto) searchParams.set('texto', texto);
        if (cargos) searchParams.set('cargos', cargos);
        if (ugel) searchParams.set('ugel', ugel);

        const newURL = window.location.pathname + '?' + searchParams.toString();
        window.location.href = newURL;
    });

    // Inicializar DataTables de manera segura
    try {
        if ($.fn.DataTable) {
            // Tabla oculta con todos los datos para exportación
            var fullDataTable = $('#full-users-data').DataTable({
                dom: 'Bfrtip',
                buttons: [],
                "paging": false,
                "searching": false
            });
            
            // Tabla visible con datos paginados y botones de exportación
            $('#users').DataTable({
                scrollX: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"> Imprimir</i>',
                        className: 'btn btn-warning',
                        exportOptions: {
                            columns: ':not(:last-child)' // Excluir la columna de opciones
                        },
                        action: function (e, dt, node, config) {
                            // Usar la acción de imprimir de la tabla visible
                            $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, node, config);
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"> Excel</i>',
                        className: 'btn btn-success',
                        exportOptions: {
                            columns: ':not(:last-child)' // Excluir la columna de opciones
                        },
                        action: function (e, dt, node, config) {
                            // Usar la tabla oculta con todos los datos para exportar
                            var exportConfig = $.extend(true, {}, config);
                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(
                                this, 
                                e, 
                                fullDataTable, 
                                node, 
                                exportConfig
                            );
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"> PDF</i>',
                        className: 'btn btn-danger',
                        exportOptions: {
                            columns: ':not(:last-child)' // Excluir la columna de opciones
                        },
                        action: function (e, dt, node, config) {
                            // Usar la tabla oculta con todos los datos para exportar
                            var exportConfig = $.extend(true, {}, config);
                            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(
                                this, 
                                e, 
                                fullDataTable, 
                                node, 
                                exportConfig
                            );
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fas fa-file-csv"> CSV</i>',
                        className: 'btn btn-info',
                        exportOptions: {
                            columns: ':not(:last-child)' // Excluir la columna de opciones
                        },
                        action: function (e, dt, node, config) {
                            // Usar la tabla oculta con todos los datos para exportar
                            var exportConfig = $.extend(true, {}, config);
                            $.fn.dataTable.ext.buttons.csvHtml5.action.call(
                                this, 
                                e, 
                                fullDataTable, 
                                node, 
                                exportConfig
                            );
                        }
                    },
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"> Copiar</i>',
                        className: 'btn btn-secondary',
                        exportOptions: {
                            columns: ':not(:last-child)' // Excluir la columna de opciones
                        }
                    }
                ],
                "processing": true,
                "bInfo": true,
                "bPaginate": true,
                "bFilter": true,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]]
            });
        }
    } catch (e) {
        console.warn("Error al inicializar DataTable:", e);
    }
});
</script>
@stop