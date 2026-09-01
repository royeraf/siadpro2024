@if ($paginator->hasPages())
    <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3">
        <span class="text-xs font-medium text-gray-600 sm:hidden">
            Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
        </span>

        <nav class="inline-flex items-center gap-1.5" role="navigation" aria-label="Paginación">
            {{-- Primera página (solo móvil) --}}
            @if ($paginator->onFirstPage())
                <span class="sm:hidden w-9 h-9 inline-flex items-center justify-center rounded-full border border-gray-200 text-gray-300 shadow-none cursor-not-allowed">
                    <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                </span>
            @else
                <a href="{{ $paginator->url(1) }}" title="Primera página"
                   class="sm:hidden w-9 h-9 inline-flex items-center justify-center rounded-full border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:bg-gray-100 hover:text-blue-700 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                </a>
            @endif

            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="w-9 h-9 inline-flex items-center justify-center rounded-full border border-gray-200 text-gray-300 shadow-none cursor-not-allowed">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" title="Anterior"
                   class="w-9 h-9 inline-flex items-center justify-center rounded-full border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:bg-gray-100 hover:text-blue-700 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </a>
            @endif

            {{-- Números de página (solo desde sm hacia arriba) --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="hidden sm:inline-flex w-9 h-9 items-center justify-center text-xs font-medium text-gray-400 select-none">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="hidden sm:inline-flex w-9 h-9 items-center justify-center rounded-full text-xs font-semibold bg-gradient-to-br from-blue-600 to-blue-700 text-white shadow-md shadow-blue-600/30 ring-2 ring-blue-300 ring-offset-1">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               class="hidden sm:inline-flex w-9 h-9 items-center justify-center rounded-full text-xs font-medium border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" title="Siguiente"
                   class="w-9 h-9 inline-flex items-center justify-center rounded-full border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:bg-gray-100 hover:text-blue-700 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            @else
                <span class="w-9 h-9 inline-flex items-center justify-center rounded-full border border-gray-200 text-gray-300 shadow-none cursor-not-allowed">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </span>
            @endif

            {{-- Última página (solo móvil) --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->url($paginator->lastPage()) }}" title="Última página"
                   class="sm:hidden w-9 h-9 inline-flex items-center justify-center rounded-full border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:bg-gray-100 hover:text-blue-700 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                </a>
            @else
                <span class="sm:hidden w-9 h-9 inline-flex items-center justify-center rounded-full border border-gray-200 text-gray-300 shadow-none cursor-not-allowed">
                    <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                </span>
            @endif
        </nav>
    </div>
@endif
