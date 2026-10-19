<script setup>
import { ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useForm as useVeeForm } from 'vee-validate';
import * as yup from 'yup';
import Swal from 'sweetalert2';
import { AlertCircle, Eye, EyeOff, Loader2, LogIn, Mail } from 'lucide-vue-next';

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const validationSchema = yup.object({
    email: yup
        .string()
        .trim()
        .required('El correo electrónico es obligatorio.')
        .email('El formato del correo electrónico no es válido.'),
    password: yup.string().required('La contraseña es obligatoria.'),
    remember: yup.boolean(),
});

const { errors, defineField, handleSubmit } = useVeeForm({
    validationSchema,
    initialValues: { email: '', password: '', remember: false },
});

const [email, emailAttrs] = defineField('email', { validateOnModelUpdate: false });
const [password, passwordAttrs] = defineField('password', { validateOnModelUpdate: false });
const [remember, rememberAttrs] = defineField('remember');

// Los errores que devuelve el servidor (Fortify) viven en `form.errors` y no se
// limpian al editar por sí solos. Los borramos en cuanto el usuario corrige el
// campo para que el mensaje inline desaparezca en el momento.
watch(email, () => {
    if (form.errors.email) form.clearErrors('email');
});
watch(password, () => {
    if (form.errors.password) form.clearErrors('password');
});

const submit = handleSubmit((values) => {
    form.email = String(values.email || '').trim();
    form.password = values.password || '';
    form.remember = !!values.remember;

    form.post('/login', {
        onError: (serverErrors) => {
            const mensaje = Object.values(serverErrors || {}).find(Boolean) || 'No se pudo iniciar sesión.';
            Swal.fire({
                title: 'No se pudo iniciar sesión',
                text: mensaje,
                icon: 'error',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl border border-slate-100 p-6',
                    title: 'text-base sm:text-lg font-bold text-slate-800',
                    htmlContainer: 'text-xs sm:text-sm text-slate-600',
                },
                buttonsStyling: false,
            });
        },
    });
});
</script>

