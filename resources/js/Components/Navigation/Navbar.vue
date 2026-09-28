<script setup>
import { ref, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { usePage, router } from '@inertiajs/vue3';
import { useUiStore } from '@/stores/ui';
import { Menu, ChevronDown, LogOut } from 'lucide-vue-next';

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
const user = page.props.auth?.user || { name: 'Usuario', role: 'Docente' };
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
    router.post('/logout');
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
                <button 
                    type="button"
                    class="group/badge flex items-center gap-2.5 rounded-full border bg-white py-1 pl-1 pr-1.5 shadow-xs transition-[border-color,background-color,box-shadow] duration-200 hover:shadow-sm focus:outline-none focus-visible:outline-none"
                    :class="dropdownOpen
                        ? 'border-indigo-300 bg-indigo-50/70 shadow-sm'
                        : 'border-slate-200/80 hover:border-slate-300 hover:bg-slate-50'"
                    @click="dropdownOpen = !dropdownOpen"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-400 text-xs font-extrabold text-white shadow-md ring-1 ring-white/80">
                        {{ user.name.charAt(0).toUpperCase() }}
                    </span>

                    <span class="hidden min-w-0 text-left md:block">
                        <span class="block max-w-[10rem] truncate text-[13px] font-bold leading-none text-slate-800">
                            {{ user.name }}
                        </span>
                        <span class="mt-1 block max-w-[10rem] truncate text-[10px] font-semibold uppercase leading-none tracking-wider text-slate-400">
                            {{ user.role }}
                        </span>
                    </span>

                    <span
                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full transition-colors"
                        :class="dropdownOpen
                            ? 'bg-indigo-100 text-indigo-600'
                            : 'bg-slate-100 text-slate-500 group-hover/badge:bg-slate-200/80'"
                    >
                        <ChevronDown class="h-3.5 w-3.5 transition-transform duration-200" :class="{ 'rotate-180': dropdownOpen }" />
                    </span>
                </button>

                <!-- Backdrop invisible para cerrar al hacer clic fuera -->
                <div 
                    v-if="dropdownOpen" 
                    class="fixed inset-0 z-40" 
                    @click="dropdownOpen = false"
                ></div>

                <!-- Dropdown -->
                <div 
                    v-if="dropdownOpen"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50 animate-in fade-in zoom-in-95 duration-150"
                    @click="dropdownOpen = false"
                >
                    <div class="px-4 py-2 border-b border-slate-100 md:hidden">
                        <p class="text-xs font-bold text-slate-800 mb-0">{{ user.name }}</p>
                        <p class="text-[10px] text-slate-500 mb-0">{{ user.role }}</p>
                    </div>

                    <button 
                        type="button"
                        class="w-full text-left px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 font-semibold flex items-center space-x-2 transition-colors"
                        @click="logout"
                    >
                        <LogOut class="w-3.5 h-3.5 text-rose-500 shrink-0" />
                        <span>Cerrar Sesión</span>
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>
