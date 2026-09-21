<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Megaphone, CheckCircle, AlertCircle } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import CreateButton from '@/Components/UI/CreateButton.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';
import FileViewerModal from '@/Components/UI/FileViewerModal.vue';
import AccionFormModal from './Partials/AccionFormModal.vue';

const props = defineProps({
    accions: {
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
    const cols = [
        { key: 'nombreAccion', label: 'Nombre de la Acción', sortable: false },
        { key: 'descripcion', label: 'Descripción', sortable: false },
        { key: 'lugar', label: 'Lugar', sortable: false },
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

// ── Estado del Modal de Formulario (Crear / Editar) ──
const formModal = reactive({
    show: false,
    accion: null,
});

function openCreateModal() {
    formModal.accion = null;
    formModal.show = true;
}

function openEditModal(accion) {
    formModal.accion = accion;
    formModal.show = true;
}

function closeFormModal() {
    formModal.show = false;
    formModal.accion = null;
}

// Fecha actual en formato local "YYYY-MM-DD"
const today = computed(() => {
    const d = new Date();
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
});

// ── Estado del Visor de Archivos ──
const viewer = reactive({
    show: false,
    url: '',
    name: '',
    downloadUrl: '',
});

function openViewer(accion) {
    if (!accion.enlace) return;
    const fileName = accion.enlace.split('/').pop() || 'documento';
    viewer.name = fileName;
    viewer.url = `/visor/stream?path=${encodeURIComponent(accion.enlace)}`;
    viewer.downloadUrl = `/accions/${accion.id}/download`;
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
        // Fallback
    }
    return dateStr;
}

// ── Manejo de Filtros y Búsqueda con Inertia ──
function applyFilters() {
    loading.value = true;
    router.get('/accions', {
        texto: localFilters.texto || undefined,
        lugar: localFilters.lugar || undefined,
        fecha: localFilters.fecha || undefined,
        buscar: localFilters.buscar || undefined,
        per_page: localFilters.per_page !== 10 ? localFilters.per_page : undefined,
    }, {
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
    localFilters.texto = '';
    localFilters.lugar = '';
    localFilters.fecha = '';
    localFilters.buscar = '';
    applyFilters();
}

function handlePageChange(newPage) {
    loading.value = true;
    router.get('/accions', {
        ...localFilters,
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

// ── Exportar a Excel (Cliente) ──
function handleExportExcel() {
    const dataToExport = (props.accions.data || []).map((row, idx) => ({
        '#': idx + 1,
        'Nombre de la Acción': row.nombreAccion,
        'Descripción': row.descripcion || '',
        'Lugar': row.lugar || '',
        'Fecha': formatDate(row.fecha),
        'Usuario': row.get_user?.name || row.user?.name || '',
        'Institución': row.get_user?.institucion || row.user?.institucion || '',
        'Provincia': row.get_user?.provincia || row.user?.provincia || '',
        'Distrito': row.get_user?.distrito || row.user?.distrito || '',
        'UGEL': row.get_user?.ugel || row.user?.ugel || '',
    }));

    const ws = XLSX.utils.json_to_sheet(dataToExport);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Acciones');
    XLSX.writeFile(wb, `acciones_sensibilizacion_${new Date().toISOString().slice(0, 10)}.xlsx`);
}

// ── Confirmar Eliminación ──
function confirmDelete(accion) {
    Swal.fire({
        title: '¿Está seguro de eliminar esta acción?',
        text: `Se eliminará "${accion.nombreAccion}".`,
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
            router.delete(`/accions/${accion.id}`, {
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
        <Head title="Acción de Sensibilización" />

        <!-- ══ CABECERA DE LA PÁGINA ══ -->
        <PageHeader 
            title="Acción de Sensibilización" 
            icon="Megaphone" 
            color="yellow"
            subtitle="Registra y consulta las acciones pedagógicas de sensibilización"
        />

        <!-- ══ PESTAÑAS DE ALCANCE (MIS REGISTROS / UGEL / GENERAL / DIRECTOR) ══ -->
        <SectionTabs :tabs="tabs" color="yellow" />

        <!-- ══ MENSAJES FLASH ══ -->
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

        <!-- ══ BOTÓN NUEVA ACCIÓN (DEBAJO DE PESTAÑAS - VERDE) ══ -->
        <CreateButton v-if="can.create" @click="openCreateModal">
            Nueva Acción
        </CreateButton>

        <!-- ══ TABLA BASE REUTILIZABLE ══ -->
        <BaseTable
            id="tabla-acciones"
            :columns="columns"
            :rows="accions.data || []"
            :pagination="accions"
            :search-value="localFilters.buscar"
            search-placeholder="Buscar en todos los registros..."
            export-filename="acciones_sensibilizacion"
            :loading="loading"
            @search="handleSearch"
            @clear="handleClearFilters"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
            @export="handleExportExcel"
        >
            <!-- Filtros adicionales específicos del módulo -->
            <template #filters>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nombre de la Acción</label>
                    <input 
                        type="text" 
                        v-model="localFilters.texto" 
                        placeholder="Ej. Taller de sensibilización"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lugar</label>
                    <input 
                        type="text" 
                        v-model="localFilters.lugar" 
                        placeholder="Lugar de la acción"
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

            <!-- Renderizado de cada fila (Estilo Bootstrap con desplazamiento horizontal en móvil) -->
            <template #row="{ row }">
                <td class="px-4 py-3 font-semibold text-blue-600 align-middle min-w-[200px]">
                    {{ row.nombreAccion }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle min-w-[220px] max-w-[340px]">
                    {{ row.descripcion || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap min-w-[120px]">
                    {{ row.lugar || '-' }}
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
                        <i :class="row.documento || 'fas fa-file-alt'" :style="{ color: row.color || '#6366F1', fontSize: '18px' }"></i>
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

        <!-- ══ MODAL VISOR DE ARCHIVOS ══ -->
        <FileViewerModal 
            :show="viewer.show"
            :url="viewer.url"
            :name="viewer.name"
            :download-url="viewer.downloadUrl"
            @close="viewer.show = false"
        />

        <!-- ══ MODAL FORMULARIO DE ACCIÓN (CREAR / EDITAR) ══ -->
        <AccionFormModal 
            :show="formModal.show"
            :accion="formModal.accion"
            @close="closeFormModal"
        />
    </AppLayout>
</template>
