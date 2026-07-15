<script lang="ts">
  import { onMount } from 'svelte';
  import { portfolioStore } from '$lib/stores/portfolio.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { formatDate, uploadUrl, imageUrl } from '$lib/utils';
  import { langStore } from '$lib/stores/lang.svelte';
  import { categoryLabel } from '$lib/constants/categories';
  import { portfolioDetailPath } from '$lib/constants/routes';
  import { useSearch } from '$lib/hooks/useSearch.svelte';
  import type { PortfolioCategory, PortfolioSort } from '$lib/types';
  import Badge from '$lib/components/Badge.svelte';
  import Pagination from '$lib/components/Pagination.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import { Search, ArrowUpDown } from '@lucide/svelte';

  const search = useSearch('', 400);

  const filters = [
    { value: 'all' as const, label: t('category_all') },
    { value: 'achievement' as PortfolioCategory, label: t('category_achievement') },
    { value: 'activity' as PortfolioCategory, label: t('category_activity') },
  ];

  const sorts: { value: PortfolioSort; label: string }[] = [
    { value: 'newest', label: t('newest') },
    { value: 'oldest', label: t('oldest') },
    { value: 'alphabetical', label: t('alphabetical') },
  ];

  onMount(() => {
    portfolioStore.fetchList();
  });

  $effect(() => {
    if (search.debouncedValue !== undefined) {
      portfolioStore.setSearch(search.debouncedValue);
    }
  });

  function getCover(item: { cover?: { filename: string } | null; slug: string }): string {
    return item.cover ? uploadUrl(item.cover.filename) : imageUrl(`portfolio-${item.slug}`, 600, 400);
  }
</script>

<svelte:head>
  <title>{t('portfolio_title')} — {t('site_name')}</title>
  <meta name="description" content={t('portfolio_description')} />
</svelte:head>

