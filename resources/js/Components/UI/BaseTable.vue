<script setup>
import { ref, watch, computed } from 'vue';
import { 
    Search, X, Eraser, FileSpreadsheet, Inbox, 
    ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight,
    ArrowUpNarrowWide, ArrowDownWideNarrow 
} from 'lucide-vue-next';

const props = defineProps({
    id: {
        type: String,
        default: () => 'table-' + Math.random().toString(36).substring(2, 9),
    },
    columns: {
        type: Array,
        default: () => [],
    },
    rows: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        default: null,
    },
    searchable: {
        type: Boolean,
        default: true,
    },
    searchPlaceholder: {
        type: String,
        default: 'Buscar en todos los registros...',
    },
    searchValue: {
        type: String,
        default: '',
    },
    exportable: {
        type: Boolean,
        default: true,
    },
    exportUrl: {
        type: String,
        default: null,
    },
    exportFilename: {
        type: String,
        default: 'reporte',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    emptyMessage: {
        type: String,
        default: 'No se encontraron registros.',
    },
    showIndex: {
        type: Boolean,
        default: true,
    },
    headerBg: {
        type: String,
        default: 'bg-slate-100 text-slate-700 border-b border-slate-200',
    },
    hoverBg: {
        type: String,
        default: 'hover:bg-blue-100/70',
    },
    stacked: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['search', 'clear', 'sort', 'pageChange', 'perPageChange', 'export']);

const search = ref(props.searchValue);
const currentSortCol = ref(null);
const currentSortAsc = ref(true);
let debounceTimer = null;

watch(() => props.searchValue, (newVal) => {
    search.value = newVal;
});

function handleSearchInput() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        emit('search', search.value);
    }, 400);
}

function submitSearch() {
    clearTimeout(debounceTimer);
    emit('search', search.value);
}

function clearSearch() {
    search.value = '';
    clearTimeout(debounceTimer);
    emit('search', '');
}

function handleClearAll() {
    search.value = '';
    clearTimeout(debounceTimer);
    emit('clear');
}

function handleSort(col) {
    if (!col.sortable) return;
    if (currentSortCol.value === col.key) {
        currentSortAsc.value = !currentSortAsc.value;
    } else {
        currentSortCol.value = col.key;
        currentSortAsc.value = true;
    }
    emit('sort', {
        key: currentSortCol.value,
        order: currentSortAsc.value ? 'asc' : 'desc',
    });
}

function changePerPage(e) {
    emit('perPageChange', parseInt(e.target.value, 10));
}

function changePage(page) {
    if (page >= 1 && page <= totalPages.value) {
        emit('pageChange', page);
    }
}

function getRowIndex(idx) {
    const base = props.pagination?.from;
    if (typeof base === 'number' && base > 0) {
        return base + idx;
    }
    return idx + 1;
}

// Cálculo de paginación inteligente
const currentPage = computed(() => props.pagination?.current_page || 1);
const totalPages = computed(() => props.pagination?.last_page || 1);
const totalRecords = computed(() => props.pagination?.total ?? props.rows.length);
const fromRecord = computed(() => props.pagination?.from ?? (props.rows.length ? 1 : 0));
const toRecord = computed(() => props.pagination?.to ?? props.rows.length);
const perPage = computed(() => props.pagination?.per_page || 10);

const visiblePages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const delta = 2;
    const range = [];

    for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
        range.push(i);
    }

    if (current - delta > 2) {
        range.unshift('...');
    }
    if (current + delta < total - 1) {
        range.push('...');
    }

    range.unshift(1);
    if (total > 1) {
        range.push(total);
    }

    return range;
});
</script>

