<script setup>
import { Check, Minus, Lock } from 'lucide-vue-next';

/**
 * Mapa de solo lectura: cada permiso (agrupado por módulo) con una columna por
 * rol, marcando qué rol tiene cada permiso. Se usa como pestaña dentro de
 * "Roles y Permisos".
 */
const props = defineProps({
    modules: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    actionTypes: { type: Object, default: () => ({}) },
});

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

function roleHas(perm, roleName) {
    return (perm.roles_actual || []).includes(roleName);
}
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[720px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-wider text-slate-500 sticky left-0 bg-slate-50 z-10 min-w-[240px]">
                            Permiso
                        </th>
                        <th
                            v-for="role in roles"
                            :key="role.name"
                            class="px-3 py-3 text-[10px] font-black uppercase tracking-wider text-slate-500 text-center whitespace-nowrap"
                        >
                            <span class="inline-flex items-center gap-1">
                                <Lock v-if="role.protected" class="w-3 h-3 text-rose-500" />
                                {{ role.name }}
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="module in modules" :key="module.key">
                        <tr class="bg-indigo-50/50 border-y border-indigo-100">
                            <td class="px-4 py-2 text-[10px] font-black uppercase tracking-wider text-indigo-700 sticky left-0 bg-indigo-50/90 z-10">
                                {{ module.label }}
                            </td>
                            <td :colspan="roles.length" class="px-3 py-2 text-[10px] text-indigo-400 font-semibold">
                                {{ module.permissions.length }} permisos
                            </td>
                        </tr>
                        <tr
                            v-for="perm in module.permissions"
                            :key="perm.name"
                            class="border-b border-slate-100 hover:bg-slate-50/60 transition-colors"
                        >
                            <td class="px-4 py-2 sticky left-0 bg-white z-10">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-700">{{ perm.label }}</span>
                                    <span
                                        class="shrink-0 text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded border"
                                        :class="actionClass(perm.action)"
                                    >
                                        {{ actionLabel(perm.action) }}
                                    </span>
                                </div>
                                <code class="text-[10px] text-slate-400">{{ perm.name }}</code>
                            </td>
                            <td
                                v-for="role in roles"
                                :key="role.name"
                                class="px-3 py-2 text-center align-middle"
                            >
                                <span
                                    class="inline-flex items-center justify-center w-5 h-5 rounded-full"
                                    :class="roleHas(perm, role.name)
                                        ? 'bg-emerald-100 text-emerald-600'
                                        : 'bg-slate-50 text-slate-300'"
                                >
                                    <Check v-if="roleHas(perm, role.name)" class="w-3 h-3" />
                                    <Minus v-else class="w-3 h-3" />
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>
