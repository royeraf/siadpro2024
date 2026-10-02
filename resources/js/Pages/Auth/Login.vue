<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useForm as useVeeForm } from 'vee-validate';
import * as yup from 'yup';
import Swal from 'sweetalert2';
import { AlertCircle, Eye, EyeOff, Loader2, Lock, LogIn, Mail } from 'lucide-vue-next';

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
        class="login-bg flex min-h-screen w-full flex-col items-center justify-center bg-white px-5 py-10"
    >
        <div class="w-full max-w-sm login-card">
                <div class="logo-chip flex items-center justify-center gap-3">
                    <img
                        src="/vendor/adminlte/dist/img/inicial.png"
                        alt="SIADPRO"
                        class="h-12 w-12 rounded-full object-contain"
                    />
                    <span class="text-2xl font-extrabold tracking-wide text-[#192761]">SIADPRO</span>
                </div>

                <div class="login-divider" role="separator"></div>

                <div class="login-rise-item rounded-none border-0 bg-transparent p-0 shadow-none sm:rounded-2xl sm:border sm:border-slate-200/80 sm:bg-white sm:p-7 sm:shadow-xl">
                    <h1 class="text-center text-2xl font-extrabold tracking-tight text-slate-900">Inicia sesión</h1>
                    <p class="mt-1.5 text-center text-sm text-slate-500">
                        Ingresa tu correo y contraseña para acceder.
                    </p>

                    <form class="mt-6 space-y-5" novalidate @submit.prevent="submit">
                        <div>
                            <div class="relative h-12 w-full sm:h-10">
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
                                    class="peer h-full w-full rounded-xl border bg-slate-50 pl-12 pr-4 text-base text-slate-900 transition-all focus:border-2 focus:bg-white focus:outline-none disabled:cursor-not-allowed disabled:opacity-60 sm:pl-11 sm:text-sm"
                                    :class="errors.email ? 'border-rose-400 bg-rose-50/40' : 'border-slate-200'"
                                />
                                <Mail
                                    class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 sm:h-4 sm:w-4"
                                />
                                <label
                                    for="email"
                                    class="pointer-events-none absolute -top-4 left-0 flex h-full w-full select-none text-[11px] font-medium leading-tight text-slate-500 transition-all peer-placeholder-shown:-top-1.5 peer-placeholder-shown:pl-12 sm:peer-placeholder-shown:pl-11 peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[3.75rem] sm:peer-placeholder-shown:leading-[3.25rem] peer-placeholder-shown:text-slate-400 peer-focus:!-top-4 peer-focus:!pl-0 peer-focus:!text-[11px] peer-focus:!leading-tight peer-focus:!text-blue-600"
                                >
                                    Correo
                                </label>
                            </div>
                            <p
                                class="mt-1.5 flex min-h-[1.125rem] items-start text-xs font-semibold leading-[1.125rem] text-rose-600 transition-opacity duration-150"
                                :class="errors.email ? 'opacity-100' : 'invisible opacity-0'"
                                role="alert"
                            >
                                <AlertCircle class="mr-1 mt-0.5 h-3.5 w-3.5 shrink-0" />
                                <span>{{ errors.email }}</span>
                            </p>
                        </div>

                        <div>
                            <div class="relative h-12 w-full sm:h-10">
                                <input
                                    id="password"
                                    v-model="password"
                                    v-bind="passwordAttrs"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder=" "
                                    required
                                    autocomplete="current-password"
                                    :disabled="form.processing"
                                    class="peer h-full w-full rounded-xl border bg-slate-50 pl-12 pr-12 text-base text-slate-900 transition-all focus:border-2 focus:bg-white focus:outline-none disabled:cursor-not-allowed disabled:opacity-60 sm:pl-11 sm:pr-11 sm:text-sm"
                                    :class="errors.password ? 'border-rose-400 bg-rose-50/40' : 'border-slate-200'"
                                />
                                <Lock
                                    class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400 sm:h-4 sm:w-4"
                                />
                                <button
                                    type="button"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-2 text-slate-400 transition hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:right-3 sm:p-1"
                                    :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                    @click="showPassword = !showPassword"
                                >
                                    <EyeOff v-if="showPassword" class="h-5 w-5 sm:h-4 sm:w-4" />
                                    <Eye v-else class="h-5 w-5 sm:h-4 sm:w-4" />
                                </button>
                                <label
                                    for="password"
                                    class="pointer-events-none absolute -top-4 left-0 flex h-full w-full select-none text-[11px] font-medium leading-tight text-slate-500 transition-all peer-placeholder-shown:-top-1.5 peer-placeholder-shown:pl-12 sm:peer-placeholder-shown:pl-11 peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[3.75rem] sm:peer-placeholder-shown:leading-[3.25rem] peer-placeholder-shown:text-slate-400 peer-focus:!-top-4 peer-focus:!pl-0 peer-focus:!text-[11px] peer-focus:!leading-tight peer-focus:!text-blue-600"
                                >
                                    Contraseña
                                </label>
                            </div>
                            <p
                                class="mt-1.5 flex min-h-[1.125rem] items-start text-xs font-semibold leading-[1.125rem] text-rose-600 transition-opacity duration-150"
                                :class="errors.password ? 'opacity-100' : 'invisible opacity-0'"
                                role="alert"
                            >
                                <AlertCircle class="mr-1 mt-0.5 h-3.5 w-3.5 shrink-0" />
                                <span>{{ errors.password }}</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-2.5">
                            <input
                                id="remember"
                                v-model="remember"
                                v-bind="rememberAttrs"
                                type="checkbox"
                                class="h-4 w-4 cursor-pointer rounded border-slate-300 text-[#724ffb] focus:ring-blue-500"
                            />
                            <label for="remember" class="cursor-pointer text-sm text-slate-600">
                                Mantener sesión iniciada
                            </label>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#724ffb] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:bg-[#6141e6] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <LogIn v-else class="h-4 w-4" />
                            {{ form.processing ? 'Iniciando…' : 'Iniciar sesión' }}
                        </button>
                    </form>
                </div>
            </div>
    </div>
</template>

<style scoped>
@keyframes login-rise {    from {
        opacity: 0;
        transform: translateY(14px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/*
 * La entrada animada vive en el panel, nunca en un ancestro de .logo-chip:
 * un ancestro transformado/animado se vuelve backdrop root y mata en silencio
 * el blur del backdrop-filter del chip.
 */
.login-rise-item {
    animation: login-rise 0.5s cubic-bezier(0.22, 0.61, 0.36, 1) both;
}

.logo-chip {
    width: fit-content;
    margin-left: auto;
    margin-right: auto;
    padding: 0.5rem 1.25rem;
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.55);
    border: 1px solid rgba(255, 255, 255, 0.65);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
    -webkit-backdrop-filter: blur(14px) saturate(150%);
    backdrop-filter: blur(14px) saturate(150%);
}

.login-divider {
    height: 1px;
    width: 4.5rem;
    margin: 1.25rem auto;
    background: linear-gradient(90deg, transparent, rgba(100, 116, 139, 0.5), transparent);
}

@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
    .logo-chip {
        background: rgba(255, 255, 255, 0.85);
    }
}

/*
 * Fondo: en movil se queda blanco y limpio; la imagen entra solo en desktop.
 */
@media (min-width: 640px) {
    .login-bg {
        background-image: url('/vendor/adminlte/dist/img/infantil.jpg');
        background-size: 100% 100%;
        background-repeat: no-repeat;
        background-position: center;
    }
}

@media (prefers-reduced-motion: reduce) {
    .login-rise-item {
        animation: none;
    }
}
</style>
