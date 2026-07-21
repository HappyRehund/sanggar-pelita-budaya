<script lang="ts">
  import type { Snippet } from 'svelte';

  interface Props {
    eyebrow?: string;
    title: string;
    description?: string;
    align?: 'left' | 'center';
    variant?: 'default' | 'light';
    children?: Snippet;
  }

  let {
    eyebrow,
    title,
    description,
    align = 'left',
    variant = 'default',
    children,
  }: Props = $props();
</script>

<div class="section-title section-title--{align} section-title--{variant}">
  {#if eyebrow}
    <span class="eyebrow" class:eyebrow--centered={align === 'center'}>
      {eyebrow}
    </span>
  {/if}
  <h2 class="section-title__heading">{title}</h2>
  {#if description}
    <p class="section-title__desc text-pretty">{description}</p>
  {/if}
  {#if children}
    {@render children()}
  {/if}
</div>

<style>
  .section-title {
    display: flex;
    flex-direction: column;
    gap: var(--sp-3);
  }
  .section-title--center {
    align-items: center;
    text-align: center;
  }

  .section-title__heading {
    font-size: var(--fs-h2);
    line-height: var(--lh-tight);
    max-width: 36rem;
  }
  .section-title--center .section-title__heading {
    max-width: 48rem;
  }

  .section-title__desc {
    font-size: var(--fs-body-md);
    color: var(--color-text-muted);
    max-width: 40rem;
    line-height: var(--lh-relaxed);
  }
  .section-title--center .section-title__desc {
    margin-inline: auto;
  }

  /* Light variant for dark sections */
  .section-title--light .section-title__heading {
    color: var(--color-ivory);
  }
  .section-title--light .section-title__desc {
    color: var(--color-beige);
  }
</style>