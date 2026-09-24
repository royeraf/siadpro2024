<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Pencil, Trash2, CheckCircle, AlertCircle } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import CreateButton from '@/Components/UI/CreateButton.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';
import FileViewerModal from '@/Components/UI/FileViewerModal.vue';
import InformeFormModal from './Partials/InformeFormModal.vue';

const props = defineProps({
    informes: {
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
});

const columns = computed(() => {
    const cols = [
        { key: 'nombreInforme', label: 'Nombre de la Biblioteca', sortable: false },
        { key: 'descripcion', label: 'Descripción', sortable: false },
        { key: 'fecha', label: 'Fecha', width: '110px', sortable: false },
        { key: 'documento', label: 'Documento', width: '90px', align: 'center', class: 'no-export' },
        { key: 'usuario', label: 'Usuario', sortable: false },
        { key: 'institucion', label: 'Institución', sortable: false },
        { key: 'provincia', label: 'Provincia', sortable: false },
        { key: 'distrito', label: 'Distrito', sortable: false },
        { key: 'ugel', label: 'UGEL', width: '90px', sortable: false },
    ];
    if (props.can?.edit || props.can?.destroy) {
        cols.push({ key: 'opciones', label: 'Opciones', width: '100px', align: 'center', class: 'no-export' });
    }
    return cols;
});

const formModal = reactive({
    show: false,
    informe: null,
});

function openCreateModal() {
    formModal.informe = null;
    formModal.show = true;
}

function openEditModal(informe) {
    formModal.informe = informe;
    formModal.show = true;
}

function closeFormModal() {
    formModal.show = false;
    formModal.informe = null;
}

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
        const parts = dateStr.split('-');
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
    localFilters.year = String(new Date().getFullYear());
    localFilters.texto = '';
    localFilters.fecha = '';
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

function handleExportExcel() {
    const dataToExport = (props.informes.data || []).map((row, idx) => ({
        '#': idx + 1,
        'Nombre de la Biblioteca': row.nombreInforme,
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
    XLSX.utils.book_append_sheet(wb, ws, 'Biblioteca del Aula');
    XLSX.writeFile(wb, `biblioteca_aula_${new Date().toISOString().slice(0, 10)}.xlsx`);
}

function confirmDelete(informe) {
    Swal.fire({
        title: '¿Está seguro de eliminar esta biblioteca de aula?',
        text: `Se eliminará "${informe.nombreInforme}".`,
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
            router.delete(`/informes/${informe.id}`, {
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
        <Head title="Biblioteca del Aula" />

        <PageHeader
            title="Biblioteca del Aula"
            icon="BookOpen"
            color="orange"
            subtitle="Registra y consulta la biblioteca del aula"
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

        <CreateButton v-if="can.create" @click="openCreateModal">
            Nueva Biblioteca
        </CreateButton>

        <BaseTable
            id="tabla-informes"
            :columns="columns"
            :rows="informes.data || []"
            :pagination="informes"
            :search-value="localFilters.buscar"
            search-placeholder="Buscar en todos los registros..."
            export-filename="biblioteca_aula"
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombre de la Biblioteca</label>
                    <input
                        type="text"
                        v-model="localFilters.texto"
                        placeholder="Ej. Biblioteca de aula"
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
                    {{ row.get_user?.name || row.user?.name || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle min-w-[180px]">
                    {{ row.get_user?.institucion || row.user?.institucion || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.get_user?.provincia || row.user?.provincia || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.get_user?.distrito || row.user?.distrito || '-' }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 align-middle">
                    {{ row.get_user?.ugel || row.user?.ugel || '-' }}
                </td>
                <td v-if="can.edit || can.destroy" class="px-4 py-3 text-center no-export whitespace-nowrap align-middle">
                    <div class="inline-flex items-center justify-center gap-1.5">
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

        <InformeFormModal
            :show="formModal.show"
            :informe="formModal.informe"
            @close="closeFormModal"
        />
    </AppLayout>
</template>
