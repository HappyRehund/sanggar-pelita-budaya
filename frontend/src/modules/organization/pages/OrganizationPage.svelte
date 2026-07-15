<script lang="ts">
  import { onMount } from 'svelte';
  import { organizationStore } from '$lib/stores/organization.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { uploadUrl, imageUrl } from '$lib/utils';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import type { OrganizationMember } from '$lib/types';

  onMount(() => {
    organizationStore.fetchTree();
  });

  function getPhoto(member: OrganizationMember): string {
    return member.photo ? uploadUrl(member.photo) : imageUrl(`member-${member.id}`, 400, 400);
  }
</script>

<svelte:head>
  <title>{t('org_page_hero_title')} — {t('site_name')}</title>
  <meta name="description" content={t('org_page_hero_desc')} />
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
    {#if organizationStore.loading && organizationStore.tree.length === 0}
      <div class="org-loading">
        {#each [0, 1, 2] as i (i)}
          <Skeleton variant="rect" width="300px" height="400px" />
        {/each}
      </div>
    {:else if organizationStore.tree.length > 0}
      <div class="org-tree">
        {#each organizationStore.tree as member (member.id)}
          <div class="org-node">
            <div class="org-node__card">
              <div class="org-node__photo">
                <img src={getPhoto(member)} alt={member.name} loading="lazy" />
              </div>
              <h3 class="org-node__name">{member.name}</h3>
              <p class="org-node__position">{member.position}</p>
              {#if member.biography}
                <p class="org-node__bio">{member.biography}</p>
              {/if}
            </div>
            {#if member.children && member.children.length > 0}
              <div class="org-node__children">
                {#each member.children as child (child.id)}
                  <div class="org-node__card org-node__card--child">
                    <div class="org-node__photo org-node__photo--small">
                      <img src={getPhoto(child)} alt={child.name} loading="lazy" />
                    </div>
                    <h4 class="org-node__name">{child.name}</h4>
                    <p class="org-node__position">{child.position}</p>
                  </div>
                {/each}
              </div>
            {/if}
          </div>
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

  .org-loading {
    display: flex;
    gap: var(--sp-5);
    justify-content: center;
    flex-wrap: wrap;
  }

  .org-tree {
    display: flex;
    flex-direction: column;
    gap: var(--sp-8);
    align-items: center;
  }

  .org-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-6);
  }

  .org-node__card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--sp-2);
    padding: var(--sp-6);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    transition: box-shadow var(--duration-short) var(--ease-smooth), transform var(--duration-short) var(--ease-out);
    min-width: 16rem;
  }

  .org-node__card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-4px);
  }

  .org-node__photo {
    width: 6rem;
    height: 6rem;
    border-radius: var(--radius-full);
    overflow: hidden;
    border: 3px solid var(--color-gold);
    margin-bottom: var(--sp-2);
  }

  .org-node__photo--small {
    width: 4rem;
    height: 4rem;
    border-width: 2px;
  }

  .org-node__photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .org-node__name {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
  }

  .org-node__position {
    font-size: var(--fs-body-sm);
    color: var(--color-gold-dark);
    font-weight: var(--fw-medium);
  }

  .org-node__bio {
    font-size: var(--fs-body-sm);
    color: var(--color-text-muted);
    line-height: var(--lh-relaxed);
    max-width: 20rem;
  }

  .org-node__children {
    display: flex;
    gap: var(--sp-4);
    flex-wrap: wrap;
    justify-content: center;
    border-top: 1px solid var(--color-border);
    padding-top: var(--sp-6);
  }

  .org-node__card--child {
    min-width: 12rem;
    padding: var(--sp-4);
    box-shadow: none;
    border: 1px solid var(--color-border);
  }

  @media (max-width: 768px) {
    .org-node__children { flex-direction: column; align-items: center; }
    .org-node__card { min-width: 100%; max-width: 24rem; }
  }
</style>