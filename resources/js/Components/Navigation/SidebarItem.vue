<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
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

function handleClick(e) {
    emit('click', e);
}
</script>

<template>
    <div class="relative group">
        <!-- Modo SPA (Inertia Link) -->
        <Link
            v-if="item.isSpa"
            :href="itemUrl"
            class="flex items-center rounded-xl font-medium transition-all duration-200 select-none relative"
            :class="[
                collapsed 
                    ? 'justify-center w-11 h-11 mx-auto' 
                    : 'px-3 py-2.5 w-full text-xs space-x-3',
                isActive
                    ? 'text-white shadow-lg shadow-indigo-600/30'
                    : 'text-slate-300 hover:text-white hover:bg-white/10 active:bg-white/15'
            ]"
            :style="isActive ? { background: 'linear-gradient(135deg, #6366F1 0%, #4F46E5 100%)' } : {}"
            @click="handleClick"
        >
            <component 
                :is="resolvedIcon" 
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-110" 
                :class="isActive ? 'text-white' : 'text-slate-400 group-hover:text-white'"
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
            class="flex items-center rounded-xl font-medium transition-all duration-200 select-none relative"
            :class="[
                collapsed 
                    ? 'justify-center w-11 h-11 mx-auto' 
                    : 'px-3 py-2.5 w-full text-xs space-x-3',
                isActive
                    ? 'text-white shadow-lg shadow-indigo-600/30'
                    : 'text-slate-300 hover:text-white hover:bg-white/10 active:bg-white/15'
            ]"
            :style="isActive ? { background: 'linear-gradient(135deg, #6366F1 0%, #4F46E5 100%)' } : {}"
            @click="handleClick"
        >
            <component 
                :is="resolvedIcon" 
                class="w-4 h-4 shrink-0 transition-transform duration-200 group-hover:scale-110" 
                :class="isActive ? 'text-white' : 'text-slate-400 group-hover:text-white'"
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

        <!-- Floating Tooltip cuando el sidebar está colapsado en pantallas de escritorio -->
        <div 
            v-if="collapsed"
            class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-900/95 text-white text-xs font-semibold rounded-xl shadow-2xl whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-150 z-50 border border-white/15 hidden lg:flex items-center space-x-2"
        >
            <span>{{ item.text }}</span>
            <span 
                v-if="item.badge"
                class="px-1.5 py-0.2 rounded bg-indigo-500 text-white text-[10px] font-bold"
            >
                {{ item.badge }}
            </span>
            <!-- Flechita del tooltip -->
            <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-slate-900 rotate-45 border-l border-b border-white/15"></div>
        </div>
    </div>
</template>
