<script setup>
import { ref, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import { useUiStore } from '@/stores/ui';
import { AuthService } from '@/Services/auth';
import { 
    Menu, ChevronDown, LogOut, 
    IdCard, Mail, Briefcase, School, MapPin, Shield 
} from 'lucide-vue-next';

const props = defineProps({
    sidebarCollapsed: {
        type: Boolean,
        default: undefined,
    },
});

const emit = defineEmits(['toggleSidebar']);

const uiStore = useUiStore();
const { sidebarCollapsed: storeCollapsed, isMobile: storeIsMobile } = storeToRefs(uiStore);
const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Usuario', role: 'Docente' });
const dropdownOpen = ref(false);

// Título correcto según contexto: en móvil abre/cierra drawer, en desktop colapsa/expande.
const toggleTitle = computed(() => {
    if (storeIsMobile.value) return 'Abrir / cerrar menú lateral';
    const collapsed = props.sidebarCollapsed ?? storeCollapsed.value;
    return collapsed ? 'Expandir menú lateral' : 'Colapsar menú lateral';
});

function handleToggle() {
    uiStore.toggleSidebar();
    emit('toggleSidebar');
}

function logout() {
    AuthService.logout();
}
</script>

<template>
    <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 shadow-xs">
        <!-- Izquierda: Botón Hamburguesa + Logo SIADPRO -->
        <div class="flex items-center min-w-0 space-x-3">
            <button 
                type="button" 
                class="p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 active:bg-slate-200 transition-colors focus:outline-none"
                @click="handleToggle"
                :title="toggleTitle"
            >
                <Menu class="w-5 h-5 text-slate-600" />
            </button>

            <div class="flex items-center min-w-0 overflow-hidden">
                <img 
                    src="/vendor/adminlte/dist/img/inicial.png" 
                    alt="Logo SIADPRO" 
                    class="w-9 h-9 object-contain shrink-0 shadow-sm"
                />
                <div class="ml-2.5 truncate hidden sm:block">
                    <span class="font-extrabold text-slate-900 text-base tracking-tight block leading-tight">
                        SIADPRO
                    </span>
                    <span class="text-[10px] text-indigo-600 font-semibold uppercase tracking-wider block leading-tight">
                        DRE Huánuco
                    </span>
                </div>
            </div>
        </div>

        <!-- Derecha: Perfil de usuario y acciones -->
        <div class="flex items-center space-x-4">
            <!-- Menú de Usuario -->
            <div class="relative">
                <!-- Botón / Badge del Header -->
                <button 
                    type="button"
                    class="group/badge flex items-center gap-2.5 rounded-full border bg-white py-1 pl-1 pr-2.5 shadow-xs transition-[border-color,background-color,box-shadow] duration-200 hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400"
                    :class="dropdownOpen
                        ? 'border-indigo-300 bg-indigo-50/70 shadow-sm ring-2 ring-indigo-100'
                        : 'border-slate-200/90 hover:border-slate-300 hover:bg-slate-50/80'"
                    @click="dropdownOpen = !dropdownOpen"
                    :title="`Perfil de ${user.name}`"
                >
                    <!-- Avatar con degradado e inicial -->
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-400 text-xs font-extrabold text-white shadow-md ring-2 ring-white">
                        {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                    </span>

                    <!-- Información básica visible en pantallas medianas y grandes -->
                    <span class="hidden min-w-0 text-left md:block">
                        <span class="block max-w-[10rem] truncate text-[13px] font-bold leading-none text-slate-800">
                            {{ user.name }}
                        </span>
                        <span class="mt-1 block max-w-[10rem] truncate text-[10px] font-semibold uppercase leading-none tracking-wider text-slate-400">
                            {{ user.role }}
                        </span>
                    </span>

                    <span
                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full transition-transform duration-200 text-slate-400 group-hover/badge:text-slate-600"
                        :class="dropdownOpen ? 'rotate-180 text-indigo-600 bg-indigo-100/60' : ''"
                    >
                        <ChevronDown class="h-3.5 w-3.5" />
                    </span>
                </button>

                <!-- Backdrop invisible para cerrar al hacer clic fuera -->
                <div 
                    v-if="dropdownOpen" 
                    class="fixed inset-0 z-40" 
                    @click="dropdownOpen = false"
                ></div>

                <!-- Dropdown con Tarjeta Detallada de Perfil -->
                <div 
                    v-if="dropdownOpen"
                    class="absolute right-0 mt-2.5 w-80 sm:w-88 bg-white rounded-2xl shadow-2xl border border-slate-100 py-3.5 px-4 z-50 animate-in fade-in zoom-in-95 duration-150 divide-y divide-slate-100"
                    @click.stop
                >
                    <!-- Cabecera de la tarjeta con Avatar grande y Rol -->
                    <div class="pb-3.5 flex items-start gap-3">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-400 text-base font-black text-white shadow-md ring-4 ring-indigo-50">
                            {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <h6 class="text-xs sm:text-sm font-extrabold text-slate-900 leading-snug break-words mb-1">
                                {{ user.name }}
                            </h6>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/70">
                                    <Shield class="w-3 h-3 text-indigo-600" />
                                    {{ user.role || 'Usuario' }}
                                </span>
                                <span v-if="user.cargo && user.cargo !== 'No' && user.cargo !== user.role" class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ user.cargo }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de detalles del perfil -->
                    <div class="py-3 space-y-2.5 text-xs text-slate-600">
                        <!-- DNI -->
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0 text-indigo-600">
                                <IdCard class="w-3.5 h-3.5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block leading-tight">DNI</span>
                                <span class="font-bold text-slate-800 text-xs">{{ user.dni || 'No registrado' }}</span>
                            </div>
                        </div>

                        <!-- Correo Electrónico -->
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center shrink-0 text-blue-600">
                                <Mail class="w-3.5 h-3.5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block leading-tight">Correo Electrónico</span>
                                <span class="font-semibold text-slate-800 text-xs truncate block" :title="user.email">{{ user.email || 'No registrado' }}</span>
                            </div>
                        </div>

                        <!-- Institución Educativa -->
                        <div v-if="user.institucion" class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center shrink-0 text-amber-600">
                                <School class="w-3.5 h-3.5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block leading-tight">Institución Educativa</span>
                                <span class="font-semibold text-slate-800 text-xs truncate block" :title="user.institucion">
                                    {{ user.institucion }}
                                    <span v-if="user.nivelinstitucion" class="text-[10px] text-slate-500 font-normal">({{ user.nivelinstitucion }})</span>
                                </span>
                            </div>
                        </div>

                        <!-- UGEL / Ubicación -->
                        <div v-if="user.ugel || user.provincia" class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0 text-emerald-600">
                                <MapPin class="w-3.5 h-3.5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block leading-tight">UGEL / Jurisdicción</span>
                                <span class="font-semibold text-slate-800 text-xs">
                                    {{ user.ugel ? `UGEL ${user.ugel}` : user.provincia }}
                                    <span v-if="user.distrito" class="text-[10px] text-slate-500 font-normal"> · {{ user.distrito }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pie: Botón Cerrar Sesión -->
                    <div class="pt-2.5">
                        <button 
                            type="button"
                            class="w-full py-2 px-3 text-xs text-rose-600 hover:text-rose-700 hover:bg-rose-50 active:bg-rose-100 rounded-xl font-bold flex items-center justify-center space-x-2 transition-colors border border-transparent hover:border-rose-100 cursor-pointer"
                            @click="logout"
                        >
                            <LogOut class="w-3.5 h-3.5 text-rose-500 shrink-0" />
                            <span>Cerrar Sesión</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
