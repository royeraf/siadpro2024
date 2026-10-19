<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ProduccionsService } from '@/Services/produccion';
import { Pencil, Trash2, CheckCircle, AlertCircle, Eye } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import CreateButton from '@/Components/UI/CreateButton.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';
import { formatFecha } from '@/Utils/fecha';
import DetailModal from '@/Components/UI/DetailModal.vue';
import FileViewerModal from '@/Components/UI/FileViewerModal.vue';
import ArchivoIcono from '@/Components/UI/ArchivoIcono.vue';
import ProduccionFormModal from './Partials/ProduccionFormModal.vue';

const props = defineProps({
    produccions: {
        type: Object,
        required: true,
    },
    listaAnios: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    tabs: {
        type: Array,
        default: () => [],
    },
    can: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const loading = ref(false);

const localFilters = reactive({
    year: props.filters.year || String(new Date().getFullYear()),
    texto: props.filters.texto || '',
    fecha: props.filters.fecha || '',
    buscar: props.filters.buscar || '',
    per_page: props.filters.per_page || 10,
    sort: props.filters.sort || '',
    direction: props.filters.direction || '',
});

const columns = computed(() => {
    return [
        { key: 'nombreProduccion', label: 'Tipo de Producción', sortable: true },
        { key: 'fecha', label: 'Fecha', width: '110px', sortable: true },
        { key: 'documento', label: 'Documento', width: '90px', align: 'center', class: 'no-export' },
        { key: 'opciones', label: 'Acciones', width: '130px', align: 'center', class: 'no-export' },
    ];
});

const formModal = reactive({
    show: false,
    produccion: null,
});

function openCreateModal() {
    formModal.produccion = null;
    formModal.show = true;
}

function openEditModal(produccion) {
    formModal.produccion = produccion;
    formModal.show = true;
}

function closeFormModal() {
    formModal.show = false;
    formModal.produccion = null;
}

// ── Modal de Detalle (solo lectura) ──
const detalle = reactive({
    show: false,
    title: '',
    fields: [],
});

function openDetail(row) {
    detalle.title = row.nombreProduccion || 'Detalle de la producción';
    detalle.fields = [
        { label: 'Descripción', value: row.descripcion, full: true },
        { label: 'Fecha', value: formatFecha(row.fecha) },
        { label: 'Usuario', value: row.get_user?.name || row.user?.name },
        { label: 'Institución', value: row.get_user?.institucion || row.user?.institucion, full: true },
        { label: 'UGEL', value: row.get_user?.ugel || row.user?.ugel },
        { label: 'Provincia', value: row.get_user?.provincia || row.user?.provincia },
        { label: 'Distrito', value: row.get_user?.distrito || row.user?.distrito },
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

function openViewer(produccion) {
    if (!produccion.enlace) return;
    const fileName = produccion.enlace.split('/').pop() || 'documento';
    viewer.name = fileName;
    viewer.url = `/visor/stream?path=${encodeURIComponent(produccion.enlace)}`;
    viewer.downloadUrl = `/produccions/${produccion.id}/download`;
    viewer.show = true;
}

function getCleanParams() {
    const params = {};
    if (localFilters.year) params.year = localFilters.year;
    if (localFilters.texto) params.texto = localFilters.texto;
    if (localFilters.fecha) params.fecha = localFilters.fecha;
    if (localFilters.buscar) params.buscar = localFilters.buscar;
    if (localFilters.per_page && localFilters.per_page !== 10) params.per_page = localFilters.per_page;
    if (localFilters.sort) params.sort = localFilters.sort;
    if (localFilters.direction) params.direction = localFilters.direction;
    return params;
}

function applyFilters() {
    loading.value = true;
    ProduccionsService.list(getCleanParams(), {
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
    localFilters.fecha = '';
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
    ProduccionsService.list({
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

function handleExportExcel(filename) {
    const dataToExport = (props.produccions.data || []).map((row, idx) => ({
        '#': idx + 1,
        'Tipo de Producción': row.nombreProduccion,
        'Descripción': row.descripcion || '',
        'Fecha': formatFecha(row.fecha),
        'Usuario': row.get_user?.name || row.user?.name || '',
        'Institución': row.get_user?.institucion || row.user?.institucion || '',
        'Provincia': row.get_user?.provincia || row.user?.provincia || '',
        'Distrito': row.get_user?.distrito || row.user?.distrito || '',
        'UGEL': row.get_user?.ugel || row.user?.ugel || '',
    }));

    const ws = XLSX.utils.json_to_sheet(dataToExport);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Producción de Textos');
    XLSX.writeFile(wb, `${filename}_${new Date().toISOString().slice(0, 10)}.xlsx`);
}

function confirmDelete(produccion) {
    Swal.fire({
        title: '¿Está seguro de eliminar esta producción de textos infantiles?',
        text: `Se eliminará "${produccion.nombreProduccion}".`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup: 'rounded-2xl shadow-2xl border border-slate-100 p-6',
            title: 'text-base sm:text-lg font-bold text-slate-800',
            htmlContainer: 'text-xs sm:text-sm text-slate-600 mt-1',
            actions: 'flex items-center justify-center gap-3 mt-5',
            confirmButton: 'px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold hover:bg-rose-700 active:bg-rose-800 shadow-md shadow-rose-600/30 transition-all text-xs sm:text-sm cursor-pointer border-0 outline-none',
            cancelButton: 'px-5 py-2.5 rounded-xl bg-slate-700 text-white font-semibold hover:bg-slate-600 active:bg-slate-500 border border-slate-600 shadow-xs transition-all text-xs sm:text-sm cursor-pointer outline-none',
        },
        buttonsStyling: false,
    }).then((result) => {
        if (result.isConfirmed) {
            ProduccionsService.destroy(produccion.id, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El registro ha sido eliminado con éxito.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'rounded-2xl shadow-2xl border border-slate-100 p-6',
                            title: 'text-base sm:text-lg font-bold text-slate-800',
                            htmlContainer: 'text-xs sm:text-sm text-slate-600',
                        },
                    });
                },
            });
        }
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Producción de Textos Infantiles" />

        <PageHeader
            title="Producción de Textos Infantiles"
            icon="NotebookPen"
            color="green"
            subtitle="Registra y consulta las producciones de textos infantiles"
        />

        <SectionTabs :tabs="tabs" color="green" />

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

        <CreateButton v-if="can.create" @click="openCreateModal">
            Nueva Producción
        </CreateButton>

        <BaseTable
            id="tabla-produccions"
            :columns="columns"
            :rows="produccions.data || []"
            :pagination="produccions"
            :search-value="localFilters.buscar"
            :sort-key="localFilters.sort"
            :sort-order="localFilters.direction"
            @sort="handleSort"
            search-placeholder="Buscar en todos los registros..."
            export-filename="produccion_textos_infantiles"
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de Producción</label>
                    <input
                        type="text"
                        v-model="localFilters.texto"
                        placeholder="Ej. Cuento infantil"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Fecha</label>
                    <input
                        type="date"
                        v-model="localFilters.fecha"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    />
                </div>
            </template>

            <template #row="{ row }">
                <td class="px-4 py-3 font-semibold text-blue-600 align-middle min-w-[200px]">
                    {{ row.nombreProduccion }}
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
                    <div class="inline-flex items-center justify-center gap-1.5">
                        <button
                            type="button"
                            @click="openDetail(row)"
                            class="inline-flex items-center justify-center p-1.5 bg-slate-600 hover:bg-slate-700 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                            title="Ver detalle"
                        >
                            <Eye class="w-3.5 h-3.5" />
                        </button>
                        <button
                            v-if="can.edit"
                            type="button"
                            @click="openEditModal(row)"
                            class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                            title="Editar"
                        >
                            <Pencil class="w-3.5 h-3.5" />
                        </button>
                        <button
                            v-if="can.destroy"
                            type="button"
                            @click="confirmDelete(row)"
                            class="inline-flex items-center justify-center p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                            title="Eliminar"
                        >
                            <Trash2 class="w-3.5 h-3.5" />
                        </button>
                    </div>
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

        <ProduccionFormModal
            :show="formModal.show"
            :produccion="formModal.produccion"
            @close="closeFormModal"
        />

        <DetailModal
            :show="detalle.show"
            :title="detalle.title"
            subtitle="Detalle de la producción de textos"
            icon="NotebookPen"
            color="green"
            :fields="detalle.fields"
            @close="closeDetail"
        />
    </AppLayout>
</template>
