<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { SectoresService } from '@/Services/sector';
import { CheckCircle, AlertCircle, Eye } from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';
import { formatFecha } from '@/Utils/fecha';
import DetailModal from '@/Components/UI/DetailModal.vue';
import FileViewerModal from '@/Components/UI/FileViewerModal.vue';
import ArchivoIcono from '@/Components/UI/ArchivoIcono.vue';

const props = defineProps({
    sectores: {
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
    listaInstituciones: {
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
    year: props.filters.year || props.anio || String(new Date().getFullYear()),
    texto: props.filters.texto || '',
    nivel: props.filters.nivel || '',
    ugels: props.filters.ugels || '',
    instituciones: props.filters.instituciones || '',
    docentes: props.filters.docentes || '',
    buscar: props.filters.buscar || '',
    per_page: props.filters.per_page || 10,
    sort: props.filters.sort || '',
    direction: props.filters.direction || '',
});

const niveles = ['', 'Escolarizado', 'No escolarizado - PRONOEI'];

const columns = [
    { key: 'nombreSector', label: 'Nombre del Sector', sortable: true },
    { key: 'name', label: 'Docente', sortable: true },
    { key: 'fecha', label: 'Fecha', width: '110px', sortable: true },
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
    detalle.title = row.nombreSector || 'Detalle del sector';
    detalle.fields = [
        { label: 'Descripción', value: row.descripcion, full: true },
        { label: 'Fecha', value: formatFecha(row.fecha) },
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

const viewer = reactive({
    show: false,
    url: '',
    name: '',
    downloadUrl: '',
});

function openViewer(sector) {
    if (!sector.enlace) return;
    const fileName = sector.enlace.split('/').pop() || 'documento';
    viewer.name = fileName;
    viewer.url = `/visor/stream?path=${encodeURIComponent(sector.enlace)}`;
    viewer.downloadUrl = `/sectores/${sector.id}/download`;
    viewer.show = true;
}

function getCleanParams() {
    const params = {};
    if (localFilters.year) params.year = localFilters.year;
    if (localFilters.texto) params.texto = localFilters.texto;
    if (localFilters.nivel) params.nivel = localFilters.nivel;
    if (localFilters.ugels) params.ugels = localFilters.ugels;
    if (localFilters.instituciones) params.instituciones = localFilters.instituciones;
    if (localFilters.docentes) params.docentes = localFilters.docentes;
    if (localFilters.buscar) params.buscar = localFilters.buscar;
    if (localFilters.per_page && localFilters.per_page !== 10) params.per_page = localFilters.per_page;
    if (localFilters.sort) params.sort = localFilters.sort;
    if (localFilters.direction) params.direction = localFilters.direction;
    return params;
}

function applyFilters() {
    loading.value = true;
    SectoresService.list(getCleanParams(), {
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
    localFilters.year = String(new Date().getFullYear());
    localFilters.texto = '';
    localFilters.nivel = '';
    localFilters.ugels = '';
    localFilters.instituciones = '';
    localFilters.docentes = '';
    localFilters.buscar = '';
    applyFilters();
}

function handleSort({ key, order }) {
    localFilters.sort = key;
    localFilters.direction = order;
    applyFilters();
}

function handlePageChange(newPage) {
    loading.value = true;
    SectoresService.list({
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

function handleExportExcel() {
    SectoresService.exportar(getCleanParams(), props.scope);
}
</script>

<template>
    <AppLayout>
        <Head title="Sectores del Aula - Consulta" />

        <PageHeader
            title="Sectores del Aula"
            icon="LayoutGrid"
            color="gray"
            subtitle="Consulta y seguimiento de los sectores del aula"
        />

        <SectionTabs :tabs="tabs" color="gray" />

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

        <BaseTable
            :id="`tabla-sectores-${scope}`"
            :columns="columns"
            :rows="sectores.data || []"
            :pagination="sectores"
            :search-value="localFilters.buscar"
            :sort-key="localFilters.sort"
            :sort-order="localFilters.direction"
            @sort="handleSort"
            search-placeholder="Buscar en todos los registros..."
            :loading="loading"
            @search="handleSearch"
            @clear="handleClearFilters"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
            @export="handleExportExcel"
        >
            <template #filters>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Año</label>
                    <select
                        v-model="localFilters.year"
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de II.EE</label>
                    <select
                        v-model="localFilters.nivel"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">-- Todos --</option>
                        <option v-for="n in niveles.filter(Boolean)" :key="n" :value="n">{{ n }}</option>
                    </select>
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

                <div v-if="listaInstituciones.length">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Institución</label>
                    <select
                        v-model="localFilters.instituciones"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">-- Todas las Instituciones --</option>
                        <option v-for="i in listaInstituciones" :key="i" :value="i">{{ i }}</option>
                    </select>
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
            </template>

            <template #row="{ row }">
                <td class="px-4 py-3 font-semibold text-blue-600 align-middle min-w-[200px]">
                    {{ row.nombreSector }}
                </td>
                <td class="px-4 py-3 text-slate-700 font-medium align-middle whitespace-nowrap">
                    {{ row.name || '-' }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 align-middle">
                    {{ formatFecha(row.fecha) }}
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

        <FileViewerModal
            :show="viewer.show"
            :url="viewer.url"
            :name="viewer.name"
            :download-url="viewer.downloadUrl"
            @close="viewer.show = false"
        />

        <DetailModal
            :show="detalle.show"
            :title="detalle.title"
            subtitle="Detalle del sector"
            icon="LayoutGrid"
            color="gray"
            :fields="detalle.fields"
            @close="closeDetail"
        />
    </AppLayout>
</template>
