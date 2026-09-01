@props([
    'href',
    'icon' => 'circle-plus',
])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-1.5 max-sm:w-full
        px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800
        text-white hover:text-white text-sm font-semibold rounded-md shadow-sm transition
        border-0 text-decoration-none focus:outline-none focus:ring-2 focus:ring-emerald-300']) }}>
    <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
    <span>{{ $slot }}</span>
</a>
