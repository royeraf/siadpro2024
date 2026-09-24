<script setup>
import { ref, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { usePage, router } from '@inertiajs/vue3';
import { useUiStore } from '@/stores/ui';
import SidebarItem from './SidebarItem.vue';
import SidebarSection from './SidebarSection.vue';
import { 
    X, ChevronsLeft, ChevronsRight, Search, LogOut, User as UserIcon 
} from 'lucide-vue-next';

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: undefined,
    },
});

const emit = defineEmits(['close']);

const uiStore = useUiStore();
// Refs reactivas del store (canónico Pinia: evita perder reactividad al
// desestructurar y evita envolver en computed redundantes).
const { sidebarCollapsed: storeCollapsed, sidebarMobileOpen: isMobileOpen, isMobile: storeIsMobile } = storeToRefs(uiStore);
const page = usePage();

// Determinamos el estado colapsado (usando prop si fue enviada, o la tienda Pinia)
const isCollapsed = computed(() => {
    return props.collapsed !== undefined ? props.collapsed : storeCollapsed.value;
});

// En móvil el drawer siempre debe mostrarse expandido: el colapso es un
// concepto solo de escritorio (persistido en localStorage). Si usáramos
// `isCollapsed` directamente, abrir el drawer en móvil con el sidebar
// colapsado dejaría el drawer en w-64 pero con todos los títulos ocultos
// por los `v-show="!isCollapsed"`.
const effectiveCollapsed = computed(() => {
    if (storeIsMobile.value || isMobileOpen.value) return false;
    return isCollapsed.value;
});
const currentUrl = computed(() => page.url);
const authUser = computed(() => page.props.auth?.user || { name: 'Usuario', role: 'Docente' });

// Filtro rápido de búsqueda de módulos
const searchQuery = ref('');

const menuSections = computed(() => {
    const fromProps = page.props.sidebarMenu;
    if (Array.isArray(fromProps) && fromProps.length > 0) {
        return fromProps;
    }
    // Fallback por defecto si no viniera desde el backend
    return [
        {
            header: 'PANEL',
            items: [
                { text: 'Inicio', href: '/inicio', icon: 'house', isSpa: true },
                { text: 'Dashboard', href: '/dashboard-index', icon: 'chart-line', isSpa: false },
            ]
        },
        {
            header: 'GESTIÓN',
            items: [
                { text: 'Instituciones', href: '/institucions', icon: 'landmark', isSpa: false },
                { text: 'Usuarios', href: '/users', icon: 'users', isSpa: false },
            ]
        },
        {
            header: 'ACTIVIDADES',
            items: [
                { text: 'Acción de Sensibilización', href: '/accion-inicio', icon: 'megaphone', isSpa: false },
                { text: 'Acción de Difusión', href: '/difusion-inicio', icon: 'radio', isSpa: false },
                { text: 'Sectores del Aula', href: '/sector-inicio', icon: 'layout-grid', isSpa: false },
                { text: 'Asistencia Técnica', href: '/evidencia-inicio', icon: 'file-text', isSpa: false },
                { text: 'Biblioteca del Aula', href: '/informe', icon: 'book-open', isSpa: false },
                { text: 'Espacio de Lectura', href: '/plan-inicio', icon: 'book-heart', isSpa: false },
                { text: 'Producción de Textos', href: '/produccion-inicio', icon: 'notebook-pen', isSpa: false },
                { text: 'Agenda de Lectura', href: '/agenda-inicio', icon: 'calendar-check', isSpa: false },
            ]
        }
    ];
});

// Secciones filtradas si el usuario busca algo
const filteredMenuSections = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return menuSections.value;

    return menuSections.value
        .map(section => ({
            ...section,
            items: section.items.filter(item => 
                (item.text && item.text.toLowerCase().includes(q))
            )
        }))
        .filter(section => section.items.length > 0);
});

function isItemActive(item) {
    if (!item) return false;
    if (item.active) return true;
    const href = item.href || item.url || '';
    const currentPath = currentUrl.value.split('?')[0];

    if (href.endsWith('/inicio') && (currentPath === '/inicio' || currentPath === '/')) {
        return true;
    }
    const cleanPath = href.replace(/^https?:\/\/[^\/]+/, '').split('?')[0];
    if (cleanPath.length > 1) {
        if (currentPath === cleanPath) return true;
        if (currentPath.startsWith(cleanPath + '/')) return true;
    }
    return false;
}

function handleClose() {
    emit('close');
    uiStore.closeMobileSidebar();
}

function handleItemClick() {
    // En móviles cerramos el drawer al seleccionar cualquier ítem
    if (uiStore.isMobile) {
        handleClose();
    }
}

function handleToggleCollapse() {
    uiStore.toggleCollapse();
}

function logout() {
    router.post('/logout');
}
</script>

