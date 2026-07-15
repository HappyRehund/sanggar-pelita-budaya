<script lang="ts">
  import type { Snippet } from 'svelte';
  import Navbar from './Navbar.svelte';
  import Footer from './Footer.svelte';

  interface Props {
    children: Snippet;
  }

  let { children }: Props = $props();
</script>

<div class="public-layout">
  <Navbar />
  <main class="public-layout__main">
    {@render children()}
  </main>
  <Footer />
</div>

<style>
  .public-layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  .public-layout__main {
    flex: 1;
  }
</style>