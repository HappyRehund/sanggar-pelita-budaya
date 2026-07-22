<script lang="ts">
  import { onMount } from 'svelte';
  import { highlightsApi } from '$lib/api';
  import { t } from '$lib/i18n/index.svelte';
  import { uploadUrl } from '$lib/utils';
  import { revealOnScroll, staggerReveal } from '$lib/utils';
  import { HIGHLIGHTS_GALLERY_IMAGE_LIMIT } from '$lib/constants/uploadLimits';
  import { useLightbox } from '$lib/hooks/useLightbox.svelte';
  import type { LightboxImage } from '$lib/hooks/useLightbox.svelte';
  import type { HighlightMedia } from '$lib/types';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import Lightbox from '$lib/components/Lightbox.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import Button from '$lib/components/Button.svelte';
  import PatternDivider from '$lib/components/PatternDivider.svelte';
  import { ArrowRight } from '@lucide/svelte';

  let highlightsImages = $state<HighlightMedia[]>([]);
  let loading = $state(true);
  let sectionEl = $state<HTMLElement | null>(null);

  const lightbox = useLightbox();

  onMount(() => {
    let cleanups: (() => void)[] = [];

    (async () => {
      try {
        const all = await highlightsApi.galleryImages();
        highlightsImages = shuffle(all).slice(0, HIGHLIGHTS_GALLERY_IMAGE_LIMIT);
      } catch {
        highlightsImages = [];
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

  function shuffle<T>(arr: T[]): T[] {
    const copy = [...arr];
    for (let i = copy.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [copy[i], copy[j]] = [copy[j], copy[i]];
    }
    return copy;
  }

  function openLightbox(index: number): void {
    const items: LightboxImage[] = highlightsImages.map((img) => ({
      src: uploadUrl(img.filename),
      alt: img.alt_text ?? 'Highlights image',
    }));
    lightbox.open(items, index);
  }

  function getImageSrc(img: HighlightMedia): string {
    return uploadUrl(img.filename);
  }
</script>

<section bind:this={sectionEl} class="section section--alt highlights">
  <PatternDivider position="top" />
  <div class="container highlights-container">
    <SectionTitle eyebrow={t('highlights_eyebrow')} title={t('highlights_title')} description={t('highlights_description')} align="center" />

    {#if loading}
      <div class="highlights__collage">
        {#each Array(10) as _, i (i)}
          <Skeleton variant="rect" />
        {/each}
      </div>
    {:else if highlightsImages.length > 0}
      <div class="highlights__collage">
        {#each highlightsImages as img, i (img.id)}
          <button
            class="highlights__item"
            class:highlights__item--large={i === 0 || i === 5}
            onclick={() => openLightbox(i)}
            aria-label="View image"
          >
            <img src={getImageSrc(img)} alt={img.alt_text ?? 'Highlights image'} loading="lazy" />
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
    /* background-color: green; */
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