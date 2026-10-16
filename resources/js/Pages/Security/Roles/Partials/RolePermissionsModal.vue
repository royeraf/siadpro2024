<script setup>
import { ref, computed, watch } from 'vue';
import { Lock, KeyRound } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import BaseModal from '@/Components/UI/BaseModal.vue';
import PermissionMatrix from '@/Components/Security/PermissionMatrix.vue';
import { RolesService } from '@/Services/security';

const props = defineProps({
    show: { type: Boolean, default: false },
    role: { type: Object, default: null },
    modules: { type: Array, default: () => [] },
    actionTypes: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close', 'saved']);

const selected = ref([]);
const submitting = ref(false);

const isProtected = computed(() => !!props.role?.protected);

watch(() => props.show, (visible) => {
    if (!visible) return;
    selected.value = [...(props.role?.permissions || [])];
});

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

function submit() {
    if (isProtected.value) {
        emit('close');
        return;
    }

    submitting.value = true;
    RolesService.update(props.role.id, {
        label: props.role.label,
        permissions: selected.value,
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            emit('saved');
            emit('close');
            toast('success', page?.props?.flash?.success || 'Permisos del rol actualizados correctamente.');
        },
        onError: (serverErrors) => {
            toast('error', Object.values(serverErrors || {}).flat().join(' ')
                || 'No se pudieron guardar los permisos.');
        },
        onFinish: () => {
            submitting.value = false;
        },
    });
}
</script>

<template>
    <BaseModal
        :show="show"
        title="Permisos del Rol"
        :subtitle="role ? `${role.label} (${role.name})` : ''"
        :icon="KeyRound"
        color="blue"
        max-width="4xl"
        :submitting="submitting"
        :submit-text="isProtected ? 'Cerrar' : 'Guardar Permisos'"
        cancel-text="Cancelar"
        @close="emit('close')"
        @submit="submit"
    >
        <div v-if="isProtected" class="mb-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2">
            <Lock class="w-4 h-4 shrink-0 mt-0.5" />
            <span>
                El rol <b>Admin</b> es protegido y tiene acceso total. Su matriz de permisos la
                gobierna el catálogo canónico y no se puede editar desde aquí.
            </span>
        </div>

        <PermissionMatrix
            v-model="selected"
            :modules="modules"
            :action-types="actionTypes"
            :disabled="isProtected"
        />
    </BaseModal>
</template>
