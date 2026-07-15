import { api, ApiError } from './client';
import type { User } from '$lib/types';

export { ApiError };

export const authApi = {
  login: (username: string, password: string) =>
    api.post<{ user: User }>('/api/auth/login', { username, password }),
  logout: () => api.post<null>('/api/auth/logout'),
  me: () => api.get<User>('/api/auth/me'),
};

export const healthApi = {
  check: () => api.get<{ status: string; time: string; env: string }>('/api/health'),
};