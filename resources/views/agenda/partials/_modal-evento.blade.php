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
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white" id="colorPreviewContainer" style="padding: 0 10px; display: flex; align-items: center; justify-content: center;">
                                <span id="colorBadge" style="display: inline-block; width: 18px; height: 18px; border-radius: 4px; border: 1px solid #ced4da; background-color: transparent; transition: all 0.2s ease;"></span>
                            </span>
                        </div>
                        <select name="color" class="form-control" id="color" required>
                            <option value="">Seleccionar</option>
                            <option style="color:#FF0085; font-weight: 600;" value="#FF0085">Lila</option>
                            <option style="color:#0071c5; font-weight: 600;" value="#0071c5">Azul oscuro</option>
                            <option style="color:#40E0D0; font-weight: 600;" value="#40E0D0">Turquesa</option>
                            <option style="color:#008000; font-weight: 600;" value="#008000">Verde</option>
                            <option style="color:#FFD700; font-weight: 600;" value="#FFD700">Amarillo</option>
                            <option style="color:#FF8C00; font-weight: 600;" value="#FF8C00">Naranja</option>
                            <option style="color:#FF0000; font-weight: 600;" value="#FF0000">Rojo</option>
                            <option style="color:#9D00FF; font-weight: 600;" value="#9D00FF">Violeta</option>
                            <option style="color:#BA4A00; font-weight: 600;" value="#BA4A00">Marron</option>
                            <option style="color:#99A3A4; font-weight: 600;" value="#99A3A4">Gris</option>
                            <option style="color:#21618C; font-weight: 600;" value="#21618C">Acero</option>
                            <option style="color:#000000; font-weight: 600;" value="#000000">Negro</option>
                        </select>
                    </div>
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
            @can('agendas.destroy')
            <div class="form-group" id="grupoEliminar" style="display: none;">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                    <label class="text-danger"><input type="checkbox" name="delete" id="deleteCheckbox"> Eliminar Evento</label>
                    </div>
                </div>
            </div>
            @endcan

            <input type="hidden" name="id" class="form-control" id="id">

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="btnCerrar" data-bs-dismiss="modal" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i> Cerrar
            </button>
            @can('agendas.edit')
            <button type="button" class="btn btn-warning" id="btnEditar" style="display: none;">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
            @endcan
            <button type="button" class="btn btn-secondary" id="btnCancelar" style="display: none;">
                <i class="fas fa-ban mr-1"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary" id="btnAccion" style="display: none;">
                <i class="fas fa-save mr-1"></i> Guardar
            </button>
        </div>
    </form>
    </div>
    </div>
</div>
