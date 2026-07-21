<script lang="ts">
  import type { Snippet } from 'svelte';
  import type { HTMLAnchorAttributes, HTMLButtonAttributes } from 'svelte/elements';

  type Variant = 'primary' | 'secondary' | 'ghost' | 'icon' | 'gold' | 'outline-red' | 'outline-gold' | 'gradient' | 'gradient-outline';
  type Size = 'sm' | 'md' | 'lg';

  interface CommonProps {
    variant?: Variant;
    size?: Size;
    href?: string;
    full?: boolean;
    class?: string;
    children: Snippet;
  }

  let {
    variant = 'primary',
    size = 'md',
    href,
    full = false,
    class: className = '',
    children,
    ...rest
  }: CommonProps & HTMLButtonAttributes & HTMLAnchorAttributes = $props();

  const classes = $derived(
    [
      'btn',
      `btn--${variant}`,
      `btn--${size}`,
      full ? 'btn--full' : '',
      className,
    ]
      .filter(Boolean)
      .join(' ')
  );

  const buttonRest = $derived(rest as HTMLButtonAttributes);
  const anchorRest = $derived(rest as HTMLAnchorAttributes);
</script>

{#if href}
  <a {href} class={classes} {...anchorRest}>
    {@render children()}
  </a>
{:else}
  <button class={classes} {...buttonRest}>
    {@render children()}
  </button>
{/if}

<style>
  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--sp-2);
    font-family: var(--font-sans);
    font-weight: var(--fw-semibold);
    letter-spacing: var(--tracking-wide);
    text-decoration: none;
    border-radius: var(--radius-md);
    transition:
      background-color var(--duration-fast) var(--ease-smooth),
      color var(--duration-fast) var(--ease-smooth),
      border-color var(--duration-fast) var(--ease-smooth),
      box-shadow var(--duration-fast) var(--ease-smooth),
      transform var(--duration-fast) var(--ease-out);
    white-space: nowrap;
    cursor: pointer;
  }

  .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
  }

  .btn:not(:disabled):hover {
    transform: scale(1.03);
  }

  .btn:not(:disabled):active {
    transform: scale(1);
  }

  .btn--sm {
    padding: var(--sp-2) var(--sp-4);
    font-size: var(--fs-body-sm);
  }
  .btn--md {
    padding: var(--sp-3) var(--sp-6);
    font-size: var(--fs-body-sm);
  }
  .btn--lg {
    padding: var(--sp-4) var(--sp-8);
    font-size: var(--fs-body);
  }

  .btn--full {
    width: 100%;
  }

  .btn--primary {
    background-color: var(--color-accent);
    color: var(--color-white);
    border: 1px solid var(--color-accent);
    box-shadow: var(--shadow-sm);
  }
  .btn--primary:not(:disabled):hover {
    background-color: var(--color-accent-hover);
    border-color: var(--color-accent-hover);
    color: var(--color-white);
    box-shadow: var(--shadow-md);
  }

  .btn--secondary {
    background-color: transparent;
    color: var(--color-accent);
    border: 1px solid var(--color-accent);
  }
  .btn--secondary:not(:disabled):hover {
    background-color: var(--color-accent);
    color: var(--color-white);
    border-color: var(--color-accent);
  }

  .btn--ghost {
    background-color: transparent;
    color: var(--color-text);
    border: 1px solid transparent;
  }
  .btn--ghost:not(:disabled):hover {
    color: var(--color-accent);
    background-color: var(--color-surface-alt);
  }

  .btn--gold {
    background-color: var(--color-gold);
    color: var(--color-brown);
    border: 1px solid var(--color-gold);
    box-shadow: var(--shadow-sm);
  }
  .btn--gold:not(:disabled):hover {
    background-color: var(--color-gold-dark);
    color: var(--color-white);
    border-color: var(--color-gold-dark);
  }

  .btn--outline-red {
    background-color: transparent;
    color: var(--color-red);
    border: 1px solid var(--color-red);
  }
  .btn--outline-red:not(:disabled):hover {
    background-color: var(--color-red);
    color: var(--color-white);
    border-color: var(--color-red);
  }

  .btn--outline-gold {
    background-color: transparent;
    color: var(--color-gold-dark);
    border: 1px solid var(--color-gold-dark);
  }
  .btn--outline-gold:not(:disabled):hover {
    background-color: var(--color-gold);
    color: var(--color-brown);
    border-color: var(--color-gold);
  }

  .btn--gradient {
    background:
      linear-gradient(135deg,
        var(--color-red) 0%,
        var(--color-gold-dark) 50%,
        var(--color-red) 100%) padding-box;
    background-size: 200% 200%;
    background-position: 0% 50%;
    color: var(--color-white);
    border: 1px solid transparent;
    box-shadow: var(--shadow-sm);
    animation: btn-gradient-pan 6s linear infinite;
  }
  .btn--gradient:not(:disabled):hover {
    box-shadow: var(--shadow-md);
  }

  .btn--gradient-outline {
    background:
      var(--color-surface) padding-box,
      linear-gradient(135deg,
        var(--color-red) 0%,
        var(--color-gold-dark) 50%,
        var(--color-red) 100%) border-box;
    background-size: 100% 100%, 200% 200%;
    background-position: 0% 50%, 0% 50%;
    color: var(--color-text);
    border: 2px solid transparent;
    animation: btn-gradient-pan 6s linear infinite;
  }
  .btn--gradient-outline:not(:disabled):hover {
    color: var(--color-red);
  }

  @keyframes btn-gradient-pan {
    0%   { background-position:   0% 50%,   0% 50%; }
    50%  { background-position: 100% 50%, 100% 50%; }
    100% { background-position:   0% 50%,   0% 50%; }
  }

  @media (prefers-reduced-motion: reduce) {
    .btn--gradient,
    .btn--gradient-outline {
      animation: none;
    }
  }

  .btn--icon {
    background-color: transparent;
    color: var(--color-text);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-full);
    padding: var(--sp-2);
    width: 2.5rem;
    height: 2.5rem;
  }
  .btn--icon:not(:disabled):hover {
    border-color: var(--color-accent);
    color: var(--color-accent);
  }
</style>