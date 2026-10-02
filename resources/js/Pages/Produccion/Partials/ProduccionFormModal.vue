<script setup>
import { ref, computed, watch } from 'vue';
import { ProduccionsService } from '@/Services/produccion';
import { useForm as useVeeForm } from 'vee-validate';
import * as yup from 'yup';
import { AlertCircle, Paperclip } from 'lucide-vue-next';
import BaseModal from '@/Components/UI/BaseModal.vue';
import FileDropzone from '@/Components/UI/FileDropzone.vue';
import { useDocumentUpload } from '@/Composables/useDocumentUpload';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    produccion: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const {
    accept: fileAccept,
    hint: fileHint,
    compressors: pdfCompressors,
    showCompressors,
    file: selectedFile,
    fileError,
    uploading,
    progress,
    setFile,
    setServerError,
    removeFile,
    reset: resetFile,
    startUpload,
    trackProgress,
    finishUpload,
} = useDocumentUpload({
    accept: '.pdf,.xls,.xlsx,.ppt,.pptx,.doc,.docx',
    extensions: ['pdf', 'xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx'],
    hint: 'PDF, Word, Excel o PowerPoint',
});

const submitting = ref(false);

const isEditing = computed(() => !!props.produccion?.id);

const today = computed(() => {
    const d = new Date();
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
});

const validationSchema = computed(() => yup.object({
    nombreProduccion: yup
        .string()
        .trim()
        .required('El tipo de producción es obligatorio.')
        .min(3, 'El tipo debe tener al menos 3 caracteres.')
        .max(191, 'El tipo no debe exceder 191 caracteres.'),
    descripcion: yup
        .string()
        .trim()
        .required('La descripción es obligatoria.'),
    fecha: yup
        .string()
        .required('La fecha es obligatoria.')
        .test('no-futuro', 'La fecha no puede ser posterior al día de hoy.', (val) => {
            if (!val) return false;
            return val <= today.value;
        }),
}));

const { errors, defineField, handleSubmit, resetForm, setErrors } = useVeeForm({
    validationSchema,
});

const [nombreProduccion, nombreProduccionAttrs] = defineField('nombreProduccion', {
    validateOnModelUpdate: true,
});
const [descripcion, descripcionAttrs] = defineField('descripcion', {
    validateOnModelUpdate: true,
});
const [fecha, fechaAttrs] = defineField('fecha', {
    validateOnModelUpdate: true,
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        resetFile();

        if (props.produccion) {
            resetForm({
                values: {
                    nombreProduccion: props.produccion.nombreProduccion || '',
                    descripcion: props.produccion.descripcion || '',
                    fecha: props.produccion.fecha ? props.produccion.fecha.split(' ')[0] : '',
                },
            });
        } else {
            resetForm({
                values: {
                    nombreProduccion: '',
                    descripcion: '',
                    fecha: today.value,
                },
            });
        }
    }
});

const submit = handleSubmit((values) => {
    fileError.value = '';

    if (!isEditing.value && !selectedFile.value) {
        fileError.value = 'Debe adjuntar un archivo para el registro.';
        return;
    }

    submitting.value = true;
    const formData = new FormData();
    formData.append('nombreProduccion', values.nombreProduccion);
    formData.append('descripcion', values.descripcion);
    formData.append('fecha', values.fecha);
    if (selectedFile.value) {
        formData.append('documento', selectedFile.value);
    }

    const request = {
        preserveScroll: true,
        onStart: startUpload,
        onProgress: trackProgress,
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
        onError: (serverErrors) => {
            if (serverErrors.documento) {
                setServerError(serverErrors.documento);
                delete serverErrors.documento;
            }
            setErrors(serverErrors);
        },
        onFinish: () => {
            finishUpload();
            submitting.value = false;
        },
    };

    if (isEditing.value) {
        formData.append('_method', 'PUT');
        ProduccionsService.update(props.produccion.id, formData, request);
    } else {
        ProduccionsService.store(formData, request);
    }
});
</script>

<template>
    <BaseModal
        :show="show"
        :title="isEditing ? 'Editar Producción de Textos' : 'Nueva Producción de Textos'"
        :subtitle="isEditing ? 'Modifique los campos necesarios para actualizar el registro' : 'Complete los campos para registrar una nueva producción de textos infantiles'"
        icon="NotebookPen"
        color="green"
        max-width="2xl"
        :submitting="submitting"
        :submit-text="isEditing ? 'Actualizar Registro' : 'Guardar Registro'"
        cancel-text="Cancelar"
        @close="emit('close')"
        @submit="submit"
    >
        <div class="space-y-4">
            <div>
                <label for="nombreProduccion" class="block text-xs font-bold text-slate-700 mb-1">
                    Tipo de Producción <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input
                        id="nombreProduccion"
                        type="text"
                        v-model="nombreProduccion"
                        v-bind="nombreProduccionAttrs"
                        placeholder="Ej. Cuento infantil"
                        class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="errors.nombreProduccion ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400' : 'border-slate-300'"
                    />
                </div>
                <p v-if="errors.nombreProduccion" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.nombreProduccion }}
                </p>
            </div>

            <div>
                <label for="descripcion" class="block text-xs font-bold text-slate-700 mb-1">
                    Descripción <span class="text-rose-500">*</span>
                </label>
                <textarea
                    id="descripcion"
                    rows="3"
                    v-model="descripcion"
                    v-bind="descripcionAttrs"
                    placeholder="Ingrese detalles o aspectos relevantes de la producción de textos..."
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="errors.descripcion ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400' : 'border-slate-300'"
                ></textarea>
                <p v-if="errors.descripcion" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.descripcion }}
                </p>
            </div>

            <div>
                <label for="fecha" class="block text-xs font-bold text-slate-700 mb-1">
                    Fecha <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input
                        id="fecha"
                        type="date"
                        v-model="fecha"
                        v-bind="fechaAttrs"
                        :max="today"
                        class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="errors.fecha ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400' : 'border-slate-300'"
                    />
                </div>
                <p v-if="errors.fecha" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.fecha }}
                </p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Documento <span v-if="!isEditing" class="text-rose-500">*</span>
                    <span v-else class="text-slate-400 font-normal ml-1">(Opcional: solo si desea reemplazar el actual)</span>
                </label>

                <div v-if="isEditing && produccion.enlace && !selectedFile" class="mb-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <Paperclip class="w-4 h-4 text-slate-500 mr-2 shrink-0" />
                        <span class="text-xs text-slate-700 font-medium truncate">
                            Archivo actual: <strong class="text-slate-900">{{ produccion.enlace.split('/').pop() }}</strong>
                        </span>
                    </div>
                    <span class="text-[11px] text-blue-700 font-bold bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200 shrink-0 ml-2">
                        Conservado
                    </span>
                </div>

                <FileDropzone
                    :file="selectedFile"
                    :error="fileError"
                    :uploading="uploading"
                    :progress="progress"
                    :accept="fileAccept"
                    :hint="fileHint"
                    :compressors="pdfCompressors"
                    :show-compressors="showCompressors"
                    @pick="setFile"
                    @remove="removeFile"
                />
            </div>
        </div>
    </BaseModal>
</template>
