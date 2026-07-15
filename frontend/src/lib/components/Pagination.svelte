<script lang="ts">
  import { ChevronLeft, ChevronRight } from '@lucide/svelte';
  import type { PaginationMeta } from '$lib/types';

  interface Props {
    pagination: PaginationMeta;
    onchange: (page: number) => void;
  }

  let { pagination, onchange }: Props = $props();

  const pages = $derived.by(() => {
    const total = pagination.total_pages;
    const current = pagination.current_page;
    const maxVisible = 7;
    if (total <= maxVisible) {
      return Array.from({ length: total }, (_, i) => i + 1);
    }

    const result: (number | '...')[] = [];
    const half = Math.floor(maxVisible / 2);
    const start = Math.max(1, current - half);
    const end = Math.min(total, current + half);

    if (start > 1) {
      result.push(1);
      if (start > 2) result.push('...');
    }
    for (let i = start; i <= end; i++) result.push(i);
    if (end < total) {
      if (end < total - 1) result.push('...');
      result.push(total);
    }
    return result;
  });
</script>

{#if pagination.total_pages > 1}
  <nav class="pagination" aria-label="Pagination">
    <button
      class="pagination__btn pagination__btn--nav"
      disabled={!pagination.has_prev}
      onclick={() => onchange(pagination.current_page - 1)}
      aria-label="Previous page"
    >
      <ChevronLeft size={18} />
    </button>

    {#each pages as page, i (i)}
      {#if page === '...'}
        <span class="pagination__ellipsis">…</span>
      {:else}
        <button
          class="pagination__btn"
          class:pagination__btn--active={page === pagination.current_page}
          onclick={() => onchange(page)}
          aria-label={`Page ${page}`}
          aria-current={page === pagination.current_page ? 'page' : undefined}
        >
          {page}
        </button>
      {/if}
    {/each}

    <button
      class="pagination__btn pagination__btn--nav"
      disabled={!pagination.has_next}
      onclick={() => onchange(pagination.current_page + 1)}
      aria-label="Next page"
    >
      <ChevronRight size={18} />
    </button>
  </nav>
{/if}

<style>
  .pagination {
    display: flex;
    align-items: center;
    gap: var(--sp-1);
    justify-content: center;
  }

  .pagination__btn {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0 var(--sp-2);
    border-radius: var(--radius-md);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    color: var(--color-text-muted);
    transition: background-color var(--duration-fast) var(--ease-smooth), color var(--duration-fast) var(--ease-smooth);
  }

  .pagination__btn:hover:not(:disabled):not(.pagination__btn--active) {
    background-color: var(--color-surface-alt);
    color: var(--color-text);
  }

  .pagination__btn--active {
    background-color: var(--color-accent);
    color: var(--color-white);
  }

  .pagination__btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }

  .pagination__btn--nav {
    color: var(--color-text);
  }

  .pagination__ellipsis {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2.5rem;
    color: var(--color-text-subtle);
  }
</style>