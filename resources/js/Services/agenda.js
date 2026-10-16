import { http } from './http';
import axios from 'axios';

const BASE = '/agendas';

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
        return http.post(BASE, data, options);
    },

    update(id, data, options = {}) {
        return http.put(`${BASE}/${id}`, data, options);
    },

    destroy(id, options = {}) {
        return http.delete(`${BASE}/${id}`, options);
    },

    // ---- Evidencias (archivos) por día programado ----

    async evidencias(agendaId) {
        return http.json(`${BASE}/${agendaId}/evidencias`);
    },

    /**
     * Sube las evidencias de un día **fuera del ciclo de visitas de Inertia**
     * (axios/XHR). Así la subida sigue en segundo plano aunque el usuario cierre
     * el modal o navegue, en lugar de cancelarse. Devuelve una promesa y reporta
     * el progreso (0-100) por `onProgress`.
     */
    subirEvidencias(agendaId, formData, { onProgress } = {}) {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        return axios.post(`${BASE}/${agendaId}/evidencias`, formData, {
            headers: {
                // Se elimina la convención "ajax": axios envía Accept JSON y el
                // backend responde JSON por `expectsJson()`.
                'X-Requested-With': null,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            onUploadProgress: (e) => {
                if (onProgress && e.total) {
                    onProgress(Math.min(100, Math.max(0, (e.loaded / e.total) * 100)));
                }
            },
        });
    },

    destroyEvidencia(evidenciaId, options = {}) {
        return http.delete(`${BASE}/evidencias/${evidenciaId}`, options);
    },

    downloadEvidenciaUrl(evidenciaId) {
        return `${BASE}/evidencias/${evidenciaId}/download`;
    },

    visorUrl(enlace) {
        return `/visor/stream?path=${encodeURIComponent(enlace)}`;
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
