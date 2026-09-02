{{-- Visualizador de archivos inline (modal fullscreen / bottom sheet en móvil) --}}
{{-- El estilo inline es una salvaguarda: si por lo que sea el CSS de Tailwind
     no carga, este div igual debe quedar oculto y fuera del flujo normal de
     la página en vez de mostrarse permanentemente como contenido inline. --}}
<div id="file-viewer" class="fixed inset-0 z-[9999] hidden" style="display:none; position:fixed; inset:0; z-index:9999;" role="dialog" aria-modal="true" aria-label="Visualizador de archivos">
    {{-- Backdrop --}}
    <div id="fv-backdrop" class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

    {{-- Panel: bottom sheet animado en móvil, fullscreen en desktop --}}
    <div id="fv-panel"
         class="absolute inset-x-0 bottom-0 top-0 sm:inset-0 bg-gray-100 flex flex-col
                overflow-hidden
                translate-y-full transition-transform duration-200 ease-out will-change-transform">

        {{-- Barra de herramientas --}}
        <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-5 py-3 sm:py-4 bg-gray-900 text-white shrink-0 flex-wrap">
            <span id="fv-title" class="text-sm sm:text-base font-medium truncate max-w-[60vw] sm:max-w-[70vw]" title=""></span>

            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                {{-- Descargar --}}
                <a id="fv-download" href="#" title="Descargar"
                   class="p-2 rounded hover:bg-gray-700 inline-flex">
                    <i data-lucide="download" class="w-5 h-5"></i>
                </a>

                {{-- Cerrar --}}
                <button id="fv-close" type="button" title="Cerrar (Esc)"
                        class="p-2 rounded hover:bg-red-600 bg-gray-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- Área de contenido --}}
        <div id="fv-content" class="flex-1 min-h-0 overflow-auto bg-gray-200 relative">
            {{-- Spinner de carga --}}
            <div id="fv-loading" class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-gray-500">
                <svg class="animate-spin w-8 h-8 text-blue-600" width="32" height="32" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-sm">Cargando archivo…</span>
            </div>
            <div id="fv-body" class="min-h-full flex flex-col items-center gap-4 p-2 sm:p-4"></div>
        </div>

        {{-- Panel flotante de paginación / zoom (estilo visor nativo, fijo sobre el contenido) --}}
        <div id="fv-vtools" class="hidden flex-col items-center gap-1 absolute bottom-4 right-4 z-10 bg-gray-900/90 backdrop-blur border border-gray-600/80 rounded-xl p-1.5 text-white shadow-lg">
            <div id="fv-pager" class="hidden flex-col items-center gap-1">
                <input id="fv-page-input" type="text" inputmode="numeric" title="Ir a la página"
                       class="w-9 h-8 text-center text-sm bg-gray-800 border border-gray-500 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                <span id="fv-page-total" class="text-xs text-gray-300 tabular-nums">1</span>
                <div class="w-6 border-t border-gray-600 my-1"></div>
                <button id="fv-prev" type="button" title="Página anterior"
                        class="p-1.5 rounded hover:bg-gray-700 disabled:opacity-30 disabled:cursor-not-allowed">
                    <i data-lucide="chevron-up" class="w-4 h-4"></i>
                </button>
                <button id="fv-next" type="button" title="Página siguiente"
                        class="p-1.5 rounded hover:bg-gray-700 disabled:opacity-30 disabled:cursor-not-allowed">
                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                </button>
                <div class="w-6 border-t border-gray-600 my-1"></div>
            </div>
            <button id="fv-zoom-in" type="button" title="Acercar"
                    class="p-1.5 rounded hover:bg-gray-700">
                <i data-lucide="zoom-in" class="w-4 h-4"></i>
            </button>
            <button id="fv-zoom-out" type="button" title="Alejar"
                    class="p-1.5 rounded hover:bg-gray-700">
                <i data-lucide="zoom-out" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>
