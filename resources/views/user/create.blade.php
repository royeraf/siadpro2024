@extends('adminlte::page')

@section('title', 'Usuario')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-cyan card-outline">
                <div class="card-header"><h1 align="center">Crear Nuevo Usuario</h1></div>

                <div class="card-body">
                    <form method="POST" action="/users" >
                        @csrf
                        <div class="form-group row">
                            <label for="dni" class="col-md-4 col-form-label text-md-right">{{ __('DNI') }}</label>

                            <div class="col-md-6">
                                <input id="dni" type="text" class="form-control @error('dni') is-invalid @enderror" required name="dni" autofocus>

                                @error('dni')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="estado" class="col-md-4 col-form-label text-md-right">{{ __('ESTADO') }}</label>

                            <div class="col-md-6">
                                <select id="estado" name="estado" class="form-control @error('estado') is-invalid @enderror" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                        
                                @error('estado')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Apellidos y Nombres') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" required name="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="ugel" class="col-md-4 col-form-label text-md-right">{{ __('UGEL') }}</label>

                            <div class="col-md-6">
                            <select id="ugel" name="ugel" class="form-control">
                                <option value="">----Seleccione Ugel-----</option>
                                    <option value="Ugel Huánuco">Ugel Huánuco</option>
                                    <option value="Ugel Ambo">Ugel Ambo</option>
                                    <option value="Ugel Dos de Mayo">Ugel Dos de Mayo</option>
                                    <option value="Ugel Lauricocha">Ugel Lauricocha</option>
                                    <option value="Ugel Leoncio Prado">Ugel Leoncio Prado</option>
                                    <option value="Ugel Huacaybamba">Ugel Huacaybamba</option>
                                    <option value="Ugel Huamalies">Ugel Huamalies</option>
                                    <option value="Ugel Marañon">Ugel Marañon</option>
                                    <option value="Ugel Pachitea">Ugel Pachitea</option>
                                    <option value="Ugel Puerto Inca">Ugel Puerto Inca</option>
                                    <option value="Ugel Yarowilca">Ugel Yarowilca</option>
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
                            <input id="codmodular" name="" type="number" class="form-control" tabindex="1" onchange="showInstituciones(this.value)" maxlength="7" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">

                                @error('codmodular')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="institucion" class="col-md-4 col-form-label text-md-right">{{ __('Institución Educativa') }}</label>

                            <div class="col-md-6">
                            <select name="institucion" id="institucion" class="form-control" >
            
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
                            <select id="nivelinstitucion" name="nivelinstitucion" class="form-control">
                            <option value="">----Seleccione Nivel-----</option>
                                <option value="Escolarizado">Escolarizado</option>
                                <option value="No escolarizado - PRONOEI" >No escolarizado - PRONOEI</option>
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
                            <select name="provincia" id="provincia" class="form-control" >
            
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
                            <select name="distrito" id="distrito" class="form-control" >
            
                            </select>
                                @error('distrito')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="lugar" class="col-md-4 col-form-label text-md-right">{{ __('Cargo') }}</label>

                            <div class="col-md-6">
                            <select id="cargo" name="cargo" class="form-control" required>
                                <option value="">----Seleccione Cargo-----</option>
                                    <option value="Especialista DRE">Especialista DRE</option>
                                    <option value="Especialista UGEL">Especialista UGEL</option>
                                    <option value="Especialista UGEL">Director (a) con seccion a cargo</option>
                                    <option value="Especialista UGEL">Director (a) sin seccion a cargo</option>
                                    <option value="Especialista UGEL">Profesor (a) de aula</option>
                                    <option value="Profesor Coordinador">Profesor(a) Coordinador(a)</option>
                                    <option value="Docente">Promotor(a) Educativa Comunitaria</option>
                                </select>
                                @error('lugar')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Correo Electronico') }}</label>

                            <div class="col-md-6">
                            <input id="email" name="email" type="text" class="form-control" tabindex="1" required size="50" maxlength="50"> 

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span> 
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Contraseña') }}</label>

                            <div class="col-md-6">
                            <input id="password" name="password" type="password" class="form-control" tabindex="1" required size="50" maxlength="50"> 

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span> 
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <a href="/users" class="btn btn-danger" tabindex="4">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
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
  
</style>
@stop

@section('js') 
@vite(['resources/js/app.js'])
<x-sweet-alert />
<script>
  function showInstituciones(id) {
    $.get("/api/instituciones/"+id, function(instituciones){
      let selectInstituciones = document.querySelector("#institucion");
      selectInstituciones.innerHTML = "";
      instituciones.forEach(institucion => {
        let option = document.createElement("option");
        option.setAttribute("value", institucion.nomInstitucion);
        option.innerHTML = institucion.nomInstitucion;
        selectInstituciones.appendChild(option);
      });

      let selectProvincia = document.querySelector("#provincia");
      selectProvincia.innerHTML = "";
      instituciones.forEach(provincia => {
        let option = document.createElement("option");
        option.setAttribute("value", provincia.provincia);
        option.innerHTML = provincia.provincia;
        selectProvincia.appendChild(option);
      });
      let selectDistrito = document.querySelector("#distrito");
      selectDistrito.innerHTML = "";
      instituciones.forEach(distrito => {
        let option = document.createElement("option");
        option.setAttribute("value", distrito.distrito);
        option.innerHTML = distrito.distrito;
        selectDistrito.appendChild(option);
      });
      
    });
    
  }
</script> 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dniInput = document.getElementById('dni');

        dniInput.addEventListener('input', function() {
            const inputValue = dniInput.value.trim();
            const numericValue = inputValue.replace(/[^\d]/g, ''); // Elimina caracteres no numéricos

            if (numericValue.length > 8) {
                dniInput.value = numericValue.slice(0, 8); // Limita a 8 caracteres
            } else {
                dniInput.value = numericValue;
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const estadoInput = document.getElementById('estado');

        dniInput.addEventListener('input', function() {
            const inputValue = estadoInput.value.trim();
            const numericValue = inputValue.replace(/[^\d]/g, ''); // Elimina caracteres no numéricos

            if (numericValue.length > 8) {
                estadoInput.value = numericValue.slice(0, 1); // Limita a 8 caracteres
            } else {
                estadoInput.value = numericValue;
            }
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        const validDomains = ['gmail.com', 'hotmail.com', 'outlook.es','outlook.com', 'movistar.pe']; 

        emailInput.addEventListener('input', function() {
            const inputValue = emailInput.value.trim();
            const domain = inputValue.split('@')[1]; // Obtener el dominio del correo electrónico

            if (validDomains.includes(domain)) {
                emailInput.setCustomValidity(''); // Dirección válida
            } else {
                emailInput.setCustomValidity('El correo electrónico debe ser de uno de los dominios válidos.');
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal en base a errores
        @if ($errors->any())
            @if ($errors->has('dni') && $errors->has('email'))
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: 'El correo electrónico y el DNI ya están registrados. Por favor, ingrese un correo y un DNI diferentes.'
                });
            @elseif ($errors->has('dni'))
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: 'El DNI ya está registrado. Por favor, ingrese un DNI diferente.'
                });
            @elseif ($errors->has('email'))
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: 'El correo electrónico ya está registrado. Por favor, ingrese un correo diferente.'
                });
            @else
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: 'Hubo un problema al guardar los datos. Por favor, verifique los campos.'
                });
            @endif
        @endif
    });
</script>
@stop