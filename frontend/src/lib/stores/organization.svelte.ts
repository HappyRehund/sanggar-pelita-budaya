import { organizationApi } from '$lib/api';
import type { OrganizationMember } from '$lib/types';

class OrganizationStore {
  members = $state<OrganizationMember[]>([]);
  featured = $state<OrganizationMember[]>([]);
  loading = $state(false);
  error = $state<string | null>(null);

  async fetchList(): Promise<void> {
    this.loading = true;
    this.error = null;
    try {
      this.members = await organizationApi.list();
    } catch (e) {
      this.error = e instanceof Error ? e.message : 'Failed to load organization';
    } finally {
      this.loading = false;
    }
  }

  async fetchFeatured(): Promise<void> {
    this.loading = true;
    this.error = null;
    try {
      this.featured = await organizationApi.featured();
    } catch (e) {
      this.error = e instanceof Error ? e.message : 'Failed to load featured members';
    } finally {
      this.loading = false;
    }
  }

  reset(): void {
    this.members = [];
    this.featured = [];
    this.error = null;
  }
}

export const organizationStore = new OrganizationStore();