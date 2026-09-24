<script setup>
import { computed } from 'vue';
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

const colorStyles = {
    yellow: { icon: 'text-amber-600 drop-shadow-sm', title: 'text-amber-700' },
    blue:   { icon: 'text-blue-600 drop-shadow-sm',   title: 'text-blue-700' },
    red:    { icon: 'text-rose-600 drop-shadow-sm',   title: 'text-rose-700' },
    indigo: { icon: 'text-indigo-600 drop-shadow-sm', title: 'text-indigo-700' },
    green:  { icon: 'text-emerald-600 drop-shadow-sm', title: 'text-emerald-700' },
    cyan:   { icon: 'text-cyan-600 drop-shadow-sm',   title: 'text-cyan-700' },
    orange: { icon: 'text-orange-600 drop-shadow-sm', title: 'text-orange-700' },
    pink:   { icon: 'text-pink-600 drop-shadow-sm',   title: 'text-pink-700' },
    gray:   { icon: 'text-slate-600 drop-shadow-sm',  title: 'text-slate-700' },
};

const activeColor = computed(() => colorStyles[props.color] || colorStyles.blue);
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3">
            <component
                :is="getIconComponent()"
                :size="28"
                :stroke-width="1.8"
                class="shrink-0"
                :class="activeColor.icon"
                aria-hidden="true"
            />

            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight mb-0 leading-tight" :class="activeColor.title">
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
