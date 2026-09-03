@props([
    'icon',
    'title',
    'href',
    'color' => null,
    'description' => null,
])

@php
    // Clases escritas en literal a propósito (ver components/section-heading.blade.php
    // y components/section-tabs.blade.php): el escáner JIT de Tailwind no detecta
    // clases interpoladas en tiempo de ejecución. Mismo color sólido que usa la
    // pestaña activa en components/section-tabs.blade.php para que la tarjeta se
    // vea como el mismo color de sección en el sidebar y en las cabeceras.
    $colorClasses = [
        'red'    => 'bg-red-600 hover:bg-red-700',
        'blue'   => 'bg-blue-600 hover:bg-blue-700',
        'yellow' => 'bg-yellow-600 hover:bg-yellow-700',
        'orange' => 'bg-orange-600 hover:bg-orange-700',
        'cyan'   => 'bg-cyan-600 hover:bg-cyan-700',
        'green'  => 'bg-green-600 hover:bg-green-700',
        'pink'   => 'bg-pink-600 hover:bg-pink-700',
        // 'white' no es legible como fondo sólido; se resuelve a gris, igual
        // que en components/section-tabs.blade.php.
        'white'  => 'bg-gray-600 hover:bg-gray-700',
    ];
    $bg = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<a href="{{ $href }}"
   class="flex aspect-square w-full flex-col items-center justify-center gap-2 rounded-lg p-3 text-center
          text-decoration-none shadow-sm transition hover:shadow-md {{ $bg }}
          focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400">
    <i data-lucide="{{ $icon }}" class="w-8 h-8 sm:w-10 sm:h-10 shrink-0 text-white"></i>
    <span class="min-w-0">
        <span class="block font-semibold text-xs sm:text-sm text-white leading-tight">{{ $title }}</span>
        @if($description)
            <span class="hidden sm:block text-xs text-white/80 mt-1 leading-snug">{{ $description }}</span>
        @endif
    </span>
</a>
