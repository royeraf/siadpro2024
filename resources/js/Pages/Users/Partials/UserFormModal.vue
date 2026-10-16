<script setup>
import { ref, computed, watch } from 'vue';
import { useForm as useVeeForm } from 'vee-validate';
import * as yup from 'yup';
import { Search, AlertCircle, CheckCircle2, Loader2 } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import BaseModal from '@/Components/UI/BaseModal.vue';
import { UsersService } from '@/Services/users';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const isEditing = computed(() => !!props.user?.id);

// Opciones estáticas: son las mismas que el Blade legacy (create.blade.php /
// update.blade.php) tiene embebidas en el HTML.
const UGEL_OPCIONES = [
    'Ugel Huánuco', 'Ugel Ambo', 'Ugel Dos de Mayo', 'Ugel Lauricocha',
    'Ugel Leoncio Prado', 'Ugel Huacaybamba', 'Ugel Huamalies', 'Ugel Marañon',
    'Ugel Pachitea', 'Ugel Puerto Inca', 'Ugel Yarowilca',
];
const CARGOS_OPCIONES = [
    'Especialista DRE', 'Especialista UGEL', 'Director', 'Docente',
    'Profesor Coordinador', 'Promotor(a) Educativa Comunitaria',
];
const NIVELES_OPCIONES = ['Escolarizado', 'No escolarizado - PRONOEI'];
const ESTADO_OPCIONES = [
    { value: '1', label: 'Activo' },
    { value: '0', label: 'Inactivo' },
];

const submitting = ref(false);

// ── Estado de la verificación DNI / RENIEC ──
const checkingDni = ref(false);
const dniExists = ref(false);
const reniecOk = ref(false);
const dniStatus = ref(null); // { type: 'info'|'ok'|'error'|'warn', text }
const dniInfo = ref(null);   // data.user cuando ya existe
let dniTimer = null;
let lastCheckedDni = '';
const originalDni = computed(() => props.user?.dni || '');

// ── Estado de la cascada código modular → institución ──
const currentInstituciones = ref([]);
const institucionesOpciones = ref([]);
const provinciasOpciones = ref([]);
const distritosOpciones = ref([]);
const codStatus = ref(null);
let codTimer = null;

const validationSchema = yup.object({
    dni: yup
        .string()
        .trim()
        .required('El DNI es obligatorio.')
        .matches(/^\d{8}$/, 'El DNI debe tener exactamente 8 dígitos.'),
    name: yup
        .string()
        .trim()
        .required('Los apellidos y nombres son obligatorios.')
        .max(191, 'Los apellidos y nombres no deben exceder los 191 caracteres.'),
    email: yup
        .string()
        .trim()
        .required('El correo electrónico es obligatorio.')
        .email('El formato del correo electrónico no es válido.')
        .max(191, 'El correo electrónico no debe exceder los 191 caracteres.'),
    password: yup.string().test('password', function (value) {
        const v = value || '';
        if (!v) {
            // En edición la contraseña es opcional ("dejar en blanco = mantener")
            if (isEditing.value) return true;
            return this.createError({ message: 'La contraseña es obligatoria.' });
        }
        if (v.length < 6) {
            return this.createError({
                message: isEditing.value
                    ? 'La nueva contraseña debe tener al menos 6 caracteres.'
                    : 'La contraseña debe tener al menos 6 caracteres.',
            });
        }
        if (v.length > 50) {
            return this.createError({ message: 'La contraseña no debe exceder los 50 caracteres.' });
        }
        return true;
    }),
    estado: yup
        .string()
        .required('El estado es obligatorio.')
        .oneOf(['1', '0'], 'El estado seleccionado no es válido.'),
    cargo: yup
        .string()
        .required('El cargo es obligatorio.')
        .max(50, 'El cargo no debe exceder los 50 caracteres.'),
    ugel: yup.string().nullable(),
    codmodular: yup
        .string()
        .test('codmodular', 'El código modular debe tener 6 o 7 dígitos.', (v) => !v || /^\d{6,7}$/.test(v)),
    institucion: yup.string().nullable(),
    nivelinstitucion: yup.string().nullable(),
    provincia: yup.string().nullable(),
    distrito: yup.string().nullable(),
});

const { errors, defineField, handleSubmit, resetForm, setErrors } = useVeeForm({
    validationSchema,
});

