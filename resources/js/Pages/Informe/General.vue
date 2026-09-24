<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { CheckCircle, AlertCircle } from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';
import FileViewerModal from '@/Components/UI/FileViewerModal.vue';

const props = defineProps({
    informes: {
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
        default: '/exportar-biblioteca',
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
    fecha: props.filters.fecha || '',
    ugels: props.filters.ugels || '',
    instituciones: props.filters.instituciones || '',
    docentes: props.filters.docentes || '',
    nivel: props.filters.nivel || '',
    buscar: props.filters.buscar || '',
    per_page: props.filters.per_page || 10,
});

const niveles = ['Escolarizado', 'No escolarizado - PRONOEI'];

// La vista General encadena UGEL → Institución: la lista de instituciones se
// carga por AJAX (igual que el Blade) porque el filtro del servidor exige
// coincidencia exacta sobre users.institucion.
const instOptions = ref([]);

const institucionOptions = computed(() => {
    const current = localFilters.instituciones;
    if (current && !instOptions.value.includes(current)) {
        return [current, ...instOptions.value];
    }
    return instOptions.value;
});

async function loadInstituciones() {
    instOptions.value = [];
    if (!props.showFullFilters || !localFilters.ugels) return;
    try {
        const params = new URLSearchParams({
            ugel: localFilters.ugels,
            year: localFilters.year || '',
        });
        const res = await fetch(`/buscar-instituciones-por-ugel-inf?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        instOptions.value = Array.isArray(data) ? data.map((d) => d.nomInstitucion) : [];
    } catch (e) {
        instOptions.value = [];
    }
}

watch(() => props.filters, (f) => {
    localFilters.year = f.year || props.anio || String(new Date().getFullYear());
    localFilters.texto = f.texto || '';
    localFilters.fecha = f.fecha || '';
    localFilters.ugels = f.ugels || '';
    localFilters.instituciones = f.instituciones || '';
    localFilters.docentes = f.docentes || '';
    localFilters.nivel = f.nivel || '';
    localFilters.buscar = f.buscar || '';
    localFilters.per_page = f.per_page || 10;

    if (props.showFullFilters && localFilters.ugels && !instOptions.value.length) {
        loadInstituciones();
    }
}, { deep: true });

onMounted(() => {
    if (props.showFullFilters && localFilters.ugels) {
        loadInstituciones();
    }
});

const isDirector = computed(() => props.scope === 'director');

const textoLabel = computed(() => (isDirector.value ? 'Nombre de la Biblioteca' : 'DNI del Docente'));
const textoPlaceholder = computed(() => (isDirector.value ? 'Ej. Biblioteca de aula' : 'Ingrese DNI'));

const columns = [
    { key: 'nombreInforme', label: 'Nombre de la Biblioteca', sortable: false },
    { key: 'descripcion', label: 'Descripción', sortable: false },
    { key: 'fecha', label: 'Fecha', width: '110px', sortable: false },
    { key: 'documento', label: 'Documento', width: '90px', align: 'center', class: 'no-export' },
    { key: 'name', label: 'Usuario', sortable: false },
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

function openViewer(informe) {
    if (!informe.enlace) return;
    const fileName = informe.enlace.split('/').pop() || 'documento';
    viewer.name = fileName;
    viewer.url = `/visor/stream?path=${encodeURIComponent(informe.enlace)}`;
    viewer.downloadUrl = `/informes/${informe.id}/download`;
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
    if (localFilters.year) params.year = localFilters.year;
    if (localFilters.texto) params.texto = localFilters.texto;
    if (localFilters.fecha) params.fecha = localFilters.fecha;
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
    instOptions.value = [];
    if (localFilters.ugels) {
        loadInstituciones();
    }
    applyFilters();
}

function onYearChange() {
    if (props.showFullFilters && localFilters.ugels) {
        loadInstituciones();
    }
    applyFilters();
}

function handleSearch(query) {
    localFilters.buscar = query;
    applyFilters();
}

function handleClearFilters() {
    localFilters.year = String(new Date().getFullYear());
    localFilters.texto = '';
    localFilters.fecha = '';
    localFilters.ugels = '';
    localFilters.instituciones = '';
    localFilters.docentes = '';
    localFilters.nivel = '';
    localFilters.buscar = '';
    instOptions.value = [];
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

function handleExportExcel() {
    const params = new URLSearchParams(getCleanParams()).toString();
    window.location.href = `${props.exportRoute}?${params}`;
}
</script>

<template>
    <AppLayout>
        <Head title="Biblioteca del Aula - Consulta" />

        <PageHeader
            title="Biblioteca del Aula"
            icon="BookOpen"
            color="orange"
            subtitle="Consulta y seguimiento de la biblioteca del aula"
        />

        <SectionTabs :tabs="tabs" color="orange" />

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
            :id="`tabla-informes-${scope}`"
            :columns="columns"
            :rows="informes.data || []"
            :pagination="informes"
            :search-value="localFilters.buscar"
            search-placeholder="Buscar en todos los registros..."
            :export-filename="`biblioteca_aula_${scope}`"
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ textoLabel }}</label>
                    <input
                        type="text"
                        v-model="localFilters.texto"
                        :placeholder="textoPlaceholder"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>

                <div v-if="isDirector">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Fecha</label>
                    <input
                        type="date"
                        v-model="localFilters.fecha"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    />
                </div>

                <div v-if="showFullFilters">
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

                <div v-if="showFullFilters">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Institución</label>
                    <select
                        v-model="localFilters.instituciones"
                        :disabled="!localFilters.ugels"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-slate-100 disabled:cursor-not-allowed"
                        :title="localFilters.ugels ? '' : 'Selecciona una UGEL primero'"
                        @change="applyFilters"
                    >
                        <option value="">{{ localFilters.ugels ? '-- Todas las Instituciones --' : 'Selecciona una UGEL primero' }}</option>
                        <option v-for="i in institucionOptions" :key="i" :value="i">{{ i }}</option>
                    </select>
                </div>

                <div v-else-if="listaInstituciones.length">
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

                <div v-if="showFullFilters">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Docente</label>
                    <input
                        type="text"
                        v-model="localFilters.docentes"
                        placeholder="Nombre del docente"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>

                <div v-if="!isDirector">
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
                <td class="px-4 py-3 font-semibold text-blue-600 align-middle min-w-[200px]">
                    {{ row.nombreInforme }}
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
