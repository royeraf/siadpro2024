@extends('adminlte::page')
@section('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('css')
@vite(['resources/css/app.css'])

<!-- FullCalendar -->
<link href="{{ asset('vendor/css/fullcalendar.css') }}" rel="stylesheet">
<style>
	#calendar {
		max-width: 700px;
	}
	.col-centered{
		float: none;
		margin: 0 auto;
	}
    /* Garantizar que el modal y su contenido estén por encima del backdrop y 100% nítidos */
    #ModalEvent {
        z-index: 1060 !important;
    }
    #ModalEvent .modal-dialog {
        z-index: 1061 !important;
    }
    .modal-backdrop {
        z-index: 1050 !important;
    }
    #ModalEvent .modal-content {
        opacity: 1 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
    }

    /* Estilos explícitos y vivos para botones del modal de evento */
    #ModalEvent .modal-footer {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 0.6rem !important;
        background-color: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
        padding: 0.75rem 1.25rem !important;
    }
    #ModalEvent .modal-footer .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-weight: 600;
        padding: 0.45rem 1.1rem;
        border-radius: 0.375rem;
        font-size: 0.92rem;
        line-height: 1.5;
        opacity: 1 !important;
        filter: none !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    #ModalEvent .modal-footer .btn[style*="display: none"],
    #ModalEvent .modal-footer .btn.d-none {
        display: none !important;
    }
    #ModalEvent .modal-footer .btn:hover {
        opacity: 1 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.16) !important;
    }
    #ModalEvent .modal-footer .btn:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12) !important;
    }

    /* Botón Cerrar: tono pizarra oscuro, elegante, sólido y de alto contraste */
    #ModalEvent .btn-secondary {
        background-color: #475569 !important;
        border-color: #334155 !important;
        color: #ffffff !important;
    }
    #ModalEvent .btn-secondary:hover {
        background-color: #334155 !important;
        border-color: #1e293b !important;
        color: #ffffff !important;
    }

    /* Botón Editar: ámbar / dorado vibrante, cálido, con texto blanco nítido */
    #ModalEvent #btnEditar,
    #ModalEvent .btn-warning {
        background-color: #f59e0b !important;
        border-color: #d97706 !important;
        color: #ffffff !important;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2) !important;
    }
    #ModalEvent #btnEditar:hover,
    #ModalEvent .btn-warning:hover {
        background-color: #d97706 !important;
        border-color: #b45309 !important;
        color: #ffffff !important;
    }

    /* Botón Cancelar */
    #ModalEvent #btnCancelar {
        background-color: #64748b !important;
        border-color: #475569 !important;
        color: #ffffff !important;
    }
    #ModalEvent #btnCancelar:hover {
        background-color: #475569 !important;
        border-color: #334155 !important;
        color: #ffffff !important;
    }

    /* Botón Guardar / Modificar (Acción principal) */
    #ModalEvent .btn-primary {
        background-color: #2563eb !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }
    #ModalEvent .btn-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1e40af !important;
        color: #ffffff !important;
    }
</style>
@endsection

@section('title', 'Agenda')

@section('content_header')
    <x-page-header icon="calendar-check" title="Agenda de Lectura" color="pink" />
@stop

@section('content')

<x-section-tabs :tabs="$tabs" color="pink" />

        <div class="row" id="eventos">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="col-lg-12 text-center">
                        <div id="calendar" class="col-centered">
                            @csrf
                        </div>
                    </div>
                </div>
            </div>
        </div>          

        @include('agenda.partials._modal-evento')
@stop

@section('js')

<!-- FullCalendar -->
<script src="{{ asset('vendor/js/moment.min.js') }}"></script>
<script src="{{ asset('vendor/js/fullcalendar/fullcalendar.js') }}"></script>
<script src="{{ asset('vendor/js/fullcalendar/locale/es.js') }}"></script>


        <script>
            $(document).ready(function() {

		// Mover el modal directamente al body para evitar que cualquier backdrop o contenedor padre lo oscurezca
		$('#ModalEvent').appendTo('body');

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
				$('#btnCerrar').show().html('<i class="fas fa-times mr-1"></i> Cerrar');
				$('#btnEditar').show();
				$('#btnCancelar').hide();
				$('#btnAccion').hide();
				$('#grupoEliminar').hide();
				return;
			}

			$('#title, #evento, #start, #end').prop('readonly', false);
			$('#color').prop('disabled', false);
			$('#btnEditar').hide();

			if (mode === 'edit') {
				$('#myModalLabel').text('Modificar Evento');
				$('#btnCerrar').hide();
				$('#btnCancelar').show().html('<i class="fas fa-ban mr-1"></i> Cancelar');
				$('#btnAccion').show().html('<i class="fas fa-save mr-1"></i> Modificar');
				$('#grupoEliminar').show();
			} else {
				$('#myModalLabel').text('Agregar Evento');
				$('#btnCerrar').show().html('<i class="fas fa-times mr-1"></i> Cancelar');
				$('#btnCancelar').hide();
				$('#btnAccion').show().html('<i class="fas fa-save mr-1"></i> Registrar');
				$('#grupoEliminar').hide();
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
			// Defensa en profundidad: el backend ya exige agendas.create/agendas.edit
			// en el constructor de AgendaController, pero además se oculta la
			// interacción de arrastrar/soltar y de seleccionar un día vacío si el
			// usuario no tiene el permiso correspondiente.
			editable: @can('agendas.edit') true @else false @endcan,
			eventLimit: true,
			selectable: @can('agendas.create') true @else false @endcan,
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
