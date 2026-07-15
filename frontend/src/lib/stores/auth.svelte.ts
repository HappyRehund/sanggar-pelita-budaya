import { authApi } from '$lib/api';
import { clearCsrfToken } from '$lib/api/client';
import type { User } from '$lib/types';

class AuthStore {
  user = $state<User | null>(null);
  loading = $state(false);
  initialized = $state(false);

  get isAuthenticated(): boolean {
    return this.user !== null;
  }

  async init() {
    if (this.initialized) return;
    this.loading = true;
    try {
      this.user = await authApi.session();
    } catch {
      this.user = null;
    } finally {
      this.loading = false;
      this.initialized = true;
    }
  }

  async login(username: string, password: string) {
    const res = await authApi.login(username, password);
    this.user = res.user;
    return res.user;
  }

  async logout() {
    try {
      await authApi.logout();
    } finally {
      this.user = null;
      clearCsrfToken();
    }
  }
}

export const authStore = new AuthStore();