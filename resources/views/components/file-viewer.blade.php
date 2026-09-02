{{-- Visualizador de archivos inline (modal fullscreen / bottom sheet en móvil) --}}
<div id="file-viewer" class="fixed inset-0 z-[9999] hidden" role="dialog" aria-modal="true" aria-label="Visualizador de archivos">
    {{-- Backdrop --}}
    <div id="fv-backdrop" class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

    {{-- Panel: bottom sheet animado en móvil, fullscreen en desktop --}}
    <div id="fv-panel"
         class="absolute inset-x-0 bottom-0 top-0 sm:inset-0 bg-gray-100 flex flex-col
                overflow-hidden
                translate-y-full transition-transform duration-300 ease-out will-change-transform">

        {{-- Handle de arrastre (solo visible en móvil) --}}
        <div class="sm:hidden flex justify-center pt-2 pb-1 bg-gray-900 shrink-0">
            <div class="w-10 h-1.5 rounded-full bg-gray-500"></div>
        </div>

        {{-- Barra de herramientas --}}
        <div class="flex items-center gap-1 sm:gap-2 px-2 sm:px-4 py-2 bg-gray-900 text-white shrink-0 flex-wrap">
            <span id="fv-title" class="text-xs sm:text-sm font-medium truncate max-w-[28vw] sm:max-w-[30vw]" title=""></span>

            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                {{-- Paginación 1/N --}}
                <div id="fv-pager" class="hidden items-center gap-1">
                    <button id="fv-prev" type="button" title="Página anterior"
                            class="p-1.5 rounded hover:bg-gray-700 disabled:opacity-30 disabled:cursor-not-allowed">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    <span id="fv-page-label" class="text-xs tabular-nums min-w-[3.5rem] text-center">1/1</span>
                    <button id="fv-next" type="button" title="Página siguiente"
                            class="p-1.5 rounded hover:bg-gray-700 disabled:opacity-30 disabled:cursor-not-allowed">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Zoom --}}
                <div id="fv-zoom" class="hidden items-center gap-1">
                    <button id="fv-zoom-out" type="button" title="Alejar"
                            class="p-1.5 rounded hover:bg-gray-700">
                        <i data-lucide="zoom-out" class="w-4 h-4"></i>
                    </button>
                    <button id="fv-zoom-reset" type="button" title="Restablecer zoom"
                            class="text-xs tabular-nums px-1.5 py-1 rounded hover:bg-gray-700 min-w-[3rem] text-center">100%</button>
                    <button id="fv-zoom-in" type="button" title="Acercar"
                            class="p-1.5 rounded hover:bg-gray-700">
                        <i data-lucide="zoom-in" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Descargar --}}
                <a id="fv-download" href="#" title="Descargar"
                   class="p-1.5 rounded hover:bg-gray-700 inline-flex">
                    <i data-lucide="download" class="w-4 h-4"></i>
                </a>

                {{-- Cerrar --}}
                <button id="fv-close" type="button" title="Cerrar (Esc)"
                        class="p-1.5 rounded hover:bg-red-600 bg-gray-700">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        {{-- Área de contenido --}}
        <div id="fv-content" class="flex-1 min-h-0 overflow-auto bg-gray-200 relative">
            {{-- Spinner de carga --}}
            <div id="fv-loading" class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-gray-500">
                <svg class="animate-spin w-8 h-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-sm">Cargando archivo…</span>
            </div>
            <div id="fv-body" class="min-h-full flex items-start justify-center p-2 sm:p-4"></div>
        </div>
    </div>
</div>
