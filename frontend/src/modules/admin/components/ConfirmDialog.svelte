<script lang="ts">
  import Modal from '$lib/components/Modal.svelte';
  import { AlertTriangle } from '@lucide/svelte';

  interface Props {
    open: boolean;
    title: string;
    message: string;
    confirmLabel: string;
    cancelLabel?: string;
    onconfirm: () => void;
    oncancel: () => void;
  }

  let {
    open,
    title,
    message,
    confirmLabel,
    cancelLabel = 'Cancel',
    onconfirm,
    oncancel,
  }: Props = $props();
</script>

<Modal {open} title={title} size="sm" onclose={oncancel}>
  <div class="confirm">
    <div class="confirm__icon">
      <AlertTriangle size={32} strokeWidth={1.5} />
    </div>
    <p class="confirm__message">{message}</p>
  </div>
  {#snippet footer()}
    <button class="confirm__btn confirm__btn--cancel" onclick={oncancel}>{cancelLabel}</button>
    <button class="confirm__btn confirm__btn--danger" onclick={onconfirm}>{confirmLabel}</button>
  {/snippet}
</Modal>

<style>
  .confirm {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--sp-4);
    padding: var(--sp-2) 0;
  }

  .confirm__icon {
    color: var(--color-danger);
  }

  .confirm__message {
    font-size: var(--fs-body);
    color: var(--color-text-muted);
    line-height: var(--lh-relaxed);
  }

  .confirm__btn {
    padding: var(--sp-2) var(--sp-5);
    border-radius: var(--radius-md);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-semibold);
    cursor: pointer;
    transition: background-color var(--duration-fast) var(--ease-smooth);
  }

  .confirm__btn--cancel {
    background: var(--color-surface-alt);
    color: var(--color-text);
    border: 1px solid var(--color-border);
  }

  .confirm__btn--cancel:hover {
    background: var(--color-gray-100);
  }

  .confirm__btn--danger {
    background: var(--color-danger);
    color: var(--color-white);
  }

  .confirm__btn--danger:hover {
    background: var(--color-red-dark);
  }
</style>