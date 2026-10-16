import { http } from './http';

const BASE = '/sectores';

const EXPORT_ENDPOINTS = {
    general: '/export-sectores-general',
    ugel: '/export-sectores-ugel',
    director: '/export-sectores-director',
};

export const SectoresService = {
    list(params = {}, options = {}) {
        return http.get(window.location.pathname, params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            ...options,
        });
    },

    exportar(params = {}, scope = 'general') {
        return http.download(EXPORT_ENDPOINTS[scope] || EXPORT_ENDPOINTS.general, params);
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
};
