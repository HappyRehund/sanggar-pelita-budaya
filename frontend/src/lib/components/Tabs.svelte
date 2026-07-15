<script lang="ts">
  import type { Snippet } from 'svelte';

  interface TabItem {
    id: string;
    label: string;
    content: Snippet;
  }

  interface Props {
    tabs: TabItem[];
    activeTab?: string;
    onchange?: (id: string) => void;
  }

  let { tabs, activeTab = '', onchange }: Props = $props();
  let active = $state(activeTab || tabs[0]?.id || '');

  function select(id: string): void {
    active = id;
    onchange?.(id);
  }

  function handleKeydown(e: KeyboardEvent): void {
    const currentIndex = tabs.findIndex((t) => t.id === active);
    if (e.key === 'ArrowRight') {
      e.preventDefault();
      const nextIdx = (currentIndex + 1) % tabs.length;
      select(tabs[nextIdx].id);
    } else if (e.key === 'ArrowLeft') {
      e.preventDefault();
      const prevIdx = (currentIndex - 1 + tabs.length) % tabs.length;
      select(tabs[prevIdx].id);
    }
  }
</script>

<div class="tabs">
  <div class="tabs__list" role="tablist" tabindex="-1" onkeydown={handleKeydown}>
    {#each tabs as tab (tab.id)}
      <button
        class="tabs__trigger"
        class:tabs__trigger--active={tab.id === active}
        role="tab"
        aria-selected={tab.id === active}
        aria-controls={`tab-panel-${tab.id}`}
        id={`tab-trigger-${tab.id}`}
        tabindex={tab.id === active ? 0 : -1}
        onclick={() => select(tab.id)}
      >
        {tab.label}
      </button>
    {/each}
  </div>
  {#each tabs as tab (tab.id)}
    <div
      class="tabs__panel"
      class:tabs__panel--active={tab.id === active}
      role="tabpanel"
      aria-labelledby={`tab-trigger-${tab.id}`}
      id={`tab-panel-${tab.id}`}
      tabindex="0"
      hidden={tab.id !== active}
    >
      {@render tab.content()}
    </div>
  {/each}
</div>

<style>
  .tabs__list {
    display: flex;
    gap: var(--sp-1);
    border-bottom: 1px solid var(--color-border);
    overflow-x: auto;
  }

  .tabs__trigger {
    padding: var(--sp-3) var(--sp-5);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    color: var(--color-text-muted);
    border-bottom: 2px solid transparent;
    transition: color var(--duration-fast) var(--ease-smooth), border-color var(--duration-fast) var(--ease-smooth);
    white-space: nowrap;
  }

  .tabs__trigger:hover {
    color: var(--color-text);
  }

  .tabs__trigger--active {
    color: var(--color-accent);
    border-bottom-color: var(--color-accent);
  }

  .tabs__panel {
    padding: var(--sp-5) 0;
    animation: fade-in var(--duration-short) var(--ease-smooth);
  }

  @keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
  }
</style>