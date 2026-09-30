import { defineStore } from 'pinia';
import { ref } from 'vue';

// Última pestaña visitada por sección (módulo). Con Inertia el estado sobrevive
// entre visitas, pero no en un F5: por eso se persiste en localStorage, igual
// que el sidebar en stores/ui.js.
const STORAGE_KEY = 'siadpro_active_tabs';

function loadActiveTabs() {
    if (typeof window === 'undefined') return {};
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : null;
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (e) {
        console.warn('No se pudo leer el estado de las pestañas desde localStorage:', e);
        return {};
    }
}

function persistActiveTabs(map) {
    if (typeof window === 'undefined') return;
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(map));
    } catch (e) {
        console.warn('No se pudo guardar el estado de las pestañas en localStorage:', e);
    }
}

export const useTabsStore = defineStore('tabs', () => {
    // { [section]: { id, url } } — id = nombre de ruta del tab (p.ej. plans.view)
    const active = ref(loadActiveTabs());

    function setTab(section, id, url) {
        if (!section || !id || !url) return;
        active.value = { ...active.value, [section]: { id, url } };
        persistActiveTabs(active.value);
    }

    function getTab(section) {
        return active.value[section] || null;
    }

    function clear(section) {
        if (!section || !active.value[section]) return;
        const { [section]: _removed, ...rest } = active.value;
        active.value = rest;
        persistActiveTabs(active.value);
    }

    function clearAll() {
        active.value = {};
        persistActiveTabs(active.value);
    }

    return {
        // Estado
        active,

        // Acciones
        setTab,
        getTab,
        clear,
        clearAll,
    };
});
