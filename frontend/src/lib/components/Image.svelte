<script lang="ts">
  interface Props {
    src: string;
    alt: string;
    width?: number;
    height?: number;
    loading?: 'lazy' | 'eager';
    sizes?: string;
    fallback?: string;
    ratio?: string;
    rounded?: boolean;
    class?: string;
  }

  let {
    src,
    alt,
    width,
    height,
    loading = 'lazy',
    sizes,
    fallback = '/assets/images/placeholder.svg',
    ratio,
    rounded = true,
    class: className = '',
  }: Props = $props();

  let failed = $state(false);
  let loaded = $state(false);

  const currentSrc = $derived(failed ? fallback : src);
</script>

<div class="img-wrap {className}" style:aspect-ratio={ratio}>
  {#if !loaded}
    <div class="img-wrap__placeholder" aria-hidden="true"></div>
  {/if}
  <img
    class="img-wrap__img"
    class:img-wrap__img--loaded={loaded}
    class:img-wrap__img--rounded={rounded}
    src={currentSrc}
    {alt}
    {width}
    {height}
    {loading}
    {sizes}
    onload={() => (loaded = true)}
    onerror={() => {
      failed = true;
      loaded = true;
    }}
  />
</div>

<style>
  .img-wrap {
    position: relative;
    overflow: hidden;
    background-color: var(--color-surface-alt);
  }

  .img-wrap__placeholder {
    position: absolute;
    inset: 0;
    background: linear-gradient(
      90deg,
      var(--color-gray-100) 25%,
      var(--color-gray-200) 50%,
      var(--color-gray-100) 75%
    );
    background-size: 200% 100%;
    animation: shimmer 1.6s var(--ease-smooth) infinite;
  }

  @keyframes shimmer {
    0% {
      background-position: 200% 0;
    }
    100% {
      background-position: -200% 0;
    }
  }

  .img-wrap__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity var(--duration-medium) var(--ease-smooth);
  }

  .img-wrap__img--loaded {
    opacity: 1;
  }

  .img-wrap__img--rounded {
    border-radius: var(--radius-2xl);
  }
</style>