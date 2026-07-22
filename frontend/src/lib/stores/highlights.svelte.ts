import { highlightsApi } from '$lib/api';
import type {
  Highlight,
  HighlightListSummary,
  HighlightListQuery,
  HighlightCategory,
  HighlightSort,
} from '$lib/types';

class HighlightsStore {
  items = $state<HighlightListSummary[]>([]);
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

  category = $state<HighlightCategory | 'all'>('all');
  search = $state('');
  sort = $state<HighlightSort>('newest');
  page = $state(1);

  current = $state<Highlight | null>(null);

  async fetchList(): Promise<void> {
    this.loading = true;
    this.error = null;
    try {
      const query: HighlightListQuery = {
        page: this.page,
        per_page: 12,
        category: this.category,
        search: this.search,
        sort: this.sort,
      };
      const result = await highlightsApi.list(query);
      this.items = result.items;
      this.pagination = result.pagination;
    } catch (e) {
      this.error = e instanceof Error ? e.message : 'Failed to load highlights';
    } finally {
      this.loading = false;
    }
  }

  async fetchBySlug(slug: string): Promise<Highlight | null> {
    this.loading = true;
    this.error = null;
    try {
      this.current = await highlightsApi.getBySlug(slug);
      return this.current;
    } catch (e) {
      this.error = e instanceof Error ? e.message : 'Failed to load highlights';
      this.current = null;
      return null;
    } finally {
      this.loading = false;
    }
  }

  setCategory(category: HighlightCategory | 'all'): void {
    this.category = category;
    this.page = 1;
    this.fetchList();
  }

  setSearch(search: string): void {
    this.search = search;
    this.page = 1;
    this.fetchList();
  }

  setSort(sort: HighlightSort): void {
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
    this.current = null;
    this.error = null;
  }
}

export const highlightsStore = new HighlightsStore();