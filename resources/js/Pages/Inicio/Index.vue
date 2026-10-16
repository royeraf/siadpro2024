<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import HeroWelcome from '@/Components/Dashboard/HeroWelcome.vue';
import ParticipationBar from '@/Components/Dashboard/ParticipationBar.vue';
import ActivityCard from '@/Components/Dashboard/ActivityCard.vue';
import { useThemeColors } from '@/Composables/useThemeColors';

const props = defineProps({
    modulos: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name || 'Docente');

const { resolveTheme } = useThemeColors();
</script>

<template>
    <AppLayout>
        <Head title="Inicio" />

        <!-- ══ SECCIÓN HERO: TARJETA DE BIENVENIDA ══ -->
        <HeroWelcome :user-name="userName" />

        <!-- ══ SECCIÓN PARTICIPACIÓN POR MÓDULO (BARRA HORIZONTAL) ══ -->
        <ParticipationBar :initial-stats="stats" />

        <!-- ══ SECCIÓN MÓDULOS DE APRENDIZAJE ══ -->
        <div class="mb-4">
            <h4 class="font-extrabold text-slate-800 text-xl tracking-tight mb-1">
                Módulos de Aprendizaje
            </h4>
            <p class="text-slate-500 text-xs font-medium mb-0">
                Selecciona una actividad para comenzar a trabajar
            </p>
        </div>

        <!-- Cuadrícula de tarjetas de actividades -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mb-6">
            <ActivityCard
                v-for="modulo in modulos"
                :key="modulo.href"
                :title="modulo.text"
                :description="modulo.description"
                :href="modulo.href"
                :theme="resolveTheme(modulo)"
            />
        </div>
    </AppLayout>
</template>
