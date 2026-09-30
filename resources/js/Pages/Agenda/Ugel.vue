<script setup>
import { ref, reactive, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import { AgendasService } from '@/Services/agenda';
import * as XLSX from 'xlsx';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';

const props = defineProps({
    agendas: {
        type: Object,
        required: true,
    },
    anio: {
        type: [String, Number],
        default: () => new Date().getFullYear(),
    },
    listaInstituciones: {
        type: Array,
        default: () => [],
    },
    listaDocentes: {
        type: Array,
        default: () => [],
    },
    listaAnios: {
        type: Array,
        default: () => [],
    },
    scope: {
        type: String,
        default: 'ugel',
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

const loading = ref(false);

const localFilters = reactive({
    year: props.filters.year || String(props.anio || new Date().getFullYear()),
    instituciones: props.filters.instituciones || '',
    docentes: props.filters.docentes || '',
    nivel: props.filters.nivel || '',
    buscar: props.filters.buscar || '',
    per_page: props.filters.per_page || 10,
});

const niveles = ['Escolarizado', 'No escolarizado - PRONOEI'];

watch(() => props.filters, (f) => {
    localFilters.year = f.year || String(props.anio || new Date().getFullYear());
    localFilters.instituciones = f.instituciones || '';
    localFilters.docentes = f.docentes || '';
    localFilters.nivel = f.nivel || '';
    localFilters.buscar = f.buscar || '';
    localFilters.per_page = f.per_page || 10;
}, { deep: true });

const columns = [
    { key: 'nomDocente', label: 'Docente', sortable: false },
    { key: 'title', label: 'Nombre de la agenda', sortable: false },
    { key: 'evento', label: 'Descripción', sortable: false },
    { key: 'start', label: 'Fecha Inicio', width: '110px', sortable: false },
    { key: 'end', label: 'Fecha Fin', width: '110px', sortable: false },
    { key: 'institucion', label: 'Institución', sortable: false },
    { key: 'nivelinstitucion', label: 'Tipo de II.EE', sortable: false },
    { key: 'provincia', label: 'Provincia', sortable: false },
    { key: 'distrito', label: 'Distrito', sortable: false },
    { key: 'ugel', label: 'UGEL', width: '90px', sortable: false },
];

function formatDate(value) {
    if (!value) return '-';
    const fecha = new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(fecha.getTime())) return String(value);
    const pad = (n) => String(n).padStart(2, '0');
    return `${pad(fecha.getDate())}-${pad(fecha.getMonth() + 1)}-${fecha.getFullYear()}`;
}

function getCleanParams() {
    const params = {};
    if (localFilters.year) params.year = localFilters.year;
    if (localFilters.instituciones) params.instituciones = localFilters.instituciones;
    if (localFilters.docentes) params.docentes = localFilters.docentes;
    if (localFilters.nivel) params.nivel = localFilters.nivel;
    if (localFilters.buscar) params.buscar = localFilters.buscar;
    if (localFilters.per_page && localFilters.per_page !== 10) params.per_page = localFilters.per_page;
    return params;
}

function applyFilters() {
    loading.value = true;
    AgendasService.list(getCleanParams(), {
        onFinish: () => {
            loading.value = false;
        },
    });
}

function onYearChange() {
    applyFilters();
}

function handleSearch(query) {
    localFilters.buscar = query;
    applyFilters();
}

function handleClearFilters() {
    localFilters.year = String(new Date().getFullYear());
    localFilters.instituciones = '';
    localFilters.docentes = '';
    localFilters.nivel = '';
    localFilters.buscar = '';
    applyFilters();
}

function handlePageChange(newPage) {
    loading.value = true;
    AgendasService.list({
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

// No existe endpoint de exportación con alcance UGEL (el único,
// /exportar-agendas, exige agendas.general), igual que en el Blade original,
// cuyo export era client-side: se replica con la misma librería que el resto
// de los módulos migrados.
function handleExportExcel() {
    const dataToExport = (props.agendas.data || []).map((row, idx) => ({
        '#': idx + 1,
        'Docente': row.nomDocente || '',
        'Nombre de la agenda': row.title || '',
        'Descripción': row.evento || '',
        'Fecha Inicio': formatDate(row.start),
        'Fecha Fin': formatDate(row.end),
        'Institución': row.institucion || '',
        'Tipo de II.EE': row.nivelinstitucion || '',
        'Provincia': row.provincia || '',
        'Distrito': row.distrito || '',
        'UGEL': row.ugel || '',
    }));

    const ws = XLSX.utils.json_to_sheet(dataToExport);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Agenda de Lectura');
    XLSX.writeFile(wb, `agendas_lectura_ugel_${new Date().toISOString().slice(0, 10)}.xlsx`);
}
</script>

<template>
    <AppLayout>
        <Head title="Agenda de Lectura (UGEL)" />

        <PageHeader
            title="Agenda de Lectura"
            icon="CalendarCheck"
            color="pink"
            subtitle="Lectura y actividades de lectura en la institución educativa"
        />

        <SectionTabs :tabs="tabs" color="pink" />

        <BaseTable
            id="tabla-agendas-ugel"
            :columns="columns"
            :rows="agendas.data || []"
            :pagination="agendas"
            :search-value="localFilters.buscar"
            search-placeholder="Buscar en todos los registros..."
            export-filename="agendas_lectura_ugel"
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
                        @change="onYearChange"
                    >
                        <option v-for="y in listaAnios" :key="y" :value="String(y)">{{ y }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Institución</label>
                    <select
                        v-model="localFilters.instituciones"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">Buscar institución...</option>
                        <option v-for="i in listaInstituciones" :key="i" :value="i">{{ i }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Docente</label>
                    <select
                        v-model="localFilters.docentes"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">Buscar docente...</option>
                        <option v-for="d in listaDocentes" :key="d" :value="d">{{ d }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de II.EE</label>
                    <select
                        v-model="localFilters.nivel"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">-- Todos --</option>
                        <option v-for="n in niveles" :key="n" :value="n">{{ n }}</option>
                    </select>
                </div>
            </template>

            <template #row="{ row }">
                <td class="px-4 py-3 font-semibold text-blue-600 align-middle min-w-[180px]">
                    {{ row.nomDocente || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-700 align-middle min-w-[180px]">
                    {{ row.title || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle min-w-[220px] max-w-[340px]">
                    {{ row.evento || '-' }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 align-middle">
                    {{ formatDate(row.start) }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 align-middle">
                    {{ formatDate(row.end) }}
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
    </AppLayout>
</template>
