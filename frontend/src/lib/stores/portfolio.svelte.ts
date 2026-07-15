import { portfolioApi } from '$lib/api';
import type {
  Portfolio,
  PortfolioListSummary,
  PortfolioListQuery,
  PortfolioCategory,
  PortfolioSort,
} from '$lib/types';

class PortfolioStore {
  items = $state<PortfolioListSummary[]>([]);
  pagination = $state({
    current_page: 1,
    total_pages: 1,
    total_items: 0,
    per_page: 12,
    has_next: false,
    has_prev: false,
  });
  loading = $state(false);
  error = $state<string | null>(null);

  category = $state<PortfolioCategory | 'all'>('all');
  search = $state('');
  sort = $state<PortfolioSort>('newest');
  page = $state(1);

  featured = $state<PortfolioListSummary[]>([]);
  current = $state<Portfolio | null>(null);

  async fetchList(): Promise<void> {
    this.loading = true;
    this.error = null;
    try {
      const query: PortfolioListQuery = {
        page: this.page,
        per_page: 12,
        category: this.category,
        search: this.search,
        sort: this.sort,
        published: true,
      };
      const result = await portfolioApi.list(query);
      this.items = result.items;
      this.pagination = result.pagination;
    } catch (e) {
      this.error = e instanceof Error ? e.message : 'Failed to load portfolio';
    } finally {
      this.loading = false;
    }
  }

  async fetchFeatured(limit = 6): Promise<void> {
    try {
      this.featured = await portfolioApi.featured(limit);
    } catch {
      this.featured = [];
    }
  }

  async fetchBySlug(slug: string): Promise<Portfolio | null> {
    this.loading = true;
    this.error = null;
    try {
      this.current = await portfolioApi.getBySlug(slug);
      return this.current;
    } catch (e) {
      this.error = e instanceof Error ? e.message : 'Failed to load portfolio';
      this.current = null;
      return null;
    } finally {
      this.loading = false;
    }
  }

  setCategory(category: PortfolioCategory | 'all'): void {
    this.category = category;
    this.page = 1;
    this.fetchList();
  }

  setSearch(search: string): void {
    this.search = search;
    this.page = 1;
    this.fetchList();
  }

  setSort(sort: PortfolioSort): void {
    this.sort = sort;
    this.page = 1;
    this.fetchList();
  }

  setPage(page: number): void {
    this.page = page;
    this.fetchList();
  }

  reset(): void {
    this.category = 'all';
    this.search = '';
    this.sort = 'newest';
    this.page = 1;
    this.items = [];
    this.featured = [];
    this.current = null;
    this.error = null;
  }
}

export const portfolioStore = new PortfolioStore();