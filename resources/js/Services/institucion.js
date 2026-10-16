import { http } from './http';

const BASE = '/institucions';

export const InstitucionsService = {
    list(params = {}, options = {}) {
        return http.get(BASE, params, {
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
        return http.put(`${BASE}/${id}`, data, options);
    },

    destroy(id, options = {}) {
        return http.delete(`${BASE}/${id}`, options);
    },

    exportUrl(params = {}) {
        return http.buildUrl('/export-instituciones', params);
    },
};
