import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

const STORAGE_KEY_SIDEBAR = 'siadpro_sidebar_collapsed';

function getStoredSidebarCollapsed() {
    if (typeof window === 'undefined') return false;
    try {
        const stored = localStorage.getItem(STORAGE_KEY_SIDEBAR);
        return stored !== null ? JSON.parse(stored) === true : false;
    } catch (e) {
        console.warn('No se pudo leer el estado del sidebar desde localStorage:', e);
        return false;
    }
}

function persistSidebarCollapsed(value) {
    if (typeof window === 'undefined') return;
    try {
        localStorage.setItem(STORAGE_KEY_SIDEBAR, JSON.stringify(Boolean(value)));
    } catch (e) {
        console.warn('No se pudo guardar el estado del sidebar en localStorage:', e);
    }
}

export const useUiStore = defineStore('ui', () => {
    // Estado reactivo
    const sidebarCollapsed = ref(getStoredSidebarCollapsed());
    const sidebarMobileOpen = ref(false);
    const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1200);

    // Computados
    const isMobile = computed(() => windowWidth.value < 1024);

    // Acciones para controlar el colapso en escritorio
    function toggleCollapse() {
        sidebarCollapsed.value = !sidebarCollapsed.value;
        persistSidebarCollapsed(sidebarCollapsed.value);
    }

    function setSidebarCollapsed(value) {
        sidebarCollapsed.value = Boolean(value);
        persistSidebarCollapsed(sidebarCollapsed.value);
    }

    function openSidebar() {
        setSidebarCollapsed(false);
    }

    function closeSidebar() {
        setSidebarCollapsed(true);
    }

    // Acciones para controlar la apertura en dispositivos móviles
    function toggleMobileSidebar() {
        sidebarMobileOpen.value = !sidebarMobileOpen.value;
    }

    function openMobileSidebar() {
        sidebarMobileOpen.value = true;
    }

    function closeMobileSidebar() {
        sidebarMobileOpen.value = false;
    }

    // Acción unificada inteligente (usada por el botón de hamburguesa)
    function toggleSidebar() {
        if (isMobile.value) {
            toggleMobileSidebar();
        } else {
            toggleCollapse();
        }
    }

    // Inicializador para eventos globales (resize de pantalla)
    let listenerAttached = false;
    function initWindowListeners() {
        if (typeof window === 'undefined' || listenerAttached) return;
        
        const handleResize = () => {
            windowWidth.value = window.innerWidth;
            if (window.innerWidth >= 1024 && sidebarMobileOpen.value) {
                // Al volver a escritorio, cerramos el drawer móvil
                sidebarMobileOpen.value = false;
            }
        };

        window.addEventListener('resize', handleResize, { passive: true });
        listenerAttached = true;
    }

    return {
        // Estado
        sidebarCollapsed,
        sidebarMobileOpen,
        windowWidth,
        isMobile,

        // Acciones
        toggleSidebar,
        toggleCollapse,
        setSidebarCollapsed,
        openSidebar,
        closeSidebar,
        toggleMobileSidebar,
        openMobileSidebar,
        closeMobileSidebar,
        initWindowListeners,
    };
});
