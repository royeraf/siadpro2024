<script setup>
import { CirclePlus, Plus } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { isSpaPath } from '@/Composables/useSpaRoutes';

const props = defineProps({
    href: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['click']);

// Estilos compartidos por el botón inline (escritorio) y el flotante (móvil).
const fabClasses = 'sm:hidden fixed right-5 bottom-6 z-40 inline-flex items-center justify-center gap-2 h-14 max-w-[calc(100vw-2.5rem)] pl-4 pr-5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-bold shadow-lg shadow-emerald-600/30 transition-colors border-0 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 cursor-pointer';
const inlineClasses = 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition-colors border-0 focus:outline-hidden focus:ring-2 focus:ring-emerald-400 focus:ring-offset-1 cursor-pointer';
</script>

<template>
    <!-- Escritorio/tablet: botón inline junto al encabezado de la tabla -->
    <div class="hidden sm:flex justify-start mb-4">
        <Link
            v-if="href && isSpaPath(href)"
            :href="href"
            :class="[inlineClasses, 'text-decoration-none']"
        >
            <CirclePlus class="w-4 h-4 shrink-0" />
            <span><slot /></span>
        </Link>
        <a
            v-else-if="href"
            :href="href"
            :class="[inlineClasses, 'text-decoration-none']"
        >
            <CirclePlus class="w-4 h-4 shrink-0" />
            <span><slot /></span>
        </a>
        <button
            v-else
            type="button"
            :class="inlineClasses"
            @click="emit('click', $event)"
        >
            <CirclePlus class="w-4 h-4 shrink-0" />
            <span><slot /></span>
        </button>
    </div>

    <!-- Móvil: botón flotante, igual que en Agenda de Lectura › Mis registros -->
    <Link
        v-if="href && isSpaPath(href)"
        :href="href"
        :class="[fabClasses, 'text-decoration-none']"
    >
        <Plus class="w-5 h-5 shrink-0" />
        <span class="truncate min-w-0"><slot /></span>
    </Link>
    <a
        v-else-if="href"
        :href="href"
        :class="[fabClasses, 'text-decoration-none']"
    >
        <Plus class="w-5 h-5 shrink-0" />
        <span class="truncate min-w-0"><slot /></span>
    </a>
    <button
        v-else
        type="button"
        :class="fabClasses"
        @click="emit('click', $event)"
    >
        <Plus class="w-5 h-5 shrink-0" />
        <span class="truncate min-w-0"><slot /></span>
    </button>
</template>
