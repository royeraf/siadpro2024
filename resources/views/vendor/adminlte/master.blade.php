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
        /* ══════════════════════════════════════════════════════════════════════
           Animación ultra-suave y sin saltos del Sidebar (AdminLTE 3)
           ══════════════════════════════════════════════════════════════════════ */

        /* 1. Dimensiones y Curva de aceleración sincronizada */
        :root {
            --sidebar-width: 275px;
            --sidebar-anim-duration: 0.32s;
            --sidebar-anim-timing: cubic-bezier(0.25, 1, 0.5, 1);
        }

        .main-sidebar,
        .main-sidebar::before {
            width: var(--sidebar-width) !important;
            transition: width var(--sidebar-anim-duration) var(--sidebar-anim-timing),
                        margin-left var(--sidebar-anim-duration) var(--sidebar-anim-timing) !important;
        }

        .layout-fixed .brand-link {
            width: var(--sidebar-width) !important;
        }

        @media (min-width: 768px) {
            body:not(.sidebar-collapse) .content-wrapper,
            body:not(.sidebar-collapse) .main-footer,
            body:not(.sidebar-collapse) .main-header {
                margin-left: var(--sidebar-width) !important;
            }

            .sidebar-mini.sidebar-collapse .content-wrapper,
            .sidebar-mini.sidebar-collapse .main-footer,
            .sidebar-mini.sidebar-collapse .main-header {
                margin-left: 4.6rem !important;
            }
        }

        /* Hover expandido sobre sidebar colapsado */
        .sidebar-mini.sidebar-collapse .main-sidebar:not(.sidebar-no-expand):hover,
        .sidebar-mini.sidebar-collapse .main-sidebar:not(.sidebar-no-expand).sidebar-focused {
            width: var(--sidebar-width) !important;
        }
        .sidebar-mini.sidebar-collapse .main-sidebar:not(.sidebar-no-expand):hover .brand-link,
        .sidebar-mini.sidebar-collapse .main-sidebar:not(.sidebar-no-expand).sidebar-focused .brand-link {
            width: var(--sidebar-width) !important;
        }

        .content-wrapper,
        .main-header,
        .main-footer {
            transition: margin-left var(--sidebar-anim-duration) var(--sidebar-anim-timing) !important;
        }

        .brand-link {
            transition: width var(--sidebar-anim-duration) var(--sidebar-anim-timing),
                        padding var(--sidebar-anim-duration) var(--sidebar-anim-timing) !important;
        }

        /* Garantizar que los modales de Bootstrap se sitúen siempre por encima del backdrop */
        .modal {
            z-index: 1060 !important;
        }
        .modal-dialog {
            z-index: 1061 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }

        /* Anular animaciones durante la carga para evitar parpadeos */
        body.hold-transition .main-sidebar,
        body.hold-transition .content-wrapper,
        body.hold-transition .main-header,
        body.hold-transition .main-footer,
        body.hold-transition .brand-link,
        body.hold-transition .brand-text,
        body.hold-transition .nav-sidebar .nav-link p {
            transition: none !important;
            animation: none !important;
        }

        /* 2. Neutralizar las keyframes nativas de AdminLTE que provocan saltos */
        .sidebar-is-opening .sidebar .nav-sidebar .nav-link p,
        .sidebar-mini.sidebar-collapse .sidebar .nav-sidebar .nav-link p,
        .sidebar-mini.sidebar-collapse .brand-text,
        .sidebar-mini.sidebar-collapse .main-sidebar:not(.sidebar-no-expand):hover .sidebar .nav-sidebar .nav-link p,
        .sidebar-mini.sidebar-collapse .main-sidebar:not(.sidebar-no-expand):hover .brand-text {
            -webkit-animation: none !important;
            animation: none !important;
        }

        /* 3. Estructura de navegación y elementos del menú */
        .main-sidebar {
            overflow-x: hidden !important;
        }

        .main-sidebar .nav-sidebar .nav-item {
            width: 100% !important;
            margin-bottom: 0.2rem;
        }

        .main-sidebar .nav-sidebar .nav-link {
            display: flex !important;
            align-items: center !important;
            padding: 0.55rem 0.8rem !important;
            border-radius: 0.375rem;
            width: 100% !important;
            min-height: 2.35rem;
            transition: background-color 0.2s ease, color 0.2s ease !important;
        }

        /* 4. Anclaje de íconos: posición constante tanto abierto como cerrado */
        .main-sidebar .nav-sidebar .nav-link .nav-icon,
        .main-sidebar .nav-sidebar .nav-link i:not(.right),
        .main-sidebar .nav-sidebar .nav-link .nav-icon-lucide {
            width: 1.5rem !important;
            min-width: 1.5rem !important;
            max-width: 1.5rem !important;
            height: 1.5rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 1.1rem !important;
            margin: 0 !important;
            margin-right: 0.65rem !important;
            flex-shrink: 0 !important;
            text-align: center !important;
            float: none !important;
        }

        .main-sidebar .nav-sidebar .nav-link .nav-icon-lucide {
            width: 1.15rem !important;
            height: 1.15rem !important;
        }

        /* 5. Textos completos: sin recortes, sin puntos suspensivos y con legibilidad total */
        .main-sidebar .nav-sidebar .nav-link p {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 0.35rem;
            flex: 1 1 auto;
            margin: 0 !important;
            padding: 0 !important;
            white-space: normal !important;
            word-break: normal;
            overflow: visible !important;
            text-overflow: unset !important;
            max-width: none !important;
            font-size: 0.84rem !important;
            line-height: 1.25 !important;
            opacity: 1;
            transform: translateX(0);
            transition: opacity 0.22s var(--sidebar-anim-timing),
                        transform 0.22s var(--sidebar-anim-timing) !important;
        }

        .main-sidebar .nav-sidebar .nav-link p .right {
            margin-left: auto !important;
            padding-left: 0.35rem;
            flex-shrink: 0 !important;
            transition: opacity 0.2s ease, transform 0.2s ease !important;
        }

        /* Encabezados de sección del sidebar */
        .main-sidebar .nav-sidebar > .nav-header {
            font-size: 0.75rem !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.6rem 0.8rem 0.25rem !important;
            white-space: normal !important;
        }

        /* Ocultamiento del texto cuando el sidebar está colapsado y no hovered */
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover),
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover),
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) {
            width: 4.6rem !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar .nav-link p,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .nav-sidebar .nav-link p,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .nav-sidebar .nav-link p {
            opacity: 0 !important;
            max-width: 0 !important;
            width: 0 !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            pointer-events: none !important;
            visibility: hidden !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-sidebar > .nav-header,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .nav-sidebar > .nav-header,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .nav-sidebar > .nav-header {
            display: none !important;
        }

        /* 6. Cabecera y Logo (Brand Link) */
        .main-sidebar .brand-link {
            display: flex !important;
            align-items: center !important;
            height: calc(3.5rem + 1px) !important;
            padding: 0.8125rem 1.3rem !important;
            overflow: hidden !important;
            white-space: nowrap !important;
        }

        .main-sidebar .brand-link .brand-image {
            float: none !important;
            width: 2rem !important;
            height: 2rem !important;
            min-width: 2rem !important;
            max-height: 2rem !important;
            object-fit: contain;
            margin: 0 !important;
            margin-right: 0.75rem !important;
            flex-shrink: 0 !important;
            display: block !important;
            transition: margin var(--sidebar-anim-duration) var(--sidebar-anim-timing) !important;
        }

        .main-sidebar .brand-link .brand-text {
            display: inline-block !important;
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: unset !important;
            max-width: none !important;
            opacity: 1;
            transform: translateX(0);
            font-size: 1.1rem !important;
            transition: opacity 0.22s var(--sidebar-anim-timing),
                        transform 0.22s var(--sidebar-anim-timing) !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .brand-link,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .brand-link,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .brand-link {
            width: 4.6rem !important;
            padding-left: 1.3rem !important;
            padding-right: 1.3rem !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .brand-link .brand-text,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .brand-link .brand-text,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .brand-link .brand-text {
            opacity: 0 !important;
            transform: translateX(-10px) !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* 7. Submenús, formularios y scrollbars */
        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .nav-treeview,
        body.sidebar-collapse.sidebar-mini-md .main-sidebar:not(:hover) .nav-treeview,
        body.sidebar-collapse.sidebar-mini-xs .main-sidebar:not(:hover) .nav-treeview {
            display: none !important;
            opacity: 0 !important;
        }

        body.sidebar-collapse.sidebar-mini .main-sidebar:not(:hover) .form-control-sidebar {
            display: none !important;
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

        /* Tono más suave para el item seleccionado del sidebar */
        [class*="sidebar-dark-"] .nav-sidebar > .nav-item > .nav-link.active {
            background-color: #4c4a6e !important;
        }
    </style>

    {{-- Favicon --}}
    @php($favVersion = '?v=' . (@filemtime(public_path('favicons/favicon.ico')) ?: '2'))
    @if(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}{{ $favVersion }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}{{ $favVersion }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}{{ $favVersion }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}{{ $favVersion }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}{{ $favVersion }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}{{ $favVersion }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}{{ $favVersion }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}{{ $favVersion }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}{{ $favVersion }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}{{ $favVersion }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}{{ $favVersion }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}{{ $favVersion }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}{{ $favVersion }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}{{ $favVersion }}">
        <link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('favicons/android-icon-192x192.png') }}{{ $favVersion }}">
        <link rel="manifest" href="{{ asset('favicons/manifest.json') }}{{ $favVersion }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicons/ms-icon-144x144.png') }}{{ $favVersion }}">
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

    {{-- DataTables: realinear encabezados (scrollX) cuando la tabla se inicializa
         antes de que el iframe/contenedor tenga su ancho final --}}
    <script>
        (function () {
            function adjustDataTables() {
                if (window.jQuery && jQuery.fn.dataTable) {
                    jQuery.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                }
            }
            window.addEventListener('load', adjustDataTables);
            window.addEventListener('resize', adjustDataTables);
            document.addEventListener('visibilitychange', adjustDataTables);
            // Reintentos iniciales por si la tabla se inicializó con el contenedor aún oculto
            var retries = 0;
            var timer = setInterval(function () {
                adjustDataTables();
                if (++retries >= 5) clearInterval(timer);
            }, 800);
        })();
    </script>

</body>

</html>
