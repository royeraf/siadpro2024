<script setup>
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

defineProps({
    sidebarCollapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggleSidebar']);

const page = usePage();
const user = page.props.auth?.user || { name: 'Usuario', role: 'Docente' };
const dropdownOpen = ref(false);

function logout() {
    router.post('/logout');
}
</script>

<template>
    <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 shadow-sm">
        <!-- Izquierda: Botón Hamburguesa -->
        <div class="flex items-center space-x-3">
            <button 
                type="button" 
                class="p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors focus:outline-none"
                @click="emit('toggleSidebar')"
                title="Alternar barra lateral"
            >
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- Buscador estilo píldora -->
            <div class="hidden sm:flex items-center bg-slate-100/80 rounded-full px-4 py-1.5 border border-slate-200/60 w-64 lg:w-80">
                <i class="fas fa-search text-slate-400 text-xs mr-2.5"></i>
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
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs border border-indigo-200">
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
                    <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
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
                        class="w-full text-left px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 font-semibold flex items-center transition-colors"
                        @click="logout"
                    >
                        <i class="fas fa-sign-out-alt mr-2 text-xs"></i>
                        Cerrar Sesión
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>
