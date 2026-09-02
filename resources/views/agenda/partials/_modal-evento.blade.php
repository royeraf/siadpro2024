{{-- Modal único de evento (Agregar / Visualizar / Modificar).
     El modo (view/edit/create) lo controla la función JS setMode() en
     agenda/index.blade.php, que también llena estos mismos campos. --}}
<div class="modal fade" id="ModalEvent" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
    <div class="modal-content">

    <form class="form-horizontal" id="formEvento" action="/agendas" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Agregar Evento</h4>
        <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                    <textarea name="evento" id="evento" class="form-control" rows="5" placeholder="Descripcion del evento" required></textarea>
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
            <div class="form-group" id="grupoEliminar">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                    <label class="text-danger"><input type="checkbox" name="delete" id="deleteCheckbox"> Eliminar Evento</label>
                    </div>
                </div>
            </div>

            <input type="hidden" name="id" class="form-control" id="id">

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-info" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-warning" id="btnEditar">Editar</button>
            <button type="button" class="btn btn-secondary" id="btnCancelar">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnAccion">Guardar</button>
        </div>
    </form>
    </div>
    </div>
</div>
