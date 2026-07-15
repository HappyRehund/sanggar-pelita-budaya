import { settingsApi, heroApi, footerApi } from '$lib/api';
import type { Settings, Hero, Footer } from '$lib/types';

class SettingsStore {
  settings = $state<Settings | null>(null);
  hero = $state<Hero | null>(null);
  footer = $state<Footer | null>(null);
  loading = $state(false);
  initialized = $state(false);

  async init(): Promise<void> {
    if (this.initialized) return;
    this.loading = true;
    try {
      const [settings, hero, footer] = await Promise.all([
        settingsApi.get(),
        heroApi.get(),
        footerApi.get(),
      ]);
      this.settings = settings;
      this.hero = hero;
      this.footer = footer;
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

  async refreshHero(): Promise<void> {
    try {
      this.hero = await heroApi.get();
    } catch {
      // silent fail
    }
  }

  async refreshFooter(): Promise<void> {
    try {
      this.footer = await footerApi.get();
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