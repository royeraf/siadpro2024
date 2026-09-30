// Fuente única del frontend para saber qué paths resuelve la SPA (Inertia <Link>)
// sin recarga completa. Debe mantenerse sincronizada con $spaRoutes en
// app/Http/Middleware/HandleInertiaRequests.php (fuente real del sidebar).
//
// Al convertir un módulo a SPA: agregar el path aquí + en $spaRoutes.
export const SPA_PREFIXES = [
    '/inicio',
    '/users',
    '/institucions',
    '/accions',
    '/accion-inicio',
    '/accion-general',
    '/accion-ugel',
    '/accion-director',
    '/difusions',
    '/difusion-inicio',
    '/difusion-general',
    '/difusion-ugel',
    '/difusion-director',
    '/sector',
    '/sectores',
    '/evidencia',
    '/informe',
    '/plan',
    '/produccion',
    '/agenda',
];

export function isSpaPath(url) {
    if (!url || typeof url !== 'string') return false;
    try {
        const path =
            url.startsWith('http://') || url.startsWith('https://')
                ? new URL(url).pathname
                : url.split('?')[0];
        return SPA_PREFIXES.some((prefix) => path.startsWith(prefix));
    } catch (e) {
        return false;
    }
}
