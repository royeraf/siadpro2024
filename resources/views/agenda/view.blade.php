@extends('adminlte::page')


@section('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('css')

<!-- Bootstrap Core CSS -->
<!-- FullCalendar -->
<link href="/vendor/css/fullcalendar.css" rel="stylesheet">

<style>
	#calendar {
		max-width: 800px;
	}
	.col-centered{
		float: none;
		margin: 0 auto;
	}
    </style>
@endsection
@section('content')
 <!-- Page Content -->

    <!-- Main content -->
	
            <div class="row" id="eventos">			
                <div class="col-12">
                    <div class="card card-primary card-outline">
							<div class="col-lg-12 text-center">		
							</div>
							<div id="calendar" class="col-centered">
							</div>
				
						
                        <!-- Modal Visualizar Eventos-->
						<div class="modal fade" id="ModalView" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
							<div class="modal-dialog" role="document">
							<div class="modal-content">							
								<div class="modal-header">
								<h4 class="modal-title" id="myModalLabel">Visualizacion de Evento</h4>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
									<button type="button" class="btn btn-info" data-dismiss="modal">Cerrar</button>
								</div>
							</div>
							</div>
						</div>
						<!-- Modal Visualizar Eventos-->
                    </div>
                </div>
            </div>

	
@endsection

@section('js')
<!-- FullCalendar -->
<script src="/vendor/js/moment.min.js"></script>
<script src="/vendor/js/fullcalendar/fullcalendar.js"></script>
<script src="/vendor/js/fullcalendar/locale/es.js"></script>

<script>
	$(document).ready(function() {
    	

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
			events: [
				
			<?php foreach($events as $event): 
			
				$start = explode(" ", $event['start']);
				$end = explode(" ", $event['end']);
				$start = isset($start[1]) && $start[1] == '00:00:00' ? $start[0] : $event['start'];
				$end = isset($end[1]) && $end[1] == '00:00:00' ? $end[0] : $event['end'];
			?>
				{
					id: '<?php echo $event['id']; ?>',
					title: '<?php echo $event['title']; ?>',
					nomDocente: '<?php echo $event['nomDocente']; ?>',
					evento: '<?php echo $event['evento']; ?>',					
					color: '<?php echo $event['color']; ?>',
					start: '<?php echo $start; ?>',
					end: '<?php echo $end; ?>',
				},
			<?php endforeach; ?>
			]
		});		
	});

</script>
@endsection