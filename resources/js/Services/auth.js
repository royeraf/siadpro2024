import { http } from './http';

export const AuthService = {
    logout(options = {}) {
        return http.post('/logout', {}, options);
    },
};
