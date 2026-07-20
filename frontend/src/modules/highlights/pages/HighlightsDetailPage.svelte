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
  import DOMPurify from 'dompurify';
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

  function getGalleryImages(): LightboxImage[] {
    if (!highlight?.gallery) return [];
    return highlight.gallery.map((img: { filename: string; alt_text: string | null }) => ({
      src: uploadUrl(img.filename),
      alt: img.alt_text ?? 'Highlights image',
    }));
  }

  function openLightbox(index: number): void {
    lightbox.open(getGalleryImages(), index);
  }

  function getYouTubeEmbed(url: string): string | null {
    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/);
    return match ? `https://www.youtube.com/embed/${match[1]}` : null;
  }

  function sanitizeContent(html: string): string {
    return DOMPurify.sanitize(html, {
      ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'u', 's', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'a', 'img', 'hr', 'code', 'pre'],
      ALLOWED_ATTR: ['href', 'src', 'alt', 'title'],
    });
  }

  function share(): void {
    if (navigator.share) {
      navigator.share({ title: highlight?.title ?? '', url: window.location.href });
    } else {
      navigator.clipboard?.writeText(window.location.href);
    }
  }
</script>

<svelte:head>
  {#if highlight}
    <title>{highlight.seo_title || highlight.title} — {t('site_name')}</title>
    <meta name="description" content={highlight.seo_description || highlight.short_description} />
    <link rel="canonical" href={`/highlights/${highlight.slug}`} />
    <meta property="og:title" content={highlight.seo_title || highlight.title} />
    <meta property="og:description" content={highlight.seo_description || highlight.short_description} />
    <meta property="og:type" content="article" />
    {#if highlight.og_media}
      <meta property="og:image" content={uploadUrl(highlight.og_media.filename)} />
    {/if}
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content={highlight.seo_title || highlight.title} />
    <meta name="twitter:description" content={highlight.seo_description || highlight.short_description} />
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
      <img src={getCoverUrl()} alt={highlight.title} class="highlights-detail__hero-img" />
      <div class="highlights-detail__hero-overlay"></div>
      <div class="container highlights-detail__hero-content">
        <Badge variant={highlight.category}>{categoryLabel(highlight.category, langStore.current)}</Badge>
        <h1 class="highlights-detail__title">{highlight.title}</h1>
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
      <p class="lead">{highlight.short_description}</p>
      <div class="rich-content">
        {@html sanitizeContent(highlight.content)}
      </div>
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

    {#if highlight.gallery && highlight.gallery.length > 0}
      <section class="section">
        <div class="container">
          <h2 class="highlights-detail__section-title">{t('highlights_detail_gallery')}</h2>
          <div class="highlights-detail__gallery">
            {#each highlight.gallery as img, i (img.id)}
              <button class="highlights-detail__gallery-item" onclick={() => openLightbox(i)} aria-label="View image">
                <img src={uploadUrl(img.filename)} alt={img.alt_text ?? 'Highlights image'} loading="lazy" />
              </button>
            {/each}
          </div>
        </div>
      </section>
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
                    alt={item.title}
                    loading="lazy"
                  />
                </div>
                <h3 class="related-card__title">{item.title}</h3>
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

  .rich-content {
    margin-top: var(--sp-5);
    font-size: var(--fs-body-lg);
    line-height: var(--lh-relaxed);
  }

  .rich-content :global(h2) { font-family: var(--font-serif); font-size: var(--fs-h3); margin: var(--sp-5) 0 var(--sp-2); }
  .rich-content :global(h3) { font-family: var(--font-serif); font-size: var(--fs-h4); margin: var(--sp-4) 0 var(--sp-2); }
  .rich-content :global(blockquote) { border-left: 3px solid var(--color-gold); padding-left: var(--sp-4); margin: var(--sp-3) 0; color: var(--color-text-muted); font-style: italic; }
  .rich-content :global(img) { max-width: 100%; border-radius: var(--radius-lg); margin: var(--sp-3) 0; }
  .rich-content :global(a) { color: var(--color-accent); text-decoration: underline; }
  .rich-content :global(ul), .rich-content :global(ol) { padding-left: var(--sp-5); margin: var(--sp-2) 0; }

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

  .highlights-detail__gallery {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--sp-4);
  }

  .highlights-detail__gallery-item {
    border: none;
    padding: 0;
    border-radius: var(--radius-lg);
    overflow: hidden;
    cursor: pointer;
    aspect-ratio: 4 / 3;
  }

  .highlights-detail__gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-medium) var(--ease-out);
  }

  .highlights-detail__gallery-item:hover img { transform: scale(1.06); }

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
    .highlights-detail__gallery { grid-template-columns: repeat(2, 1fr); }
  }

  @media (max-width: 480px) {
    .highlights-detail__related { grid-template-columns: 1fr; }
    .highlights-detail__gallery { grid-template-columns: 1fr; }
  }
</style>