import { settingsApi } from '$lib/api';
import type { Settings } from '$lib/types';

class SettingsStore {
  settings = $state<Settings | null>(null);
  loading = $state(false);
  initialized = $state(false);

  async init(): Promise<void> {
    if (this.initialized) return;
    this.loading = true;
    try {
      this.settings = await settingsApi.get();
    } catch {
      // Settings are non-critical for rendering; fail silently
    } finally {
      this.loading = false;
      this.initialized = true;
    }
  }

  async refreshSettings(): Promise<void> {
    try {
      this.settings = await settingsApi.get();
    } catch {
      // silent fail
    }
  }

  get siteName(): string {
    return this.settings?.site_name ?? 'Sanggar Pelita Budaya';
  }

  get siteDescription(): string {
    return this.settings?.site_description ?? '';
  }

  get maintenanceMode(): boolean {
    return this.settings?.maintenance_mode ?? false;
  }
}

export const settingsStore = new SettingsStore();