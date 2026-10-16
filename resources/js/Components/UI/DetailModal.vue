<script setup>
import { computed } from 'vue';
import BaseModal from './BaseModal.vue';

/**
 * Modal genérico de solo lectura para ver el detalle de un registro.
 *
 * Reutilizable en cualquier módulo: se le pasa un título, un ícono/color
 * (paleta de BaseModal) y la lista de campos a mostrar. Así las tablas quedan
 * compactas (pocas columnas) y los datos extra viven aquí.
 *
 * Cada campo admite:
 *   { label: 'Descripción', value: '...', full?: true, type?: 'chips' }
 *  - full: el campo ocupa todo el ancho (útil para textos largos).
 *  - type: 'chips' dibuja el valor (array) como etiquetas; con cualquier
 *    otro tipo/omitido se pinta como texto.
 */
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Detalle',
    },
    subtitle: {
        type: String,
        default: '',
    },
    icon: {
        type: [String, Object],
        default: 'Folder',
    },
    color: {
        type: String,
        default: 'blue',
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    fields: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const campos = computed(() => (props.fields || []).filter((f) => f && f.label));

function esLista(field) {
    return field.type === 'chips' || Array.isArray(field.value);
}

function valorTexto(field) {
    const v = field.value;
    if (v === null || v === undefined || v === '') return '—';
    return String(v);
}
</script>

<template>
    <BaseModal
        :show="show"
        :title="title"
        :subtitle="subtitle"
        :icon="icon"
        :color="color"
        :max-width="maxWidth"
        :is-form="false"
        @close="emit('close')"
    >
        <div v-if="campos.length" class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
            <div
                v-for="(field, index) in campos"
                :key="index"
                :class="field.full ? 'sm:col-span-2' : ''"
            >
                <p class="text-[10px] font-black uppercase tracking-wide text-slate-400 mb-1">
                    {{ field.label }}
                </p>

                <!-- Lista de etiquetas (roles, etc.) -->
                <div v-if="esLista(field)" class="flex flex-wrap gap-1.5">
                    <span
                        v-for="(item, i) in (field.value || [])"
                        :key="i"
                        class="inline-block px-2 py-0.5 text-[11px] font-bold rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200"
                    >
                        {{ item }}
                    </span>
                    <span v-if="!(field.value || []).length" class="text-xs text-slate-400">—</span>
                </div>

                <!-- Valor de texto -->
                <p
                    v-else
                    class="text-xs sm:text-sm text-slate-700 font-medium whitespace-pre-line break-words m-0"
                >
                    {{ valorTexto(field) }}
                </p>
            </div>
        </div>

        <p v-else class="text-xs text-slate-400 text-center py-8 m-0">
            Sin detalles para mostrar.
        </p>

        <template #footer>
            <div class="flex items-center justify-end gap-2.5 max-sm:w-full">
                <button
                    type="button"
                    @click="emit('close')"
                    class="max-sm:flex-1 inline-flex items-center justify-center px-4 py-2.5 sm:py-2 text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-100 active:bg-slate-200 border border-slate-300 rounded-xl transition-colors shadow-2xs cursor-pointer"
                >
                    Cerrar
                </button>
            </div>
        </template>
    </BaseModal>
</template>
