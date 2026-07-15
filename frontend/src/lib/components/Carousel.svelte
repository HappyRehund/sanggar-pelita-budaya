<script lang="ts">
  import type { Snippet } from 'svelte';
  import { ChevronLeft, ChevronRight } from '@lucide/svelte';

  interface Props {
    items: Snippet[];
    activeIndex?: number;
    onChange?: (index: number) => void;
  }

  let { items, activeIndex = 0, onChange }: Props = $props();
  let current = $state(activeIndex);
  let touchStartX = $state(0);

  const total = $derived(items.length);
  const hasPrev = $derived(current > 0);
  const hasNext = $derived(current < total - 1);

  $effect(() => {
    current = activeIndex;
  });

  function go(index: number): void {
    current = Math.max(0, Math.min(index, total - 1));
    onChange?.(current);
  }

  function next(): void {
    if (hasNext) go(current + 1);
  }

  function prev(): void {
    if (hasPrev) go(current - 1);
  }

  function handleKeydown(e: KeyboardEvent): void {
    if (e.key === 'ArrowRight') next();
    else if (e.key === 'ArrowLeft') prev();
  }

  function handleTouchStart(e: TouchEvent): void {
    touchStartX = e.touches[0].clientX;
  }

  function handleTouchEnd(e: TouchEvent): void {
    const diff = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(diff) > 50) {
      if (diff > 0) prev();
      else next();
    }
  }
</script>

<div
  class="carousel"
  role="region"
  aria-label="Carousel"
  tabindex="0"
  onkeydown={handleKeydown}
  ontouchstart={handleTouchStart}
  ontouchend={handleTouchEnd}
>
  <div class="carousel__track" style:transform={`translateX(-${current * 100}%)`}>
    {#each items as item, i (i)}
      <div class="carousel__slide" class:carousel__slide--active={i === current} aria-hidden={i !== current}>
        {@render item()}
      </div>
    {/each}
  </div>

  {#if hasPrev}
    <button class="carousel__nav carousel__nav--prev" onclick={prev} aria-label="Previous slide">
      <ChevronLeft size={28} />
    </button>
  {/if}
  {#if hasNext}
    <button class="carousel__nav carousel__nav--next" onclick={next} aria-label="Next slide">
      <ChevronRight size={28} />
    </button>
  {/if}

  <div class="carousel__dots">
    {#each Array(total) as _, i (i)}
      <button
        class="carousel__dot"
        class:carousel__dot--active={i === current}
        onclick={() => go(i)}
        aria-label={`Go to slide ${i + 1}`}
      ></button>
    {/each}
  </div>
</div>

<style>
  .carousel {
    position: relative;
    overflow: hidden;
    border-radius: var(--radius-2xl);
    outline: none;
  }

  .carousel:focus-visible {
    box-shadow: 0 0 0 2px var(--color-accent);
  }

  .carousel__track {
    display: flex;
    transition: transform var(--duration-long) var(--ease-out);
  }

  .carousel__slide {
    flex: 0 0 100%;
    opacity: 0.4;
    transition: opacity var(--duration-medium) var(--ease-smooth);
  }

  .carousel__slide--active {
    opacity: 1;
  }

  .carousel__nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: var(--z-base);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-full);
    background: rgba(255, 255, 255, 0.9);
    color: var(--color-brown);
    box-shadow: var(--shadow-md);
    transition: background-color var(--duration-fast) var(--ease-smooth);
  }

  .carousel__nav:hover {
    background: var(--color-white);
  }

  .carousel__nav--prev { left: var(--sp-3); }
  .carousel__nav--next { right: var(--sp-3); }

  .carousel__dots {
    display: flex;
    gap: var(--sp-2);
    justify-content: center;
    padding: var(--sp-4) 0 var(--sp-2);
  }

  .carousel__dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: var(--radius-full);
    background: var(--color-border-strong);
    transition: background-color var(--duration-fast) var(--ease-smooth), width var(--duration-fast) var(--ease-smooth);
  }

  .carousel__dot--active {
    background: var(--color-accent);
    width: 1.5rem;
  }

  @media (prefers-reduced-motion: reduce) {
    .carousel__track { transition: none; }
  }
</style>