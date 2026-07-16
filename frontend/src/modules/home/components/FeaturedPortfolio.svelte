<script lang="ts">
  import { onMount } from 'svelte';
  import { portfolioStore } from '$lib/stores/portfolio.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { formatDate, uploadUrl, imageUrl, revealOnScroll, staggerReveal } from '$lib/utils';
  import { langStore } from '$lib/stores/lang.svelte';
  import { categoryLabel } from '$lib/constants/categories';
  import { portfolioDetailPath } from '$lib/constants/routes';
  import { FEATURED_PORTFOLIO_LIMIT } from '$lib/constants/uploadLimits';
  import Badge from '$lib/components/Badge.svelte';
  import Button from '$lib/components/Button.svelte';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import { ArrowRight } from '@lucide/svelte';

  let sectionEl = $state<HTMLElement | null>(null);

  onMount(() => {
    portfolioStore.fetchFeatured(FEATURED_PORTFOLIO_LIMIT);
    if (sectionEl) {
      const c1 = revealOnScroll(sectionEl, { y: 30, duration: 0.6 });
      const c2 = staggerReveal(sectionEl, '.portfolio-card', { stagger: 0.08, duration: 0.5 });
      return () => { c1(); c2(); };
    }
  });

  function getCover(item: { cover?: { filename: string } | null; slug: string }): string {
    return item.cover ? uploadUrl(item.cover.filename) : imageUrl(`portfolio-${item.slug}`, 600, 400);
  }
</script>

<section bind:this={sectionEl} class="section featured-portfolio">
  <div class="container">
    <SectionTitle eyebrow={t('featured_eyebrow')} title={t('featured_title')} description={t('featured_description')} align="center" />

    {#if portfolioStore.loading && portfolioStore.featured.length === 0}
      <div class="featured-portfolio__grid">
        {#each Array(6) as _, i (i)}
          <Skeleton variant="card" />
        {/each}
      </div>
    {:else if portfolioStore.featured.length > 0}
      <div class="featured-portfolio__grid">
        {#each portfolioStore.featured as item (item.id)}
          <a href={portfolioDetailPath(item.slug)} class="portfolio-card" onclick={(e) => { e.preventDefault(); }}>
            <div class="portfolio-card__image">
              <img src={getCover(item)} alt={item.title} loading="lazy" />
              <div class="portfolio-card__overlay"></div>
              <Badge variant={item.category}>{categoryLabel(item.category, langStore.current)}</Badge>
            </div>
            <div class="portfolio-card__body">
              <h3 class="portfolio-card__title">{item.title}</h3>
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
      <div class="featured-portfolio__cta">
        <Button variant="secondary" size="md" href="/portfolio">
          {t('featured_view_all')}
          <ArrowRight size={16} />
        </Button>
      </div>
    {:else}
      <EmptyState title={t('portfolio_empty')} description={t('portfolio_empty_search')} />
    {/if}
  </div>
</section>

<style>
  .featured-portfolio__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--sp-5);
    margin-top: var(--sp-10);
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

  .portfolio-card:hover .portfolio-card__image img {
    transform: scale(1.08);
  }

  .portfolio-card__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(26, 22, 18, 0.6), transparent 50%);
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
    color: var(--color-brown);
    margin-bottom: var(--sp-1);
  }

  .portfolio-card__meta {
    font-size: var(--fs-caption);
    color: var(--color-text-muted);
  }

  .featured-portfolio__cta {
    display: flex;
    justify-content: center;
    margin-top: var(--sp-8);
  }

  @media (max-width: 880px) {
    .featured-portfolio__grid { grid-template-columns: repeat(2, 1fr); }
  }

  @media (max-width: 560px) {
    .featured-portfolio__grid { grid-template-columns: 1fr; }
  }
</style>