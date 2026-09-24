<script setup>
import { ref, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { usePage, router } from '@inertiajs/vue3';
import { useUiStore } from '@/stores/ui';
import { Menu, Search, ChevronDown, LogOut } from 'lucide-vue-next';

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
        <!-- Izquierda: Botón Hamburguesa y Buscador -->
        <div class="flex items-center space-x-3">
            <button 
                type="button" 
                class="p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 active:bg-slate-200 transition-colors focus:outline-none"
                @click="handleToggle"
                :title="toggleTitle"
            >
                <Menu class="w-5 h-5 text-slate-600" />
            </button>

            <!-- Buscador estilo píldora -->
            <div class="hidden sm:flex items-center bg-slate-100/80 rounded-full px-4 py-1.5 border border-slate-200/60 w-64 lg:w-80">
                <Search class="w-3.5 h-3.5 text-slate-400 mr-2.5 shrink-0" />
                <input 
                    type="text" 
                    placeholder="Buscar módulo o actividad..." 
                    class="bg-transparent border-0 text-xs text-slate-700 focus:outline-none w-full placeholder-slate-400 p-0"
                />
            </div>
        </div>

        <!-- Derecha: Perfil de usuario y acciones -->
        <div class="flex items-center space-x-4">
            <!-- Menú de Usuario -->
            <div class="relative">
                <button 
                    type="button"
                    class="flex items-center space-x-2.5 p-1 rounded-full hover:bg-slate-50 transition-colors focus:outline-none"
                    @click="dropdownOpen = !dropdownOpen"
                >
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs border border-indigo-200 shadow-xs">
                        {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="hidden md:block text-left">
                        <div class="text-xs font-bold text-slate-800 leading-none">
                            {{ user.name }}
                        </div>
                        <div class="text-[10px] text-slate-400 font-medium mt-0.5 leading-none">
                            {{ user.role }}
                        </div>
                    </div>
                    <ChevronDown class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': dropdownOpen }" />
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
