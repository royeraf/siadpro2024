<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { AgendasService } from '@/Services/agenda';
import { useForm as useVeeForm } from 'vee-validate';
import * as yup from 'yup';
import { AlertCircle, Trash2, Plus, CalendarDays, Clock, X, Loader2, Ban, Check, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import BaseModal from '@/Components/UI/BaseModal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    mode: {
        type: String,
        default: 'create',
        validator: (v) => ['create', 'edit'].includes(v),
    },
    event: {
        type: Object,
        default: null,
    },
    secciones: {
        type: Object,
        default: () => ({}),
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
    canDestroy: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'cancel', 'delete']);

const submitting = ref(false);
const eventoHint = ref('');

const isCreate = computed(() => props.mode === 'create');
const isEdit = computed(() => props.mode === 'edit');

const tituloModal = computed(() => (isEdit.value ? 'Modificar Evento' : 'Agregar Evento'));
const subtitulo = computed(() => (isEdit.value
    ? 'Modifique los campos necesarios para actualizar el evento'
    : 'Complete los campos para registrar un nuevo evento en su agenda'));

const opcionesSeccion = computed(() =>
    Object.entries(props.secciones || {}).map(([clave, datos]) => ({
        clave,
        label: datos?.label ?? clave,
        hex: datos?.hex ?? '#FF0085',
    }))
);

/** Hex de la sección elegida; se usa para teñir el encabezado del modal. */
const colorHex = computed(() =>
    opcionesSeccion.value.find((o) => o.clave === color.value)?.hex || ''
);

/* ── Scroll horizontal del selector de color ── */
const colorRowRef = ref(null);
const puedeIzq = ref(false);
const puedeDer = ref(false);

function actualizarFlechas() {
    const el = colorRowRef.value;
    if (!el) return;
    puedeIzq.value = el.scrollLeft > 1;
    puedeDer.value = Math.ceil(el.scrollLeft + el.clientWidth) < el.scrollWidth - 1;
}

function scrollColorDir(dir) {
    const el = colorRowRef.value;
    if (!el) return;
    const paso = Math.max(140, Math.round(el.clientWidth * 0.7));
    el.scrollBy({ left: dir * paso, behavior: 'smooth' });
}

onMounted(() => {
    nextTick(actualizarFlechas);
    window.addEventListener('resize', actualizarFlechas);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', actualizarFlechas);
});

/* ── Días programados (fuente de verdad del rango) ── */let secuencia = 0;
function crearDia(fecha = '', hora_inicio = '', hora_fin = '') {
    return { _key: ++secuencia, fecha, hora_inicio, hora_fin };
}

