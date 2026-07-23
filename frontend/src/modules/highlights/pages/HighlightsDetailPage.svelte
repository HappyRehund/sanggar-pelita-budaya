<script lang="ts">
  import { onMount } from 'svelte';
  import { router } from '$lib/router.svelte';
  import { highlightsStore } from '$lib/stores/highlights.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { formatDate, uploadUrl, imageUrl } from '$lib/utils';
  import { langStore } from '$lib/stores/lang.svelte';
  import { categoryLabel } from '$lib/constants/categories';
  import { highlightDetailPath } from '$lib/constants/routes';
  import { useLightbox } from '$lib/hooks/useLightbox.svelte';
  import type { LightboxImage } from '$lib/hooks/useLightbox.svelte';
  import Badge from '$lib/components/Badge.svelte';
  import Button from '$lib/components/Button.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import Lightbox from '$lib/components/Lightbox.svelte';
  import { Calendar, MapPin, Share2 } from '@lucide/svelte';

  let slug = $state('');
  const lightbox = useLightbox();

  onMount(() => {
    const params = router.current.params;
    slug = params.slug ?? '';
    if (slug) {
      highlightsStore.fetchBySlug(slug);
    }
  });

  const highlight = $derived(highlightsStore.current);

  function getCoverUrl(): string {
    if (highlight?.cover) return uploadUrl(highlight.cover.filename);
    return imageUrl(`highlights-${slug}`, 1200, 600);
  }

  function getCoverLightboxImages(): LightboxImage[] {
    if (!highlight?.cover) return [];
    return [{
      src: uploadUrl(highlight.cover.filename),
      alt: title(highlight),
    }];
  }

  function openCoverLightbox(): void {
    const images = getCoverLightboxImages();
    if (images.length > 0) lightbox.open(images, 0);
  }

  function getYouTubeEmbed(url: string): string | null {
    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/);
    return match ? `https://www.youtube.com/embed/${match[1]}` : null;
  }

  function share(): void {
    if (navigator.share) {
      navigator.share({ title: highlight ? title(highlight) : '', url: window.location.href });
    } else {
      navigator.clipboard?.writeText(window.location.href);
    }
  }

  function title(h: { title_en: string; title_id: string }): string {
    return langStore.current === 'id' ? h.title_id : h.title_en;
  }

  function description(h: { short_description_en: string; short_description_id: string }): string {
    return langStore.current === 'id' ? h.short_description_id : h.short_description_en;
  }

  function seoTitle(h: { seo_title_en: string | null; seo_title_id: string | null; title_en: string; title_id: string }): string {
    const localized = langStore.current === 'id' ? h.seo_title_id : h.seo_title_en;
    return localized || title(h);
  }

  function seoDescription(h: { seo_description_en: string | null; seo_description_id: string | null; short_description_en: string; short_description_id: string }): string {
    const localized = langStore.current === 'id' ? h.seo_description_id : h.seo_description_en;
    return localized || description(h);
  }
</script>