const [dni, dniAttrs] = defineField('dni', { validateOnModelUpdate: false });
const [name, nameAttrs] = defineField('name', { validateOnModelUpdate: false });
const [email, emailAttrs] = defineField('email', { validateOnModelUpdate: false });
const [password, passwordAttrs] = defineField('password', { validateOnModelUpdate: false });
const [estado, estadoAttrs] = defineField('estado', { validateOnModelUpdate: false });
const [cargo, cargoAttrs] = defineField('cargo', { validateOnModelUpdate: false });
const [ugel, ugelAttrs] = defineField('ugel', { validateOnModelUpdate: false });
const [codmodular, codmodularAttrs] = defineField('codmodular', { validateOnModelUpdate: false });
const [institucion, institucionAttrs] = defineField('institucion', { validateOnModelUpdate: false });
const [nivelinstitucion, nivelinstitucionAttrs] = defineField('nivelinstitucion', { validateOnModelUpdate: false });
const [provincia, provinciaAttrs] = defineField('provincia', { validateOnModelUpdate: false });
const [distrito, distritoAttrs] = defineField('distrito', { validateOnModelUpdate: false });

function resetDynamicState() {
    clearTimeout(dniTimer);
    clearTimeout(codTimer);
    checkingDni.value = false;
    dniExists.value = false;
    reniecOk.value = false;
    dniStatus.value = null;
    dniInfo.value = null;
    lastCheckedDni = '';
    currentInstituciones.value = [];
    institucionesOpciones.value = [];
    provinciasOpciones.value = [];
    distritosOpciones.value = [];
    codStatus.value = null;
}

watch(() => props.show, (visible) => {
    if (!visible) return;
    resetDynamicState();

    if (props.user) {
        resetForm({
            values: {
                dni: String(props.user.dni || ''),
                name: props.user.name || '',
                email: props.user.email || '',
                password: '',
                estado: String(props.user.estado ?? '1'),
                cargo: props.user.cargo || '',
                ugel: props.user.ugel || '',
                codmodular: '',
                institucion: props.user.institucion || '',
                nivelinstitucion: props.user.nivelinstitucion || '',
                provincia: props.user.provincia || '',
                distrito: props.user.distrito || '',
            },
        });
    } else {
        resetForm({
            values: {
                dni: '',
                name: '',
                email: '',
                password: '',
                estado: '1',
                cargo: '',
                ugel: '',
                codmodular: '',
                institucion: '',
                nivelinstitucion: '',
                provincia: '',
                distrito: '',
            },
        });
    }
});

// ── DNI + RENIEC (portado de create.blade.php) ──
function onDniInput() {
    const clean = String(dni.value || '').replace(/\D/g, '').slice(0, 8);
    if (dni.value !== clean) dni.value = clean;

    clearTimeout(dniTimer);
    reniecOk.value = false;

    if (!clean) {
        dniExists.value = false;
        dniInfo.value = null;
        dniStatus.value = null;
        lastCheckedDni = '';
        return;
    }

    if (clean.length < 8) {
        dniExists.value = false;
        dniInfo.value = null;
        lastCheckedDni = '';
        dniStatus.value = { type: 'info', text: `Faltan ${8 - clean.length} dígito(s)` };
        return;
    }

    dniTimer = setTimeout(() => checkDni(), 150);
}

