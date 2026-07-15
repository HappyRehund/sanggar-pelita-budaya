import { organizationApi } from '$lib/api';
import type { OrganizationMember } from '$lib/types';

class OrganizationStore {
  members = $state<OrganizationMember[]>([]);
  tree = $state<OrganizationMember[]>([]);
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

  async fetchTree(): Promise<void> {
    this.loading = true;
    this.error = null;
    try {
      this.tree = await organizationApi.tree();
    } catch (e) {
      this.error = e instanceof Error ? e.message : 'Failed to load organization tree';
    } finally {
      this.loading = false;
    }
  }

  reset(): void {
    this.members = [];
    this.tree = [];
    this.error = null;
  }
}

export const organizationStore = new OrganizationStore();