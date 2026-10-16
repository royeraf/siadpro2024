<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Shield, ShieldCheck, Pencil, KeyRound, Trash2, Users, CheckCircle, AlertCircle, Lock, Eye } from 'lucide-vue-next';
import Swal from 'sweetalert2';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import CreateButton from '@/Components/UI/CreateButton.vue';
import DetailModal from '@/Components/UI/DetailModal.vue';
import { RolesService } from '@/Services/security';
import RoleFormModal from './Partials/RoleFormModal.vue';
import RolePermissionsModal from './Partials/RolePermissionsModal.vue';
import PermissionMap from '@/Components/Security/PermissionMap.vue';

const props = defineProps({
    roles: { type: Array, default: () => [] },
    modules: { type: Array, default: () => [] },
    actionTypes: { type: Object, default: () => ({}) },
    can: { type: Object, default: () => ({}) },
});

const page = usePage();

const activeTab = ref('roles');
const tabs = [
    { key: 'roles', label: 'Roles', icon: ShieldCheck },
    { key: 'permissions', label: 'Catálogo de Permisos', icon: KeyRound },
];

const totalPermisos = computed(() =>
    props.modules.reduce((sum, m) => sum + m.permissions.length, 0)
);

const formModal = reactive({ show: false, role: null });
const permissionsModal = reactive({ show: false, role: null });
const detalle = reactive({ show: false, role: null });

// Mapa permiso → etiqueta legible, para el detalle del rol.
const labelByPermission = computed(() => {
    const map = {};
    props.modules.forEach((m) => (m.permissions || []).forEach((p) => {
        map[p.name] = p.label;
    }));
    return map;
});

const detalleFields = computed(() => {
    const role = detalle.role;
    if (!role) return [];

    return [
        { label: 'Nombre visible', value: role.label },
        { label: 'Identificador técnico', value: role.name },
        { label: 'Nivel', value: role.level },
        {
            label: 'Tipo',
            value: role.protected ? 'Protegido' : (role.in_catalog ? 'Estándar' : 'Personalizado'),
        },
        { label: 'Usuarios asignados', value: role.users_count },
        {
            label: `Permisos (${role.permissions.length})`,
            value: role.permissions.map((name) => labelByPermission.value[name] || name),
            type: 'chips',
            full: true,
        },
    ];
});

function openDetail(role) {
    detalle.role = role;
    detalle.show = true;
}

function closeDetail() {
    detalle.show = false;
    detalle.role = null;
}

function openCreate() {
    formModal.role = null;
    formModal.show = true;
}

function openEdit(role) {
    formModal.role = role;
    formModal.show = true;
}

function openPermissions(role) {
    permissionsModal.role = role;
    permissionsModal.show = true;
}

function closeForm() {
    formModal.show = false;
    formModal.role = null;
}

function closePermissions() {
    permissionsModal.show = false;
    permissionsModal.role = null;
}

function toast(icon, title) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        timer: icon === 'success' ? 2600 : 4000,
        timerProgressBar: true,
        showConfirmButton: false,
    });
}

