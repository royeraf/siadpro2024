<script setup>
import { computed } from 'vue';
import { esImagenArchivo, iconoArchivo } from '@/Utils/archivoIconos';

const props = defineProps({
    /** Nombre o ruta del archivo; de ahí se deduce la extensión. */
    nombre: {
        type: String,
        default: '',
    },
    /**
     * URL de la miniatura. Solo se usa cuando el archivo es una imagen; para el
     * resto (o si no se envía) se muestra el icono por tipo.
     */
    url: {
        type: String,
        default: '',
    },
    /** Texto alternativo del icono. */
    alt: {
        type: String,
        default: '',
    },
});

const esMiniatura = computed(() => !!props.url && esImagenArchivo(props.nombre));

const src = computed(() => (esMiniatura.value ? props.url : iconoArchivo(props.nombre)));
</script>

<template>
    <img
        :src="src"
        :alt="alt || nombre || 'archivo'"
        loading="lazy"
        draggable="false"
        :class="esMiniatura ? 'object-cover' : 'object-contain'"
    />
</template>
