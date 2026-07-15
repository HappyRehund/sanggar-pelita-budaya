<script lang="ts">
  import { X, ChevronLeft, ChevronRight } from '@lucide/svelte';
  import type { LightboxImage } from '$lib/hooks/useLightbox.svelte';

  interface Props {
    open: boolean;
    images: LightboxImage[];
    index: number;
    onclose: () => void;
    onnext: () => void;
    onprev: () => void;
  }

  let { open, images, index, onclose, onnext, onprev }: Props = $props();

  let touchStartX = $state(0);

  const current = $derived(images[index] ?? null);
  const total = $derived(images.length);
  const hasPrev = $derived(index > 0);
  const hasNext = $derived(index < total - 1);

  function handleKeydown(e: KeyboardEvent): void {
    if (!open) return;
    if (e.key === 'Escape') onclose();
    else if (e.key === 'ArrowRight') onnext();
    else if (e.key === 'ArrowLeft') onprev();
  }

  function handleTouchStart(e: TouchEvent): void {
    touchStartX = e.touches[0].clientX;
  }

  function handleTouchEnd(e: TouchEvent): void {
    const diff = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(diff) > 50) {
      if (diff > 0) onprev();
      else onnext();
    }
  }

  function handleOverlayClick(e: MouseEvent): void {
    if (e.target === e.currentTarget) onclose();
  }
</script>

<svelte:window onkeydown={handleKeydown} />

{#if open && current}
  <div
    class="lightbox"
    role="dialog"
    aria-modal="true"
    tabindex="-1"
    aria-label="Image viewer"
    onclick={handleOverlayClick}
    onkeydown={handleKeydown}
    ontouchstart={handleTouchStart}
    ontouchend={handleTouchEnd}
  >
    <button class="lightbox__close" onclick={onclose} aria-label="Close lightbox">
      <X size={28} />
    </button>

    {#if hasPrev}
      <button class="lightbox__nav lightbox__nav--prev" onclick={onprev} aria-label="Previous image">
        <ChevronLeft size={32} />
      </button>
    {/if}

    <div class="lightbox__content">
      <img src={current.src} alt={current.alt} class="lightbox__img" />
      {#if current.caption}
        <p class="lightbox__caption">{current.caption}</p>
      {/if}
      <span class="lightbox__counter">{index + 1} / {total}</span>
    </div>

    {#if hasNext}
      <button class="lightbox__nav lightbox__nav--next" onclick={onnext} aria-label="Next image">
        <ChevronRight size={32} />
      </button>
    {/if}
  </div>
{/if}

<style>
  .lightbox {
    position: fixed;
    inset: 0;
    z-index: var(--z-lightbox);
    background: rgba(26, 22, 18, 0.92);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--sp-6);
    animation: fade-in var(--duration-short) var(--ease-smooth);
  }

  .lightbox__close {
    position: absolute;
    top: var(--sp-5);
    right: var(--sp-5);
    z-index: 1;
    color: var(--color-white);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-full);
    transition: background-color var(--duration-fast) var(--ease-smooth);
  }

  .lightbox__close:hover {
    background-color: rgba(255, 255, 255, 0.15);
  }

  .lightbox__nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    color: var(--color-white);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3.5rem;
    height: 3.5rem;
    border-radius: var(--radius-full);
    transition: background-color var(--duration-fast) var(--ease-smooth);
  }

  .lightbox__nav:hover {
    background-color: rgba(255, 255, 255, 0.12);
  }

  .lightbox__nav--prev { left: var(--sp-4); }
  .lightbox__nav--next { right: var(--sp-4); }

  .lightbox__content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-3);
    max-width: 90vw;
    max-height: 90vh;
  }

  .lightbox__img {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
    border-radius: var(--radius-md);
  }

  .lightbox__caption {
    color: var(--color-gray-300);
    font-size: var(--fs-body-sm);
    text-align: center;
  }

  .lightbox__counter {
    color: var(--color-gray-400);
    font-size: var(--fs-caption);
    letter-spacing: var(--tracking-wide);
  }

  @keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  @media (prefers-reduced-motion: reduce) {
    .lightbox { animation: none; }
  }
</style>