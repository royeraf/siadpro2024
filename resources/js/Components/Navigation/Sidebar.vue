<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    House, ChartLine, Landmark, Users, 
    Megaphone, Radio, LayoutGrid, FileText, 
    BookOpen, BookHeart, NotebookPen, CalendarCheck, 
    Folder, X 
} from 'lucide-vue-next';

defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const page = usePage();
const currentUrl = computed(() => page.url);

const iconMap = {
    'house': House,
    'home': House,
    'chart-line': ChartLine,
    'landmark': Landmark,
    'users': Users,
    'megaphone': Megaphone,
    'radio': Radio,
    'layout-grid': LayoutGrid,
    'file-text': FileText,
    'book-open': BookOpen,
    'book-heart': BookHeart,
    'notebook-pen': NotebookPen,
    'calendar-check': CalendarCheck,
};

function resolveIcon(iconName) {
    if (!iconName) return Folder;
    const clean = iconName.toLowerCase().replace(/^fas fa-/, '').replace(/^fa-/, '').trim();
    return iconMap[clean] || Folder;
}

const menuSections = computed(() => {
    const fromProps = page.props.sidebarMenu;
    if (Array.isArray(fromProps) && fromProps.length > 0) {
        return fromProps;
    }
    // Fallback si no viniera desde el backend
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

function isItemActive(item) {
    if (!item) return false;
    if (item.active) return true;
    const href = item.href || item.url || '';
    if (href.endsWith('/inicio') && (currentUrl.value === '/inicio' || currentUrl.value === '/')) {
        return true;
    }
    const cleanPath = href.replace(/^https?:\/\/[^\/]+/, '');
    return cleanPath.length > 1 && currentUrl.value.startsWith(cleanPath);
}
</script>

<template>
    <aside 
        class="main-sidebar flex flex-col fixed inset-y-0 left-0 z-40 transition-all duration-300 select-none"
        :class="[
            collapsed 
                ? '-translate-x-full lg:translate-x-0 lg:w-20' 
                : 'translate-x-0 w-64'
        ]"
        style="background-color: #1A1D36;"
    >
        <!-- Logo / Marca -->
        <div class="brand-link px-4 py-4 border-b border-white/10 flex items-center justify-between shrink-0">
            <div class="flex items-center min-w-0">
                <img 
                    src="/vendor/adminlte/dist/img/inicial.png" 
                    alt="Logo" 
                    class="w-9 h-9 rounded-full object-contain shrink-0 elevation-2"
                />
                <div v-show="!collapsed" class="ml-3 font-extrabold text-white text-lg tracking-tight truncate">
                    SIADPRO
                </div>
            </div>

            <!-- Botón cerrar en móviles -->
            <button 
                type="button" 
                class="lg:hidden p-1 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
                @click="emit('close')"
                title="Cerrar menú"
            >
                <X class="w-5 h-5" />
            </button>
        </div>

        <!-- Menú de navegación con scroll -->
        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5 sidebar-scroll">
            <div v-for="section in menuSections" :key="section.header" class="space-y-1">
                <!-- Encabezado de sección -->
                <div 
                    v-if="section.header"
                    v-show="!collapsed"
                    class="px-3 text-[10px] font-black tracking-widest text-indigo-400 uppercase"
                >
                    {{ section.header }}
                </div>

                <!-- Enlaces -->
                <nav class="space-y-1 pt-1">
                    <template v-for="item in section.items" :key="item.text">
                        <!-- Enlace SPA para rutas Inertia -->
                        <Link
                            v-if="item.isSpa"
                            :href="item.href || item.url"
                            class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-150 group"
                            :class="[
                                isItemActive(item)
                                    ? 'text-white shadow-lg shadow-indigo-600/30'
                                    : 'text-slate-300 hover:text-white hover:bg-white/10'
                            ]"
                            :style="isItemActive(item) ? { background: 'linear-gradient(135deg, #6366F1 0%, #4F46E5 100%)' } : {}"
                            @click="emit('close')"
                        >
                            <component 
                                :is="resolveIcon(item.icon)" 
                                class="w-4 h-4 shrink-0" 
                                :class="isItemActive(item) ? 'text-white' : 'text-slate-400 group-hover:text-white'"
                            />
                            <span v-show="!collapsed" class="ml-3 truncate">
                                {{ item.text }}
                            </span>
                        </Link>

                        <!-- Enlace tradicional para vistas Blade estándar -->
                        <a
                            v-else
                            :href="item.href || item.url"
                            class="flex items-center px-3 py-2 text-xs font-semibold rounded-xl transition-all duration-150 group"
                            :class="[
                                isItemActive(item)
                                    ? 'text-white shadow-lg shadow-indigo-600/30'
                                    : 'text-slate-300 hover:text-white hover:bg-white/10'
                            ]"
                            :style="isItemActive(item) ? { background: 'linear-gradient(135deg, #6366F1 0%, #4F46E5 100%)' } : {}"
                            @click="emit('close')"
                        >
                            <component 
                                :is="resolveIcon(item.icon)" 
                                class="w-4 h-4 shrink-0" 
                                :class="isItemActive(item) ? 'text-white' : 'text-slate-400 group-hover:text-white'"
                            />
                            <span v-show="!collapsed" class="ml-3 truncate">
                                {{ item.text }}
                            </span>
                        </a>
                    </template>
                </nav>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.main-sidebar {
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
}

.sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}
.sidebar-scroll::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 4px;
}
</style>
