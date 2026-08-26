@extends('adminlte::page')

@section('title', 'Rol')

@section('content_header')
    <h1>Asignar Rol</h1>
@stop

@section('content')

   <p class="h5">Nombre</p>
   <p class="form-control">{{$user->name}}</p>
   <h2 class="h5">Listado de Roles</h2>
   {!! Form::model($user, ['route' => ['users.update',$user], 'method' => 'put']) !!}

   @foreach($roles as $role)
   <div >
        <label >
            {!! Form::checkbox('roles[]', $role->id, null, ['class' => 'mr-1']) !!}
            {{$role->name}}
        </label>
   </div>
   @endforeach
   
   {!! Form::submit('Asignar Rol', ['class' => 'btn btn-primary mt-2']) !!}

   {!! Form::close() !!}
   
@stop

@section('css')

@stop

@section('js')  
@stop