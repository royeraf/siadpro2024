@extends('adminlte::auth.login')

@section('css')
<style>
    /* En pantallas muy estrechas, apilar el checkbox "Mantener sesión iniciada"
       y el botón de inicio de sesión a ancho completo */
    @media (max-width: 400px) {
        .login-box .login-card-body .row > .col-7,
        .login-box .login-card-body .row > .col-5 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .login-box .login-card-body .row > .col-7 {
            margin-bottom: 0.75rem;
        }

        .login-box .login-card-body .row > .col-5 .btn {
            width: 100%;
        }
    }
</style>
@stop