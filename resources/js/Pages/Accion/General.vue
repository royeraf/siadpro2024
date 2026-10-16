<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { AccionsService } from '@/Services/accion';
import { Megaphone, CheckCircle, AlertCircle, Eye } from 'lucide-vue-next';
import * as XLSX from 'xlsx';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';
import DetailModal from '@/Components/UI/DetailModal.vue';
import FileViewerModal from '@/Components/UI/FileViewerModal.vue';
import ArchivoIcono from '@/Components/UI/ArchivoIcono.vue';

const props = defineProps({
    accions: {
        type: Object,
        required: true,
    },
    anio: {
        type: [String, Number],
        default: () => new Date().getFullYear(),
    },
    showFullFilters: {
        type: Boolean,
        default: false,
    },
    listaUgels: {
        type: Array,
        default: () => [],
    },
    listaAnios: {
        type: Array,
        default: () => [],
    },
    filterActionRoute: {
        type: String,
        default: '',
    },
    exportRoute: {
        type: String,
        default: '',
    },
    scope: {
        type: String,
        default: 'general',
    },
    tabs: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const loading = ref(false);

const localFilters = reactive({
    anio: props.filters.anio || props.anio || String(new Date().getFullYear()),
    texto: props.filters.texto || '',
    docentes: props.filters.docentes || '',
    ugels: props.filters.ugels || '',
    instituciones: props.filters.instituciones || '',
    lugar: props.filters.lugar || '',
    buscar: props.filters.buscar || '',
    per_page: props.filters.per_page || 10,
});

const columns = [
    { key: 'nombreAccion', label: 'Nombre de la Acción', sortable: false },
    { key: 'name', label: 'Docente', sortable: false },
    { key: 'fecha', label: 'Fecha', width: '110px', sortable: false },
    { key: 'documento', label: 'Documento', width: '90px', align: 'center', class: 'no-export' },
    { key: 'opciones', label: 'Acciones', width: '90px', align: 'center', class: 'no-export' },
];

// ── Modal de Detalle (solo lectura) ──
const detalle = reactive({
    show: false,
    title: '',
    fields: [],
});

function openDetail(row) {
    detalle.title = row.nombreAccion || 'Detalle de la acción';
    detalle.fields = [
        { label: 'Descripción', value: row.descripcion, full: true },
        { label: 'Lugar', value: row.lugar },
        { label: 'Fecha', value: formatDate(row.fecha) },
        { label: 'Docente', value: row.name },
        { label: 'Cargo', value: row.cargo },
        { label: 'Institución', value: row.institucion, full: true },
        { label: 'Tipo de II.EE', value: row.nivelinstitucion },
        { label: 'UGEL', value: row.ugel },
        { label: 'Provincia', value: row.provincia },
        { label: 'Distrito', value: row.distrito },
    ];
    detalle.show = true;
}

function closeDetail() {
    detalle.show = false;
}

// ── Estado del Visor de Archivos ──
const viewer = reactive({
    show: false,
    url: '',
    name: '',
    downloadUrl: '',
});

function openViewer(accion) {
    if (!accion.enlace) return;
    const fileName = accion.enlace.split('/').pop() || 'documento';
    viewer.name = fileName;
    viewer.url = `/visor/stream?path=${encodeURIComponent(accion.enlace)}`;
    viewer.downloadUrl = `/accions/${accion.id}/download`;
    viewer.show = true;
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    try {
        const parts = dateStr.split(' ')[0].split('-');
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }
    } catch (e) {
        // Fallback
    }
    return dateStr;
}

// ── Manejo de Filtros y Búsqueda con Inertia ──
function getCleanParams() {
    const params = {};
    if (localFilters.anio) params.anio = localFilters.anio;
    if (localFilters.texto) params.texto = localFilters.texto;
    if (localFilters.docentes) params.docentes = localFilters.docentes;
    if (localFilters.ugels) params.ugels = localFilters.ugels;
    if (localFilters.instituciones) params.instituciones = localFilters.instituciones;
    if (localFilters.lugar) params.lugar = localFilters.lugar;
    if (localFilters.buscar) params.buscar = localFilters.buscar;
    if (localFilters.per_page && localFilters.per_page !== 10) params.per_page = localFilters.per_page;
    return params;
}

function applyFilters() {
    loading.value = true;
    AccionsService.listCurrentPath(getCleanParams(), {
        onFinish: () => {
            loading.value = false;
        },
    });
}

function handleSearch(query) {
    localFilters.buscar = query;
    applyFilters();
}

function handleClearFilters() {
    localFilters.anio = String(new Date().getFullYear());
    localFilters.texto = '';
    localFilters.docentes = '';
    localFilters.ugels = '';
    localFilters.instituciones = '';
    localFilters.lugar = '';
    localFilters.buscar = '';
    applyFilters();
}

function handlePageChange(newPage) {
    loading.value = true;
    AccionsService.listCurrentPath({
        ...getCleanParams(),
        page: newPage,
    }, {
        onFinish: () => {
            loading.value = false;
        },
    });
}

function handlePerPageChange(newPerPage) {
    localFilters.per_page = newPerPage;
    applyFilters();
}

// ── Exportar a Excel (Backend con todos los registros filtrados o Fallback Cliente) ──
function handleExportExcel() {
    AccionsService.exportar(getCleanParams(), props.scope);
}
</script>

<template>
    <AppLayout>
        <Head title="Acción de Sensibilización - General" />

        <!-- ══ CABECERA DE LA PÁGINA ══ -->
        <PageHeader 
            title="Acción de Sensibilización" 
            icon="Megaphone" 
            color="yellow"
            subtitle="Consulta y seguimiento de acciones pedagógicas de sensibilización"
        />

        <!-- ══ PESTAÑAS DE ALCANCE (MIS REGISTROS / UGEL / GENERAL / DIRECTOR) ══ -->
        <SectionTabs :tabs="tabs" color="yellow" />

        <!-- ══ MENSAJES FLASH ══ -->
        <div 
            v-if="page.props.flash?.success" 
            class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between"
        >
            <div class="flex items-center">
                <CheckCircle class="w-4 h-4 mr-2 text-emerald-600 shrink-0" />
                <span>{{ page.props.flash.success }}</span>
            </div>
            <button 
                type="button" 
                @click="page.props.flash.success = null" 
                class="text-emerald-500 hover:text-emerald-700 p-1"
            >
                &times;
            </button>
        </div>

        <div 
            v-if="page.props.flash?.error" 
            class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between"
        >
            <div class="flex items-center">
                <AlertCircle class="w-4 h-4 mr-2 text-rose-600 shrink-0" />
                <span>{{ page.props.flash.error }}</span>
            </div>
            <button 
                type="button" 
                @click="page.props.flash.error = null" 
                class="text-rose-500 hover:text-rose-700 p-1"
            >
                &times;
            </button>
        </div>

        <!-- ══ TABLA BASE REUTILIZABLE ══ -->
        <BaseTable
            :id="`tabla-acciones-${scope}`"
            :columns="columns"
            :rows="accions.data || []"
            :pagination="accions"
            :search-value="localFilters.buscar"
            search-placeholder="Buscar en todos los registros..."
            :export-filename="`acciones_sensibilizacion_${scope}`"
            :loading="loading"
            @search="handleSearch"
            @clear="handleClearFilters"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
            @export="handleExportExcel"
        >
            <!-- Filtros adicionales específicos del módulo de consulta -->
            <template #filters>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Año</label>
                    <select 
                        v-model="localFilters.anio" 
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option v-for="y in listaAnios" :key="y" :value="String(y)">{{ y }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">DNI del Docente</label>
                    <input 
                        type="text" 
                        v-model="localFilters.texto" 
                        placeholder="Ingrese DNI"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Docente</label>
                    <input 
                        type="text" 
                        v-model="localFilters.docentes" 
                        placeholder="Nombre del docente"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>

                <div v-if="showFullFilters">
                    <label class="block text-xs font-bold text-slate-700 mb-1">UGEL</label>
                    <select 
                        v-model="localFilters.ugels" 
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">-- Todas las UGEL --</option>
                        <option v-for="u in listaUgels" :key="u" :value="u">{{ u }}</option>
                    </select>
                </div>

                <div v-if="showFullFilters">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Institución</label>
                    <input 
                        type="text" 
                        v-model="localFilters.instituciones" 
                        placeholder="Nombre de la institución"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lugar</label>
                    <input 
                        type="text" 
                        v-model="localFilters.lugar" 
                        placeholder="Lugar de la acción"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>
            </template>

            <!-- Renderizado de cada fila (Estilo Bootstrap con desplazamiento horizontal en móvil) -->
            <template #row="{ row }">
                <td class="px-4 py-3 font-semibold text-blue-600 align-middle min-w-[200px]">
                    {{ row.nombreAccion }}
                </td>
                <td class="px-4 py-3 text-slate-700 font-medium align-middle whitespace-nowrap">
                    {{ row.name || '-' }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 align-middle">
                    {{ formatDate(row.fecha) }}
                </td>
                <td class="px-4 py-3 text-center no-export align-middle whitespace-nowrap">
                    <button
                        v-if="row.enlace"
                        type="button"
                        @click="openViewer(row)"
                        class="inline-flex items-center justify-center p-1.5 rounded-lg hover:bg-slate-100 transition-colors"
                        :title="`Ver ${row.enlace ? row.enlace.split('/').pop() : 'documento'}`"
                    >
                        <ArchivoIcono :nombre="row.enlace" class="w-6 h-6" />
                    </button>
                    <span v-else class="text-slate-400 text-xs">-</span>
                </td>
                <td class="px-4 py-3 text-center no-export whitespace-nowrap align-middle">
                    <button
                        type="button"
                        @click="openDetail(row)"
                        class="inline-flex items-center justify-center p-1.5 bg-slate-600 hover:bg-slate-700 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                        title="Ver detalle"
                    >
                        <Eye class="w-3.5 h-3.5" />
                    </button>
                </td>
            </template>
        </BaseTable>

        <!-- ══ MODAL VISOR DE ARCHIVOS ══ -->
        <FileViewerModal 
            :show="viewer.show"
            :url="viewer.url"
            :name="viewer.name"
            :download-url="viewer.downloadUrl"
            @close="viewer.show = false"
        />

        <!-- ══ MODAL DE DETALLE (SOLO LECTURA) ══ -->
        <DetailModal
            :show="detalle.show"
            :title="detalle.title"
            subtitle="Detalle de la acción"
            icon="Megaphone"
            color="yellow"
            :fields="detalle.fields"
            @close="closeDetail"
        />
    </AppLayout>
</template>
