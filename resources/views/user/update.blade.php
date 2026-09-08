
@extends('adminlte::page')

@section('title', 'Usuario')

@section('content_header')
    <h1>Editar Usuario</h1>
@stop

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-cyan card-outline">
                <div class="card-header"><h1 align="center">Editar Usuario</h1></div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-bs-dismiss="alert" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

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

                    <form id="form-user-edit" method="POST" action="{{ route('users.update', $user->id) }}" novalidate>
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group row">
                            <label for="dni" class="col-md-4 col-form-label text-md-right">{{ __('DNI') }} <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input id="dni" type="text" class="form-control @error('dni') is-invalid @enderror" 
                                       required name="dni" value="{{ old('dni', $user->dni) }}" autofocus maxlength="8" 
                                       pattern="[0-9]{8}" title="El DNI debe tener exactamente 8 dígitos numéricos" autocomplete="off">
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
                                    <option value="1" {{ old('estado', (string)$user->estado) === '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('estado', (string)$user->estado) === '0' ? 'selected' : '' }}>Inactivo</option>
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
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Apellidos y Nombres') }} <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                       required name="name" value="{{ old('name', $user->name) }}" maxlength="191" autocomplete="off">
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
                                        <option value="{{ $ug }}" {{ old('ugel', $user->ugel) == $ug ? 'selected' : '' }}>{{ $ug }}</option>
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
                            <label for="codmodular" class="col-md-4 col-form-label text-md-right">{{ __('Buscar por Cód. Modular') }}</label>
                            <div class="col-md-6">
                                <input id="codmodular" name="codmodular" type="text" class="form-control" tabindex="1" 
                                       value="{{ old('codmodular') }}" maxlength="7" placeholder="Ingrese 6 o 7 dígitos para autocompletar">
                                <small id="codmodular-status" class="form-text text-muted">
                                    Opcional: ingrese el código modular para autocompletar la institución en tiempo real.
                                </small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="institucion" class="col-md-4 col-form-label text-md-right">{{ __('Institución Educativa') }}</label>
                            <div class="col-md-6">
                                <input id="institucion" name="institucion" type="text" 
                                       class="form-control @error('institucion') is-invalid @enderror" 
                                       value="{{ old('institucion', $user->institucion) }}" 
                                       tabindex="1" size="50" maxlength="191">
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
                                <select id="nivelinstitucion" name="nivelinstitucion" 
                                        class="form-control @error('nivelinstitucion') is-invalid @enderror">
                                    <option value="">----Seleccione Nivel-----</option>
                                    <option value="Escolarizado" {{ old('nivelinstitucion', $user->nivelinstitucion) == 'Escolarizado' ? 'selected' : '' }}>Escolarizado</option>
                                    <option value="No escolarizado - PRONOEI" {{ old('nivelinstitucion', $user->nivelinstitucion) == 'No escolarizado - PRONOEI' ? 'selected' : '' }}>No escolarizado - PRONOEI</option>
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
                                <input id="provincia" name="provincia" type="text" 
                                       class="form-control @error('provincia') is-invalid @enderror" 
                                       value="{{ old('provincia', $user->provincia) }}" 
                                       tabindex="1" size="40" maxlength="80">
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
                                <input id="distrito" name="distrito" type="text" 
                                       class="form-control @error('distrito') is-invalid @enderror" 
                                       tabindex="1" value="{{ old('distrito', $user->distrito) }}" 
                                       size="40" maxlength="80">
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
                                    @foreach(['Especialista DRE', 'Especialista UGEL', 'Director', 'Docente', 'Profesor Coordinador', 'Promotor(a) Educativa Comunitaria'] as $c)
                                        <option value="{{ $c }}" {{ old('cargo', $user->cargo) == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
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
                                <input id="email" name="email" type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       tabindex="1" value="{{ old('email', $user->email) }}" 
                                       required maxlength="191" autocomplete="off">
                                <div id="email-error" class="invalid-feedback font-weight-bold"></div>
                                @error('email')
                                    <span class="invalid-feedback d-block font-weight-bold" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span> 
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Nueva Contraseña (Opcional)') }}</label>
                            <div class="col-md-6">
                                <input id="password" name="password" type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="Dejar en blanco para mantener la contraseña actual" 
                                       tabindex="1" minlength="6" maxlength="50" autocomplete="new-password">
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
                                    {{ __('Guardar Cambios') }}
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
#institucion{
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
                const instInput = document.querySelector("#institucion");
                const provInput = document.querySelector("#provincia");
                const distInput = document.querySelector("#distrito");
                const selectUgel = document.querySelector("#ugel");
                const selectNivel = document.querySelector("#nivelinstitucion");

                if (currentInstituciones.length > 0) {
                    const firstInst = currentInstituciones[0];
                    if (statusEl) {
                        statusEl.innerHTML = '<span class="text-success font-weight-bold">✓ Institución encontrada: ' + firstInst.nomInstitucion + '</span>';
                    }

                    if (instInput) instInput.value = firstInst.nomInstitucion || '';
                    if (provInput) provInput.value = firstInst.provincia || '';
                    if (distInput) distInput.value = firstInst.distrito || '';

                    // Auto-seleccionar UGEL si coincide
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
        // Verificación de DNI en tiempo real (excluyendo al usuario actual)
        const currentUserId = {{ $user->id }};
        const currentInitialDni = '{{ $user->dni }}';
        let dniDebounceTimer = null;
        let dniAlreadyExists = false;
        let lastCheckedDni = '';
        const dniInput = document.getElementById('dni');
        const dniFeedback = document.getElementById('dni-feedback');
        const btnGuardar = document.getElementById('btn-guardar');
        const formUserEdit = document.getElementById('form-user-edit');

        // Función de validación por campo individual para edición
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
                        message = 'Este DNI ya le pertenece a otro usuario en el sistema.';
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
                    // En edición la contraseña es opcional. Solo se valida si se escribe algo.
                    if (val.length > 0) {
                        if (val.length < 6) {
                            isValid = false;
                            message = 'La nueva contraseña debe tener al menos 6 caracteres.';
                        } else if (val.length > 50) {
                            isValid = false;
                            message = 'La nueva contraseña no debe exceder los 50 caracteres.';
                        }
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
                if (fieldId === 'password' && val.length === 0) {
                    el.classList.remove('is-valid');
                } else {
                    el.classList.add('is-valid');
                }
                errEl.textContent = '';
                errEl.classList.remove('d-block');
                return true;
            }
        }

        // Vincular validadores interactivos a eventos blur, input y change
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

        function checkDniRealTime(dniVal) {
            if (!dniFeedback) return;
            if (dniVal === lastCheckedDni && dniVal.length === 8) return;
            lastCheckedDni = dniVal;

            if (dniVal === currentInitialDni) {
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
                dniFeedback.innerHTML = '<small class="text-muted"><i class="fas fa-check text-success mr-1"></i> DNI actual de este usuario.</small>';
                return;
            }

            dniFeedback.innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-spin mr-1"></i> Verificando DNI en tiempo real...</span>';

            fetch('{{ url('/users/check-dni') }}/' + encodeURIComponent(dniVal) + '?exclude_id=' + currentUserId, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (dniInput.value.replace(/[^\d]/g, '') !== dniVal) return;

                if (data.exists && data.user) {
                    dniAlreadyExists = true;
                    dniInput.classList.add('is-invalid');
                    dniInput.classList.remove('is-valid');
                    const dniErr = document.getElementById('dni-error');
                    if (dniErr) {
                        dniErr.textContent = 'Este DNI ya le pertenece a otro usuario en el sistema.';
                        dniErr.classList.add('d-block');
                    }
                    if (btnGuardar) {
                        btnGuardar.disabled = true;
                        btnGuardar.classList.add('disabled');
                    }

                    dniFeedback.innerHTML = `
                        <div class="alert alert-danger py-2 px-3 mt-2 mb-0 shadow-sm" style="font-size: 0.88rem; line-height: 1.4;">
                            <div class="font-weight-bold text-danger">
                                <i class="fas fa-exclamation-triangle mr-1"></i> ¡Este DNI ya le pertenece a otro usuario!
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
                              'El DNI <b>' + dniVal + '</b> ya le pertenece a otro usuario en el sistema:<br><br>' +
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

                    dniFeedback.innerHTML = '<small class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> DNI disponible.</small>';
                }
            })
            .catch(err => {
                console.error('Error al verificar DNI:', err);
                dniFeedback.innerHTML = '';
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
                }, 100);
            });
        }

        if (formUserEdit) {
            formUserEdit.addEventListener('submit', function(e) {
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
                const cleanVal = this.value.replace(/[^\d]/g, '').slice(0, 7);
                if (this.value !== cleanVal) {
                    this.value = cleanVal;
                }

                const statusEl = document.getElementById('codmodular-status');
                clearTimeout(codDebounceTimer);

                if (cleanVal.length === 0) {
                    if (statusEl) statusEl.innerHTML = 'Opcional: ingrese el código modular para autocompletar la institución en tiempo real.';
                    return;
                }

                if (cleanVal.length < 6) {
                    if (statusEl) statusEl.innerHTML = '<span class="text-muted">Faltan ' + (6 - cleanVal.length) + ' dígito(s)...</span>';
                    return;
                }

                const delay = cleanVal.length === 7 ? 80 : 250;
                codDebounceTimer = setTimeout(function() {
                    showInstituciones(cleanVal);
                }, delay);
            });

            if (codInput.value && codInput.value.replace(/[^\d]/g, '').length >= 6) {
                showInstituciones(codInput.value);
            }
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