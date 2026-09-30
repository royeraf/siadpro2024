<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useForm as useVeeForm } from 'vee-validate';
import * as yup from 'yup';
import { AlertCircle, Trash2 } from 'lucide-vue-next';
import BaseModal from '@/Components/UI/BaseModal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    mode: {
        type: String,
        default: 'create',
        validator: (v) => ['create', 'view', 'edit'].includes(v),
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

const emit = defineEmits(['close', 'edit', 'cancel']);

const submitting = ref(false);
const eventoHint = ref('');

const isView = computed(() => props.mode === 'view');
const isCreate = computed(() => props.mode === 'create');
const isEdit = computed(() => props.mode === 'edit');

const tituloModal = computed(() => {
    if (isView.value) return 'Visualización de Evento';
    if (isEdit.value) return 'Modificar Evento';
    return 'Agregar Evento';
});

const subtitulo = computed(() => {
    if (isView.value) return 'Detalle del evento seleccionado';
    if (isEdit.value) return 'Modifique los campos necesarios para actualizar el evento';
    return 'Complete los campos para registrar un nuevo evento en su agenda';
});

const opcionesSeccion = computed(() =>
    Object.entries(props.secciones || {}).map(([clave, datos]) => ({
        clave,
        label: datos?.label ?? clave,
        hex: datos?.hex ?? '#FF0085',
    }))
);

const colorSeleccionado = computed(() =>
    opcionesSeccion.value.find((o) => o.clave === color.value)
);

// <input type="datetime-local"> trabaja con "YYYY-MM-DDTHH:mm"; el guardado en
// base de datos usa exactamente ese formato.
function toDatetimeLocal(value) {
    if (!value) return '';
    const clean = String(value).replace(' ', 'T');
    return clean.length >= 16 ? clean.substring(0, 16) : clean;
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
    color: yup
        .string()
        .required('Debe seleccionar la sección (color) del evento.'),
    start: yup
        .string()
        .required('La fecha inicial es obligatoria.'),
    end: yup
        .string()
        .required('La fecha final es obligatoria.')
        .test(
            'no-anterior',
            'La fecha final debe ser posterior o igual a la inicial.',
            function (value) {
                if (!value || !this.parent.start) return true;
                return value >= this.parent.start;
            }
        ),
}));

const { errors, defineField, handleSubmit, resetForm, setErrors } = useVeeForm({
    validationSchema,
});

const [title, titleAttrs] = defineField('title', { validateOnModelUpdate: true });
const [evento, eventoAttrs] = defineField('evento', { validateOnModelUpdate: true });
const [color, colorAttrs] = defineField('color', { validateOnModelUpdate: true });
const [start, startAttrs] = defineField('start', { validateOnModelUpdate: true });
const [end, endAttrs] = defineField('end', { validateOnModelUpdate: true });

function valoresOriginales() {
    if (!props.event) {
        return { title: '', evento: '', color: '', start: '', end: '' };
    }
    return {
        title: props.event.title || '',
        evento: props.event.evento || '',
        color: props.event.seccion || '',
        start: toDatetimeLocal(props.event.start),
        end: toDatetimeLocal(props.event.end),
    };
}

watch(() => props.show, (visible) => {
    if (visible) {
        eventoHint.value = '';
        setErrors({});
        resetForm({ values: valoresOriginales() });
    }
});

