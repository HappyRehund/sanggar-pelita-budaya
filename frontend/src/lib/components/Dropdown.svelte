<script lang="ts">
  import type { Snippet } from 'svelte';
  import { ChevronDown } from '@lucide/svelte';

  interface Props {
    label: string;
    children: Snippet;
    align?: 'left' | 'right';
  }

  let { label, children, align = 'left' }: Props = $props();
  let open = $state(false);
  let containerEl = $state<HTMLElement | null>(null);

  function toggle(): void {
    open = !open;
  }

  function handleOutside(e: MouseEvent): void {
    if (containerEl && !containerEl.contains(e.target as Node)) {
      open = false;
    }
  }

  function handleKeydown(e: KeyboardEvent): void {
    if (e.key === 'Escape') open = false;
  }

  $effect(() => {
    if (open) {
      document.addEventListener('click', handleOutside);
    } else {
      document.removeEventListener('click', handleOutside);
    }
    return () => document.removeEventListener('click', handleOutside);
  });
</script>

  <div
    bind:this={containerEl}
    class="dropdown"
    role="group"
    onkeydown={handleKeydown}
  >
  <button
    class="dropdown__trigger"
    onclick={toggle}
    aria-expanded={open}
    aria-haspopup="true"
  >
    {label}
    <span class="dropdown__icon" style:transform={open ? 'rotate(180deg)' : 'rotate(0)'}>
      <ChevronDown size={16} />
    </span>
  </button>

  {#if open}
    <div class="dropdown__menu dropdown__menu--{align}" role="menu" tabindex="-1" onclick={() => (open = false)} onkeydown={(e) => { if (e.key === 'Escape') open = false; }}>
      {@render children()}
    </div>
  {/if}
</div>

<style>
  .dropdown {
    position: relative;
    display: inline-block;
  }

  .dropdown__trigger {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    padding: var(--sp-2) var(--sp-4);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    color: var(--color-text);
    transition: border-color var(--duration-fast) var(--ease-smooth);
  }

  .dropdown__trigger:hover {
    border-color: var(--color-accent);
  }

  .dropdown__icon {
    display: flex;
    align-items: center;
    color: var(--color-text-muted);
    transition: transform var(--duration-short) var(--ease-smooth);
  }

  .dropdown__menu {
    position: absolute;
    top: calc(100% + var(--sp-1));
    z-index: var(--z-sticky);
    min-width: 12rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    padding: var(--sp-1);
    animation: fade-in var(--duration-fast) var(--ease-smooth);
  }

  .dropdown__menu--left { left: 0; }
  .dropdown__menu--right { right: 0; }

  @keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
  }
</style>