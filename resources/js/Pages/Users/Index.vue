<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { UsersService } from '@/Services/users';
import { Pencil, Shield, UserCheck, UserX, CheckCircle, AlertCircle } from 'lucide-vue-next';
import Swal from 'sweetalert2';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import CreateButton from '@/Components/UI/CreateButton.vue';
import BaseTable from '@/Components/UI/BaseTable.vue';
import UserFormModal from './Partials/UserFormModal.vue';
import UserRolesModal from './Partials/UserRolesModal.vue';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    estado: {
        type: String,
        default: '1',
    },
    conteos: {
        type: Object,
        default: () => ({ 1: 0, 0: 0 }),
    },
    listaUgels: {
        type: Array,
        default: () => [],
    },
    listaInstituciones: {
        type: Array,
        default: () => [],
    },
    roles: {
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
    texto: props.filters.texto || '',
    cargos: props.filters.cargos || '',
    ugel: props.filters.ugel || '',
    institucion: props.filters.institucion || '',
    buscar: props.filters.buscar || '',
    per_page: props.filters.per_page || 10,
});

const columns = computed(() => {
    const cols = [
        { key: 'estado', label: 'Estado', width: '90px', align: 'center' },
        { key: 'id', label: 'ID', width: '70px', align: 'center' },
        { key: 'dni', label: 'DNI', width: '120px', align: 'center' },
        { key: 'name', label: 'Usuario' },
        { key: 'email', label: 'Correo' },
        { key: 'cargo', label: 'Cargo' },
        { key: 'institucion', label: 'Institución' },
        { key: 'ugel', label: 'UGEL', width: '120px' },
        { key: 'nivelinstitucion', label: 'Tipo de II.EE' },
        { key: 'provincia', label: 'Provincia' },
        { key: 'distrito', label: 'Distrito' },
    ];
    if (props.can?.edit || props.can?.destroy) {
        cols.push({ key: 'opciones', label: 'Opciones', width: '130px', align: 'center', class: 'no-export' });
    }
    return cols;
});

// Descarga server-side: conserva el mismo .xls con BOM que genera
// UserController::exportUsers (no XLSX en cliente, a diferencia de otros módulos).
const exportUrl = computed(() => UsersService.exportUrl({
    estado: props.estado,
    texto: localFilters.texto,
    cargos: localFilters.cargos,
    ugel: localFilters.ugel,
    institucion: localFilters.institucion,
    buscar: localFilters.buscar,
}));

const formModal = reactive({
    show: false,
    user: null,
});

const rolesModal = reactive({
    show: false,
    user: null,
});

function openCreateModal() {
    formModal.user = null;
    formModal.show = true;
}

function openEditModal(row) {
    formModal.user = row;
    formModal.show = true;
}

function closeFormModal() {
    formModal.show = false;
    formModal.user = null;
}

function openRolesModal(row) {
    rolesModal.user = row;
    rolesModal.show = true;
}

function closeRolesModal() {
    rolesModal.show = false;
    rolesModal.user = null;
}

function getCleanParams() {
    const params = { estado: props.estado };
    if (localFilters.texto) params.texto = localFilters.texto;
    if (localFilters.cargos) params.cargos = localFilters.cargos;
    if (localFilters.ugel) params.ugel = localFilters.ugel;
    if (localFilters.institucion) params.institucion = localFilters.institucion;
    if (localFilters.buscar) params.buscar = localFilters.buscar;
    if (localFilters.per_page && String(localFilters.per_page) !== '10') {
        params.per_page = localFilters.per_page;
    }
    return params;
}

