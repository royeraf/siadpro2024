<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    tabs: {
        type: Array,
        default: () => [],
    },
    color: {
        type: String,
        default: 'yellow',
    },
});

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

function isSpaUrl(url) {
    if (!url) return false;
    try {
        const path = url.startsWith('http://') || url.startsWith('https://') 
            ? new URL(url).pathname 
            : url;
        return path.startsWith('/inicio') || 
               path.startsWith('/accions') || 
               path.startsWith('/accion-inicio') ||
               path.startsWith('/accion-general') ||
               path.startsWith('/accion-ugel') ||
               path.startsWith('/accion-director') ||
               path.startsWith('/difusions') ||
               path.startsWith('/difusion-inicio') ||
               path.startsWith('/difusion-general') ||
               path.startsWith('/difusion-ugel') ||
               path.startsWith('/difusion-director') ||
               path.startsWith('/sector') ||
               path.startsWith('/sectores');
    } catch (e) {
        return false;
    }
}
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
                        v-if="isSpaUrl(tab.url)"
                        :href="tab.url"
                        role="tab"
                        :data-tab-target="tab.route || ''"
                        :aria-selected="tab.active ? 'true' : 'false'"
                        class="flex items-center justify-center px-3.5 py-1.5 text-xs sm:text-sm mb-0 transition-all ease-in-out border-0 rounded-md cursor-pointer text-decoration-none"
                        :class="getTabClasses(tab)"
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
                    >
                        {{ tab.label }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>
