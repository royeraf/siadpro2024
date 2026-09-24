<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useForm as useVeeForm } from 'vee-validate';
import * as yup from 'yup';
import {
    Upload, CheckCircle2, AlertCircle,
    X, Paperclip
} from 'lucide-vue-next';
import BaseModal from '@/Components/UI/BaseModal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    informe: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

// Extensiones admitidas por `store()` (mimetypes: pdf / xls / xlsx / ppt /
// pptx / doc / docx). El servidor sigue siendo quien valida en última instancia.
const EXTENSIONES_OK = ['pdf', 'xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx'];

const fileInput = ref(null);
const selectedFile = ref(null);
const selectedFileName = ref('');
const selectedFileSize = ref('');
const isDragging = ref(false);
const submitting = ref(false);
const fileError = ref('');

const isEditing = computed(() => !!props.informe?.id);

const today = computed(() => {
    const d = new Date();
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
});

const validationSchema = computed(() => yup.object({
    nombreInforme: yup
        .string()
        .trim()
        .required('El nombre de la biblioteca es obligatorio.')
        .min(3, 'El nombre debe tener al menos 3 caracteres.')
        .max(191, 'El nombre no debe exceder 191 caracteres.'),
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

const [nombreInforme, nombreInformeAttrs] = defineField('nombreInforme', {
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
        fileError.value = '';
        selectedFile.value = null;
        selectedFileName.value = '';
        selectedFileSize.value = '';
        if (fileInput.value) fileInput.value.value = '';

        if (props.informe) {
            resetForm({
                values: {
                    nombreInforme: props.informe.nombreInforme || '',
                    descripcion: props.informe.descripcion || '',
                    fecha: props.informe.fecha ? props.informe.fecha.split(' ')[0] : '',
                },
            });
        } else {
            resetForm({
                values: {
                    nombreInforme: '',
                    descripcion: '',
                    fecha: today.value,
                },
            });
        }
    }
});

function formatBytes(bytes, decimals = 1) {
    if (!+bytes) return '0 B';
    const k = 1024;
    const dm = decimals < 0 ? decimals : decimals;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
}

function handleFileSelect(e) {
    const file = e.target.files?.[0];
    if (file) {
        setFile(file);
    }
}

function handleDrop(e) {
    isDragging.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) {
        setFile(file);
    }
}

function setFile(file) {
    fileError.value = '';
    const ext = (file.name.split('.').pop() || '').toLowerCase();
    if (!EXTENSIONES_OK.includes(ext)) {
        fileError.value = `Formato no permitido (.${ext}). Admitidos: ${EXTENSIONES_OK.join(', ')}.`;
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        fileError.value = 'El archivo no debe ser superior a 10MB.';
        return;
    }
    selectedFile.value = file;
    selectedFileName.value = file.name;
    selectedFileSize.value = formatBytes(file.size);
}

function removeFile() {
    selectedFile.value = null;
    selectedFileName.value = '';
    selectedFileSize.value = '';
    fileError.value = '';
    if (fileInput.value) fileInput.value.value = '';
}

const submit = handleSubmit((values) => {
    fileError.value = '';

    if (!isEditing.value && !selectedFile.value) {
        fileError.value = 'Debe adjuntar un archivo para el registro.';
        return;
    }

    submitting.value = true;
    const formData = new FormData();
    formData.append('nombreInforme', values.nombreInforme);
    formData.append('descripcion', values.descripcion);
    formData.append('fecha', values.fecha);
    if (selectedFile.value) {
        formData.append('documento', selectedFile.value);
    }

    const request = {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('close');
        },
        onError: (serverErrors) => {
            if (serverErrors.documento) {
                fileError.value = serverErrors.documento;
                delete serverErrors.documento;
            }
            setErrors(serverErrors);
        },
        onFinish: () => {
            submitting.value = false;
        },
    };

    if (isEditing.value) {
        formData.append('_method', 'PUT');
        router.post(`/informes/${props.informe.id}`, formData, request);
    } else {
        router.post('/informes', formData, request);
    }
});
</script>

<template>
    <BaseModal
        :show="show"
        :title="isEditing ? 'Editar Biblioteca del Aula' : 'Nueva Biblioteca del Aula'"
        :subtitle="isEditing ? 'Modifique los campos necesarios para actualizar el registro' : 'Complete los campos para registrar una nueva biblioteca del aula'"
        icon="BookOpen"
        color="orange"
        max-width="2xl"
        :submitting="submitting"
        :submit-text="isEditing ? 'Actualizar Registro' : 'Guardar Registro'"
        cancel-text="Cancelar"
        @close="emit('close')"
        @submit="submit"
    >
        <div class="space-y-4">
            <div>
                <label for="nombreInforme" class="block text-xs font-bold text-slate-700 mb-1">
                    Nombre de la Biblioteca <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input
                        id="nombreInforme"
                        type="text"
                        v-model="nombreInforme"
                        v-bind="nombreInformeAttrs"
                        placeholder="Ej. Biblioteca de aula"
                        class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="errors.nombreInforme ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400' : 'border-slate-300'"
                    />
                </div>
                <p v-if="errors.nombreInforme" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.nombreInforme }}
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
                    placeholder="Ingrese detalles o aspectos relevantes de la biblioteca del aula..."
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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

                <div v-if="isEditing && informe.enlace && !selectedFileName" class="mb-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <Paperclip class="w-4 h-4 text-slate-500 mr-2 shrink-0" />
                        <span class="text-xs text-slate-700 font-medium truncate">
                            Archivo actual: <strong class="text-slate-900">{{ informe.enlace.split('/').pop() }}</strong>
                        </span>
                    </div>
                    <span class="text-[11px] text-blue-700 font-bold bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200 shrink-0 ml-2">
                        Conservado
                    </span>
                </div>

                <div
                    class="border-2 border-dashed rounded-2xl p-4 text-center transition-colors cursor-pointer"
                    :class="[
                        isDragging ? 'border-blue-500 bg-blue-50/50' : 'border-slate-300 hover:border-blue-400 hover:bg-slate-50/50',
                        fileError ? 'border-rose-400 bg-rose-50/10' : ''
                    ]"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                    @click="fileInput?.click()"
                >
                    <input
                        type="file"
                        ref="fileInput"
                        class="hidden"
                        accept=".pdf,.xls,.xlsx,.ppt,.pptx,.doc,.docx"
                        @change="handleFileSelect"
                    />

                    <div v-if="selectedFileName" class="flex items-center justify-between bg-blue-50/70 border border-blue-200 p-2.5 rounded-xl">
                        <div class="flex items-center min-w-0">
                            <CheckCircle2 class="w-4 h-4 text-emerald-600 mr-2 shrink-0" />
                            <div class="text-left min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate m-0">{{ selectedFileName }}</p>
                                <p class="text-[11px] text-slate-500 m-0">{{ selectedFileSize }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click.stop="removeFile"
                            class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-white transition-colors ml-2 cursor-pointer"
                            title="Quitar archivo"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <div v-else class="py-2">
                        <Upload class="w-7 h-7 text-blue-500 mx-auto mb-1.5" />
                        <p class="text-xs font-semibold text-slate-700 m-0">
                            Haga clic o arrastre su archivo aquí
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1 m-0">
                            PDF, Word, Excel o PowerPoint · Máx. 10MB
                        </p>
                    </div>
                </div>

                <p v-if="fileError" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ fileError }}
                </p>
            </div>
        </div>
    </BaseModal>
</template>
