<script setup>
import { computed, ref } from 'vue';
import {
    Building2, GraduationCap, Users, ArrowUpDown, AlertTriangle,
} from 'lucide-vue-next';

/**
 * Resumen por territorio o jerarquía. El backend decide la forma de los datos:
 *
 *  - `porcentaje_tipo: 'participacion'` → % de docentes con al menos un
 *    registro (UGEL e institución).
 *  - `porcentaje_tipo: 'cobertura'`    → % de módulos en los que el docente
 *    tiene al menos un registro (nivel docente).
 *
 * El componente solo las pinta, para que el mismo bloque sirva a los tres
 * niveles sin duplicar vistas.
 */
const props = defineProps({
    data: {
        type: Object,
        default: null,
    },
});

const ORDENES = {
    cobertura: [
        { key: 'desc', label: 'Mayor cobertura' },
        { key: 'asc', label: 'Menor cobertura' },
        { key: 'alpha', label: 'Alfabético' },
    ],
    participacion: [
        { key: 'asc', label: 'Menor participación' },
        { key: 'desc', label: 'Mayor participación' },
        { key: 'alpha', label: 'Alfabético' },
    ],
};

const orden = ref('asc');

const nivel = computed(() => props.data?.nivel || '');
const esDocente = computed(() => props.data?.porcentaje_tipo === 'cobertura');

const opcionesOrden = computed(() => (esDocente.value ? ORDENES.cobertura : ORDENES.participacion));

const icono = computed(() => {
    if (nivel.value === 'UGEL') return GraduationCap;
    if (nivel.value === 'Institución') return Building2;
    return Users;
});

/** Etiqueta de la columna de porcentaje según el tipo de métrica. */
const etiquetaPorcentaje = computed(() => (esDocente.value ? 'Cobertura' : 'Participación'));

const columnas = computed(() => {
    // En el nivel territorial el % mide participación (docentes que registraron);
    // en el nivel docente mide cobertura (módulos donde registró).
    if (esDocente.value) {
        return [
            { key: 'registros', label: 'Registros', align: 'text-right' },
            { key: 'ultima', label: 'Última actividad', align: 'text-right' },
            { key: 'modulos', label: 'Módulos', align: 'text-right' },
            { key: 'porcentaje', label: etiquetaPorcentaje.value, align: 'text-left' },
        ];
    }

    return [
        { key: 'registros', label: 'Registros', align: 'text-right' },
        { key: 'con_registro', label: 'Docentes con registro', align: 'text-right' },
        { key: 'porcentaje', label: etiquetaPorcentaje.value, align: 'text-left' },
    ];
});

const filas = computed(() => {
    const lista = [...(props.data?.filas || [])];

    switch (orden.value) {
        case 'asc':
            return lista.sort((a, b) => (a.porcentaje - b.porcentaje) || String(a.label).localeCompare(b.label));
        case 'desc':
            return lista.sort((a, b) => (b.porcentaje - a.porcentaje) || String(a.label).localeCompare(b.label));
        default:
            return lista.sort((a, b) => String(a.label).localeCompare(b.label, 'es'));
    }
});

/**
 * Escala de color por rango, coherente con la que ya usa `StatsModal.vue`
 * para no tener dos lecturas distintas del mismo dato.
 */
