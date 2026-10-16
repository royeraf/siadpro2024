import { http } from './http';

/**
 * Servicios del módulo de Seguridad (roles + permisos).
 * Todas las llamadas usan el router de Inertia salvo `UserAccessService.get`,
 * que consume un endpoint JSON para el modal de accesos.
 */
export const RolesService = {
    list(options = {}) {
        return http.get('/security/roles', {}, {
            preserveState: true,
            preserveScroll: true,
            ...options,
        });
    },

    store(data, options = {}) {
        return http.post('/security/roles', data, options);
    },

    update(id, data, options = {}) {
        return http.put(`/security/roles/${id}`, data, options);
    },

    destroy(id, options = {}) {
        return http.delete(`/security/roles/${id}`, options);
    },
};

export const UserAccessService = {
    async get(userId) {
        return http.json(`/users/${userId}/access`, {
            headers: { Accept: 'application/json' },
        });
    },

    update(userId, data, options = {}) {
        return http.put(`/users/${userId}/access`, data, options);
    },
};
