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
		max-width: 800px;
	}
	.col-centered{
		float: none;
		margin: 0 auto;
	}
    #ModalView {
        z-index: 1060 !important;
    }
    #ModalView .modal-dialog {
        z-index: 1061 !important;
    }
    #ModalView .modal-content {
        opacity: 1 !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
    }
    #ModalView .modal-footer {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        background-color: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
        padding: 0.75rem 1.25rem !important;
    }
    #ModalView .modal-footer .btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.4rem !important;
        font-weight: 600 !important;
        padding: 0.45rem 1.1rem !important;
        border-radius: 0.375rem !important;
        font-size: 0.92rem !important;
        opacity: 1 !important;
        filter: none !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12) !important;
        transition: all 0.2s ease-in-out !important;
        cursor: pointer !important;
    }
    #ModalView .btn-secondary {
        background-color: #475569 !important;
        border-color: #334155 !important;
        color: #ffffff !important;
    }
    #ModalView .btn-secondary:hover {
        background-color: #334155 !important;
        border-color: #1e293b !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.16) !important;
    }
</style>
@endsection

@section('content_header')
    <x-page-header icon="calendar-check" title="Agenda de Lectura" color="pink" />
@stop

@section('content')

<x-section-tabs :tabs="$tabs" color="pink" />

 <!-- Page Content -->

    <!-- Main content -->
	
            <div class="row" id="eventos">			
                <div class="col-12">
                    <div class="card card-primary card-outline">
                        <div class="col-lg-12 text-center">		
                        </div>
                        <div id="calendar" class="col-centered">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Visualizar Eventos-->
            <div class="modal fade" id="ModalView" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                <div class="modal-content">							
                    <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Visualizacion de Evento</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body center" >							
                        <div class="form-group col-md-12" >
                            <label for="title" class="col-sm-3 control-label">Titulo</label>
                            <input type="text" name="title" class="form-control" id="title" placeholder="Titulo" readonly>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="evento" class="col-sm-3 control-label">Evento</label><br>
                            <br><textarea name="evento" id="evento" class="form-control" rows="3" placeholder="Descripcion del evento" readonly></textarea>
                        </div>							
                        <div class="form-group col-md-12" >
                            <label for="nomDocente" class="col-sm-3 control-label">Docente</label>
                            <input type="text" name="nomDocente" class="form-control" id="nomDocente" placeholder="nomDocente" readonly>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="start" class="col-sm-5 control-label">Fecha Inicial</label>
                            <input type="text" name="start" class="form-control" id="start" readonly>
                        </div>
                            <div class="form-group col-md-12">
                            <label for="end" class="col-sm-3 control-label">Fecha Final</label>
                            <input type="text" name="end" class="form-control" id="end" readonly>
                        </div>
                    
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cerrar
                        </button>
                    </div>
                </div>
                </div>
            </div>
            <!-- Modal Visualizar Eventos-->

	
@endsection

@section('js')
<!-- FullCalendar -->
<script src="{{ asset('vendor/js/moment.min.js') }}"></script>
<script src="{{ asset('vendor/js/fullcalendar/fullcalendar.js') }}"></script>
<script src="{{ asset('vendor/js/fullcalendar/locale/es.js') }}"></script>

<script>
	$(document).ready(function() {
		// Mover el modal directamente al body para evitar que el backdrop lo oscurezca
		$('#ModalView').appendTo('body');

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
			eventLimit: true, 
			selectable: true,
			selectHelper: true,
			eventClick: function(event) {
				var formato = 'DD/MM/YYYY HH:mm';
				$('#ModalView #id').val(event.id);
				$('#ModalView #title').val(event.title);
				$('#ModalView #nomDocente').val(event.nomDocente);
				$('#ModalView #evento').val(event.evento);
				$('#ModalView #start').val(event.start ? moment(event.start).format(formato) : '');
				$('#ModalView #end').val(event.end ? moment(event.end).format(formato) : '');
				$('#ModalView').modal('show');
			},
			events: <?php
				$jsEvents = [];
				foreach ($events as $event) {
					$start = explode(" ", $event['start']);
					$end = explode(" ", $event['end']);
					$start = isset($start[1]) && $start[1] == '00:00:00' ? $start[0] : $event['start'];
					$end = isset($end[1]) && $end[1] == '00:00:00' ? $end[0] : $event['end'];

					$jsEvents[] = [
						'id' => $event['id'],
						'title' => $event['title'],
						'nomDocente' => $event['nomDocente'],
						'evento' => $event['evento'],
						'color' => $event['color'],
						'start' => $start,
						'end' => $end,
					];
				}
				echo json_encode($jsEvents);
			?>,
		});		
	});

</script>
@endsection