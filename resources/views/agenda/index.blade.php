@extends('adminlte::page')
@section('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('css')

<!-- Bootstrap Core CSS -->
<link href="vendor/css/bootstrap.min.css" rel="stylesheet">

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
                    
                    <!-- Modal Agregar Evento-->
                    <div class="modal fade" id="ModalAdd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            
                        <form class="form-horizontal" action="/agendas" method="POST" enctype="multipart/form-data">
                            @csrf
                        
                            <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Agregar Evento</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">                            
                                <div class="form-group">
                                    <label for="title" class="col-sm-3 control-label">Titulo</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="title" class="form-control" id="title" placeholder="Titulo" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="evento" class="col-sm-3 control-label">Evento</label>
                                    <div class="col-sm-8">
                                        <textarea name="evento" id="evento" cols="40" rows="5" placeholder="Descripcion del evento" required></textarea>
                                        <p id="mensajeEnter" class="text-danger" style="display: none;">No es válido presionar Enter en este campo.</p>
                                        <p id="mensajeComilla" class="text-danger" style="display: none;">No es válido colocar ' en este campo.</p>
                                    </div>
                                </div>         
                                <div class="form-group">
                                    <label for="color" class="col-sm-3 control-label">Seccion</label>
                                    <div class="col-sm-8">
                                        <select name="color" class="form-control" id="color" required>
                                            <option value="">Seleccionar</option>											
                                            <option style="color:#FF0085;" value="#FF0085">&#9724; Lila</option>
                                            <option style="color:#0071c5;" value="#0071c5">&#9724; Azul oscuro</option>
                                            <option style="color:#40E0D0;" value="#40E0D0">&#9724; Turquesa</option>
                                            <option style="color:#008000;" value="#008000">&#9724; Verde</option>						  
                                            <option style="color:#FFD700;" value="#FFD700">&#9724; Amarillo</option>
                                            <option style="color:#FF8C00;" value="#FF8C00">&#9724; Naranja</option>
                                            <option style="color:#FF0000;" value="#FF0000">&#9724; Rojo</option>
                                            <option style="color:#9D00FF;" value="#9D00FF">&#9724; Violeta</option>
                                            <option style="color:#BA4A00;" value="#BA4A00">&#9724; Marron</option>
                                            <option style="color:#99A3A4;" value="#99A3A4">&#9724; Gris</option>
                                            <option style="color:#21618C;" value="#21618C">&#9724; Acero</option>
                                            <option style="color:#000;" value="#000">&#9724; Negro</option>
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="start" class="col-sm-3 control-label">Fecha Inicial</label>
                                    <div class="col-sm-8">
                                        <input type="datetime-local" name="start" class="form-control" id="start" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="end" class="col-sm-3 control-label">Fecha Final</label>
                                    <div class="col-sm-8">
                                        <input type="datetime-local" name="end" class="form-control" id="end" required>
                                    </div>
                                </div>
                            
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-info" data-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary" id="btnAccion">Guardar</button>
                            </div>
                        </form>
                        </div>
                        </div>
                    </div>
                    <!-- Modal Agregar Evento-->

                    <!-- Modal Modificar Evento-->
                    <div class="modal fade" id="ModalEdit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog" role="document">
                        <div class="modal-content">
                        <form class="form-horizontal" action="/agendas/update" method="POST">
                            @csrf
                            <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Modificar Evento</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>								
                            </div>
                            <div class="modal-body">                            
                                <div class="form-group">
                                    <label for="title" class="col-sm-3 control-label">Titulo</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="title" class="form-control" id="title" placeholder="Titulo" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="evento" class="col-sm-3 control-label">Evento</label>
                                    <div class="col-sm-8">
                                        <textarea name="evento" id="evento" cols="40" rows="5" placeholder="Descripcion del evento" required></textarea>
                                        <p id="mensajeEnter" class="text-danger" style="display: none;">No es válido presionar Enter en este campo.</p>
                                        <p id="mensajeComilla" class="text-danger" style="display: none;">No es válido colocar ' en este campo.</p>
                                    </div>
                                </div>     
                                <div class="form-group">
                                <label for="color" class="col-sm-3 control-label">Color</label>
                                <div class="col-sm-8">
                                    <select name="color" class="form-control" id="color">
                                        <option value="">Seleccionar</option>											
                                        <option style="color:#FF0085;" value="#FF0085">&#9724; Lila</option>
                                        <option style="color:#0071C5;" value="#0071c5">&#9724; Azul oscuro</option>
                                        <option style="color:#40E0D0;" value="#40E0D0">&#9724; Turquesa</option>
                                        <option style="color:#008000;" value="#008000">&#9724; Verde</option>						  
                                        <option style="color:#FFD700;" value="#FFD700">&#9724; Amarillo</option>
                                        <option style="color:#FF8C00;" value="#FF8C00">&#9724; Naranja</option>
                                        <option style="color:#FF0000;" value="#FF0000">&#9724; Rojo</option>
                                        <option style="color:#9D00FF;" value="#9D00FF">&#9724; Violeta</option>
                                        <option style="color:#BA4A00;" value="#BA4A00">&#9724; Marron</option>
                                        <option style="color:#99A3A4;" value="#99A3A4">&#9724; Gris</option>
                                        <option style="color:#21618C;" value="#21618C">&#9724; Acero</option>
                                        <option style="color:#000;" value="#000">&#9724; Negro</option>
                                        
                                    </select>
                                </div>
                                </div>
                                <div class="form-group">
                                    <label for="start" class="col-sm-3 control-label">Fecha Inicial</label>
                                    <div class="col-sm-8">
                                        <input type="datetime-local" name="start" class="form-control" id="start">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="end" class="col-sm-3 control-label">Fecha Final</label>
                                    <div class="col-sm-8">
                                        <input type="datetime-local" name="end" class="form-control" id="end">
                                    </div>
                                </div>
                                <div class="form-group"> 
                                    <div class="col-sm-offset-2 col-sm-10">
                                        <div class="checkbox">
                                        <label class="text-danger"><input type="checkbox"  name="delete"> Eliminar Evento</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <input type="hidden" name="id" class="form-control" id="id">
                            
                            
                            </div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary" id="btnAccion">Guardar</button>
                            </div>
                        </form>
                        </div>
                        </div>
                    </div>
                    <!-- Modal Modificar Evento-->
                </div>
            </div>
        </div>          
@stop

@section('css')
 
@stop

@section('js')

<script src="vendor/js/jquery.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="vendor/js/bootstrap.min.js"></script>

<!-- FullCalendar -->
<script src="vendor/js/moment.min.js"></script>
<script src="vendor/js/fullcalendar/fullcalendar.min.js"></script>
<script src="vendor/js/fullcalendar/fullcalendar.js"></script>
<script src="vendor/js/fullcalendar/locale/es.js"></script>


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
			editable: true,
			eventLimit: true, 
			selectable: true,
			selectHelper: true,
			select: function(start, end) {
				$('#ModalAdd #start').val(moment(start).format('YYYY-MM-DDTHH:mm:ss'));
				$('#ModalAdd #end').val(moment(end).format('YYYY-MM-DDTHH:mm:ss'));
				$('#ModalAdd').modal({show:true});
				document.getElementById('btnAccion').textContent = 'Registrar';
				
			},
			eventRender: function(event, element) {
				element.bind('dblclick', function() {
					$('#ModalEdit #id').val(event.id);
					$('#ModalEdit #title').val(event.title);
					$('#ModalEdit #evento').val(event.evento);
					$('#ModalEdit #color').val(event.color);
					$('#ModalEdit #start').val(moment(event.start).format('YYYY-MM-DDTHH:mm:ss'));
					$('#ModalEdit #end').val(moment(event.end).format('YYYY-MM-DDTHH:mm:ss'));
					$('#ModalEdit').modal('show');
					document.getElementById('btnAccion').textContent = 'Modificar';
				});
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