function hoyISO() {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

const dias = ref([]);
const diasError = ref('');
const diaErrors = ref({});
const horaErrors = ref({});
const cargandoDias = ref(false);

function diasDesdeEvento() {
    if (Array.isArray(props.event?.dias) && props.event.dias.length) {
        return props.event.dias.map((d) => crearDia(d.fecha, d.hora_inicio || d.horaInicio || '', d.hora_fin || d.horaFin || ''));
    }
    return [crearDia(hoyISO(), '', '')];
}

function agregarDia() {
    dias.value.push(crearDia(hoyISO(), '', ''));
    revalidarHoras();
}

function quitarDia(index) {
    dias.value.splice(index, 1);
    revalidarHoras();
}

/** Devuelve el primer mensaje de error y marca las filas inválidas. */
function validarDias() {
    diaErrors.value = {};
    horaErrors.value = {};

    if (!dias.value.length) {
        return 'Debe registrar al menos un día.';
    }

    const vistas = new Set();
    let mensaje = '';

    dias.value.forEach((dia, index) => {
        if (!dia.fecha) {
            diaErrors.value[index] = 'Indique la fecha.';
            mensaje = mensaje || 'Complete la fecha de todos los días.';
            return;
        }

        if (vistas.has(dia.fecha)) {
            diaErrors.value[index] = 'Fecha repetida.';
            mensaje = mensaje || 'No se puede repetir la misma fecha en dos días.';
        }
        vistas.add(dia.fecha);

        const inicio = dia.hora_inicio;
        const fin = dia.hora_fin;

        if ((inicio && !fin) || (!inicio && fin)) {
            diaErrors.value[index] = 'Indique hora inicial y final.';
            horaErrors.value[index] = diaErrors.value[index];
            mensaje = mensaje || 'Complete el rango de hora (inicial y final) o déjelo vacío.';
            return;
        }

        if (inicio && fin && fin <= inicio) {
            diaErrors.value[index] = 'La hora final debe ser posterior.';
            horaErrors.value[index] = diaErrors.value[index];
            mensaje = mensaje || 'En cada día, la hora final debe ser posterior a la inicial.';
        }
    });

    return mensaje;
}

/**
 * Valida en vivo el rango de hora de todos los días: la hora final debe ser
 * posterior a la inicial. Se ejecuta al cambiar cualquiera de las horas (o al
 * agregar/quitar días) para avisar de inmediato, sin esperar al envío.
 */
function revalidarHoras() {
    const copia = {};

    dias.value.forEach((dia, index) => {
        if (dia.hora_inicio && dia.hora_fin && dia.hora_fin <= dia.hora_inicio) {
            copia[index] = 'La hora final debe ser posterior a la hora de inicio.';
        }
    });

    horaErrors.value = copia;
}

/** Mensaje visible de una fila: el de envío o el de hora en vivo. */
function errorDia(index) {
    return diaErrors.value[index] || horaErrors.value[index] || '';
}

async function cargarDiasDesdeServidor() {
    if (!props.event?.id) return;

    cargandoDias.value = true;
    try {
        const data = await AgendasService.evidencias(props.event.id);
        dias.value = (data.dias || []).map((d) => crearDia(d.fecha, d.hora_inicio || '', d.hora_fin || ''));
        if (!dias.value.length) {
            dias.value = [crearDia(hoyISO(), '', '')];
        }
    } catch (e) {
        dias.value = [crearDia(hoyISO(), '', '')];
    } finally {
        cargandoDias.value = false;
    }
}

const validationSchema = computed(() => yup.object({
    title: yup
        .string()
        .trim()
        .required('El título es obligatorio.')
        .max(191, 'El título no debe exceder 191 caracteres.'),
    evento: yup
        .string()
        .trim()
        .required('La descripción del evento es obligatoria.')
        .max(191, 'La descripción no debe exceder 191 caracteres.'),
    // La sección es opcional: se puede guardar el evento "sin color".
    color: yup.string(),
}));

const { errors, defineField, handleSubmit, resetForm, setErrors } = useVeeForm({
    validationSchema,
});

const [title, titleAttrs] = defineField('title', { validateOnModelUpdate: true });
const [evento, eventoAttrs] = defineField('evento', { validateOnModelUpdate: true });
const [color, colorAttrs] = defineField('color', { validateOnModelUpdate: true });

function valoresOriginales() {
    if (!props.event) {
        return { title: '', evento: '', color: '' };
    }
    return {
        title: props.event.title || '',
        evento: props.event.evento || '',
        color: props.event.seccion || '',
    };
}

watch(() => props.show, (visible) => {
    if (!visible) return;

    eventoHint.value = '';
    diasError.value = '';
    diaErrors.value = {};
    horaErrors.value = {};
    setErrors({});
    resetForm({ values: valoresOriginales() });
    dias.value = diasDesdeEvento();

    // Edición directa (sin el detalle cargado): los días se traen del servidor.
    if (props.mode === 'edit' && props.event?.id && !Array.isArray(props.event?.dias)) {
        cargarDiasDesdeServidor();
    }

    nextTick(actualizarFlechas);
});

watch([() => props.mode, () => props.event], () => {
    if (!props.show) return;
    resetForm({ values: valoresOriginales() });
    dias.value = diasDesdeEvento();
    diasError.value = '';
    diaErrors.value = {};
    horaErrors.value = {};
});

// Reglas heredadas del formulario legacy: el campo "evento" no admite Enter
// (rompe el almacenamiento en una sola línea) ni comillas simples.
function bloquearEnter(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        eventoHint.value = 'No es válido presionar Enter en este campo.';
    }
}

