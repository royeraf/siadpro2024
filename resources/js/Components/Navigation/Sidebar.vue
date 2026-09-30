<script setup>
import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import { useUiStore } from '@/stores/ui';
import SidebarItem from './SidebarItem.vue';
import SidebarSection from './SidebarSection.vue';
import { X } from 'lucide-vue-next';

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
const { sidebarCollapsed: storeCollapsed, sidebarMobileOpen: isMobileOpen, isMobile: storeIsMobile, sidebarTooltip } = storeToRefs(uiStore);
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
                { text: 'Instituciones', href: '/institucions', icon: 'landmark', isSpa: true },
                { text: 'Usuarios', href: '/users', icon: 'users', isSpa: true },
            ]
        },
        {
            header: 'ACTIVIDADES',
            items: [
                { text: 'Acción de Sensibilización', href: '/accion-inicio', icon: 'megaphone', color: 'yellow', isSpa: true },
                { text: 'Acción de Difusión', href: '/difusion-inicio', icon: 'radio', color: 'blue', isSpa: true },
                { text: 'Sectores del Aula', href: '/sector-inicio', icon: 'layout-grid', color: 'white', isSpa: true },
                { text: 'Asistencia Técnica', href: '/evidencia-inicio', icon: 'file-text', color: 'red', isSpa: true },
                { text: 'Biblioteca del Aula', href: '/informe', icon: 'book-open', color: 'orange', isSpa: true },
                { text: 'Espacio de Lectura', href: '/plan-inicio', icon: 'book-heart', color: 'cyan', isSpa: true },
                { text: 'Producción de Textos', href: '/produccion-inicio', icon: 'notebook-pen', color: 'green', isSpa: true },
                { text: 'Agenda de Lectura', href: '/agenda-inicio', icon: 'calendar-check', color: 'pink', isSpa: true },
            ]
        }
    ];
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
</script>

<template>
    <aside 
        class="main-sidebar flex flex-col fixed inset-y-0 left-0 z-50 transition-all duration-300 select-none bg-[#192761]"
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
        <!-- Encabezado del drawer en móviles: cuando está abierto tapa al
             navbar, así que aquí va la marca + botón de cierre -->
        <div class="lg:hidden px-4 h-16 border-b border-white/10 flex items-center justify-between gap-3 shrink-0">
            <div class="flex items-center min-w-0 overflow-hidden">
                <img 
                    src="/vendor/adminlte/dist/img/inicial.png" 
                    alt="Logo SIADPRO" 
                    class="w-9 h-9 object-contain shrink-0 shadow-md"
                />
                <div class="ml-2.5 truncate">
                    <span class="font-extrabold text-white text-base tracking-tight block leading-tight">
                        SIADPRO
                    </span>
                    <span class="text-[10px] text-indigo-300 font-semibold uppercase tracking-wider block leading-tight">
                        DRE Huánuco
                    </span>
                </div>
            </div>

            <button 
                type="button" 
                class="p-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-400/70 shrink-0"
                @click="handleClose"
                title="Cerrar menú"
            >
                <X class="w-5 h-5" />
            </button>
        </div>

        <!-- Menú de navegación con scroll estilizado -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-3 space-y-4 sidebar-scroll">
            <template v-for="section in menuSections" :key="section.header">
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
        </div>

        <!-- Tooltip flotante en modo colapsado: vive como hijo directo del
             <aside> (sin overflow) para que el contenedor con overflow-x-hidden
             del menú no lo recorte. -->
        <Transition
            enter-active-class="transition-opacity duration-150 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-if="effectiveCollapsed && sidebarTooltip"
                class="hidden lg:flex items-center space-x-2 absolute ml-2 -translate-y-1/2 px-3 py-1.5 bg-slate-200 text-slate-900 text-xs font-semibold rounded-xl shadow-2xl whitespace-nowrap pointer-events-none z-[60]"
                :style="{ top: sidebarTooltip.top + 'px', left: sidebarTooltip.left + 'px' }"
            >
                <span>{{ sidebarTooltip.text }}</span>
                <span
                    v-if="sidebarTooltip.badge"
                    class="px-1.5 py-0.2 rounded bg-indigo-500 text-white text-[10px] font-bold"
                >
                    {{ sidebarTooltip.badge }}
                </span>
            </div>
        </Transition>
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
