<script setup>
import { computed, ref } from 'vue';
import * as d3 from 'd3';
import huanuco from '@/data/huanuco-provincias.json';
import { useThemeColors } from '@/Composables/useThemeColors';

/**
 * Mapa coroplético de las 11 provincias de Huánuco (una por UGEL de la DRE).
 *
 * El componente solo pinta: recibe la matriz `módulo × UGEL` que arma
 * `DashboardService::participacionPorUgel()` y, según el módulo activo, colorea
 * cada provincia con el % de docentes con al menos un registro. Al pasar el
 * mouse se muestra el nombre de la UGEL y su detalle.
 */
const props = defineProps({
    data: {
        type: Object,
        default: null,
    },
});

const { statsConfig } = useThemeColors();

// Lienzo base del SVG; con `preserveAspectRatio` se adapta al ancho disponible.
const WIDTH = 620;
const HEIGHT = 520;

const wrapper = ref(null);
const hover = ref(null);

const moduloActivo = ref(props.data?.modulos?.[0]?.key || 'agenda');

/** Normaliza un nombre (mayúsculas, sin tildes) para cruzar provincia ↔ UGEL. */
function normalizar(valor) {
    return (valor || '')
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toUpperCase()
        .trim();
}

/** provincia normalizada → fila de UGEL (id, nombre, docentes). */
const ugelPorProvincia = computed(() => {
    const mapa = new Map();

    for (const u of props.data?.ugels || []) {
        mapa.set(normalizar(u.provincia || u.nombre), u);
    }

    return mapa;
});

/** Módulo activo con sus porcentajes por UGEL. */
const moduloData = computed(() =>
    (props.data?.modulos || []).find((m) => m.key === moduloActivo.value) || null
);

/** Umbrales coherentes con `StatsModal.vue` / `HierarchySummary.vue`. */
const colorScale = d3
    .scaleThreshold()
    .domain([25, 50, 75])
    .range(['#F43F5E', '#F59E0B', '#3B82F6', '#10B981']);

const pathGenerator = d3.geoPath(d3.geoMercator().fitSize([WIDTH, HEIGHT], huanuco));

/** Una entrada por provincia, ya resuelta contra los datos de la UGEL. */
const regiones = computed(() => {
    const porUgel = moduloData.value?.por_ugel || {};

    return huanuco.features.map((feature) => {
        const ugel = ugelPorProvincia.value.get(normalizar(feature.properties.provincia));
        const dato = ugel ? porUgel[ugel.id] : null;
        const porcentaje = dato ? dato.porcentaje : null;

        return {
            d: pathGenerator(feature),
            provincia: feature.properties.provincia,
            ugel,
            conRegistro: dato?.con_registro ?? 0,
            docentes: ugel?.docentes ?? 0,
            porcentaje,
            fill: porcentaje == null ? '#E2E8F0' : colorScale(porcentaje),
        };
    });
});

const leyenda = [
    { color: '#10B981', label: '≥ 75%' },
    { color: '#3B82F6', label: '50–75%' },
    { color: '#F59E0B', label: '25–50%' },
    { color: '#F43F5E', label: '< 25%' },
    { color: '#E2E8F0', label: 'Sin docentes' },
];

function onMove(event, region) {
    if (!wrapper.value) return;

    const rect = wrapper.value.getBoundingClientRect();

    hover.value = {
        ...region,
        x: event.clientX - rect.left,
        y: event.clientY - rect.top,
    };
}

function onLeave() {
    hover.value = null;
}
</script>

<template>
    <div ref="wrapper" class="relative">
        <!-- Selector de módulo -->
        <div class="flex flex-wrap items-center gap-1.5 mb-3">
            <button
                v-for="m in data?.modulos || []"
                :key="m.key"
                type="button"
                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-colors cursor-pointer"
                :style="moduloActivo === m.key
                    ? { backgroundColor: statsConfig[m.key]?.barColor || '#475569', color: '#fff' }
                    : { backgroundColor: statsConfig[m.key]?.circleBg || '#F1F5F9', color: statsConfig[m.key]?.pctColor || '#475569' }"
                @click="moduloActivo = m.key"
            >
                {{ statsConfig[m.key]?.short || m.nombre }}
            </button>
        </div>

        <div class="flex flex-col lg:flex-row gap-5 items-start justify-center">
            <!-- Mapa -->
            <div class="relative w-full max-w-[360px] mx-auto lg:mx-0 shrink-0">
                <svg
                    :viewBox="`0 0 ${WIDTH} ${HEIGHT}`"
                    preserveAspectRatio="xMidYMid meet"
                    class="w-full h-auto select-none"
                    role="img"
                    aria-label="Mapa de participación por UGEL en Huánuco"
                >
                    <path
                        v-for="region in regiones"
                        :key="region.provincia"
                        :d="region.d"
                        :fill="region.fill"
                        stroke="#ffffff"
                        stroke-width="1.2"
                        class="transition-[fill,filter] duration-200 cursor-pointer"
                        :class="{ 'brightness-90': hover?.provincia === region.provincia }"
                        @mousemove="onMove($event, region)"
                        @mouseleave="onLeave"
                    />
                </svg>

                <!-- Tooltip (solo al pasar el mouse) -->
                <div
                    v-if="hover"
                    class="pointer-events-none absolute z-10 -translate-x-1/2 -translate-y-[calc(100%+10px)] rounded-xl bg-slate-900/95 text-white px-3 py-2 shadow-lg text-left whitespace-nowrap"
                    :style="{ left: `${hover.x}px`, top: `${hover.y}px` }"
                >
                    <p class="m-0 text-xs font-bold leading-tight">
                        {{ hover.ugel?.nombre || 'Sin UGEL asignada' }}
                    </p>
                    <p class="m-0 text-[11px] text-white/70 leading-tight">
                        {{ hover.provincia }}
                    </p>
                    <p v-if="hover.porcentaje != null" class="m-0 mt-1 text-[11px] leading-tight">
                        <span class="font-extrabold" :style="{ color: hover.fill }">{{ hover.porcentaje }}%</span>
                        · {{ hover.conRegistro.toLocaleString() }}/{{ hover.docentes.toLocaleString() }} docentes
                    </p>
                    <p v-else class="m-0 mt-1 text-[11px] text-white/60 leading-tight">
                        Sin docentes registrados
                    </p>
                </div>
            </div>

            <!-- Leyenda -->
            <div class="shrink-0 w-full lg:w-44">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-2">
                    {{ moduloData?.nombre || 'Participación' }}
                </p>
                <ul class="m-0 p-0 list-none space-y-1.5">
                    <li
                        v-for="item in leyenda"
                        :key="item.label"
                        class="flex items-center gap-2 text-[11px] text-slate-600 font-medium"
                    >
                        <span
                            class="inline-block w-3.5 h-3.5 rounded-sm border border-black/5 shrink-0"
                            :style="{ backgroundColor: item.color }"
                        ></span>
                        {{ item.label }}
                    </li>
                </ul>
                <p class="mt-3 mb-0 text-[10px] text-slate-400 leading-snug">
                    % de docentes con al menos un registro en el módulo seleccionado.
                </p>
            </div>
        </div>
    </div>
</template>
