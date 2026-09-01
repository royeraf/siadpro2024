<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets --}}
    @if(!config('adminlte.enabled_laravel_mix'))
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/favicon.png') }}">
        <link rel="shortcut icon" type="image/icon" href="{{asset('favicons/favicon.png')}}">

        {{-- Configured Stylesheets --}}
        @include('adminlte::plugins', ['type' => 'css'])

        <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    @else
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @endif

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(app()->version() >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    <style>
        /* Ajustes para Sidebar Contraído (sidebar-mini / sidebar-collapse) */
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover),
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover),
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) {
            width: 4.6rem !important;
            overflow-x: hidden !important;
        }

        /* 1. Centrado absoluto del logo en el encabezado del sidebar colapsado */
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .brand-link,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .brand-link,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .brand-link {
            width: 4.6rem !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            padding: 0.8rem 0 !important;
            margin: 0 !important;
            text-align: center !important;
            overflow: hidden !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .brand-link .brand-image,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .brand-link .brand-image,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .brand-link .brand-image {
            float: none !important;
            margin: 0 auto !important;
            max-height: 33px !important;
            max-width: 33px !important;
            width: 33px !important;
            height: 33px !important;
            object-fit: contain;
            display: block !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .brand-link .brand-text,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .brand-link .brand-text,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .brand-link .brand-text {
            display: none !important;
            width: 0 !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }

        /* 2. Centrado de íconos en los ítems de navegación */
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-item,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .nav-sidebar .nav-item,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .nav-sidebar .nav-item {
            width: 100% !important;
            text-align: center !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link {
            width: 3.6rem !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            padding: 0.6rem 0 !important;
            margin: 0 auto 0.25rem auto !important;
            text-align: center !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .nav-icon,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link i,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .nav-icon,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link i,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .nav-icon,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link i {
            float: none !important;
            margin: 0 auto !important;
            text-align: center !important;
            font-size: 1.15rem !important;
            width: 100% !important;
            display: block !important;
        }

        /* Íconos Lucide (SVG) del sidebar: font-size no afecta a un <svg>, por eso necesitan su propia regla.
           display:inline-block pisa el Preflight de Tailwind (svg{display:block}) que en las vistas migradas
           forzaba el ícono a su propia línea y empujaba el título del ítem debajo. */
        .main-sidebar .nav-sidebar .nav-link .nav-icon-lucide {
            display: inline-block;
            vertical-align: middle;
            width: 1.1rem;
            height: 1.1rem;
            margin-right: .5rem;
            flex-shrink: 0;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .nav-icon-lucide,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .nav-icon-lucide,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .nav-icon-lucide {
            margin: 0 auto !important;
            width: 1.15rem !important;
            height: 1.15rem !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link p,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .badge,
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .right,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link p,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .badge,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .right,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link p,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .badge,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .nav-sidebar .nav-item > .nav-link .right {
            display: none !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .form-control-sidebar,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .form-control-sidebar,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .form-control-sidebar {
            display: none !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .input-group-append .btn-sidebar,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .input-group-append .btn-sidebar,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .input-group-append .btn-sidebar {
            width: 100% !important;
            margin: 0 auto !important;
            text-align: center !important;
        }

        /* Scrollbar elegante y adaptativo a la altura de la pantalla para el sidebar */
        .main-sidebar .sidebar {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        .main-sidebar .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .main-sidebar .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .main-sidebar .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .main-sidebar .sidebar:hover::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.4);
        }
    </style>

    {{-- Favicon --}}
    @if(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    @endif

</head>

<body class="@yield('classes_body')" @yield('body_data')>

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts --}}
    @if(!config('adminlte.enabled_laravel_mix'))
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>

        {{-- Configured Scripts --}}
        @include('adminlte::plugins', ['type' => 'js'])

        <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @else
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @endif

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(app()->version() >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- App script global: hidrata íconos Lucide (incl. los del sidebar) y Alpine.js en toda página --}}
    @vite(['resources/js/app.js'])

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

</body>

</html>
