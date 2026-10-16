<script setup>
import { computed, ref } from 'vue';
import {
    Upload, CheckCircle2, AlertCircle, X,
    Paperclip, ExternalLink, FileWarning, Plus
} from 'lucide-vue-next';
import CircularProgress from '@/Components/UI/CircularProgress.vue';
import ArchivoIcono from '@/Components/UI/ArchivoIcono.vue';
import { formatBytes, formatBytesCompact } from '@/Utils/formatBytes';

const props = defineProps({
    /** Candidatos: [{ file, error }] (lo entrega `useMultiDocumentUpload`). */
    items: {
        type: Array,
        default: () => [],
    },
    /** Error global del bloque (p. ej. excedió el máximo de archivos). */
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
    hint: {
        type: String,
        default: '',
    },
    compressors: {
        type: Array,
        default: () => [],
    },
    showCompressors: {
        type: Boolean,
        default: false,
    },
    max: {
        type: Number,
        default: 10,
    },
});

const emit = defineEmits(['pick', 'remove']);

const fileInput = ref(null);
const isDragging = ref(false);

const disabled = computed(() => props.uploading);

const progressCaption = computed(() => {
    const total = props.items.reduce((acc, it) => acc + (it.file?.size || 0), 0);
    if (!total) return '';
    return `${formatBytesCompact(total * (props.progress / 100))} de ${formatBytesCompact(total)}`;
});

const progressLabel = computed(() => (props.progress >= 100
    ? 'Procesando los archivos, espere…'
    : 'Subiendo en segundo plano… puedes cerrar esta ventana sin perder los archivos.'));

function openPicker() {
    if (disabled.value) return;
    fileInput.value?.click();
}

function handleSelect(e) {
    const picked = Array.from(e.target.files || []);
    // Se limpia el input para poder volver a elegir los mismos archivos.
    e.target.value = '';
    if (picked.length) emit('pick', picked);
}

function handleDrop(e) {
    isDragging.value = false;
    if (disabled.value) return;
    const dropped = Array.from(e.dataTransfer?.files || []);
    if (dropped.length) emit('pick', dropped);
}
</script>

<template>
    <div>
        <div
            class="border-2 border-dashed rounded-2xl p-4 transition-colors"
            :class="[
                disabled ? 'cursor-default opacity-80' : 'cursor-pointer',
                isDragging ? 'border-blue-500 bg-blue-50/50' : 'border-slate-300 hover:border-blue-400 hover:bg-slate-50/50',
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
                multiple
                :accept="accept"
                :disabled="disabled"
                @change="handleSelect"
            />

            <div v-if="!items.length && !uploading" class="py-2 text-center">
                <Upload class="w-7 h-7 text-blue-500 mx-auto mb-1.5" />
                <p class="text-xs font-semibold text-slate-700 m-0">
                    Haga clic o arrastre uno o varios archivos aquí
                </p>
                <p v-if="hint" class="text-[11px] text-slate-400 mt-1 m-0">
                    {{ hint }}
                </p>
            </div>

            <ul v-if="items.length" class="space-y-1.5 m-0 p-0 list-none">
                <li
                    v-for="(item, index) in items"
                    :key="index"
                    class="flex items-center justify-between rounded-xl p-2.5 border"
                    :class="item.error ? 'bg-rose-50/60 border-rose-200' : 'bg-blue-50/70 border-blue-200'"
                >
                    <div class="flex items-center min-w-0">
                        <ArchivoIcono :nombre="item.file.name" class="w-7 h-7 mr-2 shrink-0" />
                        <div class="text-left min-w-0">
                            <p class="text-xs font-bold text-slate-800 truncate m-0">{{ item.file.name }}</p>
                            <p class="text-[11px] m-0" :class="item.error ? 'text-rose-600 font-semibold' : 'text-slate-500'">
                                {{ item.error || formatBytes(item.file.size) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 ml-2 shrink-0">
                        <component
                            :is="item.error ? AlertCircle : CheckCircle2"
                            class="w-4 h-4"
                            :class="item.error ? 'text-rose-600' : 'text-emerald-600'"
                        />
                        <button
                            v-if="!disabled"
                            type="button"
                            @click.stop="emit('remove', index)"
                            class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-white transition-colors cursor-pointer"
                            title="Quitar archivo"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                </li>
            </ul>

            <!-- Agregar más archivos al lote actual (además de hacer clic/arrastrar) -->
            <button
                v-if="items.length && !uploading"
                type="button"
                @click.stop="openPicker"
                class="mt-2.5 w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-[11px] font-bold text-blue-700 bg-white border border-dashed border-blue-300 rounded-xl hover:bg-blue-50 hover:border-blue-400 transition-colors cursor-pointer"
            >
                <Plus class="w-3.5 h-3.5" />
                Agregar otro archivo
            </button>

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
        </div>

        <div v-if="error" class="mt-2 rounded-xl border border-rose-200 bg-rose-50 p-3">
            <p class="text-xs text-rose-700 font-semibold flex items-start m-0">
                <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0 mt-px" />
                {{ error }}
            </p>
        </div>

        <div v-if="showCompressors && compressors.length" class="mt-2 rounded-xl border border-rose-200 bg-rose-50 p-3">
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
</template>
