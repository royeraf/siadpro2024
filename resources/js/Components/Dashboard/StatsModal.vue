<script setup>
import BaseModal from '@/Components/UI/BaseModal.vue';
import { BarChart3, MapPin, Loader2, AlertCircle, X } from 'lucide-vue-next';
import MapaParticipacionUgel from './MapaParticipacionUgel.vue';

/**
 * Detalle de participación por módulo: tarjetas de resumen, el mapa por UGEL y
 * la tabla de módulos. Se apoya en `BaseModal` (igual que el resto de detalles
 * del proyecto) para heredar overlay, cabecera, cierre con ESC y bloqueo de
 * scroll, en vez de reimplementarlos.
 */
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    data: {
        type: Object,
        default: null,
    },
    // Matriz módulo × UGEL para el mapa; llega `null` mientras carga.
    mapa: {
        type: Object,
        default: null,
    },
    mapaLoading: {
        type: Boolean,
        default: false,
    },
    mapaError: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['close']);

function badgeClass(pct) {
    if (pct >= 75) return 'bg-emerald-100 text-emerald-800 border-emerald-300';
    if (pct >= 50) return 'bg-blue-100 text-blue-800 border-blue-300';
    if (pct >= 25) return 'bg-amber-100 text-amber-800 border-amber-300';
    return 'bg-rose-100 text-rose-800 border-rose-300';
}
</script>

<template>
    <BaseModal
        :show="show"
        title="Participación por Módulo"
        :subtitle="data?.scope ? `${data.scope} · ${data.totalDocentes?.toLocaleString() ?? 0} docentes` : ''"
        :icon="BarChart3"
        color="indigo"
        max-width="4xl"
        :is-form="false"
        @close="emit('close')"
    >
        <!-- Tarjetas de resumen por módulo -->
        <div v-if="data?.modulos" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div
                v-for="m in data.modulos"
                :key="m.key"
                class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between"
            >
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold text-slate-800 text-xs truncate" :title="m.nombre">
                        {{ m.nombre }}
                    </span>
                    <span class="text-sm font-extrabold text-indigo-600">
                        {{ m.porcentaje }}%
                    </span>
                </div>
                <div class="text-[11px] text-slate-500 mb-2 flex justify-between">
                    <span>{{ m.docentes_con_registro.toLocaleString() }} docentes</span>
                    <span>{{ m.registros.toLocaleString() }} reg.</span>
                </div>
                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :style="{
                            width: `${Math.min(m.porcentaje, 100)}%`,
                            background: m.porcentaje >= 50 ? '#10B981' : '#F59E0B'
                        }"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Mapa de participación por UGEL -->
        <div class="mt-4">
            <div class="flex items-center gap-2 mb-3">
                <MapPin class="w-4 h-4 text-indigo-500" />
                <h4 class="font-bold text-slate-800 text-sm mb-0">
                    Participación por UGEL — Mapa de Huánuco
                </h4>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4">
                <div v-if="mapaLoading && !mapa" class="flex items-center justify-center py-12 text-slate-400 text-xs">
                    <Loader2 class="w-3.5 h-3.5 animate-spin mr-2" />Cargando mapa...
                </div>
                <div v-else-if="mapaError" class="py-10 text-center text-rose-500 text-xs">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 inline" />{{ mapaError }}
                </div>
                <MapaParticipacionUgel v-else-if="mapa" :data="mapa" />
                <div v-else class="py-10 text-center text-slate-400 text-xs">
                    Sin datos de mapa disponibles.
                </div>
            </div>
        </div>

        <!-- Tabla detallada -->
        <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Módulo</th>
                        <th class="px-4 py-3 text-center">Registros</th>
                        <th class="px-4 py-3 text-center">Docentes con Registro</th>
                        <th class="px-4 py-3 text-center">% Participación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr v-for="m in data?.modulos || []" :key="m.key" class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-2.5 font-medium text-slate-800">{{ m.nombre }}</td>
                        <td class="px-4 py-2.5 text-center text-slate-600">{{ m.registros.toLocaleString() }}</td>
                        <td class="px-4 py-2.5 text-center text-slate-600">{{ m.docentes_con_registro.toLocaleString() }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span
                                class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold border"
                                :class="badgeClass(m.porcentaje)"
                            >
                                {{ m.porcentaje }}%
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pie: solo cierre (modal de lectura) -->
        <template #footer>
            <div class="flex items-center justify-end gap-2.5 max-sm:w-full">
                <button
                    type="button"
                    class="max-sm:flex-1 inline-flex items-center justify-center px-4 py-2.5 sm:py-2 text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-100 active:bg-slate-200 border border-slate-300 rounded-xl transition-colors shadow-2xs cursor-pointer"
                    @click="emit('close')"
                >
                    <X class="w-3.5 h-3.5 mr-1.5" />Cerrar
                </button>
            </div>
        </template>
    </BaseModal>
</template>