function confirmDelete(role) {
    Swal.fire({
        title: '¿Eliminar rol?',
        html: `Se eliminará el rol <b>${role.label}</b> (<code>${role.name}</code>). Esta acción no se puede deshacer.`,
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
            confirmButton: 'px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold hover:bg-rose-700 shadow-md shadow-rose-600/30 transition-all text-xs sm:text-sm cursor-pointer border-0',
            cancelButton: 'px-5 py-2.5 rounded-xl bg-slate-700 text-white font-semibold hover:bg-slate-600 transition-all text-xs sm:text-sm cursor-pointer border-0',
        },
        buttonsStyling: false,
    }).then((result) => {
        if (!result.isConfirmed) return;

        RolesService.destroy(role.id, {
            preserveScroll: true,
            onSuccess: (page) => {
                // El backend puede rechazar el borrado (rol protegido o con
                // usuarios) redirigiendo con flash.error.
                const flashError = page?.props?.flash?.error;
                if (flashError) {
                    toast('error', flashError);
                } else {
                    toast('success', page?.props?.flash?.success || 'Rol eliminado correctamente.');
                }
            },
            onError: () => toast('error', 'No se pudo eliminar el rol.'),
        });
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Roles y Permisos" />

        <PageHeader
            title="Roles y Permisos"
            :icon="Shield"
            color="indigo"
            subtitle="Gestiona los roles del sistema y consulta el catálogo de permisos"
        />

        <div class="mb-4 max-w-full">
            <div class="inline-flex max-w-full overflow-x-auto pb-0.5">
                <ul class="flex items-center p-1 list-none rounded-lg bg-slate-100 min-w-max gap-1" role="list">
                    <li v-for="tab in tabs" :key="tab.key">
                        <button
                            type="button"
                            role="tab"
                            :aria-selected="activeTab === tab.key ? 'true' : 'false'"
                            class="flex items-center justify-center gap-1.5 px-3.5 py-1.5 text-xs sm:text-sm mb-0 transition-all ease-in-out border-0 rounded-md cursor-pointer"
                            :class="activeTab === tab.key
                                ? 'bg-indigo-600 text-white shadow-xs font-bold'
                                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium'"
                            @click="activeTab = tab.key"
                        >
                            <component :is="tab.icon" class="w-3.5 h-3.5" />
                            {{ tab.label }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <template v-if="activeTab === 'roles'">
        <div
            v-if="page.props.flash?.success"
            class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between"
        >
            <div class="flex items-center">
                <CheckCircle class="w-4 h-4 mr-2 text-emerald-600 shrink-0" />
                <span>{{ page.props.flash.success }}</span>
            </div>
            <button type="button" @click="page.props.flash.success = null" class="text-emerald-500 hover:text-emerald-700 p-1">&times;</button>
        </div>

        <div
            v-if="page.props.flash?.error"
            class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between"
        >
            <div class="flex items-center">
                <AlertCircle class="w-4 h-4 mr-2 text-rose-600 shrink-0" />
                <span>{{ page.props.flash.error }}</span>
            </div>
            <button type="button" @click="page.props.flash.error = null" class="text-rose-500 hover:text-rose-700 p-1">&times;</button>
        </div>

        <CreateButton v-if="can.create" @click="openCreate">Nuevo Rol</CreateButton>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-500">Rol</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-500 text-center">Usuarios</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-500 text-center">Permisos</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-500 text-center">Tipo</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-500 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="role in roles" :key="role.id" class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-3 align-middle">
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                        :class="role.protected ? 'bg-rose-100 text-rose-600' : 'bg-indigo-100 text-indigo-600'"
                                    >
                                        <ShieldCheck class="w-4 h-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 mb-0 truncate">{{ role.label }}</p>
                                        <code class="text-[10px] text-slate-400">{{ role.name }}</code>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center align-middle">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600">
                                    <Users class="w-3.5 h-3.5 text-slate-400" />
                                    {{ role.users_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center align-middle">
                                <span class="inline-block px-2 py-0.5 text-xs font-bold rounded bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ role.permissions.length }} / {{ totalPermisos }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center align-middle">
                                <span
                                    v-if="role.protected"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded bg-rose-50 text-rose-700 border border-rose-200"
                                >
                                    <Lock class="w-3 h-3" /> Protegido
                                </span>
                                <span
                                    v-else-if="!role.in_catalog"
                                    class="inline-block px-2 py-0.5 text-[10px] font-bold rounded bg-amber-50 text-amber-700 border border-amber-200"
                                >
                                    Personalizado
                                </span>
                                <span v-else class="text-[10px] text-slate-400 font-semibold">Estándar</span>
                            </td>
                            <td class="px-4 py-3 text-center align-middle">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    <button
                                        type="button"
                                        @click="openDetail(role)"
                                        class="inline-flex items-center justify-center p-1.5 bg-slate-600 hover:bg-slate-700 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                                        title="Ver detalle"
                                    >
                                        <Eye class="w-3.5 h-3.5" />
                                    </button>
                                    <button
                                        v-if="can.edit"
                                        type="button"
                                        @click="openPermissions(role)"
                                        class="inline-flex items-center justify-center p-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                                        title="Editar permisos"
                                    >
                                        <KeyRound class="w-3.5 h-3.5" />
                                    </button>
                                    <button
                                        v-if="can.edit && !role.protected"
                                        type="button"
                                        @click="openEdit(role)"
                                        class="inline-flex items-center justify-center p-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                                        title="Editar nombre"
                                    >
                                        <Pencil class="w-3.5 h-3.5" />
                                    </button>
                                    <button
                                        v-if="can.destroy && !role.protected"
                                        type="button"
                                        @click="confirmDelete(role)"
                                        class="inline-flex items-center justify-center p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow-xs transition-colors border-0 cursor-pointer"
                                        title="Eliminar rol"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </template>

        <template v-else>
            <PermissionMap
                :modules="modules"
                :roles="roles"
                :action-types="actionTypes"
            />
        </template>

        <RoleFormModal
            :show="formModal.show"
            :role="formModal.role"
            @close="closeForm"
            @saved="closeForm"
        />

        <RolePermissionsModal
            :show="permissionsModal.show"
            :role="permissionsModal.role"
            :modules="modules"
            :action-types="actionTypes"
            @close="closePermissions"
            @saved="closePermissions"
        />

        <DetailModal
            :show="detalle.show"
            :title="detalle.role ? detalle.role.label : 'Detalle del rol'"
            subtitle="Información del rol"
            :icon="ShieldCheck"
            color="indigo"
            max-width="2xl"
            :fields="detalleFields"
            @close="closeDetail"
        />
    </AppLayout>
</template>
