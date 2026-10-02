<script setup>
import { computed, ref } from 'vue';
import {
    Upload, CheckCircle2, AlertCircle,
    X, Paperclip, ExternalLink, FileWarning
} from 'lucide-vue-next';
import CircularProgress from '@/Components/UI/CircularProgress.vue';
import { formatBytes, formatBytesCompact } from '@/Utils/formatBytes';

const props = defineProps({
    /** Archivo aceptado (lo entrega `useDocumentUpload`). */
    file: {
        type: Object,
        default: null,
    },
    error: {
        type: String,
        default: '',
    },
    uploading: {
        type: Boolean,
        default: false,
    },
    /** Porcentaje de subida (0-100). */
    progress: {
        type: Number,
        default: 0,
    },
    accept: {
        type: String,
        default: '',
    },
    /** Texto de ayuda bajo el icono. */
    hint: {
        type: String,
        default: '',
    },
    /** Compresores sugeridos cuando el PDF pesa demasiado. */
    compressors: {
        type: Array,
        default: () => [],
    },
    showCompressors: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['pick', 'remove']);

const fileInput = ref(null);
const isDragging = ref(false);

const disabled = computed(() => props.uploading);

const sizeLabel = computed(() => (props.file ? formatBytes(props.file.size) : ''));

const progressCaption = computed(() => {
    if (!props.file) return '';
    return `${formatBytesCompact(props.file.size * (props.progress / 100))} de ${formatBytesCompact(props.file.size)}`;
});

/** Al llegar al 100% el archivo ya subiu: queda el procesado del servidor. */
const progressLabel = computed(() => (props.progress >= 100
    ? 'Procesando el archivo, espere…'
    : 'Subiendo el archivo, no cierre esta ventana…'));

function openPicker() {
    if (disabled.value) return;
    fileInput.value?.click();
}

function handleSelect(e) {
    const picked = e.target.files?.[0];
    // Se limpia el input para poder volver a elegir el mismo archivo.
    e.target.value = '';
    if (picked) emit('pick', picked);
}

function handleDrop(e) {
    isDragging.value = false;
    if (disabled.value) return;

    const dropped = e.dataTransfer?.files?.[0];
    if (dropped) emit('pick', dropped);
}
</script>

<template>
    <div>
        <div
            class="border-2 border-dashed rounded-2xl p-4 text-center transition-colors"
            :class="[
                disabled ? 'cursor-default opacity-80' : 'cursor-pointer',
                isDragging ? 'border-blue-500 bg-blue-50/50' : 'border-slate-300 hover:border-blue-400 hover:bg-slate-50/50',
                error ? 'border-rose-400 bg-rose-50/10' : ''
            ]"
            @dragover.prevent="!disabled && (isDragging = true)"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="openPicker"
        >
            <input
                type="file"
                ref="fileInput"
                class="hidden"
                :accept="accept"
                :disabled="disabled"
                @change="handleSelect"
            />

            <div v-if="file" class="flex items-center justify-between bg-blue-50/70 border border-blue-200 p-2.5 rounded-xl">
                <div class="flex items-center min-w-0">
                    <CheckCircle2 class="w-4 h-4 text-emerald-600 mr-2 shrink-0" />
                    <div class="text-left min-w-0">
                        <p class="text-xs font-bold text-slate-800 truncate m-0">{{ file.name }}</p>
                        <p class="text-[11px] text-slate-500 m-0">{{ sizeLabel }}</p>
                    </div>
                </div>
                <button
                    v-if="!disabled"
                    type="button"
                    @click.stop="emit('remove')"
                    class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-white transition-colors ml-2 cursor-pointer"
                    title="Quitar archivo"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <div v-if="uploading" class="mt-3 flex flex-col items-center gap-2">
                <CircularProgress
                    :value="progress"
                    :caption="progressCaption"
                    :bar-class="progress >= 100 ? 'stroke-emerald-600' : 'stroke-blue-600'"
                />
                <p class="text-[11px] font-semibold text-slate-500 m-0 text-center">
                    {{ progressLabel }}
                </p>
            </div>

            <div v-else-if="!file" class="py-2">
                <Upload class="w-7 h-7 text-blue-500 mx-auto mb-1.5" />
                <p class="text-xs font-semibold text-slate-700 m-0">
                    Haga clic o arrastre su archivo aquí
                </p>
                <p v-if="hint" class="text-[11px] text-slate-400 mt-1 m-0">
                    {{ hint }}
                </p>
            </div>
        </div>

        <div v-if="error" class="mt-2 rounded-xl border border-rose-200 bg-rose-50 p-3">
            <p class="text-xs text-rose-700 font-semibold flex items-start m-0">
                <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0 mt-px" />
                {{ error }}
            </p>

            <div v-if="showCompressors && compressors.length" class="mt-2.5 pt-2.5 border-t border-rose-200/70">
                <p class="text-[11px] text-rose-700/90 font-bold flex items-center m-0">
                    <FileWarning class="w-3.5 h-3.5 mr-1 shrink-0" />
                    Antes de volver a intentarlo, comprima su PDF con cualquiera de estas webs gratuitas:
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <a
                        v-for="tool in compressors"
                        :key="tool.url"
                        :href="tool.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white border border-rose-200 text-rose-700 text-[11px] font-bold hover:bg-rose-100 hover:border-rose-300 transition-colors"
                    >
                        <Paperclip class="w-3 h-3 shrink-0" />
                        {{ tool.name }}
                        <ExternalLink class="w-3 h-3 shrink-0" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
