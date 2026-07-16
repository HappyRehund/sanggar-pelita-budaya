<script lang="ts">
  import type { Snippet } from 'svelte';
  import { X } from '@lucide/svelte';

  interface Props {
    open: boolean;
    side?: 'left' | 'right';
    title?: string;
    onclose: () => void;
    children: Snippet;
  }

  let {
    open,
    side = 'left',
    title,
    onclose,
    children,
  }: Props = $props();

  function handleKeydown(e: KeyboardEvent): void {
    if (e.key === 'Escape' && open) {
      onclose();
    }
  }

  $effect(() => {
    if (open) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  });
</script>

<svelte:window onkeydown={handleKeydown} />

{#if open}
  <div class="drawer-overlay" role="presentation" onclick={() => onclose()} onkeydown={(e) => { if (e.key === 'Enter') onclose(); }}>
    <div
      class="drawer drawer--{side}"
      role="dialog"
      aria-modal="true"
      tabindex="-1"
      aria-label={title ?? 'Drawer'}
      onclick={(e) => e.stopPropagation()}
      onkeydown={(e) => e.stopPropagation()}
    >
      {#if title}
        <div class="drawer__header">
          <h2 class="drawer__title">{title}</h2>
          <button class="drawer__close" onclick={onclose} aria-label="Close drawer">
            <X size={22} />
          </button>
        </div>
      {:else}
        <button class="drawer__close drawer__close--floating" onclick={onclose} aria-label="Close drawer">
          <X size={22} />
        </button>
      {/if}
      <div class="drawer__body">
        {@render children()}
      </div>
    </div>
  </div>
{/if}

<style>
  .drawer-overlay {
    position: fixed;
    inset: 0;
    z-index: var(--z-drawer);
    background: rgba(26, 22, 18, 0.5);
    animation: fade-in var(--duration-short) var(--ease-smooth);
  }

  .drawer {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 100%;
    max-width: 24rem;
    background: var(--color-surface);
    box-shadow: var(--shadow-xl);
    display: flex;
    flex-direction: column;
    animation: slide-in var(--duration-medium) var(--ease-out);
  }

  .drawer--left { left: 0; }
  .drawer--right { right: 0; }

  .drawer__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--sp-5) var(--sp-6);
    border-bottom: 1px solid var(--color-border);
  }

  .drawer__title {
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
  }

  .drawer__close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: var(--radius-md);
    color: var(--color-text-muted);
    transition: background-color var(--duration-fast) var(--ease-smooth), color var(--duration-fast) var(--ease-smooth);
  }

  .drawer__close:hover {
    background-color: var(--color-surface-alt);
    color: var(--color-text);
  }

  .drawer__close--floating {
    position: absolute;
    top: var(--sp-3);
    right: var(--sp-3);
    z-index: 1;
  }

  .drawer__body {
    flex: 1;
    overflow-y: auto;
    padding: var(--sp-5) var(--sp-6);
  }

  @keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  @keyframes slide-in {
    from { transform: translateX(-100%); }
    to { transform: translateX(0); }
  }

  .drawer--right {
    animation-name: slide-in-right;
  }

  @keyframes slide-in-right {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
  }

  @media (prefers-reduced-motion: reduce) {
    .drawer-overlay, .drawer { animation: none; }
  }
</style>