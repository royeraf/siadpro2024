<script setup>
defineProps({
    header: {
        type: String,
        default: '',
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="space-y-1">
        <!-- Encabezado de sección cuando el sidebar está expandido -->
        <div 
            v-if="header && !collapsed"
            class="px-3 pt-3 pb-1 text-[10px] font-black tracking-widest text-indigo-400 uppercase select-none"
        >
            {{ header }}
        </div>

        <!-- Divisor sutil cuando está colapsado -->
        <div 
            v-else-if="header && collapsed"
            class="my-2.5 mx-auto w-8 h-[1px] bg-white/10"
            :title="header"
        ></div>

        <!-- Contenedor de enlaces de la sección -->
        <nav class="space-y-1">
            <slot />
        </nav>
    </div>
</template>
