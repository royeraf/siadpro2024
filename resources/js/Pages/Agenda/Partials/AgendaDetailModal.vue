<script setup>
import { ref, computed, watch } from 'vue';
import { AgendasService } from '@/Services/agenda';
import { useMultiDocumentUpload } from '@/Composables/useMultiDocumentUpload';
import BaseModal from '@/Components/UI/BaseModal.vue';
import FileDropzoneMulti from '@/Components/UI/FileDropzoneMulti.vue';
import FileViewerModal from '@/Components/UI/FileViewerModal.vue';
import EvidenciaStack from './EvidenciaStack.vue';
import Swal from 'sweetalert2';
import {
    CalendarDays, Clock, Plus, Loader2,
    FileText, AlertCircle, Paperclip, RefreshCw,
} from 'lucide-vue-next';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    /** Evento/agenda: { id, title, evento, seccion, nomDocente }. */
    agenda: {
        type: Object,
        default: null,
    },
    /** Puede editar el evento (abre el formulario). */
    canEdit: {
        type: Boolean,
        default: false,
    },
    /** Puede subir/eliminar archivos (dueño de la agenda). */
    canEvidencia: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'edit']);

const EXTENSIONES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'xlm', 'xlsm', 'ppt', 'pptx', 'pptm', 'png', 'jpg', 'jpeg'];

const cargando = ref(false);
const errorCarga = ref('');
const dias = ref([]);
const agendaInfo = ref(null);
const diaActivo = ref(null);

const {
    accept,
    hint,
    compressors,
    showCompressors,
    items: archivos,
    globalError,
    uploading,
    progress,
    addFiles,
    removeAt,
    clear,
    validFiles,
    canSubmit,
    startUpload,
    trackProgress,
    finishUpload,
} = useMultiDocumentUpload({
    accept: EXTENSIONES.map((e) => `.${e}`).join(','),
    extensions: EXTENSIONES,
    hint: 'PDF, Word, Excel, PowerPoint e imágenes',
    max: 10,
});

const visor = ref({ show: false, diaId: null, index: -1, url: '', name: '', downloadUrl: '' });

const titulo = computed(() => agendaInfo.value?.title || props.agenda?.title || 'Evento');

// Hex de la sección del evento para teñir el encabezado del modal (solo si el
// evento tiene una sección seleccionada; "sin color" no tiñe).
const tintHex = computed(() => {
    const seccion = props.agenda?.seccion;
    const color = props.agenda?.color;
    if (!seccion || typeof color !== 'string' || !/^#[0-9a-fA-F]{6}$/.test(color)) return '';
    return color;
});

// Archivos del día que está abierto en el visor (solo ese día; al abrir un
// archivo se fija `visor.diaId`). El visor navega únicamente dentro del día.
const listaEvidencias = computed(() => {
    const dia = dias.value.find((d) => d.id === visor.value.diaId);
    return dia?.evidencias || [];
});

// Misma lista, con url/nombre listos para el navegador de miniaturas del visor.
const visorItems = computed(() => listaEvidencias.value.map((ev) => ({
    url: AgendasService.visorUrl(ev.enlace),
    name: ev.nombre_original || ev.enlace,
})));

async function cargar() {
    if (!props.agenda?.id) return;

    cargando.value = true;
    errorCarga.value = '';
    diaActivo.value = null;
    clear();

    try {
        const data = await AgendasService.evidencias(props.agenda.id);
        agendaInfo.value = data.agenda || null;
        dias.value = data.dias || [];
    } catch (e) {
        errorCarga.value = 'No se pudieron cargar los días y archivos del evento.';
    } finally {
        cargando.value = false;
    }
}

watch(() => props.show, (visible) => {
    if (visible) {
        cargar();
    } else {
        dias.value = [];
        agendaInfo.value = null;
        errorCarga.value = '';
        diaActivo.value = null;
        clear();
    }
});

watch(() => props.agenda?.id, () => {
    if (props.show) cargar();
});

function fechaLarga(iso) {
    if (!iso) return '';
    const [y, m, d] = iso.split('-').map(Number);
    const fecha = new Date(y, m - 1, d);
    const texto = fecha.toLocaleDateString('es-PE', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

function rangoHora(dia) {
    if (dia.hora_inicio && dia.hora_fin) return `${dia.hora_inicio} – ${dia.hora_fin}`;
    if (dia.hora_inicio) return `Desde ${dia.hora_inicio}`;
    return 'Sin hora definida';
}

function totalArchivos() {
    return dias.value.reduce((acc, d) => acc + (d.evidencias?.length || 0), 0);
}

/* ── Subida por día ── */
function alternarDropzone(diaId) {
    if (diaActivo.value === diaId) {
        diaActivo.value = null;
        clear();
        return;
    }
    diaActivo.value = diaId;
    clear();
}

function subir(diaId) {
    if (!canSubmit.value) return;

    const formData = new FormData();
    formData.append('agenda_dia_id', String(diaId));
    validFiles.value.forEach((file) => formData.append('documento[]', file));

    startUpload();

    // Subida fuera del ciclo de Inertia: continúa en segundo plano aunque el
    // usuario cierre el modal o navegue, así no se pierden los archivos.
    AgendasService.subirEvidencias(props.agenda.id, formData, {
        onProgress: (pct) => trackProgress({ percentage: pct }),
    })
        .then(async (resp) => {
            finishUpload();
            clear();
            diaActivo.value = null;
            await cargar();

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: resp?.data?.message || 'Archivo(s) subido(s) con éxito',
                timer: 2500,
                showConfirmButton: false,
            });
        })
        .catch((err) => {
            finishUpload();

            const data = err?.response?.data || {};
            const errors = data.errors || {};
            const mensaje = Object.values(errors).flat().join(' ')
                || data.message
                || 'No se pudieron subir los archivos.';

            Swal.fire({
                title: 'No se pudo subir',
                text: mensaje,
                icon: 'error',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl border border-slate-100 p-6',
                    title: 'text-base sm:text-lg font-bold text-slate-800',
                    htmlContainer: 'text-xs sm:text-sm text-slate-600',
                },
                buttonsStyling: false,
            });
        });
}

