import { http } from './http';

export const AgendasService = {
    list(params = {}, options = {}) {
        return http.get(window.location.pathname, params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            ...options,
        });
    },

    store(data, options = {}) {
        return http.post('/agendas', data, options);
    },

    update(data, options = {}) {
        return http.post('/agendas/update', data, options);
    },

    exportUrl(params = {}, endpoint = '/exportar-agendas') {
        return http.buildUrl(endpoint, params);
    },

    async buscarInstitucionesPorUgel(params = {}) {
        const query = new URLSearchParams(params).toString();
        return http.json(`/buscar-instituciones-por-ugel-ag${query ? `?${query}` : ''}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
    },

    async buscarDocentesPorInstitucion(params = {}) {
        const query = new URLSearchParams(params).toString();
        return http.json(`/buscar-docentes-por-institucion-ag${query ? `?${query}` : ''}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
    },
};
