<script lang="ts">
  import { onMount } from 'svelte';
  import { highlightsApi } from '$lib/api';
  import { t } from '$lib/i18n/index.svelte';
  import { uploadUrl, imageUrl } from '$lib/utils';
  import { revealOnScroll, staggerReveal } from '$lib/utils';
  import { useLightbox } from '$lib/hooks/useLightbox.svelte';
  import type { LightboxImage } from '$lib/hooks/useLightbox.svelte';
  import type { HighlightListSummary } from '$lib/types';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import Lightbox from '$lib/components/Lightbox.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import Button from '$lib/components/Button.svelte';
  import PatternDivider from '$lib/components/PatternDivider.svelte';
  import { ArrowRight } from '@lucide/svelte';

  const COLLAGE_LIMIT = 8;

  let highlights = $state<HighlightListSummary[]>([]);
  let loading = $state(true);
  let sectionEl = $state<HTMLElement | null>(null);

  const lightbox = useLightbox();

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

  function openLightbox(index: number): void {
    const items: LightboxImage[] = highlights.map((h) => ({
      src: h.cover ? uploadUrl(h.cover.filename) : imageUrl(`highlights-${h.slug}`, 400, 300),
      alt: h.title,
    }));
    lightbox.open(items, index);
  }

  function getImageSrc(item: HighlightListSummary): string {
    return item.cover ? uploadUrl(item.cover.filename) : imageUrl(`highlights-${item.slug}`, 400, 300);
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
          <button
            class="highlights__item"
            class:highlights__item--large={i === 0 || i === 5}
            onclick={() => openLightbox(i)}
            aria-label={item.title}
          >
            <img src={getImageSrc(item)} alt={item.title} loading="lazy" />
          </button>
        {/each}
      </div>
      <div class="highlights__cta">
        <Button variant="secondary" size="md" href="/highlights">
          {t('highlights_view_all')}
          <ArrowRight size={16} />
        </Button>
      </div>
    {:else}
      <EmptyState title={t('highlights_empty')} />
    {/if}
  </div>
  <PatternDivider position="bottom" />
</section>

<Lightbox
  open={lightbox.isOpen}
  images={lightbox.images}
  index={lightbox.currentIndex}
  onclose={lightbox.close}
  onnext={lightbox.next}
  onprev={lightbox.prev}
/>

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
    border: none;
    padding: 0;
    overflow: hidden;
    border-radius: var(--radius-lg);
    cursor: pointer;
    background: var(--color-surface-alt);
    transition: opacity var(--duration-fast) var(--ease-smooth);
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

  .highlights__item:hover {
    opacity: 0.9;
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
  }
</style>