function eliminar(evidencia) {
    Swal.fire({
        title: '¿Eliminar este archivo?',
        text: 'Se quitará de la evidencia del día. No se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup: 'rounded-2xl shadow-2xl border border-slate-100 p-6',
            title: 'text-base sm:text-lg font-bold text-slate-800',
            htmlContainer: 'text-xs sm:text-sm text-slate-600 mt-1',
            actions: 'flex items-center justify-center gap-3 mt-5',
            confirmButton: 'px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold hover:bg-rose-700 shadow-md shadow-rose-600/30 transition-all text-xs sm:text-sm cursor-pointer border-0 outline-none',
            cancelButton: 'px-5 py-2.5 rounded-xl bg-slate-700 text-white font-semibold hover:bg-slate-600 border border-slate-600 shadow-xs transition-all text-xs sm:text-sm cursor-pointer outline-none',
        },
        buttonsStyling: false,
    }).then((result) => {
        if (!result.isConfirmed) return;

        AgendasService.destroyEvidencia(evidencia.id, {
            preserveScroll: true,
            onSuccess: () => cargar(),
            onError: () => Swal.fire({
                title: 'No se pudo eliminar',
                text: 'Inténtelo de nuevo.',
                icon: 'error',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl border border-slate-100 p-6',
                    title: 'text-base sm:text-lg font-bold text-slate-800',
                },
                buttonsStyling: false,
            }),
        });
    });
}

function ver(evidencia, diaId) {
    if (!evidencia) return;

    // Fija el día del archivo abierto: el visor solo navegará ese día.
    const dia = dias.value.find((d) => d.id === diaId);
    const index = (dia?.evidencias || []).findIndex((e) => e.id === evidencia.id);

    visor.value.diaId = diaId;
    abrirVisor(index);
}

function abrirVisor(idx) {
    const lista = listaEvidencias.value;
    if (idx < 0 || idx >= lista.length) return;
    const ev = lista[idx];
    visor.value = {
        show: true,
        diaId: visor.value.diaId,
        index: idx,
        url: AgendasService.visorUrl(ev.enlace),
        name: ev.nombre_original || ev.enlace,
        downloadUrl: AgendasService.downloadEvidenciaUrl(ev.id),
    };
}

function visorAnterior() {
    abrirVisor(visor.value.index - 1);
}

function visorSiguiente() {
    abrirVisor(visor.value.index + 1);
}
</script>

