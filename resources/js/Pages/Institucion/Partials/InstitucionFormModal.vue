<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useForm as useVeeForm } from 'vee-validate';
import * as yup from 'yup';
import { AlertCircle } from 'lucide-vue-next';
import BaseModal from '@/Components/UI/BaseModal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    institucion: {
        type: Object,
        default: null,
    },
    listaUgels: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close', 'saved']);

const submitting = ref(false);

const isEditing = computed(() => !!props.institucion?.id);

const NIVELES = [
    { value: 'Inicial-Jardin', label: 'Inicial - Jardín' },
    { value: 'Primaria', label: 'Primaria' },
    { value: 'Secundaria', label: 'Secundaria' },
];

const validationSchema = computed(() => yup.object({
    nomInstitucion: yup
        .string()
        .trim()
        .required('El nombre de la institución es obligatorio.')
        .max(191, 'El nombre de la institución no debe exceder los 191 caracteres.'),
    codModular: yup
        .string()
        .trim()
        .required('El código modular es obligatorio.')
        .matches(/^[0-9]{1,9}$/, 'El código modular debe contener hasta 9 dígitos numéricos.'),
    nivel: yup
        .string()
        .required('El nivel es obligatorio.')
        .oneOf(NIVELES.map((n) => n.value), 'El nivel seleccionado no es válido.'),
    provincia: yup
        .string()
        .trim()
        .required('La provincia es obligatoria.')
        .max(40, 'La provincia no debe exceder los 40 caracteres.'),
    distrito: yup
        .string()
        .trim()
        .required('El distrito es obligatorio.')
        .max(40, 'El distrito no debe exceder los 40 caracteres.'),
    centropoblado: yup
        .string()
        .trim()
        .required('El centro poblado es obligatorio.')
        .max(60, 'El centro poblado no debe exceder los 60 caracteres.'),
    ugel: yup
        .string()
        .trim()
        .required('La UGEL es obligatoria.')
        .max(50, 'La UGEL no debe exceder los 50 caracteres.'),
}));

const { errors, defineField, handleSubmit, resetForm, setErrors } = useVeeForm({
    validationSchema,
});

const [nomInstitucion, nomInstitucionAttrs] = defineField('nomInstitucion', {
    validateOnModelUpdate: true,
});
const [codModular, codModularAttrs] = defineField('codModular', {
    validateOnModelUpdate: true,
});
const [nivel, nivelAttrs] = defineField('nivel', {
    validateOnModelUpdate: true,
});
const [provincia, provinciaAttrs] = defineField('provincia', {
    validateOnModelUpdate: true,
});
const [distrito, distritoAttrs] = defineField('distrito', {
    validateOnModelUpdate: true,
});
const [centropoblado, centropobladoAttrs] = defineField('centropoblado', {
    validateOnModelUpdate: true,
});
const [ugel, ugelAttrs] = defineField('ugel', {
    validateOnModelUpdate: true,
});

watch(() => props.show, (newVal) => {
    if (!newVal) return;

    if (props.institucion) {
        resetForm({
            values: {
                nomInstitucion: props.institucion.nomInstitucion || '',
                codModular: props.institucion.codModular || '',
                nivel: props.institucion.nivel || '',
                provincia: props.institucion.provincia || '',
                distrito: props.institucion.distrito || '',
                centropoblado: props.institucion.centropoblado || '',
                ugel: props.institucion.ugel || '',
            },
        });
    } else {
        resetForm({
            values: {
                nomInstitucion: '',
                codModular: '',
                nivel: '',
                provincia: '',
                distrito: '',
                centropoblado: '',
                ugel: '',
            },
        });
    }
});

const submit = handleSubmit((values) => {
    submitting.value = true;

    const request = {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
        onError: (serverErrors) => {
            setErrors(serverErrors);
        },
        onFinish: () => {
            submitting.value = false;
        },
    };

    if (isEditing.value) {
        router.put(`/institucions/${props.institucion.id}`, values, request);
    } else {
        router.post('/institucions', values, request);
    }
});

function inputClass(field) {
    return errors.value[field]
        ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400'
        : 'border-slate-300';
}
</script>

