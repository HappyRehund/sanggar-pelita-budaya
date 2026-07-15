<script lang="ts">
  type SkeletonVariant = 'text' | 'rect' | 'circle' | 'card';

  interface Props {
    variant?: SkeletonVariant;
    width?: string;
    height?: string;
    count?: number;
  }

  let {
    variant = 'text',
    width,
    height,
    count = 1,
  }: Props = $props();
</script>

{#each Array(count) as _, i (i)}
  <div
    class="skeleton skeleton--{variant}"
    style:width={width}
    style:height={height}
    aria-hidden="true"
  ></div>
{/each}

<style>
  .skeleton {
    background: linear-gradient(
      90deg,
      var(--color-gray-100) 25%,
      var(--color-gray-200) 50%,
      var(--color-gray-100) 75%
    );
    background-size: 200% 100%;
    animation: shimmer 1.6s var(--ease-smooth) infinite;
    border-radius: var(--radius-sm);
  }

  .skeleton--text {
    height: 0.875rem;
    width: 100%;
    margin-block: var(--sp-1);
  }

  .skeleton--rect {
    border-radius: var(--radius-lg);
  }

  .skeleton--circle {
    border-radius: var(--radius-full);
    width: 3rem;
    height: 3rem;
  }

  .skeleton--card {
    height: 20rem;
    border-radius: var(--radius-lg);
  }

  @keyframes shimmer {
    0% {
      background-position: 200% 0;
    }
    100% {
      background-position: -200% 0;
    }
  }
</style>