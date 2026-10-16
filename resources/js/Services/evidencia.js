import { http } from './http';

const BASE = '/evidencias';

export const EvidenciasService = {
    list(params = {}, options = {}) {
        return http.get(window.location.pathname, params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            ...options,
        });
    },

    exportar(params = {}, endpoint = '/exportar-evidencias') {
        return http.download(endpoint, params);
    },

    store(data, options = {}) {
        return http.post(BASE, data, options);
    },

    update(id, data, options = {}) {
        return http.post(`${BASE}/${id}`, data, options);
    },

    destroy(id, options = {}) {
        return http.delete(`${BASE}/${id}`, options);
    },

    buscarInstitucionesPorUgel(params = {}) {
        const query = new URLSearchParams(params).toString();
        return http.json(`/buscar-instituciones-por-ugel-evi?${query}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
    },
};
