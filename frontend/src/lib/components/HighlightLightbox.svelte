<script lang="ts">
  import { X, Play, ChevronDown, ChevronUp } from '@lucide/svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { langStore } from '$lib/stores/lang.svelte';
  import { categoryLabel } from '$lib/constants/categories';
  import { formatDate, uploadUrl, imageUrl } from '$lib/utils';
  import type { HighlightListSummary } from '$lib/types';

  interface Props {
    open: boolean;
    item: HighlightListSummary | null;
    onclose: () => void;
  }

  let { open, item, onclose }: Props = $props();

  let expanded = $state(false);
  let descEl = $state<HTMLParagraphElement | null>(null);
  let isClampable = $state(false);

  const lang = $derived(langStore.current);

  const title = $derived(item ? (lang === 'id' ? item.title_id : item.title_en) : '');
  const description = $derived(
    item ? (lang === 'id' ? item.short_description_id : item.short_description_en) : ''
  );
  const categoryText = $derived(item ? categoryLabel(item.category, lang) : '');
  const coverSrc = $derived(
    item?.cover
      ? uploadUrl(item.cover.filename)
      : item
        ? imageUrl(`highlights-${item.slug}`, 800, 1000)
        : ''
  );
  const hasYoutube = $derived(!!item?.youtube_url);
  const hasEventDate = $derived(!!item?.event_date);
  const hasLocation = $derived(!!item?.location);

  // Detect whether the description actually overflows the clamp so we
  // only show "Read More" when there's something to expand. DOM layout
  // measurement must run after the DOM updates, hence $effect + rAF.
  $effect(() => {
    if (!open || !descEl) {
      isClampable = false;
      return;
    }
    const raf = requestAnimationFrame(() => {
      if (!descEl) return;
      isClampable = descEl.scrollHeight > descEl.clientHeight + 2;
    });
    return () => cancelAnimationFrame(raf);
  });

  // Reset to collapsed state whenever a new item opens.
  $effect(() => {
    if (open) {
      const id = item?.id;
      void id;
      expanded = false;
    }
  });

  function handleKeydown(e: KeyboardEvent): void {
    if (e.key === 'Escape' && open) onclose();
  }

  function handleOverlayClick(e: MouseEvent): void {
    if (e.target === e.currentTarget) onclose();
  }

  function toggleExpand(): void {
    expanded = !expanded;
  }
</script>

<svelte:window onkeydown={handleKeydown} />

