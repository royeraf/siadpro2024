@extends('adminlte::page')

@section('title', 'Institucion')

@section('content_header')
   <h1 >CREAR INSTITUCION</h1>
@stop

@section('content')
<div class="container">
  <div class="abs-center">
<form class="border p-3 form" action="/institucions" method="POST">
  @csrf
  <div class="form-group ">
    <label for="" class="form-label">Cod. Modular:</label>
    <input id="codModular" name="codModular" type="number" class="form-control" tabindex="1" required maxlength="7" oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"> 
  </div>
  <div class="form-group">
    <label for="" class="form-label">Institucion:</label>
    <input id="nomInstitucion" name="nomInstitucion" type="text" class="form-control" tabindex="1" required> 
  </div>
  <div class="mb-3">
    <label for="" class="form-label">Nivel:</label>
    <select id="nivel" name="nivel" class="form-control" required>
    <option value="">----Seleccione Nivel-----</option>
         <option value="Inicial-Jardin" >Inicial - Jardin</option>
         <option value="Primaria" >Primaria</option>
         <option value="Secundaria" >Secundaria</option>
    </select>
  </div>
    <div class="form-group">
    <label for="" class="form-label">Provincia:</label>
    <input id="provincia" name="provincia" type="text" class="form-control" tabindex="1" required size="30" maxlength="30"> 
  </div>
  <div class="form-group">
    <label for="" class="form-label">Distrito:</label>
    <input id="distrito" name="distrito" type="text" class="form-control" tabindex="1" required size="40" maxlength="40"> 
  </div>
  <div class="mb-3">
    <label for="" class="form-label">Centro Poblado:</label>
    <input id="centropoblado" name="centropoblado" type="text" class="form-control" tabindex="1" required size="50" maxlength="50"> 
    </select>
  </div>
    
  <div class="form-group">
    <label for="" class="form-label">UGEL:</label>
    <input id="ugel" name="ugel" type="text" class="form-control" tabindex="1" required size="40" maxlength="40"> 
  </div>
  <a href="/institucions" class="btn btn-danger" tabindex="4">Cancelar</a>
  <button type="submit" class="btn btn-primary" tabindex="3">Guardar</button>
</form>
</div>
</div>
@stop

@section('css')
    <style>
      .abs-center {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
}
input[type=number]::-webkit-inner-spin-button, 
 input[type=number]::-webkit-outer-spin-button { 
   -webkit-appearance: none; 
   margin: 0; 
 }
 input[type=number] { -moz-appearance:textfield; }
input{
  text-transform: uppercase;
}
.form {
  width: 450px;
}
h1{

display: flex;

justify-content: center;

}
    </style>
@stop

@section('js')  
@stop