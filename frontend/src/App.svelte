<script lang="ts">
  import { onMount } from 'svelte';
  import { gsap } from 'gsap';
  import { router, defineRoute } from '$lib/router.svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import { langStore } from '$lib/stores/lang.svelte';
  import { t } from '$lib/i18n/index.svelte';

  import './assets/styles/tokens.css';
  import './assets/styles/global.css';

  import Home from './routes/Home.svelte';
  import Login from './routes/admin/Login.svelte';
  import Admin from './routes/admin/Admin.svelte';
  import NotFound from './routes/NotFound.svelte';

  defineRoute('/');
  defineRoute('/about');
  defineRoute('/organization');
  defineRoute('/portfolio');
  defineRoute('/portfolio/:slug');
  defineRoute('/contact');
  defineRoute('/admin/login');
  defineRoute('/admin');
  defineRoute('*');

  let appContainer: HTMLElement;

  onMount(() => {
    router.init();
    document.documentElement.lang = langStore.current;

    const isAdmin = router.current.path.startsWith('/admin');
    if (isAdmin) {
      authStore.initialized = true;
      authStore.init().then(() => {
        const path = router.current.path;
        if (path !== '/admin/login' && !authStore.isAuthenticated) {
          router.go('/admin/login');
        }
      });
    }

    gsap.fromTo(
      appContainer,
      { opacity: 0, y: 10 },
      { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }
    );
  });

  function getPageFromPath() {
    const m = router.current;
    if (m.path === '/') return { component: Home, key: 'home' };
    if (m.path === '/admin/login') return { component: Login, key: 'admin-login' };
    if (m.path === '/admin') return { component: Admin, key: 'admin' };
    return { component: NotFound, key: 'not-found' };
  }

  const page = $derived(getPageFromPath());

  const isAdminProtected = $derived(
    router.current.path.startsWith('/admin') && router.current.path !== '/admin/login'
  );
  const showPage = $derived(
    !isAdminProtected || (authStore.initialized && authStore.isAuthenticated)
  );

  $effect(() => {
    if (router.current.path.startsWith('/admin') && !authStore.initialized) {
      authStore.init().then(() => {
        if (
          router.current.path !== '/admin/login' &&
          !authStore.isAuthenticated
        ) {
          router.go('/admin/login');
        }
      });
    }
  });
</script>

<svelte:head>
  <title>{t('site_name')}</title>
</svelte:head>

<div bind:this={appContainer}>
  {#if showPage}
    {#key page.key}
      <page.component />
    {/key}
  {/if}
</div>