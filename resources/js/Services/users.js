import { http } from './http';

const BASE = '/users';

export const UsersService = {
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
        return http.post(`${BASE}/${id}`, data, options);
    },

    toggleEstado(id, options = {}) {
        return http.put(`/usuarios/${id}/estado`, {}, options);
    },

    checkDni(dni, params = {}) {
        return http.json(http.buildUrl(`${BASE}/check-dni/${encodeURIComponent(dni)}`, params), {
            headers: { Accept: 'application/json' },
        });
    },

    findInstitucionesByCodModular(codModular) {
        return http.json(`/api/instituciones/${encodeURIComponent(codModular)}`, {
            headers: { Accept: 'application/json' },
        });
    },

    exportUrl(params = {}) {
        return http.buildUrl('/export-users', params);
    },
};
