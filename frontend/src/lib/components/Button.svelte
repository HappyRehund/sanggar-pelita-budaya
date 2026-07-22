<script lang="ts">
  import type { Snippet } from 'svelte';
  import type { HTMLAnchorAttributes, HTMLButtonAttributes } from 'svelte/elements';

  type Variant =
    | 'primary'
    | 'secondary'
    | 'ghost'
    | 'ink'
    | 'soft-ink'
    | 'icon'
    | 'gold'
    | 'outline-red'
    | 'outline-gold'
    | 'outline-ink'
    | 'outline-ink-soft'
    | 'gradient'
    | 'outline-gradient';
  type Size = 'sm' | 'md' | 'lg';
  type Radius = 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | 'full';

  interface CommonProps {
    variant?: Variant;
    size?: Size;
    radius?: Radius;
    href?: string;
    full?: boolean;
    class?: string;
    children: Snippet;
  }

  let {
    variant = 'primary',
    size = 'md',
    radius,
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
      radius ? `btn--radius-${radius}` : '',
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
    /* border-radius set via `radius` prop (no default) */
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

  .btn--ink {
    background-color: var(--color-ink);
    color: var(--color-white);
    border: 1px solid var(--color-ink);
    box-shadow: var(--shadow-sm);
  }
  .btn--ink:not(:disabled):hover {
    background-color: var(--color-ink-soft);
    border-color: var(--color-ink-soft);
    color: var(--color-white);
    box-shadow: var(--shadow-md);
  }

  .btn--soft-ink {
    background-color: var(--color-ink-soft);
    color: var(--color-white);
    border: 1px solid var(--color-ink-soft);
    box-shadow: var(--shadow-sm);
  }
  .btn--soft-ink:not(:disabled):hover {
    background-color: var(--color-ink);
    border-color: var(--color-ink);
    color: var(--color-white);
    box-shadow: var(--shadow-md);
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

  .btn--outline-ink {
    background-color: transparent;
    color: var(--color-ink);
    border: 1px solid var(--color-ink);
  }
  .btn--outline-ink:not(:disabled):hover {
    background-color: var(--color-ink);
    color: var(--color-white);
    border-color: var(--color-ink);
  }

  .btn--outline-ink-soft {
    background-color: transparent;
    color: var(--color-ink-soft);
    border: 1px solid var(--color-ink-soft);
  }
  .btn--outline-ink-soft:not(:disabled):hover {
    background-color: var(--color-ink-soft);
    color: var(--color-white);
    border-color: var(--color-ink-soft);
  }

  .btn--gradient {
    background:
      linear-gradient(135deg,
        var(--color-red) 0%,
        var(--color-gold-soft) 50%,
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

  .btn--outline-gradient {
    position: relative;
    background: var(--color-surface);
    color: transparent;
    border: none;
    isolation: isolate;
    background-image: linear-gradient(135deg,
      var(--color-red) 0%,
      var(--color-gold-soft) 50%,
      var(--color-red) 100%);
    background-size: 200% 200%;
    background-position: 0% 50%;
    -webkit-background-clip: text;
            background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: btn-gradient-pan-text 6s linear infinite;
  }
  .btn--outline-gradient::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    padding: 2px;
    background: linear-gradient(135deg,
      var(--color-red) 0%,
      var(--color-gold-soft) 50%,
      var(--color-red) 100%);
    background-size: 200% 200%;
    -webkit-mask:
      linear-gradient(#000 0 0) content-box,
      linear-gradient(#000 0 0);
    mask:
      linear-gradient(#000 0 0) content-box,
      linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
            mask-composite: exclude;
    animation: btn-gradient-pan-border 6s linear infinite;
    pointer-events: none;
    z-index: -1;
  }
  .btn--outline-gradient:not(:disabled):hover {
    background-position: 100% 50%;
  }

  @keyframes btn-gradient-pan {
    0%   { background-position:   0% 50%,   0% 50%; }
    50%  { background-position: 100% 50%, 100% 50%; }
    100% { background-position:   0% 50%,   0% 50%; }
  }

  @keyframes btn-gradient-pan-border {
    0%   { background-position:   0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position:   0% 50%; }
  }

  @keyframes btn-gradient-pan-text {
    0%   { background-position:   0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position:   0% 50%; }
  }

  @media (prefers-reduced-motion: reduce) {
    .btn--gradient,
    .btn--outline-gradient,
    .btn--outline-gradient::before {
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

  /* ---- Radius modifiers (override variant defaults when set) ---------- */
  .btn--radius-xs  { border-radius: var(--radius-xs); }
  .btn--radius-sm  { border-radius: var(--radius-sm); }
  .btn--radius-md  { border-radius: var(--radius-md); }
  .btn--radius-lg  { border-radius: var(--radius-lg); }
  .btn--radius-xl  { border-radius: var(--radius-xl); }
  .btn--radius-2xl { border-radius: var(--radius-2xl); }
  .btn--radius-3xl { border-radius: var(--radius-3xl); }
  .btn--radius-full { border-radius: var(--radius-full); }
</style>