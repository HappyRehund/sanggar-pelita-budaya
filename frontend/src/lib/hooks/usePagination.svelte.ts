import type { PaginationMeta } from '$lib/types';

export function usePagination(meta: () => PaginationMeta) {
  const currentPage = $derived(meta().current_page);
  const totalPages = $derived(meta().total_pages);
  const hasNext = $derived(meta().has_next);
  const hasPrev = $derived(meta().has_prev);
  const totalItems = $derived(meta().total_items);
  const perPage = $derived(meta().per_page);

  const pages = $derived.by(() => {
    const total = totalPages;
    const current = currentPage;
    const maxVisible = 7;

    if (total <= maxVisible) {
      return Array.from({ length: total }, (_, i) => i + 1);
    }

    const pages: (number | '...')[] = [];
    const half = Math.floor(maxVisible / 2);
    const start = Math.max(1, current - half);
    const end = Math.min(total, current + half);

    if (start > 1) {
      pages.push(1);
      if (start > 2) pages.push('...');
    }

    for (let i = start; i <= end; i++) {
      pages.push(i);
    }

    if (end < total) {
      if (end < total - 1) pages.push('...');
      pages.push(total);
    }

    return pages;
  });

  return {
    get currentPage() { return currentPage; },
    get totalPages() { return totalPages; },
    get hasNext() { return hasNext; },
    get hasPrev() { return hasPrev; },
    get totalItems() { return totalItems; },
    get perPage() { return perPage; },
    get pages() { return pages; },
  };
}