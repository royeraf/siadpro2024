<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { DifusionsService } from '@/Services/difusion';
import { Pencil, Trash2, CheckCircle, AlertCircle, Eye } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import CreateButton from '@/Components/UI/CreateButton.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';
import DetailModal from '@/Components/UI/DetailModal.vue';
import FileViewerModal from '@/Components/UI/FileViewerModal.vue';
import ArchivoIcono from '@/Components/UI/ArchivoIcono.vue';
import DifusionFormModal from './Partials/DifusionFormModal.vue';

const props = defineProps({
    difusions: {
        type: Object,
        required: true,
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
    texto: props.filters.texto || '',
    lugar: props.filters.lugar || '',
    fecha: props.filters.fecha || '',
    buscar: props.filters.buscar || '',
    per_page: props.filters.per_page || 10,
});

const columns = computed(() => {
    return [
        { key: 'nombreAccion', label: 'Nombre de la Acción', sortable: false },
        { key: 'fecha', label: 'Fecha', width: '110px', sortable: false },
        { key: 'documento', label: 'Documento', width: '90px', align: 'center', class: 'no-export' },
        { key: 'opciones', label: 'Acciones', width: '130px', align: 'center', class: 'no-export' },
    ];
});

const formModal = reactive({
    show: false,
    difusion: null,
});

function openCreateModal() {
    formModal.difusion = null;
    formModal.show = true;
}

function openEditModal(difusion) {
    formModal.difusion = difusion;
    formModal.show = true;
}

function closeFormModal() {
    formModal.show = false;
    formModal.difusion = null;
}

// ── Modal de Detalle (solo lectura) ──
const detalle = reactive({
    show: false,
    title: '',
    fields: [],
});

function openDetail(row) {
    detalle.title = row.nombreAccion || 'Detalle de la difusión';
    detalle.fields = [
        { label: 'Descripción', value: row.descripcion, full: true },
        { label: 'Lugar', value: row.lugar },
        { label: 'Fecha', value: formatDate(row.fecha) },
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

const today = computed(() => {
    const d = new Date();
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
});

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
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }
    } catch (e) {
    }
    return dateStr;
}

function applyFilters() {
    loading.value = true;
    DifusionsService.list({
        texto: localFilters.texto || undefined,
        lugar: localFilters.lugar || undefined,
        fecha: localFilters.fecha || undefined,
        buscar: localFilters.buscar || undefined,
        per_page: localFilters.per_page !== 10 ? localFilters.per_page : undefined,
    }, {
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
    localFilters.texto = '';
    localFilters.lugar = '';
    localFilters.fecha = '';
    localFilters.buscar = '';
    applyFilters();
}

function handlePageChange(newPage) {
    loading.value = true;
    DifusionsService.list({
        ...localFilters,
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
    const dataToExport = (props.difusions.data || []).map((row, idx) => ({
        '#': idx + 1,
        'Nombre de la Acción': row.nombreAccion,
        'Lugar': row.lugar || '',
        'Descripción': row.descripcion || '',
        'Fecha': formatDate(row.fecha),
        'Usuario': row.get_user?.name || row.user?.name || '',
        'Institución': row.get_user?.institucion || row.user?.institucion || '',
        'Provincia': row.get_user?.provincia || row.user?.provincia || '',
        'Distrito': row.get_user?.distrito || row.user?.distrito || '',
        'UGEL': row.get_user?.ugel || row.user?.ugel || '',
    }));

    const ws = XLSX.utils.json_to_sheet(dataToExport);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Difusiones');
    XLSX.writeFile(wb, `acciones_difusion_${new Date().toISOString().slice(0, 10)}.xlsx`);
}

function confirmDelete(difusion) {
    Swal.fire({
        title: '¿Está seguro de eliminar esta acción?',
        text: `Se eliminará "${difusion.nombreAccion}".`,
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
            cancelButton: 'px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 active:bg-slate-300 hover:text-slate-900 border border-slate-300 shadow-xs transition-all text-xs sm:text-sm cursor-pointer outline-none',
        },
        buttonsStyling: false,
    }).then((result) => {
        if (result.isConfirmed) {
            DifusionsService.destroy(difusion.id, {
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
        <Head title="Acción de Difusión" />

        <PageHeader 
            title="Acción de Difusión" 
            icon="Radio" 
            color="blue"
            subtitle="Registra y consulta las acciones de difusión y comunicación"
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

        <CreateButton v-if="can.create" @click="openCreateModal">
            Nueva Difusión
        </CreateButton>

        <BaseTable
            id="tabla-difusiones"
            :columns="columns"
            :rows="difusions.data || []"
            :pagination="difusions"
            :search-value="localFilters.buscar"
            search-placeholder="Buscar en todos los registros..."
            export-filename="acciones_difusion"
            :loading="loading"
            @search="handleSearch"
            @clear="handleClearFilters"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
            @export="handleExportExcel"
        >
            <template #filters>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombre de la Acción</label>
                    <input 
                        type="text" 
                        v-model="localFilters.texto" 
                        placeholder="Ej. Campaña radial"
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

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Fecha</label>
                    <input 
                        type="date" 
                        v-model="localFilters.fecha" 
                        :max="today"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    />
                </div>
            </template>

            <template #row="{ row }">
                <td class="px-4 py-3 font-semibold text-blue-600 align-middle min-w-[200px]">
                    {{ row.nombreAccion }}
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

        <DifusionFormModal 
            :show="formModal.show"
            :difusion="formModal.difusion"
            @close="closeFormModal"
        />

        <DetailModal
            :show="detalle.show"
            :title="detalle.title"
            subtitle="Detalle de la difusión"
            icon="Radio"
            color="blue"
            :fields="detalle.fields"
            @close="closeDetail"
        />
    </AppLayout>
</template>