<template>
    <Head title="Iniciar sesión · SIADPRO" />

    <div
        class="login-bg flex min-h-screen w-full flex-col items-center justify-center bg-white px-4 py-8 sm:py-10"
        :style="{ backgroundImage: `url('/vendor/adminlte/dist/img/infantil.jpg')` }"
    >
        <div class="mb-5 flex items-center justify-center gap-2.5 sm:mb-6 sm:gap-3">
            <img
                src="/vendor/adminlte/dist/img/inicial.png"
                alt="SIADPRO"
                class="h-10 w-10 object-contain sm:h-12 sm:w-12"
            />
            <span class="text-xl font-extrabold tracking-wide text-[#192761] sm:text-2xl">SIADPRO</span>
        </div>

        <div class="w-full max-w-[360px] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="bg-indigo-600 px-5 py-3 text-center">
                <h1 class="text-sm font-bold tracking-tight text-white sm:text-base">Inicia sesión</h1>
            </div>

            <form class="space-y-5 p-5 sm:p-6" novalidate @submit.prevent="submit">
                <!-- Correo -->
                <div>
                    <div class="relative">
                        <input
                            id="email"
                            v-model="email"
                            v-bind="emailAttrs"
                            type="email"
                            placeholder=" "
                            required
                            autofocus
                            autocomplete="email"
                            :disabled="form.processing"
                            class="peer w-full rounded-xl border bg-white px-3 py-2.5 pr-10 text-xs text-slate-800 transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-60"
                            :class="errors.email || form.errors.email
                                ? 'border-rose-400 bg-rose-50/20 focus:border-rose-400 focus:ring-rose-400'
                                : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500'"
                            :aria-invalid="!!(errors.email || form.errors.email)"
                            aria-describedby="email-error"
                        />
                        <label
                            for="email"
                            class="pointer-events-none absolute left-0 -top-1.5 -translate-y-full select-none text-[11px] font-bold leading-none text-slate-700 transition-all duration-150 peer-placeholder-shown:left-3 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-xs peer-placeholder-shown:font-normal peer-placeholder-shown:text-slate-400 peer-focus:!left-0 peer-focus:!-top-1.5 peer-focus:!-translate-y-full peer-focus:!text-[11px] peer-focus:!font-bold peer-focus:!text-indigo-600"
                        >
                            Correo
                        </label>
                        <Mail
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                    </div>
                    <p
                        v-show="!!(errors.email || form.errors.email)"
                        id="email-error"
                        class="mt-1.5 flex items-start text-xs font-semibold leading-[1.125rem] text-rose-600"
                        role="alert"
                    >
                        <AlertCircle class="mr-1 mt-0.5 h-3.5 w-3.5 shrink-0" />
                        <span>{{ errors.email || form.errors.email }}</span>
                    </p>
                </div>

                <!-- Contraseña -->
                <div>
                    <div class="relative">
                        <input
                            id="password"
                            v-model="password"
                            v-bind="passwordAttrs"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder=" "
                            required
                            autocomplete="current-password"
                            :disabled="form.processing"
                            class="peer w-full rounded-xl border bg-white px-3 py-2.5 pr-10 text-xs text-slate-800 transition focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:opacity-60"
                            :class="errors.password || form.errors.password
                                ? 'border-rose-400 bg-rose-50/20 focus:border-rose-400 focus:ring-rose-400'
                                : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500'"
                            :aria-invalid="!!(errors.password || form.errors.password)"
                            aria-describedby="password-error"
                        />
                        <label
                            for="password"
                            class="pointer-events-none absolute left-0 -top-1.5 -translate-y-full select-none text-[11px] font-bold leading-none text-slate-700 transition-all duration-150 peer-placeholder-shown:left-3 peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:text-xs peer-placeholder-shown:font-normal peer-placeholder-shown:text-slate-400 peer-focus:!left-0 peer-focus:!-top-1.5 peer-focus:!-translate-y-full peer-focus:!text-[11px] peer-focus:!font-bold peer-focus:!text-indigo-600"
                        >
                            Contraseña
                        </label>
                        <button
                            type="button"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
                            :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                            :aria-pressed="showPassword"
                            @click="showPassword = !showPassword"
                        >
                            <EyeOff v-if="showPassword" class="h-4 w-4" />
                            <Eye v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <p
                        v-show="!!(errors.password || form.errors.password)"
                        id="password-error"
                        class="mt-1.5 flex items-start text-xs font-semibold leading-[1.125rem] text-rose-600"
                        role="alert"
                    >
                        <AlertCircle class="mr-1 mt-0.5 h-3.5 w-3.5 shrink-0" />
                        <span>{{ errors.password || form.errors.password }}</span>
                    </p>
                </div>

                <div class="flex flex-col gap-4 pt-1 sm:flex-row sm:items-center sm:justify-between sm:gap-2">
                    <div class="flex items-center gap-2">
                        <input
                            id="remember"
                            v-model="remember"
                            v-bind="rememberAttrs"
                            type="checkbox"
                            class="h-4 w-4 shrink-0 cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="remember" class="cursor-pointer whitespace-nowrap text-xs font-medium text-slate-600">
                            Mantener sesión
                        </label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition-colors hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto sm:shrink-0 sm:py-2 sm:text-sm"
                    >
                        <Loader2 v-if="form.processing" class="h-4 w-4 shrink-0 animate-spin" />
                        <LogIn v-else class="h-4 w-4 shrink-0" />
                        {{ form.processing ? 'Ingresando…' : 'Ingresar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
/*
 * Fondo responsive: en móvil se usa `cover` para no deformar la ilustración
 * (que es apaisada); en pantallas grandes se estira al 100% para mostrar todo.
 */
.login-bg {
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
}

@media (min-width: 640px) {
    .login-bg {
        background-size: 100% 100%;
    }
}
</style>
