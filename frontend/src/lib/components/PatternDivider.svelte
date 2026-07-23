<script lang="ts">
  import dividerPattern from '$assets/pattern-divider/pattern-divider-2.png';

  type Props = {
    position: 'top' | 'bottom';
  };

  let { position }: Props = $props();
</script>

<hr
  class="pattern-divider pattern-divider--{position}"
  aria-hidden="true"
  style:--divider-mask={`url(${dividerPattern})`}
/>

<style>
  .pattern-divider {
    display: block;
    width: 100%;
    height: 60px;
    margin: 0;
    border: 0;
    background: linear-gradient(135deg,
      var(--color-red) 0%,
      var(--color-gold-soft) 50%,
      var(--color-red) 100%);
    background-size: 200% 200%;
    background-position: 0% 50%;
    -webkit-mask: var(--divider-mask) top left / auto 60px repeat-x;
            mask: var(--divider-mask) top left / auto 60px repeat-x;
    animation: eyebrow-gradient-pan 6s linear infinite;
  }

  .pattern-divider--top {
    margin-block-start: calc(-1 * var(--section-padding-y));
  }

  .pattern-divider--bottom {
    margin-block-end: calc(-1 * var(--section-padding-y));
  }

  @media (prefers-reduced-motion: reduce) {
    .pattern-divider {
      animation: none;
    }
  }
</style>