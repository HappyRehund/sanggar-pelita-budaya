import { api, ApiError } from './client';
import type { User } from '$lib/types';

export { ApiError };

export const authApi = {
  login: (username: string, password: string) =>
    api.post<{ user: User }>('/api/login', { username, password }),
  logout: () => api.post<null>('/api/logout'),
  session: () => api.get<User>('/api/session'),
};

export const healthApi = {
  check: () => api.get<{ status: string; time: string; env: string }>('/api/health'),
};