<template>
    <div :id="id" class="w-full bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden my-4">
        <!-- ══ SECCIÓN SUPERIOR: FILTROS Y CONTROLES ══ -->
        <div class="p-4 bg-slate-50 border-b border-slate-200 space-y-3">
            <!-- Filtros específicos (slot personalizado) -->
            <div v-if="$slots.filters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <slot name="filters" />
            </div>

            <!-- Fila de búsqueda y botones de acción -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                <!-- Buscador universal -->
                <div v-if="searchable" class="relative w-full sm:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <Search class="w-4 h-4" />
                    </div>
                    <input 
                        type="text" 
                        v-model="search"
                        @input="handleSearchInput"
                        @keydown.enter.prevent="submitSearch"
                        :placeholder="searchPlaceholder"
                        class="w-full pl-9 pr-9 py-2 text-xs sm:text-sm bg-white border border-slate-300 rounded-xl shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-slate-800 placeholder-slate-400"
                    />
                    <!-- Botón limpiar búsqueda -->
                    <button 
                        v-if="search.length > 0" 
                        type="button" 
                        @click="clearSearch"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                        title="Borrar búsqueda"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <!-- Spinner de carga -->
                    <div v-if="loading" class="absolute inset-y-0 right-8 flex items-center pointer-events-none">
                        <i class="fas fa-spinner fa-spin text-blue-500 text-xs"></i>
                    </div>
                </div>

                <!-- Botones de Acción: Buscar, Limpiar, Excel -->
                <div class="flex items-center flex-wrap gap-2 w-full sm:w-auto">
                    <button 
                        type="button" 
                        @click="submitSearch"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-xs transition-colors border-0 cursor-pointer"
                    >
                        <Search class="w-3.5 h-3.5 mr-1.5" />
                        Buscar
                    </button>

                    <button 
                        type="button" 
                        @click="handleClearAll"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-slate-500 hover:bg-slate-600 active:bg-slate-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors border-0 cursor-pointer"
                    >
                        <Eraser class="w-3.5 h-3.5 mr-1.5" />
                        Limpiar
                    </button>

                    <!-- Botón de Exportar Excel -->
                    <template v-if="exportable">
                        <a 
                            v-if="exportUrl"
                            :href="exportUrl"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition-colors border-0 text-decoration-none cursor-pointer"
                        >
                            <FileSpreadsheet class="w-3.5 h-3.5 mr-1.5" />
                            Excel
                        </a>
                        <button 
                            v-else
                            type="button"
                            @click="emit('export')"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition-colors border-0 cursor-pointer"
                        >
                            <FileSpreadsheet class="w-3.5 h-3.5 mr-1.5" />
                            Excel
                        </button>
                    </template>

                    <!-- Slot para acciones extra -->
                    <slot name="extra-actions" />
                </div>
            </div>
        </div>

        <!-- ══ TABLA RESPONSIVA (ESTILO BOOTSTRAP) ══ -->
        <div class="table-responsive w-full overflow-x-auto transition-opacity" :class="{ 'opacity-50 pointer-events-none': loading }">
            <table class="w-full min-w-full text-left align-middle border-collapse divide-y divide-slate-200 text-xs sm:text-sm text-slate-700" :class="{ 'table-stacked': stacked }">
                <!-- Cabecera de la tabla -->
                <thead :class="headerBg" class="select-none">
                    <slot name="header">
                        <tr>
                            <!-- Columna Índice (#) -->
                            <th 
                                v-if="showIndex" 
                                class="px-3.5 py-3 font-bold text-center w-12 whitespace-nowrap align-middle"
                            >
                                #
                            </th>
                            <th 
                                v-for="col in columns" 
                                :key="col.key"
                                class="px-4 py-3 font-bold transition-colors whitespace-nowrap align-middle"
                                :class="[
                                    col.sortable ? 'cursor-pointer hover:bg-slate-200/75' : '',
                                    col.class || '',
                                    col.align === 'center' ? 'text-center' : col.align === 'right' ? 'text-right' : 'text-left'
                                ]"
                                :style="col.width ? { width: col.width } : {}"
                                @click="handleSort(col)"
                            >
                                <div class="flex items-center justify-between gap-1.5">
                                    <span>{{ col.label }}</span>
                                    <span v-if="col.sortable" class="flex items-center shrink-0">
                                        <ArrowUpNarrowWide v-if="currentSortCol === col.key && currentSortAsc" class="w-3.5 h-3.5" />
                                        <ArrowDownWideNarrow v-else-if="currentSortCol === col.key && !currentSortAsc" class="w-3.5 h-3.5" />
                                    </span>
                                </div>
                            </th>
                        </tr>
                    </slot>
                </thead>

                <!-- Cuerpo de la tabla -->
                <tbody class="divide-y divide-slate-100 bg-white">
                    <slot name="body">
                        <template v-if="rows && rows.length > 0">
                            <tr 
                                v-for="(row, idx) in rows" 
                                :key="row.id || idx" 
                                class="transition-colors border-b border-slate-100 last:border-b-0 cursor-default"
                                :class="hoverBg"
                                data-table-row
                            >
                                <!-- Celda Índice (#) -->
                                <td 
                                    v-if="showIndex" 
                                    class="px-3.5 py-3 text-center text-slate-500 font-semibold align-middle whitespace-nowrap w-12"
                                >
                                    {{ getRowIndex(idx) }}
                                </td>
                                <slot name="row" :row="row" :index="idx" :row-index="getRowIndex(idx)" />
                            </tr>
                        </template>

                        <!-- Estado vacío -->
                        <tr v-else>
                            <td :colspan="(columns.length + (showIndex ? 1 : 0)) || 12" class="px-6 py-12 text-center text-slate-500 font-medium">
                                <div class="flex flex-col items-center justify-center">
                                    <Inbox class="w-10 h-10 mb-2 text-slate-300" />
                                    <span class="text-sm">{{ emptyMessage }}</span>
                                </div>
                            </td>
                        </tr>
                    </slot>
                </tbody>
            </table>
        </div>

        <!-- ══ PIE DE TABLA: CONTADOR Y PAGINACIÓN ══ -->
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4 text-xs sm:text-sm text-slate-600">
            <!-- Izquierda: Selector por página y contador de registros -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-2 text-slate-700">
                    <span>Mostrar</span>
                    <select 
                        :value="perPage" 
                        @change="changePerPage"
                        class="text-xs bg-white border border-slate-300 rounded-lg shadow-xs py-1 px-2.5 pr-8 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>registros</span>
                </div>

                <div class="text-slate-500 text-xs">
                    Mostrando <strong class="text-slate-800">{{ fromRecord }}</strong> a 
                    <strong class="text-slate-800">{{ toRecord }}</strong> de 
                    <strong class="text-slate-800">{{ totalRecords.toLocaleString() }}</strong> registros
                </div>
            </div>

            <!-- Derecha: Botones del Paginador -->
            <div v-if="totalPages > 1" class="inline-flex items-center flex-wrap justify-center gap-1">
                <!-- Primera página -->
                <button 
                    type="button" 
                    @click="changePage(1)"
                    :disabled="currentPage === 1"
                    class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                    title="Primera página"
                >
                    <ChevronsLeft class="w-4 h-4" />
                </button>

                <!-- Anterior -->
                <button 
                    type="button" 
                    @click="changePage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                    title="Página anterior"
                >
                    <ChevronLeft class="w-4 h-4" />
                </button>

                <!-- Páginas numeradas -->
                <template v-for="(p, index) in visiblePages" :key="index">
                    <span v-if="p === '...'" class="px-2 text-slate-400 text-xs">...</span>
                    <button 
                        v-else
                        type="button"
                        @click="changePage(p)"
                        class="min-w-[32px] h-8 px-2 text-xs font-bold rounded-lg border transition-all"
                        :class="[
                            p === currentPage 
                                ? 'bg-blue-600 border-blue-600 text-white shadow-xs' 
                                : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'
                        ]"
                    >
                        {{ p }}
                    </button>
                </template>

                <!-- Siguiente -->
                <button 
                    type="button" 
                    @click="changePage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                    title="Página siguiente"
                >
                    <ChevronRight class="w-4 h-4" />
                </button>

                <!-- Última página -->
                <button 
                    type="button" 
                    @click="changePage(totalPages)"
                    :disabled="currentPage === totalPages"
                    class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                    title="Última página"
                >
                    <ChevronsRight class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>
