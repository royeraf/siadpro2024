@props(['icon', 'color' => null])

@php
    // Clases escritas en literal a propósito (ver components/section-tabs.blade.php):
    // el escáner JIT de Tailwind no detecta clases interpoladas en tiempo de ejecución.
    $colorClasses = [
        'red'    => 'text-red-600',
        'blue'   => 'text-blue-600',
        'yellow' => 'text-yellow-600',
        'orange' => 'text-orange-600',
        'cyan'   => 'text-cyan-600',
        'green'  => 'text-green-600',
        'pink'   => 'text-pink-600',
        'white'  => 'text-gray-600',
    ];
    $textColor = $colorClasses[$color] ?? 'text-dark';
@endphp

<h1 class="m-0 flex items-center justify-center sm:justify-start gap-2 text-lg sm:text-2xl w-full sm:w-auto {{ $textColor }}">
    <i data-lucide="{{ $icon }}" class="w-5 h-5 sm:w-6 sm:h-6 shrink-0"></i>
    <span>{{ $slot }}</span>
</h1>
