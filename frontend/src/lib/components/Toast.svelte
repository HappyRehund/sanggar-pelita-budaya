<script lang="ts">
  import { notifications } from '$lib/stores/notification.svelte';
  import { CheckCircle, XCircle, AlertTriangle, Info, X } from '@lucide/svelte';
  import type { ToastType } from '$lib/stores/notification.svelte';

  const iconMap: Record<ToastType, typeof CheckCircle> = {
    success: CheckCircle,
    error: XCircle,
    warning: AlertTriangle,
    info: Info,
  };

  function dismiss(id: number): void {
    notifications.dismiss(id);
  }
</script>

<div class="toast-container" role="region" aria-label="Notifications" aria-live="polite">
  {#each notifications.toasts as toast (toast.id)}
    {@const Icon = iconMap[toast.type]}
    <div class="toast toast--{toast.type}">
      <div class="toast__icon">
        <Icon size={20} />
      </div>
      <p class="toast__message">{toast.message}</p>
      <button class="toast__close" onclick={() => dismiss(toast.id)} aria-label="Dismiss notification">
        <X size={16} />
      </button>
    </div>
  {/each}
</div>

<style>
  .toast-container {
    position: fixed;
    top: var(--sp-5);
    right: var(--sp-5);
    z-index: var(--z-toast);
    display: flex;
    flex-direction: column;
    gap: var(--sp-2);
    max-width: 24rem;
    pointer-events: none;
  }

  .toast {
    display: flex;
    align-items: flex-start;
    gap: var(--sp-3);
    padding: var(--sp-4) var(--sp-5);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    pointer-events: auto;
    animation: toast-in var(--duration-medium) var(--ease-out);
  }

  .toast--success { border-left: 3px solid var(--color-success); }
  .toast--error { border-left: 3px solid var(--color-danger); }
  .toast--warning { border-left: 3px solid var(--color-warning); }
  .toast--info { border-left: 3px solid var(--color-info); }

  .toast__icon {
    flex-shrink: 0;
    margin-top: 1px;
  }

  .toast--success .toast__icon { color: var(--color-success); }
  .toast--error .toast__icon { color: var(--color-danger); }
  .toast--warning .toast__icon { color: var(--color-warning); }
  .toast--info .toast__icon { color: var(--color-info); }

  .toast__message {
    flex: 1;
    font-size: var(--fs-body-sm);
    line-height: var(--lh-normal);
    color: var(--color-text);
  }

  .toast__close {
    flex-shrink: 0;
    color: var(--color-text-subtle);
    transition: color var(--duration-fast) var(--ease-smooth);
  }

  .toast__close:hover {
    color: var(--color-text);
  }

  @keyframes toast-in {
    from { opacity: 0; transform: translateX(100%); }
    to { opacity: 1; transform: translateX(0); }
  }

  @media (prefers-reduced-motion: reduce) {
    .toast { animation: none; }
  }
</style>