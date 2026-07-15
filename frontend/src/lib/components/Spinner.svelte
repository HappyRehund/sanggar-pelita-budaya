<script lang="ts">
  import { Loader2 } from '@lucide/svelte';

  interface Props {
    size?: number;
    label?: string;
  }

  let { size = 24, label = 'Loading' }: Props = $props();
</script>

<span class="spinner" role="status" aria-live="polite" aria-label={label}>
  <Loader2 {size} class="spinner__icon" />
  <span class="sr-only">{label}</span>
</span>

<style>
  .spinner {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--color-accent);
  }

  .spinner :global(svg) {
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }
</style>