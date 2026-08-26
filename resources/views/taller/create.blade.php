@extends('adminlte::page')

@section('title', 'Taller')

@section('content_header')
    <h1>Crear Nuevo Taller</h1>
@stop

@section('content')
<div class="container">
  <div class="abs-center">
<form class="border p-3 form" action="/tallers" method="POST">
  @csrf
  <div class="form-group ">
    <label for="" class="form-label">Nombre de Taller</label>
    <input id="nombreTaller" name="nombreTaller" type="text" class="form-control" tabindex="1" required> 
  </div>
  <div class="form-group ">
    <label for="" class="form-label">Plan</label>
    <input id="plan" name="plan" type="file" class="form-control" tabindex="1" required> 
  </div>
  <div class="form-group ">
    <label for="" class="form-label">Fecha Supervicion</label>
    <input id="fechaSupervicion" name="fechaSupervicion" type="date" class="form-control" tabindex="1" required> 
  </div>
  <div class="form-group ">
    <label for="" class="form-label">Docente Supervisado</label>
    <input id="docente" name="docente" type="text" class="form-control" tabindex="1" required> 
  </div>
  <a href="/tallers" class="btn btn-danger" tabindex="4">Cancelar</a>
  <button type="submit" class="btn btn-primary" tabindex="3">Guardar</button>
  </form>
</div>
</div>
@stop

@section('css')
    
@stop

@section('js')
    
@stop