import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Sigue el estado de una media query CSS de forma reactiva.
 *
 * A diferencia de escuchar `window.resize` y leer `innerWidth`, el ref solo
 * cambia cuando se cruza el breakpoint: evita re-renderizados (y reconfigurar
 * componentes como FullCalendar) en cada píxel que se arrastra el borde de la
 * ventana.
 *
 * @param {string} query Media query válida, p. ej. `'(max-width: 767px)'`.
 * @returns {import('vue').Ref<boolean>} `true` mientras la media query haga match.
 */
export function useMediaQuery(query) {
    const matches = ref(
        typeof window !== 'undefined' ? window.matchMedia(query).matches : false
    );

    let mql = null;
    const update = (event) => {
        matches.value = event.matches;
    };

    onMounted(() => {
        mql = window.matchMedia(query);
        matches.value = mql.matches;
        // `addEventListener` es la API moderna; Safari antiguo usaba
        // `addListener`, pero el proyecto solo apunta a navegadores actuales.
        mql.addEventListener('change', update);
    });

    onBeforeUnmount(() => {
        mql?.removeEventListener('change', update);
    });

    return matches;
}
