<script lang="ts">
  import { onMount } from 'svelte';
  import { highlightsStore } from '$lib/stores/highlights.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { uploadUrl, imageUrl } from '$lib/utils';
  import { langStore } from '$lib/stores/lang.svelte';
  import { categoryLabel } from '$lib/constants/categories';
  import { useSearch } from '$lib/hooks/useSearch.svelte';
  import type { HighlightCategory, HighlightSort, HighlightListSummary } from '$lib/types';
  import Badge from '$lib/components/Badge.svelte';
  import Pagination from '$lib/components/Pagination.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import HighlightLightbox from '$lib/components/HighlightLightbox.svelte';
  import { Search, ArrowUpDown } from '@lucide/svelte';

  const search = useSearch('', 400);

  const filters = [
    { value: 'all' as const, label: t('category_all') },
    { value: 'achievement' as HighlightCategory, label: t('category_achievement') },
    { value: 'activity' as HighlightCategory, label: t('category_activity') },
  ];

  const sorts: { value: HighlightSort; label: string }[] = [
    { value: 'newest', label: t('newest') },
    { value: 'oldest', label: t('oldest') },
    { value: 'alphabetical', label: t('alphabetical') },
  ];

  let lightboxOpen = $state(false);
  let activeItem = $state<HighlightListSummary | null>(null);

  onMount(() => {
    highlightsStore.fetchList();
  });

  $effect(() => {
    if (search.debouncedValue !== undefined) {
      highlightsStore.setSearch(search.debouncedValue);
    }
  });

  function getCover(item: { cover?: { filename: string } | null; slug: string }): string {
    return item.cover ? uploadUrl(item.cover.filename) : imageUrl(`highlights-${item.slug}`, 600, 400);
  }

  function title(item: { title_en: string; title_id: string }): string {
    return langStore.current === 'id' ? item.title_id : item.title_en;
  }

  function openLightbox(item: HighlightListSummary): void {
    activeItem = item;
    lightboxOpen = true;
  }

  function closeLightbox(): void {
    lightboxOpen = false;
    activeItem = null;
  }
</script>

<svelte:head>
  <title>{t('highlights_title')} — {t('site_name')}</title>
  <meta name="description" content={t('highlights_description')} />
  <link rel="canonical" href="/highlights" />
  <meta property="og:title" content={t('highlights_title')} />
  <meta property="og:description" content={t('highlights_description')} />
  <meta property="og:type" content="website" />
  <meta name="twitter:card" content="summary" />
  <meta name="twitter:title" content={t('highlights_title')} />
</svelte:head>

