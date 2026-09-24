<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CheckCircle, AlertCircle } from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';
import FileViewerModal from '@/Components/UI/FileViewerModal.vue';

const props = defineProps({
    difusions: {
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
    { key: 'lugar', label: 'Lugar', sortable: false },
    { key: 'descripcion', label: 'Descripción', sortable: false },
    { key: 'fecha', label: 'Fecha', width: '110px', sortable: false },
    { key: 'documento', label: 'Documento', width: '90px', align: 'center', class: 'no-export' },
    { key: 'name', label: 'Docente', sortable: false },
    { key: 'cargo', label: 'Cargo', sortable: false },
    { key: 'institucion', label: 'Institución', sortable: false },
    { key: 'nivelinstitucion', label: 'Tipo de II.EE', sortable: false },
    { key: 'provincia', label: 'Provincia', sortable: false },
    { key: 'distrito', label: 'Distrito', sortable: false },
    { key: 'ugel', label: 'UGEL', width: '90px', sortable: false },
];

const viewer = reactive({
    show: false,
    url: '',
    name: '',
    downloadUrl: '',
});

function openViewer(difusion) {
    if (!difusion.enlace) return;
    const fileName = difusion.enlace.split('/').pop() || 'documento';
    viewer.name = fileName;
    viewer.url = `/visor/stream?path=${encodeURIComponent(difusion.enlace)}`;
    viewer.downloadUrl = `/difusions/${difusion.id}/download`;
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
    }
    return dateStr;
}

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
    router.get(window.location.pathname, getCleanParams(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
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
    router.get(window.location.pathname, {
        ...getCleanParams(),
        page: newPage,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            loading.value = false;
        },
    });
}

function handlePerPageChange(newPerPage) {
    localFilters.per_page = newPerPage;
    applyFilters();
}

const exportEndpoints = {
    general: '/export-difusion-general',
    ugel: '/export-difusion-ugel',
    director: '/export-difusion-director',
};

function handleExportExcel() {
    const endpoint = exportEndpoints[props.scope] || '/export-difusion-general';
    const params = new URLSearchParams(getCleanParams()).toString();
    window.location.href = `${endpoint}?${params}`;
}
</script>

<template>
    <AppLayout>
        <Head title="Acción de Difusión - Consulta" />

        <PageHeader 
            title="Acción de Difusión" 
            icon="Radio" 
            color="blue"
            subtitle="Consulta y seguimiento de acciones de difusión"
        />

        <SectionTabs :tabs="tabs" color="blue" />

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
            :id="`tabla-difusiones-${scope}`"
            :columns="columns"
            :rows="difusions.data || []"
            :pagination="difusions"
            :search-value="localFilters.buscar"
            search-placeholder="Buscar en todos los registros..."
            :export-filename="`acciones_difusion_${scope}`"
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
                        placeholder="Lugar de la difusión"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>
            </template>

            <template #row="{ row }">
                <td class="px-4 py-3 font-semibold text-blue-600 align-middle min-w-[200px]">
                    {{ row.nombreAccion }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap min-w-[120px]">
                    {{ row.lugar || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle min-w-[220px] max-w-[340px]">
                    {{ row.descripcion || '-' }}
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
                        <i :class="row.documento || 'fas fa-file-alt'" :style="{ color: row.color || '#3B82F6', fontSize: '18px' }"></i>
                    </button>
                    <span v-else class="text-slate-400 text-xs">-</span>
                </td>
                <td class="px-4 py-3 text-slate-700 font-medium align-middle whitespace-nowrap">
                    {{ row.name || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.cargo || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle min-w-[180px]">
                    {{ row.institucion || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.nivelinstitucion || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.provincia || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.distrito || '-' }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 align-middle">
                    {{ row.ugel || '-' }}
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
    </AppLayout>
</template>
