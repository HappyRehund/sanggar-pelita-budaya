<script lang="ts">
  import type { Snippet } from 'svelte';
  import { X } from '@lucide/svelte';

  interface Props {
    open: boolean;
    title?: string;
    size?: 'sm' | 'md' | 'lg';
    closeOnOverlay?: boolean;
    onclose: () => void;
    children: Snippet;
    footer?: Snippet;
  }

  let {
    open,
    title,
    size = 'md',
    closeOnOverlay = true,
    onclose,
    children,
    footer,
  }: Props = $props();

  let modalEl = $state<HTMLDivElement | null>(null);

  function handleKeydown(e: KeyboardEvent): void {
    if (e.key === 'Escape' && open) {
      onclose();
    }
    if (e.key === 'Tab' && modalEl) {
      trapFocus(e);
    }
  }

  function trapFocus(e: KeyboardEvent): void {
    if (!modalEl) return;
    const focusable = modalEl.querySelectorAll<HTMLElement>(
      'a[href], button:not(:disabled), textarea, input:not(:disabled), select, [tabindex]:not([tabindex="-1"])'
    );
    if (focusable.length === 0) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }

  function handleOverlayClick(e: MouseEvent): void {
    if (closeOnOverlay && e.target === e.currentTarget) {
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
  <div
    class="modal-overlay"
    role="dialog"
    aria-modal="true"
    tabindex="-1"
    aria-label={title ?? 'Dialog'}
    onclick={handleOverlayClick}
    onkeydown={handleKeydown}
  >
    <div bind:this={modalEl} class="modal modal--{size}">
      {#if title}
        <div class="modal__header">
          <h2 class="modal__title">{title}</h2>
          <button class="modal__close" onclick={onclose} aria-label="Close dialog">
            <X size={20} />
          </button>
        </div>
      {:else}
        <button class="modal__close modal__close--floating" onclick={onclose} aria-label="Close dialog">
          <X size={20} />
        </button>
      {/if}

      <div class="modal__body">
        {@render children()}
      </div>

      {#if footer}
        <div class="modal__footer">
          {@render footer()}
        </div>
      {/if}
    </div>
  </div>
{/if}

<style>
  .modal-overlay {
    position: fixed;
    inset: 0;
    z-index: var(--z-modal);
    background: rgba(26, 22, 18, 0.6);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--sp-4);
    animation: fade-in var(--duration-short) var(--ease-smooth);
  }

  .modal {
    background: var(--color-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: modal-in var(--duration-medium) var(--ease-out);
  }

  .modal--sm { width: 100%; max-width: 24rem; }
  .modal--md { width: 100%; max-width: 32rem; }
  .modal--lg { width: 100%; max-width: 48rem; }

  .modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--sp-5) var(--sp-6);
    border-bottom: 1px solid var(--color-border);
  }

  .modal__title {
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
  }

  .modal__close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: var(--radius-md);
    color: var(--color-text-muted);
    transition: background-color var(--duration-fast) var(--ease-smooth), color var(--duration-fast) var(--ease-smooth);
  }

  .modal__close:hover {
    background-color: var(--color-surface-alt);
    color: var(--color-text);
  }

  .modal__close--floating {
    position: absolute;
    top: var(--sp-3);
    right: var(--sp-3);
    z-index: 1;
  }

  .modal__body {
    padding: var(--sp-6);
    overflow-y: auto;
    flex: 1;
  }

  .modal__footer {
    display: flex;
    gap: var(--sp-3);
    justify-content: flex-end;
    padding: var(--sp-5) var(--sp-6);
    border-top: 1px solid var(--color-border);
  }

  @keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  @keyframes modal-in {
    from { opacity: 0; transform: translateY(16px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }

  @media (prefers-reduced-motion: reduce) {
    .modal-overlay, .modal { animation: none; }
  }
</style>