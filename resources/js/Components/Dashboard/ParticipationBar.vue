<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useThemeColors } from '@/Composables/useThemeColors';
import StatsModal from './StatsModal.vue';
import { 
    CalendarCheck, Megaphone, Radio, FileText, 
    BookHeart, NotebookPen, BookOpen, Folder 
} from 'lucide-vue-next';

const props = defineProps({
    initialStats: {
        type: Object,
        default: null,
    },
});

const { statsConfig } = useThemeColors();

const icons = {
    CalendarCheck,
    Megaphone,
    Radio,
    FileText,
    BookHeart,
    NotebookPen,
    BookOpen,
    Folder,
};

const statsData = ref(props.initialStats);
const loading = ref(!props.initialStats);
const error = ref(null);
const showModal = ref(false);

async function loadStats() {
    try {
        const response = await axios.get('/dashboard/resumen-modulos', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        statsData.value = response.data;
    } catch (err) {
        error.value = err.message || 'Error al cargar';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadStats();
});
</script>

<template>
    <div>
        <div 
            class="participacion-card mb-6 cursor-pointer bg-white rounded-2xl border border-slate-100 shadow-sm p-4 hover:shadow-md transition-all duration-200"
            @click="showModal = true"
            title="Haz clic para ver el detalle de participación"
        >
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Columna Izquierda: Título y estado -->
                <div class="lg:w-1/4 shrink-0">
                    <h6 class="font-extrabold text-slate-800 text-sm mb-0.5">
                        Participación por Módulo
                    </h6>
                    <small class="text-slate-500 text-xs block">
                        <span v-if="loading" class="text-slate-400">
                            <i class="fas fa-spinner fa-spin mr-1"></i>Cargando resumen...
                        </span>
                        <span v-else-if="error" class="text-rose-500">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ error }}
                        </span>
                        <span v-else>
                            {{ statsData?.scope }} · {{ statsData?.totalDocentes?.toLocaleString() }} docentes
                        </span>
                    </small>
                </div>

                <!-- Columna Derecha: Barras horizontales -->
                <div class="lg:w-3/4 flex-1">
                    <div class="participacion-grid flex items-center justify-between gap-2 overflow-x-auto pb-1">
                        <div 
                            v-for="m in statsData?.modulos || []" 
                            :key="m.key"
                            class="part-item flex-1 min-w-[95px] px-2.5 border-r border-slate-100 last:border-r-0"
                        >
                            <div class="flex items-center mb-1">
                                <div 
                                    class="part-icon-badge mr-2 w-7 h-7 rounded-full flex items-center justify-center shrink-0"
                                    :style="{ 
                                        backgroundColor: statsConfig[m.key]?.circleBg || '#F1F5F9', 
                                        color: statsConfig[m.key]?.iconColor || '#64748B' 
                                    }"
                                >
                                    <component :is="icons[statsConfig[m.key]?.icon] || icons.Folder" class="w-3.5 h-3.5" />
                                </div>
                                <div class="min-w-0">
                                    <div class="part-name text-slate-500 text-[11px] truncate leading-tight">
                                        {{ statsConfig[m.key]?.short || m.nombre }}
                                    </div>
                                    <div 
                                        class="part-pct font-black text-xs leading-tight"
                                        :style="{ color: statsConfig[m.key]?.pctColor || '#0F172A' }"
                                    >
                                        {{ m.porcentaje }}%
                                    </div>
                                </div>
                            </div>
                            <div class="part-progress-bar h-1.5 w-full bg-slate-100 rounded-full overflow-hidden mt-1">
                                <div 
                                    class="part-progress-fill h-full rounded-full transition-all duration-700 ease-out"
                                    :style="{ 
                                        width: `${Math.min(m.porcentaje, 100)}%`, 
                                        backgroundColor: statsConfig[m.key]?.barColor || '#64748B' 
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de estadísticas -->
        <StatsModal 
            :show="showModal" 
            :data="statsData" 
            @close="showModal = false" 
        />
    </div>
</template>

<style scoped>
.participacion-card {
    border: 1.5px solid #F1F5F9;
}
.participacion-card:hover {
    border-color: #E2E8F0;
}
</style>