function applyFilters() {
    loading.value = true;
    UsersService.list(getCleanParams(), {
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
    localFilters.cargos = '';
    localFilters.ugel = '';
    localFilters.institucion = '';
    localFilters.buscar = '';
    applyFilters();
}

function handlePageChange(newPage) {
    loading.value = true;
    UsersService.list({
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

function confirmToggleEstado(row) {
    const estaActivo = Number(row.estado) === 1;
    Swal.fire({
        title: estaActivo
            ? '¿Está seguro de desactivar a este usuario?'
            : '¿Está seguro de activar a este usuario?',
        text: estaActivo
            ? `Se desactivará a "${row.name}".`
            : `Se activará a "${row.name}".`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: estaActivo ? 'Sí, desactivar' : 'Sí, activar',
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
            UsersService.toggleEstado(row.id, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: 'El estado del usuario fue modificado con éxito.',
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
        <Head title="Usuarios" />

        <PageHeader
            title="Listado de Usuarios"
            icon="Users"
            color="blue"
            subtitle="Administra los usuarios, sus roles y su estado"
        />

        <SectionTabs :tabs="tabs" :color="estado === '1' ? 'green' : 'red'" />

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
            Nuevo Usuario
        </CreateButton>

        <BaseTable
            id="tabla-usuarios"
            :columns="columns"
            :rows="users.data || []"
            :pagination="users"
            :search-value="localFilters.buscar"
            search-placeholder="Buscar por DNI, nombre, correo, cargo, institución..."
            :export-url="exportUrl"
            :export-filename="estado === '1' ? 'usuarios_activos' : 'usuarios_inhabilitados'"
            :loading="loading"
            @search="handleSearch"
            @clear="handleClearFilters"
            @page-change="handlePageChange"
            @per-page-change="handlePerPageChange"
        >
            <template #filters>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">DNI</label>
                    <input
                        type="text"
                        v-model="localFilters.texto"
                        placeholder="Ingrese DNI"
                        maxlength="8"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cargo</label>
                    <input
                        type="text"
                        v-model="localFilters.cargos"
                        placeholder="Ingrese el cargo"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @keydown.enter.prevent="applyFilters"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">UGEL</label>
                    <select
                        v-model="localFilters.ugel"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">-- Todas las UGEL --</option>
                        <option v-for="ugel in listaUgels" :key="ugel" :value="ugel">{{ ugel }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Institución</label>
                    <select
                        v-model="localFilters.institucion"
                        class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        @change="applyFilters"
                    >
                        <option value="">-- Buscar institución --</option>
                        <option
                            v-for="inst in listaInstituciones"
                            :key="inst"
                            :value="inst"
                        >
                            {{ inst }}
                        </option>
                    </select>
                </div>
            </template>

            <template #row="{ row }">
                <td class="px-4 py-3 text-center align-middle whitespace-nowrap">
                    <span
                        v-if="Number(row.estado) === 1"
                        class="inline-block px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded"
                    >
                        Activo
                    </span>
                    <span
                        v-else
                        class="inline-block px-2 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800 rounded"
                    >
                        Inactivo
                    </span>
                </td>
                <td class="px-4 py-3 text-center font-bold text-gray-900 align-middle">
                    {{ row.id }}
                </td>
                <td class="px-4 py-3 text-center align-middle">
                    <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 rounded">
                        {{ row.dni }}
                    </span>
                </td>
                <td class="px-4 py-3 font-semibold text-blue-600 align-middle min-w-[200px]">
                    {{ row.name }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle min-w-[180px]">
                    {{ row.email }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle">
                    {{ row.cargo || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle min-w-[180px]">
                    {{ row.institucion || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.ugel || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle">
                    {{ row.nivelinstitucion || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.provincia || '-' }}
                </td>
                <td class="px-4 py-3 text-slate-600 align-middle whitespace-nowrap">
                    {{ row.distrito || '-' }}
                </td>
                <td
                    v-if="can.edit || can.destroy"
                    class="px-4 py-3 text-center no-export whitespace-nowrap align-middle"
                >
                    <div class="inline-flex items-center justify-center gap-1.5">
                        <button
                            v-if="can.edit"
                            type="button"
                            @click="openRolesModal(row)"
                            class="inline-flex items-center justify-center p-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                            title="Asignar rol"
                        >
                            <Shield class="w-3.5 h-3.5" />
                        </button>
                        <button
                            v-if="can.edit"
                            type="button"
                            @click="openEditModal(row)"
                            class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                            title="Editar datos"
                        >
                            <Pencil class="w-3.5 h-3.5" />
                        </button>
                        <button
                            v-if="can.edit"
                            type="button"
                            @click="confirmToggleEstado(row)"
                            class="inline-flex items-center justify-center p-1.5 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                            :class="Number(row.estado) === 1
                                ? 'bg-rose-600 hover:bg-rose-700'
                                : 'bg-emerald-600 hover:bg-emerald-700'"
                            :title="Number(row.estado) === 1 ? 'Desactivar' : 'Activar'"
                        >
                            <UserX v-if="Number(row.estado) === 1" class="w-3.5 h-3.5" />
                            <UserCheck v-else class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </td>
            </template>
        </BaseTable>

        <UserFormModal
            :show="formModal.show"
            :user="formModal.user"
            @close="closeFormModal"
        />

        <UserRolesModal
            :show="rolesModal.show"
            :user="rolesModal.user"
            :roles="roles"
            @close="closeRolesModal"
        />
    </AppLayout>
</template>