async function checkDni() {
    const val = String(dni.value || '');
    if (val.length !== 8) return;
    if (val === lastCheckedDni) return;

    // Edición: si el DNI no cambió no tiene sentido consultar al servidor.
    if (isEditing.value && val === originalDni.value) {
        lastCheckedDni = val;
        dniExists.value = false;
        dniInfo.value = null;
        dniStatus.value = { type: 'ok', text: 'DNI actual (sin cambios)' };
        return;
    }

    lastCheckedDni = val;
    checkingDni.value = true;
    dniStatus.value = { type: 'info', text: 'Verificando DNI y consultando RENIEC...' };

    try {
        const data = await UsersService.checkDni(val, isEditing.value
            ? { exclude_id: props.user.id }
            : { buscar_reniec: 1 });

        // El usuario siguió escribiendo mientras consultábamos: descartar.
        if (String(dni.value || '') !== val) return;

        if (data.exists) {
            dniExists.value = true;
            dniInfo.value = data.user || null;
            reniecOk.value = false;
            dniStatus.value = {
                type: 'error',
                text: data.message || 'Este DNI ya está registrado en el sistema.',
            };
            const u = data.user || {};
            Swal.fire({
                icon: 'warning',
                title: '¡DNI ya registrado!',
                html:
                    `<div style="text-align:left;font-size:0.95rem">El DNI <b>${val}</b> ya le pertenece al usuario:<br><br>` +
                    `<b>Nombre:</b> ${u.name || '-'}<br>` +
                    `<b>Cargo:</b> ${u.cargo || 'No especificado'}<br>` +
                    `<b>Estado:</b> ${u.estado || '-'}<br>` +
                    `<b>Correo:</b> ${u.email || '-'}` +
                    (u.institucion ? `<br><b>Institución:</b> ${u.institucion}` : '') +
                    `</div>`,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#e3342f',
            });
        } else {
            dniExists.value = false;
            dniInfo.value = null;
            if (data.reniec && data.reniec_data) {
                name.value = data.reniec_data.nombre_completo;
                reniecOk.value = true;
                dniStatus.value = {
                    type: 'ok',
                    text: `DNI disponible y verificado en RENIEC: ${data.reniec_data.nombre_completo}`,
                };
            } else {
                reniecOk.value = false;
                dniStatus.value = {
                    type: 'ok',
                    text: 'DNI disponible. (No se encontró en RENIEC, complete los apellidos y nombres manualmente)',
                };
            }
        }
    } catch (e) {
        dniStatus.value = {
            type: 'warn',
            text: 'Error al consultar el servicio de DNI. Puede escribir los nombres manualmente.',
        };
    } finally {
        checkingDni.value = false;
    }
}

function buscarReniec() {
    clearTimeout(dniTimer);
    lastCheckedDni = '';
    checkDni();
}

// ── Cascada código modular → institución (reemplaza el $.ajax de jQuery) ──
function onCodmodularInput() {
    const clean = String(codmodular.value || '').replace(/\D/g, '').slice(0, 7);
    if (codmodular.value !== clean) codmodular.value = clean;

    clearTimeout(codTimer);
    if (clean.length < 6) return;

    // Igual que el Blade: 80 ms con 7 dígitos, 250 ms con 6.
    codTimer = setTimeout(() => fetchInstituciones(clean), clean.length === 7 ? 80 : 250);
}

async function fetchInstituciones(cleanId) {
    codStatus.value = { type: 'info', text: 'Buscando institución...' };
    try {
        const instituciones = await UsersService.findInstitucionesByCodModular(cleanId);
        currentInstituciones.value = Array.isArray(instituciones) ? instituciones : [];

        if (currentInstituciones.value.length === 0) {
            institucionesOpciones.value = [];
            provinciasOpciones.value = [];
            distritosOpciones.value = [];
            codStatus.value = {
                type: 'warn',
                text: `No se encontró institución con el código ${cleanId}`,
            };
            return;
        }

        codStatus.value = {
            type: 'ok',
            text: `Institución encontrada (${currentInstituciones.value.length})`,
        };

        institucionesOpciones.value = [
            ...new Set(currentInstituciones.value.map((i) => i.nomInstitucion).filter(Boolean)),
        ];
        provinciasOpciones.value = [
            ...new Set(currentInstituciones.value.map((i) => i.provincia).filter(Boolean)),
        ];
        distritosOpciones.value = [
            ...new Set(currentInstituciones.value.map((i) => i.distrito).filter(Boolean)),
        ];

        const first = currentInstituciones.value[0];
        autoSelectUgel(first.ugel);
        autoSelectNivel(first.nivel);

        // En edición el Blade escribe directamente los 3 inputs con el primer
        // resultado; en creación solo puebla los selects.
        if (isEditing.value) {
            institucion.value = first.nomInstitucion || '';
            provincia.value = first.provincia || '';
            distrito.value = first.distrito || '';
        }
    } catch (e) {
        codStatus.value = { type: 'error', text: 'Error al consultar el servicio de instituciones' };
    }
}

function autoSelectUgel(rawValue) {
    if (!rawValue) return;
    const target = String(rawValue).toLowerCase().replace('ugel', '').trim();
    if (!target) return;
    const found = UGEL_OPCIONES.find((op) => op.toLowerCase().includes(target));
    if (found) ugel.value = found;
}

