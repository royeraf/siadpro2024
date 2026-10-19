<script setup>
import { Plus } from 'lucide-vue-next';

defineProps({
    label: {
        type: String,
        default: 'Agregar',
    },
    ariaLabel: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['click']);
</script>

<template>
    <button
        type="button"
        :aria-label="ariaLabel || label"
        :title="ariaLabel || label"
        class="inline-flex items-center justify-center gap-2 h-14 pl-4 pr-5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-bold shadow-lg shadow-emerald-600/30 transition-colors cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2"
        @click="emit('click')"
    >
        <Plus class="w-5 h-5 shrink-0" />
        <span>{{ label }}</span>
    </button>
</template>
