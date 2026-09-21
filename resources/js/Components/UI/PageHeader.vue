<script setup>
import { 
    Megaphone, Radio, LayoutGrid, FileText, 
    BookOpen, BookHeart, NotebookPen, CalendarCheck, 
    Folder, Landmark, Users, House 
} from 'lucide-vue-next';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    icon: {
        type: [String, Object],
        default: 'Folder',
    },
    color: {
        type: String,
        default: 'yellow',
    },
    subtitle: {
        type: String,
        default: '',
    },
});

const icons = {
    Megaphone,
    Radio,
    LayoutGrid,
    FileText,
    BookOpen,
    BookHeart,
    NotebookPen,
    CalendarCheck,
    Folder,
    Landmark,
    Users,
    House,
};

function getIconComponent() {
    if (typeof props.icon === 'object') return props.icon;
    const name = props.icon.charAt(0).toUpperCase() + props.icon.slice(1);
    return icons[name] || Folder;
}

const colorBadgeStyles = {
    yellow: 'bg-amber-100 text-amber-700 border-amber-300',
    blue:   'bg-blue-100 text-blue-700 border-blue-300',
    red:    'bg-rose-100 text-rose-700 border-rose-300',
    indigo: 'bg-indigo-100 text-indigo-700 border-indigo-300',
    green:  'bg-emerald-100 text-emerald-700 border-emerald-300',
    cyan:   'bg-cyan-100 text-cyan-700 border-cyan-300',
    orange: 'bg-orange-100 text-orange-700 border-orange-300',
    pink:   'bg-pink-100 text-pink-700 border-pink-300',
    gray:   'bg-slate-100 text-slate-700 border-slate-300',
};
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <!-- Insignia de icono -->
            <div 
                class="w-11 h-11 rounded-2xl flex items-center justify-center border shadow-xs shrink-0"
                :class="colorBadgeStyles[color] || colorBadgeStyles.blue"
            >
                <component :is="getIconComponent()" class="w-5 h-5" />
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight mb-0 leading-tight">
                    {{ title }}
                </h2>
                <p v-if="subtitle" class="text-xs text-slate-500 font-medium mb-0 mt-0.5">
                    {{ subtitle }}
                </p>
            </div>
        </div>

        <!-- Acciones en la cabecera (ej: botón Nuevo) -->
        <div v-if="$slots.actions || $slots.default" class="flex items-center gap-2 shrink-0">
            <slot name="actions" />
            <slot />
        </div>
    </div>
</template>
