<script lang="ts">
  import { onMount } from 'svelte';
  import { highlightsApi } from '$lib/api';
  import { t } from '$lib/i18n/index.svelte';
  import { uploadUrl, imageUrl } from '$lib/utils';
  import { revealOnScroll, staggerReveal } from '$lib/utils';
  import { langStore } from '$lib/stores/lang.svelte';
  import { categoryLabel } from '$lib/constants/categories';
  import type { HighlightListSummary } from '$lib/types';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import Button from '$lib/components/Button.svelte';
  import PatternDivider from '$lib/components/PatternDivider.svelte';

  const COLLAGE_LIMIT = 8;

  let highlights = $state<HighlightListSummary[]>([]);
  let loading = $state(true);
  let sectionEl = $state<HTMLElement | null>(null);

  onMount(() => {
    let cleanups: (() => void)[] = [];

    (async () => {
      try {
        const result = await highlightsApi.list({ per_page: 50 });
        highlights = result.items
          .filter((h) => h.cover)
          .slice(0, COLLAGE_LIMIT);
      } catch {
        highlights = [];
      } finally {
        loading = false;
      }
      if (sectionEl) {
        cleanups.push(revealOnScroll(sectionEl, { y: 30, duration: 0.6 }));
        cleanups.push(staggerReveal(sectionEl, '.highlights__item', { stagger: 0.06, duration: 0.4 }));
      }
    })();

    return () => { cleanups.forEach((c) => c()); };
  });

  function getImageSrc(item: HighlightListSummary): string {
    return item.cover ? uploadUrl(item.cover.filename) : imageUrl(`highlights-${item.slug}`, 400, 300);
  }

  function title(item: HighlightListSummary): string {
    return langStore.current === 'id' ? item.title_id : item.title_en;
  }

  function category(item: HighlightListSummary): string {
    return categoryLabel(item.category, langStore.current);
  }
</script>

<section bind:this={sectionEl} class="section section--alt highlights">
  <PatternDivider position="top" />
  <div class="container highlights-container">
    <SectionTitle eyebrow={t('highlights_eyebrow')} title={t('highlights_title')} description={t('highlights_description')} align="center" />

    {#if loading}
      <div class="highlights__collage">
        {#each Array(COLLAGE_LIMIT) as _, i (i)}
          <Skeleton variant="rect" />
        {/each}
      </div>
    {:else if highlights.length > 0}
      <div class="highlights__collage">
        {#each highlights as item, i (item.id)}
          <a
            class="highlights__item"
            class:highlights__item--large={i === 0 || i === 5}
            href="/highlights"
            aria-label={title(item)}
          >
            <img src={getImageSrc(item)} alt={title(item)} loading="lazy" />
            <span class="highlights__overlay">
              <span class="highlights__overlay-cat">{category(item)}</span>
              <span class="highlights__overlay-title">{title(item)}</span>
            </span>
          </a>
        {/each}
      </div>
      <div class="highlights__cta">
        <Button variant="outline-gradient" radius="xs" size="md" href="/highlights">
          {t('highlights_view_all')}
        </Button>
      </div>
    {:else}
      <EmptyState title={t('highlights_empty')} />
    {/if}
  </div>
  <PatternDivider position="bottom" />
</section>

<style>
  .highlights-container {
    margin-top: var(--sp-12);
    margin-bottom: var(--sp-12)
  }
  .highlights__collage {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-auto-rows: 12rem;
    gap: var(--sp-3);
    margin-top: var(--sp-8);
  }

  .highlights__item {
    position: relative;
    display: block;
    border: none;
    padding: 0;
    overflow: hidden;
    border-radius: var(--radius-lg);
    background: var(--color-surface-alt);
  }

  .highlights__item--large {
    grid-row: span 2;
    grid-column: span 2;
  }

  .highlights__item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-long) var(--ease-out);
  }

  .highlights__item:hover img {
    transform: scale(1.06);
  }

  /* Hover overlay — dark gradient with category + title, fades in on hover. */
  .highlights__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    gap: var(--sp-1);
    padding: var(--sp-4);
    background: linear-gradient(
      to top,
      rgba(26, 22, 18, 0.88) 0%,
      rgba(26, 22, 18, 0.5) 45%,
      transparent 75%
    );
    opacity: 0;
    transition: opacity var(--duration-short) var(--ease-smooth);
    pointer-events: none;
  }

  .highlights__item:hover .highlights__overlay,
  .highlights__item:focus-visible .highlights__overlay {
    opacity: 1;
  }

  .highlights__overlay-cat {
    font-size: var(--fs-caption);
    font-weight: var(--fw-semibold);
    text-transform: uppercase;
    letter-spacing: var(--tracking-widest);
    color: var(--color-accent);
  }

  .highlights__overlay-title {
    font-family: var(--font-serif);
    font-size: var(--fs-h5);
    font-weight: var(--fw-semibold);
    line-height: var(--lh-tight);
    color: var(--color-white);
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .highlights__item--large .highlights__overlay-title {
    font-size: var(--fs-h4);
  }

  .highlights__cta {
    display: flex;
    justify-content: center;
    margin-top: var(--sp-8);
  }

  @media (max-width: 768px) {
    .highlights__collage {
      grid-template-columns: repeat(2, 1fr);
      grid-auto-rows: 8rem;
    }
    .highlights__item--large {
      grid-row: span 1;
      grid-column: span 1;
    }
    .highlights__overlay-title {
      font-size: var(--fs-body-sm);
    }
  }
</style>