<template>
    <BaseModal
        :show="show"
        :title="titulo"
        subtitle="Días programados y evidencias"
        icon="CalendarCheck"
        color="pink"
        :tint="tintHex"
        max-width="3xl"
        :is-form="false"
        @close="emit('close')"
    >
        <div class="space-y-4">
            <!-- Resumen del evento -->
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                <div class="flex items-center gap-2 mb-1">
                    <CalendarDays class="w-4 h-4 text-pink-600 shrink-0" />
                    <p class="text-sm font-black text-slate-800 m-0 truncate">{{ titulo }}</p>
                </div>
                <p v-if="agenda?.evento || agendaInfo?.evento" class="text-xs text-slate-600 m-0 whitespace-pre-line">
                    {{ agenda?.evento || agendaInfo?.evento }}
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-500 font-semibold">
                    <span v-if="agenda?.nomDocente" class="inline-flex items-center gap-1">
                        <FileText class="w-3 h-3 shrink-0" />
                        {{ agenda.nomDocente }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <Paperclip class="w-3 h-3 shrink-0" />
                        {{ totalArchivos() }} archivo(s)
                    </span>
                </div>
            </div>

            <!-- Cargando -->
            <div v-if="cargando" class="flex items-center justify-center gap-2 py-8 text-slate-500">
                <Loader2 class="w-5 h-5 animate-spin" />
                <span class="text-xs font-semibold">Cargando días y archivos…</span>
            </div>

            <!-- Error de carga -->
            <div v-else-if="errorCarga" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-center">
                <p class="text-xs text-rose-700 font-semibold m-0 flex items-center justify-center">
                    <AlertCircle class="w-4 h-4 mr-1.5 shrink-0" />
                    {{ errorCarga }}
                </p>
                <button
                    type="button"
                    @click="cargar"
                    class="mt-3 inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-rose-700 bg-white border border-rose-200 rounded-xl hover:bg-rose-100 transition-colors cursor-pointer"
                >
                    <RefreshCw class="w-3.5 h-3.5" />
                    Reintentar
                </button>
            </div>

            <!-- Sin días -->
            <div v-else-if="!dias.length" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                <CalendarDays class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                <p class="text-xs font-semibold text-slate-500 m-0">
                    Este evento no tiene días programados.
                </p>
            </div>

            <!-- Días -->
            <div v-else class="space-y-3">
                <div
                    v-for="dia in dias"
                    :key="dia.id"
                    class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden"
                >
                    <div class="flex items-center justify-between gap-3 bg-white px-4 py-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-2 min-w-0">
                            <CalendarDays class="w-4 h-4 text-slate-400 shrink-0" />
                            <span class="text-xs font-bold text-slate-700 truncate">{{ fechaLarga(dia.fecha) }}</span>
                        </div>
                        <span
                            class="inline-flex items-center gap-1 shrink-0 text-[11px] font-bold px-2 py-1 rounded-lg"
                            :class="dia.hora_inicio ? 'text-pink-700 bg-pink-50 border border-pink-200' : 'text-slate-500 bg-slate-100 border border-slate-200'"
                        >
                            <Clock class="w-3 h-3 shrink-0" />
                            {{ rangoHora(dia) }}
                        </span>
                    </div>

                    <div class="px-4 py-3">
                        <EvidenciaStack
                            v-if="dia.evidencias?.length"
                            :evidencias="dia.evidencias"
                            :total="dia.evidencias.length"
                            :can-delete="canEvidencia"
                            size="md"
                            @view="(ev) => ver(ev, dia.id)"
                            @delete="eliminar"
                        />

                        <p v-else class="text-[11px] text-slate-400 font-semibold m-0">
                            Sin archivos en este día.
                        </p>

                        <!-- Subir evidencias -->
                        <template v-if="canEvidencia">
                            <button
                                v-if="diaActivo !== dia.id"
                                type="button"
                                @click="alternarDropzone(dia.id)"
                                class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-pink-700 bg-pink-50 border border-pink-200 rounded-xl hover:bg-pink-100 transition-colors cursor-pointer"
                            >
                                <Plus class="w-3.5 h-3.5" />
                                Añadir archivos
                            </button>

                            <div v-else class="mt-3 space-y-2.5">
                                <FileDropzoneMulti
                                    :items="archivos"
                                    :error="globalError"
                                    :uploading="uploading"
                                    :progress="progress"
                                    :accept="accept"
                                    :hint="hint"
                                    :compressors="compressors"
                                    :show-compressors="showCompressors"
                                    :max="10"
                                    @pick="addFiles"
                                    @remove="removeAt"
                                />

                                <div class="flex items-center gap-2 justify-end">
                                    <button
                                        type="button"
                                        :disabled="uploading"
                                        @click="alternarDropzone(dia.id)"
                                        class="px-3 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-100 transition-colors disabled:opacity-50 cursor-pointer"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="!canSubmit"
                                        @click="subir(dia.id)"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-pink-600 hover:bg-pink-700 active:bg-pink-800 rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                    >
                                        <Loader2 v-if="uploading" class="w-3.5 h-3.5 animate-spin" />
                                        <Plus v-else class="w-3.5 h-3.5" />
                                        {{ uploading ? 'Subiendo…' : 'Subir' }}
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center gap-2.5 max-sm:w-full">
                <button
                    v-if="canEdit"
                    type="button"
                    @click="emit('edit', { dias })"
                    class="max-sm:flex-1 inline-flex items-center justify-center px-4 py-2.5 sm:py-2 text-xs sm:text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 active:bg-amber-700 border border-transparent rounded-xl transition-colors shadow-2xs cursor-pointer"
                >
                    Editar
                </button>
                <button
                    type="button"
                    @click="emit('close')"
                    class="max-sm:flex-1 inline-flex items-center justify-center px-4 py-2.5 sm:py-2 text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-100 active:bg-slate-200 border border-slate-300 rounded-xl transition-colors shadow-2xs cursor-pointer"
                >
                    Cerrar
                </button>
            </div>
        </template>
    </BaseModal>

    <FileViewerModal
        :show="visor.show"
        :url="visor.url"
        :name="visor.name"
        :download-url="visor.downloadUrl"
        :position="visor.index + 1"
        :total="listaEvidencias.length"
        :items="visorItems"
        @prev="visorAnterior"
        @next="visorSiguiente"
        @select="abrirVisor"
        @close="visor = { ...visor, show: false }"
    />
</template>
