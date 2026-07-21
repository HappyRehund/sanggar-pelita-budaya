<script lang="ts">
  import { onMount } from 'svelte';
  import { organizationStore } from '$lib/stores/organization.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { uploadUrl, imageUrl, revealOnScroll, staggerReveal } from '$lib/utils';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import type { OrganizationMember } from '$lib/types';

  let sectionEl = $state<HTMLElement | null>(null);

  onMount(() => {
    organizationStore.fetchList();
    if (sectionEl) {
      const c1 = revealOnScroll(sectionEl, { y: 30, duration: 0.6 });
      const c2 = staggerReveal(sectionEl, '.org-card', { stagger: 0.1, duration: 0.5 });
      return () => { c1(); c2(); };
    }
  });

  function getPhoto(member: OrganizationMember): string {
    return member.photo ? uploadUrl(member.photo) : imageUrl(`member-${member.id}`, 400, 400);
  }
</script>

<svelte:head>
  <title>{t('org_page_hero_title')} — {t('site_name')}</title>
  <meta name="description" content={t('org_page_hero_desc')} />
  <link rel="canonical" href="/organization" />
  <meta property="og:title" content={t('org_page_hero_title')} />
  <meta property="og:description" content={t('org_page_hero_desc')} />
  <meta property="og:type" content="website" />
  <meta name="twitter:card" content="summary" />
  <meta name="twitter:title" content={t('org_page_hero_title')} />
</svelte:head>

<section class="org-hero">
  <div class="container org-hero__inner">
    <span class="eyebrow eyebrow--centered">{t('org_eyebrow')}</span>
    <h1>{t('org_page_hero_title')}</h1>
    <p class="text-pretty">{t('org_page_hero_desc')}</p>
  </div>
</section>

<section class="section">
  <div class="container">
    {#if organizationStore.loading && organizationStore.members.length === 0}
      <div class="org-grid">
        {#each [0, 1, 2, 3, 4, 5] as i (i)}
          <div class="org-card org-card--{i % 4 === 0 || i % 4 === 3 ? 'lg' : 'sm'}">
            <Skeleton variant="rect" width="100%" height={i % 4 === 0 || i % 4 === 3 ? '420px' : '200px'} />
          </div>
        {/each}
      </div>
    {:else if organizationStore.members.length > 0}
      <div bind:this={sectionEl} class="org-grid">
        {#each organizationStore.members as member, i (member.id)}
          <article class="org-card org-card--{i % 4 === 0 || i % 4 === 3 ? 'lg' : 'sm'}">
            <div class="org-card__photo">
              <img src={getPhoto(member)} alt={member.name} loading="lazy" />
            </div>
            <div class="org-card__body">
              <h3 class="org-card__name">{member.name}</h3>
              <p class="org-card__position">{member.position}</p>
              {#if member.biography}
                <p class="org-card__bio text-pretty">{member.biography}</p>
              {/if}
            </div>
          </article>
        {/each}
      </div>
    {:else}
      <EmptyState title={t('empty_organization')} />
    {/if}
  </div>
</section>

<style>
  .org-hero {
    padding: var(--sp-16) 0 var(--sp-12);
    text-align: center;
    background: var(--color-surface-alt);
  }

  .org-hero__inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-4);
  }

  .org-hero h1 {
    font-size: var(--fs-h1);
    font-weight: var(--fw-semibold);
  }

  .org-hero p {
    font-size: var(--fs-body-lg);
    color: var(--color-text-muted);
    max-width: 32rem;
  }

  /* Checkerboard grid: 4 columns, dense flow, alternating spans */
  .org-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-auto-rows: 200px;
    grid-auto-flow: dense;
    gap: var(--sp-4);
  }

  /* Large cards span 2x2 — creates the checkerboard rhythm */
  .org-card--lg {
    grid-column: span 2;
    grid-row: span 2;
  }

  /* Small cards span 1x1 */
  .org-card--sm {
    grid-column: span 1;
    grid-row: span 1;
  }

  .org-card {
    display: flex;
    flex-direction: column;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition:
      box-shadow var(--duration-short) var(--ease-smooth),
      transform var(--duration-short) var(--ease-out);
  }

  .org-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-4px);
  }

  .org-card__photo {
    flex-shrink: 0;
    overflow: hidden;
    background: var(--color-cream);
  }

  /* Large card: photo takes top portion, body fills rest */
  .org-card--lg .org-card__photo {
    height: 60%;
  }

  /* Small card: photo is a square thumbnail only */
  .org-card--sm .org-card__photo {
    height: 120px;
  }

  .org-card__photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-medium) var(--ease-smooth);
  }

  .org-card:hover .org-card__photo img {
    transform: scale(1.04);
  }

  .org-card__body {
    display: flex;
    flex-direction: column;
    gap: var(--sp-1);
    padding: var(--sp-4);
    flex: 1;
  }

  .org-card--lg .org-card__body {
    padding: var(--sp-5);
    gap: var(--sp-2);
  }

  .org-card__name {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
    line-height: var(--lh-tight);
  }

  .org-card--sm .org-card__name {
    font-size: var(--fs-body-md);
  }

  .org-card__position {
    font-size: var(--fs-body-sm);
    color: var(--color-gold-dark);
    font-weight: var(--fw-medium);
    letter-spacing: var(--tracking-wide);
  }

  .org-card--sm .org-card__position {
    font-size: var(--fs-caption);
  }

  .org-card__bio {
    font-size: var(--fs-body-sm);
    color: var(--color-text-muted);
    line-height: var(--lh-relaxed);
    margin-top: var(--sp-1);
  }

  /* Tablet: 2 columns, large cards still span 2x2 */
  @media (max-width: 900px) {
    .org-grid {
      grid-template-columns: repeat(2, 1fr);
      grid-auto-rows: 180px;
    }
  }

  /* Mobile: single column, uniform height, no checkerboard */
  @media (max-width: 600px) {
    .org-grid {
      grid-template-columns: 1fr;
      grid-auto-rows: auto;
    }

    .org-card--lg,
    .org-card--sm {
      grid-column: span 1;
      grid-row: span 1;
    }

    .org-card--lg .org-card__photo,
    .org-card--sm .org-card__photo {
      height: 240px;
    }
  }
</style>