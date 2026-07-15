<script lang="ts">
  import * as LucideIcons from '@lucide/svelte';
  import type { Component } from 'svelte';

  interface IconProps {
    size?: number | string;
    color?: string;
    strokeWidth?: number | string;
    absoluteStrokeWidth?: boolean;
    class?: string;
  }

  interface Props extends IconProps {
    name: keyof typeof LucideIcons;
  }

  let {
    name,
    size = 24,
    color = 'currentColor',
    strokeWidth = 2,
    absoluteStrokeWidth = false,
    class: className = '',
  }: Props = $props();

  const IconComponent = $derived(
    (LucideIcons[name] as unknown as Component<IconProps>) ?? LucideIcons.X
  );
</script>

<IconComponent {size} {color} {strokeWidth} {absoluteStrokeWidth} class={className} />