function bloquearComilla(e) {
    if (e.key === "'") {
        e.preventDefault();
        eventoHint.value = "No es válido colocar ' en este campo.";
    }
}

function limpiarHint() {
    eventoHint.value = '';
}

// Un solo listener para el textarea: Vue no admite dos atributos @keydown en
// el mismo elemento, así que ambas reglas heredadas comparten handler.
function validarTeclaEvento(e) {
    bloquearEnter(e);
    bloquearComilla(e);
}

const submit = handleSubmit((values) => {
    const errorDias = validarDias();
    if (errorDias) {
        diasError.value = errorDias;
        return;
    }

    diasError.value = '';
    submitting.value = true;

    const payload = {
        title: values.title,
        evento: values.evento,
        color: values.color,
        dias: dias.value.map((d) => ({
            fecha: d.fecha,
            hora_inicio: d.hora_inicio || null,
            hora_fin: d.hora_fin || null,
        })),
    };

    const request = {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
        onError: (serverErrors) => {
            setErrors(serverErrors);
            if (serverErrors?.dias) {
                diasError.value = Array.isArray(serverErrors.dias) ? serverErrors.dias[0] : serverErrors.dias;
            }
        },
        onFinish: () => {
            submitting.value = false;
        },
    };

    if (isCreate.value) {
        AgendasService.store(payload, request);
    } else {
        AgendasService.update(props.event.id, payload, request);
    }
});
</script>