function autoSelectNivel(rawValue) {
    if (!rawValue) return;
    const nivel = String(rawValue).toLowerCase();
    if (nivel.includes('no escolariz') || nivel.includes('pronoei')) {
        nivelinstitucion.value = 'No escolarizado - PRONOEI';
    } else if (nivel.includes('escolarizado')) {
        nivelinstitucion.value = 'Escolarizado';
    }
}

function onInstitucionChange() {
    if (isEditing.value || !institucion.value) return;
    const seleccion = currentInstituciones.value.filter(
        (i) => i.nomInstitucion === institucion.value
    );
    provinciasOpciones.value = [...new Set(seleccion.map((i) => i.provincia).filter(Boolean))];
    distritosOpciones.value = [...new Set(seleccion.map((i) => i.distrito).filter(Boolean))];
    provincia.value = provinciasOpciones.value[0] || '';
    distrito.value = distritosOpciones.value[0] || '';
}

// ── Submit ──
const submit = handleSubmit((values) => {
    if (dniExists.value) return;

    submitting.value = true;
    const formData = new FormData();
    formData.append('dni', String(values.dni || '').trim());
    formData.append('name', String(values.name || '').trim());
    formData.append('email', String(values.email || '').trim());
    if (values.password) formData.append('password', values.password);
    formData.append('estado', String(values.estado));
    formData.append('cargo', String(values.cargo || ''));
    if (values.ugel) formData.append('ugel', values.ugel);
    if (values.institucion) formData.append('institucion', values.institucion);
    if (values.nivelinstitucion) formData.append('nivelinstitucion', values.nivelinstitucion);
    if (values.provincia) formData.append('provincia', values.provincia);
    if (values.distrito) formData.append('distrito', values.distrito);
    // `codmodular` NO se envía: en el Blade era solo una ayuda de búsqueda.

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
        formData.append('_method', 'PUT');
        UsersService.update(props.user.id, formData, request);
    } else {
        UsersService.store(formData, request);
    }
});

function statusClass(type) {
    if (type === 'ok') return 'text-emerald-600';
    if (type === 'error') return 'text-rose-600';
    if (type === 'warn') return 'text-amber-600';
    return 'text-blue-600';
}
</script>

