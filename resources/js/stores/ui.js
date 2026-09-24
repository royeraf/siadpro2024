import { defineStore } from 'pinia';
import { ref, computed, onScopeDispose } from 'vue';

const STORAGE_KEY_SIDEBAR = 'siadpro_sidebar_collapsed';

function getStoredSidebarCollapsed() {
    if (typeof window === 'undefined') return false;
    try {
        const stored = localStorage.getItem(STORAGE_KEY_SIDEBAR);
        return stored === 'true';
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

    // ── Listeners globales (auto-inicializados, no dependen de AppLayout) ──
    function handleResize() {
        windowWidth.value = window.innerWidth;
        if (window.innerWidth >= 1024 && sidebarMobileOpen.value) {
            // Al volver a escritorio, cerramos el drawer móvil
            sidebarMobileOpen.value = false;
        }
    }

    let listenerAttached = false;
    function initWindowListeners() {
        if (typeof window === 'undefined' || listenerAttached) {
            // Sincroniza el ancho aunque ya esté adjunto (cubre HMR/SSR)
            if (typeof window !== 'undefined') {
                windowWidth.value = window.innerWidth;
            }
            return;
        }

        windowWidth.value = window.innerWidth;
        window.addEventListener('resize', handleResize, { passive: true });
        listenerAttached = true;
    }

    function disposeWindowListeners() {
        if (typeof window === 'undefined' || !listenerAttached) return;
        window.removeEventListener('resize', handleResize);
        listenerAttached = false;
    }

    // Auto-init al crear el store: funciona en cualquier página que use el
    // store, sin esperar al onMounted de AppLayout. Se mantiene
    // initWindowListeners() como API pública idempotente por compatibilidad.
    initWindowListeners();

    // Limpieza en HMR / unmount de la app: evita listeners duplicados o
    // colgados referenciando refs viejas.
    try {
        onScopeDispose(() => {
            disposeWindowListeners();
        });
    } catch {
        // Fuera de un scope reactivo (tests unitarios sin Pinia testing): no-op.
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
        disposeWindowListeners,
    };
});
