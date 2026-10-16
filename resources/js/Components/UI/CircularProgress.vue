<script setup>
import { computed } from 'vue';

const props = defineProps({
    /** Porcentaje ya enviado (0-100). */
    value: {
        type: Number,
        default: 0,
    },
    /** Diámetro del SVG en píxeles. */
    size: {
        type: Number,
        default: 112,
    },
    stroke: {
        type: Number,
        default: 9,
    },
    trackClass: {
        type: String,
        default: 'stroke-slate-200',
    },
    barClass: {
        type: String,
        default: 'stroke-blue-600',
    },
    /** Texto bajo el porcentaje (p. ej. "1.2 MB de 5MB"). */
    caption: {
        type: String,
        default: '',
    },
});

const percentage = computed(() => Math.min(100, Math.max(0, Math.round(props.value) || 0)));

const radius = computed(() => (props.size - props.stroke) / 2);
const circumference = computed(() => 2 * Math.PI * radius.value);
const offset = computed(() => circumference.value * (1 - percentage.value / 100));
const center = computed(() => props.size / 2);
</script>

<template>
    <div
        class="relative inline-flex items-center justify-center shrink-0"
        :style="{ width: `${size}px`, height: `${size}px` }"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="100"
        :aria-valuenow="percentage"
    >
        <svg
            :width="size"
            :height="size"
            :viewBox="`0 0 ${size} ${size}`"
            class="-rotate-90"
            aria-hidden="true"
        >
            <circle
                :cx="center"
                :cy="center"
                :r="radius"
                fill="none"
                :stroke-width="stroke"
                :class="trackClass"
            />
            <circle
                :cx="center"
                :cy="center"
                :r="radius"
                fill="none"
                stroke-linecap="round"
                :stroke-width="stroke"
                :class="barClass"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="offset"
                style="transition: stroke-dashoffset 180ms ease-out"
            />
        </svg>

        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="text-xl font-black text-slate-800 tabular-nums leading-none">
                {{ percentage }}<span class="text-sm font-bold">%</span>
            </span>
            <span v-if="caption" class="text-[10px] font-semibold text-slate-500 mt-1 tabular-nums">
                {{ caption }}
            </span>
        </div>
    </div>
</template>
