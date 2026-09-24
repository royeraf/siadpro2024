<script setup>
import { useUiStore } from '@/stores/ui';
import Sidebar from '@/Components/Navigation/Sidebar.vue';
import Navbar from '@/Components/Navigation/Navbar.vue';

// El store auto-inicializa sus listeners de resize; no se requiere onMounted.
const uiStore = useUiStore();
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans">
        <!-- Barra lateral (Sidebar reactivo con Pinia) -->
        <Sidebar />

        <!-- Overlay para móviles cuando el sidebar está abierto -->
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="uiStore.sidebarMobileOpen"
                class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs lg:hidden"
                @click="uiStore.closeMobileSidebar"
            ></div>
        </Transition>

        <!-- Contenedor de contenido principal con padding dinámico según colapso -->
        <div 
            class="flex-1 flex flex-col transition-all duration-300 min-w-0"
            :class="[
                uiStore.sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'
            ]"
        >
            <!-- Cabecera Superior -->
            <Navbar />

            <!-- Contenido dinámico (Páginas SPA) -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
