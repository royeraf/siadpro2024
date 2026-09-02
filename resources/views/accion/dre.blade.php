@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css" />
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .table-container {
        width: 100%;
        overflow-x: auto;
    }
    .table-responsive {
        margin-bottom: 1rem;
    }
    #accions {
        width: 100%;
        font-size: 0.9rem;
    }
    #accions th {
        white-space: nowrap;
        padding: 8px 10px;
    }
    #accions td {
        padding: 6px 8px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.2em 0.5em;
    }
    .form-group {
        margin-bottom: 0.5rem;
    }
    .alert {
        padding: 0.5rem 1rem;
        margin-bottom: 1rem;
    }
</style>
@endsection
@section('title', 'Accion')

@section('content_header')
    @if(session('mensajeinternet'))
        <div class="alert alert-danger">
            {{ session('mensajeinternet') }}
        </div>
    @endif
    <h1>Listado de Acciones de Sensibilización</h1>
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
    
    @if(count($accions)<=0)
        <div class="alert alert-info">
            No se encontró acciones de sensibilización!
        </div>
    @endif

<div class="card">
    <div class="card-body">
        <form action="{{route('buscarAccionGeneral')}}" method="get" class="row g-3">
            <div class="form-group col-md-2">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-file"></i>
                    </span>
                    <input type="text" class="form-control" name="texto" id="dniInput" placeholder="DNI">        
                </div>
            </div>
            
            <div class="form-group col-md-2">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-calendar-year"></i>
                    </span>
                    <select class="form-control" name="anio" id="anio">
                        <option value="2026" {{ request('anio') == '2026' || !request('anio') ? 'selected' : '' }}>2026</option>
                        <option value="2025" {{ request('anio') == '2025' ? 'selected' : '' }}>2025</option>
                        <option value="2024" {{ request('anio') == '2024' ? 'selected' : '' }}>2024</option>
                        <option value="2023" {{ request('anio') == '2023' ? 'selected' : '' }}>2023</option>
                    </select>        
                </div>
            </div>

            <div class="form-group col-md-2">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-calendar"></i>
                    </span>
                    <select class="form-control" id="ugels" name="ugels">
                        <option value=""> Seleccione la UGEL: </option>
                    </select>
                </div>
            </div>
            
            <div class="form-group col-md-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-building"></i>
                    </span>
                    <input type="text" class="form-control" id="institucion" name="" placeholder="Buscar institución" autocomplete="off">
                    <select name="instituciones" id="instituciones" class="form-control">
                        <option value="">Selecciona una institución</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group col-md-2">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" class="form-control" id="docente" name="" placeholder="Buscar docente" autocomplete="off">
                    <select name="docentes" id="docentes" class="form-control">
                        <option value="">Selecciona un Docente</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group col-md-1">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>
        
        <div class="mt-3 mb-3">
            <button id="exportar-filtrado-total" class="btn btn-success">
                <i class="fas fa-file-export"></i> Exportar resultados
            </button>
            <div id="export-status" class="mt-2"></div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table id="accions" class="table table-striped table-bordered shadow-lg display nowrap compact" style="width:100%">
                    <thead class="bg-primary text-white">
                        <tr>   
                            <th>Nombre de la Acción</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th>Documento</th>
                            <th>Usuario</th>
                            <th>Cargo</th>
                            <th>Institución</th>
                            <th>Tipo de II.EE.</th>
                            <th>Provincia</th>
                            <th>Distrito</th>
                            <th>UGEL</th>
                            <th>Lugar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($accions)<=0)
                            <tr>
                                <td colspan="12">No hay Asistencia Técnica</td>
                            </tr>
                        @else
                            @foreach ($accions as $accion)
                                <tr>
                                    <td>{{$accion->nombreAccion}}</td>
                                    <td>{{$accion->descripcion}}</td>
                                    <td>{{date('d-m-Y', strtotime($accion->fecha))}}</td>
                                    <td align="center">
                                        <a href="#" data-file-viewer data-src="{{ route('visor.stream', ['path' => $accion->enlace]) }}" data-name="{{ basename($accion->enlace) }}" data-download="{{ route('accions.download', $accion->id) }}" title="Ver documento">
                                            <i class='{{$accion->documento}}' style='font-size:24px;color:{{$accion->color}}'></i>
                                        </a>
                                    </td>
                                    <td>{{$accion->name}}</td>
                                    <td>{{$accion->cargo}}</td>
                                    <td>{{$accion->institucion}}</td>
                                    <td>{{$accion->nivelinstitucion}}</td>
                                    <td>{{$accion->provincia}}</td>
                                    <td>{{$accion->distrito}}</td>
                                    <td>{{$accion->ugel}}</td>
                                    <td>{{$accion->lugar}}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="d-flex justify-content-between">
            <p>Total de Acciones Subidos: {{$accions->total()}}</p>
            <div>
                {{$accions->appends(request()->only(['texto', 'instituciones', 'ugels', 'docentes', 'anio']))->links()}}
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
    // Inicializa DataTable con configuraciones más compactas
    $(document).ready(function() {
        $('#accions').DataTable({
            "scrollX": true,
            "scrollCollapse": true,
            "paging": false, // Desactivar paginación de DataTable ya que usamos Laravel para paginar
            "searching": false, // Desactivar búsqueda de DataTable ya que tenemos formulario personalizado
            "info": false, // Ocultar información de entradas
            "autoWidth": false,
            "language": {
                "emptyTable": "No hay datos disponibles",
                "zeroRecords": "No se encontraron coincidencias"
            },
            "columnDefs": [
                { "width": "150px", "targets": 0 }, // Nombre de la Acción
                { "width": "200px", "targets": 1 }, // Descripción
                { "width": "80px", "targets": 2 }, // Fecha
                { "width": "50px", "targets": 3 }, // Documento
                { "width": "120px", "targets": 4 }, // Usuario
                { "width": "100px", "targets": 5 }, // Cargo
                { "width": "150px", "targets": 6 }, // Institución
                { "width": "100px", "targets": 7 }, // Tipo de II.EE.
                { "width": "120px", "targets": 8 }, // Provincia
                { "width": "120px", "targets": 9 }, // Distrito
                { "width": "120px", "targets": 10 }, // UGEL
                { "width": "120px", "targets": 11 } // Lugar
            ]
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Desaparecer alertas después de 3 segundos
        setTimeout(function () {
            let alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
    });

    // Limitación de DNI a 8 caracteres numéricos
    document.addEventListener('DOMContentLoaded', function() {
        const dniInput = document.getElementById('dniInput');

        dniInput.addEventListener('input', function() {
            const inputValue = dniInput.value.trim();
            const numericValue = inputValue.replace(/[^\d]/g, '');

            if (numericValue.length > 8) {
                dniInput.value = numericValue.slice(0, 8);
            } else {
                dniInput.value = numericValue;
            }
        });
    });

    // Exportación de datos
    $(document).ready(function() {
        $('#exportar-filtrado-total').on('click', function() {
            $('#export-status').html('<div class="spinner-border text-primary" role="status"></div> Preparando datos para exportación...');
            
            var currentUrl = window.location.href;
            var urlParams = {};
            
            var queryString = currentUrl.split('?')[1];
            if (queryString) {
                var pairs = queryString.split('&');
                for (var i = 0; i < pairs.length; i++) {
                    var pair = pairs[i].split('=');
                    urlParams[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
                }
            }
            
            var params = {
                texto: urlParams.texto || $('#dniInput').val() || '',
                anio: urlParams.anio || $('#anio').val() || '2026',
                ugels: urlParams.ugels || $('#ugels').val() || '',
                instituciones: urlParams.instituciones || $('#instituciones').val() || '',
                docentes: urlParams.docentes || $('#docentes').val() || ''
            };
            
            $.ajax({
                url: "{{ route('exportar.filtrado.total') }}",
                method: 'GET',
                data: params,
                dataType: 'json',
                success: function(data) {
                    $('#export-status').html('');
                    
                    if (data.length === 0) {
                        alert('No hay datos para exportar con los filtros seleccionados.');
                        return;
                    }
                    
                    var modalHtml = `
                    <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exportModalLabel">Seleccione formato de exportación</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Se encontraron <strong>${data.length}</strong> registros en total. Elija el formato de exportación:</p>
                                    <div class="d-flex flex-wrap justify-content-center">
                                        <button id="export-excel" class="btn btn-success m-2"><i class="fas fa-file-excel"></i> Excel</button>
                                        <button id="export-pdf" class="btn btn-danger m-2"><i class="fas fa-file-pdf"></i> PDF</button>
                                        <button id="export-csv" class="btn btn-info m-2"><i class="fas fa-file-csv"></i> CSV</button>
                                        <button id="export-print" class="btn btn-warning m-2"><i class="fas fa-print"></i> Imprimir</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                    
                    $('body').append(modalHtml);
                    $('#exportModal').modal('show');
                    
                    $('#exportModal').on('hidden.bs.modal', function() {
                        $(this).remove();
                    });
                    
                    var $tempTableContainer = $('<div>').css('display', 'none').appendTo('body');
                    var $tempTable = $('<table>').appendTo($tempTableContainer);
                    
                    var tempTable = $tempTable.DataTable({
                        data: data,
                        columns: [
                            { data: 'nombreAccion', title: 'Nombre de la Acción' },
                            { data: 'descripcion', title: 'Descripción' },
                            { 
                                data: 'fecha', 
                                title: 'Fecha',
                                render: function(data) {
                                    return moment(data).format('DD-MM-YYYY');
                                }
                            },
                            { data: 'name', title: 'Usuario' },
                            { data: 'cargo', title: 'Cargo' },
                            { data: 'institucion', title: 'Institución' },
                            { data: 'nivelinstitucion', title: 'Tipo de II.EE.' },
                            { data: 'provincia', title: 'Provincia' },
                            { data: 'distrito', title: 'Distrito' },
                            { data: 'ugel', title: 'UGEL' },
                            { data: 'lugar', title: 'Lugar' }
                        ],
                        dom: 'Bfrtip',
                        paging: false,
                        buttons: [
                            {
                                extend: 'excel',
                                text: 'Excel',
                                className: 'exportar-excel',
                                title: 'Acciones de Sensibilización - Reporte Completo',
                                filename: 'acciones_sensibilizacion_' + moment().format('YYYYMMDD_HHmmss'),
                                exportOptions: { 
                                    modifier: { page: 'all' },
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                                }
                            },
                            {
                                extend: 'pdf',
                                text: 'PDF',
                                className: 'exportar-pdf',
                                title: 'Acciones de Sensibilización - Reporte Completo',
                                filename: 'acciones_sensibilizacion_' + moment().format('YYYYMMDD_HHmmss'),
                                exportOptions: { 
                                    modifier: { page: 'all' },
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                                },
                                orientation: 'landscape',
                                pageSize: 'A4'
                            },
                            {
                                extend: 'csv',
                                text: 'CSV',
                                className: 'exportar-csv',
                                title: 'Acciones de Sensibilización - Reporte Completo',
                                filename: 'acciones_sensibilizacion_' + moment().format('YYYYMMDD_HHmmss'),
                                exportOptions: { 
                                    modifier: { page: 'all' },
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                                }
                            },
                            {
                                extend: 'print',
                                text: 'Imprimir',
                                className: 'exportar-print',
                                title: 'Acciones de Sensibilización - Reporte Completo',
                                exportOptions: { 
                                    modifier: { page: 'all' },
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                                }
                            }
                        ]
                    });
                    
                    $('#export-excel').on('click', function() {
                        tempTable.button('.exportar-excel').trigger();
                        $('#exportModal').modal('hide');
                    });
                    
                    $('#export-pdf').on('click', function() {
                        tempTable.button('.exportar-pdf').trigger();
                        $('#exportModal').modal('hide');
                    });
                    
                    $('#export-csv').on('click', function() {
                        tempTable.button('.exportar-csv').trigger();
                        $('#exportModal').modal('hide');
                    });
                    
                    $('#export-print').on('click', function() {
                        tempTable.button('.exportar-print').trigger();
                        $('#exportModal').modal('hide');
                    });
                    
                    setTimeout(function() {
                        tempTable.destroy();
                        $tempTableContainer.remove();
                    }, 10000);
                },
                error: function(xhr, status, error) {
                    $('#export-status').html('');
                    console.error('Error en la exportación:', error);
                    alert('Error al exportar los datos: ' + error);
                }
            });
        });
    });

    // Cargar UGEL
    $(document).ready(function() {
        var selectedYear = $('#anio').val();
        loadUgels(selectedYear);
        
        $('#anio').on('change', function() {
            var year = $(this).val();
            loadUgels(year);
            
            $('#instituciones').empty().append($('<option>', {
                value: '',
                text: 'Selecciona una institución'
            })).prop('disabled', true);
            
            $('#docentes').empty().append($('<option>', {
                value: '',
                text: 'Selecciona un Docente'
            })).prop('disabled', true);
        });
        
        function loadUgels(year) {
            $.ajax({
                url: "{{ route('get-ugels-acc') }}",
                method: 'GET',
                data: {
                    anio: year,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(data) {
                    var $ugelsSelect = $('#ugels');
                    $ugelsSelect.empty();
                    
                    $ugelsSelect.append($('<option>', {
                        value: '',
                        text: ' Seleccione la UGEL: '
                    }));
                    
                    for (var i = 0; i < data.length; i++) {
                        var ugel = data[i].ugel;
                        var docentesCount = data[i].docentes_count;
                        
                        var $option = $('<option>', {
                            value: ugel,
                            text: ugel + ' (' + docentesCount + ' docente)'
                        });
                        
                        $ugelsSelect.append($option);
                    }
                },
                error: function() {
                    alert('Error al cargar las UGELs.');
                }
            });
        }
        
        $('#ugels').on('change', function() {
            var selectedUgel = $(this).val();
            var selectedYear = $('#anio').val();
            
            $.ajax({
                url: "{{ route('buscarInstitucionporUgel-acc') }}",
                method: 'GET',
                data: {
                    ugel: selectedUgel,
                    anio: selectedYear,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(data) {
                    var $institucionesSelect = $('#instituciones');
                    $institucionesSelect.empty();
                    
                    $institucionesSelect.append($('<option>', {
                        value: '',
                        text: 'Selecciona una institución'
                    }));
                    
                    for (var i = 0; i < data.length; i++) {
                        var institucion = data[i].nomInstitucion;
                        var docentesCount = data[i].agendas_count;
                        var totalDocentes = data[i].total_docentes;
                        
                        var $option = $('<option>', {
                            value: institucion,
                            text: institucion + ' (' + docentesCount + ' docentes, ' + totalDocentes + ' total)'
                        });
                        
                        $institucionesSelect.append($option);
                    }
                    
                    $institucionesSelect.prop('disabled', false);
                },
                error: function() {
                    alert('Error al cargar las instituciones.');
                }
            });
        });
        
        $('#institucion').on('input', function() {
            var selectedUgel = $('#ugels').val();
            var selectedYear = $('#anio').val();
            var searchTerm = $(this).val();
            
            $.ajax({
                url: "{{ route('buscarInstitucionesAccion') }}",
                method: 'GET',
                data: {
                    ugel: selectedUgel,
                    anio: selectedYear,
                    term: searchTerm,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(data) {
                    var $institucionesSelect = $('#instituciones');
                    $institucionesSelect.empty();
                    
                    $institucionesSelect.append($('<option>', {
                        value: '',
                        text: 'Selecciona una institución'
                    }));
                    
                    for (var i = 0; i < data.length; i++) {
                        var institucion = data[i].nomInstitucion;
                        var agendasCount = data[i].agendas_count;
                        var totalDocentes = data[i].total_docentes;
                        
                        var $option = $('<option>', {
                            value: institucion,
                            text: institucion + ' (' + agendasCount + ' docentes, ' + totalDocentes + ' total)'
                        });
                        
                        if (agendasCount > 0) {
                            $option.addClass('docente-con-agendas');
                        }
                        
                        $institucionesSelect.append($option);
                    }
                    
                    $institucionesSelect.prop('disabled', false);
                },
                error: function() {
                    alert('Error al cargar las instituciones.');
                }
            });
        });
    });

    $(document).ready(function() {
        $('#instituciones').on('change', function() {
            var selectedInstitucion = $(this).val();
            var selectedYear = $('#anio').val();

            $.ajax({
                url: "{{ route('buscarDocenteporInstitucion-acc') }}",
                method: 'GET',
                data: {
                    docente: selectedInstitucion,
                    ugel: $('#ugels').val(),
                    anio: selectedYear,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(data) {
                    var $docentesSelect = $('#docentes');
                    $docentesSelect.empty();
                    
                    $docentesSelect.append($('<option>', {
                        value: '',
                        text: 'Selecciona un Docente'
                    }));
                    
                    for (var i = 0; i < data.length; i++) {
                        var docente = data[i].name;
                        var agendasCount = data[i].agendas_count;
                        
                        var $option = $('<option>', {
                            value: docente,
                            text: docente + ' (' + agendasCount + ' acciones)'
                        });
                        
                        if (agendasCount > 0) {
                            $option.addClass('docente-con-agendas');
                        }
                        
                        $docentesSelect.append($option);
                    }
                    
                    $docentesSelect.prop('disabled', false);
                },
                error: function() {
                    alert('Error al cargar los docentes.');
                }
            });
        });

        $('#docente').on('input', function() {
            var selectedInstitucion = $('#instituciones').val();
            var selectedYear = $('#anio').val();
            var searchTerm = $(this).val();

            $.ajax({
                url: "{{ route('buscarDocentesAccion') }}",
                method: 'GET',
                data: {
                    institucion: selectedInstitucion,
                    anio: selectedYear,
                    term: searchTerm,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(data) {
                    var $docentesSelect = $('#docentes');
                    $docentesSelect.empty();
                    
                    $docentesSelect.append($('<option>', {
                        value: '',
                        text: 'Selecciona un Docente'
                    }));
                    
                    for (var i = 0; i < data.length; i++) {
                        var docente = data[i].name;
                        var agendasCount = data[i].agendas_count;
                        
                        var $option = $('<option>', {
                            value: docente,
                            text: docente + ' (' + agendasCount + ' acciones)'
                        });
                        
                        if (agendasCount > 0) {
                            $option.addClass('docente-con-agendas');
                        }
                        
                        $docentesSelect.append($option);
                    }
                    
                    $('#docentes').prop('disabled', false);
                },
                error: function() {
                    alert('Error al cargar las docentes.');
                }
            });
        });
    });
</script>
@stop