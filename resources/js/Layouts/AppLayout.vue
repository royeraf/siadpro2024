<script setup>
import { ref, onMounted } from 'vue';
import Sidebar from '@/Components/Navigation/Sidebar.vue';
import Navbar from '@/Components/Navigation/Navbar.vue';

const sidebarCollapsed = ref(true); // Inicialmente cerrado en móviles

onMounted(() => {
    if (window.innerWidth >= 1024) {
        sidebarCollapsed.value = false;
    }
});

function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans">
        <!-- Barra lateral -->
        <Sidebar 
            :collapsed="sidebarCollapsed" 
            @close="sidebarCollapsed = true"
        />

        <!-- Overlay para móviles cuando el sidebar está abierto -->
        <div 
            v-if="!sidebarCollapsed"
            class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-xs lg:hidden"
            @click="sidebarCollapsed = true"
        ></div>

        <!-- Contenedor de contenido principal -->
        <div 
            class="flex-1 flex flex-col transition-all duration-300 min-w-0"
            :class="[
                sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'
            ]"
        >
            <!-- Cabecera Superior -->
            <Navbar 
                :sidebar-collapsed="sidebarCollapsed"
                @toggle-sidebar="toggleSidebar"
            />

            <!-- Contenido dinámico (Páginas SPA) -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
