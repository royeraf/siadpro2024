<script setup>
import { ref, computed, watch } from 'vue';
import { AccionsService } from '@/Services/accion';
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
    accion: {
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
    accept: '.pdf,.doc,.docx,.xls,.xlsx,.xlm,.xlsm,.ppt,.pptx,.pptm,.png,.jpg,.jpeg',
    hint: 'Formatos soportados: PDF, Word, Excel, PowerPoint, Imágenes',
});

const submitting = ref(false);

const isEditing = computed(() => !!props.accion?.id);

// Fecha actual en formato local "YYYY-MM-DD"
const today = computed(() => {
    const d = new Date();
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
});

// ══ Esquema de Validación con Vee-Validate y Yup ══
const validationSchema = computed(() => yup.object({
    nombreAccion: yup
        .string()
        .trim()
        .required('El nombre de la acción es obligatorio.')
        .min(3, 'El nombre debe tener al menos 3 caracteres.')
        .max(191, 'El nombre no debe exceder 191 caracteres.'),
    lugar: yup
        .string()
        .trim()
        .required('El lugar es obligatorio.')
        .min(2, 'El lugar debe tener al menos 2 caracteres.')
        .max(191, 'El lugar no debe exceder 191 caracteres.'),
    fecha: yup
        .string()
        .required('La fecha es obligatoria.')
        .test('no-futuro', 'La fecha no puede ser posterior al día de hoy.', (val) => {
            if (!val) return false;
            return val <= today.value;
        }),
    descripcion: yup
        .string()
        .nullable()
        .max(1000, 'La descripción no debe exceder 1000 caracteres.'),
}));

const { errors, defineField, handleSubmit, resetForm, setErrors } = useVeeForm({
    validationSchema,
});

const [nombreAccion, nombreAccionAttrs] = defineField('nombreAccion', {
    validateOnModelUpdate: true,
});
const [lugar, lugarAttrs] = defineField('lugar', {
    validateOnModelUpdate: true,
});
const [fecha, fechaAttrs] = defineField('fecha', {
    validateOnModelUpdate: true,
});
const [descripcion, descripcionAttrs] = defineField('descripcion', {
    validateOnModelUpdate: true,
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        resetFile();

        if (props.accion) {
            resetForm({
                values: {
                    nombreAccion: props.accion.nombreAccion || '',
                    lugar: props.accion.lugar || '',
                    fecha: props.accion.fecha ? props.accion.fecha.split(' ')[0] : '',
                    descripcion: props.accion.descripcion || '',
                },
            });
        } else {
            resetForm({
                values: {
                    nombreAccion: '',
                    lugar: '',
                    fecha: today.value,
                    descripcion: '',
                },
            });
        }
    }
});

const submit = handleSubmit((values) => {
    fileError.value = '';

    // Validar archivo si es creación
    if (!isEditing.value && !selectedFile.value) {
        fileError.value = 'Debe adjuntar un archivo para el registro.';
        return;
    }

    submitting.value = true;
    const formData = new FormData();
    formData.append('nombreAccion', values.nombreAccion);
    formData.append('lugar', values.lugar);
    formData.append('fecha', values.fecha);
    if (values.descripcion) {
        formData.append('descripcion', values.descripcion);
    }
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
        AccionsService.update(props.accion.id, formData, request);
    } else {
        AccionsService.store(formData, request);
    }
});
</script>

<template>
    <BaseModal
        :show="show"
        :title="isEditing ? 'Editar Acción de Sensibilización' : 'Nueva Acción de Sensibilización'"
        :subtitle="isEditing ? 'Modifique los campos necesarios para actualizar el registro' : 'Complete los campos para registrar una nueva acción pedagógica'"
        icon="Megaphone"
        color="yellow"
        max-width="2xl"
        :submitting="submitting"
        :submit-text="isEditing ? 'Actualizar Registro' : 'Guardar Registro'"
        cancel-text="Cancelar"
        @close="emit('close')"
        @submit="submit"
    >
        <!-- ══ CAMPOS DEL FORMULARIO CON VEE-VALIDATE ══ -->
        <div class="space-y-4">
            <!-- Nombre de la Acción -->
            <div>
                <label for="nombreAccion" class="block text-xs font-bold text-slate-700 mb-1">
                    Nombre de la Acción <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        id="nombreAccion"
                        type="text" 
                        v-model="nombreAccion"
                        v-bind="nombreAccionAttrs"
                        placeholder="Ej. Taller de sensibilización pedagógica integral"
                        class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="errors.nombreAccion ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400' : 'border-slate-300'"
                    />
                </div>
                <p v-if="errors.nombreAccion" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.nombreAccion }}
                </p>
            </div>

            <!-- Fila: Lugar y Fecha -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Lugar -->
                <div>
                    <label for="lugar" class="block text-xs font-bold text-slate-700 mb-1">
                        Lugar <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            id="lugar"
                            type="text" 
                            v-model="lugar"
                            v-bind="lugarAttrs"
                            placeholder="Ej. Auditorio Central / I.E. San Martín"
                            class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="errors.lugar ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400' : 'border-slate-300'"
                        />
                    </div>
                    <p v-if="errors.lugar" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                        <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                        {{ errors.lugar }}
                    </p>
                </div>

                <!-- Fecha -->
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
            </div>

            <!-- Descripción -->
            <div>
                <label for="descripcion" class="block text-xs font-bold text-slate-700 mb-1">
                    Descripción / Resumen
                </label>
                <textarea 
                    id="descripcion"
                    rows="3"
                    v-model="descripcion"
                    v-bind="descripcionAttrs"
                    placeholder="Ingrese detalles o aspectos relevantes de la acción realizada..."
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="errors.descripcion ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400' : 'border-slate-300'"
                ></textarea>
                <p v-if="errors.descripcion" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.descripcion }}
                </p>
            </div>

            <!-- Adjuntar Documento -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Documento de Evidencia <span v-if="!isEditing" class="text-rose-500">*</span>
                    <span v-else class="text-slate-400 font-normal ml-1">(Opcional: solo si desea reemplazar el actual)</span>
                </label>

                <!-- Archivo existente en modo edición -->
                <div v-if="isEditing && accion.enlace && !selectedFile" class="mb-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <Paperclip class="w-4 h-4 text-slate-500 mr-2 shrink-0" />
                        <span class="text-xs text-slate-700 font-medium truncate">
                            Archivo actual: <strong class="text-slate-900">{{ accion.enlace.split('/').pop() }}</strong>
                        </span>
                    </div>
                    <span class="text-[11px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200 shrink-0 ml-2">
                        Conservado
                    </span>
                </div>

                <!-- Zona Drag & Drop -->
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
