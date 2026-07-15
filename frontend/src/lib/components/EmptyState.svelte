<script lang="ts">
  import type { Snippet } from 'svelte';
  import { ImageOff } from '@lucide/svelte';

  interface Props {
    title: string;
    description?: string;
    icon?: boolean;
    children?: Snippet;
  }

  let { title, description, icon = true, children }: Props = $props();
</script>

<div class="empty-state">
  {#if icon}
    <div class="empty-state__icon" aria-hidden="true">
      <ImageOff size={48} strokeWidth={1.25} />
    </div>
  {/if}
  <h3 class="empty-state__title">{title}</h3>
  {#if description}
    <p class="empty-state__desc">{description}</p>
  {/if}
  {#if children}
    <div class="empty-state__action">
      {@render children()}
    </div>
  {/if}
</div>

<style>
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: var(--sp-12) var(--sp-6);
    gap: var(--sp-4);
  }

  .empty-state__icon {
    color: var(--color-gray-400);
    margin-bottom: var(--sp-2);
  }

  .empty-state__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-medium);
    color: var(--color-text);
  }

  .empty-state__desc {
    font-size: var(--fs-body);
    color: var(--color-text-muted);
    max-width: 28rem;
  }

  .empty-state__action {
    margin-top: var(--sp-2);
  }
</style>