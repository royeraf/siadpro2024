<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { InstitucionsService } from '@/Services/institucion';
import { Pencil, Trash2, CheckCircle, AlertCircle } from 'lucide-vue-next';
import Swal from 'sweetalert2';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import CreateButton from '@/Components/UI/CreateButton.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';
import InstitucionFormModal from './Partials/InstitucionFormModal.vue';

const props = defineProps({
    institucions: {
        type: Object,
        required: true,
    },
    listaUgels: {
        type: Array,
        default: () => [],
    },
    listaNiveles: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    can: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const loading = ref(false);

const localFilters = reactive({
    institucion: props.filters.institucion || '',
    codModular: props.filters.codModular || '',
    ugels: props.filters.ugels || '',
    nivel: props.filters.nivel || '',
    buscar: props.filters.buscar || '',
    per_page: props.filters.per_page || 15,
});

const columns = computed(() => {
    const cols = [
        { key: 'id', label: 'ID', width: '70px', align: 'center' },
        { key: 'nomInstitucion', label: 'Institución' },
        { key: 'codModular', label: 'Cód. Modular', width: '140px', align: 'center' },
        { key: 'nivel', label: 'Nivel', width: '130px' },
        { key: 'provincia', label: 'Provincia' },
        { key: 'distrito', label: 'Distrito' },
        { key: 'centropoblado', label: 'Centro Poblado' },
        { key: 'ugel', label: 'UGEL', width: '120px' },
    ];
    if (props.can?.edit || props.can?.destroy) {
        cols.push({ key: 'opciones', label: 'Opciones', width: '110px', align: 'center', class: 'no-export' });
    }
    return cols;
});

// Descarga server-side: conserva el .xls con BOM de
// InstitucionController::exportInstituciones con los filtros vigentes.
const exportUrl = computed(() => InstitucionsService.exportUrl({
    buscar: localFilters.buscar,
    institucion: localFilters.institucion,
    codModular: localFilters.codModular,
    ugels: localFilters.ugels,
    nivel: localFilters.nivel,
}));

const formModal = reactive({
    show: false,
    institucion: null,
});

function openCreateModal() {
    formModal.institucion = null;
    formModal.show = true;
}

function openEditModal(row) {
    formModal.institucion = row;
    formModal.show = true;
}

function closeFormModal() {
    formModal.show = false;
    formModal.institucion = null;
}

function getCleanParams() {
    const params = {};
    if (localFilters.institucion) params.institucion = localFilters.institucion;
    if (localFilters.codModular) params.codModular = localFilters.codModular;
    if (localFilters.ugels) params.ugels = localFilters.ugels;
    if (localFilters.nivel) params.nivel = localFilters.nivel;
    if (localFilters.buscar) params.buscar = localFilters.buscar;
    if (localFilters.per_page && String(localFilters.per_page) !== '15') {
        params.per_page = localFilters.per_page;
    }
    return params;
}

function applyFilters() {
    loading.value = true;
    InstitucionsService.list(getCleanParams(), {
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
    localFilters.institucion = '';
    localFilters.codModular = '';
    localFilters.ugels = '';
    localFilters.nivel = '';
    localFilters.buscar = '';
    applyFilters();
}

function handlePageChange(newPage) {
    loading.value = true;
    InstitucionsService.list({
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

// ── Confirmar Eliminación (baja lógica: estado = 0) ──
function confirmDelete(row) {
    Swal.fire({
        title: '¿Está seguro de eliminar esta institución?',
        text: `Se eliminará "${row.nomInstitucion}".`,
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
            InstitucionsService.destroy(row.id, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'La institución ha sido eliminada con éxito.',
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
        <Head title="Instituciones" />

        <PageHeader
            title="Listado de Instituciones"
            icon="Landmark"
            color="indigo"
            subtitle="Administra el registro de instituciones educativas"
        />

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
            Nueva Institución
        </CreateButton>

        <BaseTable
            id="tabla-instituciones"
            :columns="columns"
            :rows="institucions.data || []"
            :pagination="institucions"
            :search-value="localFilters.buscar"
            search-placeholder="Buscar por nombre, código modular..."
            :export-url="exportUrl"
            export-filename="instituciones"
            :loading="loading"
            @search="handleSearch"
            @clear="handleClearFilters"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
        >
            <template #filters>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Institución</label>
                    <input
                        type="text"
                        v-model="localFilters.institucion"
                        placeholder="Ej. Illathupa"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cód. Modular</label>
                    <input
                        type="text"
                        v-model="localFilters.codModular"
                        placeholder="Ej. 0234567"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">UGEL</label>
                    <select
                        v-model="localFilters.ugels"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">-- Todas las UGEL --</option>
                        <option v-for="ugel in listaUgels" :key="ugel" :value="ugel">{{ ugel }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nivel</label>
                    <select
                        v-model="localFilters.nivel"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">-- Todos los niveles --</option>
                        <option v-for="nivel in listaNiveles" :key="nivel" :value="nivel">{{ nivel }}</option>
                    </select>
                </div>
            </template>

            <template #row="{ row }">
                <td class="px-4 py-3 text-center font-bold text-gray-900 align-middle">
                    {{ row.id }}
                </td>
                <td class="px-4 py-3 font-semibold text-indigo-600 align-middle min-w-[220px]">
                    {{ row.nomInstitucion }}
                </td>
                <td class="px-4 py-3 text-center align-middle">
                    <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-indigo-100 text-indigo-800 rounded">
                        {{ row.codModular }}
                    </span>
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.nivel || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.provincia || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.distrito || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle">
                    {{ row.centropoblado || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.ugel || '-' }}
                </td>
                <td
                    v-if="can.edit || can.destroy"
                    class="px-4 py-3 text-center no-export whitespace-nowrap align-middle"
                >
                    <div class="inline-flex items-center justify-center gap-1.5">
                        <button
                            v-if="can.edit"
                            type="button"
                            @click="openEditModal(row)"
                            class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                            title="Editar institución"
                        >
                            <Pencil class="w-3.5 h-3.5" />
                        </button>
                        <button
                            v-if="can.destroy"
                            type="button"
                            @click="confirmDelete(row)"
                            class="inline-flex items-center justify-center p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                            title="Eliminar institución"
                        >
                            <Trash2 class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </td>
            </template>
        </BaseTable>

        <InstitucionFormModal
            :show="formModal.show"
            :institucion="formModal.institucion"
            :lista-ugels="listaUgels"
            @close="closeFormModal"
        />
    </AppLayout>
</template>