<template>
    <BaseModal
        :show="show"
        :title="isEditing ? 'Editar Usuario' : 'Nuevo Usuario'"
        :subtitle="isEditing
            ? 'Modifique los campos necesarios para actualizar el registro'
            : 'Complete los campos para registrar un nuevo usuario'"
        icon="Users"
        color="blue"
        max-width="3xl"
        :submitting="submitting"
        :submit-text="isEditing ? 'Actualizar Usuario' : 'Guardar Usuario'"
        cancel-text="Cancelar"
        :submit-disabled="dniExists"
        @close="emit('close')"
        @submit="submit"
    >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <!-- DNI -->
            <div>
                <label for="user-dni" class="block text-xs font-bold text-slate-700 mb-1">
                    DNI <span class="text-rose-500">*</span>
                </label>
                <div class="flex gap-2">
                    <input
                        id="user-dni"
                        type="text"
                        v-model="dni"
                        v-bind="dniAttrs"
                        maxlength="8"
                        inputmode="numeric"
                        placeholder="8 dígitos"
                        class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="errors.dni || dniExists
                            ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus:ring-rose-400 focus:border-rose-400'
                            : 'border-slate-300'"
                        @input="onDniInput"
                    />
                    <button
                        v-if="!isEditing"
                        type="button"
                        @click="buscarReniec"
                        :disabled="checkingDni || String(dni || '').length !== 8"
                        class="shrink-0 inline-flex items-center justify-center gap-1.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-xs font-bold shadow-xs transition-colors border-0 cursor-pointer"
                        title="Buscar en RENIEC"
                    >
                        <Loader2 v-if="checkingDni" class="w-3.5 h-3.5 animate-spin" />
                        <Search v-else class="w-3.5 h-3.5" />
                        RENIEC
                    </button>
                </div>

                <p v-if="dniInfo && dniExists" class="mt-1.5 text-xs bg-rose-50 border border-rose-200 text-rose-800 rounded-lg px-2.5 py-2 leading-relaxed">
                    <strong>¡Este DNI ya está registrado!</strong><br />
                    • <strong>Usuario:</strong> {{ dniInfo.name }}<br />
                    • <strong>Cargo:</strong> {{ dniInfo.cargo || 'No especificado' }} —
                    <strong>Estado:</strong> {{ dniInfo.estado }}<br />
                    • <strong>Correo:</strong> {{ dniInfo.email }}
                    <template v-if="dniInfo.institucion"><br />• <strong>Institución:</strong> {{ dniInfo.institucion }} {{ dniInfo.ugel ? `(${dniInfo.ugel})` : '' }}</template>
                </p>
                <p
                    v-else-if="dniStatus"
                    class="mt-1.5 text-xs font-semibold flex items-start"
                    :class="statusClass(dniStatus.type)"
                >
                    <Loader2 v-if="dniStatus.type === 'info' && checkingDni" class="w-3.5 h-3.5 mr-1 mt-px shrink-0 animate-spin" />
                    <CheckCircle2 v-else-if="dniStatus.type === 'ok'" class="w-3.5 h-3.5 mr-1 mt-px shrink-0" />
                    <AlertCircle v-else class="w-3.5 h-3.5 mr-1 mt-px shrink-0" />
                    <span>{{ dniStatus.text }}</span>
                </p>
                <p v-if="errors.dni" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.dni }}
                </p>
            </div>

            <!-- Estado -->
            <div>
                <label for="user-estado" class="block text-xs font-bold text-slate-700 mb-1">
                    Estado <span class="text-rose-500">*</span>
                </label>
                <select
                    id="user-estado"
                    v-model="estado"
                    v-bind="estadoAttrs"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="errors.estado ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300'"
                >
                    <option v-for="op in ESTADO_OPCIONES" :key="op.value" :value="op.value">
                        {{ op.label }}
                    </option>
                </select>
                <p v-if="errors.estado" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.estado }}
                </p>
            </div>

            <!-- Nombres -->
            <div class="sm:col-span-2">
                <label for="user-name" class="block text-xs font-bold text-slate-700 mb-1">
                    Apellidos y Nombres <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input
                        id="user-name"
                        type="text"
                        v-model="name"
                        v-bind="nameAttrs"
                        maxlength="191"
                        placeholder="APELLIDOS Y NOMBRES"
                        class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 uppercase transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="errors.name ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300'"
                    />
                    <span
                        v-if="reniecOk"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded-md"
                    >
                        RENIEC
                    </span>
                </div>
                <p v-if="errors.name" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.name }}
                </p>
            </div>

            <!-- Correo -->
            <div>
                <label for="user-email" class="block text-xs font-bold text-slate-700 mb-1">
                    Correo electrónico <span class="text-rose-500">*</span>
                </label>
                <input
                    id="user-email"
                    type="email"
                    v-model="email"
                    v-bind="emailAttrs"
                    maxlength="191"
                    placeholder="correo@ejemplo.com"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="errors.email ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300'"
                />
                <p v-if="errors.email" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.email }}
                </p>
            </div>

            <!-- Contraseña -->
            <div>
                <label for="user-password" class="block text-xs font-bold text-slate-700 mb-1">
                    {{ isEditing ? 'Nueva contraseña' : 'Contraseña' }} <span v-if="!isEditing" class="text-rose-500">*</span>
                    <span v-else class="text-slate-400 font-normal ml-1">(opcional)</span>
                </label>
                <input
                    id="user-password"
                    type="password"
                    v-model="password"
                    v-bind="passwordAttrs"
                    maxlength="50"
                    :placeholder="isEditing ? 'Dejar en blanco para mantener la actual' : 'Mínimo 6 caracteres'"
                    autocomplete="new-password"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="errors.password ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300'"
                />
                <p v-if="errors.password" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.password }}
                </p>
            </div>

            <!-- Cargo -->
            <div>
                <label for="user-cargo" class="block text-xs font-bold text-slate-700 mb-1">
                    Cargo <span class="text-rose-500">*</span>
                </label>
                <select
                    id="user-cargo"
                    v-model="cargo"
                    v-bind="cargoAttrs"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="errors.cargo ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300'"
                >
                    <option value="">----Seleccione Cargo-----</option>
                    <option v-for="c in CARGOS_OPCIONES" :key="c" :value="c">{{ c }}</option>
                </select>
                <p v-if="errors.cargo" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.cargo }}
                </p>
            </div>

            <!-- UGEL -->
            <div>
                <label for="user-ugel" class="block text-xs font-bold text-slate-700 mb-1">UGEL</label>
                <select
                    id="user-ugel"
                    v-model="ugel"
                    v-bind="ugelAttrs"
                    class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">----Seleccione UGEL-----</option>
                    <option v-for="u in UGEL_OPCIONES" :key="u" :value="u">{{ u }}</option>
                </select>
            </div>

            <!-- Código modular (solo ayuda de búsqueda, no se envía) -->
            <div>
                <label for="user-codmodular" class="block text-xs font-bold text-slate-700 mb-1">
                    Código Modular
                    <span class="text-slate-400 font-normal ml-1">(ayuda de búsqueda)</span>
                </label>
                <input
                    id="user-codmodular"
                    type="text"
                    v-model="codmodular"
                    v-bind="codmodularAttrs"
                    maxlength="7"
                    inputmode="numeric"
                    placeholder="Ingrese 6 o 7 dígitos"
                    class="w-full text-xs bg-white border rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="errors.codmodular ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300'"
                    @input="onCodmodularInput"
                />
                <p v-if="codStatus" class="mt-1 text-xs font-semibold" :class="statusClass(codStatus.type)">
                    {{ codStatus.text }}
                </p>
                <p v-if="errors.codmodular" class="mt-1 text-xs text-rose-600 font-semibold flex items-center">
                    <AlertCircle class="w-3.5 h-3.5 mr-1 shrink-0" />
                    {{ errors.codmodular }}
                </p>
            </div>

            <!-- Institución -->
            <div>
                <label for="user-institucion" class="block text-xs font-bold text-slate-700 mb-1">Institución</label>
                <select
                    v-if="!isEditing"
                    id="user-institucion"
                    v-model="institucion"
                    v-bind="institucionAttrs"
                    class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    @change="onInstitucionChange"
                >
                    <option value="">-- Ingrese Cód. Modular o seleccione --</option>
                    <option v-for="inst in institucionesOpciones" :key="inst" :value="inst">{{ inst }}</option>
                </select>
                <input
                    v-else
                    id="user-institucion"
                    type="text"
                    v-model="institucion"
                    v-bind="institucionAttrs"
                    maxlength="191"
                    placeholder="Nombre de la institución"
                    class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 uppercase transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <!-- Tipo de II.EE -->
            <div>
                <label for="user-nivel" class="block text-xs font-bold text-slate-700 mb-1">Tipo de II.EE</label>
                <select
                    id="user-nivel"
                    v-model="nivelinstitucion"
                    v-bind="nivelinstitucionAttrs"
                    class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">----Seleccione Nivel-----</option>
                    <option v-for="n in NIVELES_OPCIONES" :key="n" :value="n">{{ n }}</option>
                </select>
            </div>

            <!-- Provincia -->
            <div>
                <label for="user-provincia" class="block text-xs font-bold text-slate-700 mb-1">Provincia</label>
                <select
                    v-if="!isEditing"
                    id="user-provincia"
                    v-model="provincia"
                    v-bind="provinciaAttrs"
                    class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">-- Seleccione la institución --</option>
                    <option v-for="p in provinciasOpciones" :key="p" :value="p">{{ p }}</option>
                </select>
                <input
                    v-else
                    id="user-provincia"
                    type="text"
                    v-model="provincia"
                    v-bind="provinciaAttrs"
                    maxlength="80"
                    placeholder="Provincia"
                    class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 uppercase transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <!-- Distrito -->
            <div>
                <label for="user-distrito" class="block text-xs font-bold text-slate-700 mb-1">Distrito</label>
                <select
                    v-if="!isEditing"
                    id="user-distrito"
                    v-model="distrito"
                    v-bind="distritoAttrs"
                    class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">-- Seleccione la institución --</option>
                    <option v-for="d in distritosOpciones" :key="d" :value="d">{{ d }}</option>
                </select>
                <input
                    v-else
                    id="user-distrito"
                    type="text"
                    v-model="distrito"
                    v-bind="distritoAttrs"
                    maxlength="80"
                    placeholder="Distrito"
                    class="w-full text-xs bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 uppercase transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
        </div>
    </BaseModal>
</template>
