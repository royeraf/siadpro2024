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
                    <form method="POST" action="/usuarios/{{$user->id}}" >
                        @csrf
                        @method('PUT')
                        <div class="form-group row">
                            <label for="dni" class="col-md-4 col-form-label text-md-right">{{ __('DNI') }}</label>

                            <div class="col-md-6">
                                <input id="dni" type="text" class="form-control @error('dni') is-invalid @enderror" required name="dni" value="{{$user->dni}}" autofocus>

                                @error('dni')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="estado" class="col-md-4 col-form-label text-md-right">{{ __('Estado') }}</label>
                            <div class="col-md-6">
                                <select id="estado" name="estado" class="form-control">
                                    <option value="0" @if($user->estado == 0) selected @endif>Inactivo</option>
                                    <option value="1" @if($user->estado == 1) selected @endif>Activo</option>
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
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" required name="name" value="{{$user->name}}" autofocus>

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
                            <select id="ugel" name="ugel" class="form-control" >
                                <option value="{{$user->ugel}}">{{$user->ugel}}</option>
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
                            <label for="institucion" class="col-md-4 col-form-label text-md-right">{{ __('Institución Educativa') }}</label>

                            <div class="col-md-6">
                            <input id="institucion" name="institucion" type="text" class="form-control" value="{{$user->institucion}}" tabindex="1" required size="30" maxlength="30">
                            

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
                            <select id="nivelinstitucion" name="nivelinstitucion" class="form-control" >
                            <option value="{{$user->nivelinstitucion}}">{{$user->nivelinstitucion}}</option>
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
                          
                            <input id="provincia" name="provincia" type="text" class="form-control" value="{{$user->provincia}}" tabindex="1" required size="30" maxlength="30">
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
                          
                            <input id="distrito" name="distrito" type="text" class="form-control" tabindex="1" value="{{$user->distrito}}" required size="30" maxlength="30">
                                @error('distrito')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="cargo" class="col-md-4 col-form-label text-md-right">{{ __('Cargo') }}</label>

                            <div class="col-md-6">
                            <select id="cargo" name="cargo" class="form-control" >
                                <option value="{{$user->cargo}}">{{$user->cargo}}</option>
                                    <option value="Especialista DRE">Especialista DRE</option>
                                    <option value="Especialista UGEL">Especialista UGEL</option>
                                    <option value="Director">Director</option>
                                    <option value="Profesor Coordinador">Profesor Coordinador</option>
                                    <option value="Docente">Docente</option>
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
                            <input id="email" name="email" type="text" class="form-control" tabindex="1" value="{{$user->email}}" required size="50" maxlength="50"> 

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
                            <input id="password" name="password" type="password" class="form-control" value="{{$user->password}}" tabindex="1" required size="50" maxlength="50"> 

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
    </style>
@stop

@section('js') 
<script>
  function showInstituciones(id) {
    if (!id) return;
    let cleanId = id.toString().trim();
    if (cleanId.length === 0) return;

    let baseUrl = "{{ url('api/instituciones') }}";
    let url = baseUrl + "/" + encodeURIComponent(cleanId);

    $.ajax({
      url: url,
      type: 'GET',
      dataType: 'json',
      success: function(instituciones) {
        let selectInstituciones = document.querySelector("#institucion");
        let selectProvincia = document.querySelector("#provincia");
        let selectDistrito = document.querySelector("#distrito");
        let selectUgel = document.querySelector("#ugel");
        let selectNivel = document.querySelector("#nivelinstitucion");

        if (selectInstituciones) selectInstituciones.innerHTML = "";
        if (selectProvincia) selectProvincia.innerHTML = "";
        if (selectDistrito) selectDistrito.innerHTML = "";

        if (instituciones && instituciones.length > 0) {
          instituciones.forEach(inst => {
            let option = document.createElement("option");
            option.value = inst.nomInstitucion;
            option.textContent = inst.nomInstitucion;
            if (selectInstituciones) selectInstituciones.appendChild(option);
          });

          if (selectProvincia) {
            instituciones.forEach(prov => {
              let option = document.createElement("option");
              option.value = prov.provincia;
              option.textContent = prov.provincia;
              selectProvincia.appendChild(option);
            });
          }

          if (selectDistrito) {
            instituciones.forEach(dist => {
              let option = document.createElement("option");
              option.value = dist.distrito;
              option.textContent = dist.distrito;
              selectDistrito.appendChild(option);
            });
          }

          if (selectUgel && instituciones[0].ugel) {
            let ugelTarget = instituciones[0].ugel.toLowerCase().replace('ugel', '').trim();
            for (let i = 0; i < selectUgel.options.length; i++) {
              let optText = selectUgel.options[i].text.toLowerCase();
              let optVal = selectUgel.options[i].value.toLowerCase();
              if (optText.includes(ugelTarget) || optVal.includes(ugelTarget)) {
                selectUgel.selectedIndex = i;
                break;
              }
            }
          }

          if (selectNivel && instituciones[0].nivel) {
            let nivel = instituciones[0].nivel.toLowerCase();
            for (let i = 0; i < selectNivel.options.length; i++) {
              let optText = selectNivel.options[i].text.toLowerCase();
              if (nivel.includes("no escolariz") || nivel.includes("pronoei")) {
                if (optText.includes("no escolarizado") || optText.includes("pronoei")) {
                  selectNivel.selectedIndex = i;
                  break;
                }
              } else if (nivel.includes("inicial") || nivel.includes("primaria") || nivel.includes("secundaria") || nivel.includes("jardin")) {
                if (optText.includes("escolarizado") && !optText.includes("no escolarizado")) {
                  selectNivel.selectedIndex = i;
                  break;
                }
              }
            }
          }
        } else {
          if (selectInstituciones) {
            let option = document.createElement("option");
            option.value = "";
            option.textContent = "-- No se encontró institución --";
            selectInstituciones.appendChild(option);
          }
        }
      },
      error: function(xhr, status, error) {
        console.error("Error al buscar institución:", error);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    const codInput = document.getElementById('codmodular');
    if (codInput) {
      ['input', 'change', 'keyup', 'paste'].forEach(evt => {
        codInput.addEventListener(evt, function() {
          showInstituciones(this.value);
        });
      });
    }
  });
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
@stop