function colorRango(porcentaje) {
    if (porcentaje >= 75) return { bar: '#10B981', badge: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
    if (porcentaje >= 50) return { bar: '#3B82F6', badge: 'bg-blue-50 text-blue-700 border-blue-200' };
    if (porcentaje >= 25) return { bar: '#F59E0B', badge: 'bg-amber-50 text-amber-700 border-amber-200' };
    return { bar: '#F43F5E', badge: 'bg-rose-50 text-rose-700 border-rose-200' };
}

/** "2025-06-03 14:22:10" -> "03/06/2025" */
function fecha(valor) {
    if (!valor) return '—';

    const [fechaParte] = String(valor).split(' ');

    const [anio, mes, dia] = fechaParte.split('-');

    return anio && mes && dia ? `${dia}/${mes}/${anio}` : valor;
}

/** El detalle de la columna "docentes" del nivel territorial. */
function modulosCubiertos(fila) {
    return fila.modulos != null ? `${fila.modulos}/${fila.modulos_totales}` : '—';
}

const resumen = computed(() => {
    if (!props.data) return null;

    return {
        docentes: props.data.total_docentes,
        registros: props.data.registros,
        conRegistro: props.data.con_registro,
        porcentaje: props.data.porcentaje_global,
    };
});
</script>

<template>
    <section
        v-if="data"
        class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6"
    >
        <!-- ══ ENCABEZADO ══ -->
        <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-3 min-w-0">
                <span
                    class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                    :style="{ backgroundColor: '#EEF2FF', color: '#4F46E5' }"
                >
                    <component :is="icono" class="w-5 h-5" />
                </span>

                <div class="min-w-0">
                    <h6 class="font-extrabold text-slate-800 text-sm mb-0.5 m-0">
                        {{ data.title }}
                    </h6>
                    <p class="text-slate-500 text-xs m-0">
                        <span class="font-semibold text-slate-600">
                            {{ resumen.conRegistro.toLocaleString() }}
                        </span>
                        de {{ resumen.docentes.toLocaleString() }} docentes han registrado
                        ({{ resumen.porcentaje }}%)
                    </p>
                </div>
            </div>

            <!-- Selector de orden: lo que más sirve es poner arriba lo que falla -->
            <div v-if="filas.length > 1" class="flex items-center gap-1.5 shrink-0">
                <ArrowUpDown class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                <button
                    v-for="opcion in opcionesOrden"
                    :key="opcion.key"
                    type="button"
                    class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-colors cursor-pointer"
                    :class="orden === opcion.key
                        ? 'bg-slate-800 text-white'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    @click="orden = opcion.key"
                >
                    {{ opcion.label }}
                </button>
            </div>
        </header>

        <!-- ══ MÉTRICAS GLOBALES ══ -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
            <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2.5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide m-0">Docentes</p>
                <p class="text-lg font-black text-slate-800 tabular-nums leading-tight m-0">
                    {{ resumen.docentes.toLocaleString() }}
                </p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2.5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide m-0">Con registro</p>
                <p class="text-lg font-black text-emerald-600 tabular-nums leading-tight m-0">
                    {{ resumen.conRegistro.toLocaleString() }}
                </p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2.5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide m-0">Registros</p>
                <p class="text-lg font-black text-slate-800 tabular-nums leading-tight m-0">
                    {{ resumen.registros.toLocaleString() }}
                </p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2.5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide m-0">{{ etiquetaPorcentaje }}</p>
                <p class="text-lg font-black tabular-nums leading-tight m-0" :style="{ color: colorRango(resumen.porcentaje).bar }">
                    {{ resumen.porcentaje }}%
                </p>
            </div>
        </div>

        <!-- ══ TABLA ══ -->
        <div class="border border-slate-100 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500">
                            <th
                                class="text-left font-bold px-3 py-2.5 whitespace-nowrap"
                                scope="col"
                            >
                                {{ data.dimension }}
                            </th>
                            <th
                                v-for="columna in columnas"
                                :key="columna.key"
                                class="font-bold px-3 py-2.5 whitespace-nowrap"
                                :class="columna.align"
                                scope="col"
                            >
                                {{ columna.label }}
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="fila in filas"
                            :key="fila.id ?? fila.label"
                            class="border-t border-slate-100 hover:bg-slate-50/70 transition-colors"
                        >
                            <td class="px-3 py-2.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="font-semibold text-slate-800 truncate m-0">{{ fila.label }}</span>
                                    <span
                                        v-if="fila.sublabel"
                                        class="text-[10px] font-bold text-slate-400 bg-slate-100 rounded-md px-1.5 py-0.5 shrink-0"
                                    >
                                        {{ fila.sublabel }}
                                    </span>
                                </div>
                                <span
                                    v-if="!esDocente && fila.docentes"
                                    class="block text-[10px] text-slate-400 font-medium m-0"
                                >
                                    {{ fila.docentes }} docente{{ fila.docentes === 1 ? '' : 's' }}
                                </span>
                            </td>

                            <td
                                v-for="columna in columnas"
                                :key="columna.key"
                                class="px-3 py-2.5 whitespace-nowrap"
                                :class="columna.align"
                            >
                                <!-- Registros -->
                                <span
                                    v-if="columna.key === 'registros'"
                                    class="font-bold text-slate-700 tabular-nums"
                                >
                                    {{ fila.registros.toLocaleString() }}
                                </span>

                                <!-- Docentes con registro -->
                                <span
                                    v-else-if="columna.key === 'con_registro'"
                                    class="font-semibold text-slate-600 tabular-nums"
                                >
                                    {{ fila.con_registro.toLocaleString() }}
                                </span>

                                <!-- Módulos cubiertos -->
                                <span
                                    v-else-if="columna.key === 'modulos'"
                                    class="font-semibold text-slate-600 tabular-nums"
                                >
                                    {{ modulosCubiertos(fila) }}
                                </span>

                                <!-- Última actividad -->
                                <span
                                    v-else-if="columna.key === 'ultima'"
                                    class="text-slate-500 tabular-nums"
                                >
                                    {{ fecha(fila.ultima) }}
                                </span>

                                <!-- Barra de porcentaje -->
                                <span v-else-if="columna.key === 'porcentaje'" class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md border text-[10px] font-bold tabular-nums shrink-0"
                                        :class="colorRango(fila.porcentaje).badge"
                                    >
                                        {{ fila.porcentaje }}%
                                    </span>
                                    <span class="flex-1 min-w-[60px] h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <span
                                            class="block h-full rounded-full transition-all duration-700 ease-out"
                                            :style="{
                                                width: `${Math.min(fila.porcentaje, 100)}%`,
                                                backgroundColor: colorRango(fila.porcentaje).bar,
                                            }"
                                        ></span>
                                    </span>
                                </span>
                            </td>
                        </tr>

                        <tr v-if="!filas.length">
                            <td :colspan="columnas.length + 1" class="px-3 py-8 text-center">
                                <Users class="w-6 h-6 text-slate-300 mx-auto mb-2" />
                                <p class="text-xs text-slate-500 font-medium m-0">
                                    No hay {{ data.dimension.toLowerCase() }} para mostrar.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══ AVISOS ══ -->
        <p
            v-if="data.truncado"
            class="mt-3 mb-0 text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 flex items-center gap-1.5"
        >
            <AlertTriangle class="w-3.5 h-3.5 shrink-0" />
            Se muestran {{ filas.length }} de {{ data.total_filas }} {{ data.dimension.toLowerCase() }}.
        </p>
    </section>
</template>
