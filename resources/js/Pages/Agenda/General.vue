<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { AgendasService } from '@/Services/agenda';
import { Paperclip, Eye } from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';
import { formatFecha } from '@/Utils/fecha';
import DetailModal from '@/Components/UI/DetailModal.vue';
import AgendaDetailModal from './Partials/AgendaDetailModal.vue';
import EvidenciaStack from './Partials/EvidenciaStack.vue';

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

const detalle = reactive({
    show: false,
    agenda: null,
});

function abrirDetalle(row) {
    detalle.agenda = {
        id: row.id,
        title: row.title,
        evento: row.evento,
        nomDocente: row.nomDocente,
        seccion: row.seccion,
        color: row.color,
    };
    detalle.show = true;
}

const localFilters = reactive({
    year: props.filters.year || String(props.anio || new Date().getFullYear()),
    ugels: props.filters.ugels || '',
    instituciones: props.filters.instituciones || '',
    docentes: props.filters.docentes || '',
    nivel: props.filters.nivel || '',
    buscar: props.filters.buscar || '',
    per_page: props.filters.per_page || 10,
    sort: props.filters.sort || '',
    direction: props.filters.direction || '',
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
        const data = await AgendasService.buscarInstitucionesPorUgel({
            ugel: localFilters.ugels,
            year: localFilters.year || '',
        });
        instOptions.value = Array.isArray(data) ? data.map((d) => d.nomInstitucion) : [];
    } catch (e) {
        instOptions.value = [];
    }
}

async function loadDocentes() {
    docOptions.value = [];
    if (!localFilters.instituciones) return;
    try {
        const data = await AgendasService.buscarDocentesPorInstitucion({
            docente: localFilters.instituciones,
            ugel: localFilters.ugels,
            year: localFilters.year || '',
        });
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
    localFilters.sort = f.sort || '';
    localFilters.direction = f.direction || '';

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
    { key: 'nomDocente', label: 'Docente', sortable: true },
    { key: 'title', label: 'Nombre de la agenda', sortable: true },
    { key: 'start', label: 'Fecha Inicio', width: '110px', sortable: true },
    { key: 'end', label: 'Fecha Fin', width: '110px', sortable: true },
    { key: 'evidencias_count', label: 'Evidencias', width: '110px', align: 'center', sortable: false },
    { key: 'opciones', label: 'Acciones', width: '90px', align: 'center', class: 'no-export' },
];

// ── Modal de Detalle genérico (solo lectura) ──
const ficha = reactive({
    show: false,
    title: '',
    fields: [],
});

function openDetail(row) {
    ficha.title = row.title || 'Detalle de la agenda';
    ficha.fields = [
        { label: 'Docente', value: row.nomDocente },
        { label: 'Descripción', value: row.evento, full: true },
        { label: 'Fecha Inicio', value: formatFecha(row.start) },
        { label: 'Fecha Fin', value: formatFecha(row.end) },
        { label: 'Evidencias', value: row.evidencias_count ?? 0 },
        { label: 'Institución', value: row.institucion, full: true },
        { label: 'Tipo de II.EE', value: row.nivelinstitucion },
        { label: 'UGEL', value: row.ugel },
        { label: 'Provincia', value: row.provincia },
        { label: 'Distrito', value: row.distrito },
    ];
    ficha.show = true;
}

function closeDetail() {
    ficha.show = false;
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
    if (localFilters.sort) params.sort = localFilters.sort;
    if (localFilters.direction) params.direction = localFilters.direction;
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

function handleSort({ key, order }) {
    localFilters.sort = key;
    localFilters.direction = order;
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

const exportUrl = computed(() => AgendasService.exportUrl(getCleanParams(), props.exportRoute));
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
            :sort-key="localFilters.sort"
            :sort-order="localFilters.direction"
            @sort="handleSort"
            search-placeholder="Buscar en todos los registros..."
            :export-url="exportUrl"
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
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 align-middle">
                    {{ formatFecha(row.start) }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 align-middle">
                    {{ formatFecha(row.end) }}
                </td>
                <td class="px-4 py-3 align-middle text-center">
                    <EvidenciaStack
                        v-if="row.evidencias_preview?.length"
                        :evidencias="row.evidencias_preview"
                        :total="row.evidencias_count || row.evidencias_preview.length"
                        :limite="3"
                        :expandable="false"
                        :mostrar-contador="false"
                        size="sm"
                        @view="abrirDetalle(row)"
                    />
                    <button
                        v-else
                        type="button"
                        @click="abrirDetalle(row)"
                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold rounded-lg border transition-colors cursor-pointer"
                        :class="row.evidencias_count
                            ? 'text-pink-700 bg-pink-50 border-pink-200 hover:bg-pink-100'
                            : 'text-slate-400 bg-slate-50 border-slate-200 hover:bg-slate-100'"
                        title="Ver días y evidencias"
                    >
                        <Paperclip class="w-3.5 h-3.5" />
                        {{ row.evidencias_count || 0 }}
                    </button>
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

        <AgendaDetailModal
            :show="detalle.show"
            :agenda="detalle.agenda"
            :can-edit="false"
            :can-evidencia="false"
            @close="detalle.show = false"
        />

        <!-- ══ MODAL DE DETALLE (SOLO LECTURA) ══ -->
        <DetailModal
            :show="ficha.show"
            :title="ficha.title"
            subtitle="Detalle de la agenda"
            icon="CalendarCheck"
            color="pink"
            :fields="ficha.fields"
            @close="closeDetail"
        />
    </AppLayout>
</template>
