<script setup>
import { onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { isSpaPath } from '@/Composables/useSpaRoutes';
import { useTabsStore } from '@/stores/tabs';

const props = defineProps({
    tabs: {
        type: Array,
        default: () => [],
    },
    color: {
        type: String,
        default: 'yellow',
    },
    // Identidad de la sección/módulo ("plan", "users", "agenda"...). Clave con la
    // que el store recuerda la última pestaña visitada al volver a la sección.
    section: {
        type: String,
        default: '',
    },
});

const tabsStore = useTabsStore();

const palette = {
    yellow: { active: 'bg-amber-500 text-white shadow-xs font-bold',   inactive: 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium' },
    blue:   { active: 'bg-blue-600 text-white shadow-xs font-bold',    inactive: 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium' },
    red:    { active: 'bg-rose-600 text-white shadow-xs font-bold',    inactive: 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium' },
    green:  { active: 'bg-emerald-600 text-white shadow-xs font-bold', inactive: 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium' },
    cyan:   { active: 'bg-cyan-600 text-white shadow-xs font-bold',    inactive: 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium' },
    orange: { active: 'bg-orange-600 text-white shadow-xs font-bold',  inactive: 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium' },
    pink:   { active: 'bg-pink-600 text-white shadow-xs font-bold',    inactive: 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium' },
    indigo: { active: 'bg-indigo-600 text-white shadow-xs font-bold',  inactive: 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium' },
    gray:   { active: 'bg-slate-800 text-white shadow-xs font-bold',   inactive: 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 font-medium' },
};

function getTabClasses(tab) {
    const scheme = palette[props.color] || palette.yellow;
    return tab.active ? scheme.active : scheme.inactive;
}

// Identidad estable de la pestaña: nombre de ruta del backend (p.ej. plans.view).
// La URL no sirve como id porque arrastra los filtros activos.
function tabId(tab) {
    return tab.route || tab.url;
}

function normalize(url) {
    try {
        const parsed = new URL(url, window.location.origin);
        return `${parsed.pathname}${parsed.search}`;
    } catch {
        return url;
    }
}

function select(tab) {
    if (!props.section) return;
    tabsStore.setTab(props.section, tabId(tab), tab.url);
}

onMounted(() => {
    if (!props.section || !props.tabs.length) return;

    const saved = tabsStore.getTab(props.section);
    if (!saved || !saved.id) return;

    const target = props.tabs.find((tab) => tabId(tab) === saved.id);

    // El tab guardado ya no existe (permiso revocado / cambio de sección).
    if (!target) {
        tabsStore.clear(props.section);
        return;
    }

    // Guardas anti-búcle: el servidor ya activó ese tab o ya estamos en su URL.
    if (target.active || normalize(target.url) === normalize(window.location.href)) return;

    router.visit(target.url, { preserveState: true, preserveScroll: true, replace: true });
});

</script>

<template>
    <div v-if="tabs && tabs.length > 1" class="mb-4 max-w-full">
        <div class="inline-flex max-w-full overflow-x-auto pb-0.5">
            <ul class="flex items-center p-1 list-none rounded-lg bg-slate-100 min-w-max gap-1" data-tabs="tabs" role="list">
                <li 
                    v-for="tab in tabs" 
                    :key="tab.label" 
                    class="text-center"
                    role="presentation"
                >
                    <Link
                        v-if="isSpaPath(tab.url)"
                        :href="tab.url"
                        role="tab"
                        :data-tab-target="tab.route || ''"
                        :aria-selected="tab.active ? 'true' : 'false'"
                        class="flex items-center justify-center px-3.5 py-1.5 text-xs sm:text-sm mb-0 transition-all ease-in-out border-0 rounded-md cursor-pointer text-decoration-none"
                        :class="getTabClasses(tab)"
                        @click="select(tab)"
                    >
                        {{ tab.label }}
                    </Link>
                    <a
                        v-else
                        :href="tab.url"
                        role="tab"
                        :data-tab-target="tab.route || ''"
                        :aria-selected="tab.active ? 'true' : 'false'"
                        class="flex items-center justify-center px-3.5 py-1.5 text-xs sm:text-sm mb-0 transition-all ease-in-out border-0 rounded-md cursor-pointer text-decoration-none"
                        :class="getTabClasses(tab)"
                        @click="select(tab)"
                    >
                        {{ tab.label }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>
