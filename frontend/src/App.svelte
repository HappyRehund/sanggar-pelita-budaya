<script lang="ts">
  import { onMount } from 'svelte';
  import { gsap } from 'gsap';
  import { router, defineRoute } from '$lib/router.svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import { settingsStore } from '$lib/stores/settings.svelte';
  import { langStore } from '$lib/stores/lang.svelte';
  import { t } from '$lib/i18n/index.svelte';

  import './assets/styles/tokens.css';
  import './assets/styles/global.css';

  import PublicLayout from './layouts/PublicLayout.svelte';
  import AdminLayout from './layouts/AdminLayout.svelte';
  import Toast from '$lib/components/Toast.svelte';

  import Home from './routes/Home.svelte';
  import AboutPage from '$modules/about/pages/AboutPage.svelte';
  import OrganizationPage from '$modules/organization/pages/OrganizationPage.svelte';
  import PortfolioListPage from '$modules/portfolio/pages/PortfolioListPage.svelte';
  import PortfolioDetailPage from '$modules/portfolio/pages/PortfolioDetailPage.svelte';
  import Login from './routes/admin/Login.svelte';
  import NotFound from './routes/NotFound.svelte';

  defineRoute('/');
  defineRoute('/about');
  defineRoute('/organization');
  defineRoute('/portfolio');
  defineRoute('/portfolio/:slug');
  defineRoute('/admin/login');
defineRoute('/admin');
defineRoute('/admin/portfolio');
defineRoute('/admin/organization');
defineRoute('/admin/settings');
defineRoute('*');

  let appContainer: HTMLElement;
  let pageContainer = $state<HTMLElement | null>(null);

  let lazyComponent = $state<any>(null);

  onMount(() => {
    router.init();
    document.documentElement.lang = langStore.current;

    settingsStore.init();

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

  async function loadAdminPage(path: string): Promise<any> {
    switch (path) {
      case '/admin': return (await import('$modules/admin/pages/Dashboard.svelte')).default;
      case '/admin/portfolio': return (await import('$modules/admin/pages/PortfolioAdmin.svelte')).default;
      case '/admin/organization': return (await import('$modules/admin/pages/OrganizationAdmin.svelte')).default;
      case '/admin/settings': return (await import('$modules/admin/pages/SettingsAdmin.svelte')).default;
      default: return NotFound;
    }
  }

  function getStaticPage() {
    const m = router.current;
    if (m.path === '/') return { component: Home, key: 'home' };
    if (m.path === '/about') return { component: AboutPage, key: 'about' };
    if (m.path === '/organization') return { component: OrganizationPage, key: 'organization' };
    if (m.path === '/portfolio') return { component: PortfolioListPage, key: 'portfolio' };
    if (m.path === '/portfolio/:slug') return { component: PortfolioDetailPage, key: 'portfolio-detail' };
    if (m.path === '/admin/login') return { component: Login, key: 'admin-login' };
    return null;
  }

  const staticPage = $derived(getStaticPage());

  $effect(() => {
    if (staticPage) {
      lazyComponent = null;
      return;
    }
    const path = router.current.path;
    if (path.startsWith('/admin') && path !== '/admin/login') {
      lazyComponent = null;
      loadAdminPage(path).then((comp) => {
        lazyComponent = comp;
      });
    }
  });

  const page = $derived(staticPage ?? (lazyComponent ? { component: lazyComponent, key: router.current.path } : null));

  const isAdminProtected = $derived(
    router.current.path.startsWith('/admin') && router.current.path !== '/admin/login'
  );
  const showPage = $derived(
    !isAdminProtected || (authStore.initialized && authStore.isAuthenticated)
  );

  const isLoginRoute = $derived(router.current.path === '/admin/login');
  const isAdminRoute = $derived(router.current.path.startsWith('/admin') && !isLoginRoute);
  const usePublicLayout = $derived(!isAdminRoute && !isLoginRoute && showPage);
  const useAdminLayout = $derived(isAdminRoute && showPage);

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

  $effect(() => {
    if (!pageContainer) return;
    gsap.fromTo(
      pageContainer,
      { opacity: 0, y: 8 },
      { opacity: 1, y: 0, duration: 0.35, ease: 'power2.out' }
    );
  });
</script>

<svelte:head>
  <title>{t('site_name')}</title>
</svelte:head>

<div bind:this={appContainer}>
  {#if showPage}
    {#if page}
      {#if usePublicLayout}
        <PublicLayout>
          {#key page.key}
            <div bind:this={pageContainer}>
              <page.component />
            </div>
          {/key}
        </PublicLayout>
      {:else if useAdminLayout}
        <AdminLayout>
          {#key page.key}
            <div bind:this={pageContainer}>
              <page.component />
            </div>
          {/key}
        </AdminLayout>
      {:else}
        {#key page.key}
          <div bind:this={pageContainer}>
            <page.component />
          </div>
        {/key}
      {/if}
    {:else}
      <div class="admin-loading" style="display:flex;align-items:center;justify-content:center;min-height:100vh">
        <div style="display:flex;gap:0.5rem">
          <span style="width:0.5rem;height:0.5rem;border-radius:50%;background:var(--color-accent);animation:bounce 0.6s infinite alternate"></span>
          <span style="width:0.5rem;height:0.5rem;border-radius:50%;background:var(--color-accent);animation:bounce 0.6s 0.2s infinite alternate"></span>
          <span style="width:0.5rem;height:0.5rem;border-radius:50%;background:var(--color-accent);animation:bounce 0.6s 0.4s infinite alternate"></span>
        </div>
      </div>
    {/if}
  {/if}
</div>

<Toast />

<style>
  @keyframes bounce {
    from { opacity: 0.3; transform: translateY(0); }
    to { opacity: 1; transform: translateY(-8px); }
  }
</style>