<template>
    <BaseModal
        :show="show"
        :title="tituloModal"
        :subtitle="subtitulo"
        icon="CalendarCheck"
        color="pink"
        :tint="colorHex"
        max-width="2xl"
        :submitting="submitting"
        :submit-text="isCreate ? 'Registrar' : 'Modificar'"
        cancel-text="Cancelar"
        @close="emit('close')"
        @submit="submit"
    >
        <div class="space-y-4">
            <div>
                <label for="title" class="block text-xs font-bold text-slate-700 mb-1">
                    Título <span class="text-rose-500">*</span>
                </label>
                <input
                    id="title"
                    type="text"
                    v-model="title"
                    v-bind="titleAttrs"
                    placeholder="Título del evento"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="errors.title ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400' : 'border-slate-300'"
                />
                <p v-if="errors.title" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.title }}
                </p>
            </div>

            <div>
                <label for="evento" class="block text-xs font-bold text-slate-700 mb-1">
                    Evento <span class="text-rose-500">*</span>
                </label>
                <textarea
                    id="evento"
                    rows="4"
                    v-model="evento"
                    v-bind="eventoAttrs"
                    placeholder="Descripcion del evento"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="errors.evento ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400' : 'border-slate-300'"
                    @keydown="validarTeclaEvento"
                    @keyup="limpiarHint"
                ></textarea>
                <p v-if="eventoHint" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ eventoHint }}
                </p>
                <p v-if="errors.evento" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.evento }}
                </p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Sección
                </label>
                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        title="Ver más"
                        @click="scrollColorDir(-1)"
                        class="w-7 h-7 shrink-0 inline-flex items-center justify-center rounded-full bg-white border border-slate-300 text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition-all cursor-pointer shadow-xs"
                        :class="puedeIzq ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                    >
                        <ChevronLeft class="w-4 h-4" />
                    </button>

                    <div
                        ref="colorRowRef"
                        class="color-scroll flex items-center gap-2.5 overflow-x-auto py-1.5 px-1.5 flex-1 min-w-0"
                        role="radiogroup"
                        aria-label="Sección (color) del evento"
                        @scroll="actualizarFlechas"
                    >
                        <!-- Sin color -->
                        <button
                            type="button"
                            role="radio"
                            :aria-checked="color === ''"
                            title="Sin color"
                            @click="color = ''"
                            class="relative w-9 h-9 shrink-0 rounded-full bg-slate-700 text-white flex items-center justify-center transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-fuchsia-500 cursor-pointer"
                            :class="color === '' ? 'ring-2 ring-fuchsia-500 ring-offset-2 ring-offset-white' : 'ring-1 ring-slate-300'"
                        >
                            <Ban class="w-4 h-4" />
                            <span
                                v-if="color === ''"
                                class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-fuchsia-600 text-white flex items-center justify-center ring-2 ring-white"
                            >
                                <Check class="w-2.5 h-2.5" />
                            </span>
                        </button>

                        <!-- Secciones -->
                        <button
                            v-for="op in opcionesSeccion"
                            :key="op.clave"
                            type="button"
                            role="radio"
                            :aria-checked="color === op.clave"
                            :title="op.label"
                            @click="color = op.clave"
                            class="relative w-9 h-9 shrink-0 rounded-full transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-fuchsia-500 cursor-pointer"
                            :style="{ backgroundColor: op.hex }"
                            :class="color === op.clave ? 'ring-2 ring-fuchsia-500 ring-offset-2 ring-offset-white' : 'ring-1 ring-black/10'"
                        >
                            <span
                                v-if="color === op.clave"
                                class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-fuchsia-600 text-white flex items-center justify-center ring-2 ring-white"
                            >
                                <Check class="w-2.5 h-2.5" />
                            </span>
                        </button>
                    </div>

                    <button
                        type="button"
                        title="Ver más"
                        @click="scrollColorDir(1)"
                        class="w-7 h-7 shrink-0 inline-flex items-center justify-center rounded-full bg-white border border-slate-300 text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition-all cursor-pointer shadow-xs"
                        :class="puedeDer ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                    >
                        <ChevronRight class="w-4 h-4" />
                    </button>
                </div>
                <p v-if="errors.color" class="mt-1.5 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.color }}
                </p>
            </div>

            <!-- Días programados -->
            <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-3">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <CalendarDays class="w-4 h-4 text-pink-600 shrink-0" />
                        <span class="text-xs font-bold text-slate-700">
                            Días programados <span class="text-rose-500">*</span>
                        </span>
                    </div>
                    <button
                        type="button"
                        @click="agregarDia"
                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold text-pink-700 bg-white border border-pink-200 rounded-lg hover:bg-pink-50 transition-colors cursor-pointer shrink-0"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        Agregar día
                    </button>
                </div>

                <p class="text-[11px] text-slate-400 m-0 mb-2">
                    Registre uno o varios días (pueden no ser seguidos). Indique el rango de hora en formato 24 h; puede dejarlo vacío si aún no lo define.
                </p>

                <div v-if="cargandoDias" class="flex items-center justify-center gap-2 py-4 text-slate-500">
                    <Loader2 class="w-4 h-4 animate-spin" />
                    <span class="text-[11px] font-semibold">Cargando días…</span>
                </div>

                <div v-else class="space-y-2">
                    <div
                        v-for="(dia, index) in dias"
                        :key="dia._key"
                        class="rounded-xl border bg-white p-2.5"
                        :class="errorDia(index) ? 'border-rose-300' : 'border-slate-200'"
                    >
                        <div class="grid grid-cols-1 sm:grid-cols-[1.4fr_1fr_1fr_auto] gap-2 items-end">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Fecha</label>
                                <input
                                    type="date"
                                    v-model="dia.fecha"
                                    class="w-full text-xs bg-white border border-slate-300 rounded-lg px-2.5 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Hora inicio</label>
                                <input
                                    type="time"
                                    v-model="dia.hora_inicio"
                                    :max="dia.hora_fin || undefined"
                                    @change="revalidarHoras"
                                    class="w-full text-xs bg-white border rounded-lg px-2.5 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :class="horaErrors[index] ? 'border-rose-300 bg-rose-50/30' : 'border-slate-300'"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 mb-1">Hora fin</label>
                                <input
                                    type="time"
                                    v-model="dia.hora_fin"
                                    :min="dia.hora_inicio || undefined"
                                    @change="revalidarHoras"
                                    class="w-full text-xs bg-white border rounded-lg px-2.5 py-2 text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :class="horaErrors[index] ? 'border-rose-300 bg-rose-50/30' : 'border-slate-300'"
                                />
                            </div>
                            <button
                                type="button"
                                @click="quitarDia(index)"
                                :disabled="dias.length <= 1"
                                class="h-9 w-9 mb-px inline-flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer shrink-0"
                                title="Quitar día"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="errorDia(index)" class="mt-1 text-[11px] text-rose-600 font-semibold flex items-center m-0">
                            <AlertCircle class="w-3 h-3 mr-1 shrink-0" />
                            {{ errorDia(index) }}
                        </p>
                    </div>
                </div>

                <p v-if="diasError" class="mt-2 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ diasError }}
                </p>

                <p v-if="!dias.length && !cargandoDias" class="text-[11px] text-slate-400 m-0 flex items-center">
                    <Clock class="w-3 h-3 mr-1 shrink-0" />
                    Sin días. Agregue al menos uno.
                </p>
            </div>

            <div
                v-if="isEdit && canDestroy"
                class="border border-rose-200 bg-rose-50/60 rounded-xl p-3 flex items-start justify-between gap-3"
            >
                <div class="min-w-0">
                    <p class="text-xs font-bold text-rose-800 m-0">Eliminar Evento</p>
                    <p class="text-[11px] text-rose-600 m-0 mt-0.5">
                        Quita el evento de la agenda. No se puede deshacer desde aquí.
                    </p>
                </div>
                <button
                    type="button"
                    @click="emit('delete', event)"
                    class="inline-flex items-center justify-center px-3 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 border border-transparent rounded-xl shadow-xs transition-colors cursor-pointer shrink-0"
                >
                    <Trash2 class="w-3.5 h-3.5 mr-1.5" />
                    Eliminar
                </button>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center gap-2.5 max-sm:w-full">
                <button
                    type="button"
                    @click="emit('cancel')"
                    :disabled="submitting"
                    class="max-sm:flex-1 inline-flex items-center justify-center px-4 py-2.5 sm:py-2 text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-100 active:bg-slate-200 border border-slate-300 rounded-xl transition-colors shadow-2xs disabled:opacity-50 cursor-pointer"
                >
                    Cancelar
                </button>
                <button
                    type="submit"
                    :disabled="submitting"
                    class="max-sm:flex-1 inline-flex items-center justify-center px-5 py-2.5 sm:py-2 text-xs sm:text-sm font-bold rounded-xl transition-colors shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    :class="submitting
                        ? 'bg-pink-400 text-white'
                        : 'bg-pink-600 hover:bg-pink-700 active:bg-pink-800 text-white focus:ring-pink-500'"
                >
                    <span>{{ submitting ? 'Guardando...' : (isCreate ? 'Registrar' : 'Modificar') }}</span>
                </button>
            </div>
        </template>
    </BaseModal>
</template>

<style scoped>
/* Fila única con scroll horizontal y barra visible (llamativa). */
.color-scroll {
    scrollbar-width: thin;
    scrollbar-color: #c026d3 #e2e8f0;
}

.color-scroll::-webkit-scrollbar {
    height: 8px;
}

.color-scroll::-webkit-scrollbar-track {
    background: #e2e8f0;
    border-radius: 9999px;
}

.color-scroll::-webkit-scrollbar-thumb {
    background: #c026d3;
    border-radius: 9999px;
}

.color-scroll::-webkit-scrollbar-thumb:hover {
    background: #a21caf;
}
</style>
