import { http } from './http';

const BASE = '/difusions';

const EXPORT_ENDPOINTS = {
    general: '/export-difusion-general',
    ugel: '/export-difusion-ugel',
    director: '/export-difusion-director',
};

export const DifusionsService = {
    list(params = {}, options = {}) {
        return http.get(BASE, params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            ...options,
        });
    },

    exportar(params = {}, scope = 'general') {
        return http.download(EXPORT_ENDPOINTS[scope] || EXPORT_ENDPOINTS.general, params);
    },

    listCurrentPath(params = {}, options = {}) {
        return http.get(window.location.pathname, params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            ...options,
        });
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
