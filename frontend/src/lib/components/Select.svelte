<script lang="ts">
  import type { HTMLSelectAttributes } from 'svelte/elements';

  interface Option {
    value: string | number;
    label: string;
  }

  interface Props extends HTMLSelectAttributes {
    label?: string;
    error?: string;
    hint?: string;
    options: Option[];
    class?: string;
  }

  let {
    label,
    error,
    hint,
    options,
    class: className = '',
    value = '',
    ...rest
  }: Props = $props();

  let fieldValue = $state(value);

  $effect(() => {
    fieldValue = value;
  });

  const fieldId = rest.id ?? `select-${Math.random().toString(36).slice(2, 9)}`;
</script>

<div class="field {className}">
  {#if label}
    <label class="field__label" for={fieldId}>{label}</label>
  {/if}
  <select
    class="field__select"
    class:field__select--error={!!error}
    id={fieldId}
    value={fieldValue}
    onchange={(e) => (fieldValue = (e.target as HTMLSelectElement).value)}
    {...rest}
  >
    {#each options as opt (opt.value)}
      <option value={opt.value}>{opt.label}</option>
    {/each}
  </select>
  {#if error}
    <p class="field__error">{error}</p>
  {:else if hint}
    <p class="field__hint">{hint}</p>
  {/if}
</div>

<style>
  .field {
    display: flex;
    flex-direction: column;
    gap: var(--sp-2);
  }

  .field__label {
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    color: var(--color-text);
  }

  .field__select {
    width: 100%;
    padding: var(--sp-3) var(--sp-4);
    border: 1px solid var(--color-border-strong);
    border-radius: var(--radius-md);
    font-size: var(--fs-body);
    color: var(--color-text);
    background: var(--color-surface);
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238a7f6f' stroke-width='2' stroke-linecap='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right var(--sp-4) center;
    padding-right: var(--sp-10);
    transition: border-color var(--duration-fast) var(--ease-smooth), box-shadow var(--duration-fast) var(--ease-smooth);
  }

  .field__select:focus {
    outline: none;
    border-color: var(--color-accent);
    box-shadow: 0 0 0 3px rgba(158, 42, 43, 0.1);
  }

  .field__select--error {
    border-color: var(--color-danger);
  }

  .field__select:disabled {
    background: var(--color-surface-alt);
    cursor: not-allowed;
  }

  .field__error {
    font-size: var(--fs-caption);
    color: var(--color-danger);
  }

  .field__hint {
    font-size: var(--fs-caption);
    color: var(--color-text-subtle);
  }
</style>