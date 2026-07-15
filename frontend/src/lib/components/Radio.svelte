<script lang="ts">
  import type { HTMLInputAttributes } from 'svelte/elements';

  interface Props extends HTMLInputAttributes {
    label?: string;
    description?: string;
    class?: string;
  }

  let {
    label,
    description,
    class: className = '',
    checked = false,
    ...rest
  }: Props = $props();

  let isChecked = $state(checked);

  $effect(() => {
    isChecked = checked;
  });

  const fieldId = rest.id ?? `radio-${Math.random().toString(36).slice(2, 9)}`;
</script>

<div class="radio {className}">
  <div class="radio__row">
    <input
      type="radio"
      class="radio__input"
      id={fieldId}
      checked={isChecked}
      onchange={(e) => (isChecked = (e.target as HTMLInputElement).checked)}
      {...rest}
    />
    {#if label}
      <label class="radio__label" for={fieldId}>{label}</label>
    {/if}
  </div>
  {#if description}
    <p class="radio__desc">{description}</p>
  {/if}
</div>

<style>
  .radio {
    display: flex;
    flex-direction: column;
    gap: var(--sp-1);
  }

  .radio__row {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
  }

  .radio__input {
    width: 1.125rem;
    height: 1.125rem;
    accent-color: var(--color-accent);
    cursor: pointer;
    flex-shrink: 0;
  }

  .radio__label {
    font-size: var(--fs-body-sm);
    color: var(--color-text);
    cursor: pointer;
  }

  .radio__desc {
    font-size: var(--fs-caption);
    color: var(--color-text-subtle);
    margin-left: calc(1.125rem + var(--sp-3));
  }
</style>