<template>
    <BaseModal
        :show="show"
        :title="isEditing ? 'Editar Institución' : 'Nueva Institución'"
        :subtitle="isEditing ? 'Modifique los campos necesarios para actualizar el registro' : 'Complete los campos para registrar una nueva institución'"
        icon="Landmark"
        color="indigo"
        max-width="2xl"
        :submitting="submitting"
        :submit-text="isEditing ? 'Actualizar Registro' : 'Guardar Registro'"
        cancel-text="Cancelar"
        @close="emit('close')"
        @submit="submit"
    >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="nomInstitucion" class="block text-xs font-bold text-slate-700 mb-1">
                    Nombre de la Institución <span class="text-rose-500">*</span>
                </label>
                <input
                    id="nomInstitucion"
                    type="text"
                    v-model="nomInstitucion"
                    v-bind="nomInstitucionAttrs"
                    placeholder="Ej. I.E. SAN MARTIN DE PORRES"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="inputClass('nomInstitucion')"
                />
                <p v-if="errors.nomInstitucion" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.nomInstitucion }}
                </p>
            </div>

            <div>
                <label for="codModular" class="block text-xs font-bold text-slate-700 mb-1">
                    Código Modular <span class="text-rose-500">*</span>
                </label>
                <input
                    id="codModular"
                    type="text"
                    inputmode="numeric"
                    maxlength="9"
                    v-model="codModular"
                    v-bind="codModularAttrs"
                    placeholder="Ej. 0234567"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="inputClass('codModular')"
                />
                <p v-if="errors.codModular" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.codModular }}
                </p>
            </div>

            <div>
                <label for="nivel" class="block text-xs font-bold text-slate-700 mb-1">
                    Nivel <span class="text-rose-500">*</span>
                </label>
                <select
                    id="nivel"
                    v-model="nivel"
                    v-bind="nivelAttrs"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="inputClass('nivel')"
                >
                    <option value="">-- Seleccione nivel --</option>
                    <option v-for="opcion in NIVELES" :key="opcion.value" :value="opcion.value">
                        {{ opcion.label }}
                    </option>
                </select>
                <p v-if="errors.nivel" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.nivel }}
                </p>
            </div>

            <div>
                <label for="provincia" class="block text-xs font-bold text-slate-700 mb-1">
                    Provincia <span class="text-rose-500">*</span>
                </label>
                <input
                    id="provincia"
                    type="text"
                    v-model="provincia"
                    v-bind="provinciaAttrs"
                    placeholder="Ej. HUANUCO"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="inputClass('provincia')"
                />
                <p v-if="errors.provincia" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.provincia }}
                </p>
            </div>

            <div>
                <label for="distrito" class="block text-xs font-bold text-slate-700 mb-1">
                    Distrito <span class="text-rose-500">*</span>
                </label>
                <input
                    id="distrito"
                    type="text"
                    v-model="distrito"
                    v-bind="distritoAttrs"
                    placeholder="Ej. HUANUCO"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="inputClass('distrito')"
                />
                <p v-if="errors.distrito" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.distrito }}
                </p>
            </div>

            <div>
                <label for="centropoblado" class="block text-xs font-bold text-slate-700 mb-1">
                    Centro Poblado <span class="text-rose-500">*</span>
                </label>
                <input
                    id="centropoblado"
                    type="text"
                    v-model="centropoblado"
                    v-bind="centropobladoAttrs"
                    placeholder="Ej. PILLAO"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="inputClass('centropoblado')"
                />
                <p v-if="errors.centropoblado" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.centropoblado }}
                </p>
            </div>

            <div>
                <label for="ugel" class="block text-xs font-bold text-slate-700 mb-1">
                    UGEL <span class="text-rose-500">*</span>
                </label>
                <input
                    id="ugel"
                    type="text"
                    v-model="ugel"
                    v-bind="ugelAttrs"
                    placeholder="Ej. UGEL HUANUCO"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="inputClass('ugel')"
                    list="ugels-list"
                />
                <datalist id="ugels-list">
                    <option v-for="ugelExistente in listaUgels" :key="ugelExistente" :value="ugelExistente" />
                </datalist>
                <p v-if="errors.ugel" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.ugel }}
                </p>
            </div>
        </div>
    </BaseModal>
</template>
