<script lang="ts">
  import { onMount } from 'svelte';
  import { organizationStore } from '$lib/stores/organization.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { uploadUrl, imageUrl, revealOnScroll, staggerReveal } from '$lib/utils';
  import Button from '$lib/components/Button.svelte';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import { ArrowRight } from '@lucide/svelte';
  import type { OrganizationMember } from '$lib/types';

  let sectionEl = $state<HTMLElement | null>(null);

  onMount(() => {
    organizationStore.fetchList();
    if (sectionEl) {
      const c1 = revealOnScroll(sectionEl, { y: 30, duration: 0.6 });
      const c2 = staggerReveal(sectionEl, '.org-card', { stagger: 0.12, duration: 0.5 });
      return () => { c1(); c2(); };
    }
  });

  const previewPositions = ['Chairperson', 'Ketua', 'Secretary', 'Sekretaris', 'Treasurer', 'Bendahara'];

  const previewMembers = $derived(
    (organizationStore.members || [])
      .filter((m: OrganizationMember) => m.published && previewPositions.some((p) => m.position.toLowerCase().includes(p.toLowerCase())))
      .slice(0, 3)
  );
</script>

<section bind:this={sectionEl} class="section org-preview">
  <div class="container">
    <SectionTitle eyebrow={t('org_eyebrow')} title={t('org_title')} description={t('org_description')} align="center" />

    {#if organizationStore.loading}
      <div class="org-preview__grid">
        {#each [0, 1, 2] as i (i)}
          <div class="org-preview__skeleton"></div>
        {/each}
      </div>
    {:else if previewMembers.length > 0}
      <div class="org-preview__grid">
        {#each previewMembers as member (member.id)}
          <div class="org-card">
            <div class="org-card__photo">
              <img
                src={member.photo ? uploadUrl(member.photo) : imageUrl(`member-${member.id}`, 400, 400)}
                alt={member.name}
                loading="lazy"
              />
            </div>
            <h3 class="org-card__name">{member.name}</h3>
            <p class="org-card__position">{member.position}</p>
          </div>
        {/each}
      </div>
      <div class="org-preview__cta">
        <Button variant="secondary" size="md" href="/organization">
          {t('org_view_all')}
          <ArrowRight size={16} />
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
    grid-template-columns: repeat(3, 1fr);
    gap: var(--sp-6);
    margin-top: var(--sp-10);
  }

  .org-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--sp-2);
  }

  .org-card__photo {
    width: 8rem;
    height: 8rem;
    border-radius: var(--radius-full);
    overflow: hidden;
    border: 3px solid var(--color-gold);
    box-shadow: var(--shadow-md);
    margin-bottom: var(--sp-3);
    transition: transform var(--duration-short) var(--ease-out);
  }

  .org-card__photo:hover {
    transform: scale(1.05);
  }

  .org-card__photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .org-card__name {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
  }

  .org-card__position {
    font-size: var(--fs-body-sm);
    color: var(--color-gold-dark);
    font-weight: var(--fw-medium);
    letter-spacing: var(--tracking-wide);
  }

  .org-preview__cta {
    display: flex;
    justify-content: center;
    margin-top: var(--sp-8);
  }

  .org-preview__skeleton {
    height: 14rem;
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

  @media (max-width: 768px) {
    .org-preview__grid {
      grid-template-columns: 1fr;
      gap: var(--sp-5);
    }
  }
</style>