import { onBeforeUnmount } from 'vue';

/**
 * Bloqueo del scroll del body compartido por capas (modales, visor, etc.).
 *
 * Antes cada componente escribía `document.body.style.overflow` por su cuenta:
 * al cerrar el visor que estaba encima del detalle, el body se desbloqueaba
 * aunque el detalle siguiera abierto. Con un contador, el scroll solo se
 * restaura cuando se cierra la última capa.
 */
let locks = 0;

export function useBodyScrollLock() {
    let held = false;

    function lock() {
        if (held) return;
        held = true;
        locks += 1;
        if (typeof document !== 'undefined') {
            document.body.style.overflow = 'hidden';
        }
    }

    function unlock() {
        if (!held) return;
        held = false;
        locks = Math.max(0, locks - 1);
        if (locks === 0 && typeof document !== 'undefined') {
            document.body.style.overflow = '';
        }
    }

    onBeforeUnmount(unlock);

    return { lock, unlock };
}