watch([() => props.mode, () => props.event], () => {
    if (props.show) {
        resetForm({ values: valoresOriginales() });
    }
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

const submit = handleSubmit((values) => {
    submitting.value = true;

    // FormData a propósito: AgendaController usa $request->get(...) para leer
    // title/evento/start/end/delete, y ese método no ve el body JSON (solo los
    // bags de query/request). Con multipart entra por $_POST y funciona igual
    // que el formulario jQuery original.
    const formData = new FormData();
    if (props.event?.id) {
        formData.append('id', String(props.event.id));
    }
    formData.append('title', values.title);
    formData.append('evento', values.evento);
    formData.append('color', values.color);
    formData.append('start', values.start);
    formData.append('end', values.end);

    const request = {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
        onError: (serverErrors) => {
            setErrors(serverErrors);
        },
        onFinish: () => {
            submitting.value = false;
        },
    };

    if (isCreate.value) {
        router.post('/agendas', formData, request);
    } else {
        router.post('/agendas/update', formData, request);
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
        max-width="2xl"
        :is-form="!isView"
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
                    :readonly="isView"
                    placeholder="Título del evento"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="[
                        errors.title ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400' : 'border-slate-300',
                        isView ? 'bg-slate-50 cursor-not-allowed' : ''
                    ]"
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
                    rows="5"
                    v-model="evento"
                    v-bind="eventoAttrs"
                    :readonly="isView"
                    placeholder="Descripcion del evento"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="[
                        errors.evento ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400' : 'border-slate-300',
                        isView ? 'bg-slate-50 cursor-not-allowed' : ''
                    ]"
                    @keydown="bloquearEnter"
                    @keydown.shift.tab.exact="bloquearComilla"
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
                <label for="color" class="block text-xs font-bold text-slate-700 mb-1">
                    Sección <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-2">
                    <span
                        class="w-9 h-9 rounded-lg border shadow-xs shrink-0 transition-colors"
                        :style="{
                            backgroundColor: colorSeleccionado?.hex || 'transparent',
                            borderColor: colorSeleccionado?.hex || '#cbd5e1',
                            boxShadow: colorSeleccionado ? '0 0 0 1px rgba(0,0,0,0.15)' : 'none'
                        }"
                        aria-hidden="true"
                    ></span>
                    <select
                        id="color"
                        v-model="color"
                        v-bind="colorAttrs"
                        :disabled="isView"
                        class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="[
                            errors.color ? 'border-rose-400 bg-rose-50/20 text-rose-900' : 'border-slate-300',
                            isView ? 'bg-slate-50 cursor-not-allowed' : ''
                        ]"
                    >
                        <option value="">Seleccionar</option>
                        <option
                            v-for="op in opcionesSeccion"
                            :key="op.clave"
                            :value="op.clave"
                            :style="{ color: op.hex, fontWeight: 600 }"
                        >
                            {{ op.label }}
                        </option>
                    </select>
                </div>
                <p v-if="errors.color" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.color }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start" class="block text-xs font-bold text-slate-700 mb-1">
                        Fecha Inicial <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="start"
                        type="datetime-local"
                        v-model="start"
                        v-bind="startAttrs"
                        :readonly="isView"
                        class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="[
                            errors.start ? 'border-rose-400 bg-rose-50/20 text-rose-900' : 'border-slate-300',
                            isView ? 'bg-slate-50 cursor-not-allowed' : ''
                        ]"
                    />
                    <p v-if="errors.start" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                        <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                        {{ errors.start }}
                    </p>
                </div>

                <div>
                    <label for="end" class="block text-xs font-bold text-slate-700 mb-1">
                        Fecha Final <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="end"
                        type="datetime-local"
                        v-model="end"
                        v-bind="endAttrs"
                        :readonly="isView"
                        class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="[
                            errors.end ? 'border-rose-400 bg-rose-50/20 text-rose-900' : 'border-slate-300',
                            isView ? 'bg-slate-50 cursor-not-allowed' : ''
                        ]"
                    />
                    <p v-if="errors.end" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                        <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                        {{ errors.end }}
                    </p>
                </div>
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
                <template v-if="isView">
                    <button
                        v-if="canEdit"
                        type="button"
                        @click="emit('edit')"
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
                </template>

                <template v-else>
                    <button
                        type="button"
                        @click="emit('cancel')"
                        :disabled="submitting"
                        class="max-sm:flex-1 inline-flex items-center justify-center px-4 py-2.5 sm:py-2 text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-100 active:bg-slate-200 border border-slate-300 rounded-xl transition-colors shadow-2xs disabled:opacity-50 cursor-pointer"
                    >
                        {{ isCreate ? 'Cancelar' : 'Cancelar' }}
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
                </template>
            </div>
        </template>
    </BaseModal>
</template>
