<script lang="ts">
  import { onMount } from 'svelte';
  import { portfolioApi } from '$lib/api';
  import { t } from '$lib/i18n/index.svelte';
  import { uploadUrl } from '$lib/utils';
  import { revealOnScroll, staggerReveal } from '$lib/utils';
  import { GALLERY_IMAGE_LIMIT } from '$lib/constants/uploadLimits';
  import { useLightbox } from '$lib/hooks/useLightbox.svelte';
  import type { LightboxImage } from '$lib/hooks/useLightbox.svelte';
  import type { PortfolioMedia } from '$lib/types';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import Lightbox from '$lib/components/Lightbox.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';

  let galleryImages = $state<PortfolioMedia[]>([]);
  let loading = $state(true);
  let sectionEl = $state<HTMLElement | null>(null);

  const lightbox = useLightbox();

  onMount(() => {
    let cleanups: (() => void)[] = [];

    (async () => {
      try {
        const all = await portfolioApi.galleryImages();
        galleryImages = shuffle(all).slice(0, GALLERY_IMAGE_LIMIT);
      } catch {
        galleryImages = [];
      } finally {
        loading = false;
      }
      if (sectionEl) {
        cleanups.push(revealOnScroll(sectionEl, { y: 30, duration: 0.6 }));
        cleanups.push(staggerReveal(sectionEl, '.gallery__item', { stagger: 0.06, duration: 0.4 }));
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
    const items: LightboxImage[] = galleryImages.map((img) => ({
      src: uploadUrl(img.filename),
      alt: img.alt_text ?? 'Gallery image',
    }));
    lightbox.open(items, index);
  }

  function getImageSrc(img: PortfolioMedia): string {
    return uploadUrl(img.filename);
  }
</script>

<section bind:this={sectionEl} class="section section--alt gallery">
  <div class="container">
    <SectionTitle eyebrow={t('gallery_eyebrow')} title={t('gallery_title')} description={t('gallery_description')} align="center" />

    {#if loading}
      <div class="gallery__collage">
        {#each Array(10) as _, i (i)}
          <Skeleton variant="rect" />
        {/each}
      </div>
    {:else if galleryImages.length > 0}
      <div class="gallery__collage">
        {#each galleryImages as img, i (img.id)}
          <button
            class="gallery__item"
            class:gallery__item--large={i === 0 || i === 5}
            onclick={() => openLightbox(i)}
            aria-label="View image"
          >
            <img src={getImageSrc(img)} alt={img.alt_text ?? 'Gallery image'} loading="lazy" />
          </button>
        {/each}
      </div>
    {:else}
      <EmptyState title={t('gallery_empty')} />
    {/if}
  </div>
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
  .gallery__collage {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-auto-rows: 12rem;
    gap: var(--sp-3);
    margin-top: var(--sp-8);
  }

  .gallery__item {
    border: none;
    padding: 0;
    overflow: hidden;
    border-radius: var(--radius-lg);
    cursor: pointer;
    background: var(--color-surface-alt);
    transition: opacity var(--duration-fast) var(--ease-smooth);
  }

  .gallery__item--large {
    grid-row: span 2;
    grid-column: span 2;
  }

  .gallery__item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-long) var(--ease-out);
  }

  .gallery__item:hover img {
    transform: scale(1.06);
  }

  .gallery__item:hover {
    opacity: 0.9;
  }

  @media (max-width: 768px) {
    .gallery__collage {
      grid-template-columns: repeat(2, 1fr);
      grid-auto-rows: 8rem;
    }
    .gallery__item--large {
      grid-row: span 1;
      grid-column: span 1;
    }
  }
</style>