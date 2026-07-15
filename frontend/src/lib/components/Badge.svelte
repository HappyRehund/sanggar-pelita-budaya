<script lang="ts">
  import type { Snippet } from 'svelte';

  type Variant = 'achievement' | 'activity' | 'neutral' | 'gold';

  interface Props {
    variant?: Variant;
    children: Snippet;
  }

  let { variant = 'neutral', children }: Props = $props();
</script>

<span class="badge badge--{variant}">
  {@render children()}
</span>

<style>
  .badge {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-1);
    padding: var(--sp-1) var(--sp-3);
    font-family: var(--font-sans);
    font-size: var(--fs-caption);
    font-weight: var(--fw-semibold);
    letter-spacing: var(--tracking-wide);
    text-transform: uppercase;
    border-radius: var(--radius-full);
    white-space: nowrap;
  }

  .badge--achievement {
    background-color: rgba(201, 162, 39, 0.14);
    color: var(--color-gold-dark);
    border: 1px solid rgba(201, 162, 39, 0.3);
  }

  .badge--activity {
    background-color: rgba(158, 42, 43, 0.1);
    color: var(--color-accent);
    border: 1px solid rgba(158, 42, 43, 0.24);
  }

  .badge--neutral {
    background-color: var(--color-surface-alt);
    color: var(--color-text-muted);
    border: 1px solid var(--color-border);
  }

  .badge--gold {
    background-color: var(--color-gold);
    color: var(--color-brown);
    border: 1px solid var(--color-gold);
  }
</style>