<svelte:head>
  {#if highlight}
    <title>{seoTitle(highlight)} — {t('site_name')}</title>
    <meta name="description" content={seoDescription(highlight)} />
    <link rel="canonical" href={`/highlights/${highlight.slug}`} />
    <meta property="og:title" content={seoTitle(highlight)} />
    <meta property="og:description" content={seoDescription(highlight)} />
    <meta property="og:type" content="article" />
    <meta property="og:image" content={getCoverUrl()} />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content={seoTitle(highlight)} />
    <meta name="twitter:description" content={seoDescription(highlight)} />
  {/if}
</svelte:head>

{#if highlightsStore.loading && !highlight}
  <div class="container section">
    <Skeleton variant="rect" height="400px" />
    <div style="margin-top: var(--sp-6)">
      <Skeleton variant="text" width="60%" />
      <Skeleton variant="text" width="40%" />
    </div>
  </div>
{:else if highlight}
  <article class="highlights-detail">
    <div class="highlights-detail__hero">
      <button class="highlights-detail__hero-btn" onclick={openCoverLightbox} aria-label={t('highlights_detail_view_image')}>
        <img src={getCoverUrl()} alt={title(highlight)} class="highlights-detail__hero-img" />
      </button>
      <div class="highlights-detail__hero-overlay"></div>
      <div class="container highlights-detail__hero-content">
        <Badge variant={highlight.category}>{categoryLabel(highlight.category, langStore.current)}</Badge>
        <h1 class="highlights-detail__title">{title(highlight)}</h1>
        <div class="highlights-detail__meta">
          {#if highlight.event_date}
            <span class="highlights-detail__meta-item">
              <Calendar size={16} />
              {formatDate(highlight.event_date, langStore.current)}
            </span>
          {/if}
          {#if highlight.location}
            <span class="highlights-detail__meta-item">
              <MapPin size={16} />
              {highlight.location}
            </span>
          {/if}
          <button class="highlights-detail__share" onclick={share} aria-label={t('highlights_detail_share')}>
            <Share2 size={16} />
            {t('highlights_detail_share')}
          </button>
        </div>
      </div>
    </div>

    <div class="container container--reading highlights-detail__content">
      <p class="lead">{description(highlight)}</p>
    </div>

    {#if highlight.youtube_url}
      {@const embed = getYouTubeEmbed(highlight.youtube_url)}
      {#if embed}
        <div class="container highlights-detail__video">
          <div class="highlights-detail__video-wrap">
            <iframe src={embed} title="YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
          </div>
        </div>
      {/if}
    {/if}

    {#if highlight.related && highlight.related.length > 0}
      <section class="section section--alt">
        <div class="container">
          <h2 class="highlights-detail__section-title">{t('highlights_detail_related')}</h2>
          <div class="highlights-detail__related">
            {#each highlight.related as item (item.id)}
              <a href={highlightDetailPath(item.slug)} class="related-card">
                <div class="related-card__image">
                  <img
                    src={item.cover ? uploadUrl(item.cover.filename) : imageUrl(`highlights-${item.slug}`, 400, 300)}
                    alt={title(item)}
                    loading="lazy"
                  />
                </div>
                <h3 class="related-card__title">{title(item)}</h3>
                <Badge variant={item.category}>{categoryLabel(item.category, langStore.current)}</Badge>
              </a>
            {/each}
          </div>
        </div>
      </section>
    {/if}
  </article>

  <Lightbox
    open={lightbox.isOpen}
    images={lightbox.images}
    index={lightbox.currentIndex}
    onclose={lightbox.close}
    onnext={lightbox.next}
    onprev={lightbox.prev}
  />
{:else}
  <div class="container section">
    <EmptyState title={t('highlights_detail_not_found')}>
      <Button variant="primary" size="md" href="/highlights">{t('back')}</Button>
    </EmptyState>
  </div>
{/if}

<style>
  .highlights-detail__hero {
    position: relative;
    height: 60vh;
    min-height: 24rem;
    overflow: hidden;
  }

  .highlights-detail__hero-btn {
    display: block;
    width: 100%;
    height: 100%;
    padding: 0;
    border: none;
    background: none;
    cursor: pointer;
  }

  .highlights-detail__hero-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .highlights-detail__hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(26, 22, 18, 0.85), rgba(26, 22, 18, 0.3) 60%, transparent);
  }

  .highlights-detail__hero-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding-bottom: var(--sp-8);
    display: flex;
    flex-direction: column;
    gap: var(--sp-3);
  }

  .highlights-detail__title {
    font-size: var(--fs-h1);
    font-weight: var(--fw-semibold);
    color: var(--color-ivory);
    max-width: 36rem;
  }

  .highlights-detail__meta {
    display: flex;
    align-items: center;
    gap: var(--sp-5);
    color: var(--color-beige);
    font-size: var(--fs-body-sm);
    flex-wrap: wrap;
  }

  .highlights-detail__meta-item {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
  }

  .highlights-detail__share {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    color: var(--color-gold-soft);
    margin-left: auto;
  }

  .highlights-detail__content {
    padding: var(--sp-10) var(--sp-5);
  }

  .highlights-detail__video {
    margin-bottom: var(--sp-10);
  }

  .highlights-detail__video-wrap {
    position: relative;
    aspect-ratio: 16 / 9;
    border-radius: var(--radius-2xl);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
  }

  .highlights-detail__video-wrap iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
  }

  .highlights-detail__section-title {
    font-family: var(--font-serif);
    font-size: var(--fs-h2);
    font-weight: var(--fw-semibold);
    margin-bottom: var(--sp-6);
  }

  .highlights-detail__related {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--sp-4);
  }

  .related-card {
    display: flex;
    flex-direction: column;
    gap: var(--sp-2);
  }

  .related-card__image {
    border-radius: var(--radius-lg);
    overflow: hidden;
    aspect-ratio: 4 / 3;
  }

  .related-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-medium) var(--ease-out);
  }

  .related-card:hover .related-card__image img { transform: scale(1.06); }

  .related-card__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-medium);
  }

  @media (max-width: 880px) {
    .highlights-detail__related { grid-template-columns: repeat(2, 1fr); }
  }

  @media (max-width: 480px) {
    .highlights-detail__related { grid-template-columns: 1fr; }
  }
</style>