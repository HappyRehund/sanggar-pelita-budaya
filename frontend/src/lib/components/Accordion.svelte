<script lang="ts">
  import type { Snippet } from 'svelte';
  import { ChevronDown } from '@lucide/svelte';

  interface AccordionItem {
    id: string;
    title: string;
    content: Snippet;
  }

  interface Props {
    items: AccordionItem[];
    multi?: boolean;
  }

  let { items, multi = false }: Props = $props();
  let openItems = $state<Set<string>>(new Set());

  function toggle(id: string): void {
    const next = new Set(openItems);
    if (next.has(id)) {
      next.delete(id);
    } else {
      if (!multi) next.clear();
      next.add(id);
    }
    openItems = next;
  }

  function isOpen(id: string): boolean {
    return openItems.has(id);
  }
</script>

<div class="accordion">
  {#each items as item (item.id)}
    <div class="accordion__item" class:accordion__item--open={isOpen(item.id)}>
      <button
        class="accordion__header"
        onclick={() => toggle(item.id)}
        aria-expanded={isOpen(item.id)}
        aria-controls={`accordion-panel-${item.id}`}
        id={`accordion-trigger-${item.id}`}
      >
        <span class="accordion__title">{item.title}</span>
        <span class="accordion__icon" style:transform={isOpen(item.id) ? 'rotate(180deg)' : 'rotate(0deg)'}>
          <ChevronDown size={20} />
        </span>
      </button>
      <div
        class="accordion__panel"
        id={`accordion-panel-${item.id}`}
        role="region"
        aria-labelledby={`accordion-trigger-${item.id}`}
      >
        {#if isOpen(item.id)}
          <div class="accordion__content">
            {@render item.content()}
          </div>
        {/if}
      </div>
    </div>
  {/each}
</div>

<style>
  .accordion {
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
  }

  .accordion__item + .accordion__item {
    border-top: 1px solid var(--color-border);
  }

  .accordion__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: var(--sp-4) var(--sp-5);
    text-align: left;
    transition: background-color var(--duration-fast) var(--ease-smooth);
  }

  .accordion__header:hover {
    background-color: var(--color-surface-alt);
  }

  .accordion__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-medium);
    color: var(--color-text);
  }

  .accordion__icon {
    display: flex;
    align-items: center;
    color: var(--color-text-muted);
    flex-shrink: 0;
    transition: transform var(--duration-short) var(--ease-smooth);
  }

  .accordion__panel {
    overflow: hidden;
  }

  .accordion__content {
    padding: var(--sp-4) var(--sp-5) var(--sp-5);
    color: var(--color-text-muted);
    line-height: var(--lh-relaxed);
  }
</style>