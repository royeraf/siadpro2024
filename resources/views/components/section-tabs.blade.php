@props(['tabs' => [], 'color' => 'blue'])

@php
    // Clases escritas en literal (no interpoladas) a propósito: el escáner JIT
    // de Tailwind solo detecta clases que aparecen como texto en el archivo.
    $palette = [
        'red'    => ['active' => 'bg-red-600 border-red-600 text-white',    'inactive' => 'bg-red-50 text-red-700 hover:bg-red-100'],
        'blue'   => ['active' => 'bg-blue-600 border-blue-600 text-white',   'inactive' => 'bg-blue-50 text-blue-700 hover:bg-blue-100'],
        'yellow' => ['active' => 'bg-yellow-600 border-yellow-600 text-white', 'inactive' => 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100'],
        'orange' => ['active' => 'bg-orange-600 border-orange-600 text-white', 'inactive' => 'bg-orange-50 text-orange-700 hover:bg-orange-100'],
        'cyan'   => ['active' => 'bg-cyan-600 border-cyan-600 text-white',   'inactive' => 'bg-cyan-50 text-cyan-700 hover:bg-cyan-100'],
        'green'  => ['active' => 'bg-green-600 border-green-600 text-white', 'inactive' => 'bg-green-50 text-green-700 hover:bg-green-100'],
        'pink'   => ['active' => 'bg-pink-600 border-pink-600 text-white',   'inactive' => 'bg-pink-50 text-pink-700 hover:bg-pink-100'],
        // 'white' no es legible como fondo de pestaña activa (texto blanco sobre
        // blanco); se resuelve a gris. El icono del sidebar del módulo se queda
        // blanco tal cual, esto solo afecta a esta barra de pestañas.
        'white'  => ['active' => 'bg-gray-600 border-gray-600 text-white',  'inactive' => 'bg-gray-100 text-gray-700 hover:bg-gray-200'],
    ];
    $scheme = $palette[$color] ?? $palette['blue'];
@endphp

@if(count($tabs) > 1)
<ul class="flex gap-1 border-b border-gray-300 mb-4 -mt-2" role="tablist">
    @foreach ($tabs as $tab)
        <li role="presentation" class="flex-1 sm:flex-initial min-w-0 -mb-px flex">
            <a href="{{ $tab['url'] }}" role="tab" aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
               class="flex-1 flex items-center justify-center text-center px-2 sm:px-4 py-2 text-sm rounded-t-md border border-transparent text-decoration-none transition {{ $tab['active'] ? $scheme['active'] : $scheme['inactive'] }}">
                {{ $tab['label'] }}
            </a>
        </li>
    @endforeach
</ul>
@endif