{#if open && item}
  <div
    class="hl-lightbox"
    role="dialog"
    aria-modal="true"
    tabindex="-1"
    aria-label={title}
    onclick={handleOverlayClick}
    onkeydown={handleKeydown}
  >
    <button class="hl-lightbox__close" onclick={onclose} aria-label={t('close')}>
      <X size={24} />
    </button>

    <article class="hl-lightbox__card">
      <div class="hl-lightbox__media">
        <img src={coverSrc} alt={title} />
      </div>

      <div class="hl-lightbox__panel">
        <div class="hl-lightbox__panel-inner">
          <span class="hl-lightbox__category">{categoryText}</span>

          <h2 class="hl-lightbox__title">{title}</h2>

          <p
            bind:this={descEl}
            class="hl-lightbox__desc"
            class:hl-lightbox__desc--expanded={expanded}
          >
            {description}
          </p>

          {#if isClampable}
            <button class="hl-lightbox__toggle" onclick={toggleExpand}>
              {expanded ? t('read_less') : t('read_more')}
              {#if expanded}
                <ChevronUp size={16} />
              {:else}
                <ChevronDown size={16} />
              {/if}
            </button>
          {/if}

          <div class="hl-lightbox__separator"></div>

          <dl class="hl-lightbox__info">
            <div class="hl-lightbox__info-row">
              <dt>{t('highlights_category_label')}</dt>
              <dd>{categoryText}</dd>
            </div>
            <div class="hl-lightbox__info-row">
              <dt>{t('highlights_uploaded_at')}</dt>
              <dd>{formatDate(item.created_at, lang, 'long')}</dd>
            </div>
            {#if hasEventDate}
              <div class="hl-lightbox__info-row">
                <dt>{t('highlights_event_date_label')}</dt>
                <dd>{formatDate(item.event_date, lang, 'long')}</dd>
              </div>
            {/if}
            {#if hasLocation}
              <div class="hl-lightbox__info-row">
                <dt>{t('highlights_location_label')}</dt>
                <dd>{item.location}</dd>
              </div>
            {/if}
          </dl>

          {#if hasYoutube}
            <a
              href={item.youtube_url!}
              target="_blank"
              rel="noopener noreferrer"
              class="hl-lightbox__youtube"
            >
              <Play size={18} fill="currentColor" />
              {t('highlights_watch_performance')}
            </a>
          {/if}
        </div>
      </div>
    </article>
  </div>
{/if}

<style>
  .hl-lightbox {
    position: fixed;
    inset: 0;
    z-index: var(--z-lightbox);
    background: rgba(26, 22, 18, 0.92);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--sp-4);
    animation: hl-fade-in var(--duration-short) var(--ease-smooth);
  }

  .hl-lightbox__close {
    position: absolute;
    top: var(--sp-4);
    right: var(--sp-4);
    z-index: 1;
    color: var(--color-white);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: var(--radius-full);
    transition: background-color var(--duration-fast) var(--ease-smooth);
  }
  .hl-lightbox__close:hover {
    background-color: rgba(255, 255, 255, 0.15);
  }

  /* Fixed-size two-column card. Stays a fixed size even when content grows;
     the right panel scrolls internally when the description is expanded. */
  .hl-lightbox__card {
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    width: 100%;
    max-width: 64rem;
    max-height: calc(100vh - var(--sp-8));
    background: var(--color-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    animation: hl-card-in var(--duration-medium) var(--ease-out);
  }

  .hl-lightbox__media {
    position: relative;
    overflow: hidden;
    background: var(--color-surface-alt);
    min-height: 0;
  }
  .hl-lightbox__media img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  /* Right content panel — scrolls internally; the card itself stays fixed. */
  .hl-lightbox__panel {
    min-height: 0;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
  }
  .hl-lightbox__panel-inner {
    padding: var(--sp-6) var(--sp-6) var(--sp-6);
    display: flex;
    flex-direction: column;
    gap: var(--sp-3);
  }

  .hl-lightbox__category {
    font-size: var(--fs-caption);
    font-weight: var(--fw-semibold);
    text-transform: uppercase;
    letter-spacing: var(--tracking-widest);
    color: var(--color-accent);
  }

  .hl-lightbox__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h3);
    font-weight: var(--fw-semibold);
    line-height: var(--lh-tight);
    margin: 0;
  }

  /* Collapsed: 5-line clamp. Expanded: full text, panel scrolls. */
  .hl-lightbox__desc {
    font-size: var(--fs-body);
    line-height: var(--lh-relaxed);
    color: var(--color-text);
    margin: 0;
  }
  .hl-lightbox__desc:not(.hl-lightbox__desc--expanded) {
    display: -webkit-box;
    -webkit-line-clamp: 5;
    line-clamp: 5;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .hl-lightbox__toggle {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-1);
    align-self: flex-start;
    background: none;
    border: none;
    padding: 0;
    color: var(--color-accent);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    cursor: pointer;
    transition: color var(--duration-fast) var(--ease-smooth);
  }
  .hl-lightbox__toggle:hover {
    text-decoration: underline;
  }

  .hl-lightbox__separator {
    height: 1px;
    background: var(--color-border);
    margin: var(--sp-2) 0;
  }

  .hl-lightbox__info {
    display: flex;
    flex-direction: column;
    gap: var(--sp-3);
    margin: 0;
  }
  .hl-lightbox__info-row {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .hl-lightbox__info-row dt {
    font-size: var(--fs-caption);
    font-weight: var(--fw-semibold);
    text-transform: uppercase;
    letter-spacing: var(--tracking-wide);
    color: var(--color-text-subtle);
  }
  .hl-lightbox__info-row dd {
    margin: 0;
    font-size: var(--fs-body-sm);
    color: var(--color-text);
  }

  .hl-lightbox__youtube {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    align-self: flex-start;
    margin-top: var(--sp-2);
    padding: var(--sp-2) var(--sp-4);
    border-radius: var(--radius-md);
    background: #ff0000;
    color: var(--color-white);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    text-decoration: none;
    transition: background-color var(--duration-fast) var(--ease-smooth);
  }
  .hl-lightbox__youtube:hover {
    background: #cc0000;
  }

  @keyframes hl-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  @keyframes hl-card-in {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }

  @media (prefers-reduced-motion: reduce) {
    .hl-lightbox,
    .hl-lightbox__card { animation: none; }
  }

  /* Keep two-column on mobile, but tighten spacing and balance columns. */
  @media (max-width: 640px) {
    .hl-lightbox { padding: var(--sp-2); }
    .hl-lightbox__card {
      max-width: 100%;
      grid-template-columns: 1fr 1fr;
      max-height: calc(100vh - var(--sp-4));
    }
    .hl-lightbox__panel-inner {
      padding: var(--sp-4);
      gap: var(--sp-2);
    }
    .hl-lightbox__title { font-size: var(--fs-h4); }
    .hl-lightbox__desc { font-size: var(--fs-body-sm); }
    .hl-lightbox__desc:not(.hl-lightbox__desc--expanded) {
      -webkit-line-clamp: 4;
      line-clamp: 4;
    }
    .hl-lightbox__info { gap: var(--sp-2); }
  }
</style>