<section class="portfolio-hero">
  <div class="container portfolio-hero__inner">
    <span class="eyebrow eyebrow--centered">{t('portfolio_eyebrow')}</span>
    <h1>{t('portfolio_title')}</h1>
    <p class="text-pretty">{t('portfolio_description')}</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="portfolio-controls">
      <div class="portfolio-filters">
        {#each filters as filter (filter.value)}
          <button
            class="filter-chip"
            class:filter-chip--active={portfolioStore.category === filter.value}
            onclick={() => portfolioStore.setCategory(filter.value)}
          >
            {filter.label}
          </button>
        {/each}
      </div>

      <div class="portfolio-search">
        <Search size={16} class="portfolio-search__icon" />
        <input
          type="search"
          class="portfolio-search__input"
          placeholder={t('portfolio_search_placeholder')}
          value={search.value}
          oninput={(e) => search.set((e.target as HTMLInputElement).value)}
          aria-label={t('search')}
        />
      </div>

      <div class="portfolio-sort">
        <ArrowUpDown size={14} />
        <select
          class="portfolio-sort__select"
          value={portfolioStore.sort}
          onchange={(e) => portfolioStore.setSort((e.target as HTMLSelectElement).value as PortfolioSort)}
          aria-label={t('portfolio_sort_label')}
        >
          {#each sorts as sort (sort.value)}
            <option value={sort.value}>{sort.label}</option>
          {/each}
        </select>
      </div>
    </div>

    {#if portfolioStore.loading && portfolioStore.items.length === 0}
      <div class="portfolio-grid">
        {#each Array(12) as _, i (i)}
          <Skeleton variant="card" />
        {/each}
      </div>
    {:else if portfolioStore.items.length > 0}
      <div class="portfolio-grid">
        {#each portfolioStore.items as item (item.id)}
          <a href={portfolioDetailPath(item.slug)} class="portfolio-card">
            <div class="portfolio-card__image">
              <img src={getCover(item)} alt={item.title} loading="lazy" />
              <div class="portfolio-card__overlay"></div>
              <Badge variant={item.category}>{categoryLabel(item.category, langStore.current)}</Badge>
            </div>
            <div class="portfolio-card__body">
              <h3 class="portfolio-card__title">{item.title}</h3>
              <p class="portfolio-card__desc">{item.short_description}</p>
              <div class="portfolio-card__meta">
                {#if item.event_date}
                  <span>{formatDate(item.event_date, langStore.current, 'short')}</span>
                {/if}
                {#if item.location}
                  <span>· {item.location}</span>
                {/if}
              </div>
            </div>
          </a>
        {/each}
      </div>

      <Pagination
        pagination={portfolioStore.pagination}
        onchange={(page) => portfolioStore.setPage(page)}
      />
    {:else}
      <EmptyState
        title={search.value ? t('portfolio_empty_search') : t('portfolio_empty')}
        description={search.value ? t('empty_search') : ''}
      />
    {/if}
  </div>
</section>

<style>
  .portfolio-hero {
    padding: var(--sp-12) 0 var(--sp-8);
    text-align: center;
    background: var(--color-surface-alt);
  }

  .portfolio-hero__inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-3);
  }

  .portfolio-hero h1 {
    font-size: var(--fs-h1);
    font-weight: var(--fw-semibold);
  }

  .portfolio-hero p {
    font-size: var(--fs-body-lg);
    color: var(--color-text-muted);
    max-width: 32rem;
  }

  .portfolio-controls {
    display: flex;
    align-items: center;
    gap: var(--sp-4);
    margin-bottom: var(--sp-8);
    flex-wrap: wrap;
  }

  .portfolio-filters {
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

  .portfolio-search {
    position: relative;
    flex: 1;
    min-width: 12rem;
  }

  .portfolio-search__icon {
    position: absolute;
    left: var(--sp-3);
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-text-subtle);
  }

  .portfolio-search__input {
    width: 100%;
    padding: var(--sp-2) var(--sp-4) var(--sp-2) var(--sp-8);
    border: 1px solid var(--color-border-strong);
    border-radius: var(--radius-md);
    font-size: var(--fs-body-sm);
  }

  .portfolio-search__input:focus {
    outline: none;
    border-color: var(--color-accent);
  }

  .portfolio-sort {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    color: var(--color-text-muted);
  }

  .portfolio-sort__select {
    border: 1px solid var(--color-border-strong);
    border-radius: var(--radius-md);
    padding: var(--sp-2) var(--sp-3);
    font-size: var(--fs-body-sm);
    background: var(--color-surface);
    cursor: pointer;
  }

  .portfolio-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--sp-5);
  }

  .portfolio-card {
    display: block;
    border-radius: var(--radius-lg);
    overflow: hidden;
    background: var(--color-surface);
    box-shadow: var(--shadow-sm);
    transition: box-shadow var(--duration-short) var(--ease-smooth), transform var(--duration-short) var(--ease-out);
  }

  .portfolio-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
  }

  .portfolio-card__image {
    position: relative;
    aspect-ratio: 4 / 3;
    overflow: hidden;
  }

  .portfolio-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-long) var(--ease-out);
  }

  .portfolio-card:hover .portfolio-card__image img { transform: scale(1.08); }

  .portfolio-card__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(26, 22, 18, 0.5), transparent 50%);
  }

  .portfolio-card__image :global(.badge) {
    position: absolute;
    top: var(--sp-3);
    left: var(--sp-3);
  }

  .portfolio-card__body {
    padding: var(--sp-4) var(--sp-5) var(--sp-5);
  }

  .portfolio-card__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
    margin-bottom: var(--sp-1);
  }

  .portfolio-card__desc {
    font-size: var(--fs-body-sm);
    color: var(--color-text-muted);
    line-height: var(--lh-normal);
    margin-bottom: var(--sp-2);
  }

  .portfolio-card__meta {
    font-size: var(--fs-caption);
    color: var(--color-text-subtle);
  }

  @media (max-width: 880px) {
    .portfolio-grid { grid-template-columns: repeat(2, 1fr); }
  }

  @media (max-width: 560px) {
    .portfolio-grid { grid-template-columns: 1fr; }
    .portfolio-controls { flex-direction: column; align-items: stretch; }
  }
</style>