<template>
    <aside 
        class="main-sidebar flex flex-col fixed inset-y-0 left-0 z-50 transition-all duration-300 select-none bg-[#1A1D36]"
        :class="[
            isMobileOpen 
                ? 'translate-x-0 w-64' 
                : '-translate-x-full',
            'lg:translate-x-0',
            isCollapsed 
                ? 'lg:w-20' 
                : 'lg:w-64'
        ]"
    >
        <!-- Logo / Marca del Sistema -->
        <div class="brand-link px-4 py-3.5 border-b border-white/10 flex items-center justify-between shrink-0 h-16">
            <div class="flex items-center min-w-0 overflow-hidden">
                <img 
                    src="/vendor/adminlte/dist/img/inicial.png" 
                    alt="Logo" 
                    class="w-9 h-9 rounded-full object-contain shrink-0 ring-2 ring-white/10 shadow-md"
                />
                <div v-show="!effectiveCollapsed" class="ml-3 truncate">
                    <span class="font-extrabold text-white text-base tracking-tight block leading-tight">
                        SIADPRO
                    </span>
                    <span class="text-[10px] text-indigo-300 font-semibold uppercase tracking-wider block">
                        DRE Huánuco
                    </span>
                </div>
            </div>

            <!-- Botón cerrar en dispositivos móviles -->
            <button 
                type="button" 
                class="lg:hidden p-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
                @click="handleClose"
                title="Cerrar menú"
            >
                <X class="w-5 h-5" />
            </button>
        </div>

        <!-- Buscador rápido dentro del Sidebar (solo visible cuando está expandido) -->
        <div v-show="!effectiveCollapsed" class="px-3 pt-3 pb-1 shrink-0">
            <div class="relative">
                <Search class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                <input 
                    v-model="searchQuery"
                    type="text" 
                    placeholder="Filtrar menú..." 
                    class="w-full pl-8 pr-3 py-1.5 bg-white/5 border border-white/10 rounded-xl text-xs text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-400/50 transition"
                />
                <button 
                    v-if="searchQuery" 
                    type="button" 
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white"
                    @click="searchQuery = ''"
                >
                    <X class="w-3 h-3" />
                </button>
            </div>
        </div>

        <!-- Menú de navegación con scroll estilizado -->
        <div class="flex-1 overflow-y-auto px-3 py-3 space-y-4 sidebar-scroll">
            <template v-for="section in filteredMenuSections" :key="section.header">
                <SidebarSection 
                    :header="section.header" 
                    :collapsed="effectiveCollapsed"
                >
                    <SidebarItem
                        v-for="item in section.items"
                        :key="item.text"
                        :item="item"
                        :collapsed="effectiveCollapsed"
                        :is-active="isItemActive(item)"
                        @click="handleItemClick"
                    />
                </SidebarSection>
            </template>

            <!-- Mensaje si la búsqueda no encuentra coincidencias -->
            <div 
                v-if="filteredMenuSections.length === 0" 
                class="text-center py-6 px-3 text-slate-400 text-xs"
            >
                No se encontraron módulos
            </div>
        </div>

        <!-- Footer del Sidebar: Perfil de usuario y botón de colapso -->
        <div class="p-3 border-t border-white/10 shrink-0 bg-[#16182E]/80 backdrop-blur-xs space-y-2">
            <!-- Información resumida de usuario -->
            <div 
                class="flex items-center rounded-xl p-1.5 transition-colors"
                :class="effectiveCollapsed ? 'justify-center' : 'justify-between hover:bg-white/5'"
            >
                <div class="flex items-center min-w-0">
                    <div 
                        class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 text-white font-bold flex items-center justify-center text-xs shadow-md shrink-0 ring-1 ring-white/20"
                        :title="effectiveCollapsed ? `${authUser.name} (${authUser.role})` : undefined"
                    >
                        {{ authUser.name.charAt(0).toUpperCase() }}
                    </div>
                    <div v-show="!effectiveCollapsed" class="ml-2.5 truncate">
                        <p class="text-xs font-bold text-white truncate mb-0 leading-tight">
                            {{ authUser.name }}
                        </p>
                        <p class="text-[10px] text-slate-400 truncate mb-0 leading-tight">
                            {{ authUser.role }}
                        </p>
                    </div>
                </div>

                <button
                    v-show="!effectiveCollapsed"
                    type="button"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors"
                    title="Cerrar sesión"
                    @click="logout"
                >
                    <LogOut class="w-4 h-4" />
                </button>
            </div>

            <!-- Botón para Colapsar / Expandir en pantallas de escritorio -->
            <button
                type="button"
                class="hidden lg:flex items-center rounded-xl py-2 transition-all duration-200 text-slate-400 hover:text-white hover:bg-white/10 w-full"
                :class="effectiveCollapsed ? 'justify-center px-0' : 'justify-between px-3 text-xs'"
                :title="effectiveCollapsed ? 'Expandir barra lateral' : 'Colapsar barra lateral'"
                @click="handleToggleCollapse"
            >
                <span v-show="!effectiveCollapsed" class="font-medium text-[11px] text-slate-300">
                    Colapsar menú
                </span>
                <component 
                    :is="effectiveCollapsed ? ChevronsRight : ChevronsLeft" 
                    class="w-4 h-4 shrink-0 transition-transform duration-200"
                />
            </button>
        </div>
    </aside>
</template>

<style scoped>
.main-sidebar {
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.25);
}

.sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}
.sidebar-scroll::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 4px;
}
.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.25);
}
</style>
