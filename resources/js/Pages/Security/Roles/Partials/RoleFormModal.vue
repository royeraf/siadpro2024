<script setup>
import { ref, computed, watch } from 'vue';
import { AlertCircle, ShieldCheck, Info } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import BaseModal from '@/Components/UI/BaseModal.vue';
import { RolesService } from '@/Services/security';

const props = defineProps({
    show: { type: Boolean, default: false },
    role: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const isEditing = computed(() => !!props.role?.id);
// Rol del catálogo: su `name` técnico (EspecDRE) es fijo; solo cambia el label.
const isCatalog = computed(() => isEditing.value && !!props.role?.in_catalog);

const label = ref('');
const submitting = ref(false);
const error = ref('');

watch(() => props.show, (visible) => {
    if (!visible) return;
    label.value = props.role?.label || props.role?.name || '';
    error.value = '';
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
    const clean = String(label.value || '').trim();

    if (!clean) {
        error.value = 'El nombre del rol es obligatorio.';
        return;
    }
    if (!/^[A-Za-z0-9 _\-\.]+$/.test(clean)) {
        error.value = 'El nombre solo puede contener letras, números, espacios, guiones y puntos.';
        return;
    }

    submitting.value = true;
    error.value = '';

    const request = {
        preserveScroll: true,
        onSuccess: (page) => {
            emit('saved');
            emit('close');
            toast('success', page?.props?.flash?.success
                || (isEditing.value ? 'Rol actualizado correctamente.' : 'Rol creado correctamente.'));
        },
        onError: (serverErrors) => {
            const message = Object.values(serverErrors || {}).flat().join(' ')
                || 'No se pudo guardar el rol.';
            error.value = message;
            toast('error', message);
        },
        onFinish: () => {
            submitting.value = false;
        },
    };

    if (isEditing.value) {
        // Solo el nombre visible: no se envían permisos para no pisar la matriz.
        RolesService.update(props.role.id, { label: clean }, request);
    } else {
        RolesService.store({ label: clean }, request);
    }
}
</script>

<template>
    <BaseModal
        :show="show"
        :title="isEditing ? 'Editar Rol' : 'Nuevo Rol'"
        :subtitle="isEditing ? role?.name : 'Crea un rol y asígnale permisos después'"
        :icon="ShieldCheck"
        color="indigo"
        max-width="lg"
        :submitting="submitting"
        :submit-text="isEditing ? 'Guardar' : 'Crear Rol'"
        cancel-text="Cancelar"
        @close="emit('close')"
        @submit="submit"
    >
        <div>
            <label for="role-label" class="block text-xs font-bold text-slate-700 mb-1">
                Nombre del rol <span class="text-rose-500">*</span>
            </label>
            <input
                id="role-label"
                type="text"
                v-model="label"
                maxlength="191"
                placeholder="Ej: Coordinador Regional"
                class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                :class="error ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300'"
                @keydown.enter.prevent="submit"
            />
            <p v-if="error" class="mt-1.5 text-xs text-rose-600 font-semibold flex items-center">
                <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                {{ error }}
            </p>
            <p v-else class="mt-1.5 text-[11px] text-slate-400">
                Es el nombre que verás en las listas. No puede repetirse.
            </p>

            <div
                v-if="isCatalog"
                class="mt-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-500 flex items-start gap-2"
            >
                <Info class="w-3.5 h-3.5 shrink-0 mt-0.5 text-slate-400" />
                <span>
                    Identificador técnico: <code class="text-slate-700">{{ role.name }}</code>.
                    Es un rol del sistema y no se puede renombrar (solo su nombre visible).
                </span>
            </div>
        </div>
    </BaseModal>
</template>
