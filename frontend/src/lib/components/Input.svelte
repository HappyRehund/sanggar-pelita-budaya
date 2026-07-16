<script lang="ts">
  import { untrack } from 'svelte';
  import type { HTMLInputAttributes } from 'svelte/elements';

  interface Props extends HTMLInputAttributes {
    label?: string;
    error?: string;
    hint?: string;
    class?: string;
  }

  let {
    label,
    error,
    hint,
    class: className = '',
    value = '',
    ...rest
  }: Props = $props();

  let fieldValue = $state(untrack(() => value));

  $effect(() => {
    fieldValue = value;
  });

  const fieldId = untrack(() => rest.id) ?? `input-${Math.random().toString(36).slice(2, 9)}`;
</script>

<div class="field {className}">
  {#if label}
    <label class="field__label" for={fieldId}>{label}</label>
  {/if}
  <input
    class="field__input"
    class:field__input--error={!!error}
    id={fieldId}
    value={fieldValue}
    oninput={(e) => (fieldValue = (e.target as HTMLInputElement).value)}
    {...rest}
  />
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

  .field__input {
    width: 100%;
    padding: var(--sp-3) var(--sp-4);
    border: 1px solid var(--color-border-strong);
    border-radius: var(--radius-md);
    font-size: var(--fs-body);
    color: var(--color-text);
    background: var(--color-surface);
    transition: border-color var(--duration-fast) var(--ease-smooth), box-shadow var(--duration-fast) var(--ease-smooth);
  }

  .field__input::placeholder {
    color: var(--color-text-subtle);
  }

  .field__input:focus {
    outline: none;
    border-color: var(--color-accent);
    box-shadow: 0 0 0 3px rgba(158, 42, 43, 0.1);
  }

  .field__input--error {
    border-color: var(--color-danger);
  }

  .field__input:disabled {
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