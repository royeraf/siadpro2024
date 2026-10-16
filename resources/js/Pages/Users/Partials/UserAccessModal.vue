<script setup>
import { ref, computed, watch } from 'vue';
import { Loader2, AlertCircle, ShieldCheck, Info } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import BaseModal from '@/Components/UI/BaseModal.vue';
import PermissionMatrix from '@/Components/Security/PermissionMatrix.vue';
import { UserAccessService } from '@/Services/security';

const props = defineProps({
    show: { type: Boolean, default: false },
    user: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const loading = ref(false);
const submitting = ref(false);
const error = ref('');
const data = ref(null);

const selectedRoles = ref([]);
const selectedDirect = ref([]);

const actionTypes = {
    registro: { label: 'Registro (CRUD)' },
    general: { label: 'General / DRE' },
    ugel: { label: 'UGEL' },
    director: { label: 'Institución' },
    seguridad: { label: 'Seguridad' },
    especial: { label: 'Especial' },
};

const modules = computed(() => Object.values(data.value?.access?.modules || {}));

// Candado dinámico: un permiso queda bloqueado si CUALQUIERA de los roles
// marcados arriba lo otorga. Al desmarcar el rol, el permiso se desbloquea.
const lockedPermissions = computed(() => {
    const map = data.value?.role_permissions || {};
    const locked = new Set();

    selectedRoles.value.forEach((roleName) => {
        (map[roleName] || []).forEach((permission) => locked.add(permission));
    });

    return Array.from(locked);
});

watch(() => props.show, async (visible) => {
    if (!visible || !props.user) return;

    loading.value = true;
    error.value = '';
    data.value = null;

    try {
        const payload = await UserAccessService.get(props.user.id);
        data.value = payload;
        selectedRoles.value = [...(payload.access?.roles || [])];
        selectedDirect.value = [...(payload.access?.direct || [])];
    } catch (e) {
        error.value = 'No se pudieron cargar los accesos del usuario.';
    } finally {
        loading.value = false;
    }
});

function toggleRole(name) {
    const idx = selectedRoles.value.indexOf(name);
    if (idx === -1) selectedRoles.value.push(name);
    else selectedRoles.value.splice(idx, 1);
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

function submit() {
    if (!props.user || loading.value) return;

    submitting.value = true;
    error.value = '';

    UserAccessService.update(props.user.id, {
        roles: selectedRoles.value,
        permissions: selectedDirect.value,
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            emit('saved');
            emit('close');
            toast('success', page?.props?.flash?.success || 'Accesos actualizados correctamente.');
        },
        onError: (serverErrors) => {
            const message = Object.values(serverErrors || {}).flat().join(' ')
                || 'No se pudieron guardar los accesos.';
            error.value = message;
            toast('error', message);
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
        title="Accesos del Usuario"
        :subtitle="user ? `${user.name}${user.cargo ? ' · ' + user.cargo : ''}` : ''"
        :icon="ShieldCheck"
        color="blue"
        max-width="4xl"
        :submitting="submitting || loading"
        :submit-disabled="loading"
        submit-text="Guardar Accesos"
        cancel-text="Cancelar"
        @close="emit('close')"
        @submit="submit"
    >
        <div v-if="loading" class="flex flex-col items-center justify-center py-12 text-slate-500">
            <Loader2 class="w-6 h-6 animate-spin mb-2" />
            <span class="text-xs font-semibold">Cargando accesos…</span>
        </div>

        <div v-else-if="data" class="space-y-5">
            <!-- Roles -->
            <section>
                <div class="flex items-center gap-2 mb-2">
                    <ShieldCheck class="w-4 h-4 text-indigo-500" />
                    <h4 class="text-xs font-black text-slate-700 uppercase tracking-wide mb-0">Roles</h4>
                </div>

                <div v-if="data.assignable_roles.length === 0" class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-3 py-3">
                    No hay roles disponibles para asignar.
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <label
                        v-for="role in data.assignable_roles"
                        :key="role.name"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl border cursor-pointer transition-colors"
                        :class="selectedRoles.includes(role.name)
                            ? 'bg-indigo-50/80 border-indigo-200'
                            : 'bg-white border-slate-200 hover:bg-slate-50'"
                    >
                        <input
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            :checked="selectedRoles.includes(role.name)"
                            @change="toggleRole(role.name)"
                        />
                        <span class="text-xs font-semibold text-slate-700 truncate">{{ role.label }}</span>
                        <code class="ml-auto text-[10px] text-slate-400">{{ role.name }}</code>
                    </label>
                </div>
            </section>

            <!-- Permisos directos -->
            <section>
                <div class="flex items-center gap-2 mb-2">
                    <h4 class="text-xs font-black text-slate-700 uppercase tracking-wide mb-0">Permisos directos</h4>
                    <span class="text-[10px] text-slate-400 font-semibold">(se suman a los de los roles)</span>
                </div>

                <div class="mb-3 p-2.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-[11px] flex items-start gap-2">
                    <Info class="w-3.5 h-3.5 shrink-0 mt-0.5" />
                    <span>
                        Los permisos con candado los otorgan los <b>roles marcados arriba</b>. Si desmarcas un rol,
                        sus permisos se desbloquean y podrás ajustarlos individualmente. Los demás se pueden otorgar
                        directamente a este usuario.
                    </span>
                </div>

                <PermissionMatrix
                    v-model="selectedDirect"
                    :modules="modules"
                    :action-types="actionTypes"
                    :locked="lockedPermissions"
                />
            </section>

            <p v-if="error" class="text-xs text-rose-600 font-semibold flex items-center">
                <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                {{ error }}
            </p>
        </div>

        <p v-else-if="error" class="text-xs text-rose-600 font-semibold flex items-center">
            <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
            {{ error }}
        </p>
    </BaseModal>
</template>
