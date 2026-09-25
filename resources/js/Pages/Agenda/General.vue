<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';

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
        default: 'agenda.general',
    },
    exportRoute: {
        type: String,
        default: '/exportar-agendas',
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

const loading = ref(false);

const localFilters = reactive({
    year: props.filters.year || String(props.anio || new Date().getFullYear()),
    ugels: props.filters.ugels || '',
    instituciones: props.filters.instituciones || '',
    docentes: props.filters.docentes || '',
    nivel: props.filters.nivel || '',
    buscar: props.filters.buscar || '',
    per_page: props.filters.per_page || 10,
});

const niveles = ['Escolarizado', 'No escolarizado - PRONOEI'];

// UGEL -> Institución -> Docente encadenados por AJAX, igual que el Blade.
const instOptions = ref([]);
const docOptions = ref([]);

const institucionOptions = computed(() => {
    const current = localFilters.instituciones;
    if (current && !instOptions.value.includes(current)) {
        return [current, ...instOptions.value];
    }
    return instOptions.value;
});

const docenteOptions = computed(() => {
    const current = localFilters.docentes;
    if (current && !docOptions.value.includes(current)) {
        return [current, ...docOptions.value];
    }
    return docOptions.value;
});

async function loadInstituciones() {
    instOptions.value = [];
    if (!localFilters.ugels) return;
    try {
        const params = new URLSearchParams({
            ugel: localFilters.ugels,
            year: localFilters.year || '',
        });
        const res = await fetch(`/buscar-instituciones-por-ugel-ag?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        instOptions.value = Array.isArray(data) ? data.map((d) => d.nomInstitucion) : [];
    } catch (e) {
        instOptions.value = [];
    }
}

async function loadDocentes() {
    docOptions.value = [];
    if (!localFilters.instituciones) return;
    try {
        const params = new URLSearchParams({
            docente: localFilters.instituciones,
            ugel: localFilters.ugels,
            year: localFilters.year || '',
        });
        const res = await fetch(`/buscar-docentes-por-institucion-ag?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        docOptions.value = Array.isArray(data) ? data.map((d) => d.name) : [];
    } catch (e) {
        docOptions.value = [];
    }
}

function reloadChain() {
    loadInstituciones().then(() => loadDocentes());
}

watch(() => props.filters, (f) => {
    localFilters.year = f.year || String(props.anio || new Date().getFullYear());
    localFilters.ugels = f.ugels || '';
    localFilters.instituciones = f.instituciones || '';
    localFilters.docentes = f.docentes || '';
    localFilters.nivel = f.nivel || '';
    localFilters.buscar = f.buscar || '';
    localFilters.per_page = f.per_page || 10;

    if (localFilters.ugels && !instOptions.value.length) {
        reloadChain();
    }
}, { deep: true });

onMounted(() => {
    if (localFilters.ugels) {
        reloadChain();
    }
});

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
    if (localFilters.ugels) params.ugels = localFilters.ugels;
    if (localFilters.instituciones) params.instituciones = localFilters.instituciones;
    if (localFilters.docentes) params.docentes = localFilters.docentes;
    if (localFilters.nivel) params.nivel = localFilters.nivel;
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

function onUgelChange() {
    localFilters.instituciones = '';
    localFilters.docentes = '';
    docOptions.value = [];
    loadInstituciones();
    applyFilters();
}

function onInstitucionChange() {
    localFilters.docentes = '';
    docOptions.value = [];
    loadDocentes();
    applyFilters();
}

function onYearChange() {
    if (localFilters.ugels) {
        reloadChain();
    }
    applyFilters();
}

function handleSearch(query) {
    localFilters.buscar = query;
    applyFilters();
}

function handleClearFilters() {
    localFilters.year = String(new Date().getFullYear());
    localFilters.ugels = '';
    localFilters.instituciones = '';
    localFilters.docentes = '';
    localFilters.nivel = '';
    localFilters.buscar = '';
    instOptions.value = [];
    docOptions.value = [];
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

const exportUrl = computed(() => {
    const params = new URLSearchParams(getCleanParams()).toString();
    return params ? `${props.exportRoute}?${params}` : props.exportRoute;
});
</script>

<template>
    <AppLayout>
        <Head title="Agenda de Lectura (General)" />

        <PageHeader
            title="Agenda de Lectura"
            icon="CalendarCheck"
            color="pink"
            subtitle="Lectura y actividades de lectura en la institución educativa"
        />

        <SectionTabs :tabs="tabs" color="pink" />

        <BaseTable
            id="tabla-agendas-general"
            :columns="columns"
            :rows="agendas.data || []"
            :pagination="agendas"
            :search-value="localFilters.buscar"
            search-placeholder="Buscar en todos los registros..."
            :export-url="exportUrl"
            export-filename="agendas_lectura_general"
            :loading="loading"
            @search="handleSearch"
            @clear="handleClearFilters"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">UGEL</label>
                    <select
                        v-model="localFilters.ugels"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="onUgelChange"
                    >
                        <option value="">-- Todas las UGEL --</option>
                        <option v-for="u in listaUgels" :key="u" :value="u">{{ u }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Institución</label>
                    <select
                        v-model="localFilters.instituciones"
                        :disabled="!localFilters.ugels"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-slate-100 disabled:cursor-not-allowed"
                        :title="localFilters.ugels ? '' : 'Selecciona una UGEL primero'"
                        @change="onInstitucionChange"
                    >
                        <option value="">{{ localFilters.ugels ? '-- Todas las Instituciones --' : 'Selecciona una UGEL primero' }}</option>
                        <option v-for="i in institucionOptions" :key="i" :value="i">{{ i }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Docente</label>
                    <select
                        v-model="localFilters.docentes"
                        :disabled="!localFilters.instituciones"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-slate-100 disabled:cursor-not-allowed"
                        :title="localFilters.instituciones ? '' : 'Selecciona una institución primero'"
                        @change="applyFilters"
                    >
                        <option value="">{{ localFilters.instituciones ? '-- Todos los docentes --' : 'Selecciona una institución primero' }}</option>
                        <option v-for="d in docenteOptions" :key="d" :value="d">{{ d }}</option>
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
