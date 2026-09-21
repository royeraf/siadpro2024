<script setup>
import { Link } from '@inertiajs/vue3';
import { 
    Megaphone, Radio, LayoutGrid, FileText, 
    BookOpen, BookHeart, NotebookPen, CalendarCheck, Folder 
} from 'lucide-vue-next';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
    href: {
        type: String,
        required: true,
    },
    theme: {
        type: Object,
        required: true,
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
};

const iconComponent = icons[props.theme.icon] || Folder;
</script>

<template>
    <div 
        class="activity-card h-full flex flex-col justify-between relative"
        :style="{
            '--card-border-hover': theme.borderHover,
            background: theme.cardBg,
            border: `2px solid ${theme.border}`,
            boxShadow: theme.shadow,
        }"
    >
        <div>
            <!-- Insignia con icono -->
            <div 
                class="activity-icon-badge mb-3"
                :style="{ background: theme.badgeBg, color: theme.badgeText }"
            >
                <component :is="iconComponent" class="w-6 h-6" />
            </div>

            <!-- Título -->
            <h5 class="activity-card-title mb-1 font-extrabold text-slate-800">
                {{ theme.title || title }}
            </h5>

            <!-- Descripción -->
            <p class="activity-card-desc mb-3 text-slate-600 font-medium">
                {{ description }}
            </p>
        </div>

        <!-- Botón de acción -->
        <div class="flex items-center justify-between mt-auto pt-3">
            <a 
                :href="href"
                class="btn-module-action inline-flex items-center no-underline"
                :style="{
                    backgroundColor: theme.btnBg,
                    color: theme.btnText,
                    border: `1.5px solid ${theme.btnBorder}`,
                }"
            >
                <span>Ir al módulo</span>
                <i class="fas fa-arrow-right ml-1.5 text-xs"></i>
            </a>
        </div>
    </div>
</template>

<style scoped>
.activity-card {
    border-radius: 22px;
    padding: 1.4rem 1.3rem 1.2rem 1.3rem;
    transition: border-color 0.2s ease;
    overflow: hidden;
}

.activity-card:hover {
    border-color: var(--card-border-hover, #4F46E5) !important;
}

.activity-icon-badge {
    width: 50px;
    height: 50px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
}

.activity-card-title {
    font-size: 1.05rem;
    line-height: 1.3;
}

.activity-card-desc {
    font-size: 0.82rem;
    line-height: 1.45;
    min-height: 40px;
}

.btn-module-action {
    font-size: 0.8rem;
    font-weight: 800;
    border-radius: 9999px;
    padding: 7px 16px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.btn-module-action:hover {
    filter: brightness(0.92);
}
</style>
