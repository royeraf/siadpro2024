import { router } from '@inertiajs/vue3';

const JSON_HEADERS = {
    'X-Requested-With': 'XMLHttpRequest',
};

function withQuery(url, params = {}) {
    const query = new URLSearchParams(
        Object.entries(params).filter(([, value]) => value !== undefined && value !== null && value !== '')
    ).toString();

    return query ? `${url}?${query}` : url;
}

export const http = {
    get: (url, params = {}, options = {}) => router.get(url, params, options),
    post: (url, data = {}, options = {}) => router.post(url, data, options),
    put: (url, data = {}, options = {}) => router.put(url, data, options),
    patch: (url, data = {}, options = {}) => router.patch(url, data, options),
    delete: (url, options = {}) => router.delete(url, options),

    async json(url, init = {}) {
        const { headers = {}, ...rest } = init;
        const response = await fetch(url, {
            ...rest,
            headers: { ...JSON_HEADERS, ...headers },
        });
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    },

    buildUrl: withQuery,

    download(url, params = {}) {
        window.location.href = withQuery(url, params);
    },
};
