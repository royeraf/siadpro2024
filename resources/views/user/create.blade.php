@extends('adminlte::page')

@section('title', 'Usuario')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-cyan card-outline">
                <div class="card-header"><h1 align="center">Crear Nuevo Usuario</h1></div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>¡Hay errores en el formulario!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-bs-dismiss="alert" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form id="form-user" method="POST" action="{{ route('users.store') }}" novalidate>
                        @csrf
                        <div class="form-group row">
                            <label for="dni" class="col-md-4 col-form-label text-md-right">{{ __('DNI') }} <span class="text-danger">*</span></label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="dni" type="text" class="form-control @error('dni') is-invalid @enderror" 
                                           required name="dni" value="{{ old('dni') }}" maxlength="8" pattern="[0-9]{8}" 
                                           title="El DNI debe tener exactamente 8 dígitos numéricos" autofocus autocomplete="off"
                                           placeholder="Ingrese 8 dígitos de DNI">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-primary" type="button" id="btn-buscar-dni" title="Consultar en RENIEC">
                                            <i class="fas fa-search" id="icon-buscar-dni"></i> <span class="d-none d-sm-inline">Buscar RENIEC</span>
                                        </button>
                                    </div>
                                </div>
                                <div id="dni-error" class="invalid-feedback font-weight-bold"></div>
                                <div id="dni-feedback" class="mt-1"></div>

                                @error('dni')
                                    <span class="invalid-feedback d-block font-weight-bold" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="estado" class="col-md-4 col-form-label text-md-right">{{ __('Estado') }} <span class="text-danger">*</span></label>

                            <div class="col-md-6">
                                <select id="estado" name="estado" class="form-control @error('estado') is-invalid @enderror" required>
                                    <option value="1" {{ old('estado', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('estado') === '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                <div id="estado-error" class="invalid-feedback font-weight-bold"></div>
                        
                                @error('estado')
                                    <span class="invalid-feedback d-block font-weight-bold" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">
                                {{ __('Apellidos y Nombres') }} <span class="text-danger">*</span>
                                <span id="badge-reniec" class="badge badge-success ml-1 d-none" title="Verificado con RENIEC">
                                    <i class="fas fa-check-circle"></i> RENIEC
                                </span>
                            </label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                       required name="name" value="{{ old('name') }}" maxlength="191" autocomplete="off"
                                       placeholder="Se autocompleta con el DNI o ingrese manualmente">
                                <div id="name-error" class="invalid-feedback font-weight-bold"></div>

                                @error('name')
                                    <span class="invalid-feedback d-block font-weight-bold" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="ugel" class="col-md-4 col-form-label text-md-right">{{ __('UGEL') }}</label>

                            <div class="col-md-6">
                                <select id="ugel" name="ugel" class="form-control @error('ugel') is-invalid @enderror">
                                    <option value="">----Seleccione UGEL-----</option>
                                    @foreach(['Ugel Huánuco', 'Ugel Ambo', 'Ugel Dos de Mayo', 'Ugel Lauricocha', 'Ugel Leoncio Prado', 'Ugel Huacaybamba', 'Ugel Huamalies', 'Ugel Marañon', 'Ugel Pachitea', 'Ugel Puerto Inca', 'Ugel Yarowilca'] as $ug)
                                        <option value="{{ $ug }}" {{ old('ugel') == $ug ? 'selected' : '' }}>{{ $ug }}</option>
                                    @endforeach
                                </select>

                                @error('ugel')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="codmodular" class="col-md-4 col-form-label text-md-right">{{ __('Código Modular') }}</label>

                            <div class="col-md-6">
                                <input id="codmodular" name="codmodular" type="text" class="form-control" tabindex="1" 
                                       value="{{ old('codmodular') }}" maxlength="7" placeholder="Ingrese 6 o 7 dígitos">
                                <small id="codmodular-status" class="form-text text-muted">
                                    Ingrese el código modular (6 o 7 dígitos) para autocompletar en tiempo real.
                                </small>

                                @error('codmodular')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="institucion" class="col-md-4 col-form-label text-md-right">{{ __('Institución Educativa') }}</label>

                            <div class="col-md-6">
                                <select name="institucion" id="institucion" class="form-control @error('institucion') is-invalid @enderror">
                                    @if(old('institucion'))
                                        <option value="{{ old('institucion') }}" selected>{{ old('institucion') }}</option>
                                    @else
                                        <option value="">-- Ingrese Cód. Modular o seleccione --</option>
                                    @endif
                                </select>

                                @error('institucion')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="nivelinstitucion" class="col-md-4 col-form-label text-md-right">{{ __('Tipo de II.EE.') }}</label>

                            <div class="col-md-6">
                                <select id="nivelinstitucion" name="nivelinstitucion" class="form-control @error('nivelinstitucion') is-invalid @enderror">
                                    <option value="">----Seleccione Nivel-----</option>
                                    <option value="Escolarizado" {{ old('nivelinstitucion') == 'Escolarizado' ? 'selected' : '' }}>Escolarizado</option>
                                    <option value="No escolarizado - PRONOEI" {{ old('nivelinstitucion') == 'No escolarizado - PRONOEI' ? 'selected' : '' }}>No escolarizado - PRONOEI</option>
                                </select>

                                @error('nivelinstitucion')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="provincia" class="col-md-4 col-form-label text-md-right">{{ __('Provincia') }}</label>

                            <div class="col-md-6">
                                <select name="provincia" id="provincia" class="form-control @error('provincia') is-invalid @enderror">
                                    @if(old('provincia'))
                                        <option value="{{ old('provincia') }}" selected>{{ old('provincia') }}</option>
                                    @endif
                                </select>

                                @error('provincia')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="distrito" class="col-md-4 col-form-label text-md-right">{{ __('Distrito') }}</label>

                            <div class="col-md-6">
                                <select name="distrito" id="distrito" class="form-control @error('distrito') is-invalid @enderror">
                                    @if(old('distrito'))
                                        <option value="{{ old('distrito') }}" selected>{{ old('distrito') }}</option>
                                    @endif
                                </select>

                                @error('distrito')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="cargo" class="col-md-4 col-form-label text-md-right">{{ __('Cargo') }} <span class="text-danger">*</span></label>

                            <div class="col-md-6">
                                <select id="cargo" name="cargo" class="form-control @error('cargo') is-invalid @enderror" required>
                                    <option value="">----Seleccione Cargo-----</option>
                                    <option value="Especialista DRE" {{ old('cargo') == 'Especialista DRE' ? 'selected' : '' }}>Especialista DRE</option>
                                    <option value="Especialista UGEL" {{ old('cargo') == 'Especialista UGEL' ? 'selected' : '' }}>Especialista UGEL</option>
                                    <option value="Director" {{ old('cargo') == 'Director' ? 'selected' : '' }}>Director</option>
                                    <option value="Docente" {{ old('cargo') == 'Docente' ? 'selected' : '' }}>Docente</option>
                                    <option value="Profesor Coordinador" {{ old('cargo') == 'Profesor Coordinador' ? 'selected' : '' }}>Profesor Coordinador</option>
                                    <option value="Promotor(a) Educativa Comunitaria" {{ old('cargo') == 'Promotor(a) Educativa Comunitaria' ? 'selected' : '' }}>Promotor(a) Educativa Comunitaria</option>
                                </select>
                                <div id="cargo-error" class="invalid-feedback font-weight-bold"></div>

                                @error('cargo')
                                    <span class="invalid-feedback d-block font-weight-bold" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Correo Electrónico') }} <span class="text-danger">*</span></label>

                            <div class="col-md-6">
                                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                       tabindex="1" required maxlength="191" value="{{ old('email') }}" autocomplete="off"> 
                                <div id="email-error" class="invalid-feedback font-weight-bold"></div>

                                @error('email')
                                    <span class="invalid-feedback d-block font-weight-bold" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span> 
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Contraseña') }} <span class="text-danger">*</span></label>

                            <div class="col-md-6">
                                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                       tabindex="1" required minlength="6" maxlength="50" placeholder="Mínimo 6 caracteres" autocomplete="new-password"> 
                                <div id="password-error" class="invalid-feedback font-weight-bold"></div>

                                @error('password')
                                    <span class="invalid-feedback d-block font-weight-bold" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span> 
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <a href="{{ route('users.index') }}" class="btn btn-danger" tabindex="4">Cancelar</a>
                                <button type="submit" id="btn-guardar" class="btn btn-primary">
                                    {{ __('Guardar') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
  #name{
    text-transform: uppercase;
  }
  #provincia{
    text-transform: uppercase;
  }
  #distrito{
    text-transform: uppercase;
  }
  .invalid-feedback {
    display: none;
    color: #e3342f;
    font-size: 0.875rem;
    margin-top: 0.35rem;
  }
  .invalid-feedback.d-block {
    display: block !important;
  }
  .form-control.is-invalid {
    border-color: #e3342f !important;
  }
  .form-control.is-valid {
    border-color: #38c172 !important;
  }
</style>
@stop

@section('js') 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/js/app.js'])
<x-sweet-alert />
<script>
  let currentInstituciones = [];
  let codDebounceTimer = null;

  function showInstituciones(id) {
    if (!id) return;
    const cleanId = id.toString().replace(/[^\d]/g, '').trim();
    if (cleanId.length < 6) return;

    const statusEl = document.getElementById('codmodular-status');
    if (statusEl) {
      statusEl.innerHTML = '<span class="text-primary font-weight-bold"><i class="fas fa-spinner fa-spin"></i> Buscando institución...</span>';
    }

    $.ajax({
      url: "/api/instituciones/" + encodeURIComponent(cleanId),
      type: "GET",
      dataType: "json",
      success: function(instituciones) {
        currentInstituciones = instituciones || [];
        const selectInstituciones = document.querySelector("#institucion");
        const selectProvincia = document.querySelector("#provincia");
        const selectDistrito = document.querySelector("#distrito");
        const selectUgel = document.querySelector("#ugel");
        const selectNivel = document.querySelector("#nivelinstitucion");

        if (selectInstituciones) selectInstituciones.innerHTML = "";
        if (selectProvincia) selectProvincia.innerHTML = "";
        if (selectDistrito) selectDistrito.innerHTML = "";

        if (currentInstituciones.length > 0) {
          if (statusEl) {
            statusEl.innerHTML = '<span class="text-success font-weight-bold">✓ Institución encontrada (' + currentInstituciones.length + ')</span>';
          }

          currentInstituciones.forEach(inst => {
            const option = document.createElement("option");
            option.value = inst.nomInstitucion;
            option.textContent = inst.nomInstitucion;
            if (selectInstituciones) selectInstituciones.appendChild(option);
          });

          // Provincias
          const provincias = [...new Set(currentInstituciones.map(i => i.provincia).filter(Boolean))];
          provincias.forEach(prov => {
            const option = document.createElement("option");
            option.value = prov;
            option.textContent = prov;
            if (selectProvincia) selectProvincia.appendChild(option);
          });

          // Distritos
          const distritos = [...new Set(currentInstituciones.map(i => i.distrito).filter(Boolean))];
          distritos.forEach(dist => {
            const option = document.createElement("option");
            option.value = dist;
            option.textContent = dist;
            if (selectDistrito) selectDistrito.appendChild(option);
          });

          // Auto-seleccionar UGEL si coincide
          const firstInst = currentInstituciones[0];
          if (selectUgel && firstInst.ugel) {
            const ugelTarget = firstInst.ugel.toLowerCase().replace('ugel', '').trim();
            for (let i = 0; i < selectUgel.options.length; i++) {
              const optVal = selectUgel.options[i].value.toLowerCase();
              if (optVal.includes(ugelTarget)) {
                selectUgel.selectedIndex = i;
                break;
              }
            }
          }

          // Auto-seleccionar Tipo de II.EE.
          if (selectNivel && firstInst.nivel) {
            const nivel = firstInst.nivel.toLowerCase();
            for (let i = 0; i < selectNivel.options.length; i++) {
              const optVal = selectNivel.options[i].value.toLowerCase();
              if (nivel.includes("no escolariz") || nivel.includes("pronoei")) {
                if (optVal.includes("no escolarizado")) {
                  selectNivel.selectedIndex = i;
                  break;
                }
              } else {
                if (optVal.includes("escolarizado") && !optVal.includes("no escolarizado")) {
                  selectNivel.selectedIndex = i;
                  break;
                }
              }
            }
          }
        } else {
          if (statusEl) {
            statusEl.innerHTML = '<span class="text-warning font-weight-bold">⚠ No se encontró institución con el código ' + cleanId + '</span>';
          }
          if (selectInstituciones) {
            const option = document.createElement("option");
            option.value = "";
            option.textContent = "-- No se encontró institución con ese código --";
            selectInstituciones.appendChild(option);
          }
        }
      },
      error: function() {
        if (statusEl) {
          statusEl.innerHTML = '<span class="text-danger">Error al consultar el servicio de instituciones</span>';
        }
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    let dniDebounceTimer = null;
    let dniAlreadyExists = false;
    let lastCheckedDni = '';
    const dniInput = document.getElementById('dni');
    const dniFeedback = document.getElementById('dni-feedback');
    const btnGuardar = document.getElementById('btn-guardar');
    const formUser = document.getElementById('form-user');

    // Función de validación por campo individual
    function validateField(fieldId) {
      const el = document.getElementById(fieldId);
      const errEl = document.getElementById(fieldId + '-error');
      if (!el || !errEl) return true;

      let isValid = true;
      let message = '';
      const val = el.value.trim();

      switch (fieldId) {
        case 'dni':
          const digits = el.value.replace(/[^\d]/g, '');
          if (digits.length === 0) {
            isValid = false;
            message = 'El DNI es obligatorio.';
          } else if (digits.length !== 8) {
            isValid = false;
            message = 'El DNI debe tener exactamente 8 dígitos numéricos (actual: ' + digits.length + ').';
          } else if (dniAlreadyExists) {
            isValid = false;
            message = 'Este DNI ya está registrado en otro usuario.';
          }
          break;

        case 'estado':
          if (val === '') {
            isValid = false;
            message = 'Debe seleccionar un estado.';
          }
          break;

        case 'name':
          if (val.length === 0) {
            isValid = false;
            message = 'Los apellidos y nombres son obligatorios.';
          } else if (val.length > 191) {
            isValid = false;
            message = 'Los apellidos y nombres no deben exceder los 191 caracteres.';
          }
          break;

        case 'cargo':
          if (val === '') {
            isValid = false;
            message = 'Debe seleccionar un cargo de la lista.';
          }
          break;

        case 'email':
          const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (val.length === 0) {
            isValid = false;
            message = 'El correo electrónico es obligatorio.';
          } else if (!emailPattern.test(val)) {
            isValid = false;
            message = 'Ingrese un correo electrónico válido (ejemplo: usuario@dominio.com).';
          } else if (val.length > 191) {
            isValid = false;
            message = 'El correo electrónico no debe exceder los 191 caracteres.';
          }
          break;

        case 'password':
          if (val.length === 0) {
            isValid = false;
            message = 'La contraseña es obligatoria.';
          } else if (val.length < 6) {
            isValid = false;
            message = 'La contraseña debe tener al menos 6 caracteres.';
          } else if (val.length > 50) {
            isValid = false;
            message = 'La contraseña no debe exceder los 50 caracteres.';
          }
          break;
      }

      if (!isValid) {
        el.classList.add('is-invalid');
        el.classList.remove('is-valid');
        errEl.textContent = message;
        errEl.classList.add('d-block');
        return false;
      } else {
        el.classList.remove('is-invalid');
        el.classList.add('is-valid');
        errEl.textContent = '';
        errEl.classList.remove('d-block');
        return true;
      }
    }

    // Vincular validadores interactivos a eventos
    ['dni', 'estado', 'name', 'cargo', 'email', 'password'].forEach(fieldId => {
      const el = document.getElementById(fieldId);
      if (!el) return;

      el.addEventListener('blur', function() {
        validateField(fieldId);
      });

      el.addEventListener('input', function() {
        if (el.classList.contains('is-invalid')) {
          validateField(fieldId);
        }
      });

      if (el.tagName === 'SELECT') {
        el.addEventListener('change', function() {
          validateField(fieldId);
        });
      }
    });

    const btnBuscarDni = document.getElementById('btn-buscar-dni');
    const iconBuscarDni = document.getElementById('icon-buscar-dni');
    const badgeReniec = document.getElementById('badge-reniec');
    const nameInput = document.getElementById('name');

    function setSearchingState(isSearching) {
      if (btnBuscarDni) btnBuscarDni.disabled = isSearching;
      if (iconBuscarDni) {
        iconBuscarDni.className = isSearching ? 'fas fa-spinner fa-spin' : 'fas fa-search';
      }
    }

    function checkDniRealTime(dniVal) {
      if (!dniFeedback) return;
      if (dniVal === lastCheckedDni && dniVal.length === 8) return;
      lastCheckedDni = dniVal;

      setSearchingState(true);
      dniFeedback.innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-spin mr-1"></i> Verificando DNI y consultando RENIEC...</span>';
      
      fetch('{{ url('/users/check-dni') }}/' + encodeURIComponent(dniVal) + '?buscar_reniec=1', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        setSearchingState(false);
        if (dniInput.value.replace(/[^\d]/g, '') !== dniVal) return;

        if (data.exists && data.user) {
          dniAlreadyExists = true;
          dniInput.classList.add('is-invalid');
          dniInput.classList.remove('is-valid');
          if (badgeReniec) badgeReniec.classList.add('d-none');
          const dniErr = document.getElementById('dni-error');
          if (dniErr) {
            dniErr.textContent = 'Este DNI ya está registrado en el sistema.';
            dniErr.classList.add('d-block');
          }
          if (btnGuardar) {
            btnGuardar.disabled = true;
            btnGuardar.classList.add('disabled');
          }

          dniFeedback.innerHTML = `
            <div class="alert alert-danger py-2 px-3 mt-2 mb-0 shadow-sm" style="font-size: 0.88rem; line-height: 1.4;">
              <div class="font-weight-bold text-danger">
                <i class="fas fa-exclamation-triangle mr-1"></i> ¡Este DNI ya está registrado en el sistema!
              </div>
              <div class="mt-1 text-dark">
                • <strong>Usuario:</strong> ${data.user.name}<br>
                • <strong>Cargo:</strong> ${data.user.cargo || 'No especificado'} &nbsp;|&nbsp; <strong>Estado:</strong> <span class="badge ${data.user.estado === 'Activo' ? 'badge-success' : 'badge-danger'}">${data.user.estado}</span><br>
                • <strong>Correo:</strong> ${data.user.email}
                ${data.user.institucion ? `<br>• <strong>Institución:</strong> ${data.user.institucion}` : ''}
                ${data.user.ugel ? ` (${data.user.ugel})` : ''}
              </div>
            </div>
          `;

          Swal.fire({
            icon: 'warning',
            title: '¡DNI ya registrado!',
            html: '<div style="text-align: left; font-size: 0.95rem;">' +
                  'El DNI <b>' + dniVal + '</b> ya le pertenece al usuario:<br><br>' +
                  '<b>Nombre:</b> ' + data.user.name + '<br>' +
                  '<b>Cargo:</b> ' + (data.user.cargo || 'No especificado') + '<br>' +
                  '<b>Estado:</b> ' + data.user.estado + '<br>' +
                  '<b>Correo:</b> ' + data.user.email +
                  (data.user.institucion ? '<br><b>Institución:</b> ' + data.user.institucion : '') +
                  '</div>',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#e3342f'
          });
        } else {
          dniAlreadyExists = false;
          dniInput.classList.remove('is-invalid');
          dniInput.classList.add('is-valid');
          const dniErr = document.getElementById('dni-error');
          if (dniErr) {
            dniErr.textContent = '';
            dniErr.classList.remove('d-block');
          }
          if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.classList.remove('disabled');
          }

          if (data.reniec && data.reniec_data) {
            const person = data.reniec_data;
            if (nameInput) {
              nameInput.value = person.nombre_completo;
              validateField('name');
            }
            if (badgeReniec) badgeReniec.classList.remove('d-none');

            dniFeedback.innerHTML = `
              <div class="alert alert-success py-1 px-2 mt-1 mb-0 shadow-sm" style="font-size: 0.88rem;">
                <i class="fas fa-check-circle mr-1 text-success"></i>
                <strong>DNI disponible y verificado en RENIEC:</strong> ${person.nombre_completo}
              </div>
            `;
          } else {
            if (badgeReniec) badgeReniec.classList.add('d-none');
            dniFeedback.innerHTML = '<small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> DNI disponible.</small> <small class="text-muted">(No se encontró en RENIEC, complete los apellidos y nombres manualmente)</small>';
          }
        }
      })
      .catch(err => {
        setSearchingState(false);
        console.error('Error al verificar DNI:', err);
        dniFeedback.innerHTML = '<span class="text-muted"><small>Error al consultar el servicio de DNI. Puede escribir los nombres manualmente.</small></span>';
      });
    }

    if (dniInput) {
      dniInput.addEventListener('input', function() {
        const cleanVal = this.value.replace(/[^\d]/g, '').slice(0, 8);
        if (this.value !== cleanVal) {
          this.value = cleanVal;
        }

        clearTimeout(dniDebounceTimer);

        if (cleanVal.length === 0) {
          dniAlreadyExists = false;
          lastCheckedDni = '';
          this.classList.remove('is-invalid', 'is-valid');
          if (badgeReniec) badgeReniec.classList.add('d-none');
          const dniErr = document.getElementById('dni-error');
          if (dniErr) {
            dniErr.textContent = '';
            dniErr.classList.remove('d-block');
          }
          if (dniFeedback) dniFeedback.innerHTML = '';
          if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.classList.remove('disabled');
          }
          return;
        }

        if (cleanVal.length < 8) {
          dniAlreadyExists = false;
          lastCheckedDni = '';
          if (badgeReniec) badgeReniec.classList.add('d-none');
          const dniErr = document.getElementById('dni-error');
          if (dniErr) {
            dniErr.textContent = 'El DNI debe tener exactamente 8 dígitos (actual: ' + cleanVal.length + ').';
            dniErr.classList.add('d-block');
          }
          this.classList.add('is-invalid');
          this.classList.remove('is-valid');
          if (dniFeedback) {
            dniFeedback.innerHTML = '<span class="text-muted"><small>Faltan ' + (8 - cleanVal.length) + ' dígito(s)...</small></span>';
          }
          if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.classList.remove('disabled');
          }
          return;
        }

        const dniErr = document.getElementById('dni-error');
        if (dniErr) {
          dniErr.textContent = '';
          dniErr.classList.remove('d-block');
        }
        dniDebounceTimer = setTimeout(function() {
          checkDniRealTime(cleanVal);
        }, 150);
      });

      dniInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          const cleanVal = this.value.replace(/[^\d]/g, '');
          if (cleanVal.length === 8) {
            lastCheckedDni = '';
            checkDniRealTime(cleanVal);
          }
        }
      });

      if (dniInput.value && dniInput.value.replace(/[^\d]/g, '').length === 8) {
        checkDniRealTime(dniInput.value.replace(/[^\d]/g, ''));
      }
    }

    if (btnBuscarDni) {
      btnBuscarDni.addEventListener('click', function() {
        const cleanVal = (dniInput ? dniInput.value : '').replace(/[^\d]/g, '');
        if (cleanVal.length !== 8) {
          Swal.fire({
            icon: 'info',
            title: 'DNI incompleto',
            text: 'Debe ingresar un DNI de exactamente 8 dígitos para consultar en RENIEC.',
            confirmButtonText: 'Entendido'
          });
          if (dniInput) dniInput.focus();
          return;
        }
        lastCheckedDni = '';
        checkDniRealTime(cleanVal);
      });
    }

    if (formUser) {
      formUser.addEventListener('submit', function(e) {
        const fieldsToValidate = ['dni', 'estado', 'name', 'cargo', 'email', 'password'];
        let hasErrors = false;
        let firstInvalidField = null;
        const errorList = [];

        fieldsToValidate.forEach(fieldId => {
          const isValid = validateField(fieldId);
          if (!isValid) {
            hasErrors = true;
            const errEl = document.getElementById(fieldId + '-error');
            if (errEl && errEl.textContent) {
              errorList.push(errEl.textContent);
            }
            if (!firstInvalidField) {
              firstInvalidField = document.getElementById(fieldId);
            }
          }
        });

        if (dniAlreadyExists) {
          hasErrors = true;
          errorList.push('El DNI ingresado ya le pertenece a otro usuario en el sistema.');
          if (!firstInvalidField) firstInvalidField = dniInput;
        }

        if (hasErrors) {
          e.preventDefault();
          e.stopPropagation();

          if (firstInvalidField) {
            firstInvalidField.focus();
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }

          const listHtml = errorList.map(err => `<li>${err}</li>`).join('');
          Swal.fire({
            icon: 'error',
            title: '¡Campos requeridos pendientes!',
            html: '<p class="text-muted mb-2">Por favor verifique los campos marcados en rojo:</p><ul style="text-align: left; margin-bottom: 0;">' + listHtml + '</ul>',
            confirmButtonText: 'Corregir',
            confirmButtonColor: '#e3342f'
          });
          return false;
        }
      });
    }

    // Búsqueda modular en tiempo real mientras escribe
    const codInput = document.getElementById('codmodular');
    if (codInput) {
      codInput.addEventListener('input', function() {
        // Solo dígitos y máximo 7
        const cleanVal = this.value.replace(/[^\d]/g, '').slice(0, 7);
        if (this.value !== cleanVal) {
          this.value = cleanVal;
        }

        const statusEl = document.getElementById('codmodular-status');
        clearTimeout(codDebounceTimer);

        if (cleanVal.length === 0) {
          if (statusEl) statusEl.innerHTML = 'Ingrese el código modular (6 o 7 dígitos) para autocompletar en tiempo real.';
          return;
        }

        if (cleanVal.length < 6) {
          if (statusEl) statusEl.innerHTML = '<span class="text-muted">Faltan ' + (6 - cleanVal.length) + ' dígito(s)...</span>';
          return;
        }

        // Si tiene 7 dígitos, buscar casi de inmediato (80ms); si tiene 6, dar 250ms por si sigue escribiendo el 7mo
        const delay = cleanVal.length === 7 ? 80 : 250;
        codDebounceTimer = setTimeout(function() {
          showInstituciones(cleanVal);
        }, delay);
      });

      // Si ya hay un valor previo cargado (por redirección con errores)
      if (codInput.value && codInput.value.replace(/[^\d]/g, '').length >= 6) {
        showInstituciones(codInput.value);
      }
    }

    // Si cambia la institución seleccionada y hay varias, actualizar provincia/distrito
    const selectInstitucion = document.getElementById('institucion');
    if (selectInstitucion) {
      selectInstitucion.addEventListener('change', function() {
        const selected = this.value;
        const match = currentInstituciones.find(i => i.nomInstitucion === selected);
        if (match) {
          const selectProv = document.getElementById('provincia');
          const selectDist = document.getElementById('distrito');
          if (selectProv && match.provincia) selectProv.value = match.provincia;
          if (selectDist && match.distrito) selectDist.value = match.distrito;
        }
      });
    }


    // Modal SweetAlert en base a errores
    @if ($errors->any())
      Swal.fire({
        icon: 'error',
        title: '¡Errores en el formulario!',
        html: '<ul style="text-align: left; margin-bottom: 0;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
        confirmButtonText: 'Entendido'
      });
    @endif
  });
</script>
@stop