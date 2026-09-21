<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { Download, X, FileText, AlertTriangle, FileQuestion } from 'lucide-vue-next';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    url: {
        type: String,
        default: '',
    },
    name: {
        type: String,
        default: '',
    },
    downloadUrl: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close']);

const extension = computed(() => {
    if (!props.name && !props.url) return '';
    const target = props.name || props.url;
    const base = target.split(/[?#]/)[0];
    return (base.split('.').pop() || '').toLowerCase();
});

const isImage = computed(() => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'].includes(extension.value));
const isPdf = computed(() => extension.value === 'pdf');

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
        class="fixed inset-0 z-[60] overflow-y-auto bg-slate-900/75 backdrop-blur-sm p-3 sm:p-6 flex items-center justify-center"
        @click.self="emit('close')"
    >
        <div 
            class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl max-w-5xl w-full h-[90vh] overflow-hidden border border-slate-200 flex flex-col animate-in fade-in zoom-in-95 duration-200"
            @click.stop
        >
            <!-- Barra superior del visor -->
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between shrink-0 shadow-sm">
                <div class="flex items-center space-x-2.5 min-w-0 pr-4">
                    <FileText class="w-4 h-4 text-indigo-400 shrink-0" />
                    <span class="text-sm font-bold text-slate-100 truncate" :title="name">
                        {{ name || 'Visualizador de Documento' }}
                    </span>
                </div>

                <div class="flex items-center space-x-2 shrink-0">
                    <!-- Botón Descargar -->
                    <a 
                        v-if="downloadUrl || url" 
                        :href="downloadUrl || url" 
                        :download="name"
                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-bold rounded-lg shadow-xs transition-colors text-decoration-none"
                        title="Descargar archivo"
                    >
                        <Download class="w-3.5 h-3.5 mr-1.5" />
                        <span>Descargar</span>
                    </a>

                    <!-- Botón Cerrar -->
                    <button 
                        type="button" 
                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                        @click="emit('close')"
                        title="Cerrar (Esc)"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Contenido del archivo -->
            <div class="flex-1 bg-slate-100 overflow-auto flex items-center justify-center p-2 sm:p-4 relative">
                <!-- Visor PDF -->
                <iframe 
                    v-if="isPdf" 
                    :src="url" 
                    class="w-full h-full rounded-xl bg-white shadow-xs border border-slate-200"
                    title="Vista previa PDF"
                ></iframe>

                <!-- Visor de Imágenes -->
                <img 
                    v-else-if="isImage" 
                    :src="url" 
                    :alt="name" 
                    class="max-w-full max-h-full object-contain rounded-xl shadow-lg bg-white"
                />

                <!-- Archivo descargable (Word, Excel, etc.) -->
                <div 
                    v-else 
                    class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8 max-w-md w-full text-center my-auto"
                >
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4 border border-indigo-100">
                        <FileQuestion class="w-8 h-8" />
                    </div>
                    <h4 class="text-base font-bold text-slate-800 mb-1">
                        {{ name || 'Archivo adjunto' }}
                    </h4>
                    <p class="text-xs text-slate-500 mb-5 leading-relaxed">
                        Este formato (<strong>.{{ extension.toUpperCase() }}</strong>) se visualiza mejor en su aplicación nativa. Haz clic en el botón inferior para descargarlo.
                    </p>
                    <a 
                        :href="downloadUrl || url" 
                        :download="name"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all"
                    >
                        <Download class="w-4 h-4 mr-2" />
                        Descargar archivo
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