<section class="highlights-hero">
  <div class="container highlights-hero__inner">
    <span class="eyebrow eyebrow--centered">{t('highlights_eyebrow')}</span>
    <h1>{t('highlights_title')}</h1>
    <p class="text-pretty">{t('highlights_description')}</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="highlights-controls">
      <div class="highlights-filters">
        {#each filters as filter (filter.value)}
          <button
            class="filter-chip"
            class:filter-chip--active={highlightsStore.category === filter.value}
            onclick={() => highlightsStore.setCategory(filter.value)}
          >
            {filter.label}
          </button>
        {/each}
      </div>

      <div class="highlights-search">
        <Search size={16} class="highlights-search__icon" />
        <input
          type="search"
          class="highlights-search__input"
          placeholder={t('highlights_search_placeholder')}
          value={search.value}
          oninput={(e) => search.set((e.target as HTMLInputElement).value)}
          aria-label={t('search')}
        />
      </div>

      <div class="highlights-sort">
        <ArrowUpDown size={14} />
        <select
          class="highlights-sort__select"
          value={highlightsStore.sort}
          onchange={(e) => highlightsStore.setSort((e.target as HTMLSelectElement).value as HighlightSort)}
          aria-label={t('highlights_sort_label')}
        >
          {#each sorts as sort (sort.value)}
            <option value={sort.value}>{sort.label}</option>
          {/each}
        </select>
      </div>
    </div>

    {#if highlightsStore.loading && highlightsStore.items.length === 0}
      <div class="highlights-grid">
        {#each Array(12) as _, i (i)}
          <Skeleton variant="card" />
        {/each}
      </div>
    {:else if highlightsStore.items.length > 0}
      <div class="highlights-grid">
        {#each highlightsStore.items as item (item.id)}
          <button
            class="highlights-card"
            onclick={() => openLightbox(item)}
            aria-label={title(item)}
          >
            <div class="highlights-card__image">
              <img src={getCover(item)} alt={title(item)} loading="lazy" />
              <div class="highlights-card__overlay"></div>
              <Badge variant={item.category}>{categoryLabel(item.category, langStore.current)}</Badge>
            </div>
            <div class="highlights-card__body">
              <h3 class="highlights-card__title">{title(item)}</h3>
            </div>
          </button>
        {/each}
      </div>

      <Pagination
        pagination={highlightsStore.pagination}
        onchange={(page) => highlightsStore.setPage(page)}
      />
    {:else}
      <EmptyState
        title={search.value ? t('highlights_empty_search') : t('highlights_empty')}
        description={search.value ? t('empty_search') : ''}
      />
    {/if}
  </div>
</section>

<HighlightLightbox open={lightboxOpen} item={activeItem} onclose={closeLightbox} />

<style>
  .highlights-hero {
    padding: var(--sp-12) 0 var(--sp-8);
    text-align: center;
    background: var(--color-surface-alt);
  }

  .highlights-hero__inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-3);
  }

  .highlights-hero h1 {
    font-size: var(--fs-h1);
    font-weight: var(--fw-semibold);
  }

  .highlights-hero p {
    font-size: var(--fs-body-lg);
    color: var(--color-text-muted);
    max-width: 32rem;
  }

  .highlights-controls {
    display: flex;
    align-items: center;
    gap: var(--sp-4);
    margin-bottom: var(--sp-8);
    flex-wrap: wrap;
  }

  .highlights-filters {
    display: flex;
    gap: var(--sp-2);
  }

  .filter-chip {
    padding: var(--sp-2) var(--sp-4);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-full);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    color: var(--color-text-muted);
    transition: all var(--duration-fast) var(--ease-smooth);
  }

  .filter-chip:hover {
    border-color: var(--color-accent);
    color: var(--color-accent);
  }

  .filter-chip--active {
    background: var(--color-accent);
    color: var(--color-white);
    border-color: var(--color-accent);
  }

  .highlights-search {
    position: relative;
    flex: 1;
    min-width: 12rem;
  }

  .highlights-search :global(.highlights-search__icon) {
    position: absolute;
    left: var(--sp-3);
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-text-subtle);
  }

  .highlights-search__input {
    width: 100%;
    padding: var(--sp-2) var(--sp-4) var(--sp-2) var(--sp-8);
    border: 1px solid var(--color-border-strong);
    border-radius: var(--radius-md);
    font-size: var(--fs-body-sm);
  }

  .highlights-search__input:focus {
    outline: none;
    border-color: var(--color-accent);
  }

  .highlights-sort {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    color: var(--color-text-muted);
  }

  .highlights-sort__select {
    border: 1px solid var(--color-border-strong);
    border-radius: var(--radius-md);
    padding: var(--sp-2) var(--sp-3);
    font-size: var(--fs-body-sm);
    background: var(--color-surface);
    cursor: pointer;
  }

  .highlights-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--sp-5);
  }

  .highlights-card {
    display: block;
    width: 100%;
    text-align: left;
    border: none;
    padding: 0;
    border-radius: var(--radius-lg);
    overflow: hidden;
    background: var(--color-surface);
    box-shadow: var(--shadow-sm);
    cursor: pointer;
    transition: box-shadow var(--duration-short) var(--ease-smooth), transform var(--duration-short) var(--ease-out);
  }

  .highlights-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
  }

  .highlights-card__image {
    position: relative;
    aspect-ratio: 4 / 3;
    overflow: hidden;
  }

  .highlights-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-long) var(--ease-out);
  }

  .highlights-card:hover .highlights-card__image img { transform: scale(1.08); }

  .highlights-card__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(26, 22, 18, 0.5), transparent 50%);
  }

  .highlights-card__image :global(.badge) {
    position: absolute;
    top: var(--sp-3);
    left: var(--sp-3);
  }

  .highlights-card__body {
    padding: var(--sp-4) var(--sp-5) var(--sp-5);
  }

  .highlights-card__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
    margin-bottom: 0;
  }

  @media (max-width: 880px) {
    .highlights-grid { grid-template-columns: repeat(2, 1fr); }
  }

  @media (max-width: 560px) {
    .highlights-grid { grid-template-columns: 1fr; }
    .highlights-controls { flex-direction: column; align-items: stretch; }
  }
</style>