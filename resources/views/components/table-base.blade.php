@props([
    'id' => 'custom-table-' . uniqid(),
    'perPage' => 15,
    'exportable' => true,
    'searchable' => true,
    'exportFilename' => 'reporte',
    'exportUrl' => null,
    'emptyMessage' => 'No se encontraron registros.',
    'serverPaginated' => false,
    'totalServerRecords' => null,
    'fromServer' => null,
    'toServer' => null,
    'filterAction' => null,
    'filterParams' => [],
    'searchName' => 'buscar',
    'stacked' => true,
])

<div x-data="TableEngine('{{ $id }}', {
        perPage: {{ is_numeric($perPage) ? $perPage : "'$perPage'" }},
        serverPaginated: {{ $serverPaginated ? 'true' : 'false' }},
        totalServerRecords: {{ $totalServerRecords !== null ? (int) $totalServerRecords : 'null' }},
        fromServer: {{ $fromServer !== null ? (int) $fromServer : 'null' }},
        toServer: {{ $toServer !== null ? (int) $toServer : 'null' }}
    })" class="w-full bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden my-3">

    {{-- Barra de Filtros y Controles --}}
    <form method="GET" action="{{ $filterAction ?? '#' }}" class="p-4 bg-gray-50 border-b border-gray-200 space-y-3" @if(!$filterAction) onsubmit="return false;" @endif>
        @foreach ($filterParams as $paramName => $paramValue)
            <input type="hidden" name="{{ $paramName }}" value="{{ $paramValue }}">
        @endforeach

        @if(isset($filters))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                {{ $filters }}
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            {{-- Buscador --}}
            @if($searchable)
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </div>
                @if($serverPaginated)
                    <div x-data="{ q: @js((string) request($searchName)), t: null }"
                         x-init="$watch('q', () => { clearTimeout(t); t = setTimeout(() => liveSearch(), 400); })">
                        <input type="text" name="{{ $searchName }}" x-model="q" placeholder="Buscar en todos los registros..."
                               class="w-full pl-9 pr-8 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               @keydown.enter.prevent="clearTimeout(t); liveSearch()">
                        <button type="button" x-show="q.length > 0" @click="q = ''; clearTimeout(t); $nextTick(() => liveSearch())" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                        <div x-show="srvLoading" class="absolute inset-y-0 right-8 flex items-center pointer-events-none">
                            <svg class="animate-spin h-3.5 w-3.5 text-blue-500" width="14" height="14" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </div>
                    </div>
                @else
                    <input type="text" x-model="search" @input="onSearchChange()" placeholder="Buscar en tabla..."
                           class="w-full pl-9 pr-8 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" x-show="search.length > 0" @click="search = ''; onSearchChange()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                @endif
            </div>
            @endif

            {{-- Acciones: Buscar / Limpiar / Exportar --}}
            <div class="flex items-center gap-2 w-full sm:w-auto">
                @if($filterAction)
                    <button type="submit" class="flex-1 sm:flex-none inline-flex items-center justify-center px-3.5 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold rounded-md shadow-sm transition border-0 cursor-pointer">
                        <i data-lucide="search" class="w-4 h-4 mr-1.5"></i> Buscar
                    </button>
                    <a href="{{ $filterAction }}{{ count($filterParams) ? '?' . http_build_query($filterParams) : '' }}" class="flex-1 sm:flex-none inline-flex items-center justify-center px-3.5 py-2 bg-gray-500 hover:bg-gray-600 active:bg-gray-700 text-white text-xs font-semibold rounded-md shadow-sm transition text-decoration-none">
                        <i data-lucide="eraser" class="w-4 h-4 mr-1.5"></i> Limpiar
                    </a>
                @endif
                @if($exportable)
                    @if($exportUrl)
                        <a href="{{ $exportUrl }}" class="tb-export-link flex-1 sm:flex-none inline-flex items-center justify-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-md shadow-sm transition border-0 text-decoration-none cursor-pointer">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-1.5"></i> Excel
                        </a>
                    @else
                        <button type="button" @click="exportExcel('{{ $exportFilename }}')" class="flex-1 sm:flex-none inline-flex items-center justify-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-md shadow-sm transition border-0 cursor-pointer">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-1.5"></i> Excel
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </form>

    {{-- Contenedor de la Tabla --}}
    <div class="overflow-x-auto transition-opacity" :class="{ 'opacity-50': srvLoading }">
        <table id="{{ $id }}" class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-700 {{ $stacked ? 'table-stacked' : '' }}">
            <thead class="bg-blue-600 text-white select-none">
                {{ $header }}
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                {{ $slot }}
                <tr class="table-empty-row" style="display: none;">
                    <td colspan="100" class="px-6 py-8 text-center text-gray-500 font-medium">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="inbox" class="w-8 h-8 mb-2 text-gray-400"></i>
                            <span>{{ $emptyMessage }}</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pie de Tabla: Contador, Selector de Registros y Paginación --}}
    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-gray-600">
        
        {{-- Izquierda: Resumen y Selector de Registros --}}
        <div class="flex flex-wrap items-center gap-4">
            {{-- Selector de Registros por Página --}}
            <div class="flex items-center space-x-2 text-sm text-gray-700">
                <span>Mostrar</span>
                <select x-model="perPage" @change="onPerPageChange($event)" class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-1 px-2.5 pr-8 bg-white">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>registros</span>
            </div>

            <div class="text-gray-500 text-xs sm:text-sm">
                @if($serverPaginated && $totalServerRecords !== null)
                    Mostrando <span class="font-semibold text-gray-900" x-text="srvFrom">{{ $fromServer ?? 0 }}</span> a
                    <span class="font-semibold text-gray-900" x-text="srvTo">{{ $toServer ?? 0 }}</span> de
                    <span class="font-semibold text-gray-900" x-text="srvTotalFormatted">{{ number_format($totalServerRecords) }}</span> registros
                @else
                    Mostrando <span class="font-semibold text-gray-900" x-text="fromIndex"></span> a 
                    <span class="font-semibold text-gray-900" x-text="toIndex"></span> de 
                    <span class="font-semibold text-gray-900" x-text="filteredRowsCount"></span> registros
                    <template x-if="filteredRowsCount !== totalRows">
                        <span class="text-xs text-gray-500">(filtrados de <span x-text="totalRows"></span>)</span>
                    </template>
                @endif
            </div>
        </div>

        {{-- Derecha: Controles de Paginador (Solo cuando es paginación cliente, en serverPaginated se usa el paginador de Laravel debajo) --}}
        @if(!$serverPaginated)
        <div class="inline-flex items-center gap-1.5" x-show="totalPages > 1">
            <button type="button" @click="setPage(currentPage - 1)" :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed text-gray-300 shadow-none' : 'text-gray-600 hover:bg-gray-100 hover:text-blue-700 hover:border-blue-300 shadow-sm'"
                    class="w-9 h-9 inline-flex items-center justify-center rounded-full border border-gray-300 bg-white transition focus:outline-none focus:ring-2 focus:ring-blue-300"
                    title="Anterior">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>

            <template x-for="p in pagesArray" :key="p">
                <button type="button" @click="setPage(p)"
                        :class="p === currentPage ? 'bg-gradient-to-br from-blue-600 to-blue-700 text-white font-semibold shadow-md shadow-blue-600/30 ring-2 ring-blue-300 ring-offset-1' : (p === '...' ? 'cursor-default text-gray-400 border-transparent bg-transparent shadow-none' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 border-gray-200 bg-white shadow-sm')"
                        class="hidden sm:inline-flex w-9 h-9 items-center justify-center rounded-full text-xs font-medium border transition focus:outline-none focus:ring-2 focus:ring-blue-300"
                        x-text="p">
                </button>
            </template>

            <span class="inline sm:hidden text-xs font-medium text-gray-600 px-1" x-text="'Página ' + currentPage + ' de ' + totalPages"></span>

            <button type="button" @click="setPage(currentPage + 1)" :disabled="currentPage === totalPages"
                    :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed text-gray-300 shadow-none' : 'text-gray-600 hover:bg-gray-100 hover:text-blue-700 hover:border-blue-300 shadow-sm'"
                    class="w-9 h-9 inline-flex items-center justify-center rounded-full border border-gray-300 bg-white transition focus:outline-none focus:ring-2 focus:ring-blue-300"
                    title="Siguiente">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>
        @endif
    </div>
</div>
