<script lang="ts">
  import type { HTMLInputAttributes } from 'svelte/elements';

  interface Props extends HTMLInputAttributes {
    label?: string;
    description?: string;
    error?: string;
    class?: string;
  }

  let {
    label,
    description,
    error,
    class: className = '',
    checked = false,
    ...rest
  }: Props = $props();

  let isChecked = $state(checked);

  $effect(() => {
    isChecked = checked;
  });

  const fieldId = rest.id ?? `checkbox-${Math.random().toString(36).slice(2, 9)}`;
</script>

<div class="checkbox {className}">
  <div class="checkbox__row">
    <input
      type="checkbox"
      class="checkbox__input"
      id={fieldId}
      checked={isChecked}
      onchange={(e) => (isChecked = (e.target as HTMLInputElement).checked)}
      {...rest}
    />
    {#if label}
      <label class="checkbox__label" for={fieldId}>{label}</label>
    {/if}
  </div>
  {#if description}
    <p class="checkbox__desc">{description}</p>
  {/if}
  {#if error}
    <p class="checkbox__error">{error}</p>
  {/if}
</div>

<style>
  .checkbox {
    display: flex;
    flex-direction: column;
    gap: var(--sp-1);
  }

  .checkbox__row {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
  }

  .checkbox__input {
    width: 1.125rem;
    height: 1.125rem;
    accent-color: var(--color-accent);
    cursor: pointer;
    flex-shrink: 0;
  }

  .checkbox__label {
    font-size: var(--fs-body-sm);
    color: var(--color-text);
    cursor: pointer;
  }

  .checkbox__desc {
    font-size: var(--fs-caption);
    color: var(--color-text-subtle);
    margin-left: calc(1.125rem + var(--sp-3));
  }

  .checkbox__error {
    font-size: var(--fs-caption);
    color: var(--color-danger);
    margin-left: calc(1.125rem + var(--sp-3));
  }
</style>