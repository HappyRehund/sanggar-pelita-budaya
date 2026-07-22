<script lang="ts">
  import { onMount } from 'svelte';
  import { organizationStore } from '$lib/stores/organization.svelte';
  import { t, i18n } from '$lib/i18n/index.svelte';
  import { uploadUrl, imageUrl, revealOnScroll, staggerReveal, prefersReducedMotion } from '$lib/utils';
  import Button from '$lib/components/Button.svelte';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import type { OrganizationMember } from '$lib/types';

  let sectionEl = $state<HTMLElement | null>(null);
  let activeIndex = $state(0);
  let hoveredIndex = $state<number | null>(null);

  onMount(() => {
    organizationStore.fetchFeatured();
    if (sectionEl) {
      const c1 = revealOnScroll(sectionEl, { y: 30, duration: 0.6 });
      const c2 = staggerReveal(sectionEl, '.org-card', { stagger: 0.12, duration: 0.5 });
      return () => { c1(); c2(); };
    }
  });

  let intervalId: ReturnType<typeof setInterval> | null = null;

  $effect(() => {
    const members = organizationStore.featured;
    if (members.length === 0 || prefersReducedMotion()) {
      if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
      }
      return;
    }
    intervalId = setInterval(() => {
      activeIndex = (activeIndex + 1) % members.length;
    }, 2500);
    return () => {
      if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
      }
    };
  });

  function isActive(i: number): boolean {
    return hoveredIndex === i || (hoveredIndex === null && activeIndex === i);
  }

  function onMouseEnter(i: number): void {
    hoveredIndex = i;
  }

  function onMouseLeave(): void {
    hoveredIndex = null;
  }

  function getPhoto(member: OrganizationMember): string {
    return member.photo ? uploadUrl(member.photo) : imageUrl(`member-${member.id}`, 400, 400);
  }

  function position(member: OrganizationMember): string {
    return i18n.current === 'id' ? member.position_id : member.position_en;
  }
</script>

<section bind:this={sectionEl} class="section org-preview">
  <div class="container">
    <SectionTitle eyebrow={t('org_eyebrow')} title={t('org_title')} description={t('org_description')} align="center" />

    {#if organizationStore.loading}
      <div class="org-preview__grid">
        {#each [0, 1, 2, 3] as i (i)}
          <div class="org-preview__skeleton"></div>
        {/each}
      </div>
    {:else if organizationStore.featured.length > 0}
      <div class="org-preview__grid">
        {#each organizationStore.featured as member, i (member.id)}
          <div
            class="org-card"
            class:is-active={isActive(i)}
            onmouseenter={() => onMouseEnter(i)}
            onmouseleave={onMouseLeave}
            role="img"
            aria-label={member.name}
          >
            <div class="org-card__photo">
              <img
                src={getPhoto(member)}
                alt={member.name}
                loading="lazy"
              />
              <div class="org-card__overlay"></div>
              <div class="org-card__caption">
                <h3 class="org-card__name">{member.name}</h3>
                <p class="org-card__position">{position(member)}</p>
              </div>
            </div>
          </div>
        {/each}
      </div>
      <div class="org-preview__cta">
        <Button variant="soft-ink" size="md" href="/organization">
          {t('org_view_all')}
        </Button>
      </div>
    {:else}
      <p class="org-preview__empty">{t('empty_organization')}</p>
    {/if}
  </div>
</section>

<style>
  .org-preview__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--sp-5);
    margin-top: var(--sp-10);
  }

  .org-card {
    cursor: pointer;
  }

  .org-card__photo {
    position: relative;
    width: 100%;
    aspect-ratio: 3 / 4;
    overflow: hidden;
    /* border-radius: var(--radius-lg); */
    box-shadow: var(--shadow-md);
    transition: transform var(--duration-medium) var(--ease-out), box-shadow var(--duration-medium) var(--ease-out);
  }

  .org-card.is-active .org-card__photo {
    transform: scale(1.09);
    box-shadow: var(--shadow-lg);
  }

  .org-card__photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-long) var(--ease-smooth);
  }

  .org-card.is-active .org-card__photo img {
    transform: scale(1.08);
  }

  .org-card__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, var(--color-ink) 0%, rgba(36, 0, 3, 0.6) 25%, transparent 55%);
    pointer-events: none;
  }

  .org-card__caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: var(--sp-4);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    pointer-events: none;
  }

  .org-card__name {
    color: var(--color-ivory);
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
    line-height: var(--lh-tight);
  }

  .org-card__position {
    color: var(--color-gold);
    font-family: var(--font-script);
    font-size: var(--fs-body-md);
    font-weight: var(--fw-medium);
    letter-spacing: var(--tracking-wide);
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    margin-top: 0;
    transition:
      max-height var(--duration-short) var(--ease-out),
      opacity var(--duration-short) var(--ease-out),
      margin-top var(--duration-short) var(--ease-out);
  }

  .org-card.is-active .org-card__position {
    max-height: 2.5rem;
    opacity: 1;
    margin-top: var(--sp-1);
  }

  .org-preview__cta {
    display: flex;
    justify-content: center;
    margin-top: var(--sp-10);
  }

  .org-preview__skeleton {
    height: 18rem;
    border-radius: var(--radius-lg);
    background: linear-gradient(90deg, var(--color-gray-100) 25%, var(--color-gray-200) 50%, var(--color-gray-100) 75%);
    background-size: 200% 100%;
    animation: shimmer 1.6s var(--ease-smooth) infinite;
  }

  .org-preview__empty {
    text-align: center;
    color: var(--color-text-muted);
    padding: var(--sp-8);
  }

  @keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }

  @media (max-width: 900px) {
    .org-preview__grid {
      grid-template-columns: repeat(2, 1fr);
      gap: var(--sp-4);
    }
  }

  @media (max-width: 480px) {
    .org-preview__grid {
      grid-template-columns: 1fr;
      gap: var(--sp-5);
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .org-card__photo,
    .org-card__photo img,
    .org-card__position {
      transition: none;
    }
  }
</style>