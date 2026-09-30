<script setup>
import { computed, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useUiStore } from '@/stores/ui';
import { 
    House, ChartLine, Landmark, Users, User,
    Megaphone, Radio, LayoutGrid, FileText, 
    BookOpen, BookHeart, NotebookPen, CalendarCheck, 
    Folder, School, Shield, Settings, HelpCircle
} from 'lucide-vue-next';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
    isActive: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['click']);

const ui = useUiStore();

// Tooltip en modo colapsado: se posiciona respecto al <aside> (el host real),
// de modo que el tooltip viva como hermano del contenedor con overflow-x-hidden
// y no quede recortado.
function showTooltip(e) {
    if (!props.collapsed) return;
    const host = e.currentTarget?.closest?.('.main-sidebar');
    if (!host) return;
    const rect = e.currentTarget.getBoundingClientRect();
    const hostRect = host.getBoundingClientRect();
    ui.showSidebarTooltip({
        text: props.item.text,
        badge: props.item.badge,
        top: rect.top - hostRect.top + rect.height / 2,
        left: rect.right - hostRect.left,
    });
}

function hideTooltip() {
    ui.hideSidebarTooltip();
}

onUnmounted(hideTooltip);

const iconMap = {
    'house': House,
    'home': House,
    'chart-line': ChartLine,
    'landmark': Landmark,
    'users': Users,
    'user': User,
    'megaphone': Megaphone,
    'radio': Radio,
    'layout-grid': LayoutGrid,
    'file-text': FileText,
    'book-open': BookOpen,
    'book-heart': BookHeart,
    'notebook-pen': NotebookPen,
    'calendar-check': CalendarCheck,
    'school': School,
    'shield': Shield,
    'settings': Settings,
    'folder': Folder,
};

const resolvedIcon = computed(() => {
    const raw = props.item.icon;
    if (!raw) return Folder;
    const clean = raw.toLowerCase().replace(/^fas fa-/, '').replace(/^fa-/, '').trim();
    return iconMap[clean] || Folder;
});

const itemUrl = computed(() => props.item.href || props.item.url || '#');

// Color de sección asignado al ítem (tonos 400 para fondo oscuro #192761).
// Sin color asignado (Panel/Gestión) → gris actual.
const iconPalette = {
    yellow: 'text-amber-400',
    blue: 'text-blue-400',
    red: 'text-rose-400',
    green: 'text-emerald-400',
    cyan: 'text-cyan-400',
    orange: 'text-orange-400',
    pink: 'text-pink-400',
    indigo: 'text-indigo-400',
    white: 'text-white',
    gray: 'text-slate-400',
};

const iconColorClass = computed(() => iconPalette[props.item.color] || 'text-slate-400');

const activeBackground = { background: '#724ffb' };
const activeShadow = 'shadow-[0_4px_14px_rgba(114,79,251,0.45)]';

function handleClick(e) {
    ui.hideSidebarTooltip();
    emit('click', e);
}
</script>

<template>
    <div
        class="relative group"
        @mouseenter="showTooltip"
        @mouseleave="hideTooltip"
        @focusin="showTooltip"
        @focusout="hideTooltip"
    >
        <!-- Modo SPA (Inertia Link) -->
        <Link
            v-if="item.isSpa"
            :href="itemUrl"
            class="flex items-center rounded-xl font-medium transition-[color,background-color,box-shadow] duration-200 select-none relative focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-400/70"
            :class="[
                collapsed 
                    ? 'justify-center w-11 h-11 mx-auto' 
                    : 'px-3 py-2.5 w-full text-xs space-x-3',
                isActive
                    ? `text-white ${activeShadow}`
                    : 'text-slate-300 hover:text-white hover:bg-white/10 active:bg-white/15'
            ]"
            :style="isActive ? activeBackground : {}"
            @click="handleClick"
        >
            <component 
                :is="resolvedIcon" 
                class="w-4 h-4 shrink-0 transition-[filter] duration-200"
                :class="isActive ? 'text-white' : [iconColorClass, 'group-hover:brightness-125']"
            />
            
            <span v-if="!collapsed" class="truncate font-semibold tracking-tight">
                {{ item.text }}
            </span>

            <span 
                v-if="!collapsed && item.badge"
                class="ml-auto px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-indigo-500/80 text-white shrink-0"
            >
                {{ item.badge }}
            </span>
        </Link>

        <!-- Modo Tradicional (Enlace Blade <a>) -->
        <a
            v-else
            :href="itemUrl"
            class="flex items-center rounded-xl font-medium transition-[color,background-color,box-shadow] duration-200 select-none relative focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-400/70"
            :class="[
                collapsed 
                    ? 'justify-center w-11 h-11 mx-auto' 
                    : 'px-3 py-2.5 w-full text-xs space-x-3',
                isActive
                    ? `text-white ${activeShadow}`
                    : 'text-slate-300 hover:text-white hover:bg-white/10 active:bg-white/15'
            ]"
            :style="isActive ? activeBackground : {}"
            @click="handleClick"
        >
            <component 
                :is="resolvedIcon" 
                class="w-4 h-4 shrink-0 transition-[filter] duration-200"
                :class="isActive ? 'text-white' : [iconColorClass, 'group-hover:brightness-125']"
            />
            
            <span v-if="!collapsed" class="truncate font-semibold tracking-tight">
                {{ item.text }}
            </span>

            <span 
                v-if="!collapsed && item.badge"
                class="ml-auto px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-indigo-500/80 text-white shrink-0"
            >
                {{ item.badge }}
            </span>
        </a>
    </div>
</template>
