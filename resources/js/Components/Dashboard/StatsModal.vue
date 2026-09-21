<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    data: {
        type: Object,
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

function handleKeyDown(e) {
    if (e.key === 'Escape' && props.show) {
        emit('close');
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div 
        v-if="show" 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-3 sm:p-6 flex items-start sm:items-center justify-center"
        @click.self="emit('close')"
    >
        <div 
            class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full my-auto overflow-hidden border border-slate-100 flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-200"
            @click.stop
        >
            <!-- Modal Header (fijo en la parte superior del modal) -->
            <div 
                class="px-6 py-4 text-white flex items-center justify-between shrink-0 shadow-sm"
                style="background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);"
            >
                <div class="flex items-center flex-wrap gap-2 pr-2">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-chart-bar text-lg"></i>
                        <h3 class="font-bold text-lg mb-0 text-white leading-tight">Participación por Módulo</h3>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span v-if="data?.scope" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white">
                            {{ data.scope }}
                        </span>
                        <small v-if="data?.totalDocentes" class="text-white/80 text-xs font-normal">
                            — {{ data.totalDocentes.toLocaleString() }} docentes
                        </small>
                    </div>
                </div>
                <button 
                    type="button" 
                    class="text-white/80 hover:text-white text-2xl leading-none transition-colors p-1 shrink-0 ml-auto"
                    @click="emit('close')"
                    title="Cerrar ventana"
                >
                    &times;
                </button>
            </div>

            <!-- Modal Body (desplazable internamente) -->
            <div class="p-6 overflow-y-auto flex-1">
                <!-- Tarjetas de resumen por módulo -->
                <div v-if="data?.modulos" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
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

                <!-- Tabla detallada -->
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
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
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button 
                    type="button" 
                    class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition-colors inline-flex items-center"
                    @click="emit('close')"
                >
                    <i class="fas fa-times mr-1.5"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</template>
