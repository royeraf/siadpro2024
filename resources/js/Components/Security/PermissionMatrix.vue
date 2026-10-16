<script setup>
import { computed } from 'vue';
import { Check, Lock } from 'lucide-vue-next';

/**
 * Matriz de permisos agrupada por módulo.
 *
 * Modo interactivo (por defecto): cada permiso es un checkbox. `modelValue`
 * contiene los permisos marcados; `locked` son permisos que se muestran
 * marcados pero no se pueden desmarcar (p. ej. heredados de un rol).
 * `disabled` bloquea toda la matriz (rol protegido / solo lectura).
 */
const props = defineProps({
    modules: { type: Array, default: () => [] },
    actionTypes: { type: Object, default: () => ({}) },
    modelValue: { type: Array, default: () => [] },
    locked: { type: Array, default: () => [] },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const lockedSet = computed(() => new Set(props.locked));
const selectedSet = computed(() => new Set(props.modelValue));

function isLocked(name) {
    return props.disabled || lockedSet.value.has(name);
}

function isChecked(name) {
    return selectedSet.value.has(name) || lockedSet.value.has(name);
}

function toggle(name) {
    if (isLocked(name)) return;
    const next = new Set(props.modelValue);
    if (next.has(name)) next.delete(name);
    else next.add(name);
    emit('update:modelValue', Array.from(next));
}

function moduleCheckedCount(module) {
    return module.permissions.filter((p) => isChecked(p.name)).length;
}

function moduleAllChecked(module) {
    return module.permissions.length > 0 && moduleCheckedCount(module) === module.permissions.length;
}

function toggleModule(module) {
    if (props.disabled) return;
    const next = new Set(props.modelValue);
    const all = moduleAllChecked(module);
    module.permissions.forEach((p) => {
        if (lockedSet.value.has(p.name)) return;
        if (all) next.delete(p.name);
        else next.add(p.name);
    });
    emit('update:modelValue', Array.from(next));
}

const actionPalette = {
    registro: 'bg-blue-50 text-blue-700 border-blue-200',
    general: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    ugel: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    director: 'bg-amber-50 text-amber-700 border-amber-200',
    seguridad: 'bg-rose-50 text-rose-700 border-rose-200',
    especial: 'bg-cyan-50 text-cyan-700 border-cyan-200',
};

function actionLabel(action) {
    return props.actionTypes?.[action]?.label || action;
}

function actionClass(action) {
    return actionPalette[action] || 'bg-slate-100 text-slate-600 border-slate-200';
}
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="module in modules"
            :key="module.key"
            class="border border-slate-200 rounded-2xl overflow-hidden"
        >
            <div class="flex items-center justify-between gap-3 px-3.5 py-2.5 bg-slate-50 border-b border-slate-100">
                <div class="min-w-0">
                    <h4 class="text-xs font-black text-slate-700 uppercase tracking-wide truncate mb-0">
                        {{ module.label }}
                    </h4>
                    <p class="text-[10px] text-slate-400 font-semibold mb-0">
                        {{ moduleCheckedCount(module) }} / {{ module.permissions.length }} permisos
                    </p>
                </div>

                <button
                    v-if="!disabled"
                    type="button"
                    @click="toggleModule(module)"
                    class="shrink-0 text-[10px] font-bold px-2.5 py-1 rounded-lg border transition-colors cursor-pointer"
                    :class="moduleAllChecked(module)
                        ? 'bg-slate-800 text-white border-slate-800 hover:bg-slate-700'
                        : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-100'"
                >
                    {{ moduleAllChecked(module) ? 'Quitar todos' : 'Seleccionar todos' }}
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 px-3.5 py-3">
                <label
                    v-for="perm in module.permissions"
                    :key="perm.name"
                    class="flex items-center gap-2 py-1 group"
                    :class="isLocked(perm.name) ? 'cursor-not-allowed' : 'cursor-pointer'"
                >
                    <span
                        class="w-4 h-4 rounded border flex items-center justify-center shrink-0 transition-colors"
                        :class="[
                            isChecked(perm.name) ? 'bg-blue-600 border-blue-600' : 'bg-white border-slate-300',
                            isLocked(perm.name) && isChecked(perm.name) ? 'bg-slate-400 border-slate-400' : '',
                        ]"
                    >
                        <Check v-if="isChecked(perm.name)" class="w-3 h-3 text-white" />
                    </span>
                    <input
                        type="checkbox"
                        class="sr-only"
                        :checked="isChecked(perm.name)"
                        :disabled="isLocked(perm.name)"
                        @change="toggle(perm.name)"
                    />
                    <span class="text-xs text-slate-700 truncate">{{ perm.label }}</span>
                    <span
                        v-if="perm.action"
                        class="ml-auto shrink-0 text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded border"
                        :class="actionClass(perm.action)"
                    >
                        {{ actionLabel(perm.action) }}
                    </span>
                    <Lock v-if="lockedSet.has(perm.name)" class="w-3 h-3 text-slate-400 shrink-0" />
                </label>
            </div>
        </div>
    </div>
</template>
