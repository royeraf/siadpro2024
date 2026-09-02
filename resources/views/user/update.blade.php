
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

                    <form method="POST" action="{{ route('users.update', $user->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group row">
                            <label for="dni" class="col-md-4 col-form-label text-md-right">{{ __('DNI') }}</label>
                            <div class="col-md-6">
                                <input id="dni" type="text" class="form-control @error('dni') is-invalid @enderror" 
                                       required name="dni" value="{{ old('dni', $user->dni) }}" autofocus maxlength="8">
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
                                <select id="estado" name="estado" class="form-control @error('estado') is-invalid @enderror" required>
                                    <option value="0" @if(old('estado', $user->estado) == 0) selected @endif>Inactivo</option>
                                    <option value="1" @if(old('estado', $user->estado) == 1) selected @endif>Activo</option>
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
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                       required name="name" value="{{ old('name', $user->name) }}">
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
                                <select id="ugel" name="ugel" class="form-control @error('ugel') is-invalid @enderror" required>
                                    <option value="{{ old('ugel', $user->ugel) }}">{{ old('ugel', $user->ugel) }}</option>
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
                                <input id="institucion" name="institucion" type="text" 
                                       class="form-control @error('institucion') is-invalid @enderror" 
                                       value="{{ old('institucion', $user->institucion) }}" 
                                       tabindex="1" required size="30" maxlength="30">
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
                                        class="form-control @error('nivelinstitucion') is-invalid @enderror" required>
                                    <option value="{{ old('nivelinstitucion', $user->nivelinstitucion) }}">{{ old('nivelinstitucion', $user->nivelinstitucion) }}</option>
                                    <option value="Escolarizado">Escolarizado</option>
                                    <option value="No escolarizado - PRONOEI">No escolarizado - PRONOEI</option>
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
                                       tabindex="1" required size="30" maxlength="30">
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
                                       required size="30" maxlength="30">
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
                                <select id="cargo" name="cargo" class="form-control @error('cargo') is-invalid @enderror" required>
                                    <option value="{{ old('cargo', $user->cargo) }}">{{ old('cargo', $user->cargo) }}</option>
                                    <option value="Especialista DRE">Especialista DRE</option>
                                    <option value="Especialista UGEL">Especialista UGEL</option>
                                    <option value="Director">Director</option>
                                    <option value="Profesor Coordinador">Profesor Coordinador</option>
                                    <option value="Docente">Docente</option>
                                    <option value="Promotor(a) Educativa Comunitaria">Promotor(a) Educativa Comunitaria</option>
                                </select>
                                @error('cargo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Correo Electrónico') }}</label>
                            <div class="col-md-6">
                                <input id="email" name="email" type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       tabindex="1" value="{{ old('email', $user->email) }}" 
                                       required size="50" maxlength="50">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
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
                                       placeholder="Deja vacío para mantener la contraseña actual" 
                                       tabindex="1" maxlength="50">
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
    $.get("/api/instituciones/"+id, function(instituciones){
      let selectInstituciones = document.querySelector("#institucion");
      selectInstituciones.innerHTML = "";
      instituciones.forEach(institucion => {
        let option = document.createElement("option");
        option.setAttribute("value", institucion.nomInstitucion);
        option.innerHTML = institucion.nomInstitucion;
        selectInstituciones.appendChild(option);
      });
    });
  }
</script>  
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dniInput = document.getElementById('dni');

        dniInput.addEventListener('input', function() {
            const inputValue = dniInput.value.trim();
            const numericValue = inputValue.replace(/[^\d]/g, '');

            if (numericValue.length > 8) {
                dniInput.value = numericValue.slice(0, 8);
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
            const domain = inputValue.split('@')[1];

            if (validDomains.includes(domain)) {
                emailInput.setCustomValidity('');
            } else {
                emailInput.setCustomValidity('El correo electrónico debe ser de uno de los dominios válidos.');
            }
        });
    });
</script>
@stop