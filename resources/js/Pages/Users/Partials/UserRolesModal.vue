<script setup>
import { ref, watch, computed } from 'vue';
import { UsersService } from '@/Services/users';
import { AlertCircle, ShieldCheck } from 'lucide-vue-next';
import BaseModal from '@/Components/UI/BaseModal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        default: null,
    },
    roles: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'saved']);

const submitting = ref(false);
const error = ref('');
const selected = ref([]);

const userRoleIds = computed(() => (props.user?.roles || []).map((r) => r.id));

watch(() => props.show, (visible) => {
    if (!visible) return;
    error.value = '';
    // Solo los roles que el servidor permitió asignar (jerarquía) se marcan:
    // si el usuario tiene un rol que ya no puede otorgar, no se preselecciona.
    selected.value = props.roles
        .filter((role) => userRoleIds.value.includes(role.id))
        .map((role) => role.id);
});

function toggle(id) {
    const idx = selected.value.indexOf(id);
    if (idx === -1) {
        selected.value.push(id);
    } else {
        selected.value.splice(idx, 1);
    }
    error.value = '';
}

function submit() {
    if (!props.user) return;
    submitting.value = true;
    error.value = '';

    // Payload SOLO de roles: es lo que detecta UserController::update()
    // (`has('roles') && !has('name')`) para hacer el sync.
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('roles_form', '1');
    selected.value.forEach((id) => formData.append('roles[]', String(id)));

    UsersService.update(props.user.id, formData, {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
        onError: (serverErrors) => {
            error.value = Object.values(serverErrors || {}).join(' ');
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
        title="Asignar Roles"
        :subtitle="user ? `Usuario: ${user.name}` : ''"
        icon="Users"
        color="blue"
        max-width="lg"
        :submitting="submitting"
        submit-text="Guardar Roles"
        cancel-text="Cancelar"
        @close="emit('close')"
        @submit="submit"
    >
        <div class="space-y-3">
            <p class="text-xs text-slate-500 leading-relaxed">
                Marque los roles que desea otorgar. Según su jerarquía, solo puede asignar
                sus mismos niveles o inferiores.
            </p>

            <div v-if="roles.length === 0" class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-3 py-3">
                No hay roles disponibles para asignar.
            </div>

            <div class="max-h-72 overflow-y-auto rounded-xl border border-slate-200 divide-y divide-slate-100">
                <label
                    v-for="role in roles"
                    :key="role.id"
                    class="flex items-center gap-3 px-3.5 py-2.5 cursor-pointer transition-colors hover:bg-blue-50/60"
                    :class="selected.includes(role.id) ? 'bg-blue-50/80' : 'bg-white'"
                >
                    <input
                        type="checkbox"
                        :checked="selected.includes(role.id)"
                        @change="toggle(role.id)"
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                    />
                    <span class="flex items-center min-w-0">
                        <ShieldCheck
                            class="w-4 h-4 mr-2 shrink-0"
                            :class="selected.includes(role.id) ? 'text-blue-600' : 'text-slate-400'"
                        />
                        <span class="text-xs font-semibold text-slate-700 truncate">{{ role.name }}</span>
                    </span>
                </label>
            </div>

            <p v-if="error" class="text-xs text-rose-600 font-semibold flex items-center">
                <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                {{ error }}
            </p>
        </div>
    </BaseModal>
</template>
