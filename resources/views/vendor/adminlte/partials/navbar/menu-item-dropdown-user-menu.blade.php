@php
    $logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout');

    if (config('adminlte.use_route_url', false)) {
        $logout_url = $logout_url ? route($logout_url) : '';
    } else {
        $logout_url = $logout_url ? url($logout_url) : '';
    }

    $user = Auth::user();
    // Iniciales para el badge (max. 2 letras, de las dos primeras palabras)
    $parts = preg_split('/\s+/', trim($user->name ?? ''));
    $initials = strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
    $role = $user->roles->pluck('name')->first();
@endphp

<li class="nav-item dropdown user-menu">

    {{-- Botón del menú: badge circular con iniciales --}}
    <a href="#" class="nav-link dropdown-toggle flex items-center gap-2 !py-1" data-toggle="dropdown">
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-600 text-white font-bold text-sm tracking-wide shadow-md ring-2 ring-white/30">
            {{ $initials }}
        </span>
    </a>

    {{-- Menú desplegable --}}
    <ul class="dropdown-menu dropdown-menu-right w-80 !p-0 rounded-xl overflow-hidden shadow-2xl border-0">

        {{-- Cabecera --}}
        <li class="bg-gradient-to-br from-blue-600 to-blue-700 text-white text-center px-4 py-5 list-none">
            <span class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white text-blue-600 font-bold text-2xl shadow-md mb-2">
                {{ $initials }}
            </span>
            <p class="m-0 font-semibold leading-tight">{{ $user->name }}</p>
            <p class="m-0 text-xs text-blue-100">{{ $user->email }}</p>
            @if($role)
                <span class="inline-block mt-2 px-3 py-0.5 rounded-full bg-white/20 text-white text-xs font-medium">
                    {{ $role }}
                </span>
            @endif
        </li>

        {{-- Datos del usuario --}}
        <li class="bg-white list-none px-5 py-3">
            <p class="text-center font-semibold text-gray-700 text-sm mb-2">Datos del usuario</p>
            <dl class="text-sm space-y-0">

                @if($user->dni)
                <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                    <dt class="text-gray-500">DNI</dt>
                    <dd class="m-0 font-medium text-gray-800 text-right">{{ $user->dni }}</dd>
                </div>
                @endif

                @if($user->cargo)
                <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                    <dt class="text-gray-500">Cargo</dt>
                    <dd class="m-0 font-medium text-gray-800 text-right">{{ $user->cargo }}</dd>
                </div>
                @endif

                @if($user->institucion)
                <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                    <dt class="text-gray-500">Institución</dt>
                    <dd class="m-0 font-medium text-gray-800 text-right">{{ $user->institucion }}</dd>
                </div>
                @endif

                @if($user->provincia || $user->distrito)
                <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                    <dt class="text-gray-500">Ubicación</dt>
                    <dd class="m-0 font-medium text-gray-800 text-right">{{ $user->provincia }}@if($user->provincia && $user->distrito) - @endif{{ $user->distrito }}</dd>
                </div>
                @endif

                <div class="flex justify-between gap-4 py-2">
                    <dt class="text-gray-500">Miembro desde</dt>
                    <dd class="m-0 font-medium text-gray-800 text-right">{{ optional($user->created_at)->format('d/m/Y') }}</dd>
                </div>

            </dl>
        </li>

        {{-- Pie: acciones --}}
        <li class="bg-gray-50 list-none flex px-4 py-3 border-t border-gray-100">
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition no-underline">
                <i class="fa fa-fw fa-power-off"></i> Cerrar sesión
            </a>
            <form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">
                @if(config('adminlte.logout_method'))
                    {{ method_field(config('adminlte.logout_method')) }}
                @endif
                {{ csrf_field() }}
            </form>
        </li>

    </ul>

</li>
