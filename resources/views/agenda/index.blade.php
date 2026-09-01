@extends('adminlte::page')
@section('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('css')

<!-- FullCalendar -->
<link href="vendor/css/fullcalendar.css" rel="stylesheet">
<style>
	#calendar {
		max-width: 700px;
	}
	.col-centered{
		float: none;
		margin: 0 auto;
	}
    </style>
@endsection

@section('title', 'Agenda')

@section('content_header')
@stop

@section('content')
        <div class="row" id="eventos">
            <div class="col-12">
                <div class="card card-danger card-outline">
                        <div class="col-lg-12 text-center">
                            <div id="calendar" class="col-centered">
                                @csrf
                            </div>
                        </div>
                    
                    @include('agenda.partials._modal-evento')
                </div>
            </div>
        </div>          
@stop

@section('css')
 
@stop

@section('js')

<!-- FullCalendar -->
<script src="vendor/js/moment.min.js"></script>
<script src="vendor/js/fullcalendar/fullcalendar.js"></script>
<script src="vendor/js/fullcalendar/locale/es.js"></script>


        <script>
            $(document).ready(function() {

		// Datos originales del evento actualmente abierto en el modal, para
		// poder restaurarlos si el usuario cancela una edición sin guardar.
		var eventoActual = null;
		var formato = 'YYYY-MM-DD[T]HH:mm:ss';

		function llenarCamposDesdeEvento(event) {
			$('#id').val(event.id);
			$('#title').val(event.title);
			$('#evento').val(event.evento);
			$('#color').val(event.color);
			$('#start').val(moment(event.start).format(formato));
			$('#end').val(moment(event.end).format(formato));
		}

		// Controla los 3 modos del modal único: 'view' (solo lectura, tras
		// hacer clic en un evento), 'edit' (tras pulsar "Editar") y
		// 'create' (tras seleccionar un día vacío del calendario).
		function setMode(mode) {
			$('#deleteCheckbox').prop('checked', false);

			if (mode === 'view') {
				$('#title, #evento, #start, #end').prop('readonly', true);
				$('#color').prop('disabled', true);
				$('#myModalLabel').text('Visualización de Evento');
				$('#btnEditar').show();
				$('#btnCancelar').hide();
				$('#btnAccion').hide();
				$('#grupoEliminar').hide();
				return;
			}

			$('#title, #evento, #start, #end').prop('readonly', false);
			$('#color').prop('disabled', false);
			$('#btnEditar').hide();
			$('#btnAccion').show();

			if (mode === 'edit') {
				$('#myModalLabel').text('Modificar Evento');
				$('#btnAccion').text('Modificar');
				$('#grupoEliminar').show();
				$('#btnCancelar').show();
			} else {
				$('#myModalLabel').text('Agregar Evento');
				$('#btnAccion').text('Registrar');
				$('#grupoEliminar').hide();
				$('#btnCancelar').hide();
			}
		}

		$('#btnEditar').on('click', function() {
			setMode('edit');
		});

		// Cancelar una edición en curso: descarta cualquier cambio sin
		// guardar en los campos y vuelve al modo lectura con los datos
		// originales del evento.
		$('#btnCancelar').on('click', function() {
			if (eventoActual) {
				llenarCamposDesdeEvento(eventoActual);
			}
			setMode('view');
		});

		var date = new Date();
       	var yyyy = date.getFullYear().toString();
       	var mm = (date.getMonth()+1).toString().length == 1 ? "0"+(date.getMonth()+1).toString() : (date.getMonth()+1).toString();
      	var dd  = (date.getDate()).toString().length == 1 ? "0"+(date.getDate()).toString() : (date.getDate()).toString();

		$('#calendar').fullCalendar({
			height: 550,
			header: {
				 language: 'es',
				left: 'prev,next today',
				center: 'title',
				right: 'month,basicWeek,basicDay',

			},
			defaultDate: yyyy+"-"+mm+"-"+dd,
			editable: true,
			eventLimit: true, 
			selectable: true,
			selectHelper: true,
			select: function(start, end) {
				$('#formEvento')[0].reset();
				eventoActual = null;
				$('#id').val('');
				$('#start').val(moment(start).format(formato));
				$('#end').val(moment(end).format(formato));
				$('#formEvento').attr('action', '/agendas');
				setMode('create');
				$('#ModalEvent').modal('show');
			},
			eventClick: function(event) {
				$('#formEvento')[0].reset();
				eventoActual = event;
				llenarCamposDesdeEvento(event);
				$('#formEvento').attr('action', '/agendas/update');
				setMode('view');
				$('#ModalEvent').modal('show');
			},
			events: [
                <?php foreach($events as $event): 
                    $start = explode(" ", $event['start']);
                    $end = explode(" ", $event['end']);
                    $start = isset($start[1]) && $start[1] == '00:00:00' ? $start[0] : $event['start'];
                    $end = isset($end[1]) && $end[1] == '00:00:00' ? $end[0] : $event['end'];
                ?>
                    {
                        id: <?php echo json_encode($event['id']); ?>,
                        title: <?php echo json_encode($event['title']); ?>,
                        evento: <?php echo json_encode($event['evento']); ?>,						
                        color: <?php echo json_encode($event['color']); ?>,
                        start: <?php echo json_encode($start); ?>,
                        end: <?php echo json_encode($end); ?>,
                    },
                <?php endforeach; ?>
            ]
		});		
	});
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var eventoTextarea = document.getElementById("evento");

        eventoTextarea.addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                event.preventDefault(); // Evita que se agregue el salto de línea
                document.getElementById("mensajeEnter").style.display = "block";
            }
        });

        eventoTextarea.addEventListener("keyup", function(event) {
            if (event.key === "Enter") {
                document.getElementById("mensajeEnter").style.display = "none";
            }
        });

        eventoTextarea.addEventListener("keydown", function(event) {
            if (event.key === "'") {
                event.preventDefault();
                document.getElementById("mensajeComilla").style.display = "block";
            }
        });

        eventoTextarea.addEventListener("keyup", function(event) {
            if (event.key === "'") {
                document.getElementById("mensajeComilla").style.display = "none";
            }
        });

    });
</script>

@stop
