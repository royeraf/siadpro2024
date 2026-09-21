<script setup>
import { CirclePlus } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['click']);
</script>

<template>
    <div class="flex justify-start mb-4">
        <Link
            v-if="href && (href.startsWith('/') || href.includes('/accions'))"
            :href="href"
            class="inline-flex items-center justify-center gap-2 max-sm:w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition-colors border-0 text-decoration-none focus:outline-hidden focus:ring-2 focus:ring-emerald-400 focus:ring-offset-1 cursor-pointer"
        >
            <CirclePlus class="w-4 h-4 shrink-0" />
            <span><slot /></span>
        </Link>
        <a
            v-else-if="href"
            :href="href"
            class="inline-flex items-center justify-center gap-2 max-sm:w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition-colors border-0 text-decoration-none focus:outline-hidden focus:ring-2 focus:ring-emerald-400 focus:ring-offset-1 cursor-pointer"
        >
            <CirclePlus class="w-4 h-4 shrink-0" />
            <span><slot /></span>
        </a>
        <button
            v-else
            type="button"
            @click="emit('click', $event)"
            class="inline-flex items-center justify-center gap-2 max-sm:w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition-colors border-0 focus:outline-hidden focus:ring-2 focus:ring-emerald-400 focus:ring-offset-1 cursor-pointer"
        >
            <CirclePlus class="w-4 h-4 shrink-0" />
            <span><slot /></span>
        </button>
    </div>
</template>
