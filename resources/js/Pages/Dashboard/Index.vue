<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import HierarchySummary from '@/Components/Dashboard/HierarchySummary.vue';
import ParticipationBar from '@/Components/Dashboard/ParticipationBar.vue';

/**
 * Dashboard de métricas, adaptado al perfil de quien entra:
 *
 *  - Admin / EspecDRE → resumen por UGEL.
 *  - EspecUGEL        → resumen por institución.
 *  - Director         → resumen por docente (cobertura y última actividad).
 *  - Docente / PC / PEC → solo participación por módulo; el bloque jerárquico no
 *    aplica porque no supervisan a nadie, por eso `jerarquia` llega `null` y
 *    `HierarchySummary` no se monta.
 *
 * Es deliberadamente solo de métricas. La bienvenida y los accesos rápidos a los
 * módulos viven en `Inicio/Index` (`/inicio`), que no se toca.
 */
const props = defineProps({
    stats: {
        type: Object,
        default: null,
    },
    jerarquia: {
        type: Object,
        default: null,
    },
});

/** El alcance se describe una sola vez, y alimenta el subtítulo de la cabecera. */
const subtitulo = computed(() => {
    if (props.jerarquia) return props.stats?.scope ? `Alcance: ${props.stats.scope}` : '';

    return props.stats?.scope
        ? `Alcance: ${props.stats.scope} · ${props.stats.totalDocentes?.toLocaleString() ?? 0} docente(s)`
        : '';
});
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <PageHeader
            title="Dashboard"
            :subtitle="subtitulo"
            icon="Users"
            color="indigo"
        />

        <!-- ══ RESUMEN POR TERRITORIO / JERARQUÍA (si el perfil supervisa) ══ -->
        <HierarchySummary :data="jerarquia" />

        <!-- ══ PARTICIPACIÓN POR MÓDULO ══ -->
        <ParticipationBar :initial-stats="stats" />
    </AppLayout>
</template>
